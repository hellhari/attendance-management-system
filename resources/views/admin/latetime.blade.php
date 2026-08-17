@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
<!-- SweetAlert2 CSS for Beautiful Popup Dialogs -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    /* CLEAN & SOFT TAB COLORS FIX */
    .nav-pills .nav-link { font-weight: 600; color: #64748b; border-radius: 8px; padding: 10px 20px; margin-right: 10px; transition: all 0.3s ease; border: 1px solid transparent; }
    
    /* Active State for Late Arrivals (Light Blue) */
    .nav-pills .late-tab.active { background-color: #e0e7ff !important; color: #4f46e5 !important; border: 1px solid #c7d2fe; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1); }
    
    /* Active State for Missed Punches (Light Red/Pink) */
    .nav-pills .missed-tab.active { background-color: #fee2e2 !important; color: #e11d48 !important; border: 1px solid #fecdd3; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.1); }

    .reason-box { background: #f8fafc; border-left: 3px solid #f59e0b; padding: 8px 12px; font-size: 13px; font-style: italic; border-radius: 4px; color: #333; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left font-weight-bold">Time Exceptions & Resolutions</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
        <li class="breadcrumb-item active">Time Exceptions</li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">

                <!-- TABS HEADER -->
                <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link late-tab active" data-toggle="tab" href="#late-arrivals" role="tab">
                            <i class="ti-timer mr-2"></i> Late Arrivals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link missed-tab text-danger" data-toggle="tab" href="#missed-punches" role="tab">
                            <i class="ti-alert mr-2"></i> Missed Punches / Defense Requests
                        </a>
                    </li>
                </ul>

                <!-- TABS CONTENT -->
                <div class="tab-content">
                    
                    <!-- TAB 1: LATE ARRIVALS -->
                    <div class="tab-pane active" id="late-arrivals" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered dt-responsive nowrap" style="border-collapse: collapse; width: 100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Time In</th>
                                        <th class="text-danger">Late By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($latetimes as $lateRecord)
                                    <tr>
                                        <td class="align-middle">{{ \Carbon\Carbon::parse($lateRecord->attendance_date)->format('M d, Y') }}</td>
                                        <td class="align-middle font-weight-bold text-primary">#{{ $lateRecord->emp_id }}</td>
                                        <td class="align-middle font-weight-bold">{{ optional($lateRecord->employee)->name ?? 'Emp #'.$lateRecord->emp_id }}</td>
                                        <td class="align-middle">{{ $lateRecord->clean_time_in }}</td>
                                        <td class="align-middle text-danger font-weight-bold">
                                            <i class="ti-time mr-1"></i> {{ $lateRecord->formatted_late_time }}
                                        </td>
                                        <td class="align-middle">
                                            <!-- SIMULATED ACTION BUTTONS -->
                                            <button onclick="takeAction('waive', '{{ optional($lateRecord->employee)->name }}')" class="btn btn-sm btn-outline-success rounded-pill px-3 mr-1" title="Waive / Forgive Late">Waive</button>
                                            <button onclick="takeAction('penalty', '{{ optional($lateRecord->employee)->name }}')" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Apply Penalty">Penalty</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="ti-face-smile mb-2" style="font-size: 30px; display: block;"></i>
                                            No late arrivals logged! Everyone is on time.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: MISSED PUNCHES & APPROVALS -->
                    <div class="tab-pane" id="missed-punches" role="tabpanel">
                        <div class="alert alert-warning mb-4 border-0 shadow-sm" style="background-color: #fffbeb; color: #b45309;">
                            <i class="ti-info-alt mr-2"></i> Employees type their defense reasons in their portal. You can review and approve them here to update their attendance logs.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" style="width: 100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee Name</th>
                                        <th>Recorded Time-In</th>
                                        <th>Employee Defense / Reason</th>
                                        <th class="text-center">Approval Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- MOCK DATA -->
                                    <tr>
                                        <td class="align-middle">Aug 14, 2026</td>
                                        <td class="align-middle font-weight-bold text-primary">Hariram (ID: #112)</td>
                                        <td class="align-middle">09:15 AM</td>
                                        <td class="align-middle">
                                            <div class="reason-box">
                                                "I was in the server room deployment meeting from 5 PM to 7 PM and forgot to punch out."
                                            </div>
                                            <span class="badge badge-warning mt-2 text-dark px-2 py-1">Requested Time Out: 07:00 PM</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <button onclick="resolvePunch('approve')" class="btn btn-sm btn-success shadow-sm mr-1"><i class="ti-check mr-1"></i> Approve</button>
                                            <button onclick="resolvePunch('reject')" class="btn btn-sm btn-danger shadow-sm"><i class="ti-close mr-1"></i> Reject</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> <!-- End Tab Content -->

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('plugins/RWD-Table-Patterns/dist/js/rwd-table.min.js') }}"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Tab responsive init
    $(function () {
        $('.table-responsive').responsiveTable({ addDisplayAllBtn: 'btn btn-secondary' });
    });

    // JS Logic for Late Arrival Actions
    function takeAction(type, empName) {
        if(type === 'waive') {
            Swal.fire({
                title: 'Waive Late Arrival?',
                text: "This will ignore the late mark for " + empName + " in the Pay Report.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Yes, Waive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Waived!', 'The late mark has been waived.', 'success');
                }
            });
        } else {
            Swal.fire({
                title: 'Apply Penalty?',
                text: "This will deduct pay for " + empName + " in the Pay Report.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Apply Penalty'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Penalty Applied!', 'Payroll records updated.', 'success');
                }
            });
        }
    }

    // JS Logic for Missed Punch Approval
    function resolvePunch(action) {
        if(action === 'approve') {
            Swal.fire({
                title: 'Approve Request?',
                text: "This will update the attendance log to 07:00 PM and change status to Present.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Approve'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Approved!', 'Attendance log has been updated.', 'success');
                }
            });
        } else {
            Swal.fire({
                title: 'Reject Request?',
                input: 'text',
                inputLabel: 'Reason for Rejection (Optional)',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Reject'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Rejected', 'The request was denied.', 'info');
                }
            });
        }
    }
</script>
@endsection