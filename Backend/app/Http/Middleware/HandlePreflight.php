<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandlePreflight
{
    /**
     * Handle an incoming request.
     * This middleware handles OPTIONS preflight requests immediately
     * before any authentication or other processing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $allowedOriginPatterns = config('cors.allowed_origins_patterns', []);
        $allowedMethods = config('cors.allowed_methods', ['*']);
        $allowedHeaders = config('cors.allowed_headers', ['*']);
        $supportsCredentials = (bool) config('cors.supports_credentials', false);

        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $allowOrigin = $this->determineAllowedOrigin($origin, $allowedOrigins, $allowedOriginPatterns, $supportsCredentials);

            if ($allowOrigin === null) {
                \Log::warning('Preflight OPTIONS rejected due to origin', [
                    'path' => $request->path(),
                    'origin' => $origin,
                ]);

                return response('', 403);
            }

            \Log::info('Preflight OPTIONS request handled', [
                'path' => $request->path(),
                'origin' => $origin,
                'allow_origin' => $allowOrigin,
            ]);

            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowOrigin ?? '*')
                ->header('Access-Control-Allow-Methods', $this->formatHeaderList($allowedMethods))
                ->header('Access-Control-Allow-Headers', $this->formatHeaderList($allowedHeaders))
                ->header('Access-Control-Allow-Credentials', $supportsCredentials ? 'true' : 'false')
                ->header('Access-Control-Max-Age', (string) config('cors.max_age', 86400));
        }

        // For non-OPTIONS requests, process and add CORS headers to response
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            // If an exception occurs, create a response and add CORS headers
            $response = response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
        
        // Determine allowed origin for the actual request
        $allowOrigin = $this->determineAllowedOrigin($origin, $allowedOrigins, $allowedOriginPatterns, $supportsCredentials);
        
        // If determineAllowedOrigin returned null but origin is in allowed list, use it directly
        if ($allowOrigin === null && $origin && in_array($origin, $allowedOrigins, true)) {
            $allowOrigin = $origin;
        }
        
        // Always add CORS headers if we have an origin match
        if ($allowOrigin !== null) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin, true);
            $response->headers->set('Access-Control-Allow-Methods', $this->formatHeaderList($allowedMethods), true);
            $response->headers->set('Access-Control-Allow-Headers', $this->formatHeaderList($allowedHeaders), true);
            $response->headers->set('Access-Control-Allow-Credentials', $supportsCredentials ? 'true' : 'false', true);
            
            // Log for debugging
            \Log::info('CORS headers added to response', [
                'path' => $request->path(),
                'method' => $request->method(),
                'origin' => $origin,
                'allowOrigin' => $allowOrigin,
                'status' => $response->getStatusCode(),
            ]);
        } else {
            \Log::warning('CORS headers NOT added - origin not allowed', [
                'path' => $request->path(),
                'method' => $request->method(),
                'origin' => $origin,
                'allowedOrigins' => $allowedOrigins,
            ]);
        }

        return $response;
    }

    private function determineAllowedOrigin(?string $origin, array $allowedOrigins, array $allowedOriginPatterns, bool $supportsCredentials): ?string
    {
        if ($origin && in_array($origin, $allowedOrigins, true)) {
            return $origin;
        }

        if ($origin && $this->originMatchesPatterns($origin, $allowedOriginPatterns)) {
            return $origin;
        }

        if (!$supportsCredentials && in_array('*', $allowedOrigins, true)) {
            return '*';
        }

        return null;
    }

    private function originMatchesPatterns(string $origin, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    private function formatHeaderList(array $items): string
    {
        if (count($items) === 1) {
            return $items[0];
        }

        return implode(', ', $items);
    }
}


