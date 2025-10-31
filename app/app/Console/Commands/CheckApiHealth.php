<?php

namespace App\Console\Commands;

use App\Services\ApiHealthCheckService;
use Illuminate\Console\Command;

class CheckApiHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:check-health {--comprehensive : Run comprehensive health check on multiple endpoints}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check third-party API health (DummyJSON)';

    protected $healthCheckService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ApiHealthCheckService $healthCheckService)
    {
        parent::__construct();
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking DummyJSON API health...');
        $this->line('');

        if ($this->option('comprehensive')) {
            return $this->comprehensiveCheck();
        }

        return $this->simpleCheck();
    }

    /**
     * Simple health check
     *
     * @return int
     */
    protected function simpleCheck()
    {
        $result = $this->healthCheckService->checkDummyJsonHealth();
        
        // Store in history
        $this->healthCheckService->storeHealthHistory($result);

        // Display result
        $this->displayResult($result);

        // Calculate uptime
        $uptime = $this->healthCheckService->calculateUptime();
        $this->line('');
        $this->info("API Uptime (last 100 checks): {$uptime}%");

        return $result['status'] === 'up' ? 0 : 1;
    }

    /**
     * Comprehensive health check
     *
     * @return int
     */
    protected function comprehensiveCheck()
    {
        $result = $this->healthCheckService->comprehensiveHealthCheck();

        $this->info("Overall Status: " . strtoupper($result['overall_status']));
        $this->line('');

        // Display table of endpoint results
        $rows = [];
        foreach ($result['endpoints'] as $endpoint => $data) {
            $rows[] = [
                $endpoint,
                $data['status'],
                $data['status_code'],
                $data['response_time_ms'] . ' ms',
                $data['error'] ?? '-'
            ];
        }

        $this->table(
            ['Endpoint', 'Status', 'HTTP Code', 'Response Time', 'Error'],
            $rows
        );

        return $result['overall_status'] === 'up' ? 0 : 1;
    }

    /**
     * Display health check result
     *
     * @param array $result
     * @return void
     */
    protected function displayResult($result)
    {
        $statusColor = $result['status'] === 'up' ? 'info' : 'error';

        $this->table(
            ['Metric', 'Value'],
            [
                ['API Name', $result['api_name']],
                ['Status', strtoupper($result['status'])],
                ['HTTP Status Code', $result['status_code']],
                ['Response Time', $result['response_time_ms'] . ' ms'],
                ['Checked At', $result['checked_at']],
                ['Error', $result['error'] ?? '-'],
            ]
        );

        if ($result['status'] === 'up') {
            $this->info('✓ API is healthy!');
        } else {
            $this->error('✗ API is experiencing issues!');
        }
    }
}
