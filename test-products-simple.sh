#!/bin/bash

# Simple Products API Test
# Tests all DummyJSON Products API integration endpoints

BASE_URL="http://localhost:8000"

echo "=================================================="
echo "Testing DummyJSON Products API Integration"
echo "=================================================="
echo ""

# Test 1: Database Check
echo "Test 1: Checking database..."
PRODUCT_COUNT=$(docker-compose exec -T mysql mysql -u laravel -ppassword -D laravel -se "SELECT COUNT(*) FROM products;" 2>/dev/null)
echo "✓ Database has $PRODUCT_COUNT products"
echo ""

# Test 2: Get Products List
echo "Test 2: GET /api/products"
curl -s "$BASE_URL/api/products" | jq '{total, per_page, current_page, sample: .data[0].title}'
echo ""

# Test 3: Get Product Detail
echo "Test 3: GET /api/products/1"
curl -s "$BASE_URL/api/products/1" | jq '{success, title: .data.title, price: .data.price, stock: .data.stock}'
echo ""

# Test 4: Get Categories
echo "Test 4: GET /api/products/categories"
curl -s "$BASE_URL/api/products/categories" | jq '{success, total: (.data | length), categories: .data[:5]}'
echo ""

# Test 5: Get Statistics
echo "Test 5: GET /api/products/stats"
curl -s "$BASE_URL/api/products/stats" | jq
echo ""

# Test 6: Filter by Category
echo "Test 6: GET /api/products?category=beauty"
curl -s "$BASE_URL/api/products?category=beauty" | jq '{total, sample: .data[0].title}'
echo ""

# Test 7: Search Products
echo "Test 7: GET /api/products?search=phone"
curl -s "$BASE_URL/api/products?search=phone" | jq '{total, results: [.data[].title][:3]}'
echo ""

# Test 8: Pagination
echo "Test 8: GET /api/products?per_page=5&page=2"
curl -s "$BASE_URL/api/products?per_page=5&page=2" | jq '{current_page, per_page, total, products: [.data[].title]}'
echo ""

echo "=================================================="
echo "All tests completed!"
echo "=================================================="
echo ""
echo "Available Commands:"
echo "  docker-compose exec app php artisan products:sync"
echo "  docker-compose exec app php artisan products:sync --id=1"
echo ""
