<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Academic Bridge Challenge API',
    version: '1.0.0',
    description: 'RESTful API for Employee Attendance Management System. This API provides endpoints for user authentication, employee management, attendance tracking, and report generation.',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@academicbridge.com'
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'http://localhost:8000/api/v1',
    description: 'Local Development Server (Versioned)'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Bearer token in the format: Bearer {token}'
)]
#[OA\Tag(name: 'Authentication', description: 'User authentication endpoints')]
#[OA\Tag(name: 'Employees', description: 'Employee management endpoints')]
#[OA\Tag(name: 'Attendance', description: 'Attendance tracking endpoints')]
#[OA\Tag(name: 'Reports', description: 'Report generation endpoints')]
abstract class ApiController extends Controller
{
    /**
     * HTTP Status Code Constants
     */
    protected const HTTP_OK = 200;

    protected const HTTP_CREATED = 201;

    protected const HTTP_NO_CONTENT = 204;

    protected const HTTP_BAD_REQUEST = 400;

    protected const HTTP_UNAUTHORIZED = 401;

    protected const HTTP_FORBIDDEN = 403;

    protected const HTTP_NOT_FOUND = 404;

    protected const HTTP_UNPROCESSABLE_ENTITY = 422;

    protected const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data  The response data
     * @param  string  $message  The success message
     * @param  int  $statusCode  The HTTP status code
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Request was successful.',
        int $statusCode = self::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message  The error message
     * @param  int  $statusCode  The HTTP status code
     * @param  array<string, mixed>  $errors  Additional error details
     */
    protected function errorResponse(
        string $message = 'An error occurred.',
        int $statusCode = self::HTTP_INTERNAL_SERVER_ERROR,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a created resource response.
     *
     * @param  mixed  $data  The created resource data
     * @param  string  $message  The success message
     */
    protected function createdResponse(
        mixed $data,
        string $message = 'Resource created successfully.'
    ): JsonResponse {
        return $this->successResponse($data, $message, self::HTTP_CREATED);
    }

    /**
     * Return a no content response.
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, self::HTTP_NO_CONTENT);
    }
}
