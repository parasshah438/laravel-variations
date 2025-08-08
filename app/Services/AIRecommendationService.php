<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\ProductRecommendation;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AIRecommendationService
{
    protected $weights = [
        'view' => 1,
        'cart_add' => 3,
        'wishlist_add' => 2,
        'purchase' => 5,
        'rating' => 2,
    ];

    protected $confidenceThreshold = 0.3;
    protected $maxRecommendations = 20;

    /**
     * Get personalized recommendations for a user
     */
    public function getRecommendationsForUser($userId, $sessionId = null, $limit = 10, $types = null)
    {
        $cacheKey = "recommendations_user_{$userId}_session_{$sessionId}_" . md5(serialize($types));
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $sessionId, $limit, $types) {
            $recommendations = collect();

            // Get different types of recommendations
            $recommendationTypes = $types ?? [
                ProductRecommendation::TYPE_COLLABORATIVE,
                ProductRecommendation::TYPE_CONTENT_BASED,
                ProductRecommendation::TYPE_CROSS_SELL,
                ProductRecommendation::TYPE_TRENDING,
                ProductRecommendation::TYPE_RECENTLY_VIEWED,
            ];

            foreach ($recommendationTypes as $type) {
                $typeRecommendations = $this->getRecommendationsByType($userId, $sessionId, $type, $limit);
                $recommendations = $recommendations->merge($typeRecommendations);
            }

            // Remove duplicates and sort by confidence score
            $recommendations = $recommendations->unique('product_id')
                ->sortByDesc('confidence_score')
                ->take($limit);

            return $recommendations->values();
        });
    }

    /**
     * Get recommendations by specific type
     */
    protected function getRecommendationsByType($userId, $sessionId, $type, $limit)
    {
        switch ($type) {
            case ProductRecommendation::TYPE_COLLABORATIVE:
                return $this->getCollaborativeRecommendations($userId, $sessionId, $limit);
            
            case ProductRecommendation::TYPE_CONTENT_BASED:
                return $this->getContentBasedRecommendations($userId, $sessionId, $limit);
            
            case ProductRecommendation::TYPE_CROSS_SELL:
                return $this->getCrossSellRecommendations($userId, $sessionId, $limit);
            
            case ProductRecommendation::TYPE_TRENDING:
                return $this->getTrendingRecommendations($limit);
            
            case ProductRecommendation::TYPE_RECENTLY_VIEWED:
                return $this->getRecentlyViewedRecommendations($userId, $sessionId, $limit);
            
            default:
                return collect();
        }
    }

    /**
     * Collaborative filtering - recommendations based on similar users
     */
    protected function getCollaborativeRecommendations($userId, $sessionId, $limit)
    {
        // Find users with similar behavior patterns
        $similarUsers = $this->findSimilarUsers($userId, $sessionId);
        
        if ($similarUsers->isEmpty()) {
            return collect();
        }

        $userProductIds = $this->getUserProductInteractions($userId, $sessionId);
        
        $recommendations = DB::table('user_behaviors')
            ->select('product_id', DB::raw('AVG(CASE 
                WHEN behavior_type = "purchase" THEN 5
                WHEN behavior_type = "cart_add" THEN 3
                WHEN behavior_type = "wishlist_add" THEN 2
                ELSE 1 END) as score'))
            ->whereIn('user_id', $similarUsers->pluck('user_id'))
            ->whereNotIn('product_id', $userProductIds)
            ->where('behavior_timestamp', '>=', now()->subDays(90))
            ->groupBy('product_id')
            ->having('score', '>=', 2)
            ->orderByDesc('score')
            ->limit($limit)
            ->get();

        return $recommendations->map(function ($item) {
            return (object) [
                'product_id' => $item->product_id,
                'recommendation_type' => ProductRecommendation::TYPE_COLLABORATIVE,
                'confidence_score' => min($item->score / 5, 1), // Normalize to 0-1
                'reasoning' => ['Similar users also liked this product']
            ];
        });
    }

    /**
     * Content-based filtering - recommendations based on product attributes
     */
    protected function getContentBasedRecommendations($userId, $sessionId, $limit)
    {
        $userInteractions = $this->getUserRecentInteractions($userId, $sessionId, 30);
        
        if ($userInteractions->isEmpty()) {
            return collect();
        }

        // Get categories and brands user interacts with most
        $preferredCategories = $userInteractions->pluck('product.category_id')
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys();

        $preferredBrands = $userInteractions->pluck('product.brand_id')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys();

        $viewedProductIds = $userInteractions->pluck('product_id')->unique();

        $query = Product::with(['category', 'brand', 'images', 'variations'])
            ->where('status', 'active')
            ->whereNotIn('id', $viewedProductIds);

        // Score products based on category and brand preferences
        $recommendations = $query->get()->map(function ($product) use ($preferredCategories, $preferredBrands) {
            $score = 0;
            
            if ($preferredCategories->contains($product->category_id)) {
                $score += 0.6;
            }
            
            if ($preferredBrands->contains($product->brand_id)) {
                $score += 0.4;
            }

            // Boost newer products slightly
            if ($product->created_at >= now()->subDays(30)) {
                $score += 0.1;
            }

            return (object) [
                'product_id' => $product->id,
                'recommendation_type' => ProductRecommendation::TYPE_CONTENT_BASED,
                'confidence_score' => min($score, 1),
                'reasoning' => [
                    'Based on your interest in ' . $product->category->name ?? 'this category',
                    $product->brand ? 'From ' . $product->brand->name . ' brand' : null
                ]
            ];
        })
        ->filter(function ($item) {
            return $item->confidence_score >= $this->confidenceThreshold;
        })
        ->sortByDesc('confidence_score')
        ->take($limit);

        return $recommendations->values();
    }

    /**
     * Cross-sell recommendations - frequently bought together
     */
    protected function getCrossSellRecommendations($userId, $sessionId, $limit)
    {
        $userProductIds = $this->getUserProductInteractions($userId, $sessionId);
        
        if (empty($userProductIds)) {
            return collect();
        }

        // Find products frequently bought together with user's products
        $crossSellProducts = DB::table('order_items as oi1')
            ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
            ->select('oi2.product_id', DB::raw('COUNT(*) as frequency'))
            ->whereIn('oi1.product_id', $userProductIds)
            ->where('oi1.product_id', '!=', DB::raw('oi2.product_id'))
            ->whereNotIn('oi2.product_id', $userProductIds)
            ->groupBy('oi2.product_id')
            ->having('frequency', '>=', 2)
            ->orderByDesc('frequency')
            ->limit($limit)
            ->get();

        return $crossSellProducts->map(function ($item) {
            return (object) [
                'product_id' => $item->product_id,
                'recommendation_type' => ProductRecommendation::TYPE_CROSS_SELL,
                'confidence_score' => min($item->frequency / 10, 1), // Normalize
                'reasoning' => ['Frequently bought together with your items']
            ];
        });
    }

    /**
     * Trending products recommendations
     */
    protected function getTrendingRecommendations($limit)
    {
        return Cache::remember('trending_recommendations', 3600, function () use ($limit) {
            $trending = DB::table('user_behaviors')
                ->select('product_id', DB::raw('COUNT(*) as popularity_score'))
                ->where('behavior_timestamp', '>=', now()->subDays(7))
                ->whereIn('behavior_type', ['view', 'cart_add', 'purchase'])
                ->groupBy('product_id')
                ->having('popularity_score', '>=', 5)
                ->orderByDesc('popularity_score')
                ->limit($limit)
                ->get();

            return $trending->map(function ($item) {
                return (object) [
                    'product_id' => $item->product_id,
                    'recommendation_type' => ProductRecommendation::TYPE_TRENDING,
                    'confidence_score' => min($item->popularity_score / 100, 1),
                    'reasoning' => ['Trending this week']
                ];
            });
        });
    }

    /**
     * Recently viewed based recommendations
     */
    protected function getRecentlyViewedRecommendations($userId, $sessionId, $limit)
    {
        $recentlyViewed = $this->getUserRecentInteractions($userId, $sessionId, 7, ['view']);
        
        if ($recentlyViewed->isEmpty()) {
            return collect();
        }

        $categories = $recentlyViewed->pluck('product.category_id')->unique();
        $viewedProductIds = $recentlyViewed->pluck('product_id');

        $similar = Product::with(['category', 'brand', 'images'])
            ->whereIn('category_id', $categories)
            ->whereNotIn('id', $viewedProductIds)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $similar->map(function ($product) {
            return (object) [
                'product_id' => $product->id,
                'recommendation_type' => ProductRecommendation::TYPE_RECENTLY_VIEWED,
                'confidence_score' => 0.7,
                'reasoning' => ['Similar to items you recently viewed']
            ];
        });
    }

    /**
     * Track user behavior for future recommendations
     */
    public function trackBehavior($userId, $sessionId, $productId, $behaviorType, $metadata = [], $value = null)
    {
        UserBehavior::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $productId,
            'behavior_type' => $behaviorType,
            'metadata' => $metadata,
            'value' => $value,
            'behavior_timestamp' => now(),
        ]);

        // Clear related caches
        $this->clearUserCaches($userId, $sessionId);
    }

    /**
     * Find users with similar behavior patterns
     */
    protected function findSimilarUsers($userId, $sessionId, $limit = 50)
    {
        $userProductIds = $this->getUserProductInteractions($userId, $sessionId);
        
        if (empty($userProductIds)) {
            return collect();
        }

        return DB::table('user_behaviors')
            ->select('user_id', DB::raw('COUNT(*) as common_products'))
            ->whereIn('product_id', $userProductIds)
            ->where('user_id', '!=', $userId)
            ->where('behavior_timestamp', '>=', now()->subDays(90))
            ->groupBy('user_id')
            ->having('common_products', '>=', 2)
            ->orderByDesc('common_products')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's product interactions
     */
    protected function getUserProductInteractions($userId, $sessionId = null)
    {
        $query = DB::table('user_behaviors')
            ->select('product_id')
            ->where('behavior_timestamp', '>=', now()->subDays(90))
            ->whereIn('behavior_type', ['view', 'cart_add', 'wishlist_add', 'purchase']);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        return $query->distinct()->pluck('product_id')->toArray();
    }

    /**
     * Get user's recent interactions with product details
     */
    protected function getUserRecentInteractions($userId, $sessionId, $days = 30, $behaviorTypes = null)
    {
        $query = UserBehavior::with(['product.category', 'product.brand'])
            ->where('behavior_timestamp', '>=', now()->subDays($days));

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        if ($behaviorTypes) {
            $query->whereIn('behavior_type', $behaviorTypes);
        }

        return $query->orderByDesc('behavior_timestamp')->get();
    }

    /**
     * Clear user-specific caches
     */
    protected function clearUserCaches($userId, $sessionId)
    {
        $patterns = [
            "recommendations_user_{$userId}_*",
            "recommendations_session_{$sessionId}_*"
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get recommendations for product page (related products)
     */
    public function getRelatedProducts($productId, $userId = null, $sessionId = null, $limit = 8)
    {
        return Cache::remember("related_products_{$productId}_{$userId}_{$sessionId}", 1800, function () use ($productId, $userId, $sessionId, $limit) {
            $product = Product::with(['category', 'brand'])->find($productId);
            
            if (!$product) {
                return collect();
            }

            // Get products from same category
            $categoryProducts = Product::with(['images', 'variations'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $productId)
                ->where('status', 'active')
                ->limit($limit)
                ->get();

            // If same brand available, prioritize them
            if ($product->brand_id) {
                $brandProducts = $categoryProducts->where('brand_id', $product->brand_id);
                
                if ($brandProducts->count() >= $limit / 2) {
                    $related = $brandProducts->take($limit / 2)
                        ->merge($categoryProducts->where('brand_id', '!=', $product->brand_id)->take($limit / 2));
                } else {
                    $related = $categoryProducts->take($limit);
                }
            } else {
                $related = $categoryProducts->take($limit);
            }

            return $related->map(function ($relatedProduct) {
                return (object) [
                    'product_id' => $relatedProduct->id,
                    'product' => $relatedProduct,
                    'recommendation_type' => ProductRecommendation::TYPE_CONTENT_BASED,
                    'confidence_score' => 0.8,
                    'reasoning' => ['Related products in same category']
                ];
            });
        });
    }

    /**
     * Get upsell recommendations (higher value alternatives)
     */
    public function getUpsellRecommendations($productId, $limit = 4)
    {
        $product = Product::with(['category', 'variations'])->find($productId);
        
        if (!$product) {
            return collect();
        }

        $currentPrice = $product->variations->min('price') ?? 0;
        $upsellPriceMin = $currentPrice * 1.2; // 20% higher
        $upsellPriceMax = $currentPrice * 2.0; // Up to 2x price

        return Product::with(['images', 'variations', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 'active')
            ->whereHas('variations', function ($query) use ($upsellPriceMin, $upsellPriceMax) {
                $query->whereBetween('price', [$upsellPriceMin, $upsellPriceMax]);
            })
            ->limit($limit)
            ->get()
            ->map(function ($upsellProduct) {
                return (object) [
                    'product_id' => $upsellProduct->id,
                    'product' => $upsellProduct,
                    'recommendation_type' => ProductRecommendation::TYPE_UPSELL,
                    'confidence_score' => 0.75,
                    'reasoning' => ['Premium alternative with enhanced features']
                ];
            });
    }
}
