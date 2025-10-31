<?php

namespace App\Http\Controllers;

use App\Product;
use App\Services\DummyJsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $dummyJsonService;

    public function __construct(DummyJsonService $dummyJsonService)
    {
        $this->dummyJsonService = $dummyJsonService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $category = $request->get('category');
        $search = $request->get('search');

        $query = Product::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Sync products from DummyJSON API to database
     *
     * @return \Illuminate\Http\Response
     */
    public function sync()
    {
        try {
            $limit = 100;
            $skip = 0;
            $totalSynced = 0;
            $hasMore = true;

            while ($hasMore) {
                $response = $this->dummyJsonService->fetchProducts($limit, $skip);
                
                if (!$response || !isset($response['products'])) {
                    break;
                }

                foreach ($response['products'] as $productData) {
                    $this->saveProduct($productData);
                    $totalSynced++;
                }

                // Check if there are more products
                $total = $response['total'] ?? 0;
                $skip += $limit;
                $hasMore = $skip < $total;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$totalSynced} products",
                'total' => $totalSynced
            ]);

        } catch (\Exception $e) {
            Log::error('Product sync error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync single product from DummyJSON API
     *
     * @param  int  $apiId
     * @return \Illuminate\Http\Response
     */
    public function syncOne($apiId)
    {
        try {
            $productData = $this->dummyJsonService->fetchProduct($apiId);

            if (!$productData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found in DummyJSON API'
                ], 404);
            }

            $product = $this->saveProduct($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product synced successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Product sync error', [
                'api_id' => $apiId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        $categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Save or update product from API data
     *
     * @param  array  $productData
     * @return Product
     */
    protected function saveProduct($productData)
    {
        return Product::updateOrCreate(
            ['api_id' => $productData['id']],
            [
                'title' => $productData['title'] ?? null,
                'description' => $productData['description'] ?? null,
                'category' => $productData['category'] ?? null,
                'price' => $productData['price'] ?? 0,
                'discount_percentage' => $productData['discountPercentage'] ?? 0,
                'rating' => $productData['rating'] ?? 0,
                'stock' => $productData['stock'] ?? 0,
                'brand' => $productData['brand'] ?? null,
                'sku' => $productData['sku'] ?? null,
                'weight' => $productData['weight'] ?? null,
                'dimensions' => $productData['dimensions'] ?? null,
                'warranty_information' => $productData['warrantyInformation'] ?? null,
                'shipping_information' => $productData['shippingInformation'] ?? null,
                'availability_status' => $productData['availabilityStatus'] ?? null,
                'reviews' => $productData['reviews'] ?? null,
                'return_policy' => $productData['returnPolicy'] ?? null,
                'minimum_order_quantity' => $productData['minimumOrderQuantity'] ?? null,
                'meta' => $productData['meta'] ?? null,
                'images' => $productData['images'] ?? null,
                'thumbnail' => $productData['thumbnail'] ?? null,
                'tags' => $productData['tags'] ?? null,
            ]
        );
    }

    /**
     * Get statistics
     *
     * @return \Illuminate\Http\Response
     */
    public function stats()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Product::distinct('category')->count(),
            'total_brands' => Product::whereNotNull('brand')->distinct('brand')->count(),
            'average_price' => Product::avg('price'),
            'average_rating' => Product::avg('rating'),
            'total_stock' => Product::sum('stock'),
            'out_of_stock' => Product::where('stock', 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
