<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogDeniedRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Log requests that result in 4xx or 5xx status codes
        if ($response->getStatusCode() >= 400) {
            $logData = [
                'timestamp' => now()->toDateTimeString(),
                'method' => $request->getMethod(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getStatusCode() === 404 ? 'Not Found' : 'Error',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'query_params' => $request->query(),
                'request_body' => $request->getMethod() !== 'GET' ? $request->except(['password', 'password_confirmation']) : null,
                'headers' => [
                    'host' => $request->header('host'),
                    'accept' => $request->header('accept'),
                    'content-type' => $request->header('content-type'),
                    'authorization' => $request->hasHeader('authorization') ? '[REDACTED]' : null,
                ],
            ];

            // Log to specific channel for denied requests
            Log::channel('denied_requests')->error('Denied Request', $logData);
            
            // Also log 404s specifically
            if ($response->getStatusCode() === 404) {
                Log::channel('denied_requests')->warning('404 Not Found', [
                    'url' => $request->fullUrl(),
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'suggested_fix' => $this->getSuggestedFix($request->path()),
                ]);
            }
        }

        return $response;
    }

    /**
     * Get suggested fix for common 404 paths
     */
    private function getSuggestedFix(string $path): string
    {
        $suggestions = [
            'login' => 'Try accessing "/" (root) instead - login form is served from root route',
            'admin' => 'Try "/dashboard/admin/dashboard" for admin panel',
            'api/login' => 'Use POST method instead of GET for API login',
            'dashboard' => 'Try "/dashboard/admin/dashboard" with proper authentication',
        ];

        foreach ($suggestions as $pattern => $suggestion) {
            if (str_contains($path, $pattern)) {
                return $suggestion;
            }
        }

        return 'Check routes/web.php for available routes';
    }
}