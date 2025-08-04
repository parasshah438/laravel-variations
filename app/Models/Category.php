<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'name', 
        'slug', 
        'parent_id', 
        'status', 
        'icon', 
        'image', 
        'sort_order', 
        'meta', 
        'meta_title', 
        'meta_description', 
        'meta_keywords'
    ];

    protected $casts = [
        'status' => 'boolean',
        'meta' => 'array',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function ancestors()
    {
        $ancestors = collect();
        $category = $this;
        
        while ($category->parent) {
            $ancestors->prepend($category->parent);
            $category = $category->parent;
        }
        
        return $ancestors;
    }

    public function descendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }
        
        return $descendants;
    }

    public function isParent(): bool
    {
        return $this->children()->count() > 0;
    }

    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    public function getDepthAttribute(): int
    {
        return $this->ancestors()->count();
    }

    public function getFullNameAttribute(): string
    {
        $names = $this->ancestors()->pluck('name')->push($this->name);
        return $names->implode(' > ');
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // Scope for parent categories
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope for child categories
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Get categories in tree structure
    public static function getTree($parentId = null)
    {
        return static::where('parent_id', $parentId)
                    ->where('status', true)
                    ->orderBy('sort_order')
                    ->with(['children' => function($query) {
                        $query->where('status', true)->orderBy('sort_order');
                    }])
                    ->get();
    }

    // Get all categories with their products count
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    // Get all products including from child categories
    public function getAllProducts()
    {
        $categoryIds = collect([$this->id]);
        $categoryIds = $categoryIds->merge($this->descendants()->pluck('id'));
        
        return Product::whereIn('category_id', $categoryIds);
    }
}
