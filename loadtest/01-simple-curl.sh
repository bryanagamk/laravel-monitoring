#!/bin/bash

# =========================================
# Level 1: Simple Curl Loop
# Paling sederhana - sequential requests
# =========================================

echo "🔄 Simple Curl Loop Load Test"
echo "======================================"
echo ""

# Configuration
BASE_URL="http://localhost:8000"
REQUESTS=100
DELAY=0.1  # seconds between requests

echo "Configuration:"
echo "  Base URL: $BASE_URL"
echo "  Total Requests: $REQUESTS"
echo "  Delay: ${DELAY}s"
echo ""

# Test endpoints
ENDPOINTS=(
    "/test/simple"
    "/test/cpu"
    "/test/memory"
    "/test/database"
    "/test/cache"
)

echo "🚀 Starting load test..."
echo ""

START_TIME=$(date +%s)
SUCCESS=0
FAILED=0

for i in $(seq 1 $REQUESTS); do
    # Random endpoint
    ENDPOINT=${ENDPOINTS[$RANDOM % ${#ENDPOINTS[@]}]}
    URL="${BASE_URL}${ENDPOINT}"
    
    # Make request
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL")
    
    if [ "$HTTP_CODE" = "200" ]; then
        SUCCESS=$((SUCCESS + 1))
        echo -ne "✓ Request $i/$REQUESTS - ${ENDPOINT} - HTTP $HTTP_CODE\r"
    else
        FAILED=$((FAILED + 1))
        echo "✗ Request $i/$REQUESTS - ${ENDPOINT} - HTTP $HTTP_CODE (FAILED)"
    fi
    
    # Delay between requests
    sleep $DELAY
done

END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo ""
echo "======================================"
echo "📊 Load Test Results"
echo "======================================"
echo "Total Requests: $REQUESTS"
echo "Successful: $SUCCESS"
echo "Failed: $FAILED"
echo "Duration: ${DURATION}s"
echo "Requests/sec: $(bc <<< "scale=2; $REQUESTS / $DURATION")"
echo ""
echo "✅ Check Grafana Dashboard:"
echo "   http://localhost:3000"
echo ""
