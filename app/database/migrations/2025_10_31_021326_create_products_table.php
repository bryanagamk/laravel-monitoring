<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique(); // ID from DummyJSON API
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('brand')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable(); // width, height, depth
            $table->string('warranty_information')->nullable();
            $table->string('shipping_information')->nullable();
            $table->string('availability_status')->nullable();
            $table->json('reviews')->nullable(); // Array of reviews
            $table->string('return_policy')->nullable();
            $table->integer('minimum_order_quantity')->default(1);
            $table->json('meta')->nullable(); // createdAt, updatedAt, barcode, qrCode
            $table->json('images')->nullable(); // Array of image URLs
            $table->string('thumbnail')->nullable();
            $table->json('tags')->nullable(); // Array of tags
            $table->timestamps();
            
            // Indexes
            $table->index('category');
            $table->index('brand');
            $table->index('price');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
