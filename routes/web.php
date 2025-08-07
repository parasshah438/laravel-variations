<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\RecentlyViewedController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VisualSearchController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/products', [ShopController::class, 'getFilteredProducts'])->name('shop.products');
Route::get('/shop/filter-counts', [ShopController::class, 'getFilterCounts'])->name('shop.filter-counts');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category.show');
Route::get('/load-more', [HomeController::class, 'loadMore'])->name('products.load-more');

// Search Routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/quick', [SearchController::class, 'quickSearch'])->name('search.quick');
Route::post('/search/filter', [SearchController::class, 'filter'])->name('search.filter');
Route::post('/search/visual', [SearchController::class, 'visualSearch'])->name('search.visual');
Route::get('/search/trending', [SearchController::class, 'trending'])->name('search.trending');

// Visual Search Routes
Route::prefix('visual-search')->name('visual-search.')->group(function () {
    Route::post('/image', [VisualSearchController::class, 'searchByImage'])->name('image');
    Route::post('/camera', [VisualSearchController::class, 'searchByCamera'])->name('camera');
    Route::get('/analytics', [VisualSearchController::class, 'getAnalytics'])->name('analytics');
    
    // Debug route for testing
    Route::get('/debug', function() {
        $products = \App\Models\Product::with(['images', 'variations'])->take(5)->get();
        $debug = [];
        
        foreach ($products as $product) {
            $debug[] = [
                'id' => $product->id,
                'name' => $product->name,
                'images_count' => $product->images->count(),
                'first_image' => $product->images->first()?->image_path,
                'variations_count' => $product->variations->count(),
                'min_price' => $product->variations->min('price'),
            ];
        }
        
        return response()->json([
            'total_products' => \App\Models\Product::count(),
            'products' => $debug,
            'storage_path' => storage_path('app/public/'),
        ]);
    })->name('debug');
});

// Legacy search route (keeping for backward compatibility)
Route::get('/products/search', [HomeController::class, 'search'])->name('products.search');

Route::get('/new-shop', [ShopController::class, 'newShopPage'])->name('shop.newShopPage');

// Product routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{slug}/quick-view', [ProductController::class, 'quickView'])->name('products.quickView');
Route::get('/products/{product}/variations/{variation}', [ProductController::class, 'getVariation'])->name('products.variation');
Route::get('/product/{product}/variations', [ProductController::class, 'getVariations'])->name('products.variations');
Route::get('/product/{product}/filtered-attributes', [ProductController::class, 'getFilteredAttributes'])->name('products.filtered-attributes');

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/ajax-add', [CartController::class, 'ajaxAdd'])->name('ajaxAdd');
    Route::put('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/{id}/save-for-later', [CartController::class, 'saveForLater'])->name('saveForLater');
    Route::post('/{id}/remove-with-options', [CartController::class, 'removeWithOptions'])->name('removeWithOptions');
    Route::post('/save-for-later/{id}/move-to-cart', [CartController::class, 'moveToCart'])->name('moveToCart');
    Route::delete('/save-for-later/{id}', [CartController::class, 'removeSaveForLater'])->name('removeSaveForLater');
    Route::get('/summary', [CartController::class, 'getCartSummary'])->name('summary');
    
    // Amazon-style AJAX routes
    Route::post('/update-quantity', [CartController::class, 'updateQuantity'])->name('updateQuantity');
    Route::post('/remove', [CartController::class, 'removeItem'])->name('removeItem');
});

// Wishlist routes
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
    Route::get('/status', [WishlistController::class, 'checkStatus'])->name('status');
    Route::post('/move-to-cart', [WishlistController::class, 'moveToCart'])->name('moveToCart');
    Route::post('/move-all-to-cart', [WishlistController::class, 'moveAllToCart'])->name('moveAllToCart');
    Route::delete('/clear-all', [WishlistController::class, 'clearAll'])->name('clearAll');
    Route::delete('/{id}', [WishlistController::class, 'remove'])->name('remove');
});

// Coupon routes
Route::prefix('coupon')->name('coupon.')->group(function () {
    Route::post('/apply', [CouponController::class, 'apply'])->name('apply');
    Route::delete('/remove', [CouponController::class, 'remove'])->name('remove');
});

// Recently Viewed routes
Route::prefix('recently-viewed')->name('recently-viewed.')->group(function () {
    Route::post('/add', [RecentlyViewedController::class, 'addProduct'])->name('add');
    Route::get('/get', [RecentlyViewedController::class, 'getRecentlyViewed'])->name('get');
    Route::delete('/clear', [RecentlyViewedController::class, 'clearAll'])->name('clear');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Address routes
    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/create', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [AddressController::class, 'setDefault'])->name('set-default');
        Route::post('/quick-store', [AddressController::class, 'quickStore'])->name('quick-store');
    });
    
    // Location API routes
    Route::get('/api/states/{country}', [AddressController::class, 'getStates'])->name('api.states');
    Route::get('/api/cities/{state}', [AddressController::class, 'getCities'])->name('api.cities');
    
    // Order routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('invoice');
    });
    
    // Checkout routes
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
});

// Admin Routes - Protected by auth middleware
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Product Management
    Route::resource('products', App\Http\Controllers\Admin\AdminProductController::class);
    Route::patch('products/{product}/status', [App\Http\Controllers\Admin\AdminProductController::class, 'updateStatus'])->name('products.status');
    Route::patch('products/bulk-status', [App\Http\Controllers\Admin\AdminProductController::class, 'bulkStatusUpdate'])->name('products.bulk-status');
    Route::delete('products/bulk-delete', [App\Http\Controllers\Admin\AdminProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::delete('products/variations/{variation}', [App\Http\Controllers\Admin\AdminProductController::class, 'deleteVariation'])->name('products.variations.delete');
    Route::delete('products/images/{image}', [App\Http\Controllers\Admin\AdminProductController::class, 'deleteImage'])->name('products.images.delete');
    
    // Slider Management
    Route::resource('sliders', App\Http\Controllers\Admin\SliderController::class);
    Route::patch('sliders/{slider}/status', [App\Http\Controllers\Admin\SliderController::class, 'updateStatus'])->name('sliders.status');
    Route::post('sliders/reorder', [App\Http\Controllers\Admin\SliderController::class, 'reorder'])->name('sliders.reorder');
    
    // Order Management
    Route::get('orders', [App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->name('orders.status');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Test routes for order functionality
Route::get('/test-order-details', function() {
    $user = \App\Models\User::first();
    if (!$user) {
        return 'No users found. Please create a user first.';
    }
    
    \Illuminate\Support\Facades\Auth::login($user);
    
    $order = \App\Models\Order::with([
        'items.productVariation.product.images',
        'items.productVariation.attributeValues.attribute',
        'user'
    ])->where('user_id', $user->id)->first();
    
    if (!$order) {
        return 'No orders found for this user. Please create an order first through the checkout process.';
    }
    
    return redirect()->route('orders.show', $order->id);
});

require __DIR__.'/auth.php';
