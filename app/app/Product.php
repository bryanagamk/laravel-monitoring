<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'api_id',
        'title',
        'description',
        'category',
        'price',
        'discount_percentage',
        'rating',
        'stock',
        'brand',
        'sku',
        'weight',
        'dimensions',
        'warranty_information',
        'shipping_information',
        'availability_status',
        'reviews',
        'return_policy',
        'minimum_order_quantity',
        'meta',
        'images',
        'thumbnail',
        'tags',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'rating' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'reviews' => 'array',
        'meta' => 'array',
        'images' => 'array',
        'tags' => 'array',
    ];
}
