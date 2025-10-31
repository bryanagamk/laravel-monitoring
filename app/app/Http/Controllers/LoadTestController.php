<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LoadTestController extends Controller
{
    /**
     * Simple endpoint - minimal load
     */
    public function simple()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Simple endpoint',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * CPU intensive endpoint
     */
    public function cpuIntensive()
    {
        $start = microtime(true);
        
        // Fibonacci calculation (CPU intensive)
        $n = rand(30, 35);
        $fib = $this->fibonacci($n);
        
        // Some random calculations
        $result = 0;
        for ($i = 0; $i < 100000; $i++) {
            $result += sqrt($i) * sin($i);
        }
        
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'ok',
            'endpoint' => 'cpu-intensive',
            'fibonacci' => $fib,
            'calculation' => $result,
            'duration' => round($duration, 4),
        ]);
    }

    /**
     * Memory intensive endpoint
     */
    public function memoryIntensive()
    {
        $start = microtime(true);
        
        // Allocate large arrays
        $data = [];
        for ($i = 0; $i < 10000; $i++) {
            $data[] = [
                'id' => $i,
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'data' => str_repeat('x', 100),
                'timestamp' => now()->toIso8601String(),
            ];
        }
        
        $memoryUsed = memory_get_usage(true) / 1024 / 1024; // MB
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'ok',
            'endpoint' => 'memory-intensive',
            'items_generated' => count($data),
            'memory_mb' => round($memoryUsed, 2),
            'duration' => round($duration, 4),
        ]);
    }

    /**
     * Database query endpoint
     */
    public function database()
    {
        $start = microtime(true);
        
        // Simple database queries
        $queries = [];
        
        try {
            // Test connection
            $queries[] = DB::select('SELECT 1 as test');
            
            // Show tables
            $tables = DB::select('SHOW TABLES');
            
            $duration = microtime(true) - $start;
            
            return response()->json([
                'status' => 'ok',
                'endpoint' => 'database',
                'tables_count' => count($tables),
                'query_count' => count($queries),
                'duration' => round($duration, 4),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cache operations endpoint
     */
    public function cache()
    {
        $start = microtime(true);
        
        $key = 'load_test_' . rand(1, 100);
        $data = [
            'timestamp' => now()->toIso8601String(),
            'random' => rand(1000, 9999),
            'data' => str_repeat('cache_data_', 50),
        ];
        
        // Cache operations
        Cache::put($key, $data, 60);
        $retrieved = Cache::get($key);
        $exists = Cache::has($key);
        
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'ok',
            'endpoint' => 'cache',
            'cache_key' => $key,
            'exists' => $exists,
            'duration' => round($duration, 4),
        ]);
    }

    /**
     * Mixed operations endpoint
     */
    public function mixed()
    {
        $start = microtime(true);
        
        // CPU
        $fib = $this->fibonacci(20);
        
        // Memory
        $data = array_fill(0, 1000, str_repeat('x', 10));
        
        // Cache
        $cacheKey = 'mixed_' . time();
        Cache::put($cacheKey, $data, 30);
        
        // Small calculation
        $result = 0;
        for ($i = 0; $i < 10000; $i++) {
            $result += $i;
        }
        
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'ok',
            'endpoint' => 'mixed',
            'fibonacci' => $fib,
            'array_size' => count($data),
            'calculation' => $result,
            'duration' => round($duration, 4),
        ]);
    }

    /**
     * Slow endpoint - simulates slow response
     */
    public function slow()
    {
        $start = microtime(true);
        
        // Random delay between 1-3 seconds
        $delay = rand(1000000, 3000000); // microseconds
        usleep($delay);
        
        $duration = microtime(true) - $start;
        
        return response()->json([
            'status' => 'ok',
            'endpoint' => 'slow',
            'delay_seconds' => round($delay / 1000000, 2),
            'duration' => round($duration, 4),
        ]);
    }

    /**
     * Fibonacci helper
     */
    private function fibonacci($n)
    {
        if ($n <= 1) return $n;
        
        $a = 0;
        $b = 1;
        
        for ($i = 2; $i <= $n; $i++) {
            $temp = $a + $b;
            $a = $b;
            $b = $temp;
        }
        
        return $b;
    }
}
