# DummyJSON Products API Integration

Complete documentation for the DummyJSON Products API integration that enables synchronization and management of product data from the [DummyJSON API](https://dummyjson.com/docs/products) to the local database.

## 📋 Table of Contents

- [Overview](#overview)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Artisan Command](#artisan-command)
- [Architecture](#architecture)
- [Usage Examples](#usage-examples)

## 🎯 Overview

This feature integrates the Laravel application with the DummyJSON Products API to:
- ✅ Fetch products from the external API
- ✅ Store them into the local database
- ✅ Automatic sync with progress tracking
- ✅ Provide RESTful API endpoints for data access
- ✅ Support filtering, pagination, and search

## 🗄️ Database Schema

The `products` table contains 24 columns with a complete structure:

### Main Column
- `id` - Primary key (auto-increment)
- `api_id` - Unique ID from the DummyJSON API
- `title` - Product name
- `description` - Product description
- `category` - Product category
- `price` - Price (decimal 8.2)
- `discount_percentage` - Discount percentage (decimal 5.2)
- `rating` - Product rating (decimal 3.2)
- `stock` - Stock quantity

### Product Details
- `brand` - Brand name
- `sku` - Stock Keeping Unit
- `weight` - Product weight (decimal 8.2)
- `dimensions` - Product dimensions (JSON: width, height, depth)

### Additional Information
- `warranty_information` - Warranty information
- `shipping_information` - Shipping information
- `availability_status` - Availability status

### Customer Data
- `reviews` - ​​Customer reviews (JSON array)
- `return_policy` - Return policy
- `minimum_order_quantity` - Minimum order

### Metadata
- `meta` - Product metadata (JSON: barcode, QR code, timestamps)
- `images` - Image URL array (JSON)
- `thumbnail` - Thumbnail URL
- `tags` - Product tags (JSON array)

### Indexes
- `api_id` - Unique index
- `category` - Index for filtering
- `brand` - Index for filtering
- `price` - Index for sorting
- `rating` - Index for sorting

## 🔌 API Endpoints

### 1. List Products

**GET** `/api/products`

Gets a list of products with pagination, filtering, and search.

**Query Parameters:**
- `per_page` (default: 15) - Items per page
- `category` - Filter by category
- `search` - Search in title, description, brand

**Response:**
```json
{ 
"current_page": 1, 
"data": [ 
{ 
"id": 1, 
"api_id": 1, 
"title": "Essence Mascara Lash Princess", 
"description": "...", 
"category": "beauty", 
"price": "9.99", 
"discount_percentage": "7.17", 
"rating": "4.94", 
"stock": 5, 
"brand": "Essence", 
"dimensions": { 
"width": 23.17, 
"height": 14.43, 
"depth": 28.01 
}, 
"images": [...], 
"reviews": [...], 
"tags": [...] 
} 
], 
"per_page": 15, 
"total": 194
}
```

**Examples:**
```bash
# List all products
curl http://localhost:8000/api/products

# Filter by category
curl http://localhost:8000/api/products?category=beauty

# Search products
curl http://localhost:8000/api/products?search=phone

# Pagination
curl http://localhost:8000/api/products?per_page=20&page=2
```

### 2. Get Product Details

**GET** `/api/products/{id}`

Get product details based on ID database.

**Response:**
```json
{ 
"success": true, 
"data": { 
"id": 1, 
"api_id": 1, 
"title": "Essence Mascara Lash Princess", 
"description": "...", 
"price": "9.99", 
"stock": 5, 
... 
}
}
```

**Example:**
```bash
curl http://localhost:8000/api/products/1
```

### 3. Get Categories

**GET** `/api/products/categories`

Get the list of all available categories.

**Response:**
```json
{ 
"success": true, 
"data": [ 
"beauty", 
"fragrances", 
"furniture", 
"groceries", 
"home decoration", 
... 
]
}
```

**Example:**
```bash
curl http://localhost:8000/api/products/categories
```

### 4. Get Statistics

**GET** `/api/products/stats`

Get product statistics.

**Response:**
```json
{ 
"success": true, 
"data": { 
"total_products": 194, 
"total_categories": 24, 
"total_brands": 63, 
"average_price": "1570.10", 
"average_rating": "3.80", 
"total_stock": "9779", 
"out_of_stock": 4 
}
}
```

**Example:**
```bash
curl http://localhost:8000/api/products/stats
```

### 5. Sync All Products

**POST** `/api/products/sync`

Sync all products from DummyJSON API to database.

**Response:**
```json
{ 
"success": true, 
"message": "Successfully synced 194 products", 
"total": 194
}
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/products/sync
```

### 6. Sync Single Product

**POST** `/api/products/sync/{apiId}`

Sync specific products based on DummyJSON API ID.

**Response:**
```json
{ 
"success": true, 
"message": "Product synced successfully", 
"data": { 
"id": 1, 
"api_id": 1, 
"title": "Essence Mascara Lash Princess", 
... 
}
}
```
**Example:**
```bash
curl -X POST http://localhost:8000/api/products/sync/1
```

## ⌨️ Artisan Command

### Sync All Products

```bash
docker-compose exec app php artisan products:sync
```

Output:
```
Starting to sync products from DummyJSON API... 
194/194 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
Successfully synced 194 products!
```

### Sync Single Product

```bash
docker-compose exec app php artisan products:sync --id=1
```

Output:
```
Syncing product with API ID: 1
Successfully synced product: Essence Mascara Lash Princess
+----------+----------------------+
| Fields | Value |
+----------+----------------------+
| ID | 1 |
| API ID | 1 |
| Title | Essence Mascara Lash Princess |
| Categories | beauty |
| Brands | Essence |
| Price | $9.99 |
| Stock | 99 |
| Ratings | 2.56 |
+----------+----------------------+
```

### Custom Limits

```bash
docker-compose exec app php artisan products:sync --limit=50
```

## 🏗️ Architecture

### Components

```
app/
├── Models/
│ └── Product.php # Eloquent models with casts & fillables
├── Services/
│ └── DummyJsonService.php # Service layer for API calls
├── Http/Controllers/
│ └── ProductController.php # RESTful controller
└── Console/Commands/ 
└── SyncProducts.php # Artisan command for sync
```

### Service Layer

**DummyJsonService.php** provides methods:
- `fetchProducts($limit, $skip)` - Fetch multiple products
- `fetchProduct($id)` - Fetch single product
- `fetchProductsByCategory($category)` - Filter by category
- `searchProducts($query)` - Search products
- `fetchCategories()` - Get all categories

### Models

**Product.php** features:
- Protected fillable for mass assignment
- JSON casting for: dimensions, reviews, meta, images, tags
- Decimal casting for: price, discount_percentage, rating, weight

### Controllers

**ProductController.php** provides:
- RESTful methods (index, show)
- Sync methods (sync, syncOne)
- Helper methods (categories, stats)
- Data transformation from API to database

## 📚 Usage Examples

### 1. Initial Sync

```bash
# Run migration
docker-compose exec app php artisan migrate

# Sync all products
docker-compose exec app php artisan products:sync
```

### 2. Get Products via API

```bash
# Get all products
curl http://localhost:8000/api/products | jq

# Filter by categories
curl http://localhost:8000/api/products?category=smartphones | jq

# Search products
curl http://localhost:8000/api/products?search=laptop | jq

# Get product details
curl http://localhost:8000/api/products/1 | jq

# Get statistics
curl http://localhost:8000/api/products/stats | jq
```

### 3. Scheduled Sync (Optional)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{ 
// Sync products daily at 02:00 
$schedule->command('products:sync') 
->dailyAt('02:00') 
->emailOutputOnFailure('admin@example.com');
}
```

### 4. Background Sync via Queue (Optional)

For production, it's recommended to use a queue:

```php
// Create Job
php artisan make:job SyncProductsJob

// Dispatch jobs
dispatch(new SyncProductsJob());
```

## 🔍 Advanced Queries

### Filter & Sort

```php
// In your custom controller or service
$products = Product::where('category', 'electronics') 
->where('stock', '>', 0) 
->where('price', '<', 1000) 
->orderBy('rating', 'desc') 
->get();
```

### JSON Queries

```php
// Search in JSON fields (MySQL 5.7+)
$products = Product::whereJsonContains('tags', 'laptop')->get();

// Get products with specific dimensions
$products = Product::where('dimensions->width', '>', 50)->get();
```

### Aggregations

```php
// Category statistics
$stats = Product::select('category') 
->selectRaw('COUNT(*) as total') 
->selectRaw('AVG(price) as avg_price') 
->selectRaw('SUM(stock) as total_stock') 
->groupBy('category') 
->get();
```

## 🐛 Troubleshooting

### Error: "Method newLine does not exist"

Laravel 7 does not have the `newLine()` method. Use `line('')` instead.

### Error: "Connection timed out"

Increase timeout in `DummyJsonService.php`:
```php
Http::timeout(60)->get(...)
```

### Error: "Duplicate entry for api_id"

Use `updateOrCreate()` to avoid duplicates:
```php
Product::updateOrCreate(['api_id' => $id], $data);
```

## 📊 Monitoring

Product endpoints are integrated with Prometheus metrics. Monitors:
- Request count to `/api/products`
- Response time
- Error rate

Grafana Dashboard: http://localhost:3000

## 🚀 Next Steps

1. **Add Caching**: Cache query results for better performance
2. **Add Validation**: Validate input in the controller
3. **Add Tests**: Unit & feature tests
4. **Add Queue**: Background processing for sync
5. **Add Events**: Dispatch events for product created/updated
6. **Add API Rate Limiting**: Throttle for API endpoints

## 📝 License

Same as the main application.