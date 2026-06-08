<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestTime
{
    /**
     * Handle an incoming request.
     * Logs the endpoint, method, status code, and response time.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timer before request is processed
        $startTime = microtime(true);

        // Pass request to next middleware / controller
        $response = $next($request);

        // Calculate duration in milliseconds
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Log the request details
        Log::channel('daily')->info('API Request Log', [
            'method'      => $request->method(),
            'endpoint'    => $request->path(),
            'full_url'    => $request->fullUrl(),
            'ip'          => $request->ip(),
            'user_id'     => $request->user()?->id ?? 'guest',
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration . 'ms',
            'user_agent'  => $request->userAgent(),
        ]);

        // Also attach timing header to response (useful in Postman)
        $response->headers->set('X-Response-Time', $duration . 'ms');

        return $response;
    }
}