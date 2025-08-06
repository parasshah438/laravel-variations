<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 
        'sku', 
        'price', 
        'stock', 
        'weight', 
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
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
        // Use attribute values to generate name
        if ($this->attributeValues && $this->attributeValues->count() > 0) {
            return $this->attributeValues->sortBy('attribute.name')->map(function($attributeValue) {
                return $attributeValue->value;
            })->implode(' / ');
        }
        
        // Fallback if no attributes
        return "Variation #{$this->id}";
    }
    
    public function getFullVariationNameAttribute(): string
    {
        // Detailed version with attribute names
        if ($this->attributeValues && $this->attributeValues->count() > 0) {
            return $this->attributeValues->sortBy('attribute.name')->map(function($attributeValue) {
                return $attributeValue->attribute->name . ': ' . $attributeValue->value;
            })->implode(' | ');
        }
        
        return "Variation #{$this->id}";
    }
}
