#!/bin/bash

# =========================================
# Level 2: Parallel Curl
# Using parallel execution
# =========================================

echo "🔄 Parallel Curl Load Test"
echo "======================================"
echo ""

# Configuration
BASE_URL="http://localhost:8000"
CONCURRENT=10  # parallel requests
TOTAL_REQUESTS=500
DURATION=60  # seconds

echo "Configuration:"
echo "  Base URL: $BASE_URL"
echo "  Concurrent Users: $CONCURRENT"
echo "  Total Requests: $TOTAL_REQUESTS"
echo "  Max Duration: ${DURATION}s"
echo ""

# Test endpoints
ENDPOINTS=(
    "/test/simple"
    "/test/cpu"
    "/test/memory"
    "/test/database"
    "/test/cache"
    "/test/mixed"
)

echo "🚀 Starting parallel load test..."
echo "   Open Grafana: http://localhost:3000"
echo ""

# Create temp directory for results
TEMP_DIR=$(mktemp -d)

# Function to make request
make_request() {
    local id=$1
    local endpoint=${ENDPOINTS[$RANDOM % ${#ENDPOINTS[@]}]}
    local url="${BASE_URL}${endpoint}"
    
    local start=$(date +%s%3N)
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "$url")
    local end=$(date +%s%3N)
    local duration=$((end - start))
    
    echo "${id},${endpoint},${http_code},${duration}" >> "${TEMP_DIR}/results.csv"
}

export -f make_request
export BASE_URL
export ENDPOINTS
export TEMP_DIR

# Initialize results file
echo "id,endpoint,http_code,duration_ms" > "${TEMP_DIR}/results.csv"

# Start time
START_TIME=$(date +%s)

# Run requests in parallel
seq 1 $TOTAL_REQUESTS | xargs -P $CONCURRENT -I {} bash -c 'make_request {}'

# End time
END_TIME=$(date +%s)
TOTAL_DURATION=$((END_TIME - START_TIME))

# Calculate statistics
echo ""
echo "======================================"
echo "📊 Load Test Results"
echo "======================================"

TOTAL=$(wc -l < "${TEMP_DIR}/results.csv")
TOTAL=$((TOTAL - 1))  # exclude header
SUCCESS=$(grep -c ",200," "${TEMP_DIR}/results.csv" || echo 0)
FAILED=$((TOTAL - SUCCESS))

echo "Total Requests: $TOTAL"
echo "Successful: $SUCCESS"
echo "Failed: $FAILED"
echo "Duration: ${TOTAL_DURATION}s"
echo "Requests/sec: $(bc <<< "scale=2; $TOTAL / $TOTAL_DURATION")"
echo ""

# Response time statistics
if command -v awk &> /dev/null; then
    echo "Response Time Statistics:"
    awk -F',' 'NR>1 {sum+=$4; if(NR==2 || $4<min) min=$4; if(NR==2 || $4>max) max=$4} END {
        print "  Min: " min "ms"
        print "  Max: " max "ms"  
        print "  Avg: " int(sum/(NR-1)) "ms"
    }' "${TEMP_DIR}/results.csv"
    echo ""
fi

echo "Results saved to: ${TEMP_DIR}/results.csv"
echo ""
echo "✅ Check Grafana Dashboard for real-time metrics:"
echo "   http://localhost:3000"
echo ""

# Cleanup
# rm -rf "$TEMP_DIR"
