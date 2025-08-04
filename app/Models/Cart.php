<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'guest_token', 'product_variation_id', 'qty'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->qty * $this->productVariation->price;
    }
}
