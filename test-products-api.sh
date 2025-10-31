#!/bin/bash

# Test Products API Endpoints
# This script tests all DummyJSON Products API integration endpoints

BASE_URL="http://localhost:8000"
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=================================================="
echo "Testing DummyJSON Products API Integration"
echo "=================================================="
echo ""

# Test 1: Check if products table has data
echo -e "${YELLOW}Test 1: Checking database...${NC}"
PRODUCT_COUNT=$(docker-compose exec -T mysql mysql -u laravel -ppassword -D laravel -se "SELECT COUNT(*) FROM products;" 2>/dev/null)
if [ "$PRODUCT_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ Database has $PRODUCT_COUNT products${NC}"
else
    echo -e "${RED}✗ Database is empty. Run: docker-compose exec app php artisan products:sync${NC}"
    exit 1
fi
echo ""

# Test 2: Get Products List
echo -e "${YELLOW}Test 2: GET /api/products${NC}"
RESPONSE=$(curl -s "$BASE_URL/api/products")

if [ $? -eq 0 ]; then
    TOTAL=$(echo "$RESPONSE" | jq -r '.total')
    PER_PAGE=$(echo "$RESPONSE" | jq -r '.per_page')
    echo -e "${GREEN}✓ Success${NC}"
    echo "  Total products: $TOTAL"
    echo "  Per page: $PER_PAGE"
    echo "  Sample product:"
    echo "$RESPONSE" | jq -r '.data[0] | "    - \(.title) (\(.category)) - $\(.price)"'
else
    echo -e "${RED}✗ Failed${NC}"
fi
echo ""

# Test 3: Get Product Detail
echo -e "${YELLOW}Test 3: GET /api/products/1${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products/1")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    SUCCESS=$(echo "$BODY" | jq -r '.success')
    if [ "$SUCCESS" = "true" ]; then
        TITLE=$(echo "$BODY" | jq -r '.data.title')
        PRICE=$(echo "$BODY" | jq -r '.data.price')
        STOCK=$(echo "$BODY" | jq -r '.data.stock')
        echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
        echo "  Product: $TITLE"
        echo "  Price: \$$PRICE"
        echo "  Stock: $STOCK"
    else
        echo -e "${RED}✗ Success: false${NC}"
    fi
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

# Test 4: Get Categories
echo -e "${YELLOW}Test 4: GET /api/products/categories${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products/categories")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    SUCCESS=$(echo "$BODY" | jq -r '.success')
    if [ "$SUCCESS" = "true" ]; then
        CATEGORY_COUNT=$(echo "$BODY" | jq -r '.data | length')
        echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
        echo "  Total categories: $CATEGORY_COUNT"
        echo "  Sample categories:"
        echo "$BODY" | jq -r '.data[:5][] | "    - \(.)"'
    else
        echo -e "${RED}✗ Success: false${NC}"
    fi
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

# Test 5: Get Statistics
echo -e "${YELLOW}Test 5: GET /api/products/stats${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products/stats")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    SUCCESS=$(echo "$BODY" | jq -r '.success')
    if [ "$SUCCESS" = "true" ]; then
        echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
        echo "  Statistics:"
        echo "$BODY" | jq -r '.data | to_entries[] | "    - \(.key): \(.value)"'
    else
        echo -e "${RED}✗ Success: false${NC}"
    fi
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

# Test 6: Filter by Category
echo -e "${YELLOW}Test 6: GET /api/products?category=beauty${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products?category=beauty")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    TOTAL=$(echo "$BODY" | jq -r '.total')
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "  Beauty products: $TOTAL"
    echo "  Sample:"
    echo "$BODY" | jq -r '.data[0] | "    - \(.title) - $\(.price)"'
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

# Test 7: Search Products
echo -e "${YELLOW}Test 7: GET /api/products?search=phone${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products?search=phone")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    TOTAL=$(echo "$BODY" | jq -r '.total')
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "  Search results: $TOTAL"
    if [ "$TOTAL" -gt 0 ]; then
        echo "  Sample:"
        echo "$BODY" | jq -r '.data[0] | "    - \(.title) - $\(.price)"'
    fi
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

# Test 8: Pagination
echo -e "${YELLOW}Test 8: GET /api/products?per_page=5&page=2${NC}"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/products?per_page=5&page=2")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)

if [ "$HTTP_CODE" = "200" ]; then
    CURRENT_PAGE=$(echo "$BODY" | jq -r '.current_page')
    PER_PAGE=$(echo "$BODY" | jq -r '.per_page')
    TOTAL=$(echo "$BODY" | jq -r '.total')
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "  Current page: $CURRENT_PAGE"
    echo "  Per page: $PER_PAGE"
    echo "  Total: $TOTAL"
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
fi
echo ""

echo "=================================================="
echo -e "${GREEN}All tests completed!${NC}"
echo "=================================================="
echo ""
echo "Available Commands:"
echo "  # Sync all products"
echo "  docker-compose exec app php artisan products:sync"
echo ""
echo "  # Sync single product"
echo "  docker-compose exec app php artisan products:sync --id=1"
echo ""
echo "  # View products in database"
echo "  docker-compose exec app php artisan tinker"
echo "  >>> App\\Product::count()"
echo "  >>> App\\Product::first()"
echo ""
