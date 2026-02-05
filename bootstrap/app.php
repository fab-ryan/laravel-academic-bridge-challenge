<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login to continue.',
                ], 401);
            }
        });
        // $exceptions->render(function (Throwable $e, Request $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Resource not found. Please check the URL and try again.',
        //         ], 404);
        //     }
        // });

        // $exceptions->render(function ($e, Request $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'An unexpected error occurred. Please try again later.',
        //         ], 404);
        //     }
        // });
    })->create();
