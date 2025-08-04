<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'category_id', 'brand_id', 'status', 'image'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function recentlyViewed(): HasMany
    {
        return $this->hasMany(RecentlyViewedProduct::class);
    }

    public function mainImage()
    {
        return $this->images()->where('is_main', true)->first();
    }

    public function minPrice()
    {
        return $this->variations()->min('price');
    }

    public function maxPrice()
    {
        return $this->variations()->max('price');
    }
}
