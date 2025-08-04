<div class="products-grid row g-4">
    @forelse($products as $product)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm product-item">
                <div class="position-relative">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" class="card-img-top" alt="{{ $product->name }}" 
                             style="height: 250px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 250px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                    
                    <!-- Product badges -->
                    @if($product->variations->min('stock') <= 5 && $product->variations->min('stock') > 0)
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2">Low Stock</span>
                    @elseif($product->variations->max('stock') == 0)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Out of Stock</span>
                    @endif
                    
                    <!-- Quick actions -->
                    <div class="product-actions position-absolute top-0 end-0 m-2">
                        <button class="btn btn-sm btn-light rounded-circle wishlist-btn" 
                                data-product-id="{{ $product->id }}" title="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    
                    <!-- Quick view on hover -->
                    <div class="product-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                         style="background: rgba(0,0,0,0.7); opacity: 0; transition: opacity 0.3s;">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-eye me-1"></i>Quick View
                        </a>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column p-3">
                    <div class="mb-2">
                        @if($product->category)
                            <small class="text-muted">{{ $product->category->name }}</small>
                        @endif
                        @if($product->brand)
                            <small class="text-muted"> • {{ $product->brand->name }}</small>
                        @endif
                    </div>
                    
                    <h6 class="card-title mb-2">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="text-decoration-none text-dark">{{ Str::limit($product->name, 50) }}</a>
                    </h6>
                    
                    @if($product->description)
                        <p class="card-text text-muted small mb-2">
                            {{ Str::limit($product->description, 80) }}
                        </p>
                    @endif
                    
                    <!-- Product variations info -->
                    @if($product->variations->count() > 1)
                        <div class="mb-2">
                            <small class="text-info">
                                <i class="bi bi-palette me-1"></i>{{ $product->variations->count() }} variations
                            </small>
                        </div>
                    @endif
                    
                    <!-- Price -->
                    <div class="mb-3 mt-auto">
                        @php
                            $minPrice = $product->variations->min('price');
                            $maxPrice = $product->variations->max('price');
                        @endphp
                        
                        @if($minPrice == $maxPrice)
                            <h5 class="text-primary mb-1">₹{{ number_format($minPrice, 2) }}</h5>
                        @else
                            <h5 class="text-primary mb-1">₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}</h5>
                        @endif
                        
                        @if($product->variations->sum('stock') > 0)
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>{{ $product->variations->sum('stock') }} in stock
                            </small>
                        @else
                            <small class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>Out of Stock
                            </small>
                        @endif
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="mt-auto">
                        @if($product->variations->count() == 1)
                            <button class="btn btn-primary w-100 add-to-cart-btn" 
                                    data-variation-id="{{ $product->variations->first()->id }}"
                                    {{ $product->variations->first()->stock == 0 ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus me-1"></i>Add to Cart
                            </button>
                        @else
                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye me-1"></i>View Options
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No products found</h4>
                <p class="text-muted">Try adjusting your filters or search terms</p>
                <button class="btn btn-outline-primary" id="clear-all-filters-empty">
                    <i class="bi bi-arrow-clockwise me-2"></i>Clear All Filters
                </button>
            </div>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart-btn').click(function() {
        const variationId = $(this).data('variation-id');
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Adding...');
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                product_variation_id: variationId,
                quantity: 1,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                btn.html('<i class="bi bi-check me-2"></i>Added!').removeClass('btn-primary').addClass('btn-success');
                
                // Update cart count if exists
                if ($('#cart-count').length) {
                    $('#cart-count').text(response.cart_count);
                }
                
                // Show success message
                showToast('Product added to cart successfully!', 'success');
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    btn.html(originalText).removeClass('btn-success').addClass('btn-primary').prop('disabled', false);
                }, 2000);
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                
                if (xhr.status === 401) {
                    showToast('Please login to add products to cart', 'warning');
                } else {
                    showToast('Error adding product to cart', 'error');
                }
            }
        });
    });
    
    // Wishlist functionality
    $('.wishlist-btn').click(function() {
        const productId = $(this).data('product-id');
        const btn = $(this);
        const icon = btn.find('i');
        
        $.ajax({
            url: '{{ route("wishlist.toggle") }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.added) {
                    icon.removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                    showToast('Added to wishlist!', 'success');
                } else {
                    icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                    showToast('Removed from wishlist!', 'info');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    showToast('Please login to use wishlist', 'warning');
                } else {
                    showToast('Error updating wishlist', 'error');
                }
            }
        });
    });
    
    // Clear all filters from empty state
    $(document).on('click', '#clear-all-filters-empty', function() {
        $('#reset-all-filters').click();
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const bgClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';
    
    const toastHtml = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" id="${toastId}">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Create toast container if it doesn't exist
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
    }
    
    $('#toast-container').append(toastHtml);
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
    
    // Remove toast element after it's hidden
    $(`#${toastId}`).on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush
