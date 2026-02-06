<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }

        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        .summary h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Records:</strong> {{ $attendances->count() }}</p>
        <p><strong>Employees Present:</strong> {{ $attendances->unique('employee_id')->count() }}</p>
    </div>

    @if ($attendances->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Date</th>
                    <th>Arrival Time</th>
                    <th>Departure Time</th>
                    <th>Hours Worked</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $index => $attendance)
                    @php
                        $hoursWorked = 'N/A';
                        if ($attendance->departure_time && $attendance->arrival_time) {
                            $diff = $attendance->departure_time->diff($attendance->arrival_time);
                            $hoursWorked = $diff->format('%H:%I:%S');
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $attendance->employee->names }}</td>
                        <td>{{ $attendance->employee->employee_identifier }}</td>
                        <td>{{ $attendance->date->format('Y-m-d') }}</td>
                        <td>{{ $attendance->arrival_time->format('H:i:s') }}</td>
                        <td>{{ $attendance->departure_time ? $attendance->departure_time->format('H:i:s') : 'Not checked out' }}
                        </td>
                        <td>{{ $hoursWorked }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No attendance records found for the selected period.</p>
        </div>
    @endif

    <div class="footer">
        <p>Employee Attendance Management System - Confidential Report</p>
    </div>
</body>

</html>
