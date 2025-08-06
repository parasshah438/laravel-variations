<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = $this->getWishlistItems();
        return view('wishlist.index', compact('wishlistItems'));
    }
    
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $data = ['product_id' => $request->product_id];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $existingItem = Wishlist::where('user_id', Auth::id())
                                  ->where('product_id', $request->product_id)
                                  ->first();
        } else {
            $guestToken = session('guest_token', session()->getId());
            session(['guest_token' => $guestToken]);
            $data['guest_token'] = $guestToken;
            $existingItem = Wishlist::where('guest_token', $guestToken)
                                  ->where('product_id', $request->product_id)
                                  ->first();
        }
        
        if ($existingItem) {
            $existingItem->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create($data);
            $inWishlist = true;
            $message = 'Added to wishlist';
        }
        
        return response()->json([
            'success' => true,
            'inWishlist' => $inWishlist,
            'message' => $message,
            'wishlistCount' => $this->getWishlistCount()
        ]);
    }
    
    public function remove($id)
    {
        $wishlistItem = $this->findWishlistItem($id);
        
        if (!$wishlistItem) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }
        
        $wishlistItem->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist',
            'wishlistCount' => $this->getWishlistCount()
        ]);
    }
    
    public function moveGuestWishlistToUser()
    {
        if (!Auth::check() || !session('guest_token')) {
            return;
        }
        
        $guestWishlistItems = Wishlist::where('guest_token', session('guest_token'))->get();
        
        foreach ($guestWishlistItems as $guestItem) {
            $existingItem = Wishlist::where('user_id', Auth::id())
                                  ->where('product_id', $guestItem->product_id)
                                  ->first();
            
            if (!$existingItem) {
                $guestItem->update(['user_id' => Auth::id(), 'guest_token' => null]);
            } else {
                $guestItem->delete();
            }
        }
    }
    
    private function getWishlistItems()
    {
        if (Auth::check()) {
            return Wishlist::with(['product.images', 'product.variations'])
                          ->whereHas('product.variations') // Only get items with variations
                          ->where('user_id', Auth::id())
                          ->get();
        } else {
            $guestToken = session('guest_token');
            if (!$guestToken) return collect();
            
            return Wishlist::with(['product.images', 'product.variations'])
                          ->whereHas('product.variations') // Only get items with variations
                          ->where('guest_token', $guestToken)
                          ->get();
        }
    }
    
    private function findWishlistItem($id)
    {
        if (Auth::check()) {
            return Wishlist::where('id', $id)->where('user_id', Auth::id())->first();
        } else {
            $guestToken = session('guest_token');
            return Wishlist::where('id', $id)->where('guest_token', $guestToken)->first();
        }
    }
    
    public function getWishlistCount()
    {
        return $this->getWishlistItems()->count();
    }
    
    public function checkStatus(Request $request)
    {
        $productId = $request->get('product_id');
        
        if (Auth::check()) {
            $exists = Wishlist::where('user_id', Auth::id())
                             ->where('product_id', $productId)
                             ->exists();
        } else {
            $guestToken = session('guest_token');
            $exists = $guestToken ? Wishlist::where('guest_token', $guestToken)
                                          ->where('product_id', $productId)
                                          ->exists() : false;
        }
        
        return response()->json([
            'inWishlist' => $exists,
            'count' => $this->getWishlistCount()
        ]);
    }

    public function moveToCart(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer'
        ]);

        $wishlistItem = $this->findWishlistItem($request->item_id);
        
        if (!$wishlistItem) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $product = $wishlistItem->product;
        
        // Check if product has variations
        if ($product->variations->count() == 1) {
            $variation = $product->variations->first();
            
            if ($variation->stock > 0) {
                // Add to cart
                $cartController = new CartController();
                $cartRequest = new Request([
                    'product_variation_id' => $variation->id,
                    'qty' => 1
                ]);
                $cartRequest->merge(['_token' => csrf_token()]);
                
                $response = $cartController->add($cartRequest);
                $responseData = $response->getData(true);
                
                if ($responseData['success']) {
                    // Remove from wishlist
                    $wishlistItem->delete();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Item moved to cart successfully',
                        'cartCount' => $responseData['cartCount'],
                        'wishlistCount' => $this->getWishlistCount()
                    ]);
                } else {
                    return response()->json(['success' => false, 'message' => $responseData['message']], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Product is out of stock'], 400);
            }
        } else {
            return response()->json([
                'success' => false, 
                'message' => 'Product has multiple variations. Please select options.',
                'redirect' => route('products.show', $product->slug)
            ], 400);
        }
    }

    public function moveAllToCart(Request $request)
    {
        $wishlistItems = $this->getWishlistItems();
        
        if ($wishlistItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Wishlist is empty'], 400);
        }

        $movedCount = 0;
        $skippedCount = 0;
        $cartController = new CartController();
        
        foreach ($wishlistItems as $item) {
            $product = $item->product;
            
            // Only move products with single variation and stock > 0
            if ($product->variations->count() == 1) {
                $variation = $product->variations->first();
                
                if ($variation->stock > 0) {
                    try {
                        $cartRequest = new Request([
                            'product_variation_id' => $variation->id,
                            'qty' => 1
                        ]);
                        $cartRequest->merge(['_token' => csrf_token()]);
                        
                        $response = $cartController->add($cartRequest);
                        $responseData = $response->getData(true);
                        
                        if ($responseData['success']) {
                            $item->delete();
                            $movedCount++;
                        } else {
                            $skippedCount++;
                        }
                    } catch (\Exception $e) {
                        $skippedCount++;
                    }
                } else {
                    $skippedCount++;
                }
            } else {
                $skippedCount++;
            }
        }
        
        $message = "Moved {$movedCount} items to cart";
        if ($skippedCount > 0) {
            $message .= ". {$skippedCount} items were skipped (out of stock or have multiple variations)";
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'movedCount' => $movedCount,
            'skippedCount' => $skippedCount,
            'wishlistCount' => $this->getWishlistCount()
        ]);
    }

    public function clearAll(Request $request)
    {
        $wishlistItems = $this->getWishlistItems();
        
        if ($wishlistItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Wishlist is already empty'], 400);
        }

        $deletedCount = $wishlistItems->count();
        
        if (Auth::check()) {
            Wishlist::where('user_id', Auth::id())->delete();
        } else {
            $guestToken = session('guest_token');
            if ($guestToken) {
                Wishlist::where('guest_token', $guestToken)->delete();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Removed {$deletedCount} items from wishlist",
            'wishlistCount' => 0
        ]);
    }
}
