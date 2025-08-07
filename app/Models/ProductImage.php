<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 
        'product_variation_id', 
        'variation_attribute_value_id',
        'image_path', 
        'is_main',
        'sort_order'
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function variations()
    {
        return $this->belongsToMany(ProductVariation::class, 'product_variation_images')
                    ->withPivot('sort_order')
                    ->orderBy('product_variation_images.sort_order');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'variation_attribute_value_id');
    }
}
