<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;

class CheckController extends Controller
{
    public function index()
    {
        return view('admin.check')->with(['employees' => Employee::all()]);
    }

    public function CheckStore(Request $request)
    {
        // ATTENDANCE BLOCK
        if (isset($request->attd)) {
            foreach ($request->attd as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId($key)->first()) {
                        if (
                            !Attendance::whereAttendance_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(0)
                                ->first()
                        ) {
                            $data = new Attendance();
                            $data->emp_id = $key;
                            
                            // Safety Check: Make sure schedule exists before checking time_in
                            $schedule = $employee->schedules->first();
                            $data->attendance_time = $schedule ? date('H:i:s', strtotime($schedule->time_in)) : '09:00:00';
                            $data->attendance_date = $keys;
                            
                            $data->save();
                        }
                    }
                }
            }
        }
        
        // LEAVE BLOCK
        if (isset($request->leave)) {
            foreach ($request->leave as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId($key)->first()) {
                        if (
                            !Leave::whereLeave_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(1)
                                ->first()
                        ) {
                            $data = new Leave();
                            $data->emp_id = $key;
                            
                            // Safety Check: Make sure schedule exists before checking time_out
                            $schedule = $employee->schedules->first();
                            $data->leave_time = $schedule ? $schedule->time_out : '18:00:00';
                            $data->leave_date = $keys;
                            
                            $data->save();
                        }
                    }
                }
            }
        }
        
        flash()->success('Success', 'You have successfully submited the attendance !');
        return back();
    }
    
    public function sheetReport()
    {
        return view('admin.sheet-report')->with(['employees' => Employee::all()]);
    }
}