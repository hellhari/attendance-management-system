@extends('layouts.master')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="mt-0 header-title mb-4">Daily Attendance Overview</h4>
        
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm text-center align-middle">
                <thead class="thead-light">
                    <tr>
                        <th class="text-left">Employee ID</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Total Break Time</th>
                        <th>Net Work Hours</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        @php
                            // --- THE DYNAMIC MATH ---
                            $cleanDate = \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d');
                            $timeIn = \Carbon\Carbon::parse($cleanDate . ' ' . $attendance->attendance_time);
                            
                            // 1. Calculate Gross Minutes with 16-Hour Safety Cap
                            if ($attendance->check_out_time) {
                                $timeOut = \Carbon\Carbon::parse($cleanDate . ' ' . $attendance->check_out_time);
                                $grossMinutes = $timeIn->diffInMinutes($timeOut);
                                $statusBadge = 'Completed';
                            } else {
                                $timeOut = null;
                                $grossMinutes = $timeIn->diffInMinutes(now());
                                
                                // HR Safety Cap: Stop counting at 16 hours (960 minutes)
                                if ($grossMinutes > 960) {
                                    $grossMinutes = 960; 
                                    $statusBadge = 'Missed Checkout';
                                } else {
                                    $statusBadge = 'Active';
                                }
                            }
                            
                            // 2. Calculate Break Time from Timestamps
                            $breakLogs = \App\Models\BreakLog::where('attendance_id', $attendance->id)->get();
                            $totalBreakMinutes = 0;
                            foreach($breakLogs as $bLog) {
                                if ($bLog->break_end) {
                                    $bStart = \Carbon\Carbon::parse($bLog->break_start);
                                    $bEnd = \Carbon\Carbon::parse($bLog->break_end);
                                    $totalBreakMinutes += $bStart->diffInMinutes($bEnd);
                                }
                            }
                            
                            // 3. Net Hours & Overtime
                            $netWorkingMinutes = $grossMinutes - $totalBreakMinutes;
                            if ($netWorkingMinutes < 0) $netWorkingMinutes = 0;
                            
                            $netHours = floor($netWorkingMinutes / 60);
                            $netMins = $netWorkingMinutes % 60;
                            
                            $standardShift = 480; // 8 hours
                            $overtimeMinutes = $netWorkingMinutes > $standardShift ? $netWorkingMinutes - $standardShift : 0;
                            $otHours = floor($overtimeMinutes / 60);
                            $otMins = $overtimeMinutes % 60;
                        @endphp
                        
                        <tr>
                            <td class="text-left font-weight-bold">{{ $attendance->emp_id }}</td>
                            <td>{{ $timeIn->format('h:i A') }}</td>
                            <td>{{ $timeOut ? $timeOut->format('h:i A') : 'Still Working' }}</td>
                            <td>
                                @if($statusBadge == 'Completed')
                                    <span class="badge bg-primary">Completed</span>
                                @elseif($statusBadge == 'Missed Checkout')
                                    <span class="badge bg-danger">Missed Checkout</span>
                                @else
                                    <span class="badge bg-warning">Active</span>
                                @endif
                            </td>
                            
                            <td class="align-middle text-center">
                                <span class="{{ $totalBreakMinutes > 60 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                    {{ $totalBreakMinutes }} mins
                                </span>
                                <br>
                                @if($totalBreakMinutes > 0)
                                    <button class="btn btn-sm btn-outline-secondary mt-1" data-toggle="modal" data-target="#breakModal{{ $attendance->id }}">
                                        View Details
                                    </button>
                                @endif
                            </td>

                            <td class="align-middle fw-bold text-primary">
                                {{ $netHours }}h {{ $netMins }}m
                            </td>

                            <td class="align-middle">
                                @if($overtimeMinutes > 0)
                                    <span class="badge bg-success">+{{ $otHours }}h {{ $otMins }}m</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($attendances as $attendance)
    @php
        $modalBreakLogs = \App\Models\BreakLog::where('attendance_id', $attendance->id)->get();
    @endphp
    
    @if($modalBreakLogs->count() > 0)
        <div class="modal fade" id="breakModal{{ $attendance->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Break Audit Trail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-sm text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th>Left Workspace</th>
                                    <th>Returned</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modalBreakLogs as $break)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($break->break_start)->format('h:i A') }}</td>
                                    <td>{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('h:i A') : 'Still Outside' }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ $break->break_end ? \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end)) : '...' }} mins
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection