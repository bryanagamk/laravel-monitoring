#!/bin/bash

# Test Third Party API Monitoring
# This script tests the API health monitoring system

BASE_URL="http://localhost:8000"
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=================================================="
echo "Testing Third Party API Monitoring"
echo "=================================================="
echo ""

# Test 1: Check API Health via Artisan
echo -e "${YELLOW}Test 1: Artisan Health Check${NC}"
docker-compose exec -T app php artisan api:check-health
echo ""

# Test 2: Get API Health Status
echo -e "${YELLOW}Test 2: GET /api/health${NC}"
curl -s "$BASE_URL/api/health" | jq
echo ""

# Test 3: Force Fresh Check
echo -e "${YELLOW}Test 3: GET /api/health/check${NC}"
curl -s "$BASE_URL/api/health/check" | jq '.data | {status, response_time_ms, status_code}'
echo ""

# Test 4: Comprehensive Check
echo -e "${YELLOW}Test 4: GET /api/health/comprehensive${NC}"
curl -s "$BASE_URL/api/health/comprehensive" | jq '.data | {overall_status, endpoints}'
echo ""

# Test 5: Get Statistics
echo -e "${YELLOW}Test 5: GET /api/health/stats${NC}"
curl -s "$BASE_URL/api/health/stats" | jq
echo ""

# Test 6: Get History
echo -e "${YELLOW}Test 6: GET /api/health/history${NC}"
curl -s "$BASE_URL/api/health/history" | jq '{uptime_percentage: .data.uptime_percentage, total_checks: .data.total_checks}'
echo ""

# Test 7: Run multiple checks
echo -e "${YELLOW}Test 7: Running 5 health checks...${NC}"
for i in {1..5}; do
    docker-compose exec -T app php artisan api:check-health > /dev/null 2>&1
    echo "  Check $i completed"
    sleep 1
done
echo ""

# Test 8: Final Statistics
echo -e "${YELLOW}Test 8: Final Statistics${NC}"
curl -s "$BASE_URL/api/health/stats" | jq
echo ""

# Test 9: Check Prometheus Metrics
echo -e "${YELLOW}Test 9: Checking Prometheus Metrics${NC}"
echo "Looking for third_party_api metrics..."
METRICS=$(curl -s "$BASE_URL/metrics")

if echo "$METRICS" | grep -q "third_party_api_up"; then
    echo -e "${GREEN}✓ third_party_api_up metric found${NC}"
    echo "$METRICS" | grep "third_party_api_up"
else
    echo -e "${RED}✗ third_party_api_up metric not found${NC}"
    echo "Note: Metrics might not persist with InMemory storage between requests"
fi
echo ""

if echo "$METRICS" | grep -q "third_party_api_response_time_ms"; then
    echo -e "${GREEN}✓ third_party_api_response_time_ms metric found${NC}"
    echo "$METRICS" | grep "third_party_api_response_time_ms"
else
    echo -e "${RED}✗ third_party_api_response_time_ms metric not found${NC}"
fi
echo ""

echo "=================================================="
echo -e "${GREEN}Monitoring tests completed!${NC}"
echo "=================================================="
echo ""
echo "Dashboard URLs:"
echo "  - Grafana: http://localhost:3000"
echo "  - Dashboard: 'Third Party API Monitoring (DummyJSON)'"
echo "  - Prometheus: http://localhost:9090"
echo ""
echo "Available Commands:"
echo "  # Manual health check"
echo "  docker-compose exec app php artisan api:check-health"
echo ""
echo "  # Comprehensive check"
echo "  docker-compose exec app php artisan api:check-health --comprehensive"
echo ""
echo "  # View metrics"
echo "  curl http://localhost:8000/api/health/stats | jq"
echo ""
