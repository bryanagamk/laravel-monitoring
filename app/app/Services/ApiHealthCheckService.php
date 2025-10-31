<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class ApiHealthCheckService
{
    protected $dummyJsonService;
    protected $registry;

    public function __construct(DummyJsonService $dummyJsonService)
    {
        $this->dummyJsonService = $dummyJsonService;
        $this->registry = app('prometheus');
    }

    /**
     * Check DummyJSON API health
     * 
     * @return array
     */
    public function checkDummyJsonHealth()
    {
        $startTime = microtime(true);
        $status = 'down';
        $statusCode = 0;
        $responseTime = 0;
        $error = null;

        try {
            // Try to fetch a simple endpoint
            $response = Http::timeout(10)->get('https://dummyjson.com/products/1');
            
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $statusCode = $response->status();

            if ($response->successful()) {
                $data = $response->json();
                
                // Verify response has expected structure
                if (isset($data['id']) && isset($data['title'])) {
                    $status = 'up';
                } else {
                    $status = 'degraded';
                    $error = 'Invalid response structure';
                }
            } else {
                $status = 'down';
                $error = 'HTTP ' . $statusCode;
            }

        } catch (\Exception $e) {
            $responseTime = (microtime(true) - $startTime) * 1000;
            $status = 'down';
            $error = $e->getMessage();
            
            Log::error('DummyJSON API health check failed', [
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);
        }

        $result = [
            'api_name' => 'dummyjson',
            'status' => $status,
            'status_code' => $statusCode,
            'response_time_ms' => round($responseTime, 2),
            'checked_at' => now()->toIso8601String(),
            'error' => $error,
        ];

        // Update metrics
        $this->updateMetrics($result);

        // Cache the result for quick access
        Cache::put('api_health_dummyjson', $result, 60); // Cache for 1 minute

        return $result;
    }

    /**
     * Check multiple endpoints for comprehensive health check
     * 
     * @return array
     */
    public function comprehensiveHealthCheck()
    {
        $endpoints = [
            'products' => 'https://dummyjson.com/products/1',
            'categories' => 'https://dummyjson.com/products/categories',
            'search' => 'https://dummyjson.com/products/search?q=phone',
        ];

        $results = [];
        $overallStatus = 'up';

        foreach ($endpoints as $name => $url) {
            $startTime = microtime(true);
            
            try {
                $response = Http::timeout(10)->get($url);
                $responseTime = (microtime(true) - $startTime) * 1000;
                
                $results[$name] = [
                    'status' => $response->successful() ? 'up' : 'down',
                    'status_code' => $response->status(),
                    'response_time_ms' => round($responseTime, 2),
                ];

                if (!$response->successful()) {
                    $overallStatus = 'degraded';
                }

            } catch (\Exception $e) {
                $responseTime = (microtime(true) - $startTime) * 1000;
                
                $results[$name] = [
                    'status' => 'down',
                    'status_code' => 0,
                    'response_time_ms' => round($responseTime, 2),
                    'error' => $e->getMessage(),
                ];

                $overallStatus = 'down';
            }
        }

        return [
            'api_name' => 'dummyjson',
            'overall_status' => $overallStatus,
            'endpoints' => $results,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Update Prometheus metrics
     * 
     * @param array $result
     * @return void
     */
    protected function updateMetrics($result)
    {
        try {
            // API Up/Down status (1 = up, 0 = down)
            $gauge = $this->registry->getOrRegisterGauge(
                'laravel_app',
                'third_party_api_up',
                'Third party API availability (1 = up, 0 = down)',
                ['api_name']
            );
            $gauge->set($result['status'] === 'up' ? 1 : 0, [$result['api_name']]);

            // Response time
            $gauge = $this->registry->getOrRegisterGauge(
                'laravel_app',
                'third_party_api_response_time_ms',
                'Third party API response time in milliseconds',
                ['api_name']
            );
            $gauge->set($result['response_time_ms'], [$result['api_name']]);

            // HTTP Status Code
            $gauge = $this->registry->getOrRegisterGauge(
                'laravel_app',
                'third_party_api_status_code',
                'Third party API HTTP status code',
                ['api_name']
            );
            $gauge->set($result['status_code'], [$result['api_name']]);

            // Total health checks counter
            $counter = $this->registry->getOrRegisterCounter(
                'laravel_app',
                'third_party_api_health_checks_total',
                'Total number of health checks performed',
                ['api_name', 'status']
            );
            $counter->inc([$result['api_name'], $result['status']]);

        } catch (\Exception $e) {
            Log::error('Failed to update API health metrics', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached health status
     * 
     * @return array|null
     */
    public function getCachedHealth()
    {
        return Cache::get('api_health_dummyjson');
    }

    /**
     * Get health check history from cache
     * 
     * @param int $limit
     * @return array
     */
    public function getHealthHistory($limit = 20)
    {
        $history = Cache::get('api_health_dummyjson_history', []);
        return array_slice($history, -$limit);
    }

    /**
     * Store health check in history
     * 
     * @param array $result
     * @return void
     */
    public function storeHealthHistory($result)
    {
        $history = Cache::get('api_health_dummyjson_history', []);
        
        $history[] = [
            'status' => $result['status'],
            'response_time_ms' => $result['response_time_ms'],
            'checked_at' => $result['checked_at'],
        ];

        // Keep only last 100 entries
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        Cache::put('api_health_dummyjson_history', $history, 3600); // Cache for 1 hour
    }

    /**
     * Calculate uptime percentage from history
     * 
     * @return float
     */
    public function calculateUptime()
    {
        $history = $this->getHealthHistory(100);
        
        if (empty($history)) {
            return 100.0;
        }

        $totalChecks = count($history);
        $successfulChecks = count(array_filter($history, function($check) {
            return $check['status'] === 'up';
        }));

        return round(($successfulChecks / $totalChecks) * 100, 2);
    }
}
