<!DOCTYPE html>
<html>
<head>
    <title>Attendance Dashboard</title>
</head>
<body>

<h2>📊 Attendance Dashboard</h2>

<table border="1" width="100%">
    <tr>
        <th>Employee ID</th>
        <th>Date</th>
        <th>Check In</th>
        <th>Check Out</th>
        <th>Status</th>
    </tr>

    @foreach(\App\Models\Attendance::orderBy('id','desc')->get() as $a)
    <tr>
        <td>{{ $a->emp_id }}</td>
        <td>{{ $a->attendance_date }}</td>
        <td>{{ $a->check_in_time ?? $a->attendance_time }}</td>
        <td>{{ $a->check_out_time ?? '-' }}</td>
        <td>
            @if($a->check_out_time)
                Completed
            @else
                Present
            @endif
        </td>
    </tr>
    @endforeach

</table>

</body>
</html>