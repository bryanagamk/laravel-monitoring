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
        // Use the shared registry from service provider
        $registry = app('prometheus');

        // Application metrics
        $namespace = 'laravel_app';

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

        // Render metrics
        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        return response($result)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
