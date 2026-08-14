@extends('layouts.master')

@section('css')
<style>
    /* Pragnaware Card Standard - HARDCODED SAFE COLORS */
    .pragnaware-stat-card {
        background-color: #ffffff !important;
        border-radius: 28px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
        padding: 20px !important; 
        color: #334155 !important;
        transition: transform 0.3s ease;
        border: none !important;
        overflow: hidden; 
    }

    .pragnaware-stat-card:hover {
        transform: translateY(-8px);
    }
    
    /* Make the icons use the brand gradient */
    .pragnaware-stat-card i, .pragnaware-stat-card span[class^="ti-"] {
        background: linear-gradient(135deg, #0EA5E9, #22C55E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Allow titles to wrap naturally so you see the FULL text */
    .pragnaware-stat-card h5, .pragnaware-stat-card h6 {
        white-space: normal !important; 
        font-size: 13px !important; 
        line-height: 1.4 !important; 
        min-height: 36px; 
    }

    /* Fix the 'More info' footer alignment and put the arrow on the right */
    .pragnaware-stat-card .pt-2 {
        display: flex;
        justify-content: space-between; 
        align-items: center;
        width: 100%;
        flex-direction: row-reverse; 
        padding-top: 15px !important;
        margin-top: 10px;
    }
    
    .pragnaware-stat-card .pt-2 .float-right {
        float: none !important; 
    }
</style>
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title text-dark font-weight-bold">Dashboard</h4>
     <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="/" class="text-primary font-weight-bold">Home</a></li>
         <li class="breadcrumb-item active text-dark">Overview</li>
     </ol>
</div>
@endsection

@section('content')
<div class="row mt-3">
    <!-- METRIC 1: TOTAL PRESENT TODAY -->
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat pragnaware-stat-card" onclick="window.location.href='/attendance';" style="cursor: pointer;">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-left mini-stat-img mr-4">
                        <span class="ti-id-badge" style="font-size: 20px"></span>
                    </div>
                    <h5 class="font-16 text-uppercase mt-0 text-muted">Total Present</h5>
                    <h4 class="font-500 text-primary">{{ isset($totalPresent) ? $totalPresent : 0 }} </h4>
                </div>
                <div class="pt-2 border-top">
                    <div class="float-right">
                        <a href="/attendance" class="text-muted"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>
                    <p class="text-muted mb-0">View Attendance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- METRIC 2: LATE ARRIVALS -->
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat pragnaware-stat-card" onclick="window.location.href='/latetime';" style="cursor: pointer;">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-left mini-stat-img mr-4">
                        <i class="ti-alert" style="font-size: 20px; background: linear-gradient(135deg, #EF4444, #F97316); -webkit-background-clip: text;"></i>
                    </div>
                    <h5 class="font-16 text-uppercase mt-0 text-muted">Late Arrivals</h5>
                    <h4 class="font-500 text-danger">{{ isset($lateArrivalsCount) ? $lateArrivalsCount : 0 }}</h4>
                </div>
                <div class="pt-2 border-top">
                    <div class="float-right">
                        <a href="/latetime" class="text-muted"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>
                    <p class="text-muted mb-0">View Late Logs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- METRIC 3: CURRENTLY ON BREAK -->
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat pragnaware-stat-card" onclick="window.location.href='/attendance';" style="cursor: pointer;">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-left mini-stat-img mr-4">
                        <i class="ti-alarm-clock" style="font-size: 20px; background: linear-gradient(135deg, #F59E0B, #EAB308); -webkit-background-clip: text;"></i>
                    </div>
                    <h5 class="font-16 text-uppercase mt-0 text-muted">Currently on Break</h5>
                    <h4 class="font-500 text-warning">{{ isset($currentlyOnBreak) ? $currentlyOnBreak : 0 }}</h4>
                </div>
                <div class="pt-2 border-top">
                    <div class="float-right">
                        <a href="/attendance" class="text-muted"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>
                    <p class="text-muted mb-0">View Status</p>
                </div>
            </div>
        </div>
    </div>

    <!-- METRIC 4: AVERAGE ON-TIME % -->
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat pragnaware-stat-card" style="cursor: default;">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-left mini-stat-img mr-4">
                        <i class="ti-check-box" style="font-size: 20px"></i>
                    </div>
                    <h5 class="font-16 text-uppercase mt-0 text-muted">Avg. On-Time</h5>
                    <h4 class="font-500 text-success">{{ isset($avgOnTime) ? $avgOnTime : 100 }}%</h4>
                </div>
                <div class="pt-2 border-top">
                    <div class="float-right">
                        <a href="#" class="text-muted"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>
                    <p class="text-muted mb-0">Today's Ratio</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-5 font-weight-bold">Monthly Report</h4>
                <div class="row">
                    <div class="col-lg-7">
                        <div>
                            <div id="chart-with-area" class="ct-chart earning ct-golden-section"></div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4">Avg. On-Time %</p>
                                    <h4>{{ isset($avgOnTime) ? $avgOnTime : 100 }}%</h4>
                                    <span class="peity-donut" data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">{{ isset($avgOnTime) ? $avgOnTime : 100 }}/100</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4">Total Overtime Hrs</p>
                                    <h4>0</h4>
                                    <span class="peity-donut" data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">0/100</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div>
                    <h4 class="mt-0 header-title mb-4 font-weight-bold">Daily Overview</h4>
                </div>
                <div class="wid-peity mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div>
                                <p class="text-muted">Present Today</p>
                                <h5 class="mb-4">{{ isset($totalPresent) ? $totalPresent : 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(2, 164, 153,0.3)"],"stroke": ["rgba(2, 164, 153,0.8)"]}' data-height="60">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wid-peity mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div>
                                <p class="text-muted">On Leave</p>
                                <h5 class="mb-4">0</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(2, 164, 153,0.3)"],"stroke": ["rgba(2, 164, 153,0.8)"]}' data-height="60">6,2,8,4,-3,8,1,-3,6,-5,9,2,-8,1,4,8,9,8,2,1</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="">
                    <div class="row">
                        <div class="col-md-6">
                            <div>
                                <p class="text-muted">Late Arrivals</p>
                                <h5>{{ isset($lateArrivalsCount) ? $lateArrivalsCount : 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(2, 164, 153,0.3)"],"stroke": ["rgba(2, 164, 153,0.8)"]}' data-height="60">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
    <script src="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartist-plugin-tooltips@0.0.17/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peity/3.3.0/jquery.peity.min.js"></script>
    <script src="{{ URL::asset('assets/pages/dashboard.js') }}"></script>
@endsection