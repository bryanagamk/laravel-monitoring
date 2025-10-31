# Laravel Monitoring with Docker

This project contains a Docker setup for Laravel 7.20 with **complete monitoring** using Prometheus and Grafana.

## 🎯 Monitoring Features

### ✅ System Metrics (Node Exporter)
- **CPU Usage** - Per core and average
- **Memory Usage** - RSS, cache, swap, available
- **Disk Usage** - Space utilization & I/O operations
- **Disk I/O Wait** - I/O wait percentage
- **Network I/O** - Incoming/outgoing traffic per interface
- **Load Average** - 1m, 5m, 15m

### ✅ Container Metrics (cAdvisor)
- **CPU Usage** - Per container
- **Memory Usage** - Per container
- **Network I/O** - Per container
- **Container Health** - Restart count & uptime

### ✅ Database Metrics (MySQL Exporter)
- **Query Performance** - QPS, slow queries
- **Connection Pool** - Active connections
- **Table Operations** - Locks, inserts, updates

### ✅ Web Server Metrics (Nginx Exporter)
- **Request Rate** - Requests per second
- **Active Connections** - Current connections
- **Response Codes** - Distribution status

### ✅ Laravel Application Metrics (PromPHP)
- **HTTP Requests** - Total & per endpoint
- **Response Time** - p50, p95, p99 percentiles
- **Memory Usage** - PHP memory consumption
- **Application Uptime** - Process uptime
- **Cache Performance** - Hit/miss rates (ready to implement)
- **Queue Jobs** - Job processing metrics (ready to implement)
- **Exceptions** - Error tracking (ready to implement)

### ✅ Third Party API Monitoring
- **API Health Status** - Real-time up/down monitoring
- **Response Time Tracking** - Latency monitoring
- **Uptime Percentage** - SLA tracking
- **HTTP Status Codes** - Error detection
- **Health Check History** - Trend analysis
- **Automated Checks** - Every 5 minutes via scheduler

## Stacks
- PHP 7.4-FPM
- Laravel 7.20
- Nginx
- MySQL 8.0
- Prometheus
- Grafana

## How to Install

### 1. Install Laravel 7.20

```bash
# Install Laravel using Composer
composer create-project --prefer-dist laravel/laravel:^7.20 app
```

### 2. Setup Laravel Environment

```bash
# Copy the .env file
cp app/.env.example app/.env

# Update database configuration in app/.env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
```

### 3. Run Docker Containers

```bash
# Build and run containers
docker-compose up -d --build

# Install Laravel dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate
```

### 4. Set Permission (if required)

```bash
# Set permissions for storage and cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## App Access

| Services | URL | Credentials | Description |
|---------|-----|-------------|-------------|
| **Laravel** | http://localhost:8000 | - | Laravel Applications |
| **Laravel Metrics** | http://localhost:8000/metrics | - | Prometheus Metrics Endpoints |
| **Grafana** | http://localhost:3000 | admin/admin | Dashboards & Visualization |
| **Prometheus** | http://localhost:9090 | - | Metrics Database & Queries |
| **cAdvisor** | http://localhost:8080 | - | Container Metrics UI |
| **Exporter Nodes** | http://localhost:9100/metrics | - | System Metrics |
| **MySQL Exporter** | http://localhost:9104/metrics | - | MySQL Metrics |
| **Nginx Exporter** | http://localhost:9113/metrics | - | Nginx Metrics |

## 📊 Quick Start

```bash
# 1. Start all services
docker-compose up -d

# 2. Wait for services to be ready (~30 seconds)
sleeps 30

# 3. Access Grafana Dashboard
open http://localhost:3000
# Login: admin / admin
# Dashboard: "Laravel Monitoring Dashboard"

# 4. Verify all metrics
./verify.sh
```

## 📚 Documentation

- **[QUICKSTART.md](QUICKSTART.md)** - Quick start guide & usage
- **[MONITORING.md](MONITORING.md)** - Detailed monitoring documentation
- **[LOAD-TESTING.md](LOAD-TESTING.md)** - Load testing guide with 6 different approaches
- **[PRODUCTS-API.md](PRODUCTS-API.md)** - DummyJSON Products API integration guide
- **[API-MONITORING.md](API-MONITORING.md)** - Third party API health monitoring guide
- **[README.md](README.md)** - This file (setup & installation)

## 🔥 Load Testing

This project comes with 6 level load testing scripts:

| Levels | Tools | Complexity | Best For |
|-------|------|------------|----------------|
| 1 | Simple Curls | ⭐ | Quick test, debugging |
| 2 | Parallel Curls | ⭐⭐ | Medium load, CI/CD |
| 3 | Apache Bench | ⭐⭐ | Benchmarking |
| 4 | Python Asyncio | ⭐⭐⭐ | Custom scenarios |
| 5 | K6 | ⭐⭐⭐⭐ | Production testing |
| 6 | Locust | ⭐⭐⭐⭐⭐ | Distributed testing |

### Quick Load Test:

```bash
# Simple test (100 requests)
./loadtest/01-simple-curl.sh

# Production-grade test with k6
k6 run loadtest/05-k6-load-test.js

# Distributed test with Web UI
locust -f loadtest/06-locust-test.py --host=http://localhost:8000
# Open: http://localhost:8089
```

**See full documentation:** [API-MONITORING.md](API-MONITORING.md)

## Useful Docker Commands

```bash
# View logs
docker-compose logs -f

# Go to the app container
docker-compose exec app bash

# Stop containers
docker-compose down

# Stop and delete volumes
docker-compose down -v
```

## Folder Structure

```
laravel-monitoring/
├── docker-compose.yml
├── Dockerfiles
├── app/ # Laravel 7.20 application
├── nginx/
│ └── default.conf # Nginx configuration
├── php/
│ └── local.ini # PHP configuration
├── prometheus/
│ └── prometheus.yml # Prometheus configuration
└── grafana/
└── provisioning/
├── dashboards/ # Grafana dashboards
└── datasources/ # Grafana datasources
```

## Monitoring

### Prometheus
Prometheus is configured for monitoring:
- Prometheus itself
- Nginx
- MySQL
- Laravel App

### Grafana
Grafana is already configured with:
- Prometheus as the default datasource
- Auto-provisioning for dashboards
- Default credentials: admin/admin

You can add custom dashboards in the `grafana/provisioning/dashboards/` folder.

## Troubleshooting

### Permission Issues
```bash
sudo chown -R $USER:$USER app/
```

### Clear Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Containers
```bash
docker-compose down
docker-compose up -d --build
```