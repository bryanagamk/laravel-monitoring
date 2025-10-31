# 📊 Laravel Monitoring dengan Prometheus & Grafana

Sistem monitoring lengkap untuk Laravel 7.20 dengan Docker yang mencakup monitoring sistem, container, database, dan aplikasi.

## 🎯 Metrics yang Dipantau

### 1. **System Metrics** (Node Exporter)
- ✅ **CPU Usage** - Per core dan rata-rata
- ✅ **Memory Usage** - RSS, cache, available
- ✅ **Disk Usage** - Space dan I/O operations
- ✅ **Disk I/O Wait** - Percentage waktu tunggu I/O
- ✅ **Network I/O** - Incoming/outgoing traffic per interface
- ✅ **Load Average** - 1m, 5m, 15m

### 2. **Container Metrics** (cAdvisor)
- ✅ **Container CPU Usage** - Per container
- ✅ **Container Memory Usage** - Per container
- ✅ **Container Network I/O** - Per container
- ✅ **Container Restart Count** - Monitoring stability

### 3. **MySQL Metrics** (MySQL Exporter)
- ✅ **Query Performance** - Queries per second
- ✅ **Connection Pool** - Active connections
- ✅ **Slow Queries** - Performance issues
- ✅ **Table Locks** - Concurrency issues

### 4. **Nginx Metrics** (Nginx Exporter)
- ✅ **Request Rate** - Requests per second
- ✅ **Active Connections** - Current connections
- ✅ **Response Codes** - Status code distribution

### 5. **Laravel Application Metrics** (PromPHP)
- ✅ **HTTP Requests** - Total dan per endpoint
- ✅ **Response Time** - p50, p95, p99 percentiles
- ✅ **Memory Usage** - PHP memory consumption
- ✅ **Application Uptime** - Process uptime
- ✅ **Cache Hit/Miss Rate** - Cache performance
- ✅ **Queue Jobs** - Job processing metrics
- ✅ **Exceptions** - Error tracking

## 🚀 Setup & Installation

### 1. Install Laravel 7.20 (Sudah Selesai)
```bash
composer create-project --prefer-dist laravel/laravel:^7.20 app
```

### 2. Install PromPHP Package (Sudah Selesai)
```bash
docker-compose exec app composer require promphp/prometheus_client_php
```

### 3. Restart Docker Containers
```bash
# Stop semua container
docker-compose down

# Rebuild dan start semua container
docker-compose up -d --build

# Tunggu semua service ready (~30 detik)
```

### 4. Verify Services
```bash
# Check status container
docker-compose ps

# Check logs jika ada masalah
docker-compose logs -f
```

## 🌐 Access Points

| Service | URL | Credentials |
|---------|-----|-------------|
| **Laravel** | http://localhost:8000 | - |
| **Laravel Metrics** | http://localhost:8000/metrics | - |
| **Grafana** | http://localhost:3000 | admin/admin |
| **Prometheus** | http://localhost:9090 | - |
| **cAdvisor** | http://localhost:8080 | - |
| **Node Exporter** | http://localhost:9100 | - |

## 📊 Grafana Dashboard

Dashboard sudah otomatis ter-provision dengan panels:

1. **CPU Usage** - Real-time CPU utilization
2. **Memory Usage** - Memory consumption
3. **Load Average** - System load (1m gauge)
4. **Disk Usage** - Storage utilization
5. **Network I/O** - Network traffic
6. **Container Memory** - Memory per container
7. **Container CPU** - CPU per container
8. **Laravel HTTP Requests** - Request rate
9. **Laravel Response Time** - p50/p95 latency
10. **Laravel Memory** - PHP memory usage
11. **Laravel Uptime** - Application uptime
12. **Disk I/O** - Read/write operations
13. **Disk I/O Wait** - I/O wait percentage
14. **MySQL Queries** - Query performance
15. **MySQL Connections** - Connection pool
16. **Nginx Requests** - Web server metrics

### Akses Dashboard:
1. Buka http://localhost:3000
2. Login dengan `admin` / `admin`
3. Dashboard "Laravel Monitoring Dashboard" sudah tersedia

## 🔧 Konfigurasi File

### Docker Services:
```
laravel-app           - PHP 7.4-FPM (Laravel)
laravel-nginx         - Nginx Web Server
laravel-mysql         - MySQL 8.0 Database
laravel-prometheus    - Prometheus (Metrics Collection)
laravel-grafana       - Grafana (Visualization)
laravel-node-exporter - System Metrics
laravel-cadvisor      - Container Metrics
laravel-mysql-exporter - MySQL Metrics
laravel-nginx-exporter - Nginx Metrics
```

