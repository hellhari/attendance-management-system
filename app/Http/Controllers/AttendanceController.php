<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('employee_query');
        $employee = null;
        $monthlyLogs = [];
        $attendancePercentage = 0;

        if ($query) {
            $employee = Employee::where('id', $query)
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                        ->first();

            if ($employee) {
                // டேட்டாபேஸிலிருந்து முழு ரெக்கார்டையும் எடுப்பது
                $rawLogs = Attendance::where('emp_id', $employee->id)->get();

                // தேதியின் அடிப்படையில் பிரிப்பது
                $groupedLogs = $rawLogs->groupBy(function($log) {
                    return Carbon::parse($log->attendance_date)->format('Y-m-d');
                });

                $finalLogs = collect();
                $presentDays = 0;
                $totalDays = $groupedLogs->count();

                foreach ($groupedLogs as $date => $dayLogs) {
                    // நேரத்தின் அடிப்படையில் சரியாக வரிசைப்படுத்துவது
                    $dayLogs = $dayLogs->sortBy('attendance_time')->values();
                    
                    $totalMins = 0;
                    $logCount = $dayLogs->count();
                    $isWorkingToday = false;

                    // முதல் ஸ்டெப்: ஒரு நாளின் மொத்த வேலை நேரத்தைக் கணக்கிடுவது
                    foreach ($dayLogs as $log) {
                        $actualMins = $log->worked_minutes;
                        $cleanDate = Carbon::parse($log->attendance_date)->format('Y-m-d');
                        
                        if (!$actualMins && $log->attendance_time) {
                            $checkIn = Carbon::parse($cleanDate . ' ' . $log->attendance_time);
                            if ($log->check_out_time) {
                                $checkOut = Carbon::parse($cleanDate . ' ' . $log->check_out_time);
                                $actualMins = $checkIn->diffInMinutes($checkOut);
                            } elseif (Carbon::parse($cleanDate)->isToday()) {
                                $actualMins = $checkIn->diffInMinutes(Carbon::now());
                                $isWorkingToday = true;
                            }
                        }
                        $totalMins += (int)$actualMins;
                    }

                    // இரண்டாவது ஸ்டெப்: ஒவ்வொரு ரெக்கார்டுக்கும் சரியான ஸ்டேட்டஸ் கொடுப்பது
                    foreach ($dayLogs as $index => $log) {
                        $isLastLog = ($index === $logCount - 1);
                        $cleanDate = Carbon::parse($log->attendance_date)->format('Y-m-d');
                        
                        // இந்த குறிப்பிட்ட செஷனுக்கான நிமிடங்களைக் கணக்கிடுவது
                        $sessionMins = $log->worked_minutes;
                        if (!$sessionMins && $log->attendance_time) {
                            $checkIn = Carbon::parse($cleanDate . ' ' . $log->attendance_time);
                            if ($log->check_out_time) {
                                $checkOut = Carbon::parse($cleanDate . ' ' . $log->check_out_time);
                                $sessionMins = $checkIn->diffInMinutes($checkOut);
                            } elseif (Carbon::parse($cleanDate)->isToday()) {
                                $sessionMins = $checkIn->diffInMinutes(Carbon::now());
                            } else {
                                $sessionMins = 0;
                            }
                        }
                        
                        $hours = floor((int)$sessionMins / 60);
                        $mins = (int)$sessionMins % 60;

                        // ==========================================
                        // STUPID-PROOF DYNAMIC STATUS LOGIC
                        // ==========================================
                        $status = 'Absent';
                        if (empty($log->check_out_time)) {
                            $status = 'In Progress'; // செக்கவுட் ஆகவில்லை என்றால் In Progress
                        } else {
                            if (!$isLastLog) {
                                $status = 'Break Time'; // இடைப்பட்ட செக்கவுட்கள் அனைத்தும் Break Time
                            } else {
                                // அன்றைய நாளின் கடைசி செக்கவுட்!
                                if ($totalMins >= 540) {
                                    $status = 'Present'; // மொத்த நேரம் 9 மணி நேரம் இருந்தால் Present
                                } else {
                                    $status = 'Absent'; // 9 மணி நேரத்திற்குக் குறைவு என்றால் Absent
                                }
                            }
                        }

                        // லாராவெல் ஆப்ஜெக்ட்டை விடுத்து, புதிய ஆப்ஜெக்ட்டை உருவாக்குவது (To prevent caching/overwrite issues)
                        $finalLogs->push((object)[
                            'date' => $log->attendance_date,
                            'time_in' => $log->attendance_time,
                            'time_out' => $log->check_out_time,
                            'net_hours' => "{$hours}h {$mins}m",
                            'status' => $status
                        ]);
                    }

                    // ஓவர்-ஆல் சதவீத கணக்கீடு (9 Hours Rule)
                    if ($totalMins >= 540 || ($date === Carbon::today()->format('Y-m-d') && $isWorkingToday)) {
                        $presentDays++;
                    }
                }

                // UI-க்காக தேதியின் அடிப்படையில் லேட்டஸ்ட் முதலில் வரும்படி வரிசைப்படுத்துவது
                $logs = $finalLogs->sortByDesc(function($log) {
                    return Carbon::parse($log->date)->format('Y-m-d') . ' ' . $log->time_in;
                })->values();

                $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

                $monthlyLogs = $logs->groupBy(function($log) {
                    return Carbon::parse($log->date)->format('F Y');
                });
            }
        }

        return view('admin.attendance', compact('employee', 'monthlyLogs', 'attendancePercentage'));
    }

    public function indexLatetime()
    {
        $standardStartTime = '09:30:00'; 
        
        $allAttendances = Attendance::with('employee')->orderBy('attendance_date', 'desc')->get();
        $latetimes = collect(); 

        $groupedAttendances = $allAttendances->groupBy(function($item) {
            return Carbon::parse($item->attendance_date)->format('Y-m-d');
        });

        foreach ($groupedAttendances as $date => $dayRecords) {
            $employeeFirstScans = $dayRecords->groupBy(function($item) {
                return (string) $item->emp_id;
            })->map(function ($employeeRecords) {
                return $employeeRecords->sortBy('attendance_time')->first();
            });

            foreach ($employeeFirstScans as $firstScan) {
                $timeIn = Carbon::parse($date . ' ' . $firstScan->attendance_time);
                $shiftStart = Carbon::parse($date . ' ' . $standardStartTime);

                if ($timeIn->greaterThan($shiftStart)) {
                    $lateMinutes = $shiftStart->diffInMinutes($timeIn);
                    
                    $hours = floor($lateMinutes / 60);
                    $mins = $lateMinutes % 60;
                    
                    $firstScan->formatted_late_time = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} mins";
                    $firstScan->clean_time_in = $timeIn->format('h:i A');
                    
                    if ($firstScan->check_out_time) {
                        $firstScan->clean_time_out = Carbon::parse($date . ' ' . $firstScan->check_out_time)->format('h:i A');
                    } else {
                        $firstScan->clean_time_out = 'Still Working';
                    }

                    $latetimes->push($firstScan); 
                }
            }
        }
        
        return view('admin.latetime', compact('latetimes'));
    }

    public function store(Request $request) 
    {
        $nextState = $request->input('state');
        $attendance = Attendance::find($request->input('attendance_id'));
        $emp_id = $request->input('emp_id');
        $now = Carbon::now();

        if ($nextState === 'On Break') {
            BreakLog::create([
                'attendance_id' => $attendance->id,
                'emp_id' => $emp_id,
                'break_start' => $now,
            ]);
            
            $attendance->shift_status = 'On Break';
            $attendance->save();
        }

        if ($nextState === 'Returned') {
            $openBreak = BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }
            
            $attendance->shift_status = 'Working';
            $attendance->save();
        }

        if ($nextState === 'Checked Out') {
            $attendance->check_out_time = $now->toTimeString();

            $openBreak = BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }

            $cleanDate = Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            $exactCheckInString = $cleanDate . ' ' . $attendance->attendance_time;
            $checkIn = Carbon::parse($exactCheckInString);
            
            $totalMinutes = $checkIn->diffInMinutes($now);
            $breakMinutes = BreakLog::where('attendance_id', $attendance->id)->sum('duration_minutes');

            $attendance->worked_minutes = $totalMinutes - $breakMinutes;
            $attendance->shift_status = ($attendance->worked_minutes >= 540) ? 'Full Shift' : 'Partial Shift';
            
            $attendance->save();
        }

        return redirect()->back()->with('success', 'Attendance marked!');
    }
}