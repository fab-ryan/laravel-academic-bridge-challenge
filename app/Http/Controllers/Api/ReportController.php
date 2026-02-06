<?php

namespace App\Http\Controllers\Api;

use App\Exports\AttendanceExport;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends ApiController
{
    #[OA\Get(
        path: '/v1/reports/attendance/pdf',
        summary: 'Generate attendance report in PDF format',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(
                name: 'date',
                in: 'query',
                description: 'Report date (Y-m-d), defaults to today',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                description: 'Report start date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                description: 'Report end date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF file download',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function attendancePdf(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->date) {
            $query->whereDate('date', $request->date);
            $reportTitle = 'Attendance Report for '.$request->date;
        } elseif ($request->from_date && $request->to_date) {
            $query->whereDate('date', '>=', $request->from_date)
                ->whereDate('date', '<=', $request->to_date);
            $reportTitle = 'Attendance Report from '.$request->from_date.' to '.$request->to_date;
        } else {
            $query->whereDate('date', now()->toDateString());
            $reportTitle = 'Attendance Report for '.now()->toDateString();
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('arrival_time', 'asc')
            ->get();

        $pdf = Pdf::loadView('reports.attendance-pdf', [
            'attendances' => $attendances,
            'reportTitle' => $reportTitle,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'attendance-report-'.now()->format('Y-m-d-His').'.pdf';

        return $pdf->download($filename);
    }

    #[OA\Get(
        path: '/v1/reports/attendance/excel',
        summary: 'Generate attendance report in Excel format',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(
                name: 'date',
                in: 'query',
                description: 'Report date (Y-m-d), defaults to today',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                description: 'Report start date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                description: 'Report end date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Excel file download',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function attendanceExcel(Request $request): BinaryFileResponse
    {
        $date = $request->date;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $filename = 'attendance-report-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(
            new AttendanceExport($date, $fromDate, $toDate),
            $filename
        );
    }
}
