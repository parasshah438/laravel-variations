<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/test-order-details', function() {
    // Create a test order for demonstration
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

Route::get('/test-invoice/{id}', function($id) {
    $user = \App\Models\User::first();
    if (!$user) {
        return 'No users found.';
    }
    
    \Illuminate\Support\Facades\Auth::login($user);
    
    return app(OrderController::class)->downloadInvoice($id);
});
