<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\SaveForLater;
use App\Models\ProductVariation;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->getCartItems();
        $saveForLaterItems = $this->getSaveForLaterItems();
        $total = $cartItems->sum(function($item) {
            return $item->qty * $item->productVariation->price;
        });
        
        return view('cart.index', compact('cartItems', 'saveForLaterItems', 'total'));
    }
    
    public function add(Request $request)
    {
        $request->validate([
            'product_variation_id' => 'required|exists:product_variations,id',
            'qty' => 'required|integer|min:1'
        ]);
        
        $variation = ProductVariation::findOrFail($request->product_variation_id);
        
        if ($variation->stock < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }
        
        $data = [
            'product_variation_id' => $request->product_variation_id,
            'qty' => $request->qty
        ];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $existingItem = Cart::where('user_id', Auth::id())
                              ->where('product_variation_id', $request->product_variation_id)
                              ->first();
        } else {
            $guestToken = session('guest_token', session()->getId());
            session(['guest_token' => $guestToken]);
            $data['guest_token'] = $guestToken;
            $existingItem = Cart::where('guest_token', $guestToken)
                              ->where('product_variation_id', $request->product_variation_id)
                              ->first();
        }
        
        if ($existingItem) {
            $newQty = $existingItem->qty + $request->qty;
            if ($variation->stock < $newQty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more items. Stock limit reached.'
                ], 400);
            }
            $existingItem->update(['qty' => $newQty]);
        } else {
            Cart::create($data);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cartCount' => $this->getCartCount()
        ]);
    }
    
    public function ajaxAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        // For products with single variation, get the first variation
        $product = \App\Models\Product::with('variations')->findOrFail($request->product_id);
        
        if ($product->variations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Product has no variations available'
            ], 400);
        }
        
        // Get the first available variation (for single variation products)
        $variation = $product->variations->first();
        
        if ($variation->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }
        
        $data = [
            'product_variation_id' => $variation->id,
            'qty' => $request->quantity
        ];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $existingItem = Cart::where('user_id', Auth::id())
                              ->where('product_variation_id', $variation->id)
                              ->first();
        } else {
            $guestToken = session('guest_token', session()->getId());
            session(['guest_token' => $guestToken]);
            $data['guest_token'] = $guestToken;
            $existingItem = Cart::where('guest_token', $guestToken)
                              ->where('product_variation_id', $variation->id)
                              ->first();
        }
        
        if ($existingItem) {
            $newQty = $existingItem->qty + $request->quantity;
            if ($variation->stock < $newQty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more items. Stock limit reached.'
                ], 400);
            }
            $existingItem->update(['qty' => $newQty]);
        } else {
            Cart::create($data);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cartCount' => $this->getCartCount()
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);
        
        $cartItem = $this->findCartItem($id);
        
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }
        
        if ($cartItem->productVariation->stock < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }
        
        $cartItem->update(['qty' => $request->qty]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'itemTotal' => number_format($cartItem->qty * $cartItem->productVariation->price, 2),
            'cartTotal' => number_format($this->getCartTotal(), 2)
        ]);
    }
    
    public function remove($id)
    {
        $cartItem = $this->findCartItem($id);
        
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }
        
        $cartItem->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cartCount' => $this->getCartCount(),
            'cartTotal' => number_format($this->getCartTotal(), 2)
        ]);
    }
    
    private function getCartItems()
    {
        if (Auth::check()) {
            return Cart::with(['productVariation.product.images'])
                      ->where('user_id', Auth::id())
                      ->get();
        } else {
            $guestToken = session('guest_token');
            if (!$guestToken) return collect();
            
            return Cart::with(['productVariation.product.images'])
                      ->where('guest_token', $guestToken)
                      ->get();
        }
    }
    
    private function findCartItem($id)
    {
        if (Auth::check()) {
            return Cart::where('id', $id)->where('user_id', Auth::id())->first();
        } else {
            $guestToken = session('guest_token');
            return Cart::where('id', $id)->where('guest_token', $guestToken)->first();
        }
    }
    
    public function getCartCount()
    {
        return $this->getCartItems()->sum('qty');
    }
    
    private function getCartTotal()
    {
        return $this->getCartItems()->sum(function($item) {
            return $item->qty * $item->productVariation->price;
        });
    }
    
    public function getCartSummary()
    {
        $cartItems = $this->getCartItems();
        $total = $cartItems->sum(function($item) {
            return $item->qty * $item->productVariation->price;
        });
        
        return response()->json([
            'count' => $cartItems->sum('qty'),
            'total' => number_format($total, 2),
            'items' => $cartItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->productVariation->product->name,
                    'variation' => $item->productVariation->variation_name,
                    'price' => $item->productVariation->price,
                    'qty' => $item->qty,
                    'total' => $item->qty * $item->productVariation->price
                ];
            })
        ]);
    }
    
    // Save for Later functionality
    public function saveForLater(Request $request, $id)
    {
        $cartItem = $this->findCartItem($id);
        
        $data = [
            'product_variation_id' => $cartItem->product_variation_id,
            'qty' => $cartItem->qty
        ];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $existing = SaveForLater::where('user_id', Auth::id())
                                  ->where('product_variation_id', $cartItem->product_variation_id)
                                  ->first();
        } else {
            $data['guest_token'] = session('guest_token');
            $existing = SaveForLater::where('guest_token', session('guest_token'))
                                  ->where('product_variation_id', $cartItem->product_variation_id)
                                  ->first();
        }
        
        if ($existing) {
            $existing->increment('qty', $cartItem->qty);
        } else {
            SaveForLater::create($data);
        }
        
        $cartItem->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item saved for later',
                'cartCount' => $this->getCartCount(),
                'saveForLaterCount' => $this->getSaveForLaterCount()
            ]);
        }
        
        return redirect()->route('cart.index')->with('success', 'Item saved for later');
    }
    
    public function moveToCart(Request $request, $id)
    {
        $saveForLaterItem = $this->findSaveForLaterItem($id);
        $variation = $saveForLaterItem->productVariation;
        
        if ($variation->stock < $saveForLaterItem->qty) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available'
                ], 400);
            }
            return redirect()->route('cart.index')->with('error', 'Insufficient stock available');
        }
        
        $data = [
            'product_variation_id' => $saveForLaterItem->product_variation_id,
            'qty' => $saveForLaterItem->qty
        ];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $existing = Cart::where('user_id', Auth::id())
                           ->where('product_variation_id', $saveForLaterItem->product_variation_id)
                           ->first();
        } else {
            $data['guest_token'] = session('guest_token');
            $existing = Cart::where('guest_token', session('guest_token'))
                           ->where('product_variation_id', $saveForLaterItem->product_variation_id)
                           ->first();
        }
        
        if ($existing) {
            if ($variation->stock < ($existing->qty + $saveForLaterItem->qty)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot add more items. Stock limit reached.'
                    ], 400);
                }
                return redirect()->route('cart.index')->with('error', 'Cannot add more items. Stock limit reached.');
            }
            $existing->increment('qty', $saveForLaterItem->qty);
        } else {
            Cart::create($data);
        }
        
        $saveForLaterItem->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item moved to cart',
                'cartCount' => $this->getCartCount(),
                'saveForLaterCount' => $this->getSaveForLaterCount()
            ]);
        }
        
        return redirect()->route('cart.index')->with('success', 'Item moved to cart');
    }
    
    public function removeSaveForLater(Request $request, $id)
    {
        $saveForLaterItem = $this->findSaveForLaterItem($id);
        $saveForLaterItem->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from save for later',
                'saveForLaterCount' => $this->getSaveForLaterCount()
            ]);
        }
        
        return redirect()->route('cart.index')->with('success', 'Item removed from save for later');
    }
    
    public function removeWithOptions(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:remove,wishlist'
        ]);
        
        $cartItem = $this->findCartItem($id);
        
        if ($request->action === 'wishlist') {
            // Move to wishlist
            $data = [
                'product_id' => $cartItem->productVariation->product_id
            ];
            
            if (Auth::check()) {
                $data['user_id'] = Auth::id();
                $existing = Wishlist::where('user_id', Auth::id())
                                  ->where('product_id', $cartItem->productVariation->product_id)
                                  ->first();
            } else {
                $data['guest_token'] = session('guest_token');
                $existing = Wishlist::where('guest_token', session('guest_token'))
                                  ->where('product_id', $cartItem->productVariation->product_id)
                                  ->first();
            }
            
            if (!$existing) {
                Wishlist::create($data);
            }
            
            $cartItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Item moved to wishlist',
                'cartCount' => $this->getCartCount()
            ]);
        } else {
            // Just remove
            $cartItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cartCount' => $this->getCartCount()
            ]);
        }
    }
    
    protected function getSaveForLaterItems()
    {
        if (Auth::check()) {
            return SaveForLater::with(['productVariation.product.images'])
                             ->where('user_id', Auth::id())
                             ->get();
        } else {
            $guestToken = session('guest_token');
            if (!$guestToken) return collect();
            
            return SaveForLater::with(['productVariation.product.images'])
                             ->where('guest_token', $guestToken)
                             ->get();
        }
    }
    
    protected function getSaveForLaterCount()
    {
        if (Auth::check()) {
            return SaveForLater::where('user_id', Auth::id())->sum('qty');
        } else {
            $guestToken = session('guest_token');
            if (!$guestToken) return 0;
            
            return SaveForLater::where('guest_token', $guestToken)->sum('qty');
        }
    }
    
    protected function findSaveForLaterItem($id)
    {
        if (Auth::check()) {
            return SaveForLater::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();
        } else {
            return SaveForLater::where('id', $id)
                             ->where('guest_token', session('guest_token'))
                             ->firstOrFail();
        }
    }
}
