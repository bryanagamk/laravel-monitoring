<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

class MetricsController extends Controller
{
    /**
     * Expose Prometheus metrics
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        // Create registry with in-memory storage
        $registry = new CollectorRegistry(new InMemory());

        // Application metrics
        $namespace = 'laravel_app';

        // HTTP Request Counter
        $requestCounter = $registry->getOrRegisterCounter(
            $namespace,
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'endpoint', 'status_code']
        );

        // Response Time Histogram
        $responseTime = $registry->getOrRegisterHistogram(
            $namespace,
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'endpoint']
        );

        // Active Users Gauge
        $activeUsers = $registry->getOrRegisterGauge(
            $namespace,
            'active_users',
            'Number of active users'
        );

        // Database Query Counter
        $dbQueries = $registry->getOrRegisterCounter(
            $namespace,
            'database_queries_total',
            'Total database queries',
            ['type']
        );

        // Memory Usage Gauge
        $memoryUsage = $registry->getOrRegisterGauge(
            $namespace,
            'memory_usage_bytes',
            'Current memory usage in bytes'
        );
        $memoryUsage->set(memory_get_usage(true));

        // Peak Memory Usage Gauge
        $peakMemory = $registry->getOrRegisterGauge(
            $namespace,
            'memory_peak_bytes',
            'Peak memory usage in bytes'
        );
        $peakMemory->set(memory_get_peak_usage(true));

        // Application Uptime
        $uptime = $registry->getOrRegisterGauge(
            $namespace,
            'app_uptime_seconds',
            'Application uptime in seconds'
        );
        
        // Get Laravel app start time (you can customize this)
        $startTime = LARAVEL_START ?? microtime(true);
        $uptime->set(microtime(true) - $startTime);

        // PHP Version Info
        $phpVersion = $registry->getOrRegisterGauge(
            $namespace,
            'php_info',
            'PHP version info',
            ['version']
        );
        $phpVersion->set(1, [PHP_VERSION]);

        // Cache Hit Rate (example)
        $cacheHits = $registry->getOrRegisterCounter(
            $namespace,
            'cache_hits_total',
            'Total cache hits'
        );

        $cacheMisses = $registry->getOrRegisterCounter(
            $namespace,
            'cache_misses_total',
            'Total cache misses'
        );

        // Queue Jobs
        $queueJobs = $registry->getOrRegisterCounter(
            $namespace,
            'queue_jobs_total',
            'Total queue jobs',
            ['status', 'queue']
        );

        // Exception Counter
        $exceptions = $registry->getOrRegisterCounter(
            $namespace,
            'exceptions_total',
            'Total exceptions thrown',
            ['type']
        );

        // Render metrics
        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        return response($result)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
