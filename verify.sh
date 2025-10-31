#!/bin/bash

echo "🚀 Laravel Monitoring - Quick Verification"
echo "=========================================="
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check containers
echo "📦 Checking containers..."
docker-compose ps

echo ""
echo "🔍 Checking endpoints..."
echo ""

# Check Laravel
echo -n "Laravel App (http://localhost:8000): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check Laravel Metrics
echo -n "Laravel Metrics (http://localhost:8000/metrics): "
if curl -s http://localhost:8000/metrics | grep -q "laravel_app"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check Prometheus
echo -n "Prometheus (http://localhost:9090): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:9090 | grep -q "200"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check Grafana
echo -n "Grafana (http://localhost:3000): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:3000 | grep -q "200\|302"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check Node Exporter
echo -n "Node Exporter (http://localhost:9100): "
if curl -s http://localhost:9100/metrics | grep -q "node_"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check cAdvisor
echo -n "cAdvisor (http://localhost:8080): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200\|301"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

# Check MySQL Exporter
echo -n "MySQL Exporter (http://localhost:9104): "
if curl -s http://localhost:9104/metrics | grep -q "mysql"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${YELLOW}⚠ WARNING (may need time to connect)${NC}"
fi

# Check Nginx Exporter
echo -n "Nginx Exporter (http://localhost:9113): "
if curl -s http://localhost:9113/metrics | grep -q "nginx"; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC}"
fi

echo ""
echo "📊 Prometheus Targets Status:"
echo "Check: http://localhost:9090/targets"
echo ""

echo "📈 Grafana Dashboard:"
echo "1. Open: http://localhost:3000"
echo "2. Login: admin / admin"
echo "3. Dashboard: 'Laravel Monitoring Dashboard'"
echo ""

echo "✨ Sample metrics from Laravel:"
curl -s http://localhost:8000/metrics | grep "laravel_app" | head -10
echo ""

echo "=========================================="
echo "🎉 Verification Complete!"
echo "=========================================="
