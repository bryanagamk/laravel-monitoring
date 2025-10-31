# Load Testing Scripts

Collection of load testing scripts from simple to production-grade.

## 📁 Files

| Level | File | Tool | Complexity | Use Case |
|-------|------|------|------------|----------|
| 1 | `01-simple-curl.sh` | curl | ⭐ | Quick test, debugging |
| 2 | `02-parallel-curl.sh` | curl + xargs | ⭐⭐ | Medium load, CI/CD |
| 3 | `03-apache-bench.sh` | Apache Bench | ⭐⭐ | Benchmarking |
| 4 | `04-python-concurrent.py` | Python asyncio | ⭐⭐⭐ | Custom scenarios |
| 5 | `05-k6-load-test.js` | K6 | ⭐⭐⭐⭐ | Production testing |
| 6 | `06-locust-test.py` | Locust | ⭐⭐⭐⭐⭐ | Distributed testing |

## 🚀 Quick Start

```bash
# Make scripts executable
chmod +x *.sh

# Run simplest test
./01-simple-curl.sh

# Run production-grade test
k6 run 05-k6-load-test.js
```

## 📊 Monitor Results

Open Grafana while testing: http://localhost:3000

## 📖 Full Documentation

See [LOAD-TESTING.md](../LOAD-TESTING.md) for complete guide.
