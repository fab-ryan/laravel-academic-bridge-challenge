<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EmployeeController extends ApiController
{
    #[OA\Get(
        path: '/v1/employees',
        summary: 'List all employees',
        security: [['bearerAuth' => []]],
        tags: ['Employees'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Search by name, email, or employee identifier',
                required: false,
                schema: new OA\Schema(type: 'string')
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
                description: 'List of employees',
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
                                    items: new OA\Items(ref: '#/components/schemas/Employee')
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
        $query = Employee::query();

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('names', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_identifier', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(request('per_page', 15));

        return $this->successResponse($employees);
    }

    #[OA\Post(
        path: '/v1/employees',
        summary: 'Create a new employee',
        security: [['bearerAuth' => []]],
        tags: ['Employees'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['names', 'email', 'employee_identifier', 'phone_number'],
                properties: [
                    new OA\Property(property: 'names', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@company.com'),
                    new OA\Property(property: 'phone_number', type: 'string', example: '+250788123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Employee created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Employee created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Employee'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['employee_identifier'] = $this->generateUniqueEmployeeIdentifier();
            $employee = Employee::create($data);

            return $this->createdResponse($employee, 'Employee created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create employee',
                self::HTTP_INTERNAL_SERVER_ERROR,
                ['error' => $e->getMessage()]
            );
        }
    }

    #[OA\Get(
        path: '/v1/employees/{id}',
        summary: 'Get a specific employee',
        security: [['bearerAuth' => []]],
        tags: ['Employees'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Employee ID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Employee'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Employee not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Employee $employee): JsonResponse
    {
        return $this->successResponse($employee);
    }

    #[OA\Put(
        path: '/v1/employees/{id}',
        summary: 'Update an employee',
        security: [['bearerAuth' => []]],
        tags: ['Employees'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Employee ID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'names', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@company.com'),
                    new OA\Property(property: 'employee_identifier', type: 'string', example: 'EMP001'),
                    new OA\Property(property: 'phone_number', type: 'string', example: '+250788123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Employee updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Employee'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Employee not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return $this->successResponse($employee, 'Employee updated successfully');
    }

    #[OA\Delete(
        path: '/v1/employees/{id}',
        summary: 'Delete an employee',
        security: [['bearerAuth' => []]],
        tags: ['Employees'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Employee ID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Employee deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Employee not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return $this->successResponse(null, 'Employee deleted successfully');
    }

    private function generateUniqueEmployeeIdentifier(): string
    {
        $employeeIdentifier = 'EMP001';
        $latestEmployee = Employee::latest()->first();
        if ($latestEmployee) {
            $latestIdentifier = $latestEmployee->employee_identifier;
            if (preg_match('/EMP(\d{3})/', $latestIdentifier, $matches)) {
                $number = (int)$matches[1] + 1;
                $employeeIdentifier = 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
                return $employeeIdentifier;
            }
        } else {
            return $employeeIdentifier;
        }
        return $employeeIdentifier;
    }
}
