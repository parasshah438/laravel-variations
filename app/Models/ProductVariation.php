<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariation extends Model
{
    protected $fillable = ['product_id', 'size', 'color', 'fabric', 'price', 'stock'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variation_attribute_values');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function getVariationNameAttribute(): string
    {
        // If we have attribute values, use them
        if ($this->attributeValues && $this->attributeValues->count() > 0) {
            return $this->attributeValues->map(function($attributeValue) {
                return $attributeValue->attribute->name . ': ' . $attributeValue->value;
            })->implode(' | ');
        }
        
        // Otherwise, use the old system
        $name = [];
        if ($this->size) $name[] = $this->size;
        if ($this->color) $name[] = $this->color;
        if ($this->fabric) $name[] = $this->fabric;
        return implode(' - ', $name);
    }
}
