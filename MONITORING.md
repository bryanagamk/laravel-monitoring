# 📊 Laravel Monitoring with Prometheus & Grafana

A comprehensive monitoring system for Laravel 7.20 with Docker that includes system, container, database, and application monitoring.

## 🎯 Monitored Metrics

### 1. **System Metrics** (Node Exporter)
- ✅ **CPU Usage** - Per core and average
- ✅ **Memory Usage** - RSS, cache, available
- ✅ **Disk Usage** - Space and I/O operations
- ✅ **Disk I/O Wait** - Percentage of I/O waiting time
- ✅ **Network I/O** - Incoming/outgoing traffic per interface
- ✅ **Load Average** - 1m, 5m, 15m

### 2. **Container Metrics** (cAdvisor)
- ✅ **Container CPU Usage** - Per container
- ✅ **Container Memory Usage** - Per container
- ✅ **Container Network I/O** - Per container
- ✅ **Containers Restart Count** - Monitoring stability

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
- ✅ **HTTP Requests** - Total and per endpoint
- ✅ **Response Time** - p50, p95, p99 percentiles
- ✅ **Memory Usage** - PHP memory consumption
- ✅ **Application Uptime** - Process uptime
- ✅ **Cache Hit/Miss Rate** - Cache performance
- ✅ **Queue Jobs** - Job processing metrics
- ✅ **Exceptions** - Tracking error

## 🚀 Setup & Installation

### 1. Install Laravel 7.20 (Completed)
```bash
composer create-project --prefer-dist laravel/laravel:^7.20 app
```

### 2. Install PromPHP Package (Completed)
```bash
docker-compose exec app composer require promphp/prometheus_client_php
```

### 3. Restart Docker Containers
```bash
# Stop all containers
docker-compose down

# Rebuild and start all containers
docker-compose up -d --build

#Wait for all services to be ready (~30s)
```

### 4. Verify Services
```bash
# Check container status
docker-compose ps

# Check logs if there are problems
docker-compose logs -f
```

## 🌐 Access Points

| Services | URL | Credentials |
|---------|-----|-------------|
| **Laravel** | http://localhost:8000 | - |
| **Laravel Metrics** | http://localhost:8000/metrics | - |
| **Grafana** | http://localhost:3000 | admin/admin |
| **Prometheus** | http://localhost:9090 | - |
| **cAdvisor** | http://localhost:8080 | - |
| **Exporter Nodes** | http://localhost:9100 | - |

## 📊 Grafana Dashboard

The dashboard is automatically provisioned with panels:

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
15. **MySQL Connections** - Connections pool
16. **Nginx Requests** - Web server metrics

### Dashboard Access:
1. Go to http://localhost:3000
2. Login with `admin` / `admin`
3. The "Laravel Monitoring Dashboard" dashboard is now available

## 🔧 Configuration Files

### Docker Services:
```
laravel-app - PHP 7.4-FPM (Laravel)
laravel-nginx - Nginx Web Server
laravel-mysql - MySQL 8.0 Database
laravel-prometheus - Prometheus (Metrics Collection)
laravel-grafana - Grafana (Visualization)
laravel-node-exporter - System Metrics
laravel-cadvisor - Container Metrics
laravel-mysql-exporter - MySQL Metrics
laravel-nginx-exporter - Nginx Metrics
```

### Prometheus Scrape Jobs:
```yaml
- prometheus:9090 # Prometheus self-monitoring
- node-exporter:9100 # System metrics
- cadvisor:8080 # Container metrics
- mysql-exporter:9104 # MySQL metrics
- nginx-exporter:9113 # Nginx metrics
- nginx:80/metrics # Laravel app metrics
```

## 📝 Laravel Metrics Endpoints

Metrics are available at: `http://localhost:8000/metrics`

### Sample Metrics Output:
```
# HELP laravel_app_http_requests_total Total HTTP requests
# TYPE laravel_app_http_requests_total counters
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
- Identify resource content
- Track error rates
- Monitor container health

### 4. SLA Monitoring
- Track uptime
- Monitor response time SLOs
- Alerts on threshold breaches
- Generate availability reports

## 🎯 Custom Metrics in Laravel

You can add custom metrics in your Laravel application:

```php
use Prometheus\CollectorRegistry;

// In Controller or Service
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

### Container won't start:
```bash
# Check logs
docker-compose logs [service-name]

# Restart specific services
docker-compose restart [service-name]
```

### Metrics not appearing:
```bash
# Check Prometheus targets
# Open http://localhost:9090/targets
# Ensure all targets "UP"

# Test Laravel metrics
curl http://localhost:8000/metrics
```

### Grafana blank dashboard:
```bash
# Ensure Prometheus datasource is configured
# Check in: Configuration > Data Sources

# Import dashboard manually if needed
# Import ID: laravel-monitoring
```

### Memory issues:
```bash
# Increase Docker memory limit in Docker Desktop
# Settings > Resources > Memory

# Clear Laravel cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

## 📈 Best Practices

1. **Set Alerts** - Use Grafana Alerting for notifications
2. **Regular Backups** - Backup Grafana dashboards & Prometheus data
3. **Retention Policy** - Configure Prometheus data retention
4. **Security** - Change default passwords
5. **Resource Limits** - Set container resource limits in production

## 🔐 Security Notes

⚠️ **IMPORTANT for Production:**

1. Change the default Grafana password
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

If you have questions or issues:
1. Check logs: `docker-compose logs -f`
2. Verify all services running: `docker-compose ps`
3. Test endpoints individually
4. Check Prometheus targets: http://localhost:9090/targets

---

**Happy Monitoring! 🚀📊**