<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount', 'valid_from', 'valid_to'];

    protected $casts = [
        'discount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function isValid(): bool
    {
        $now = Carbon::now();
        return $now >= $this->valid_from && $now <= $this->valid_to;
    }
}
