<?php

namespace App\Http\Controllers;

use App\Services\AIRecommendationService;
use App\Models\UserBehavior;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;

class AIRecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(AIRecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized recommendations for the current user
     */
    public function getPersonalizedRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:50',
            'types' => 'array',
            'types.*' => 'string|in:collaborative,content_based,cross_sell,upsell,trending,recently_viewed'
        ]);

        $userId = Auth::id();
        $sessionId = Session::getId();
        $limit = $request->input('limit', 12);
        $types = $request->input('types');

        $recommendations = $this->recommendationService->getRecommendationsForUser(
            $userId, 
            $sessionId, 
            $limit, 
            $types
        );

        // Load actual product data
        $productIds = $recommendations->pluck('product_id');
        $products = Product::with(['images', 'variations', 'category', 'brand'])
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $formattedRecommendations = $recommendations->map(function ($rec) use ($products) {
            $product = $products->get($rec->product_id);
            
            if (!$product) {
                return null;
            }

            return [
                'product_id' => $rec->product_id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'category' => $product->category->name ?? null,
                    'brand' => $product->brand->name ?? null,
                    'price' => $product->variations->min('price'),
                    'original_price' => $product->variations->min('original_price'),
                    'discount_percentage' => $product->variations->min('discount_percentage'),
                    'image_url' => $product->images->first()->image_url ?? null,
                    'average_rating' => $product->average_rating,
                    'review_count' => $product->review_count,
                ],
                'recommendation_type' => $rec->recommendation_type,
                'confidence_score' => round($rec->confidence_score, 2),
                'reasoning' => $rec->reasoning,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'recommendations' => $formattedRecommendations,
            'total' => $formattedRecommendations->count(),
            'user_id' => $userId,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Get related products for a specific product page
     */
    public function getRelatedProducts(Request $request, $productId): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:20'
        ]);

        $userId = Auth::id();
        $sessionId = Session::getId();
        $limit = $request->input('limit', 8);

        $relatedProducts = $this->recommendationService->getRelatedProducts(
            $productId,
            $userId,
            $sessionId,
            $limit
        );

        $formattedProducts = $relatedProducts->map(function ($rec) {
            return [
                'product_id' => $rec->product_id,
                'product' => [
                    'id' => $rec->product->id,
                    'name' => $rec->product->name,
                    'slug' => $rec->product->slug,
                    'short_description' => $rec->product->short_description,
                    'category' => $rec->product->category->name ?? null,
                    'brand' => $rec->product->brand->name ?? null,
                    'price' => $rec->product->variations->min('price'),
                    'original_price' => $rec->product->variations->min('original_price'),
                    'discount_percentage' => $rec->product->variations->min('discount_percentage'),
                    'image_url' => $rec->product->images->first()->image_url ?? null,
                    'average_rating' => $rec->product->average_rating,
                    'review_count' => $rec->product->review_count,
                ],
                'recommendation_type' => $rec->recommendation_type,
                'confidence_score' => round($rec->confidence_score, 2),
                'reasoning' => $rec->reasoning,
            ];
        });

        return response()->json([
            'success' => true,
            'related_products' => $formattedProducts,
            'product_id' => $productId,
            'total' => $formattedProducts->count()
        ]);
    }

    /**
     * Get upsell recommendations for a product
     */
    public function getUpsellRecommendations(Request $request, $productId): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:10'
        ]);

        $limit = $request->input('limit', 4);

        $upsellProducts = $this->recommendationService->getUpsellRecommendations($productId, $limit);

        $formattedProducts = $upsellProducts->map(function ($rec) {
            return [
                'product_id' => $rec->product_id,
                'product' => [
                    'id' => $rec->product->id,
                    'name' => $rec->product->name,
                    'slug' => $rec->product->slug,
                    'short_description' => $rec->product->short_description,
                    'category' => $rec->product->category->name ?? null,
                    'brand' => $rec->product->brand->name ?? null,
                    'price' => $rec->product->variations->min('price'),
                    'original_price' => $rec->product->variations->min('original_price'),
                    'discount_percentage' => $rec->product->variations->min('discount_percentage'),
                    'image_url' => $rec->product->images->first()->image_url ?? null,
                    'average_rating' => $rec->product->average_rating,
                    'review_count' => $rec->product->review_count,
                ],
                'recommendation_type' => $rec->recommendation_type,
                'confidence_score' => round($rec->confidence_score, 2),
                'reasoning' => $rec->reasoning,
                'price_difference' => $rec->product->variations->min('price') - Product::find($productId)->variations->min('price'),
            ];
        });

        return response()->json([
            'success' => true,
            'upsell_recommendations' => $formattedProducts,
            'base_product_id' => $productId,
            'total' => $formattedProducts->count()
        ]);
    }

    /**
     * Track user behavior (views, cart adds, purchases, etc.)
     */
    public function trackBehavior(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'behavior_type' => 'required|string|in:' . implode(',', [
                UserBehavior::TYPE_VIEW,
                UserBehavior::TYPE_CART_ADD,
                UserBehavior::TYPE_CART_REMOVE,
                UserBehavior::TYPE_WISHLIST_ADD,
                UserBehavior::TYPE_WISHLIST_REMOVE,
                UserBehavior::TYPE_PURCHASE,
                UserBehavior::TYPE_RATING,
                UserBehavior::TYPE_REVIEW,
                UserBehavior::TYPE_SEARCH,
                UserBehavior::TYPE_FILTER,
                UserBehavior::TYPE_SHARE
            ]),
            'metadata' => 'array',
            'value' => 'numeric|nullable'
        ]);

        $userId = Auth::id();
        $sessionId = Session::getId();

        $this->recommendationService->trackBehavior(
            $userId,
            $sessionId,
            $request->product_id,
            $request->behavior_type,
            $request->metadata ?? [],
            $request->value
        );

        return response()->json([
            'success' => true,
            'message' => 'Behavior tracked successfully',
            'tracked' => [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'product_id' => $request->product_id,
                'behavior_type' => $request->behavior_type,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:50'
        ]);

        $limit = $request->input('limit', 12);
        
        $trending = $this->recommendationService->getTrendingRecommendations($limit);

        // Load actual product data
        $productIds = $trending->pluck('product_id');
        $products = Product::with(['images', 'variations', 'category', 'brand'])
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $formattedProducts = $trending->map(function ($rec) use ($products) {
            $product = $products->get($rec->product_id);
            
            if (!$product) {
                return null;
            }

            return [
                'product_id' => $rec->product_id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'category' => $product->category->name ?? null,
                    'brand' => $product->brand->name ?? null,
                    'price' => $product->variations->min('price'),
                    'original_price' => $product->variations->min('original_price'),
                    'discount_percentage' => $product->variations->min('discount_percentage'),
                    'image_url' => $product->images->first()->image_url ?? null,
                    'average_rating' => $product->average_rating,
                    'review_count' => $product->review_count,
                ],
                'recommendation_type' => $rec->recommendation_type,
                'confidence_score' => round($rec->confidence_score, 2),
                'reasoning' => $rec->reasoning,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'trending_products' => $formattedProducts,
            'total' => $formattedProducts->count(),
            'period' => 'last_7_days'
        ]);
    }

    /**
     * Get cross-sell recommendations (frequently bought together)
     */
    public function getCrossSellRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|max:5',
            'product_ids.*' => 'integer|exists:products,id',
            'limit' => 'integer|min:1|max:20'
        ]);

        $userId = Auth::id();
        $sessionId = Session::getId();
        $limit = $request->input('limit', 8);

        // For now, we'll use the first product ID to find cross-sell recommendations
        $primaryProductId = $request->product_ids[0];
        
        $crossSell = $this->recommendationService->getCrossSellRecommendations(
            $userId,
            $sessionId,
            $limit
        );

        // Load actual product data
        $productIds = $crossSell->pluck('product_id');
        $products = Product::with(['images', 'variations', 'category', 'brand'])
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $formattedProducts = $crossSell->map(function ($rec) use ($products) {
            $product = $products->get($rec->product_id);
            
            if (!$product) {
                return null;
            }

            return [
                'product_id' => $rec->product_id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'category' => $product->category->name ?? null,
                    'brand' => $product->brand->name ?? null,
                    'price' => $product->variations->min('price'),
                    'original_price' => $product->variations->min('original_price'),
                    'discount_percentage' => $product->variations->min('discount_percentage'),
                    'image_url' => $product->images->first()->image_url ?? null,
                    'average_rating' => $product->average_rating,
                    'review_count' => $product->review_count,
                ],
                'recommendation_type' => $rec->recommendation_type,
                'confidence_score' => round($rec->confidence_score, 2),
                'reasoning' => $rec->reasoning,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'cross_sell_recommendations' => $formattedProducts,
            'base_products' => $request->product_ids,
            'total' => $formattedProducts->count()
        ]);
    }
}
