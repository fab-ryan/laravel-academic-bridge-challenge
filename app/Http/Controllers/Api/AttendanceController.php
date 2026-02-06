<?php

namespace App\Http\Controllers\Api;

use App\Enums\AttendanceType;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Notifications\AttendanceRecordedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class AttendanceController extends ApiController
{
    #[OA\Get(
        path: '/v1/attendances',
        summary: 'List all attendance records',
        security: [['bearerAuth' => []]],
        tags: ['Attendance'],
        parameters: [
            new OA\Parameter(
                name: 'employee_id',
                in: 'query',
                description: 'Filter by employee ID',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'date',
                in: 'query',
                description: 'Filter by specific date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                description: 'Filter from date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                description: 'Filter to date (Y-m-d)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Number of items per page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of attendance records',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/Attendance')
                                ),
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        request()->validate([
            'employee_id' => ['sometimes', 'string', 'uuid', 'exists:employees,id'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'from_date' => ['sometimes', 'date_format:Y-m-d'],
            'to_date' => ['sometimes', 'date_format:Y-m-d'],
            'per_page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $request = request();
        $query = Attendance::with('employee');

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('arrival_time', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($attendances);
    }

    #[OA\Post(
        path: '/v1/attendances/check-in',
        summary: 'Record employee arrival (check-in)',
        security: [['bearerAuth' => []]],
        tags: ['Attendance'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employee_id'],
                properties: [
                    new OA\Property(property: 'employee_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Check-in recorded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Check-in recorded successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Attendance'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Already checked in today'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->employee_id);
        $today = now()->toDateString();

        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance) {
            return $this->errorResponse('Employee has already checked in today', self::HTTP_BAD_REQUEST);
        }

        $attendance = DB::transaction(function () use ($employee, $today) {
            
            return Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'arrival_time' => now(),
            ]);
        });

        $attendance->load('employee');

        // Send notification via queue
        $employee->notify(new AttendanceRecordedNotification($attendance, AttendanceType::CHECK_IN->value));

        return $this->createdResponse($attendance, 'Check-in recorded successfully');
    }

    #[OA\Post(
        path: '/v1/attendances/check-out',
        summary: 'Record employee departure (check-out)',
        security: [['bearerAuth' => []]],
        tags: ['Attendance'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employee_id'],
                properties: [
                    new OA\Property(property: 'employee_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Check-out recorded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Check-out recorded successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Attendance'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No check-in record found for today'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->employee_id);
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return $this->errorResponse('No check-in record found for today', self::HTTP_BAD_REQUEST);
        }

        if ($attendance->hasCheckedOut()) {
            return $this->errorResponse('Employee has already checked out today', self::HTTP_BAD_REQUEST);
        }

        $attendance->update([
            'departure_time' => now(),
        ]);

        $attendance->load('employee');

        // Send notification via queue
        $employee->notify(new AttendanceRecordedNotification($attendance, AttendanceType::CHECK_OUT->value));

        return $this->successResponse($attendance, 'Check-out recorded successfully');
    }

    #[OA\Get(
        path: '/v1/attendances/{id}',
        summary: 'Get a specific attendance record',
        security: [['bearerAuth' => []]],
        tags: ['Attendance'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Attendance ID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Attendance record details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Attendance'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Attendance record not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Attendance $attendance): JsonResponse
    {
        $attendance->load('employee');

        return $this->successResponse($attendance);
    }

    #[OA\Get(
        path: '/v1/attendances/employee/{employee}/today',
        summary: 'Get today\'s attendance for an employee',
        security: [['bearerAuth' => []]],
        tags: ['Attendance'],
        parameters: [
            new OA\Parameter(
                name: 'employee',
                in: 'path',
                description: 'Employee ID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Today\'s attendance record',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Attendance', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Employee not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function todayAttendance(Employee $employee): JsonResponse
    {
        if (!$employee) {
            return $this->errorResponse('Employee not found', 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();

        return $this->successResponse($attendance);
    }
}
