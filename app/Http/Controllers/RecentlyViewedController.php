<?php

namespace App\Http\Controllers;

use App\Models\RecentlyViewedProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecentlyViewedController extends Controller
{
    /**
     * Add product to recently viewed
     */
    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $data = [
            'product_id' => $request->product_id
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            
            // Remove existing entry if it exists
            RecentlyViewedProduct::where('user_id', Auth::id())
                                ->where('product_id', $request->product_id)
                                ->delete();
        } else {
            $guestToken = session('guest_token', session()->getId());
            session(['guest_token' => $guestToken]);
            $data['guest_token'] = $guestToken;
            
            // Remove existing entry if it exists
            RecentlyViewedProduct::where('guest_token', $guestToken)
                                ->where('product_id', $request->product_id)
                                ->delete();
        }

        RecentlyViewedProduct::create($data);

        // Keep only the latest 20 items
        $this->cleanupOldEntries();

        return response()->json(['success' => true]);
    }

    /**
     * Get recently viewed products
     */
    public function getRecentlyViewed(Request $request)
    {
        $limit = $request->get('limit', 10);
        $recentlyViewed = $this->getRecentlyViewedItems($limit);

        return response()->json([
            'success' => true,
            'products' => $recentlyViewed->map(function($item) {
                $product = $item->product;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->images->first()?->image_path ?? '/placeholder-image.jpg',
                    'min_price' => $product->getMinPriceAttribute(),
                    'viewed_at' => $item->created_at->diffForHumans()
                ];
            })
        ]);
    }

    /**
     * Get recently viewed items for current user/guest
     */
    private function getRecentlyViewedItems($limit = 10)
    {
        if (Auth::check()) {
            return RecentlyViewedProduct::with(['product.images', 'product.variations'])
                                      ->where('user_id', Auth::id())
                                      ->orderBy('created_at', 'desc')
                                      ->limit($limit)
                                      ->get();
        } else {
            $guestToken = session('guest_token');
            if (!$guestToken) return collect();
            
            return RecentlyViewedProduct::with(['product.images', 'product.variations'])
                                      ->where('guest_token', $guestToken)
                                      ->orderBy('created_at', 'desc')
                                      ->limit($limit)
                                      ->get();
        }
    }

    /**
     * Clean up old entries, keep only latest 20
     */
    private function cleanupOldEntries()
    {
        if (Auth::check()) {
            $oldEntries = RecentlyViewedProduct::where('user_id', Auth::id())
                                             ->orderBy('created_at', 'desc')
                                             ->skip(20)
                                             ->pluck('id');
            
            if ($oldEntries->isNotEmpty()) {
                RecentlyViewedProduct::whereIn('id', $oldEntries)->delete();
            }
        } else {
            $guestToken = session('guest_token');
            if ($guestToken) {
                $oldEntries = RecentlyViewedProduct::where('guest_token', $guestToken)
                                                 ->orderBy('created_at', 'desc')
                                                 ->skip(20)
                                                 ->pluck('id');
                
                if ($oldEntries->isNotEmpty()) {
                    RecentlyViewedProduct::whereIn('id', $oldEntries)->delete();
                }
            }
        }
    }

    /**
     * Clear all recently viewed products
     */
    public function clearAll()
    {
        if (Auth::check()) {
            RecentlyViewedProduct::where('user_id', Auth::id())->delete();
        } else {
            $guestToken = session('guest_token');
            if ($guestToken) {
                RecentlyViewedProduct::where('guest_token', $guestToken)->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Recently viewed products cleared'
        ]);
    }
    
    /**
     * Debug method to check database state
     */
    public function debug()
    {
        $totalCount = RecentlyViewedProduct::count();
        $userCount = Auth::check() ? RecentlyViewedProduct::where('user_id', Auth::id())->count() : 0;
        $guestToken = session('guest_token');
        $guestCount = $guestToken ? RecentlyViewedProduct::where('guest_token', $guestToken)->count() : 0;
        
        $recentRecords = RecentlyViewedProduct::with('product')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'guest_token' => $item->guest_token,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Product not found',
                    'created_at' => $item->created_at->format('Y-m-d H:i:s')
                ];
            });
        
        return response()->json([
            'total_count' => $totalCount,
            'user_count' => $userCount,
            'guest_count' => $guestCount,
            'current_user_id' => Auth::id(),
            'guest_token' => $guestToken,
            'session_id' => session()->getId(),
            'recent_records' => $recentRecords
        ]);
    }
}
