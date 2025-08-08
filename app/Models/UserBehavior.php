<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBehavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'behavior_type',
        'metadata',
        'value',
        'behavior_timestamp',
    ];

    protected $casts = [
        'metadata' => 'array',
        'value' => 'decimal:2',
        'behavior_timestamp' => 'datetime',
    ];

    // Behavior types constants
    const BEHAVIOR_VIEW = 'view';
    const BEHAVIOR_CART_ADD = 'cart_add';
    const BEHAVIOR_CART_REMOVE = 'cart_remove';
    const BEHAVIOR_WISHLIST_ADD = 'wishlist_add';
    const BEHAVIOR_WISHLIST_REMOVE = 'wishlist_remove';
    const BEHAVIOR_PURCHASE = 'purchase';
    const BEHAVIOR_SEARCH = 'search';
    const BEHAVIOR_RATING = 'rating';

    /**
     * Get the user that performed this behavior
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product this behavior is related to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for specific behavior types
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('behavior_type', $type);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific session (guest users)
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope for recent behaviors
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('behavior_timestamp', '>=', now()->subDays($days));
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBehavior extends Model
{
    //
}
