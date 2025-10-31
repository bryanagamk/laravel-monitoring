# Laravel Monitoring dengan Docker

Project ini berisi setup Docker untuk Laravel 7.20 dengan **monitoring lengkap** menggunakan Prometheus dan Grafana.

## 🎯 Monitoring Features

### ✅ System Metrics (Node Exporter)
- **CPU Usage** - Per core dan rata-rata
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
- **Response Codes** - Status distribution

### ✅ Laravel Application Metrics (PromPHP)
- **HTTP Requests** - Total & per endpoint
- **Response Time** - p50, p95, p99 percentiles
- **Memory Usage** - PHP memory consumption
- **Application Uptime** - Process uptime
- **Cache Performance** - Hit/miss rates (ready to implement)
- **Queue Jobs** - Job processing metrics (ready to implement)
- **Exceptions** - Error tracking (ready to implement)

## Stack
- PHP 7.4-FPM
- Laravel 7.20
- Nginx
- MySQL 8.0
- Prometheus
- Grafana

## Cara Install

### 1. Install Laravel 7.20

```bash
# Install Laravel menggunakan Composer
composer create-project --prefer-dist laravel/laravel:^7.20 app
```

### 2. Setup Environment Laravel

```bash
# Copy file .env
cp app/.env.example app/.env

# Update konfigurasi database di app/.env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
```

### 3. Jalankan Docker Container

```bash
# Build dan jalankan containers
docker-compose up -d --build

# Install dependencies Laravel
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Jalankan migration
docker-compose exec app php artisan migrate
```

### 4. Set Permission (jika diperlukan)

```bash
# Set permission untuk storage dan cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## Akses Aplikasi

| Service | URL | Credentials | Description |
|---------|-----|-------------|-------------|
| **Laravel** | http://localhost:8000 | - | Laravel Application |
| **Laravel Metrics** | http://localhost:8000/metrics | - | Prometheus Metrics Endpoint |
| **Grafana** | http://localhost:3000 | admin/admin | Dashboard & Visualization |
| **Prometheus** | http://localhost:9090 | - | Metrics Database & Queries |
| **cAdvisor** | http://localhost:8080 | - | Container Metrics UI |
| **Node Exporter** | http://localhost:9100/metrics | - | System Metrics |
| **MySQL Exporter** | http://localhost:9104/metrics | - | MySQL Metrics |
| **Nginx Exporter** | http://localhost:9113/metrics | - | Nginx Metrics |

## 📊 Quick Start

```bash
# 1. Start all services
docker-compose up -d

# 2. Wait for services to be ready (~30 seconds)
sleep 30

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
- **[README.md](README.md)** - This file (setup & installation)

## Perintah Docker Useful

```bash
# Melihat log
docker-compose logs -f

# Masuk ke container app
docker-compose exec app bash

# Stop containers
docker-compose down

# Stop dan hapus volumes
docker-compose down -v
```

## Struktur Folder

```
laravel-monitoring/
├── docker-compose.yml
├── Dockerfile
├── app/                    # Laravel 7.20 application
├── nginx/
│   └── default.conf        # Nginx configuration
├── php/
│   └── local.ini           # PHP configuration
├── prometheus/
│   └── prometheus.yml      # Prometheus configuration
└── grafana/
    └── provisioning/
        ├── dashboards/     # Grafana dashboards
        └── datasources/    # Grafana datasources
```

## Monitoring

### Prometheus
Prometheus dikonfigurasi untuk monitoring:
- Prometheus itself
- Nginx
- MySQL
- Laravel App

### Grafana
Grafana sudah dikonfigurasi dengan:
- Prometheus sebagai datasource default
- Auto-provisioning untuk dashboards
- Default credentials: admin/admin

Anda bisa menambahkan custom dashboards di folder `grafana/provisioning/dashboards/`.

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
