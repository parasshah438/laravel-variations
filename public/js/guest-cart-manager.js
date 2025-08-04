/**
 * Guest Data Migration and Cart Management
 * This script handles guest cart, wishlist, and recently viewed products
 */

// Initialize guest token if not exists
if (!localStorage.getItem('guest_token')) {
    localStorage.setItem('guest_token', 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
}

// Cart Management Functions
const CartManager = {
    // Add product to cart
    addToCart: function(productVariationId, qty = 1, callback = null) {
        $.ajax({
            url: '/cart/add',
            method: 'POST',
            data: {
                product_variation_id: productVariationId,
                qty: qty,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    updateCounters();
                    if (callback) callback(response);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Error adding item to cart', 'error');
            }
        });
    },

    // Update cart item quantity
    updateCartItem: function(cartItemId, qty, callback = null) {
        $.ajax({
            url: `/cart/${cartItemId}`,
            method: 'PUT',
            data: {
                qty: qty,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    updateCounters();
                    if (callback) callback(response);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Error updating cart', 'error');
            }
        });
    },

    // Remove item from cart
    removeFromCart: function(cartItemId, callback = null) {
        $.ajax({
            url: `/cart/${cartItemId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    updateCounters();
                    if (callback) callback(response);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Error removing item', 'error');
            }
        });
    }
};

// Wishlist Management Functions
const WishlistManager = {
    // Toggle product in wishlist
    toggle: function(productId, callback = null) {
        $.ajax({
            url: '/wishlist/toggle',
            method: 'POST',
            data: {
                product_id: productId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, response.inWishlist ? 'success' : 'info');
                    updateCounters();
                    if (callback) callback(response);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Error updating wishlist', 'error');
            }
        });
    },

    // Remove from wishlist
    remove: function(wishlistItemId, callback = null) {
        $.ajax({
            url: `/wishlist/${wishlistItemId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    updateCounters();
                    if (callback) callback(response);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Error removing item', 'error');
            }
        });
    }
};

// Recently Viewed Products Management
const RecentlyViewedManager = {
    // Add product to recently viewed
    addProduct: function(productId) {
        $.ajax({
            url: '/recently-viewed/add',
            method: 'POST',
            data: {
                product_id: productId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Silent success - no toast needed for recently viewed
            },
            error: function(xhr) {
                // Silent error - recently viewed is not critical
                console.log('Recently viewed tracking failed');
            }
        });
    },

    // Get recently viewed products
    getProducts: function(limit = 10, callback = null) {
        $.ajax({
            url: '/recently-viewed/get',
            method: 'GET',
            data: { limit: limit },
            success: function(response) {
                if (response.success && callback) {
                    callback(response.products);
                }
            },
            error: function(xhr) {
                console.log('Failed to load recently viewed products');
            }
        });
    }
};

// Quantity Control Functions
function setupQuantityControls() {
    $(document).on('click', '.qty-btn-minus', function() {
        const input = $(this).siblings('.qty-input');
        const currentVal = parseInt(input.val()) || 1;
        const minVal = parseInt(input.attr('min')) || 1;
        
        if (currentVal > minVal) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    $(document).on('click', '.qty-btn-plus', function() {
        const input = $(this).siblings('.qty-input');
        const currentVal = parseInt(input.val()) || 1;
        const maxVal = parseInt(input.attr('max')) || 999;
        
        if (currentVal < maxVal) {
            input.val(currentVal + 1).trigger('change');
        }
    });

    // Handle direct input changes
    $(document).on('change', '.qty-input', function() {
        const input = $(this);
        const val = parseInt(input.val()) || 1;
        const minVal = parseInt(input.attr('min')) || 1;
        const maxVal = parseInt(input.attr('max')) || 999;
        
        // Ensure value is within bounds
        if (val < minVal) input.val(minVal);
        if (val > maxVal) input.val(maxVal);
        
        // If this is a cart item, update the cart
        const cartItemId = input.data('cart-item-id');
        if (cartItemId) {
            CartManager.updateCartItem(cartItemId, input.val(), function(response) {
                // Update the item total if element exists
                const itemTotalElement = input.closest('.cart-item').find('.item-total');
                if (itemTotalElement.length && response.itemTotal) {
                    itemTotalElement.text('₹' + response.itemTotal);
                }
                
                // Update cart total if element exists
                const cartTotalElement = $('.cart-total');
                if (cartTotalElement.length && response.cartTotal) {
                    cartTotalElement.text('₹' + response.cartTotal);
                }
            });
        }
    });
}

// Initialize everything when document is ready
$(document).ready(function() {
    setupQuantityControls();
    updateCounters();
    
    // Track page views for recently viewed products
    const productId = $('meta[name="product-id"]').attr('content');
    if (productId) {
        RecentlyViewedManager.addProduct(productId);
    }
});
