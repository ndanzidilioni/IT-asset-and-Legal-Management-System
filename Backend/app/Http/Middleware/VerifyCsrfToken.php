<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login-web', // Temporarily exclude for debugging
        'logout-web',
        'sanctum/csrf-cookie',
        'debug-session',
    ];
    
    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        
        // Log for debugging
        \Log::info('CSRF Token Check', [
            'session_token' => $request->session()->token(),
            'request_token' => $token,
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
        ]);
        
        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }
}
