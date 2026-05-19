<?php

use Illuminate\Support\Facades\Route;
use App\Models\Employee;
use Carbon\Carbon;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\BiometricDeviceController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| QUICK ATTENDANCE (LEGACY / DEVICE SUPPORT)
|--------------------------------------------------------------------------
*/
Route::get('attended/{user_id}', [AttendanceController::class, 'attended'])->name('attended');
Route::get('attended-before/{user_id}', [AttendanceController::class, 'attendedBefore'])->name('attendedBefore');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Auth::routes([
    'register' => false,
    'reset' => false,
]);

/*
|--------------------------------------------------------------------------
| FACE MODULE (PUBLIC API)
|--------------------------------------------------------------------------
*/
Route::post('/employees/{employee}/capture-face', [EmployeeController::class, 'captureFace'])
    ->name('employees.capture.face');

Route::get('/employees/{employee}/capture', function (Employee $employee) {
    return view('admin.face_capture', [
        'employee' => $employee
    ]);
})->name('employees.capture.view');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {

    // DASHBOARD
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])
        ->name('attendance.dashboard');

    // EMPLOYEES
    Route::resource('/employees', EmployeeController::class);

    Route::get('/employees/create-face', [EmployeeController::class, 'create'])
        ->name('employees.face.create');

    // ATTENDANCE
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance');

    Route::get('/latetime', [AttendanceController::class, 'indexLatetime'])
        ->name('latetime');

    // --- VISITOR MANAGEMENT (STABLE VERSION) ---
    Route::get('/visitor-checkin', function () {
        return view('admin.visitor_checkin');
    })->name('visitor.checkin');

    Route::post('/visitor/store', [VisitorController::class, 'store'])
        ->name('visitor.store');

    Route::get('/visitor-logs', [VisitorController::class, 'index'])
        ->name('admin.visitor_index'); // Matches the name used in your Index blade

    Route::post('/visitor/checkout/{id}', [VisitorController::class, 'checkout'])
        ->name('visitor.checkout');

    Route::get('/visitor-download', [VisitorController::class, 'downloadReport'])
        ->name('visitor.export'); // Matches the download button in your Index blade

    // LEAVE / OVERTIME
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave');
    Route::get('/overtime', [LeaveController::class, 'indexOvertime'])->name('overtime');

    // SCHEDULE
    Route::resource('/schedule', ScheduleController::class);

    // MANUAL CHECK
    Route::get('/check', [CheckController::class, 'index'])->name('check');
    Route::get('/sheet-report', [CheckController::class, 'sheetReport'])->name('sheet-report');
    Route::post('/check-store', [CheckController::class, 'CheckStore'])->name('check_store');

    // BIOMETRIC DEVICE
    Route::resource('/finger_device', BiometricDeviceController::class);
    Route::delete('/finger_device/destroy', [BiometricDeviceController::class, 'massDestroy'])->name('finger_device.massDestroy');
    Route::get('/finger_device/{fingerDevice}/employees/add', [BiometricDeviceController::class, 'addEmployee'])->name('finger_device.add.employee');
    Route::get('/finger_device/{fingerDevice}/get/attendance', [BiometricDeviceController::class, 'getAttendance'])->name('finger_device.get.attendance');
    Route::get('/sync-attendance', [BiometricDeviceController::class, 'sync'])->name('sync.attendance');

    // AUTO CLEAR JOB
    Route::get('/finger_device/clear/attendance', function () {
        $midnight = Carbon::createFromTime(23, 50, 00);
        $diff = now()->diffInMinutes($midnight);
        dispatch(new \App\Jobs\ClearAttendanceJob())->delay(now()->addMinutes($diff));
        return back();
    })->name('finger_device.clear.attendance');

    // CHART + EXPORT
    Route::get('/attendance/chart-data', [AttendanceController::class, 'chartData'])->name('attendance.chart');
    Route::get('/attendance/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
    Route::get('/attendance/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');
});

/*
|--------------------------------------------------------------------------
| AUTH USER ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {
    // future user features
});