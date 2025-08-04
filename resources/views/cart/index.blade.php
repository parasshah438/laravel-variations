@extends('layouts.app')

@section('title', 'Shopping Cart - E-Commerce Store')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Shopping Cart</h2>
            
            @if($cartItems->count() > 0)
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                @foreach($cartItems as $item)
                                    <div class="row align-items-center cart-item mb-3 pb-3 border-bottom" data-item-id="{{ $item->id }}">
                                        <!-- Product Image -->
                                        <div class="col-md-2">
                                            @if($item->productVariation->product->mainImage())
                                                <img src="{{ $item->productVariation->product->mainImage()->image_path }}" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}"
                                                     onerror="this.src='https://via.placeholder.com/100x100?text=Product'">
                                            @else
                                                <img src="https://via.placeholder.com/100x100?text=Product" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}">
                                            @endif
                                        </div>
                                        
                                        <!-- Product Details -->
                                        <div class="col-md-4">
                                            <h6 class="mb-1">
                                                <a href="{{ route('products.show', $item->productVariation->product->slug) }}" 
                                                   class="text-decoration-none">
                                                    {{ $item->productVariation->product->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $item->productVariation->product->brand->name }}</small>
                                            @if($item->productVariation->variation_name)
                                                <div class="small text-muted">{{ $item->productVariation->variation_name }}</div>
                                            @endif
                                            <div class="small text-success">
                                                <i class="bi bi-check-circle"></i> In Stock ({{ $item->productVariation->stock }} available)
                                            </div>
                                        </div>
                                        
                                        <!-- Price -->
                                        <div class="col-md-2">
                                            <div class="fw-bold">${{ number_format($item->productVariation->price, 2) }}</div>
                                        </div>
                                        
                                        <!-- Quantity -->
                                        <div class="col-md-2">
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" data-action="decrease">-</button>
                                                <input type="number" class="form-control text-center qty-input" 
                                                       value="{{ $item->qty }}" min="1" max="{{ $item->productVariation->stock }}"
                                                       data-item-id="{{ $item->id }}">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" data-action="increase">+</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Total & Remove -->
                                        <div class="col-md-2">
                                            <div class="fw-bold item-total">${{ number_format($item->qty * $item->productVariation->price, 2) }}</div>
                                            <button class="btn btn-link text-danger p-0 remove-item" data-item-id="{{ $item->id }}">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <!-- Coupon Section -->
                                <div class="mb-3">
                                    <label class="form-label">Coupon Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="couponCode" placeholder="Enter coupon code">
                                        <button class="btn btn-outline-primary" id="applyCouponBtn">Apply</button>
                                    </div>
                                    <div id="couponMessage" class="small mt-1"></div>
                                </div>
                                
                                <hr>
                                
                                <!-- Order Totals -->
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal ({{ $cartItems->sum('qty') }} items):</span>
                                    <span id="subtotal">${{ number_format($total, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none !important;">
                                    <span>Discount:</span>
                                    <span class="text-success" id="discountAmount">-$0.00</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span class="text-success">Free</span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total:</span>
                                    <span id="finalTotal">${{ number_format($total, 2) }}</span>
                                </div>
                                
                                <div class="d-grid gap-2 mt-3">
                                    @auth
                                        <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">
                                            <i class="bi bi-credit-card"></i> Proceed to Checkout
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                            Login to Checkout
                                        </a>
                                    @endauth
                                    
                                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Available Coupons -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Available Coupons</h6>
                            </div>
                            <div class="card-body">
                                <div class="small">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-primary fw-bold">WELCOME10</span>
                                        <span class="text-success">$10 OFF</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-primary fw-bold">SAVE20</span>
                                        <span class="text-success">$20 OFF</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">NEWUSER15</span>
                                        <span class="text-success">$15 OFF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h3 class="text-muted mt-3">Your cart is empty</h3>
                    <p>Start shopping to add items to your cart</p>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop"></i> Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Quantity controls
    $('.qty-btn').click(function() {
        const action = $(this).data('action');
        const input = $(this).siblings('.qty-input');
        let value = parseInt(input.val());
        const max = parseInt(input.attr('max'));
        
        if (action === 'increase' && value < max) {
            input.val(value + 1);
            updateCartItem(input);
        } else if (action === 'decrease' && value > 1) {
            input.val(value - 1);
            updateCartItem(input);
        }
    });

    // Quantity input change
    $('.qty-input').change(function() {
        updateCartItem($(this));
    });

    function updateCartItem(input) {
        const itemId = input.data('item-id');
        const qty = parseInt(input.val());
        const max = parseInt(input.attr('max'));
        
        if (qty > max) {
            showToast('Insufficient stock available', 'danger');
            input.val(max);
            return;
        }

        $.ajax({
            url: '/cart/' + itemId,
            method: 'PUT',
            data: { qty: qty },
            success: function(response) {
                // Update item total
                input.closest('.cart-item').find('.item-total').text('$' + response.itemTotal);
                
                // Update cart totals
                updateCartTotals(response.cartTotal);
                
                showToast(response.message, 'success');
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to update cart', 'danger');
            }
        });
    }

    // Remove item
    $('.remove-item').click(function() {
        const itemId = $(this).data('item-id');
        const cartItem = $(this).closest('.cart-item');
        
        if (confirm('Are you sure you want to remove this item?')) {
            $.ajax({
                url: '/cart/' + itemId,
                method: 'DELETE',
                success: function(response) {
                    cartItem.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if cart is empty
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        } else {
                            updateCartTotals(response.cartTotal);
                            $('#cartCount').text(response.cartCount);
                        }
                    });
                    
                    showToast(response.message, 'success');
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response.message || 'Failed to remove item', 'danger');
                }
            });
        }
    });

    // Apply coupon
    $('#applyCouponBtn').click(function() {
        const code = $('#couponCode').val().trim();
        if (!code) return;

        const btn = $(this);
        btn.prop('disabled', true).text('Applying...');

        $.post('{{ route("coupon.apply") }}', { code: code })
        .done(function(response) {
            showToast(response.message, 'success');
            $('#couponMessage').html('<span class="text-success"><i class="bi bi-check-circle"></i> ' + response.message + '</span>');
            
            // Update discount display
            $('#discountRow').show();
            $('#discountAmount').text('-$' + response.discount.toFixed(2));
            
            // Recalculate totals
            const subtotal = parseFloat($('#subtotal').text().replace('$', ''));
            const total = subtotal - response.discount;
            $('#finalTotal').text('$' + total.toFixed(2));
            
            btn.text('Applied').removeClass('btn-outline-primary').addClass('btn-success');
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            $('#couponMessage').html('<span class="text-danger"><i class="bi bi-x-circle"></i> ' + response.message + '</span>');
            btn.prop('disabled', false).text('Apply');
        });
    });

    function updateCartTotals(newTotal) {
        $('#subtotal').text('$' + newTotal);
        
        // If discount is applied, recalculate final total
        const discountAmount = parseFloat($('#discountAmount').text().replace('-$', '')) || 0;
        const finalTotal = parseFloat(newTotal) - discountAmount;
        $('#finalTotal').text('$' + finalTotal.toFixed(2));
    }
});
</script>
@endpush
