<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;

class PrometheusMetrics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        try {
            // Calculate request duration
            $duration = microtime(true) - $startTime;
            
            // Get metrics registry (using InMemory storage for simplicity)
            $registry = app('prometheus');
            
            $namespace = 'laravel_app';
            
            // Record HTTP request
            $counter = $registry->getOrRegisterCounter(
                $namespace,
                'http_requests_total',
                'Total HTTP requests',
                ['method', 'endpoint', 'status_code']
            );
            
            $endpoint = $request->path();
            $method = $request->method();
            $statusCode = $response->getStatusCode();
            
            $counter->inc([$method, $endpoint, $statusCode]);
            
            // Record request duration
            $histogram = $registry->getOrRegisterHistogram(
                $namespace,
                'http_request_duration_seconds',
                'HTTP request duration in seconds',
                ['method', 'endpoint'],
                [0.01, 0.05, 0.1, 0.5, 1.0, 2.5, 5.0, 10.0]
            );
            
            $histogram->observe($duration, [$method, $endpoint]);
            
        } catch (\Exception $e) {
            // Silent fail - don't break the app if metrics fail
            Log::error('Prometheus metrics error: ' . $e->getMessage());
        }
        
        return $response;
    }
}
