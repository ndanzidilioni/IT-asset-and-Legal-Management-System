<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Allow access to change password endpoint
        if ($request->is('api/change-password') || $request->is('api/logout')) {
            return $next($request);
        }
        
        // Check if user must change password
        if ($user && $user->must_change_password) {
            return response()->json([
                'success' => false,
                'message' => 'You must change your password before accessing this resource.',
                'must_change_password' => true
            ], 403);
        }
        
        return $next($request);
    }
}
