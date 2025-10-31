# 🔥 Load Testing Guide - Laravel Monitoring

Panduan lengkap untuk melakukan load testing pada aplikasi Laravel dan memonitor hasilnya di Grafana.

## 📋 Daftar Isi

1. [Test Endpoints](#test-endpoints)
2. [Load Testing Scripts](#load-testing-scripts)
3. [Monitoring di Grafana](#monitoring-di-grafana)
4. [Metrics yang Diamati](#metrics-yang-diamati)
5. [Best Practices](#best-practices)

---

## 🎯 Test Endpoints

Laravel sudah dilengkapi dengan berbagai endpoint untuk testing:

| Endpoint | Deskripsi | Load Type |
|----------|-----------|-----------|
| `/test/simple` | Response minimal, hampir tanpa load | Very Low |
| `/test/cpu` | Fibonacci calculation, CPU intensive | High CPU |
| `/test/memory` | Large array allocation | High Memory |
| `/test/database` | Database queries | I/O |
| `/test/cache` | Cache operations | Memory + I/O |
| `/test/mixed` | Kombinasi CPU + Memory + Cache | Mixed |
| `/test/slow` | Simulated slow response (1-3s) | Latency |

### Test Endpoint Examples:

```bash
# Simple endpoint
curl http://localhost:8000/test/simple

# CPU intensive
curl http://localhost:8000/test/cpu

# Memory intensive
curl http://localhost:8000/test/memory
```

---

## 🚀 Load Testing Scripts

### Level 1: Simple Curl Loop (Paling Sederhana)

**File:** `loadtest/01-simple-curl.sh`

**Deskripsi:** Sequential requests menggunakan curl dalam loop

**Karakteristik:**
- ✅ Sangat sederhana, tidak perlu install dependency
- ✅ Mudah dipahami dan dimodifikasi
- ❌ Sequential, tidak concurrent
- ❌ Throughput rendah

**Cara Menggunakan:**

```bash
chmod +x loadtest/01-simple-curl.sh
./loadtest/01-simple-curl.sh
```

**Konfigurasi:**
```bash
REQUESTS=100        # Total requests
DELAY=0.1          # Delay between requests (seconds)
```

**Kapan Digunakan:**
- Testing dasar
- Debugging endpoint
- Load ringan untuk development

---

### Level 2: Parallel Curl

**File:** `loadtest/02-parallel-curl.sh`

**Deskripsi:** Parallel execution menggunakan xargs

**Karakteristik:**
- ✅ Tidak perlu install dependency tambahan
- ✅ Concurrent requests
- ✅ Response time statistics
- ❌ Terbatas untuk load sedang

**Cara Menggunakan:**

```bash
chmod +x loadtest/02-parallel-curl.sh
./loadtest/02-parallel-curl.sh
```

**Konfigurasi:**
```bash
CONCURRENT=10         # Parallel requests
TOTAL_REQUESTS=500    # Total requests
DURATION=60          # Max duration (seconds)
```

**Kapan Digunakan:**
- Load testing sedang
- Quick performance check
- CI/CD pipeline testing

---

### Level 3: Apache Bench (ab)

**File:** `loadtest/03-apache-bench.sh`

**Deskripsi:** Industry-standard benchmarking tool

**Karakteristik:**
- ✅ Industry standard
- ✅ Detailed statistics
- ✅ Percentile calculations
- ✅ Connection reuse
- ❌ Perlu install apache2-utils

**Install:**

```bash
# macOS
brew install apache2

# Ubuntu/Debian
sudo apt-get install apache2-utils

# CentOS/RHEL
sudo yum install httpd-tools
```

**Cara Menggunakan:**

```bash
chmod +x loadtest/03-apache-bench.sh
./loadtest/03-apache-bench.sh
```

**Konfigurasi:**
```bash
CONCURRENT=50        # Concurrent requests
TOTAL_REQUESTS=1000  # Total requests per endpoint
```

**Kapan Digunakan:**
- Quick benchmarking
- Comparing performance changes
- Load testing production-ready apps

---

### Level 4: Python Concurrent

**File:** `loadtest/04-python-concurrent.py`

**Deskripsi:** Async/concurrent testing dengan Python asyncio

**Karakteristik:**
- ✅ True concurrency dengan asyncio
- ✅ Detailed statistics
- ✅ Easy to customize
- ✅ Good for complex scenarios
- ❌ Perlu Python 3.7+ dan dependencies

**Install Dependencies:**

```bash
pip install aiohttp asyncio
```

**Cara Menggunakan:**

```bash
chmod +x loadtest/04-python-concurrent.py
python3 loadtest/04-python-concurrent.py
```

**Konfigurasi:**
```python
CONCURRENT_USERS = 20
TOTAL_REQUESTS = 500
TIMEOUT = 10
```

**Kapan Digunakan:**
- Medium to heavy load testing
- Custom test scenarios
- Need detailed statistics
- Programmable test logic

---

### Level 5: K6 (Production-Grade)

**File:** `loadtest/05-k6-load-test.js`

**Deskripsi:** Modern load testing tool dengan advanced scenarios

**Karakteristik:**
- ✅ Production-grade tool
- ✅ Advanced scenarios (ramping, spike)
- ✅ Excellent metrics
- ✅ Thresholds & SLOs
- ✅ CI/CD friendly
- ❌ Perlu install k6

**Install:**

```bash
# macOS
brew install k6

# Ubuntu/Debian
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6

# Windows
choco install k6
```

**Cara Menggunakan:**

```bash
# Run dengan scenarios
k6 run loadtest/05-k6-load-test.js

# Custom parameters
k6 run --vus 50 --duration 5m loadtest/05-k6-load-test.js

# With output to JSON
k6 run --out json=results.json loadtest/05-k6-load-test.js
```

**Scenarios yang Tersedia:**

1. **Constant Load** - 10 VUs selama 1 menit
2. **Ramping Load** - Bertahap 0→20→50 VUs
3. **Spike Test** - Sudden spike ke 100 VUs

**Kapan Digunakan:**
- Production load testing
- Performance testing
- SLO validation
- CI/CD integration
- Stress testing

---

### Level 6: Locust (Production-Grade Distributed)

**File:** `loadtest/06-locust-test.py`

**Deskripsi:** Distributed load testing dengan Web UI

**Karakteristik:**
- ✅ Production-grade distributed testing
- ✅ Real-time web UI monitoring
- ✅ Distributed architecture
- ✅ Python-based (easy to extend)
- ✅ Custom user behavior
- ❌ Perlu install locust

**Install:**

```bash
pip install locust
```

**Cara Menggunakan:**

#### Mode 1: Web UI (Interactive)

```bash
locust -f loadtest/06-locust-test.py --host=http://localhost:8000
```

Then open: http://localhost:8089

#### Mode 2: Headless (Command Line)

```bash
locust -f loadtest/06-locust-test.py \
       --host=http://localhost:8000 \
       --users 100 \
       --spawn-rate 10 \
       --run-time 5m \
       --headless
```

#### Mode 3: Distributed (Master-Worker)

```bash
# Terminal 1 - Master
locust -f loadtest/06-locust-test.py \
       --host=http://localhost:8000 \
       --master

# Terminal 2,3,4... - Workers
locust -f loadtest/06-locust-test.py \
       --host=http://localhost:8000 \
       --worker
```

**User Types:**

1. **LaravelUser** (95%) - Regular users with weighted tasks
2. **AdminUser** (5%) - Admin checking metrics

**Task Weights:**
- Simple endpoint: 40%
- CPU intensive: 20%
- Memory intensive: 15%
- Database: 10%
- Cache: 10%
- Mixed: 5%
- Slow: 3%

**Kapan Digunakan:**
- Large-scale load testing
- Distributed testing
- Complex user scenarios
- Real-time monitoring needed
- Production-grade testing

---

## 📊 Monitoring di Grafana

### Akses Dashboard

```bash
# Buka Grafana
http://localhost:3000

# Login
Username: admin
Password: admin

# Dashboard
"Laravel Monitoring Dashboard"
```

### Metrics yang Terlihat

Saat load testing berjalan, perhatikan perubahan di panel berikut:

#### 1. System Metrics

**CPU Usage (%)**
- Akan naik saat `/test/cpu` dipanggil
- Spike selama test berlangsung
- Normal < 50%, Warning > 70%, Critical > 90%

**Memory Usage (%)**
- Akan naik saat `/test/memory` dipanggil
- Perhatikan memory leak (tidak turun setelah test)
- Normal < 70%, Warning > 80%, Critical > 90%

**Load Average (1m)**
- Indikator beban sistem
- Ideal: < jumlah CPU cores
- Warning: > 2x CPU cores

**Disk I/O**
- Read/write operations
- Akan naik saat `/test/database` dipanggil

**Network I/O**
- Incoming/outgoing traffic
- Korelasi dengan request rate

#### 2. Container Metrics

**Container CPU Usage**
- Monitor per container (app, nginx, mysql)
- Identify bottlenecks

**Container Memory Usage**
- Track memory per container
- Detect memory leaks

#### 3. Application Metrics

**Laravel HTTP Requests/s**
- Real-time request rate
- Compare dengan target throughput
- Track error rates (non-200)

**Laravel Response Time**
- p50 (median)
- p95 (95th percentile)
- p99 (99th percentile)

**Target SLOs:**
- p50 < 200ms
- p95 < 1000ms
- p99 < 2000ms

**Laravel Memory Usage**
- PHP memory consumption
- Peak memory usage

#### 4. Database Metrics

**MySQL Queries**
- QPS (queries per second)
- Total queries

**MySQL Connections**
- Active connections
- Max connections limit

---

## 🎯 Metrics yang Diamati

### System Level

```
✅ CPU Usage
   - Total CPU utilization
   - Per-core usage
   - User vs system time

✅ Memory Usage
   - Total memory
   - Available memory
   - Cache/Buffer usage
   - Swap usage

✅ Disk I/O
   - Read operations
   - Write operations
   - I/O wait time
   - Disk utilization

✅ Network I/O
   - Bytes sent
   - Bytes received
   - Packets sent/received
   - Network errors

✅ Load Average
   - 1 minute average
   - 5 minute average
   - 15 minute average
```

### Container Level

```
✅ Container CPU
   - CPU usage per container
   - CPU throttling

✅ Container Memory
   - Memory usage
   - Memory limits
   - OOM kills

✅ Container Network
   - Network traffic per container
   - Network errors
```

### Application Level

```
✅ HTTP Requests
   - Total requests
   - Requests per second
   - Status code distribution

✅ Response Time
   - Min, Max, Avg
   - Percentiles (p50, p90, p95, p99)
   - Response time distribution

✅ Application Metrics
   - PHP memory usage
   - Application uptime
   - Error rates
   - Cache hit/miss
```

---

## 💡 Best Practices

### 1. Start Small, Scale Up

```bash
# Start with low load
./loadtest/01-simple-curl.sh

# Increase gradually
./loadtest/02-parallel-curl.sh

# Heavy load
k6 run loadtest/05-k6-load-test.js
```

### 2. Monitor Before, During, and After

```bash
# Before: Establish baseline
# Check Grafana metrics at idle state

# During: Monitor real-time
# Watch for anomalies, spikes, errors

# After: Analyze results
# Check if metrics return to baseline
# Look for memory leaks
```

### 3. Use Appropriate Tool

| Use Case | Recommended Tool |
|----------|------------------|
| Quick test | Simple curl / ab |
| Development | Parallel curl / Python |
| CI/CD | k6 |
| Production | k6 / Locust |
| Distributed | Locust |

### 4. Set Realistic Targets

```bash
# Define SLOs first
Response Time (p95): < 1000ms
Response Time (p99): < 2000ms
Error Rate: < 1%
Throughput: > 100 req/s
```

### 5. Test Different Scenarios

```bash
# Normal load
# Peak load  
# Spike load
# Sustained load
# Gradual ramp-up
# Gradual ramp-down
```

### 6. Monitor Resource Limits

```bash
# Check container limits
docker-compose exec app php -i | grep memory_limit

# Check MySQL connections
docker-compose exec mysql mysql -u root -proot \
  -e "SHOW VARIABLES LIKE 'max_connections';"

# Check Nginx worker connections
docker-compose exec nginx nginx -T | grep worker_connections
```

---

## 📝 Quick Reference

### Make Scripts Executable

```bash
chmod +x loadtest/*.sh
```

### Run Quick Test

```bash
# Level 1 - Simple
./loadtest/01-simple-curl.sh

# Level 2 - Parallel
./loadtest/02-parallel-curl.sh

# Level 3 - Apache Bench
./loadtest/03-apache-bench.sh

# Level 4 - Python
python3 loadtest/04-python-concurrent.py

# Level 5 - K6
k6 run loadtest/05-k6-load-test.js

# Level 6 - Locust (Web UI)
locust -f loadtest/06-locust-test.py --host=http://localhost:8000
```

### View Grafana

```bash
open http://localhost:3000
```

### Check Laravel Logs

```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

### Check Prometheus Metrics

```bash
curl http://localhost:8000/metrics
```

---

## 🎓 Example Workflow

### Scenario: Performance Testing Sebelum Deploy

```bash
# 1. Pastikan monitoring berjalan
docker-compose ps

# 2. Buka Grafana di browser kedua
open http://localhost:3000

# 3. Run baseline test (5 menit)
k6 run --duration 5m --vus 10 loadtest/05-k6-load-test.js

# 4. Observe di Grafana:
#    - CPU usage pattern
#    - Memory consumption
#    - Response time (p95 < 1s?)
#    - Error rate (< 1%?)

# 5. Run stress test (spike)
k6 run loadtest/05-k6-load-test.js

# 6. Verify recovery:
#    - Metrics kembali normal?
#    - Ada memory leak?
#    - Container restart?

# 7. Document results
#    - Screenshot Grafana
#    - Save k6 output
#    - Note any issues
```

---

## 🚨 Troubleshooting

### High CPU Usage

```bash
# Check top processes
docker-compose exec app top

# Optimize code
# Add caching
# Review algorithms (like Fibonacci)
```

### Memory Leak

```bash
# Monitor memory over time
# Check if memory returns to baseline after test
# Review array allocations
# Check cache clearing
```

### High Response Time

```bash
# Check database queries
# Add database indexes
# Optimize N+1 queries
# Enable query caching
```

### Container Restart

```bash
# Check logs
docker-compose logs app

# Check OOM kills
dmesg | grep -i oom

# Increase container limits
# Optimize memory usage
```

---

## ✅ Checklist Before Production

- [ ] Baseline metrics documented
- [ ] Load test passed with target RPS
- [ ] Response time < SLO targets
- [ ] Error rate < 1%
- [ ] No memory leaks detected
- [ ] CPU usage acceptable
- [ ] Database connections stable
- [ ] Container limits appropriate
- [ ] Auto-scaling configured (if applicable)
- [ ] Monitoring alerts configured

---

**Happy Load Testing! 🚀📊**

Buka Grafana dan lihat metrics real-time: http://localhost:3000
