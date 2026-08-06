<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Get today's date
        $today = Carbon::today()->format('Y-m-d');

        // 1. Total Present: Count UNIQUE employees
        $totalPresent = Attendance::where('attendance_date', $today)->distinct()->count('emp_id');

        // 2. Late Arrivals: Count UNIQUE late employees
        $lateArrivals = Attendance::where('attendance_date', $today)->where('status', '0')->distinct()->count('emp_id');

        // 3. Currently On Break: Count UNIQUE active break logs
        $onBreak = BreakLog::whereDate('break_start', $today)->whereNull('break_end')->distinct()->count('attendance_id');

        // 4. Average On-Time %: Count UNIQUE on-time employees
        $ontimeEmp = Attendance::where('attendance_date', $today)->where('status', '1')->distinct()->count('emp_id');
        $onTimePercentage = 0;
        
        if ($totalPresent > 0) {
            $onTimePercentage = round(($ontimeEmp / $totalPresent) * 100);
        }

        // Fetch the actual list of today's attendance records for Layer 2
        $todayLogs = Attendance::where('attendance_date', $today)->get();

        // Pass all variables to the dashboard view
        return view('admin.dashboard', compact(
            'totalPresent', 
            'lateArrivals', 
            'onBreak', 
            'onTimePercentage',
            'todayLogs' 
        ));
    }
}