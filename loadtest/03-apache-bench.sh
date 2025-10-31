#!/bin/bash

# =========================================
# Level 3: Apache Bench (ab)
# Industry standard benchmarking tool
# =========================================

echo "🔥 Apache Bench Load Test"
echo "======================================"
echo ""

# Check if ab is installed
if ! command -v ab &> /dev/null; then
    echo "❌ Apache Bench (ab) not found!"
    echo ""
    echo "Install with:"
    echo "  macOS: brew install apache2"
    echo "  Ubuntu: sudo apt-get install apache2-utils"
    echo "  CentOS: sudo yum install httpd-tools"
    echo ""
    exit 1
fi

# Configuration
BASE_URL="http://localhost:8000"
CONCURRENT=50
TOTAL_REQUESTS=1000

echo "Configuration:"
echo "  Base URL: $BASE_URL"
echo "  Concurrent Requests: $CONCURRENT"
echo "  Total Requests: $TOTAL_REQUESTS"
echo ""

# Test endpoints
declare -A ENDPOINTS=(
    ["Simple"]="/test/simple"
    ["CPU Intensive"]="/test/cpu"
    ["Memory Intensive"]="/test/memory"
    ["Database"]="/test/database"
    ["Cache"]="/test/cache"
    ["Mixed"]="/test/mixed"
)

echo "🚀 Starting Apache Bench tests..."
echo "   Monitor in real-time: http://localhost:3000"
echo ""
echo "======================================"

for name in "${!ENDPOINTS[@]}"; do
    endpoint="${ENDPOINTS[$name]}"
    url="${BASE_URL}${endpoint}"
    
    echo ""
    echo "Testing: $name ($endpoint)"
    echo "--------------------------------------"
    
    ab -n $TOTAL_REQUESTS -c $CONCURRENT -q "$url" | grep -E "(Requests per second|Time per request|Transfer rate|Percentage of the requests|Connection Times)"
    
    echo ""
    sleep 2
done

echo ""
echo "======================================"
echo "✅ Load Test Complete!"
echo "======================================"
echo ""
echo "📊 Check detailed metrics in Grafana:"
echo "   http://localhost:3000"
echo ""
echo "   Look for:"
echo "   - CPU Usage spike"
echo "   - Memory consumption"
echo "   - Network I/O increase"
echo "   - Laravel response time"
echo "   - HTTP request rate"
echo ""
