<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class AuthController extends ApiController
{
    #[OA\Post(
        path: '/v1/auth/register',
        summary: 'Register a new user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->createdResponse([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully');
    }

    #[OA\Post(
        path: '/v1/auth/login',
        summary: 'Login a user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', self::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    #[OA\Post(
        path: '/v1/auth/logout',
        summary: 'Logout the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    #[OA\Post(
        path: '/v1/auth/forgot-password',
        summary: 'Send password reset link',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset link sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password reset link sent to your email'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return $this->errorResponse('User not found', self::HTTP_NOT_FOUND);
            }

            $token = Str::random(64);

            PasswordResetToken::updateOrCreate(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $user->notify(new ResetPasswordNotification($token));

            return $this->successResponse(null, 'Password reset link sent to your email');
        } catch (\Exception $e) {
            report($e); // Log the exception

            return $this->errorResponse(
                'Failed to send password reset link. Please try again later.',
                self::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[OA\Post(
        path: '/v1/auth/reset-password',
        summary: 'Reset password with token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'token', type: 'string', example: 'reset-token-here'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password reset successfully'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or expired token'),
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $resetRecord = PasswordResetToken::where('email', $request->email)->first();

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            return $this->errorResponse('Invalid or expired token', self::HTTP_BAD_REQUEST);
        }

        // Token expires after 60 minutes
        $tokenExpirationMinutes = 60;
        if ($resetRecord->created_at->addMinutes($tokenExpirationMinutes)->isPast()) {
            $resetRecord->delete();

            return $this->errorResponse('Token has expired', self::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        $resetRecord->delete();

        return $this->successResponse(null, 'Password reset successfully');
    }

    #[OA\Get(
        path: '/v1/auth/user',
        summary: 'Get authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User data retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse($request->user());
    }
}
