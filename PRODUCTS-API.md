# DummyJSON Products API Integration

Dokumentasi lengkap untuk fitur integrasi DummyJSON Products API yang memungkinkan sinkronisasi dan manajemen data produk dari [DummyJSON API](https://dummyjson.com/docs/products) ke database lokal.

## 📋 Daftar Isi

- [Overview](#overview)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Artisan Command](#artisan-command)
- [Arsitektur](#arsitektur)
- [Usage Examples](#usage-examples)

## 🎯 Overview

Fitur ini mengintegrasikan aplikasi Laravel dengan DummyJSON Products API untuk:
- ✅ Fetch produk dari external API
- ✅ Menyimpan ke database lokal
- ✅ Sync otomatis dengan progress tracking
- ✅ RESTful API endpoints untuk akses data
- ✅ Support filtering, pagination, dan search

## 🗄️ Database Schema

Tabel `products` berisi 24 kolom dengan struktur lengkap:

### Kolom Utama
- `id` - Primary key (auto increment)
- `api_id` - Unique ID dari DummyJSON API
- `title` - Nama produk
- `description` - Deskripsi produk
- `category` - Kategori produk
- `price` - Harga (decimal 8,2)
- `discount_percentage` - Persentase diskon (decimal 5,2)
- `rating` - Rating produk (decimal 3,2)
- `stock` - Jumlah stok

### Detail Produk
- `brand` - Nama brand
- `sku` - Stock Keeping Unit
- `weight` - Berat produk (decimal 8,2)
- `dimensions` - Dimensi produk (JSON: width, height, depth)

### Informasi Tambahan
- `warranty_information` - Info garansi
- `shipping_information` - Info pengiriman
- `availability_status` - Status ketersediaan

### Data Customer
- `reviews` - Review pelanggan (JSON array)
- `return_policy` - Kebijakan retur
- `minimum_order_quantity` - Minimum order

### Metadata
- `meta` - Metadata produk (JSON: barcode, qrCode, timestamps)
- `images` - Array URL gambar (JSON)
- `thumbnail` - URL thumbnail
- `tags` - Tags produk (JSON array)

### Indexes
- `api_id` - Unique index
- `category` - Index untuk filtering
- `brand` - Index untuk filtering
- `price` - Index untuk sorting
- `rating` - Index untuk sorting

## 🔌 API Endpoints

### 1. List Products

**GET** `/api/products`

Mendapatkan daftar produk dengan pagination, filtering, dan search.

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

### 2. Get Product Detail

**GET** `/api/products/{id}`

Mendapatkan detail produk berdasarkan database ID.

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

Mendapatkan daftar semua kategori yang tersedia.

**Response:**
```json
{
  "success": true,
  "data": [
    "beauty",
    "fragrances",
    "furniture",
    "groceries",
    "home-decoration",
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

Mendapatkan statistik produk.

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

Sync semua produk dari DummyJSON API ke database.

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

Sync produk tertentu berdasarkan DummyJSON API ID.

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
+----------+-------------------------------+
| Field    | Value                         |
+----------+-------------------------------+
| ID       | 1                             |
| API ID   | 1                             |
| Title    | Essence Mascara Lash Princess |
| Category | beauty                        |
| Brand    | Essence                       |
| Price    | $9.99                         |
| Stock    | 99                            |
| Rating   | 2.56                          |
+----------+-------------------------------+
```

### Custom Limit

```bash
docker-compose exec app php artisan products:sync --limit=50
```

## 🏗️ Arsitektur

### Components

```
app/
├── Models/
│   └── Product.php                 # Eloquent model dengan casts & fillable
├── Services/
│   └── DummyJsonService.php        # Service layer untuk API calls
├── Http/Controllers/
│   └── ProductController.php       # RESTful controller
└── Console/Commands/
    └── SyncProducts.php            # Artisan command untuk sync
```

### Service Layer

**DummyJsonService.php** menyediakan methods:
- `fetchProducts($limit, $skip)` - Fetch multiple products
- `fetchProduct($id)` - Fetch single product
- `fetchProductsByCategory($category)` - Filter by category
- `searchProducts($query)` - Search products
- `fetchCategories()` - Get all categories

### Model

**Product.php** features:
- Protected fillable untuk mass assignment
- JSON casting untuk: dimensions, reviews, meta, images, tags
- Decimal casting untuk: price, discount_percentage, rating, weight

### Controller

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

# Filter by category
curl http://localhost:8000/api/products?category=smartphones | jq

# Search products
curl http://localhost:8000/api/products?search=laptop | jq

# Get product detail
curl http://localhost:8000/api/products/1 | jq

# Get statistics
curl http://localhost:8000/api/products/stats | jq
```

### 3. Scheduled Sync (Optional)

Tambahkan ke `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync products setiap hari jam 2 pagi
    $schedule->command('products:sync')
             ->dailyAt('02:00')
             ->emailOutputOnFailure('admin@example.com');
}
```

### 4. Background Sync via Queue (Optional)

Untuk production, disarankan menggunakan queue:

```php
// Create Job
php artisan make:job SyncProductsJob

// Dispatch job
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

// Get products with specific dimension
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

Laravel 7 tidak memiliki method `newLine()`. Gunakan `line('')` sebagai gantinya.

### Error: "Connection timeout"

Tingkatkan timeout di `DummyJsonService.php`:
```php
Http::timeout(60)->get(...)
```

### Error: "Duplicate entry for api_id"

Gunakan `updateOrCreate()` untuk menghindari duplicate:
```php
Product::updateOrCreate(['api_id' => $id], $data);
```

## 📊 Monitoring

Endpoints produk sudah terintegrasi dengan Prometheus metrics. Monitor:
- Request count ke `/api/products`
- Response time
- Error rate

Dashboard Grafana: http://localhost:3000

## 🚀 Next Steps

1. **Add Caching**: Cache hasil query untuk performa lebih baik
2. **Add Validation**: Validasi input di controller
3. **Add Tests**: Unit & feature tests
4. **Add Queue**: Background processing untuk sync
5. **Add Events**: Dispatch events untuk product created/updated
6. **Add API Rate Limiting**: Throttle untuk API endpoints

## 📝 License

Sama dengan aplikasi utama.
