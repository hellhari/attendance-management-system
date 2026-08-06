@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
        
    <style>
        /* Clean vertical headers */
        .vertical-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding: 15px 5px !important;
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('check_store') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-success mb-3">Submit Attendance</button>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center">
                        <thead>
                            <tr>
                                <th class="align-middle">Employee Name</th>
                                <th class="align-middle">Position</th>
                                <th class="align-middle">ID</th>
                                @php
                                    $today = today();
                                    $dates = [];
                                    for ($i = 1; $i < $today->daysInMonth + 1; ++$i) {
                                        $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');
                                    }
                                @endphp
                                
                                @foreach ($dates as $date)
                                    <th class="vertical-header">
                                        {{ \Carbon\Carbon::parse($date)->format('M d') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($employees as $employee)
                                <input type="hidden" name="emp_id" value="{{ $employee->id }}">
                                <tr>
                                    <td class="align-middle text-left" style="white-space: nowrap;">{{ $employee->name }}</td>
                                    <td class="align-middle text-left" style="white-space: nowrap;">{{ $employee->position }}</td>
                                    <td class="align-middle">{{ $employee->id }}</td>

                                    @for ($i = 1; $i < $today->daysInMonth + 1; ++$i)
                                        @php
                                            $date_picker = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');
                                            
                                            $check_attd = \App\Models\Attendance::query()
                                                ->where('emp_id', $employee->id)
                                                ->where('attendance_date', $date_picker)
                                                ->first();
                                            
                                            $check_leave = \App\Models\Leave::query()
                                                ->where('emp_id', $employee->id)
                                                ->where('leave_date', $date_picker)
                                                ->first();
                                        @endphp
                                        
                                        <td class="align-middle" style="min-width: 60px;">
                                            <div class="form-check form-check-inline m-0" title="Attendance">
                                                <input class="form-check-input position-static" name="attd[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                                    @if (isset($check_attd)) checked @endif value="1">
                                            </div>
                                            <div class="form-check form-check-inline m-0" title="Leave">
                                                <input class="form-check-input position-static" name="leave[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                                    @if (isset($check_leave)) checked @endif value="1">
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
@endsection