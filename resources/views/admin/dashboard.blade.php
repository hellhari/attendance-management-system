@extends('layouts.master')

@section('content')
<div class="row mb-4">
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="all" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <p class="text-muted text-uppercase fw-bold mb-2">Total Present</p>
                <h2 class="mb-0 text-primary" id="metric-present">{{ $totalPresent }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="late" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <p class="text-muted text-uppercase fw-bold mb-2">Late Arrivals</p>
                <h2 class="mb-0 text-danger" id="metric-late">{{ $lateArrivals }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="break" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <p class="text-muted text-uppercase fw-bold mb-2">Currently on Break</p>
                <h2 class="mb-0 text-warning" id="metric-break">{{ $onBreak }}</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100 filter-card" data-filter="ontime" style="border-radius: 20px; cursor: pointer; transition: 0.2s;">
            <div class="card-body text-center py-4">
                <p class="text-muted text-uppercase fw-bold mb-2">Avg On-Time</p>
                <h2 class="mb-0 text-success" id="metric-avg-time">{{ $onTimePercentage }}%</h2>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 20px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="mb-0 text-dark" id="table-title">Live Daily Activity: All Present</h5>
            </div>
            <div class="card-body" id="layer-two-content">
                
                @if($todayLogs->isEmpty())
                    <p class="text-muted text-center py-5">No attendance records found for today.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Check In</th>
                                    <th>Check Out</th> 
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayLogs as $log)
                                    @php
                                        // Determine the category for the Javascript filter
                                        $isLate = $log->status == '0';
                                        $isOnBreak = \App\Models\BreakLog::where('attendance_id', $log->id)->whereNull('break_end')->exists();
                                        
                                        $category = 'ontime'; 
                                        if ($isOnBreak) $category = 'break';
                                        elseif ($isLate) $category = 'late';
                                    @endphp
                                    
                                    <tr class="activity-row" data-category="{{ $category }}">
                                        <td class="fw-bold">{{ $log->emp_id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->attendance_time)->format('h:i A') }}</td>
                                        
                                        <td>
                                            @if($log->check_out_time)
                                                {{ \Carbon\Carbon::parse($log->check_out_time)->format('h:i A') }}
                                            @else
                                                <span class="text-muted fst-italic">Still Working</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            @if($category == 'break')
                                                <span class="badge bg-warning">On Break</span>
                                            @elseif($category == 'late')
                                                <span class="badge bg-danger">Late</span>
                                            @else
                                                <span class="badge bg-success">On Time</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const cards = document.querySelectorAll('.filter-card');
        const rows = document.querySelectorAll('.activity-row');
        const tableTitle = document.getElementById('table-title');

        cards.forEach(card => {
            card.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                if(filter === 'all') tableTitle.innerText = "Live Daily Activity: All Present";
                if(filter === 'late') tableTitle.innerText = "Live Daily Activity: Late Arrivals";
                if(filter === 'break') tableTitle.innerText = "Live Daily Activity: Currently on Break";
                if(filter === 'ontime') tableTitle.innerText = "Live Daily Activity: On-Time Staff";

                rows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-category') === filter) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                });
            });
        });
    });
</script>
@endsection