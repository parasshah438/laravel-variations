@extends('layouts.app')

@section('title', 'Shopping Cart - E-Commerce Store')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-cart3"></i> My Cart & Saved Items
            </h2>
            
            <!-- Show message when both cart and saved items are empty -->
            @if($cartItems->count() == 0 && $saveForLaterItems->count() == 0)
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h3 class="text-muted mt-3">Your cart is empty</h3>
                    <p>You haven't added any items to your cart or saved for later yet.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop"></i> Start Shopping
                    </a>
                </div>
            @else
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        
                        <!-- Save for Later Section (Always show at top if items exist) -->
                        @if($saveForLaterItems->count() > 0)
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h5 class="mb-0 text-warning">
                                    <i class="bi bi-bookmark-fill"></i> Saved for Later ({{ $saveForLaterItems->count() }} items)
                                </h5>
                                <small class="text-muted">Items you've saved to purchase later</small>
                            </div>
                            <div class="card-body">
                                @foreach($saveForLaterItems as $item)
                                    <div class="row align-items-center save-for-later-item mb-3 pb-3 border-bottom" data-item-id="{{ $item->id }}">
                                        <!-- Product Image -->
                                        <div class="col-6 col-sm-4 col-md-2">
                                            @if($item->productVariation->product->mainImage())
                                                <img src="{{ $item->productVariation->product->mainImage()->image_path }}" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}"
                                                     onerror="this.src='https://via.placeholder.com/100x100?text=Product'">
                                            @else
                                                <img src="https://via.placeholder.com/100x100?text=Product" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}">
                                            @endif
                                            <div class="badge bg-warning text-dark mt-1 d-none d-sm-block">
                                                <i class="bi bi-bookmark"></i> Saved
                                            </div>
                                        </div>
                                        
                                        <!-- Product Details -->
                                        <div class="col-6 col-sm-8 col-md-4">
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
                                            <div class="small text-muted">Quantity: {{ $item->qty }}</div>
                                            @if($item->productVariation->stock >= $item->qty)
                                                <div class="small text-success">
                                                    <i class="bi bi-check-circle"></i> In Stock ({{ $item->productVariation->stock }} available)
                                                </div>
                                            @elseif($item->productVariation->stock > 0)
                                                <div class="small text-warning">
                                                    <i class="bi bi-exclamation-triangle"></i> Limited Stock ({{ $item->productVariation->stock }} available)
                                                </div>
                                            @else
                                                <div class="small text-danger">
                                                    <i class="bi bi-x-circle"></i> Out of Stock
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Price -->
                                        <div class="col-6 col-sm-4 col-md-2">
                                            <div class="fw-bold">₹{{ number_format($item->productVariation->price, 2) }}</div>
                                            @if($item->qty > 1)
                                                <small class="text-muted">Total: ₹{{ number_format($item->productVariation->price * $item->qty, 2) }}</small>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="col-6 col-sm-8 col-md-4">
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($item->productVariation->stock >= $item->qty)
                                                    <button class="btn btn-primary btn-sm move-to-cart-btn flex-fill" 
                                                            data-item-id="{{ $item->id }}">
                                                        <i class="bi bi-cart-plus"></i> 
                                                        <span class="d-none d-sm-inline">Move to Cart</span>
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                                        <i class="bi bi-cart-x"></i> 
                                                        <span class="d-none d-sm-inline">Out of Stock</span>
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline-danger btn-sm remove-save-for-later-btn" 
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <!-- Save for Later Summary -->
                                <div class="row mt-3 pt-3 border-top bg-light rounded p-2">
                                    <div class="col-12 col-md-8">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> 
                                            Items saved for later are kept for 90 days. Move them to cart when ready to purchase.
                                        </small>
                                    </div>
                                    <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                                        <strong>Total Saved Value: ₹{{ number_format($saveForLaterItems->sum(function($item) { return $item->qty * $item->productVariation->price; }), 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Cart Items Section -->
                        @if($cartItems->count() > 0)
                        <div class="card">
                            <div class="card-header bg-primary bg-opacity-10">
                                <h5 class="mb-0 text-primary">
                                    <i class="bi bi-cart-check-fill"></i> Cart Items ({{ $cartItems->count() }} items)
                                </h5>
                                <small class="text-muted">Ready for checkout</small>
                            </div>
                            <div class="card-body">
                                @foreach($cartItems as $item)
                                    <div class="row align-items-center cart-item mb-3 pb-3 border-bottom" data-item-id="{{ $item->id }}">
                                        <!-- Product Image -->
                                        <div class="col-6 col-sm-4 col-md-2">
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
                                        <div class="col-6 col-sm-8 col-md-3">
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
                                        <div class="col-6 col-sm-4 col-md-2">
                                            <div class="fw-bold">₹{{ number_format($item->productVariation->price, 2) }}</div>
                                        </div>
                                        
                                        <!-- Quantity -->
                                        <div class="col-6 col-sm-8 col-md-2">
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" data-action="decrease">-</button>
                                                <input type="number" class="form-control text-center qty-input" 
                                                       value="{{ $item->qty }}" min="1" max="{{ $item->productVariation->stock }}"
                                                       data-item-id="{{ $item->id }}">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" data-action="increase">+</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Total & Actions -->
                                        <div class="col-12 col-md-3 mt-2 mt-md-0">
                                            <div class="fw-bold item-total">₹{{ number_format($item->qty * $item->productVariation->price, 2) }}</div>
                                            <div class="mt-2">
                                                <div class="d-flex flex-wrap gap-1">
                                                    <button class="btn btn-link text-primary p-0 save-for-later-btn" 
                                                            data-item-id="{{ $item->id }}" title="Save for Later">
                                                        <i class="bi bi-bookmark"></i> 
                                                        <span class="d-none d-sm-inline">Save for Later</span>
                                                    </button>
                                                    <button class="btn btn-link text-danger p-0 remove-item-btn" 
                                                            data-item-id="{{ $item->id }}" title="Remove">
                                                        <i class="bi bi-trash"></i> 
                                                        <span class="d-none d-sm-inline">Remove</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                            <!-- Empty Cart Message -->
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-cart-x display-1 text-muted"></i>
                                    <h4 class="text-muted mt-3">Your cart is empty</h4>
                                    <p class="text-muted">Add some products to your cart to get started</p>
                                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                                        <i class="bi bi-shop"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                
                <!-- Cart Summary Sidebar -->
                @if($cartItems->count() > 0)
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 1rem;">
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
                                    <span id="subtotal">₹{{ number_format($total, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none !important;">
                                    <span>Discount:</span>
                                    <span class="text-success" id="discountAmount">-₹0.00</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span class="text-success">Free</span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total:</span>
                                    <span id="finalTotal">₹{{ number_format($total, 2) }}</span>
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
                                        <span class="text-success">₹10 OFF</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-primary fw-bold">SAVE20</span>
                                        <span class="text-success">₹20 OFF</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">NEWUSER15</span>
                                        <span class="text-success">₹15 OFF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

<!-- Remove Options Modal -->
<div class="modal fade" id="removeOptionsModal" tabindex="-1" aria-labelledby="removeOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeOptionsModalLabel">Remove Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>What would you like to do with this item?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="moveToWishlistBtn">
                        <i class="bi bi-heart"></i> Move to Wishlist
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="removeCompletelyBtn">
                        <i class="bi bi-trash"></i> Remove Completely
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Remove item button click
    $('.remove-item-btn').click(function() {
        const itemId = $(this).data('item-id');
        $('#removeOptionsModal').data('item-id', itemId).modal('show');
    });
    
    // Save for later button click
    $('.save-for-later-btn').click(function() {
        const itemId = $(this).data('item-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        
        $.ajax({
            url: `/cart/${itemId}/save-for-later`,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload(); // Reload to update both sections
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to save item', 'danger');
                button.prop('disabled', false).html('<i class="bi bi-bookmark"></i> Save for Later');
            }
        });
    });
    
    // Move to cart button click (from save for later)
    $('.move-to-cart-btn').click(function() {
        const itemId = $(this).data('item-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Moving...');
        
        $.ajax({
            url: `/cart/save-for-later/${itemId}/move-to-cart`,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload(); // Reload to update both sections
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to move item', 'danger');
                button.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Move to Cart');
            }
        });
    });
    
    // Remove save for later item
    $('.remove-save-for-later-btn').click(function() {
        const itemId = $(this).data('item-id');
        
        if (confirm('Are you sure you want to remove this item from saved items?')) {
            $.ajax({
                url: `/cart/save-for-later/${itemId}`,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    showToast(response.message, 'success');
                    $(`.save-for-later-item[data-item-id="${itemId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        // Hide section if no more items
                        if ($('.save-for-later-item').length === 0) {
                            $('.save-for-later-section').hide();
                        }
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response.message || 'Failed to remove item', 'danger');
                }
            });
        }
    });
    
    // Modal button handlers
    $('#moveToWishlistBtn').click(function() {
        const itemId = $('#removeOptionsModal').data('item-id');
        performRemoveAction(itemId, 'wishlist');
    });
    
    $('#removeCompletelyBtn').click(function() {
        const itemId = $('#removeOptionsModal').data('item-id');
        performRemoveAction(itemId, 'remove');
    });
    
    function performRemoveAction(itemId, action) {
        $.ajax({
            url: `/cart/${itemId}/remove-with-options`,
            method: 'POST',
            data: { 
                _token: $('meta[name="csrf-token"]').attr('content'),
                action: action
            },
            success: function(response) {
                showToast(response.message, 'success');
                $('#removeOptionsModal').modal('hide');
                
                // Remove the item from view
                $(`.cart-item[data-item-id="${itemId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Check if cart is empty
                    if ($('.cart-item').length === 0) {
                        location.reload();
                    } else {
                        // Update cart totals
                        updateCartTotals();
                    }
                });
                
                // Update cart count in header
                updateCartCount();
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to remove item', 'danger');
                $('#removeOptionsModal').modal('hide');
            }
        });
    }

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
        $('#subtotal').text('₹' + newTotal);
        
        // If discount is applied, recalculate final total
        const discountAmount = parseFloat($('#discountAmount').text().replace('-₹', '')) || 0;
        const finalTotal = parseFloat(newTotal) - discountAmount;
        $('#finalTotal').text('₹' + finalTotal.toFixed(2));
    }

    function updateCartCount() {
        // Update cart count in header
        $.get('/cart/count')
        .done(function(response) {
            $('.cart-count').text(response.count);
        });
    }

    function showToast(message, type) {
        // Create and show Bootstrap toast
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        $('.toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        
        // Remove toast after it's hidden
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush
