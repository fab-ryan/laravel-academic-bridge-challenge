<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

use App\Http\Controllers\Controller;

#[OA\Info(
    title: 'Academic Bridge Challenge API',
    version: '1.0.0',
    description: 'API documentation for the Academic Bridge Challenge application.',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@academicbridge.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class ApiController extends Controller
{
    protected function successResponse(mixed $data, string $message = 'Request was successful.', int $statusCode = 200)
    {

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }
    protected function errorResponse(string $message = 'An error occurred.', int $statusCode = 500, array $errors = [])
    {

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }
}
