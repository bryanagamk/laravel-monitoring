<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('prometheus', function ($app) {
            // Use Redis for persistent metrics storage
            Redis::setDefaultOptions([
                'host' => env('REDIS_HOST', 'redis'),
                'port' => (int) env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', null),
                'database' => (int) env('PROMETHEUS_REDIS_DB', 2),
                'timeout' => 0.1,
                'read_timeout' => '10',
                'persistent_connections' => false,
            ]);
            
            return new CollectorRegistry(new Redis());
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
