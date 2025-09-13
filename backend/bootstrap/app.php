<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register common aliases used by route middleware.
        /*
        $middleware->alias([
            'auth' => Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
            'verified' => Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Web middleware group: cookies, session and Sanctum stateful auth for SPA.
        $middleware->web([
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            Illuminate\Routing\Middleware\SubstituteBindings::class,
            Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        ]);

        // API middleware group: bindings and stateful detection for Sanctum.
        $middleware->api([
            Illuminate\Routing\Middleware\SubstituteBindings::class,
            Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        */
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'field' => $field,
                        'message' => $message,
                    ];
                }
            }
            return response()->json(['errors' => $errors], 422);
        });
    })->create();
