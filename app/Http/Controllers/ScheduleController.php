<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\ScheduleEmp;
use Illuminate\Support\Str;
use Carbon\Carbon; // ✅ Imported Carbon for chronological math

class ScheduleController extends Controller
{
    public function index()
    {
        return view('admin.schedule')
            ->with('schedules', Schedule::all());
    }

    public function store(ScheduleEmp $request)
    {
        $request->validated();

        $schedule = new Schedule();
        $schedule->slug = Str::slug($request->slug);

        // ✅ OVERNIGHT SHIFT MATH (+1 Day Logic)
        $time_in = Carbon::parse($request->time_in);
        $time_out = Carbon::parse($request->time_out);

        // If checkout time is earlier than check-in time, it crosses midnight
        if ($time_out->lessThan($time_in)) {
            $time_out->addDay(); 
        }

        // Carbon automatically sanitizes the format to 'HH:MM:SS' for the database
        $schedule->time_in = $time_in->format('H:i');
        $schedule->time_out = $time_out->format('H:i');
        
        $schedule->save();

        flash()->success('Success', 'Schedule has been created successfully !');

        return redirect()->route('schedule.index');
    }

    public function update(ScheduleEmp $request, Schedule $schedule)
    {
        $request->validated();

        $schedule->slug = Str::slug($request->slug);

        // ✅ APPLY THE SAME OVERNIGHT MATH FOR UPDATES
        $time_in = Carbon::parse($request->time_in);
        $time_out = Carbon::parse($request->time_out);

        if ($time_out->lessThan($time_in)) {
            $time_out->addDay(); 
        }

        // This permanently removes the need for your old "str_split" hack
        $schedule->time_in = $time_in->toTimeString();
        $schedule->time_out = $time_out->toTimeString();
        
        $schedule->save();

        flash()->success('Success', 'Schedule has been updated successfully !');

        return redirect()->route('schedule.index');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        flash()->success('Success', 'Schedule has been deleted successfully !');

        return redirect()->route('schedule.index');
    }
}