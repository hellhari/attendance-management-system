@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Monthly Attendance Sheet</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item">Attendance Records</li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Monthly Sheet</li>
    </ol>
</div>
@endsection

@section('content')

<style>
    .brand-blue-text { color: #0284c7 !important; }
    .brand-green-bg { background-color: #10b981 !important; color: #fff !important; }
    .brand-blue-bg { background-color: #0284c7 !important; color: #fff !important; }
    
    .dept-accordion .card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .dept-accordion .card-header {
        background-color: #ffffff;
        padding: 15px 25px;
        cursor: pointer;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s ease;
    }
    .dept-accordion .card-header:hover {
        background-color: #f8fafc;
    }
    .dept-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .dept-accordion .card-header i.toggle-icon {
        color: #94a3b8;
        transition: transform 0.3s ease;
        font-size: 20px;
    }
    .dept-accordion .card-header.collapsed i.toggle-icon {
        transform: rotate(-90deg);
    }
    
    /* Employee Sub-Card inside Department */
    .emp-card {
        border-bottom: 1px solid #e2e8f0;
        padding: 15px 20px;
        background-color: #fafafa;
    }
    .emp-card:last-child {
        border-bottom: none;
    }
    
    .vertical-table th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 10px 15px;
    }
    .vertical-table td {
        padding: 10px 15px;
        vertical-align: middle;
        font-size: 14px;
    }
    .badge-present { background-color: #d1fae5; color: #059669; border: 1px solid #34d399; }
    .badge-absent { background-color: #fee2e2; color: #e11d48; border: 1px solid #fb7185; }
    .badge-none { background-color: #f1f5f9; color: #64748b; }
    
    /* Percentage Progress Bar */
    .progress-wrapper {
        width: 150px;
        margin-right: 20px;
        text-align: right;
    }
</style>

<div class="row mt-3">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header brand-green-bg d-flex justify-content-between align-items-center" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="mb-0 font-weight-bold"><i class="ti-bar-chart-alt mr-2"></i> Department-wise Monthly Sheet ({{ \Carbon\Carbon::now()->format('F Y') }})</h5>
            </div>
            
            <div class="card-body bg-light p-4">
                
                @php
                    $today = today();
                    $todayStr = $today->format('Y-m-d');
                    $dates = [];
                    // Generate all days of the current month once
                    for ($i = 1; $i <= $today->daysInMonth; ++$i) {
                        $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i);
                    }
                    
                    // GROUP EMPLOYEES BY DEPARTMENT (POSITION)
                    // If position is empty, group them as 'Unassigned'
                    $groupedEmployees = $employees->groupBy(function($emp) {
                        return $emp->position ?: 'Unassigned Role';
                    });
                @endphp

                <!-- Accordion For Departments -->
                <div class="accordion dept-accordion" id="departmentAccordion">
                    
                    @foreach ($groupedEmployees as $departmentName => $deptEmployees)
                        @php
                            // Calculate Today's Percentage for this specific Department
                            $totalInDept = $deptEmployees->count();
                            $presentToday = 0;
                            
                            foreach($deptEmployees as $emp) {
                                // Checking today's attendance for calculation
                                $attdToday = \App\Models\Attendance::query()
                                    ->where('emp_id', $emp->id)
                                    ->where('attendance_date', $todayStr)
                                    ->first();
                                    
                                // Assuming status == 1 or check_out_time exists means present
                                if($attdToday) {
                                    $presentToday++;
                                }
                            }
                            
                            $deptPercentage = $totalInDept > 0 ? round(($presentToday / $totalInDept) * 100) : 0;
                            
                            // Dynamic Color for Progress text
                            $pctColor = $deptPercentage >= 80 ? 'text-success' : ($deptPercentage >= 50 ? 'text-warning' : 'text-danger');
                        @endphp
                        
                        <div class="card">
                            <!-- DEPARTMENT HEADER -->
                            <div class="card-header collapsed" id="headingDept{{ Str::slug($departmentName) }}" data-toggle="collapse" data-target="#collapseDept{{ Str::slug($departmentName) }}" aria-expanded="false">
                                <div class="dept-title">
                                    <i class="ti-briefcase text-primary mr-3" style="font-size: 24px;"></i>
                                    <div>
                                        {{ $departmentName }} 
                                        <div class="text-muted" style="font-size: 13px; font-weight: 500; margin-top: 4px;">
                                            <i class="ti-user mr-1"></i> {{ $totalInDept }} Employees
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <!-- Today's Percentage Indicator -->
                                    <div class="progress-wrapper">
                                        <div class="font-weight-bold {{ $pctColor }}" style="font-size: 16px;">
                                            {{ $deptPercentage }}% Present Today
                                        </div>
                                        <div class="progress mt-1" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar {{ $deptPercentage >= 80 ? 'bg-success' : ($deptPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $deptPercentage }}%" aria-valuenow="{{ $deptPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <i class="ti-angle-down toggle-icon ml-3"></i>
                                </div>
                            </div>

                            <!-- DEPARTMENT EMPLOYEES LIST -->
                            <div id="collapseDept{{ Str::slug($departmentName) }}" class="collapse" aria-labelledby="headingDept{{ Str::slug($departmentName) }}" data-parent="#departmentAccordion">
                                <div class="card-body p-0">
                                    
                                    <!-- Inner Accordion for Employees -->
                                    <div class="accordion" id="empAccordion{{ Str::slug($departmentName) }}">
                                        @foreach ($deptEmployees as $empIndex => $employee)
                                            <div class="emp-card">
                                                <div class="d-flex justify-content-between align-items-center" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseEmp{{ $employee->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm mr-3">
                                                            <span class="avatar-title rounded-circle brand-blue-bg font-weight-bold" style="width: 35px; height: 35px; font-size: 14px;">
                                                                {{ substr($employee->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold brand-blue-text">{{ $employee->name }}</h6>
                                                            <span class="text-muted" style="font-size: 12px;">ID: #{{ $employee->id }}</span>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View 31-Day Log</button>
                                                </div>
                                                
                                                <!-- Employee's 31-Day Vertical Table -->
                                                <div id="collapseEmp{{ $employee->id }}" class="collapse mt-3" data-parent="#empAccordion{{ Str::slug($departmentName) }}">
                                                    <div class="table-responsive border rounded bg-white">
                                                        <table class="table table-hover vertical-table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th width="30%">Date</th>
                                                                    <th width="20%">Day</th>
                                                                    <th width="25%" class="text-center">Attendance</th>
                                                                    <th width="25%" class="text-center">Leave Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($dates as $dateObj)
                                                                    @php
                                                                        $date_picker = $dateObj->format('Y-m-d');
                                                                        
                                                                        $check_attd = \App\Models\Attendance::query()
                                                                            ->where('emp_id', $employee->id)
                                                                            ->where('attendance_date', $date_picker)
                                                                            ->first();
                                                                        
                                                                        $check_leave = \App\Models\Leave::query()
                                                                            ->where('emp_id', $employee->id)
                                                                            ->where('leave_date', $date_picker)
                                                                            ->first();
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="font-weight-bold text-dark">{{ $dateObj->format('d M Y') }}</td>
                                                                        <td class="text-muted">{{ $dateObj->format('l') }}</td>
                                                                        
                                                                        <td class="text-center">
                                                                            @if (isset($check_attd))
                                                                                <span class="badge badge-present px-3 py-1"><i class="fa fa-check mr-1"></i> Present</span>
                                                                            @else
                                                                                <span class="badge badge-absent px-3 py-1"><i class="fas fa-times mr-1"></i> Absent</span>
                                                                            @endif
                                                                        </td>

                                                                        <td class="text-center">
                                                                            @if (isset($check_leave))
                                                                                @if ($check_leave->status == 1)
                                                                                    <span class="badge badge-present px-3 py-1"><i class="fa fa-check mr-1"></i> Approved</span>
                                                                                @else
                                                                                    <span class="badge badge-absent px-3 py-1"><i class="fas fa-times mr-1"></i> Denied</span>
                                                                                @endif
                                                                            @else
                                                                                <span class="badge badge-none px-3 py-1">-</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- End Inner Accordion -->

                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <!-- End Main Accordion -->

            </div>
        </div>
    </div>
</div>

@endsection