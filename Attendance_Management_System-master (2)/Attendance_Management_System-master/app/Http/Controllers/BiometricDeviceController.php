<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\FingerHelper;
use App\Http\Requests\FingerDevice\StoreRequest;
use App\Http\Requests\FingerDevice\UpdateRequest;
use App\Models\FingerDevices;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Rats\Zkteco\Lib\ZKTeco;

class BiometricDeviceController extends Controller
{
    public function index()
    {
        $devices = FingerDevices::all();
        return view('admin.fingerDevices.index', compact('devices'));
    }

    public function create()
    {
        return view('admin.fingerDevices.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $helper = new FingerHelper();
        $device = $helper->init($request->input('ip'));

        if ($device->connect()) {
            $serial = $helper->getSerial($device);
            FingerDevices::create($request->validated() + ['serialNumber' => $serial]);
            flash()->success('Success', 'Biometric Device created successfully!');
        } else {
            flash()->error('Oops', 'Failed connecting to Biometric Device!');
        }

        return redirect()->route('finger_device.index');
    }

    public function show(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.show', compact('fingerDevice'));
    }

    public function edit(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.edit', compact('fingerDevice'));
    }

    public function update(UpdateRequest $request, FingerDevices $fingerDevice): RedirectResponse
    {
        $fingerDevice->update($request->validated());
        flash()->success('Success', 'Biometric Device Updated successfully!');
        return redirect()->route('finger_device.index');
    }

    public function destroy(FingerDevices $fingerDevice): RedirectResponse
    {
        try {
            $fingerDevice->delete();
        } catch (\Exception $e) {
            toast("Failed to delete {$fingerDevice->name}", 'error');
        }

        flash()->success('Success', 'Biometric Device deleted successfully!');
        return back();
    }

    public function addEmployee(FingerDevices $fingerDevice): RedirectResponse
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);
        $device->connect();

        $deviceUsers = collect($device->getUser())->pluck('uid');

        $employees = Employee::select('name', 'id')
            ->whereNotIn('id', $deviceUsers)
            ->get();

        $i = 1;

        foreach ($employees as $employee) {
            $device->setUser($i++, $employee->id, $employee->name, '', '0', '0');
        }

        flash()->success('Success', 'All Employees added to Biometric device successfully!');
        return back();
    }

    public function getAttendance(FingerDevices $fingerDevice)
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);
        $device->connect();

        $data = $device->getAttendance();

        foreach ($data as $value) {

            if ($value['type'] == 0) {

                if ($employee = Employee::whereId($value['id'])->first()) {

                    if (!Attendance::whereAttendance_date(date('Y-m-d', strtotime($value['timestamp'])))
                        ->whereEmp_id($value['id'])
                        ->whereType(0)
                        ->first()) {

                        $att = new Attendance();
                        $att->uid = $value['uid'];
                        $att->emp_id = $value['id'];
                        $att->state = $value['state'];
                        $att->attendance_time = date('H:i:s', strtotime($value['timestamp']));
                        $att->attendance_date = date('Y-m-d', strtotime($value['timestamp']));
                        $att->type = $value['type'];

                        if (!($employee->schedules->first()->time_in >= $att->attendance_time)) {
                            $att->status = 0;
                            AttendanceController::lateTimeDevice($value['timestamp'], $employee);
                        }

                        $att->save();
                    }
                }

            } else {

                if ($employee = Employee::whereId($value['id'])->first()) {

                    if (!Leave::whereLeave_date(date('Y-m-d', strtotime($value['timestamp'])))
                        ->whereEmp_id($value['id'])
                        ->whereType(1)
                        ->first()) {

                        $leave = new Leave();
                        $leave->uid = $value['uid'];
                        $leave->emp_id = $value['id'];
                        $leave->state = $value['state'];
                        $leave->leave_time = date('H:i:s', strtotime($value['timestamp']));
                        $leave->leave_date = date('Y-m-d', strtotime($value['timestamp']));
                        $leave->type = $value['type'];

                        if (!($employee->schedules->first()->time_out <= $leave->leave_time)) {
                            $leave->status = 0;
                        } else {
                            LeaveController::overTimeDevice($value['timestamp'], $employee);
                        }

                        $leave->save();
                    }
                }
            }
        }

        flash()->success('Success', 'Attendance Synced Successfully!');
        return back();
    }

    // ✅ NEW FUNCTION (MANUAL SYNC)
    public function sync()
    {
        $devices = FingerDevices::all();

        foreach ($devices as $fingerDevice) {

            $device = new ZKTeco($fingerDevice->ip, 4370);

            if (!$device->connect()) {
                continue;
            }

            $data = $device->getAttendance();

            foreach ($data as $value) {

                if ($value['type'] == 0) {

                    if ($employee = Employee::whereId($value['id'])->first()) {

                        if (!Attendance::whereAttendance_date(date('Y-m-d', strtotime($value['timestamp'])))
                            ->whereEmp_id($value['id'])
                            ->whereType(0)
                            ->first()) {

                            Attendance::create([
                                'uid' => $value['uid'],
                                'emp_id' => $value['id'],
                                'state' => $value['state'],
                                'attendance_time' => date('H:i:s', strtotime($value['timestamp'])),
                                'attendance_date' => date('Y-m-d', strtotime($value['timestamp'])),
                                'type' => $value['type'],
                                'status' => 1
                            ]);
                        }
                    }

                } else {

                    if ($employee = Employee::whereId($value['id'])->first()) {

                        if (!Leave::whereLeave_date(date('Y-m-d', strtotime($value['timestamp'])))
                            ->whereEmp_id($value['id'])
                            ->whereType(1)
                            ->first()) {

                            Leave::create([
                                'uid' => $value['uid'],
                                'emp_id' => $value['id'],
                                'state' => $value['state'],
                                'leave_time' => date('H:i:s', strtotime($value['timestamp'])),
                                'leave_date' => date('Y-m-d', strtotime($value['timestamp'])),
                                'type' => $value['type'],
                                'status' => 1
                            ]);
                        }
                    }
                }
            }
        }

        return "Attendance Synced Successfully!";
    }
}