# 🔍 Third Party API Health Monitoring

Complete documentation for the third-party API health monitoring system (DummyJSON). This system monitors availability, performance, and response time of external APIs in real-time.

## 📋 Overview

Fitur monitoring ini menyediakan:
- ✅ **Real-time health checks** - Status API up/down
- ✅ **Response time tracking** - Monitor latency API
- ✅ **Uptime statistics** - Calculate uptime percentage
- ✅ **Historical data** - Track history checks
- ✅ **Prometheus metrics** - Export untuk monitoring
- ✅ **Grafana dashboard** - Visualisasi metrics
- ✅ **Artisan commands** - Manual & scheduled checks
- ✅ **REST API endpoints** - Programmatic access

## 🎯 Metrics yang Dipantau

### 1. API Status (Up/Down)
- **Metric**: `third_party_api_up{api_name="dummyjson"}`
- **Type**: Gauge
- **Values**: 1 = UP, 0 = DOWN
- **Description**: Current API availability status

### 2. Response Time
- **Metric**: `third_party_api_response_time_ms{api_name="dummyjson"}`
- **Type**: Gauge
- **Unit**: milliseconds
- **Description**: Latest API response time

### 3. HTTP Status Code
- **Metric**: `third_party_api_status_code{api_name="dummyjson"}`
- **Type**: Gauge
- **Values**: 200, 404, 500, etc.
- **Description**: Latest HTTP status code

### 4. Health Check Counter
- **Metric**: `third_party_api_health_checks_total{api_name="dummyjson", status="up|down"}`
- **Type**: Counter
- **Description**: Total number of health checks performed

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 Laravel Application                     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────────────────────────────────────────┐  │
│  │    ApiHealthCheckService                         │  │
│  │  - checkDummyJsonHealth()                        │  │
│  │  - comprehensiveHealthCheck()                    │  │
│  │  - updateMetrics()                               │  │
│  │  - calculateUptime()                             │  │
│  └──────────────────────────────────────────────────┘  │
│           │                        │                    │
│           ▼                        ▼                    │
│  ┌─────────────────┐    ┌──────────────────────┐      │
│  │ ApiHealthController   CheckApiHealth Command  │      │
│  │  - index()            - handle()              │      │
│  │  - check()            - simpleCheck()         │      │
│  │  - comprehensive()    - comprehensiveCheck()  │      │
│  │  - history()                                  │      │
│  │  - stats()                                    │      │
│  └─────────────────┘    └──────────────────────┘      │
│           │                        │                    │
│           ▼                        ▼                    │
│  ┌──────────────────────────────────────────────────┐  │
│  │         Prometheus Metrics Registry              │  │
│  │  - third_party_api_up                            │  │
│  │  - third_party_api_response_time_ms              │  │
│  │  - third_party_api_status_code                   │  │
│  │  - third_party_api_health_checks_total           │  │
│  └──────────────────────────────────────────────────┘  │
│           │                                             │
└───────────┼─────────────────────────────────────────────┘
            │
            ▼
┌─────────────────────────────────────────────────────────┐
│                  /metrics Endpoint                      │
│           (Exposed to Prometheus)                       │
└─────────────────────────────────────────────────────────┘
            │
            ▼
┌─────────────────────────────────────────────────────────┐
│                    Prometheus                           │
│              (Scrapes metrics every 15s)                │
└─────────────────────────────────────────────────────────┘
            │
            ▼
