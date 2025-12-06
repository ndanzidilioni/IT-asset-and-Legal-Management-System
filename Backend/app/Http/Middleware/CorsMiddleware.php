<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Allowed frontend origins
        $allowedOrigins = [
            'http://localhost',
            'http://127.0.0.1',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://10.10.100.193:3000',
        ];

        $origin = $request->headers->get('Origin');
        $allowOrigin = in_array($origin, $allowedOrigins, true) ? $origin : '';

        // Debug logging
        \Log::info('CORS Debug', [
            'method' => $request->method(),
            'path' => $request->path(),
            'origin' => $origin,
            'allowOrigin' => $allowOrigin,
            'isOptions' => $request->isMethod('OPTIONS')
        ]);

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $resp = response('', 200)
                ->header('Access-Control-Allow-Origin', 'http://localhost:3000')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, X-XSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');

            \Log::info('OPTIONS Response Headers', $resp->headers->all());
            return $resp;
        }

        $response = $next($request);

        // Add CORS headers to response
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
