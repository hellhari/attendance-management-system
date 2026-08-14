@extends('layouts.master')

@section('content')

<style>
    .profile-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 24px; text-align: center; border-top: 4px solid #5867dd; }
    .profile-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; margin-bottom: 15px; }
    .attendance-badge { font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 20px; background: #d1fae5; color: #059669; display: inline-block; margin-top: 10px; }
    .month-accordion .card { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 15px; box-shadow: none; }
    .month-accordion .card-header { background-color: #f8fafc; padding: 15px 20px; cursor: pointer; border-bottom: none; border-radius: 8px; }
    .month-accordion .card-header h5 { margin: 0; font-size: 16px; color: #1e293b; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .month-accordion .card-header h5 i { color: #5867dd; transition: transform 0.3s; }
    .month-accordion .card-header.collapsed h5 i { transform: rotate(-90deg); }
    .ot-card { background: #fff; border-radius: 12px; padding: 20px; margin-top: 20px; border: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .not-found-card { background: #fff; border-radius: 12px; padding: 60px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #fee2e2; }
    .not-found-icon { font-size: 70px; color: #f43f5e; margin-bottom: 20px; display: inline-block; }
</style>

<div class="row mt-4">
    
    <!-- Full Logs Search Bar -->
    <div class="col-12 mb-4">
        <form action="/attendance" method="GET" class="input-group shadow-sm" style="max-width: 500px; margin: 0 auto;">
            <input type="text" name="employee_query" class="form-control border-primary" placeholder="Search Employee ID or Name..." value="{{ request('employee_query') }}" required>
            <div class="input-group-append">
                <button class="btn btn-primary px-4" type="submit"><i class="ti-search"></i> Search</button>
            </div>
        </form>
    </div>

    <!-- LOGIC: IF SEARCH QUERY IS ENTERED BUT NO EMPLOYEE FOUND IN DB -->
    @if(request('employee_query') && !isset($employee))
        <div class="col-12">
            <div class="not-found-card mx-auto" style="max-width: 600px;">
                <i class="ti-face-sad not-found-icon"></i>
                <h3 class="text-dark font-weight-bold">Sorry, Result Not Found!</h3>
                <p class="text-muted mt-2" style="font-size: 15px;">
                    We couldn't find any employee matching <b>"{{ request('employee_query') }}"</b> in the database.<br>
                    Please check the spelling or enter a valid Employee ID.
                </p>
                <a href="/check" class="btn btn-outline-primary mt-4 px-4 font-weight-bold"><i class="ti-arrow-left mr-2"></i>Back to Daily Sheet</a>
            </div>
        </div>

    <!-- LOGIC: IF EMPLOYEE IS FOUND IN DB -->
    @elseif(isset($employee))
        <!-- Left Side: Dynamic Employee Profile from DB -->
        <div class="col-lg-4 col-md-5">
            <div class="profile-card">
                <!-- Using Database Image or Fallback -->
                <img src="{{ $employee->image_path ? asset($employee->image_path) : 'https://ui-avatars.com/api/?name='.urlencode($employee->name).'&background=5867dd&color=fff&size=128' }}" alt="Profile" class="profile-avatar">
                <h4 class="mb-1 text-dark font-weight-bold">{{ $employee->name }}</h4>
                <p class="text-muted mb-2">{{ $employee->position }} | ID: #{{ $employee->id }}</p>
                
                <div class="mt-3 border-top pt-3">
                    <p class="text-muted text-sm mb-1">Overall Attendance (This Year)</p>
                    <div class="attendance-badge">
                        <i class="ti-stats-up mr-1"></i> {{ $attendancePercentage ?? '0' }}% Present
                    </div>
                </div>

                <!-- Contact Details from DB -->
                <div class="mt-4 text-left">
                    <p class="mb-2"><i class="ti-email text-primary mr-2"></i> {{ $employee->email ?? 'N/A' }}</p>
                    <p class="mb-2"><i class="ti-mobile text-primary mr-2"></i> {{ $employee->mobile ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Overtime Redirect Card -->
            <div class="ot-card shadow-sm">
                <div>
                    <h5 class="text-dark font-weight-bold mb-1"><i class="ti-time text-warning mr-1"></i> Over Time Data</h5>
                    <p class="text-muted text-sm mb-0">Extra hours worked.</p>
                </div>
                <a href="/overtime?employee_id={{ $employee->id }}" class="btn btn-warning font-weight-bold shadow-sm">View OT</a>
            </div>
        </div>

        <!-- Right Side: Monthly Logs Dropdown (Accordion) fetched from DB -->
        <div class="col-lg-8 col-md-7">
            <div class="accordion month-accordion" id="attendanceAccordion">
                
                <!-- Controller should pass grouped logs (e.g., $monthlyLogs) -->
                @if(isset($monthlyLogs) && count($monthlyLogs) > 0)
                    @foreach($monthlyLogs as $month => $logs)
                        <div class="card">
                            <div class="card-header {{ $loop->first ? '' : 'collapsed' }}" id="heading{{ $loop->index }}" data-toggle="collapse" data-target="#collapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <h5>{{ $month }} <i class="ti-angle-down"></i></h5>
                            </div>
                            <div id="collapse{{ $loop->index }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#attendanceAccordion">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Net Hours</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($logs as $log)
                                                    <tr>
                                                        <td class="font-weight-bold">{{ \Carbon\Carbon::parse($log->date)->format('d M, D') }}</td>
                                                        <td>{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '-' }}</td>
                                                        <td>{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '-' }}</td>
                                                        <td class="font-weight-bold {{ $log->net_hours == '0h 0m' ? 'text-danger' : 'text-primary' }}">{{ $log->net_hours ?? '0h 0m' }}</td>
                                                        <td>
                                                            @if($log->status == 'Present')
                                                                <span class="badge badge-success px-2 py-1">Present</span>
                                                            @elseif($log->status == 'In Progress')
                                                                <span class="badge badge-warning px-2 py-1 text-dark">In Progress</span>
                                                            @elseif($log->status == 'Break Time')
                                                                <span class="badge px-2 py-1 text-white" style="background-color: #3b82f6;">Break Time</span>
                                                            @else
                                                                <span class="badge badge-danger px-2 py-1">Absent</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">
                        No attendance records found for this employee.
                    </div>
                @endif

            </div>
        </div>
        
    <!-- LOGIC: DEFAULT STATE (When page is opened directly without search) -->
    @else
        <div class="col-12">
            <div class="not-found-card mx-auto" style="max-width: 600px; border-color: #e2e8f0;">
                <i class="ti-search text-primary" style="font-size: 60px; margin-bottom: 20px; display: inline-block;"></i>
                <h3 class="text-dark font-weight-bold">Search for an Employee</h3>
                <p class="text-muted mt-2" style="font-size: 15px;">
                    Enter an Employee Name or ID in the search box above to view their full attendance logs and reports.
                </p>
            </div>
        </div>
    @endif

</div>
@endsection