┌─────────────────────────────────────────────────────────┐
│                     Grafana                             │
│      Third Party API Monitoring Dashboard               │
│   - Status Panel, Uptime %, Response Time Chart         │
└─────────────────────────────────────────────────────────┘
```

## 📡 API Endpoints

### 1. Get Current Health Status

**GET** `/api/health`

Returns cached health status (if available) or performs fresh check.

```bash
curl http://localhost:8000/api/health | jq
```

**Response:**
```json
{
  "success": true,
  "cached": true,
  "data": {
    "api_name": "dummyjson",
    "status": "up",
    "status_code": 200,
    "response_time_ms": 138.63,
    "checked_at": "2025-10-31T02:37:55+00:00",
    "error": null
  }
}
```

### 2. Force Fresh Health Check

**GET** `/api/health/check`

Performs fresh health check regardless of cache.

```bash
curl http://localhost:8000/api/health/check | jq
```

**Response:**
```json
{
  "success": true,
  "data": {
    "api_name": "dummyjson",
    "status": "up",
    "status_code": 200,
    "response_time_ms": 79.26,
    "checked_at": "2025-10-31T02:38:00+00:00",
    "error": null
  }
}
```

### 3. Comprehensive Health Check

**GET** `/api/health/comprehensive`

Checks multiple endpoints for detailed health assessment.

```bash
curl http://localhost:8000/api/health/comprehensive | jq
```

**Response:**
```json
{
  "success": true,
  "data": {
    "api_name": "dummyjson",
    "overall_status": "up",
    "endpoints": {
      "products": {
        "status": "up",
        "status_code": 200,
        "response_time_ms": 127.49
      },
      "categories": {
        "status": "up",
        "status_code": 200,
        "response_time_ms": 337.38
      },
      "search": {
        "status": "up",
        "status_code": 200,
        "response_time_ms": 442.14
      }
    },
    "checked_at": "2025-10-31T02:38:15+00:00"
  }
}
```

### 4. Get Health Statistics

**GET** `/api/health/stats`

Returns uptime statistics and response time metrics.

```bash
curl http://localhost:8000/api/health/stats | jq
```

**Response:**
```json
{
  "success": true,
  "data": {
    "uptime_percentage": 100,
    "total_checks": 10,
    "successful_checks": 10,
    "failed_checks": 0,
    "average_response_time_ms": 229.08,
    "min_response_time_ms": 79.26,
    "max_response_time_ms": 863.24
  }
}
```

### 5. Get Health History

**GET** `/api/health/history`

Returns historical health check data (last 50 checks).

```bash
curl http://localhost:8000/api/health/history | jq
```

**Response:**
```json
{
  "success": true,
  "data": {
    "uptime_percentage": 100,
    "total_checks": 10,
    "history": [
      {
        "status": "up",
        "response_time_ms": 138.63,
        "checked_at": "2025-10-31T02:37:55+00:00"
      },
      ...
    ]
  }
}
```

## ⌨️ Artisan Commands

### Simple Health Check

```bash
docker-compose exec app php artisan api:check-health
```

**Output:**
```
Checking DummyJSON API health...

+------------------+---------------------------+
| Metric           | Value                     |
+------------------+---------------------------+
| API Name         | dummyjson                 |
| Status           | UP                        |
| HTTP Status Code | 200                       |
| Response Time    | 138.63 ms                 |
| Checked At       | 2025-10-31T02:37:55+00:00 |
| Error            | -                         |
+------------------+---------------------------+
✓ API is healthy!

API Uptime (last 100 checks): 100%
```

### Comprehensive Health Check

```bash
docker-compose exec app php artisan api:check-health --comprehensive
```

**Output:**
```
Checking DummyJSON API health...

Overall Status: UP

+------------+--------+-----------+---------------+-------+
| Endpoint   | Status | HTTP Code | Response Time | Error |
+------------+--------+-----------+---------------+-------+
| products   | up     | 200       | 127.49 ms     | -     |
| categories | up     | 200       | 337.38 ms     | -     |
| search     | up     | 200       | 442.14 ms     | -     |
+------------+--------+-----------+---------------+-------+
```

## ⏰ Scheduled Monitoring

Health checks run automatically every 5 minutes via Laravel Scheduler.

### Configuration

File: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Check DummyJSON API health every 5 minutes
    $schedule->command('api:check-health')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->runInBackground();
}
```

### Setup Cron (Production)

To run the scheduler in production, add to crontab:

```bash
* * * * * cd /path/to/laravel-monitoring && docker-compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

Atau jalankan scheduler dalam container:

```bash
docker-compose exec app sh -c "while true; do php artisan schedule:run; sleep 60; done" &
```

## 📊 Grafana Dashboard

Dashboard **"Third Party API Monitoring (DummyJSON)"** tersedia di Grafana dengan panels:

### Panels

1. **API Status** - Real-time status (UP/DOWN)
2. **Uptime Percentage (5m)** - Rolling 5-minute uptime
3. **Response Time** - Time series chart
4. **Success/Failure Rate** - Health check results
5. **Response Time Bar Gauge** - Current response time
6. **Total Health Checks** - Cumulative count
7. **Successful Checks** - Success count
8. **Failed Checks** - Failure count
9. **Last HTTP Status Code** - Latest status
10. **Availability Over Time** - Historical availability

### Access Dashboard

1. Open Grafana: http://localhost:3000
2. Login: `admin` / `admin`
3. Navigate to **Dashboards** → **Third Party API Monitoring (DummyJSON)**

## 🔧 Configuration

### Health Check Settings

Edit `app/Services/ApiHealthCheckService.php` to customize:

```php
// Timeout for API requests
Http::timeout(10)->get(...)  // Change timeout

// Cache duration
Cache::put('api_health_dummyjson', $result, 60); // Change TTL

