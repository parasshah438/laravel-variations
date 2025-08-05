<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'image_alt',
        'button_text',
        'button_link',
        'button_color',
        'text_position',
        'text_color',
        'is_active',
        'sort_order',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sort_order' => 'integer'
    ];

    /**
     * Scope to get only active sliders
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get sliders within date range
     */
    public function scopeWithinDateRange(Builder $query): Builder
    {
        $now = Carbon::now();
        return $query->where(function ($query) use ($now) {
            $query->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
        });
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Check if slider is currently active (within date range and active status)
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }
        
        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }
        
        return true;
    }
}
