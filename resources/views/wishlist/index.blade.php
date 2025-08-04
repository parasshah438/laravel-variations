@extends('layouts.app')

@section('title', 'My Wishlist - E-Commerce Store')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Wishlist ({{ $wishlistItems->count() }} items)</h2>
                
                @if($wishlistItems->count() > 0)
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" id="move-all-to-cart-btn">
                            <i class="bi bi-cart-plus me-2"></i>Move All to Cart
                        </button>
                        <button class="btn btn-outline-danger" id="clear-all-btn">
                            <i class="bi bi-trash me-2"></i>Clear All
                        </button>
                    </div>
                @endif
            </div>
            
            @if($wishlistItems->count() > 0)
                <div class="row">
                    @foreach($wishlistItems as $item)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100">
                                <div class="position-relative">
                                    @if($item->product->mainImage())
                                        <img src="{{ $item->product->mainImage()->image_path }}" 
                                             class="card-img-top" alt="{{ $item->product->name }}" 
                                             style="height: 250px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/300x250?text={{ urlencode($item->product->name) }}'">
                                    @else
                                        <img src="https://via.placeholder.com/300x250?text={{ urlencode($item->product->name) }}" 
                                             class="card-img-top" alt="{{ $item->product->name }}" 
                                             style="height: 250px; object-fit: cover;">
                                    @endif
                                    
                                    <!-- Remove from Wishlist Button -->
                                    <button class="btn btn-danger remove-wishlist-btn position-absolute top-0 end-0 m-2 rounded-circle" 
                                            data-item-id="{{ $item->id }}" title="Remove from Wishlist">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                    
                                    @if($item->product->variations->where('stock', '>', 0)->count() == 0)
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <span class="badge bg-danger fs-6">Out of Stock</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $item->product->name }}</h6>
                                    <p class="card-text text-muted small">{{ $item->product->brand->name }}</p>
                                    <p class="card-text flex-grow-1">{{ Str::limit($item->product->description, 80) }}</p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                @if($item->product->variations->count() > 1)
                                                    <span class="fw-bold">${{ number_format($item->product->minPrice(), 2) }} - ${{ number_format($item->product->maxPrice(), 2) }}</span>
                                                @else
                                                    <span class="fw-bold">${{ number_format($item->product->variations->first()->price, 2) }}</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $item->product->category->name }}</small>
                                        </div>
                                        
                                        @if($item->product->variations->where('stock', '>', 0)->count() > 0)
                                            @if($item->product->variations->count() == 1)
                                                <div class="d-flex gap-1 mb-2">
                                                    <button class="btn btn-primary btn-sm flex-grow-1 add-to-cart-btn" 
                                                            data-variation-id="{{ $item->product->variations->first()->id }}"
                                                            data-max-stock="{{ $item->product->variations->first()->stock }}">
                                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                                    </button>
                                                    <button class="btn btn-success btn-sm flex-grow-1 move-to-cart-btn" 
                                                            data-item-id="{{ $item->id }}"
                                                            title="Move to cart and remove from wishlist">
                                                        <i class="bi bi-arrow-right"></i> Move to Cart
                                                    </button>
                                                </div>
                                            @else
                                                <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-primary btn-sm w-100 mb-2">
                                                    <i class="bi bi-eye"></i> View Options
                                                </a>
                                            @endif
                                        @else
                                            <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>
                                                Out of Stock
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-outline-primary btn-sm w-100">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-heart display-1 text-muted"></i>
                    <h3 class="text-muted mt-3">Your wishlist is empty</h3>
                    <p>Add items to your wishlist to save them for later</p>
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
    // Remove from wishlist
    $('.remove-wishlist-btn').click(function() {
        const btn = $(this);
        const itemId = btn.data('item-id');
        const card = btn.closest('.col-lg-3');
        
        if (confirm('Remove this item from your wishlist?')) {
            btn.prop('disabled', true);
            
            $.ajax({
                url: '/wishlist/' + itemId,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    card.fadeOut(300, function() {
                        $(this).remove();
                        updateWishlistCount();
                        
                        // Check if wishlist is empty
                        if ($('.col-lg-3').length === 0) {
                            location.reload();
                        }
                    });
                    
                    showToast(response.message, 'success');
                    $('#wishlistCount').text(response.wishlistCount);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response.message || 'Failed to remove item', 'danger');
                    btn.prop('disabled', false);
                }
            });
        }
    });
    
    // Add to cart from wishlist (keeps item in wishlist)
    $('.add-to-cart-btn').click(function() {
        const btn = $(this);
        const variationId = btn.data('variation-id');
        const maxStock = btn.data('max-stock');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
        
        $.post('{{ route("cart.add") }}', {
            _token: '{{ csrf_token() }}',
            product_variation_id: variationId,
            qty: 1
        })
        .done(function(response) {
            showToast(response.message, 'success');
            updateCartCount();
            $('#cartCount').text(response.cartCount);
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showToast(response.message || 'Failed to add to cart', 'danger');
        })
        .always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Add to Cart');
        });
    });

    // Move to cart (removes from wishlist)
    $('.move-to-cart-btn').click(function() {
        const btn = $(this);
        const itemId = btn.data('item-id');
        const card = btn.closest('.col-lg-3');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Moving...');
        
        $.post('{{ route("wishlist.moveToCart") }}', {
            _token: '{{ csrf_token() }}',
            item_id: itemId
        })
        .done(function(response) {
            if (response.redirect) {
                window.location.href = response.redirect;
                return;
            }
            
            card.fadeOut(300, function() {
                $(this).remove();
                updateWishlistCount();
                
                // Check if wishlist is empty
                if ($('.col-lg-3').length === 0) {
                    location.reload();
                }
            });
            
            showToast(response.message, 'success');
            $('#wishlistCount').text(response.wishlistCount);
            $('#cartCount').text(response.cartCount);
            updateCartCount();
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showToast(response.message || 'Failed to move to cart', 'danger');
        })
        .always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-right"></i> Move to Cart');
        });
    });

    // Move all to cart
    $('#move-all-to-cart-btn').click(function() {
        const btn = $(this);
        
        if (confirm('Move all eligible items to cart? Items with multiple variations or out of stock will be skipped.')) {
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Moving All...');
            
            $.post('{{ route("wishlist.moveAllToCart") }}', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showToast(response.message, 'success');
                
                if (response.movedCount > 0) {
                    // Reload page to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
                
                updateCartCount();
                $('#wishlistCount').text(response.wishlistCount);
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to move items to cart', 'danger');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Move All to Cart');
            });
        }
    });

    // Clear all wishlist
    $('#clear-all-btn').click(function() {
        const btn = $(this);
        
        if (confirm('Are you sure you want to remove all items from your wishlist? This action cannot be undone.')) {
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Clearing...');
            
            $.ajax({
                url: '{{ route("wishlist.clearAll") }}',
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    
                    // Reload page to show empty state
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                    
                    $('#wishlistCount').text(0);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response.message || 'Failed to clear wishlist', 'danger');
                    btn.prop('disabled', false).html('<i class="bi bi-trash me-2"></i>Clear All');
                }
            });
        }
    });
    
    // Update wishlist count helper function
    function updateWishlistCount() {
        const remainingItems = $('.col-lg-3').length - 1; // -1 because current item will be removed
        $('.h2').text('My Wishlist (' + remainingItems + ' items)');
        
        if (remainingItems === 0) {
            $('#move-all-to-cart-btn, #clear-all-btn').fadeOut();
        }
    }
});
</script>
@endpush
