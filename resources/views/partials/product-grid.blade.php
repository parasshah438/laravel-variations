<div class="row">
    @foreach($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 product-card">
                <div class="position-relative">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none">
                        @if($product->mainImage())
                            <img src="{{ $product->mainImage()->image_path }}" 
                                 class="card-img-top" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/300x250?text={{ urlencode($product->name) }}'">
                        @else
                            <img src="https://via.placeholder.com/300x250?text={{ urlencode($product->name) }}" 
                                 class="card-img-top" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;">
                        @endif
                    </a>
                    
                    <!-- Action Buttons -->
                    <div class="product-actions position-absolute top-0 end-0 m-2">
                        <button class="btn btn-outline-danger wishlist-btn rounded-circle mb-1" 
                                data-product-id="{{ $product->id }}" title="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                        <button class="btn btn-outline-primary quick-view-btn rounded-circle" 
                                data-product-id="{{ $product->id }}" 
                                data-product-slug="{{ $product->slug }}"
                                title="Quick View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    @if($product->variations->where('stock', '>', 0)->count() == 0)
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <span class="badge bg-danger fs-6">Out of Stock</span>
                        </div>
                    @endif
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">
                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
                    </h6>
                    <p class="card-text text-muted small">{{ $product->brand->name }}</p>
                    <p class="card-text flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                @if($product->variations->count() > 1)
                                    @php
                                        $minPrice = $product->minPrice();
                                        $maxPrice = $product->maxPrice();
                                    @endphp
                                    @if($minPrice && $maxPrice)
                                        <span class="fw-bold">₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}</span>
                                    @else
                                        <span class="fw-bold text-muted">Price not set</span>
                                    @endif
                                @elseif($product->variations->count() == 1 && $product->variations->first())
                                    <span class="fw-bold">₹{{ number_format($product->variations->first()->price, 2) }}</span>
                                @else
                                    <span class="fw-bold text-muted">Price not available</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $product->category->name }}</small>
                        </div>
                        
                        @php $stock = $product->variations->first(); @endphp
                        <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="mt-auto add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if($stock && $stock->stock > 0)
                            <div class="input-group mb-2">
                                <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity(this)">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="{{ $stock->stock }}" class="form-control text-center" style="max-width: 80px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity(this)">+</button>
                            </div>
                            @endif
                            @if($stock && $stock->stock <= 0)
                            <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                            @elseif($stock && $stock->stock <= 5 && $stock->stock > 0)
                            <div class="text-danger small">Only {{ $stock->stock }} left in stock!</div>
                            <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                            @elseif($stock && $stock->stock > 0)
                            <div class="text-success small">In Stock</div>
                            <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                            @else
                            <button class="btn btn-secondary w-100" disabled>Not Available</button>
                            @endif
                        </form>
                        
                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary w-100 mt-2">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading product details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-actions {
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.product-actions .btn {
    backdrop-filter: blur(10px);
    background-color: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.1);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-actions .btn:hover {
    background-color: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

.quick-view-modal-img {
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
}

.modal-body {
    max-height: 80vh;
    overflow-y: auto;
}
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart form submission
    $('.add-to-cart-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
        
        $.post(form.attr('action'), form.serialize())
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
            btn.prop('disabled', false).html(originalText);
        });
    });
    
    // Wishlist toggle
    $('.wishlist-btn').click(function() {
        const btn = $(this);
        const productId = btn.data('product-id');
        const icon = btn.find('i');
        
        btn.prop('disabled', true);
        
        $.post('{{ route("wishlist.toggle") }}', {
            _token: "{{ csrf_token() }}",
            product_id: productId
        })
        .done(function(response) {
            showToast(response.message, 'success');
            if (response.inWishlist) {
                icon.removeClass('bi-heart').addClass('bi-heart-fill');
                btn.removeClass('btn-outline-danger').addClass('btn-danger');
            } else {
                icon.removeClass('bi-heart-fill').addClass('bi-heart');
                btn.removeClass('btn-danger').addClass('btn-outline-danger');
            }
            $('#wishlistCount').text(response.wishlistCount);
        })
        .fail(function() {
            showToast('Failed to update wishlist', 'danger');
        })
        .always(function() {
            btn.prop('disabled', false);
        });
    });
    
    // Quick View functionality
    $('.quick-view-btn').click(function() {
        const productSlug = $(this).data('product-slug');
        const productId = $(this).data('product-id');
        
        // Show modal immediately
        $('#quickViewModal').modal('show');
        
        // Load product details
        loadQuickView(productSlug);
    });
    
    // Handle modal quick view form submissions
    $(document).on('submit', '.quick-view-add-to-cart', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
        
        $.post(form.attr('action'), form.serialize())
        .done(function(response) {
            showToast(response.message, 'success');
            updateCartCount();
            $('#cartCount').text(response.cartCount);
            $('#quickViewModal').modal('hide');
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showToast(response.message || 'Failed to add to cart', 'danger');
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });
});

// Quantity control functions
function increaseQuantity(button) {
    const input = $(button).siblings('input[name="quantity"]');
    const currentValue = parseInt(input.val());
    const maxValue = parseInt(input.attr('max'));
    
    if (currentValue < maxValue) {
        input.val(currentValue + 1);
    }
}

function decreaseQuantity(button) {
    const input = $(button).siblings('input[name="quantity"]');
    const currentValue = parseInt(input.val());
    const minValue = parseInt(input.attr('min'));
    
    if (currentValue > minValue) {
        input.val(currentValue - 1);
    }
}

// Quick View Modal functions
function loadQuickView(productSlug) {
    $.get(`/products/${productSlug}/quick-view`)
    .done(function(response) {
        $('#quickViewContent').html(response);
    })
    .fail(function() {
        $('#quickViewContent').html(`
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                <h5 class="mt-3">Failed to load product details</h5>
                <p class="text-muted">Please try again later.</p>
            </div>
        `);
    });
}

// Handle variation selection in quick view
$(document).on('change', '.quick-view-variation-select', function() {
    const variationId = $(this).val();
    const productId = $(this).data('product-id');
    
    if (variationId) {
        updateQuickViewVariation(productId, variationId);
    }
});

function updateQuickViewVariation(productId, variationId) {
    $.get(`/products/${productId}/variations/${variationId}`)
    .done(function(response) {
        // Update price
        $('#quickViewPrice').text(`₹${parseFloat(response.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
        
        // Update stock info
        const stockInfo = $('#quickViewStockInfo');
        const quantityInput = $('#quickViewQuantity');
        const addToCartBtn = $('#quickViewAddToCartBtn');
        
        if (response.stock <= 0) {
            stockInfo.html('<span class="text-danger">Out of Stock</span>');
            addToCartBtn.prop('disabled', true).text('Out of Stock');
            quantityInput.prop('disabled', true);
        } else if (response.stock <= 5) {
            stockInfo.html(`<span class="text-warning">Only ${response.stock} left in stock!</span>`);
            addToCartBtn.prop('disabled', false).text('Add to Cart');
            quantityInput.prop('disabled', false).attr('max', response.stock);
        } else {
            stockInfo.html('<span class="text-success">In Stock</span>');
            addToCartBtn.prop('disabled', false).text('Add to Cart');
            quantityInput.prop('disabled', false).attr('max', response.stock);
        }
        
        // Update variation input
        $('input[name="variation_id"]').val(variationId);
        
        // Reset quantity to 1
        quantityInput.val(1);
    })
    .fail(function() {
        showToast('Failed to load variation details', 'danger');
    });
}
</script>
@endpush
