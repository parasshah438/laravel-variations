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
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/products', [ShopController::class, 'getFilteredProducts'])->name('shop.products');
Route::get('/shop/filter-counts', [ShopController::class, 'getFilterCounts'])->name('shop.filter-counts');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category.show');
Route::get('/load-more', [HomeController::class, 'loadMore'])->name('products.load-more');
Route::get('/search', [HomeController::class, 'search'])->name('products.search');

// Product routes
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/product/{product}/variations', [ProductController::class, 'getVariations'])->name('products.variations');

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/ajax-add', [CartController::class, 'ajaxAdd'])->name('ajaxAdd');
    Route::put('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
    Route::get('/summary', [CartController::class, 'getCartSummary'])->name('summary');
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