// History size
if (count($history) > 100) { // Change history size
    $history = array_slice($history, -100);
}
```

### Add More APIs to Monitor

To monitor additional APIs, extend the service:

```php
public function checkMyCustomApi()
{
    $startTime = microtime(true);
    
    try {
        $response = Http::timeout(10)->get('https://api.example.com/health');
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        $result = [
            'api_name' => 'my_custom_api',
            'status' => $response->successful() ? 'up' : 'down',
            'status_code' => $response->status(),
            'response_time_ms' => $responseTime,
            'checked_at' => now()->toIso8601String(),
        ];
        
        $this->updateMetrics($result);
        
        return $result;
    } catch (\Exception $e) {
        // Handle error
    }
}
```

## 🧪 Testing

### Run Test Script

```bash
./test-api-monitoring.sh
```

### Manual Tests

```bash
# Test artisan command
docker-compose exec app php artisan api:check-health

# Test API endpoint
curl http://localhost:8000/api/health/check | jq

# Test comprehensive check
curl http://localhost:8000/api/health/comprehensive | jq

# View statistics
curl http://localhost:8000/api/health/stats | jq

# Check Prometheus metrics
curl http://localhost:8000/metrics | grep third_party_api
```

## 📈 Metrics Queries (Prometheus)

### PromQL Examples

```promql
# Current API status (1=up, 0=down)
third_party_api_up{api_name="dummyjson"}

# Response time
third_party_api_response_time_ms{api_name="dummyjson"}

# Uptime percentage (last 5 minutes)
sum(rate(third_party_api_health_checks_total{status="up"}[5m])) 
/ 
sum(rate(third_party_api_health_checks_total[5m])) * 100

# Average response time (last 5 minutes)
avg_over_time(third_party_api_response_time_ms{api_name="dummyjson"}[5m])

# Total failed checks
sum(third_party_api_health_checks_total{api_name="dummyjson",status="down"})
```

## 🚨 Alerting (Optional)

### Prometheus Alerting Rules

Create `prometheus/alerts/api_health.yml`:

```yaml
groups:
  - name: api_health
    interval: 30s
    rules:
      - alert: ApiDown
        expr: third_party_api_up{api_name="dummyjson"} == 0
        for: 2m
        labels:
          severity: critical
        annotations:
          summary: "DummyJSON API is down"
          description: "API has been down for more than 2 minutes"
      
      - alert: ApiSlowResponse
        expr: third_party_api_response_time_ms{api_name="dummyjson"} > 1000
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "DummyJSON API slow response"
          description: "API response time > 1000ms for 5 minutes"
      
      - alert: ApiLowUptime
        expr: |
          sum(rate(third_party_api_health_checks_total{status="up"}[1h])) 
          / 
          sum(rate(third_party_api_health_checks_total[1h])) * 100 < 95
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: "DummyJSON API low uptime"
          description: "API uptime < 95% in the last hour"
```

## 💡 Best Practices

1. **Frequency**: Don't check too frequently (recommended: every 5 minutes)
2. **Timeout**: Set reasonable timeout (10-30 seconds)
3. **Caching**: Cache results to avoid excessive checks
4. **History**: Keep limited history (last 100 checks)
5. **Alerting**: Setup alerts for critical issues
6. **Dashboard**: Monitor trends, not just current status

## 🐛 Troubleshooting

### Issue: Metrics not showing in Grafana

**Solution**: 
1. Verify Prometheus is scraping: http://localhost:9090/targets
2. Check if metrics exist: `curl http://localhost:8000/metrics | grep third_party_api`
3. Run health check manually: `docker-compose exec app php artisan api:check-health`

### Issue: Health check always fails

**Solution**:
1. Check network connectivity from container
2. Verify API URL is accessible
3. Check timeout settings
4. Review error logs: `docker-compose logs app`

### Issue: Scheduler not running

**Solution**:
1. Verify cron is configured
2. Check schedule:run output: `docker-compose exec app php artisan schedule:run`
3. Review scheduler logs

## 📚 Files Created

```
app/
├── Services/
│   └── ApiHealthCheckService.php          ✓ Health check service
├── Http/Controllers/
│   └── ApiHealthController.php            ✓ REST API controller
├── Console/
│   ├── Commands/
│   │   └── CheckApiHealth.php             ✓ Artisan command
│   └── Kernel.php                         ✓ Scheduled task (updated)
└── routes/
    └── web.php                            ✓ Routes (updated)

grafana/provisioning/dashboards/
└── api-monitoring.json                    ✓ Grafana dashboard

Scripts/
├── test-api-monitoring.sh                 ✓ Test script
└── API-MONITORING.md                      ✓ This documentation
```

## 🎊 Summary

✅ **API Health Monitoring** - Fully implemented
✅ **Prometheus Metrics** - Exported and ready
✅ **Grafana Dashboard** - 10 panels configured
✅ **REST API** - 5 endpoints available
✅ **Artisan Command** - Simple & comprehensive checks
✅ **Scheduled Checks** - Every 5 minutes
✅ **Statistics** - Uptime, response time, history
✅ **Testing** - Test script provided

**Ready for production use!** 🚀
