@extends('layouts.master')

@php
    $currentRange = request('range', 'all');
    $rangeLabels = [
        'all' => 'All Records',
        '1h'  => 'Last 1 Hour',
        '24h' => 'Last 24 Hours',
        '7d'  => 'Last 7 Days',
        '30d' => 'Last 30 Days',
        '6m'  => 'Last 6 Months',
        '12m' => 'Last 12 Months'
    ];
    $activeLabel = $rangeLabels[$currentRange] ?? 'All Records';
@endphp

@section('content')
<style>
    /* Professional Table Header Styling */
    #visitorTable thead th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        vertical-align: middle !important;
        border-bottom: 2px solid #dee2e6;
        padding: 15px 10px !important;
    }

    /* Professional Row Styling */
    #visitorTable tbody td {
        font-size: 13px !important;
        vertical-align: middle !important;
        height: 60px !important;
        padding: 0 15px !important;
        white-space: nowrap;
        color: #444;
    }

    /* Badge and Button sizes */
    #visitorTable tbody td span, 
    #visitorTable tbody td button {
        font-size: 11px !important;
        font-weight: 600;
    }

    /* Button specific styling */
    #visitorTable .btn-sm {
        padding: 5px 12px !important;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .table-responsive {
        border-radius: 0 0 8px 8px;
    }

    /* Row highlighting for 'Inside' status */
    #visitorTable tbody tr:has(.status-inside) {
        background-color: rgba(51, 15, 10, 0.05) !important; 
    }

    #visitorTable tbody tr:has(.status-inside) td:first-child {
        border-left: 5px solid #330F0A !important;
    }

    /* --- GREEN FILTER STYLES --- */
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #4ea749 !important; /* Company Green */
    }
    .dropdown-item.active {
        background-color: #4ea749 !important; /* Company Green */
        color: white !important;
    }
</style>

<div class="row mb-3">
    <div class="col-xl-4 col-md-6">
        <div class="card mini-stat position-relative shadow-sm" style="background-color: #f0f7ff; border-left: 5px solid #1973b8; border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase vertical-label" style="color: #1973b8;">Log</h6>
                    <div class="text-dark">
                        <h6 class="text-uppercase mt-0" style="color: #546e7a;">Total Visitors</h6>
                        <h3 class="mb-3" style="color: #1973b8;">{{ $totalVisitors ?? 0 }}</h3>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="mdi mdi-account-group display-2" style="color: rgba(25, 115, 184, 0.1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card mini-stat position-relative shadow-sm" style="background-color: #f1f8f1; border-left: 5px solid #4ea749; border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase vertical-label" style="color: #4ea749;">Active</h6>
                    <div class="text-dark">
                        <h6 class="text-uppercase mt-0" style="color: #546e7a;">Currently Inside</h6>
                        <h3 class="mb-3" style="color: #4ea749;">{{ $currentlyInside ?? 0 }}</h3>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="mdi mdi-door-open display-2" style="color: rgba(78, 167, 73, 0.1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card mini-stat position-relative shadow-sm" style="background-color: #f9f4f4; border-left: 5px solid #330F0A; border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase vertical-label" style="color: #330F0A;">History</h6>
                    <div class="text-dark">
                        <h6 class="text-uppercase mt-0" style="color: #546e7a;">Total check-outs</h6>
                        <h3 class="mb-3" style="color: #330F0A;">{{ $totalExits ?? 0 }}</h3>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="mdi mdi-account-check display-2" style="color: rgba(51, 15, 10, 0.1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-right">
            <a href="{{ route('visitor.export') }}" class="btn btn-sm shadow-sm px-4 py-2 mr-2" 
               style="background-color: #4ea749; color: white; font-weight: 700; border-radius: 6px; border: none; display: inline-block; vertical-align: middle;">
                <i class="mdi mdi-download mr-1"></i> DOWNLOAD REPORT
            </a>
            <div class="dropdown d-inline-block">
                <button class="btn btn-sm shadow-sm dropdown-toggle px-4 py-2" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                        style="background-color: white; border-left: 5px solid #4ea749; color: #546e7a; font-weight: 700; border-radius: 6px;">
                    <i class="mdi mdi-filter-variant mr-1"></i> FILTER BY: 
                    <span class="ml-1" style="color: #4ea749;">{{ $activeLabel }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mt-2" aria-labelledby="filterDropdown" style="border-radius: 8px; min-width: 220px;">
                    <a class="dropdown-item py-2 {{ $currentRange == 'all' ? 'active' : '' }}" href="?range=all">
                        <i class="mdi mdi-database mr-2"></i> All Records
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item py-2 {{ $currentRange == '1h' ? 'active' : '' }}" href="?range=1h">Last 1 Hour</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '24h' ? 'active' : '' }}" href="?range=24h">Last 24 Hours</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '7d' ? 'active' : '' }}" href="?range=7d">Last 7 Days</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '30d' ? 'active' : '' }}" href="?range=30d">Last 30 Days</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '6m' ? 'active' : '' }}" href="?range=6m">Last 6 Months</a>
                    <a class="dropdown-item py-2 {{ $currentRange == '12m' ? 'active' : '' }}" href="?range=12m">Last 12 Months</a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-dismissible fade show" role="alert" style="background-color: #fdfaf9; color: #330F0A; border: 1px solid rgba(51, 15, 10, 0.2);">
    <strong>Success!</strong> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #330F0A;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card shadow border-0">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #1973b8;">
                <h5 class="mb-0">Visitor Log Book</h5>
                <a href="{{ route('visitor.checkin') }}" class="btn btn-sm btn-light font-weight-bold" style="color: #1973b8;">
                    <i class="mdi mdi-plus-circle mr-1"></i> New Check-In
                </a>
            </div>
            
            <div class="card-body p-0"> 
                <div class="table-responsive"> 
                    <table class="table table-hover text-center mb-0" id="visitorTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Company</th>
                                <th>Person to Meet</th>
                                <th>Purpose</th>
                                <th>ID Type</th>
                                <th>ID Number</th>
                                <th>Check-In Time</th>
                                <th>Check-Out Time</th> 
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visitors as $visitor)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $visitor->name }}</td>
                                <td>{{ $visitor->phone }}</td>
                                <td>{{ $visitor->company }}</td>
                                <td>{{ $visitor->person_to_meet }}</td>
                                <td>{{ $visitor->purpose }}</td>
                                <td>{{ $visitor->id_type }}</td>
                                <td>{{ $visitor->id_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($visitor->check_in_time)->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $visitor->check_out_time ? \Carbon\Carbon::parse($visitor->check_out_time)->format('Y-m-d H:i:s') : '---' }}</td>
                                <td>
                                    <span class="badge badge-pill {{ $visitor->status == 'Inside' ? 'status-inside' : '' }}" 
                                          style="padding: 6px 12px; background-color: {{ $visitor->status == 'Inside' ? '#330F0A' : '#6c757d' }}; color: white;">
                                        {{ $visitor->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($visitor->status == 'Inside')
                                        <form action="{{ route('visitor.checkout', $visitor->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm text-white" style="background-color: #330F0A;">
                                                Check-Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Completed</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div style="color: #4ea749;"> 
                                        <i class="mdi mdi-account-search-outline display-4" style="opacity: 0.5;"></i>
                                        <h5 class="mt-3" style="font-weight: 600;">No Visitors Found</h5>
                                        <p class="text-muted small">There are no records for the selected time range.</p>
                                        <a href="?range=all" class="btn btn-sm text-white mt-2 px-4" 
                                           style="background-color: #4ea749; border: none; border-radius: 4px;">
                                            View All Records
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $(".app-search input, .navbar-search input, #search-input").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#visitorTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endsection