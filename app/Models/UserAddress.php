<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        'label',
        'full_name',
        'phone_number',
        'alternate_phone',
        'address_line_1',
        'address_line_2',
        'landmark',
        'state_id',
        'city_id',
        'postal_code',
        'country_id',
        'gst_number',
        'is_default_shipping',
        'is_default_billing',
        'latitude',
        'longitude',
        'is_active',
        'delivery_instructions'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeDefaultShipping($query)
    {
        return $query->where('is_default_shipping', true);
    }

    public function scopeDefaultBilling($query)
    {
        return $query->where('is_default_billing', true);
    }

    /**
     * Get formatted address
     */
    public function getFormattedAddressAttribute()
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->landmark,
            $this->city?->name,
            $this->state?->name,
            $this->postal_code,
            $this->country?->name
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Get short address
     */
    public function getShortAddressAttribute()
    {
        return $this->address_line_1 . ', ' . 
               $this->city?->name . ', ' . 
               $this->state?->name . ' - ' . 
               $this->postal_code;
    }

    /**
     * Boot method to handle default address logic
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->is_default) {
                // Remove default from other addresses of the same user
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            if ($address->is_default_shipping) {
                // Remove default shipping from other addresses
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_shipping' => false]);
            }

            if ($address->is_default_billing) {
                // Remove default billing from other addresses
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default_billing' => false]);
            }
        });
    }
}
