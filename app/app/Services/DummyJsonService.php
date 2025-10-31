<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DummyJsonService
{
    protected $baseUrl = 'https://dummyjson.com';

    /**
     * Fetch all products from DummyJSON API
     * 
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function fetchProducts($limit = 100, $skip = 0)
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/products", [
                'limit' => $limit,
                'skip' => $skip,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('DummyJSON API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('DummyJSON API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch single product by ID from DummyJSON API
     * 
     * @param int $id
     * @return array|null
     */
    public function fetchProduct($id)
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/products/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('DummyJSON API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('DummyJSON API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch products by category
     * 
     * @param string $category
     * @return array|null
     */
    public function fetchProductsByCategory($category)
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/products/category/{$category}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DummyJSON API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Search products
     * 
     * @param string $query
     * @return array|null
     */
    public function searchProducts($query)
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/products/search", [
                'q' => $query
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DummyJSON API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get all categories
     * 
     * @return array|null
     */
    public function fetchCategories()
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/products/categories");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DummyJSON API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
