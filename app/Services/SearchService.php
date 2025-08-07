<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    protected $maxResults = 50;
    protected $suggestionLimit = 8;

    /**
     * Advanced search with intelligent matching
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->get('filters', []);
        $sort = $request->get('sort', 'relevance');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);

        // Start with base query
        $productQuery = Product::with(['category', 'brand', 'images', 'variations.attributeValues'])
                              ->where('status', 'active');

        // Apply search query only if not empty
        if (!empty($query)) {
            $productQuery = $this->applySearchQuery($productQuery, $query);
        }

        // Apply filters
        $productQuery = $this->applyFilters($productQuery, $filters);

        // Apply sorting
        $productQuery = $this->applySorting($productQuery, $sort, $query);

        // Get results
        $products = $productQuery->paginate($perPage, ['*'], 'page', $page);

        return [
            'products' => $products,
            'facets' => $this->generateFacets($query, $filters),
            'suggestions' => empty($query) ? [] : $this->getSearchSuggestions($query),
            'query_info' => $this->getQueryInfo($query, $products->total()),
        ];
    }

    /**
     * Apply intelligent search query matching
     */
    protected function applySearchQuery($query, $searchTerm)
    {
        $terms = $this->parseSearchQuery($searchTerm);
        
        return $query->where(function($q) use ($terms, $searchTerm) {
            // Exact phrase match (highest priority)
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
            
            // Individual terms matching
            foreach ($terms as $term) {
                $q->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('category', function($categoryQuery) use ($term) {
                      $categoryQuery->where('name', 'like', "%{$term}%");
                  })
                  ->orWhereHas('brand', function($brandQuery) use ($term) {
                      $brandQuery->where('name', 'like', "%{$term}%");
                  })
                  ->orWhereHas('variations.attributeValues', function($attrQuery) use ($term) {
                      $attrQuery->where('value', 'like', "%{$term}%");
                  });
            }
        });
    }

    /**
     * Parse search query into meaningful terms
     */
    protected function parseSearchQuery($query)
    {
        // Remove special characters and split by spaces
        $cleaned = preg_replace('/[^\w\s]/', ' ', $query);
        $terms = array_filter(explode(' ', $cleaned));
        
        // Remove common stop words
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $terms = array_diff($terms, $stopWords);
        
        // Filter out terms shorter than 2 characters
        return array_filter($terms, function($term) {
            return strlen($term) >= 2;
        });
    }

    /**
     * Apply various filters
     */
    protected function applyFilters($query, $filters)
    {
        // Category filter
        if (!empty($filters['categories'])) {
            $categoryIds = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];
            $query->whereIn('category_id', $categoryIds);
        }

        // Brand filter
        if (!empty($filters['brands'])) {
            $brandIds = is_array($filters['brands']) ? $filters['brands'] : [$filters['brands']];
            $query->whereIn('brand_id', $brandIds);
        }

        // Price range filter
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $minPrice = $filters['price_min'] ?? 0;
            $maxPrice = $filters['price_max'] ?? 999999;
            
            $query->whereHas('variations', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        // Attributes filter
        if (!empty($filters['attributes'])) {
            foreach ($filters['attributes'] as $attributeId => $valueIds) {
                if (!empty($valueIds)) {
                    $valueIds = is_array($valueIds) ? $valueIds : [$valueIds];
                    $query->whereHas('variations.attributeValues', function($q) use ($valueIds) {
                        $q->whereIn('attribute_value_id', $valueIds);
                    });
                }
            }
        }

        // Stock filter
        if (!empty($filters['in_stock'])) {
            $query->whereHas('variations', function($q) {
                $q->where('stock', '>', 0);
            });
        }

        // Rating filter
        if (!empty($filters['min_rating'])) {
            // Assuming you'll add reviews/ratings later
            // $query->whereHas('reviews', function($q) use ($filters) {
            //     $q->havingRaw('AVG(rating) >= ?', [$filters['min_rating']]);
            // });
        }

        return $query;
    }

    /**
     * Apply sorting
     */
    protected function applySorting($query, $sort, $searchTerm = '')
    {
        switch ($sort) {
            case 'relevance':
                if (!empty($searchTerm)) {
                    // Sort by relevance when search term exists
                    return $query->orderByRaw("
                        CASE 
                            WHEN name LIKE ? THEN 1
                            WHEN name LIKE ? THEN 2
                            WHEN description LIKE ? THEN 3
                            ELSE 4
                        END, name ASC
                    ", ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"]);
                }
                return $query->latest();
            
            case 'price_low':
                return $query->join('product_variations', 'products.id', '=', 'product_variations.product_id')
                            ->orderBy('product_variations.price', 'asc')
                            ->select('products.*')
                            ->distinct();
            
            case 'price_high':
                return $query->join('product_variations', 'products.id', '=', 'product_variations.product_id')
                            ->orderBy('product_variations.price', 'desc')
                            ->select('products.*')
                            ->distinct();
            
            case 'name_asc':
                return $query->orderBy('name', 'asc');
            
            case 'name_desc':
                return $query->orderBy('name', 'desc');
            
            case 'newest':
                return $query->latest();
            
            case 'oldest':
                return $query->oldest();
            
            case 'popular':
                // Order by stock or sales (you can add order count later)
                return $query->withSum('variations', 'stock')
                            ->orderBy('variations_sum_stock', 'desc');
            
            default:
                return $query->latest();
        }
    }

    /**
     * Generate facets for filtering
     */
    protected function generateFacets($query, $currentFilters)
    {
        // Get base products for facet counts (without current filters)
        $baseQuery = Product::where('status', 'active');
        
        if (!empty($query)) {
            $baseQuery = $this->applySearchQuery($baseQuery, $query);
        }

        return [
            'categories' => $this->getCategoryFacets($baseQuery, $currentFilters),
            'brands' => $this->getBrandFacets($baseQuery, $currentFilters),
            'attributes' => $this->getAttributeFacets($baseQuery, $currentFilters),
            'price_ranges' => $this->getPriceRangeFacets($baseQuery, $currentFilters),
        ];
    }

    /**
     * Get category facets with product counts
     */
    protected function getCategoryFacets($baseQuery, $currentFilters)
    {
        return Cache::remember('category_facets_' . md5(serialize($currentFilters)), 300, function() use ($baseQuery) {
            return Category::withCount(['products' => function($query) use ($baseQuery) {
                $productIds = $baseQuery->pluck('id');
                $query->whereIn('id', $productIds);
            }])
            ->where('status', 'active')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();
        });
    }

    /**
     * Get brand facets with product counts
     */
    protected function getBrandFacets($baseQuery, $currentFilters)
    {
        return Cache::remember('brand_facets_' . md5(serialize($currentFilters)), 300, function() use ($baseQuery) {
            return Brand::withCount(['products' => function($query) use ($baseQuery) {
                $productIds = $baseQuery->pluck('id');
                $query->whereIn('id', $productIds);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();
        });
    }

    /**
     * Get attribute facets with value counts
     */
    protected function getAttributeFacets($baseQuery, $currentFilters)
    {
        return Attribute::with(['attributeValues' => function($query) use ($baseQuery) {
            $productIds = $baseQuery->pluck('id');
            $query->withCount(['productVariations' => function($subQuery) use ($productIds) {
                $subQuery->whereHas('product', function($productQuery) use ($productIds) {
                    $productQuery->whereIn('id', $productIds);
                });
            }])
            ->having('product_variations_count', '>', 0)
            ->orderBy('value');
        }])
        ->whereHas('attributeValues', function($query) use ($baseQuery) {
            $productIds = $baseQuery->pluck('id');
            $query->whereHas('productVariations.product', function($productQuery) use ($productIds) {
                $productQuery->whereIn('id', $productIds);
            });
        })
        ->orderBy('name')
        ->get();
    }

    /**
     * Get price range facets
     */
    protected function getPriceRangeFacets($baseQuery, $currentFilters)
    {
        $productIds = $baseQuery->pluck('id');
        
        $priceStats = DB::table('product_variations')
            ->whereIn('product_id', $productIds)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price, AVG(price) as avg_price')
            ->first();

        if (!$priceStats) {
            return [];
        }

        $minPrice = floor($priceStats->min_price);
        $maxPrice = ceil($priceStats->max_price);
        $range = $maxPrice - $minPrice;
        $step = max(1, $range / 5); // Create 5 price ranges

        $ranges = [];
        for ($i = 0; $i < 5; $i++) {
            $start = $minPrice + ($i * $step);
            $end = $i === 4 ? $maxPrice : $minPrice + (($i + 1) * $step);
            
            $count = DB::table('product_variations')
                ->whereIn('product_id', $productIds)
                ->whereBetween('price', [$start, $end])
                ->count();

            if ($count > 0) {
                $ranges[] = [
                    'label' => '₹' . number_format($start) . ' - ₹' . number_format($end),
                    'min' => $start,
                    'max' => $end,
                    'count' => $count,
                ];
            }
        }

        return $ranges;
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions($query, $limit = null)
    {
        $limit = $limit ?? $this->suggestionLimit;
        
        $suggestions = [];

        // Product name suggestions
        $productSuggestions = Product::where('name', 'like', "%{$query}%")
            ->where('status', 'active')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        // Category suggestions
        $categorySuggestions = Category::where('name', 'like', "%{$query}%")
            ->where('status', 'active')
            ->limit($limit)
            ->pluck('name')
            ->map(function($name) {
                return $name . ' (Category)';
            })
            ->toArray();

        // Brand suggestions
        $brandSuggestions = Brand::where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('name')
            ->map(function($name) {
                return $name . ' (Brand)';
            })
            ->toArray();

        // Combine and limit
        $allSuggestions = array_merge($productSuggestions, $categorySuggestions, $brandSuggestions);
        return array_slice(array_unique($allSuggestions), 0, $limit);
    }

    /**
     * Get query information
     */
    protected function getQueryInfo($query, $totalResults)
    {
        return [
            'query' => $query,
            'total_results' => $totalResults,
            'has_results' => $totalResults > 0,
            'execution_time' => microtime(true) - LARAVEL_START,
        ];
    }

    /**
     * Get trending searches
     */
    public function getTrendingSearches($limit = 10)
    {
        // This would typically come from search analytics
        // For now, return popular product names
        return Product::where('status', 'active')
            ->withSum('variations', 'stock')
            ->orderBy('variations_sum_stock', 'desc')
            ->limit($limit)
            ->pluck('name');
    }

    /**
     * Store search query for analytics
     */
    public function logSearch($query, $results_count, $user_id = null)
    {
        // Store in search_logs table for analytics
        \App\Models\SearchLog::create([
            'query' => $query,
            'results_count' => $results_count,
            'user_id' => $user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
