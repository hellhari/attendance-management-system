@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Attendance Dashboard</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Attendance</li>
    </ol>
</div>
@endsection

@section('button')
<a href="attendance/assign" class="btn btn-primary btn-sm btn-flat">
    <i class="mdi mdi-plus mr-2"></i>Add New
</a>
@endsection

@section('content')
@include('includes.flash')

<!-- 🔥 LIVE STATS -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card p-3">
            <h5>Total</h5>
            <h3 id="totalCount">0</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Today</h5>
            <h3 id="todayCount">0</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="table-rep-plugin">
                    <div class="table-responsive mb-0" data-pattern="priority-columns">

                        <table id="datatable-buttons"
                            class="table table-striped table-bordered dt-responsive nowrap"
                            style="width:100%;">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                </tr>
                            </thead>

                            <tbody id="attendanceBody">

                                @foreach ($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->attendance_date }}</td>
                                    <td>{{ $attendance->emp_id }}</td>

                                    <!-- SAFE NULL HANDLING -->
                                    <td>
                                        {{ optional($attendance->employee)->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $attendance->attendance_time }}

                                        @if ($attendance->status == 1)
                                            <span class="badge badge-success float-right">On Time</span>
                                        @else
                                            <span class="badge badge-danger float-right">Late</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ optional($attendance->employee->schedules->first())->time_in ?? '-' }}
                                    </td>

                                    <td>
                                        {{ optional($attendance->employee->schedules->first())->time_out ?? '-' }}
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

{{-- ========================= --}}
{{-- 🔥 LIVE AUTO REFRESH JS --}}
{{-- ========================= --}}
@section('script')

<script src="{{ URL::asset('plugins/RWD-Table-Patterns/dist/js/rwd-table.min.js') }}"></script>

<script>
$(function () {
    $('.table-responsive').responsiveTable({
        addDisplayAllBtn: 'btn btn-secondary'
    });

    loadLiveData();
    setInterval(loadLiveData, 5000); // refresh every 5 sec
});

function loadLiveData() {
    fetch('/attendance/live-data')
        .then(res => res.json())
        .then(data => {

            // update stats
            document.getElementById('totalCount').innerText = data.total;
            document.getElementById('todayCount').innerText = data.today;

        })
        .catch(err => console.log(err));
}
</script>

@endsection