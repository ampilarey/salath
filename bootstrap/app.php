<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return consistent JSON error envelope for all API routes
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                } elseif (method_exists($e, 'getStatusCode')) {
                    $status = $e->getStatusCode();
                } else {
                    $status = 500;
                }
                $message = $e instanceof \Illuminate\Validation\ValidationException
                    ? $e->getMessage()
                    : ($status < 500 ? $e->getMessage() : 'Server error');

                $body = ['error' => $message];

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $body['errors'] = $e->errors();
                }

                return response()->json($body, $status);
            }
        });
    })->create();
