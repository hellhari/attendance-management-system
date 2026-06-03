<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return view('admin.attendance')
            ->with(['attendances' => Attendance::orderBy('created_at', 'desc')->get()]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW LATE TIME
    |--------------------------------------------------------------------------
    */
    public function indexLatetime()
    {
        return view('admin.latetime')
            ->with(['latetimes' => Latetime::orderBy('created_at', 'desc')->get()]);
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD 
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $todayAttendance = Attendance::whereDate('created_at', now())->count();
        $employees = Employee::count();
        $lateToday = Attendance::where('status', 0)
            ->whereDate('created_at', now())
            ->count();

        return view('admin.dashboard', compact('todayAttendance', 'employees', 'lateToday'));
    }

    /*
    |--------------------------------------------------------------------------
    | PHASE 2: AI FACE ATTENDANCE API (PYTHON CALLS THIS)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $emp_id = $request->emp_id;
        
        if (!$emp_id) {
            return response()->json(['status' => false, 'message' => 'Employee ID required']);
        }

        $employee = Employee::where('id', $emp_id)->first();
        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee not found']);
        }

        $now = Carbon::now();
        $timeString = $now->format('H:i');
        $dateString = $now->toDateString();

        // 1. DETERMINE CURRENT STATE
        $attendance = Attendance::where('emp_id', $emp_id)
            ->whereDate('attendance_date', $dateString)
            ->first();

        $nextState = 'Checked In';
        $folderPrefix = 'face_logins';

        if ($attendance) {
            if ($attendance->current_state === 'Checked In') {
                $nextState = 'On Break';
                $folderPrefix = 'face_breaks';
            } elseif ($attendance->current_state === 'On Break') {
                $nextState = 'Returned';
                $folderPrefix = 'face_returns';
            } elseif ($attendance->current_state === 'Returned') {
                $nextState = 'Checked Out';
                $folderPrefix = 'face_logouts';
            } else {
                return response()->json(['status' => false, 'message' => 'Shift already completed today.']);
            }
        }

        // 2. THE STORAGE STEP (AWS S3)
        $s3FilePath = null;
        if ($request->hasFile('face_image')) {
            $fileName = strtolower(str_replace(' ', '_', $nextState)) . "_{$dateString}_{$timeString}.jpg";
            $s3FilePath = "{$folderPrefix}/{$emp_id}/{$fileName}";
            Storage::disk('s3')->put($s3FilePath, file_get_contents($request->file('face_image')));
        }

        // 3. THE DATABASE STEP
        if (!$attendance) {
            // First punch of the day
            $attendance = Attendance::create([
                'emp_id' => $emp_id,
                'attendance_date' => $dateString,
                'attendance_time' => $now->toTimeString(),
                'state' => 0,
                'status' => 1,
                'type' => 0,
                'current_state' => $nextState,
                'punch_log' => ['check_in' => $timeString]
            ]);

            // Run existing late calculation
            self::lateTimeDevice($now->toDateTimeString(), $employee);

        } else {
            // Updating state for Breaks/Logouts
            $punchLog = is_array($attendance->punch_log) ? $attendance->punch_log : [];
            
            if ($nextState === 'On Break') $punchLog['break_start'] = $timeString;
            if ($nextState === 'Returned') $punchLog['break_end'] = $timeString;
            
            if ($nextState === 'Checked Out') {
                $attendance->check_out_time = $now->toTimeString();
                $punchLog['check_out'] = $timeString;

                // --- CARBON MATH ---
                $checkIn = Carbon::parse($attendance->attendance_time);
                $totalMinutes = $now->diffInMinutes($checkIn);
                
                $breakMinutes = 0;
                if (isset($punchLog['break_start']) && isset($punchLog['break_end'])) {
                    $breakStart = Carbon::parse($dateString . ' ' . $punchLog['break_start']);
                    $breakEnd = Carbon::parse($dateString . ' ' . $punchLog['break_end']);
                    $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                }

                $attendance->worked_minutes = $totalMinutes - $breakMinutes;
                $attendance->shift_status = ($attendance->worked_minutes >= 480) ? 'Full Shift' : 'Partial Shift';
            }

            $attendance->current_state = $nextState;
            $attendance->punch_log = $punchLog;
            $attendance->save();
        }

        return response()->json([
            'status' => true,
            'action' => strtoupper(str_replace(' ', '_', $nextState)),
            'message' => "{$nextState} successful",
            's3_path' => $s3FilePath,
            'data' => $attendance
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LATE TIME CALCULATION
    |--------------------------------------------------------------------------
    */
    public static function lateTimeDevice($att_dateTime, Employee $employee)
    {
        try {
            if (!$employee->schedules || $employee->schedules->isEmpty()) {
                return;
            }

            $attendance_time = new DateTime($att_dateTime);
            $checkin = new DateTime($employee->schedules->first()->time_in);

            $difference = $checkin->diff($attendance_time)->format('%H:%I:%S');

            $latetime = new Latetime();
            $latetime->emp_id = $employee->id;
            $latetime->duration = $difference;
            $latetime->latetime_date = date('Y-m-d', strtotime($att_dateTime));
            $latetime->save();

        } catch (\Exception $e) {
            // silent fail
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHART DATA & EXPORTS
    |--------------------------------------------------------------------------
    */
    public function chartData()
    {
        $days = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[] = $date;
            $counts[] = Attendance::whereDate('attendance_date', $date)->count();
        }

        return response()->json(['days' => $days, 'counts' => $counts]);
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport,
            'attendance.xlsx'
        );
    }

    public function exportPdf()
    {
        $data = Attendance::with('employee')->get();
        $pdf = \PDF::loadView('admin.attendance_pdf', compact('data'));
        return $pdf->download('attendance.pdf');
    }
}