<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'postal_code_prefix',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
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
