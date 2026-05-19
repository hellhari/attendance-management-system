<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Illuminate\Http\Request;

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
    | DASHBOARD (NEW)
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
    | AI FACE ATTENDANCE API (PYTHON CALLS THIS)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $emp_id = $request->emp_id;

        if (!$emp_id) {
            return response()->json([
                'status' => false,
                'message' => 'Employee ID required'
            ]);
        }

        $employee = Employee::where('id', $emp_id)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ]);
        }

        $attendance = Attendance::where('emp_id', $emp_id)
            ->whereDate('attendance_date', now())
            ->first();

        /*
        |--------------------------------------------------------------------------
        | CHECK IN
        |--------------------------------------------------------------------------
        */
        if (!$attendance) {

            $attendance = Attendance::create([
                'emp_id' => $emp_id,
                'attendance_date' => now()->toDateString(),
                'attendance_time' => now()->toTimeString(),
                'state' => 0,
                'status' => 1,
                'type' => 0
            ]);

            return response()->json([
                'status' => true,
                'action' => 'CHECK_IN',
                'message' => 'Check-in successful',
                'data' => $attendance
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK OUT
        |--------------------------------------------------------------------------
        */
        if (!$attendance->check_out_time) {

            $attendance->check_out_time = now()->toTimeString();
            $attendance->save();

            return response()->json([
                'status' => true,
                'action' => 'CHECK_OUT',
                'message' => 'Check-out successful',
                'data' => $attendance
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Attendance already completed today'
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
    | CHART DATA (FOR DASHBOARD GRAPH)
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

        return response()->json([
            'days' => $days,
            'counts' => $counts
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXCEL EXPORT (HOOK)
    |--------------------------------------------------------------------------
    */
    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport,
            'attendance.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF EXPORT (HOOK)
    |--------------------------------------------------------------------------
    */
    public function exportPdf()
    {
        $data = Attendance::with('employee')->get();

        $pdf = \PDF::loadView('admin.attendance_pdf', compact('data'));

        return $pdf->download('attendance.pdf');
    }
}