### Prometheus Scrape Jobs:
```yaml
- prometheus:9090      # Prometheus self-monitoring
- node-exporter:9100   # System metrics
- cadvisor:8080        # Container metrics
- mysql-exporter:9104  # MySQL metrics
- nginx-exporter:9113  # Nginx metrics
- nginx:80/metrics     # Laravel app metrics
```

## 📝 Laravel Metrics Endpoint

Metrics tersedia di: `http://localhost:8000/metrics`

### Sample Metrics Output:
```
# HELP laravel_app_http_requests_total Total HTTP requests
# TYPE laravel_app_http_requests_total counter
laravel_app_http_requests_total{method="GET",endpoint="/",status_code="200"} 42

# HELP laravel_app_http_request_duration_seconds HTTP request duration in seconds
# TYPE laravel_app_http_request_duration_seconds histogram
laravel_app_http_request_duration_seconds_bucket{method="GET",endpoint="/",le="0.01"} 10
laravel_app_http_request_duration_seconds_bucket{method="GET",endpoint="/",le="0.05"} 35

# HELP laravel_app_memory_usage_bytes Current memory usage in bytes
# TYPE laravel_app_memory_usage_bytes gauge
laravel_app_memory_usage_bytes 12582912
```

## 🔍 Monitoring Use Cases

### 1. Performance Monitoring
- Track response times per endpoint
- Identify slow queries
- Monitor memory leaks
- Check cache effectiveness

### 2. Capacity Planning
- Monitor resource utilization trends
- Predict when to scale
- Identify bottlenecks
- Track growth patterns

### 3. Troubleshooting
- Correlate metrics during incidents
- Identify resource contention
- Track error rates
- Monitor container health

### 4. SLA Monitoring
- Track uptime
- Monitor response time SLOs
- Alert on threshold breaches
- Generate availability reports

## 🎯 Custom Metrics di Laravel

Anda bisa menambahkan custom metrics di aplikasi Laravel:

```php
use Prometheus\CollectorRegistry;

// Di Controller atau Service
$registry = app('prometheus');

// Counter example
$counter = $registry->getOrRegisterCounter(
    'laravel_app',
    'user_registrations_total',
    'Total user registrations',
    ['source']
);
$counter->inc(['web']);

// Gauge example
$gauge = $registry->getOrRegisterGauge(
    'laravel_app',
    'active_sessions',
    'Number of active sessions'
);
$gauge->set(Session::all()->count());

// Histogram example
$histogram = $registry->getOrRegisterHistogram(
    'laravel_app',
    'order_value',
    'Order value distribution',
    ['currency'],
    [10, 50, 100, 500, 1000]
);
$histogram->observe(299.99, ['USD']);
```

## 🛠️ Troubleshooting

### Container tidak start:
```bash
# Check logs
docker-compose logs [service-name]

# Restart specific service
docker-compose restart [service-name]
```

### Metrics tidak muncul:
```bash
# Check Prometheus targets
# Buka http://localhost:9090/targets
# Pastikan semua targets "UP"

# Test Laravel metrics
curl http://localhost:8000/metrics
```

### Grafana dashboard kosong:
```bash
# Pastikan Prometheus datasource terkonfigurasi
# Check di: Configuration > Data Sources

# Import dashboard manual jika perlu
# Import ID: laravel-monitoring
```

### Memory issues:
```bash
# Increase Docker memory limit di Docker Desktop
# Settings > Resources > Memory

# Clear Laravel cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

## 📈 Best Practices

1. **Set Alerts** - Gunakan Grafana Alerting untuk notifikasi
2. **Regular Backups** - Backup Grafana dashboards & Prometheus data
3. **Retention Policy** - Configure Prometheus data retention
4. **Security** - Change default passwords
5. **Resource Limits** - Set container resource limits in production

## 🔐 Security Notes

⚠️ **IMPORTANT for Production:**

1. Change default Grafana password
2. Add authentication to metrics endpoints
3. Use HTTPS/TLS
4. Configure firewall rules
5. Implement proper access control
6. Use secrets management for credentials

## 📚 Additional Resources

- [Prometheus Documentation](https://prometheus.io/docs/)
- [Grafana Documentation](https://grafana.com/docs/)
- [PromPHP Library](https://github.com/PromPHP/prometheus_client_php)
- [Node Exporter](https://github.com/prometheus/node_exporter)
- [cAdvisor](https://github.com/google/cadvisor)

## 🤝 Support

Jika ada pertanyaan atau issue:
1. Check logs: `docker-compose logs -f`
2. Verify all services running: `docker-compose ps`
3. Test endpoints individually
4. Check Prometheus targets: http://localhost:9090/targets

---

**Happy Monitoring! 🚀📊**
