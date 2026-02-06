<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $date = null,
        protected ?string $fromDate = null,
        protected ?string $toDate = null
    ) {}

    public function collection()
    {
        $query = Attendance::with('employee');

        if ($this->date) {
            $query->whereDate('date', $this->date);
        } elseif ($this->fromDate && $this->toDate) {
            $query->whereDate('date', '>=', $this->fromDate)
                ->whereDate('date', '<=', $this->toDate);
        } else {
            $query->whereDate('date', now()->toDateString());
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('arrival_time', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Employee ID',
            'Email',
            'Phone',
            'Date',
            'Arrival Time',
            'Departure Time',
            'Hours Worked',
        ];
    }

    public function map($attendance): array
    {
        $hoursWorked = null;
        if ($attendance->departure_time && $attendance->arrival_time) {
            $diff = $attendance->departure_time->diff($attendance->arrival_time);
            $hoursWorked = $diff->format('%H:%I:%S');
        }

        return [
            $attendance->id,
            $attendance->employee->names,
            $attendance->employee->employee_identifier,
            $attendance->employee->email,
            $attendance->employee->phone_number,
            $attendance->date->format('Y-m-d'),
            $attendance->arrival_time->format('H:i:s'),
            $attendance->departure_time?->format('H:i:s') ?? 'Not checked out',
            $hoursWorked ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Attendance Report'.($this->date ? " - {$this->date}" : '').
            ($this->fromDate && $this->toDate ? " ({$this->fromDate} to {$this->toDate})" : '');
    }
}
