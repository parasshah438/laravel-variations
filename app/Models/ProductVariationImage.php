<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariationImage extends Model
{
    protected $fillable = [
        'product_variation_id',
        'product_image_id',
        'sort_order'
    ];

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function productImage()
    {
        return $this->belongsTo(ProductImage::class);
    }
}
