<?php

namespace App\Http\Controllers;

use App\Services\ApiHealthCheckService;
use Illuminate\Http\Request;

class ApiHealthController extends Controller
{
    protected $healthCheckService;

    public function __construct(ApiHealthCheckService $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Get current API health status
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Try to get cached result first
        $cached = $this->healthCheckService->getCachedHealth();

        if ($cached) {
            return response()->json([
                'success' => true,
                'cached' => true,
                'data' => $cached
            ]);
        }

        // If no cache, perform fresh check
        $result = $this->healthCheckService->checkDummyJsonHealth();
        $this->healthCheckService->storeHealthHistory($result);

        return response()->json([
            'success' => true,
            'cached' => false,
            'data' => $result
        ]);
    }

    /**
     * Force fresh health check
     *
     * @return \Illuminate\Http\Response
     */
    public function check()
    {
        $result = $this->healthCheckService->checkDummyJsonHealth();
        $this->healthCheckService->storeHealthHistory($result);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Comprehensive health check
     *
     * @return \Illuminate\Http\Response
     */
    public function comprehensive()
    {
        $result = $this->healthCheckService->comprehensiveHealthCheck();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get health check history
     *
     * @return \Illuminate\Http\Response
     */
    public function history()
    {
        $history = $this->healthCheckService->getHealthHistory(50);
        $uptime = $this->healthCheckService->calculateUptime();

        return response()->json([
            'success' => true,
            'data' => [
                'uptime_percentage' => $uptime,
                'total_checks' => count($history),
                'history' => $history
            ]
        ]);
    }

    /**
     * Get uptime statistics
     *
     * @return \Illuminate\Http\Response
     */
    public function stats()
    {
        $history = $this->healthCheckService->getHealthHistory(100);
        
        if (empty($history)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'uptime_percentage' => 100.0,
                    'total_checks' => 0,
                    'successful_checks' => 0,
                    'failed_checks' => 0,
                    'average_response_time_ms' => 0,
                ]
            ]);
        }

        $totalChecks = count($history);
        $successfulChecks = count(array_filter($history, function($check) {
            return $check['status'] === 'up';
        }));
        $failedChecks = $totalChecks - $successfulChecks;
        
        $responseTimes = array_column($history, 'response_time_ms');
        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $minResponseTime = min($responseTimes);
        $maxResponseTime = max($responseTimes);

        return response()->json([
            'success' => true,
            'data' => [
                'uptime_percentage' => round(($successfulChecks / $totalChecks) * 100, 2),
                'total_checks' => $totalChecks,
                'successful_checks' => $successfulChecks,
                'failed_checks' => $failedChecks,
                'average_response_time_ms' => round($avgResponseTime, 2),
                'min_response_time_ms' => round($minResponseTime, 2),
                'max_response_time_ms' => round($maxResponseTime, 2),
            ]
        ]);
    }
}

