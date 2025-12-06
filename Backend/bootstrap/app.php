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
        // Handle CORS globally for all requests
        $middleware->prepend(\App\Http\Middleware\SimpleCorsMiddleware::class);
        
        $middleware->alias([
            'password.changed' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);
        
        // Exclude routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'login-web',
            'logout-web',
            'sanctum/csrf-cookie',
            'debug-session',
            'api/*', // API routes use Sanctum's own CSRF handling
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Add CORS headers to all exception responses
        $exceptions->render(function (\Throwable $e, $request) {
            $response = null;
            
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                if ($request->is('api/*')) {
                    $response = response()->json([
                        'message' => 'Unauthenticated.'
                    ], 401);
                }
            }
            
            // If we have a response, add CORS headers
            if ($response) {
                $origin = $request->header('Origin');
                $allowedOrigins = config('cors.allowed_origins', []);
                $supportsCredentials = (bool) config('cors.supports_credentials', false);
                
                // Determine allowed origin
                $allowOrigin = null;
                if ($origin && in_array($origin, $allowedOrigins, true)) {
                    $allowOrigin = $origin;
                } elseif (!$supportsCredentials && in_array('*', $allowedOrigins, true)) {
                    $allowOrigin = '*';
                }
                
                if ($allowOrigin) {
                    $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
                    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
                    $response->headers->set('Access-Control-Allow-Credentials', $supportsCredentials ? 'true' : 'false');
                }
            }
            
            return $response;
        });
    })->create();
