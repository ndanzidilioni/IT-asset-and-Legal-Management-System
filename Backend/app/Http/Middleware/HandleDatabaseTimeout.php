<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleDatabaseTimeout
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
        // Set a timeout for database operations
        try {
            // Test database connection with timeout
            $start = microtime(true);
            DB::connection()->getPdo();
            $elapsed = microtime(true) - $start;
            
            if ($elapsed > 2) {
                Log::warning('Slow database connection detected', [
                    'elapsed' => $elapsed,
                    'path' => $request->path()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Database connection failed in middleware', [
                'error' => $e->getMessage(),
                'path' => $request->path()
            ]);
            
            // Don't block the request, but log it
            // The actual route handler will handle the error
        }

        return $next($request);
    }
}

