<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'based_on_product_id',
        'recommendation_type',
        'confidence_score',
        'reasoning',
        'expires_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:4',
        'reasoning' => 'array',
        'expires_at' => 'datetime',
    ];

    // Recommendation types constants
    const TYPE_COLLABORATIVE = 'collaborative';      // Based on similar users
    const TYPE_CONTENT_BASED = 'content_based';     // Based on product attributes
    const TYPE_CROSS_SELL = 'cross_sell';           // Frequently bought together
    const TYPE_UPSELL = 'upsell';                   // Higher value alternatives
    const TYPE_TRENDING = 'trending';               // Popular products
    const TYPE_RECENTLY_VIEWED = 'recently_viewed'; // Based on view history
    const TYPE_CATEGORY_BASED = 'category_based';   // Same category products

    /**
     * Get the user this recommendation is for
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recommended product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product this recommendation is based on
     */
    public function basedOnProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'based_on_product_id');
    }

    /**
     * Scope for specific recommendation type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('recommendation_type', $type);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope for valid (non-expired) recommendations
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for high confidence recommendations
     */
    public function scopeHighConfidence($query, $minScore = 0.5)
    {
        return $query->where('confidence_score', '>=', $minScore);
    }

    /**
     * Order by confidence score
     */
    public function scopeOrderByConfidence($query, $direction = 'desc')
    {
        return $query->orderBy('confidence_score', $direction);
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecommendation extends Model
{
    //
}
