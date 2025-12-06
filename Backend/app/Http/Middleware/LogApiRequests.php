<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log before processing
        Log::info('API Request Received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'has_auth_header' => $request->hasHeader('Authorization'),
            'timestamp' => now()->toDateTimeString(),
        ]);

        $response = $next($request);

        // Log after processing
        Log::info('API Request Completed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $response;
    }
}

