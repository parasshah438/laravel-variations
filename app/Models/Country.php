<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
