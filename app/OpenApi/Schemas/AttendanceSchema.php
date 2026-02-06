<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

use function Laravel\Prompts\form;

#[OA\Schema(
    schema: 'Attendance',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'employee_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2024-01-15'),
        new OA\Property(property: 'arrival_time', type: 'string', format: 'date-time', example: '2024-01-15T08:00:00'),
        new OA\Property(property: 'departure_time', type: 'string', format: 'date-time', nullable: true, example: '2024-01-15T17:00:00'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'employee', ref: '#/components/schemas/Employee', nullable: true),
    ]
)]
class AttendanceSchema {}
