<?php

namespace App\Console\Commands;

use App\Product;
use App\Services\DummyJsonService;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync {--id= : Sync specific product by API ID} {--limit=100 : Limit per request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products from DummyJSON API to database';

    protected $dummyJsonService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DummyJsonService $dummyJsonService)
    {
        parent::__construct();
        $this->dummyJsonService = $dummyJsonService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiId = $this->option('id');

        if ($apiId) {
            return $this->syncOne($apiId);
        }

        return $this->syncAll();
    }

    /**
     * Sync all products
     *
     * @return int
     */
    protected function syncAll()
    {
        $this->info('Starting to sync products from DummyJSON API...');

        $limit = (int) $this->option('limit');
        $skip = 0;
        $totalSynced = 0;
        $hasMore = true;

        $progressBar = null;

        while ($hasMore) {
            $response = $this->dummyJsonService->fetchProducts($limit, $skip);
            
            if (!$response || !isset($response['products'])) {
                $this->error('Failed to fetch products from API');
                break;
            }

            $total = $response['total'] ?? 0;

            // Initialize progress bar on first iteration
            if ($progressBar === null) {
                $progressBar = $this->output->createProgressBar($total);
                $progressBar->start();
            }

            foreach ($response['products'] as $productData) {
                try {
                    $this->saveProduct($productData);
                    $totalSynced++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $this->error("\nError saving product ID {$productData['id']}: {$e->getMessage()}");
                }
            }

            // Check if there are more products
            $skip += $limit;
            $hasMore = $skip < $total;
        }

        if ($progressBar) {
            $progressBar->finish();
            $this->line('');
        }

        $this->info("Successfully synced {$totalSynced} products!");
        
        return 0;
    }

    /**
     * Sync single product
     *
     * @param int $apiId
     * @return int
     */
    protected function syncOne($apiId)
    {
        $this->info("Syncing product with API ID: {$apiId}");

        $productData = $this->dummyJsonService->fetchProduct($apiId);

        if (!$productData) {
            $this->error("Product not found in DummyJSON API");
            return 1;
        }

        try {
            $product = $this->saveProduct($productData);
            $this->info("Successfully synced product: {$product->title}");
            
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $product->id],
                    ['API ID', $product->api_id],
                    ['Title', $product->title],
                    ['Category', $product->category],
                    ['Brand', $product->brand],
                    ['Price', '$' . $product->price],
                    ['Stock', $product->stock],
                    ['Rating', $product->rating],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Error syncing product: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Save or update product from API data
     *
     * @param array $productData
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
}
