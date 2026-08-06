<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the main Attendance Logs page.
     */
    public function index()
    {
        // 1. Fetch the data
        $attendances = Attendance::all(); 
        
        // 2. Point to the 'attendance' table view, NOT the 'index' dashboard view
        return view('admin.attendance', compact('attendances')); 
    }

    public function indexLatetime()
    {
        // --- 1. SET YOUR SHIFT START TIME HERE ---
        // Change this to match Pragnaware's official start time!
        $standardStartTime = '09:30:00'; 
        
        // Fetch all attendances (eager loading employee data if the relationship exists)
        $allAttendances = Attendance::with('employee')->orderBy('attendance_date', 'desc')->get();
        $latetimes = collect(); // Create an empty bucket to hold the late records
// --- 2. ISOLATE MORNING ARRIVALS ---
        // Group safely by converting the Date object into a strict text string
        $groupedAttendances = $allAttendances->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->attendance_date)->format('Y-m-d');
        });

        foreach ($groupedAttendances as $date => $dayRecords) {
            // Group safely by converting the Employee ID into a strict text string
            $employeeFirstScans = $dayRecords->groupBy(function($item) {
                return (string) $item->emp_id;
            })->map(function ($employeeRecords) {
                return $employeeRecords->sortBy('attendance_time')->first();
            });

           // --- 3. CALCULATE LATE TIME ---
            foreach ($employeeFirstScans as $firstScan) {
                
                // FIX: We use the $date variable from the outer loop because it is already a clean 'Y-m-d' string. 
                // This prevents the "Double time" crash!
                $timeIn = \Carbon\Carbon::parse($date . ' ' . $firstScan->attendance_time);
                $shiftStart = \Carbon\Carbon::parse($date . ' ' . $standardStartTime);

                if ($timeIn->greaterThan($shiftStart)) {
                    // Employee arrived after the start time! Calculate the difference.
                    $lateMinutes = $shiftStart->diffInMinutes($timeIn);
                    
                    $hours = floor($lateMinutes / 60);
                    $mins = $lateMinutes % 60;
                    
                    // Attach the math directly to the record for the Blade file
                    $firstScan->formatted_late_time = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} mins";
                    $firstScan->clean_time_in = $timeIn->format('h:i A');
                    
                    if ($firstScan->check_out_time) {
                        // We must use $date here as well so the Check-Out time doesn't crash either!
                        $firstScan->clean_time_out = \Carbon\Carbon::parse($date . ' ' . $firstScan->check_out_time)->format('h:i A');
                    } else {
                        $firstScan->clean_time_out = 'Still Working';
                    }

                    $latetimes->push($firstScan); // Push it to our Late table bucket!
                }
            }
        }
        
        return view('admin.latetime', compact('latetimes'));
    }

    /**
     * Handle the biometric face scan check-in/check-out.
     */
    public function store(Request $request) 
    {
        $nextState = $request->input('state');
        $attendance = Attendance::find($request->input('attendance_id'));
        $emp_id = $request->input('emp_id');
        $now = Carbon::now();

        // --- FACE SCAN STATE LOGIC ---
        if ($nextState === 'On Break') {
            \App\Models\BreakLog::create([
                'attendance_id' => $attendance->id,
                'emp_id' => $emp_id,
                'break_start' => $now,
            ]);
            
            $attendance->shift_status = 'On Break';
            $attendance->save();
        }

        if ($nextState === 'Returned') {
            $openBreak = \App\Models\BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(\Carbon\Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }
            
            $attendance->shift_status = 'Working';
            $attendance->save();
        }

        if ($nextState === 'Checked Out') {
            $attendance->check_out_time = $now->toTimeString();

            $openBreak = \App\Models\BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(\Carbon\Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }

            $exactCheckInString = $attendance->attendance_date . ' ' . $attendance->attendance_time;
            $checkIn = \Carbon\Carbon::parse($exactCheckInString);
            
            $totalMinutes = $checkIn->diffInMinutes($now);
            $breakMinutes = \App\Models\BreakLog::where('attendance_id', $attendance->id)->sum('duration_minutes');

            $attendance->worked_minutes = $totalMinutes - $breakMinutes;
            $attendance->shift_status = ($attendance->worked_minutes >= 480) ? 'Full Shift' : 'Partial Shift';
            
            $attendance->save();
        }

        return redirect()->back()->with('success', 'Attendance marked!');
    }
}