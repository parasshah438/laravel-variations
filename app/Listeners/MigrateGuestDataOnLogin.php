<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\RecentlyViewedProduct;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

class MigrateGuestDataOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $migrationCount = 0;
        
        $migrationCount += $this->migrateGuestCart();
        $migrationCount += $this->migrateGuestWishlist();
        $migrationCount += $this->migrateRecentlyViewedProducts();
        
        // Set session flag if any data was migrated
        if ($migrationCount > 0) {
            session(['login_success' => true]);
        }
    }

    /**
     * Migrate guest cart items to user cart
     */
    private function migrateGuestCart(): int
    {
        $guestToken = session('guest_token');
        
        if (!$guestToken || !Auth::check()) {
            return 0;
        }

        $guestCartItems = Cart::where('guest_token', $guestToken)->get();
        $migratedCount = 0;

        foreach ($guestCartItems as $guestItem) {
            $existingItem = Cart::where('user_id', Auth::id())
                              ->where('product_variation_id', $guestItem->product_variation_id)
                              ->first();

            if ($existingItem) {
                // Merge quantities, respecting stock limits
                $newQty = $existingItem->qty + $guestItem->qty;
                $maxStock = $guestItem->productVariation->stock;
                
                $existingItem->update([
                    'qty' => min($newQty, $maxStock)
                ]);
                
                $guestItem->delete();
                $migratedCount++;
            } else {
                // Transfer guest item to user
                $guestItem->update([
                    'user_id' => Auth::id(),
                    'guest_token' => null
                ]);
                $migratedCount++;
            }
        }

        session()->forget('guest_token');
        return $migratedCount;
    }

    /**
     * Migrate guest wishlist items to user wishlist
     */
    private function migrateGuestWishlist(): int
    {
        $guestToken = session('guest_token');
        
        if (!$guestToken || !Auth::check()) {
            return 0;
        }

        $guestWishlistItems = Wishlist::where('guest_token', $guestToken)->get();
        $migratedCount = 0;

        foreach ($guestWishlistItems as $guestItem) {
            $existingItem = Wishlist::where('user_id', Auth::id())
                                  ->where('product_id', $guestItem->product_id)
                                  ->first();

            if (!$existingItem) {
                // Transfer guest wishlist item to user
                $guestItem->update([
                    'user_id' => Auth::id(),
                    'guest_token' => null
                ]);
                $migratedCount++;
            } else {
                // Item already exists in user wishlist, delete guest item
                $guestItem->delete();
            }
        }
        
        return $migratedCount;
    }

    /**
     * Migrate guest recently viewed products to user
     */
    private function migrateRecentlyViewedProducts(): int
    {
        $guestToken = session('guest_token');
        
        if (!$guestToken || !Auth::check()) {
            return 0;
        }

        $guestViewedItems = RecentlyViewedProduct::where('guest_token', $guestToken)->get();
        $migratedCount = 0;

        foreach ($guestViewedItems as $guestItem) {
            $existingItem = RecentlyViewedProduct::where('user_id', Auth::id())
                                               ->where('product_id', $guestItem->product_id)
                                               ->first();

            if ($existingItem) {
                // Update the existing item's timestamp
                $existingItem->touch();
                $guestItem->delete();
            } else {
                // Transfer guest viewed item to user
                $guestItem->update([
                    'user_id' => Auth::id(),
                    'guest_token' => null
                ]);
                $migratedCount++;
            }
        }
        
        return $migratedCount;
    }
}
