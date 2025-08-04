<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'address', 
        'total', 
        'status', 
        'payment_method',
        // Delivery options
        'delivery_speed',
        'delivery_date',
        'delivery_time_slot',
        'delivery_instructions',
        // Gift options
        'is_gift',
        'gift_wrap',
        'gift_message',
        'gift_recipient_name',
        // Communication preferences
        'sms_updates',
        'email_updates',
        // Additional charges
        'delivery_charge',
        'gift_wrap_charge'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_date' => 'date',
        'is_gift' => 'boolean',
        'gift_wrap' => 'boolean',
        'sms_updates' => 'boolean',
        'email_updates' => 'boolean',
        'delivery_charge' => 'decimal:2',
        'gift_wrap_charge' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the delivery speed display name
     */
    public function getDeliverySpeedDisplayAttribute(): string
    {
        return match($this->delivery_speed) {
            'standard' => 'Standard Delivery',
            'express' => 'Express Delivery',
            'same_day' => 'Same Day Delivery',
            default => 'Standard Delivery'
        };
    }

    /**
     * Get the delivery time slot display name
     */
    public function getDeliveryTimeSlotDisplayAttribute(): ?string
    {
        return match($this->delivery_time_slot) {
            'morning' => 'Morning (9 AM - 12 PM)',
            'afternoon' => 'Afternoon (12 PM - 4 PM)',
            'evening' => 'Evening (4 PM - 8 PM)',
            'night' => 'Night (6 PM - 9 PM)',
            default => null
        };
    }

    /**
     * Get estimated delivery date based on delivery speed
     */
    public function getEstimatedDeliveryAttribute(): string
    {
        if ($this->delivery_date) {
            return $this->delivery_date->format('M d, Y');
        }

        $days = match($this->delivery_speed) {
            'standard' => '5-7 business days',
            'express' => '2-3 business days',
            'same_day' => 'Within 24 hours',
            default => '5-7 business days'
        };

        return "Expected in {$days}";
    }

    /**
     * Check if the order has gift options
     */
    public function hasGiftOptions(): bool
    {
        return $this->is_gift || $this->gift_wrap;
    }

    /**
     * Get total including all charges
     */
    public function getFinalTotalAttribute(): float
    {
        return $this->total + $this->delivery_charge + $this->gift_wrap_charge;
    }

    /**
     * Scope for orders with gift options
     */
    public function scopeWithGifts($query)
    {
        return $query->where('is_gift', true)->orWhere('gift_wrap', true);
    }

    /**
     * Scope for orders by delivery speed
     */
    public function scopeByDeliverySpeed($query, string $speed)
    {
        return $query->where('delivery_speed', $speed);
    }
}
