<?php

namespace App\Http\Middleware;

use App\Services\AIRecommendationService;
use App\Models\UserBehavior;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class TrackUserBehavior
{
    protected $recommendationService;

    public function __construct(AIRecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $behaviorType = 'view'): Response
    {
        $response = $next($request);

        // Only track successful requests
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->trackBehavior($request, $behaviorType);
        }

        return $response;
    }

    /**
     * Track the user behavior
     */
    protected function trackBehavior(Request $request, string $behaviorType)
    {
        try {
            $userId = Auth::id();
            $sessionId = Session::getId();

            // Extract product ID from route or request
            $productId = $this->extractProductId($request);

            if (!$productId) {
                return;
            }

            // Verify product exists
            if (!Product::where('id', $productId)->exists()) {
                return;
            }

            // Gather metadata based on behavior type and request
            $metadata = $this->gatherMetadata($request, $behaviorType);

            // Track the behavior
            $this->recommendationService->trackBehavior(
                $userId,
                $sessionId,
                $productId,
                $behaviorType,
                $metadata
            );

        } catch (\Exception $e) {
            // Silently fail - don't break the user experience
            // You might want to log this error
            \Log::warning('Failed to track user behavior', [
                'error' => $e->getMessage(),
                'request_url' => $request->url(),
                'user_id' => Auth::id(),
                'session_id' => Session::getId()
            ]);
        }
    }

    /**
     * Extract product ID from the request
     */
    protected function extractProductId(Request $request): ?int
    {
        // Try to get from route parameters
        if ($request->route('product')) {
            $product = $request->route('product');
            return is_object($product) ? $product->id : (int) $product;
        }

        // Try to get from request parameters
        if ($request->has('product_id')) {
            return (int) $request->get('product_id');
        }

        // Try to get from URL segments
        $segments = $request->segments();
        
        // Look for product/{id} pattern
        foreach ($segments as $index => $segment) {
            if ($segment === 'product' && isset($segments[$index + 1])) {
                // Could be slug or ID, try to resolve
                $productIdentifier = $segments[$index + 1];
                
                // Try as ID first
                if (is_numeric($productIdentifier)) {
                    return (int) $productIdentifier;
                }
                
                // Try as slug
                $product = Product::where('slug', $productIdentifier)->first();
                return $product ? $product->id : null;
            }
        }

        return null;
    }

    /**
     * Gather metadata for the behavior tracking
     */
    protected function gatherMetadata(Request $request, string $behaviorType): array
    {
        $metadata = [
            'url' => $request->url(),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        // Add behavior-specific metadata
        switch ($behaviorType) {
            case UserBehavior::TYPE_VIEW:
                $metadata['referrer'] = $request->header('Referer');
                $metadata['view_duration'] = $request->get('duration'); // If tracked by frontend
                break;

            case UserBehavior::TYPE_SEARCH:
                $metadata['query'] = $request->get('q') ?? $request->get('query');
                $metadata['filters'] = $request->get('filters', []);
                $metadata['sort'] = $request->get('sort');
                break;

            case UserBehavior::TYPE_FILTER:
                $metadata['filters'] = $request->get('filters', []);
                $metadata['category'] = $request->get('category');
                $metadata['brand'] = $request->get('brand');
                $metadata['price_range'] = $request->get('price_range');
                break;

            case UserBehavior::TYPE_CART_ADD:
                $metadata['quantity'] = $request->get('quantity', 1);
                $metadata['variation_id'] = $request->get('variation_id');
                $metadata['price'] = $request->get('price');
                break;

            case UserBehavior::TYPE_WISHLIST_ADD:
                $metadata['variation_id'] = $request->get('variation_id');
                break;

            case UserBehavior::TYPE_SHARE:
                $metadata['platform'] = $request->get('platform');
                $metadata['medium'] = $request->get('medium'); // email, social, direct link
                break;

            case UserBehavior::TYPE_RATING:
                $metadata['rating'] = $request->get('rating');
                $metadata['review_text'] = substr($request->get('review', ''), 0, 500);
                break;
        }

        // Add device information
        $metadata['device'] = [
            'mobile' => $request->header('User-Agent') ? 
                (bool) preg_match('/Mobile|Android|iPhone/', $request->header('User-Agent')) : false,
            'tablet' => $request->header('User-Agent') ? 
                (bool) preg_match('/iPad|Tablet/', $request->header('User-Agent')) : false,
        ];

        // Add session information
        $metadata['session_info'] = [
            'is_authenticated' => Auth::check(),
            'session_duration' => Session::has('started_at') ? 
                now()->diffInMinutes(Session::get('started_at')) : null,
        ];

        return array_filter($metadata, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
