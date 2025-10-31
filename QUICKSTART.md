# 🎯 QUICK START GUIDE - Laravel Monitoring

## ✅ Setup Selesai!

Sistem monitoring Laravel Anda sudah siap dengan semua komponen:

### 📊 Metrics yang Dipantau:

#### 1. **System Metrics** (Node Exporter)
- ✅ CPU Usage (per core & rata-rata)
- ✅ Memory Usage (RSS, cache, available)
- ✅ Disk Usage & I/O operations
- ✅ Network I/O (incoming/outgoing)
- ✅ Load Average (1m, 5m, 15m)

#### 2. **Container Metrics** (cAdvisor)
- ✅ CPU usage per container
- ✅ Memory usage per container
- ✅ Network I/O per container
- ✅ Container restart count

#### 3. **Database Metrics** (MySQL Exporter)
- ✅ Query performance & QPS
- ✅ Connection pool status
- ✅ Slow queries tracking

#### 4. **Web Server Metrics** (Nginx Exporter)
- ✅ Request rate
- ✅ Active connections
- ✅ Response codes

#### 5. **Laravel App Metrics** (PromPHP)
- ✅ HTTP requests per endpoint
- ✅ Response time (p50, p95, p99)
- ✅ PHP memory usage
- ✅ Application uptime
- ✅ Process monitoring

---

## 🌐 Access URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| **Laravel** | http://localhost:8000 | - |
| **Laravel Metrics** | http://localhost:8000/metrics | - |
| **Grafana Dashboard** | http://localhost:3000 | admin / admin |
| **Prometheus** | http://localhost:9090 | - |
| **cAdvisor** | http://localhost:8080 | - |
| **Node Exporter** | http://localhost:9100/metrics | - |
| **MySQL Exporter** | http://localhost:9104/metrics | - |
| **Nginx Exporter** | http://localhost:9113/metrics | - |

---

## 🚀 Cara Menggunakan

### 1. Akses Grafana Dashboard
```bash
# Buka browser
open http://localhost:3000

# Login dengan:
Username: admin
Password: admin

# Dashboard sudah auto-loaded:
# "Laravel Monitoring Dashboard"
```

### 2. View Metrics di Prometheus
```bash
# Buka Prometheus
open http://localhost:9090

# Check targets status
open http://localhost:9090/targets

# Query examples:
# - CPU: 100 - (avg(irate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)
# - Memory: node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes * 100
# - Requests: rate(laravel_app_http_requests_total[5m])
```

### 3. Test Metrics Endpoint
```bash
# Laravel metrics
curl http://localhost:8000/metrics

# System metrics
curl http://localhost:9100/metrics | grep "node_cpu"

# Container metrics
curl http://localhost:8080/metrics | grep "container_cpu"

# MySQL metrics
curl http://localhost:9104/metrics | grep "mysql"

# Nginx metrics
curl http://localhost:9113/metrics | grep "nginx"
```

---

## 📈 Dashboard Panels

Dashboard Grafana sudah include:

1. **CPU Usage** - Real-time CPU utilization chart
2. **Memory Usage** - Memory consumption over time
3. **Load Average** - System load (1m gauge)
4. **Disk Usage** - Storage utilization gauge
5. **Network I/O** - Network traffic (TX/RX)
6. **Container Memory** - Memory per container
7. **Container CPU** - CPU usage per container
8. **Laravel HTTP Requests** - Request rate by status
9. **Laravel Response Time** - p50/p95 latency
10. **Laravel Memory** - PHP memory usage
11. **Laravel Uptime** - Application uptime
12. **Disk I/O** - Read/write operations
13. **Disk I/O Wait** - I/O wait percentage
14. **MySQL Queries** - Query performance
15. **MySQL Connections** - Connection pool
16. **Nginx Requests** - Web server RPS

---

## 🛠️ Management Commands

### Start/Stop Services
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart specific service
docker-compose restart [service-name]

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f prometheus
docker-compose logs -f grafana
```

### Laravel Commands
```bash
# Access Laravel container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan migrate

# View Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log
```

### Verification
```bash
# Run verification script
./verify.sh

# Check all containers
docker-compose ps

# Check Prometheus targets
curl http://localhost:9090/api/v1/targets
```

---

## 📊 Custom Metrics di Laravel

Tambahkan custom metrics di aplikasi Anda:

```php
// Di Controller atau Service
$registry = app('prometheus');

// Counter - untuk counting events
$counter = $registry->getOrRegisterCounter(
    'laravel_app',
    'user_logins_total',
    'Total user logins',
    ['status']
);
$counter->inc(['success']); // increment

// Gauge - untuk nilai yang naik/turun
$gauge = $registry->getOrRegisterGauge(
    'laravel_app',
    'active_users',
    'Number of currently active users'
);
$gauge->set(User::where('active', true)->count());

// Histogram - untuk distribusi nilai (response time, dll)
$histogram = $registry->getOrRegisterHistogram(
    'laravel_app',
    'api_response_time',
    'API response time in seconds',
    ['endpoint'],
    [0.1, 0.5, 1.0, 2.0, 5.0] // buckets
);
$histogram->observe(0.234, ['/api/users']);
```

---

## 🎯 Use Cases

### Performance Monitoring
- Track response times per endpoint
- Identify slow database queries
- Monitor memory leaks
- Check cache hit/miss rates

### Capacity Planning
- Monitor resource utilization trends
- Predict when to scale
- Identify bottlenecks
- Track growth patterns

### Troubleshooting
- Correlate metrics during incidents
- Identify resource contention
- Track error rates
- Monitor container health

### Alerting (Setup di Grafana)
- CPU usage > 80%
- Memory usage > 85%
- Disk usage > 90%
- Response time > 2s (p95)
- Error rate > 5%

---

## 🔧 Troubleshooting

### Container tidak start
```bash
docker-compose logs [service-name]
docker-compose restart [service-name]
```

### Metrics tidak muncul
```bash
# Check Prometheus targets
open http://localhost:9090/targets

# Test endpoint
curl http://localhost:8000/metrics
```

### Grafana dashboard kosong
```bash
# Wait 30-60 seconds untuk data collection
# Refresh Prometheus datasource
# Check time range (last 1 hour)
```

### Permission errors
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

---

## 📚 Documentation

- **Detailed Guide**: `MONITORING.md`
- **Main README**: `README.md`
- **Prometheus Config**: `prometheus/prometheus.yml`
- **Grafana Dashboard**: `grafana/provisioning/dashboards/`

---

## 🎉 You're All Set!

Monitoring system Anda sudah berjalan dan siap digunakan!

**Next Steps:**
1. ✅ Akses Grafana: http://localhost:3000
2. ✅ Explore dashboard
3. ✅ Setup alerting rules
4. ✅ Customize metrics sesuai kebutuhan
5. ✅ Monitor production metrics

**Happy Monitoring! 🚀📊**
