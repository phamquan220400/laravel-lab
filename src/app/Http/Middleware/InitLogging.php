<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InitLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique request ID for tracking
        $requestId = uniqid('req_', true);
        
        // Start timing
        $startTime = microtime(true);
        
        // Log incoming request
        Log::info('Api Request', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'query_params' => $request->query(),
            'body' => $this->sanitizeBody($request),
            'timestamp' => now()->toISOString(),
        ]);
        
        // Add request ID to request for use in other parts of the application
        $request->merge(['_request_id' => $requestId]);
        
        // Process the request
        $response = $next($request);
        
        // Calculate execution time
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log response
        Log::info('Api Response', [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'execution_time_ms' => $executionTime,
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString(),
        ]);
        
        return $response;
    }
    
    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'cookie', 'x-csrf-token'];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $headers[$key] = '******';
            }
        }
        
        return $headers;
    }
    
    /**
     * Sanitize request body to remove sensitive information
     */
    private function sanitizeBody(Request $request): array
    {
        $body = $request->all();
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '***REDACTED***';
            }
        }
        
        return $body;
    }
}
