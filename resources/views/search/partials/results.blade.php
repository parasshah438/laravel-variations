@if($results['query_info']['has_results'])
    <!-- Visual Search Special Header -->
    @if(request('visual'))
    <div class="visual-search-results-header mb-4">
        <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="visual-search-icon me-3">
                                <i class="bi bi-camera-fill fs-2"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Visual Search Results</h5>
                                <p class="mb-0 opacity-75">Found {{ count($results['products']) }} similar products based on your image</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="search-stats">
                            <span class="badge bg-white bg-opacity-25 fs-6 px-3 py-2">
                                <i class="bi bi-lightning-fill me-1"></i>
                                Smart Match Technology
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4" id="products-grid">
        @foreach($results['products'] as $product)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card product-card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 16px;">
                    <!-- Product Image Container -->
                    <div class="product-image-container position-relative overflow-hidden" style="height: 300px; border-radius: 16px 16px 0 0;">
                        @php
                            $firstImage = $product->images->first();
                            $totalStock = $product->variations->sum('stock');
                        @endphp
                        
                        @if($firstImage)
                            <img src="{{ asset('storage/' . $firstImage->image_path) }}" 
                                 class="product-image w-100 h-100 object-fit-cover" 
                                 alt="{{ $product->name }}"
                                 loading="lazy"
                                 style="transition: transform 0.4s ease;">
                        @else
                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                <div class="text-center text-muted">
                                    <i class="bi bi-image fs-1 mb-2"></i>
                                    <p class="small mb-0">No image available</p>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Visual Search Badge for matched products -->
                        @if(request('visual'))
                        <div class="position-absolute top-0 start-0 m-3">
                            <div class="visual-match-badge">
                                <span class="badge bg-success bg-opacity-90 text-white px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <span class="fw-medium">Visual Match</span>
                                </span>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Status Badges -->
                        <div class="position-absolute top-0 end-0 m-3">
                            <div class="d-flex flex-column gap-2">
                                @if($totalStock <= 0)
                                    <span class="badge bg-danger rounded-pill">Out of Stock</span>
                                @elseif($totalStock <= 5)
                                    <span class="badge bg-warning text-dark rounded-pill">Low Stock</span>
                                @endif
                                
                                @if($product->created_at->diffInDays() <= 7)
                                    <span class="badge bg-info rounded-pill">New Arrival</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="product-actions position-absolute bottom-0 end-0 m-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm wishlist-btn" 
                                        data-product-id="{{ $product->id }}" 
                                        title="Add to Wishlist"
                                        style="width: 40px; height: 40px;">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm quick-view-btn" 
                                        data-product-id="{{ $product->id }}" 
                                        title="Quick View"
                                        style="width: 40px; height: 40px;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hover Overlay -->
                        <div class="product-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white"
                             style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); transform: translateY(100%); transition: transform 0.3s ease;">
                            <div class="text-center">
                                @if($totalStock > 0)
                                    <button class="btn btn-primary btn-sm px-4 rounded-pill quick-add-btn" 
                                            data-product-id="{{ $product->id }}">
                                        <i class="bi bi-cart-plus me-2"></i>Quick Add to Cart
                                    </button>
                                @else
                                    <button class="btn btn-outline-light btn-sm px-4 rounded-pill notify-btn" 
                                            data-product-id="{{ $product->id }}">
                                        <i class="bi bi-bell me-2"></i>Notify When Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="card-body p-4">
                        <!-- Brand & Category -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            @if($product->category)
                                <span class="badge bg-light text-muted text-uppercase small">{{ $product->category->name }}</span>
                            @endif
                            @if($product->brand)
                                <span class="text-primary fw-medium small">{{ $product->brand->name }}</span>
                            @endif
                        </div>
                        
                        <!-- Product Title -->
                        <h6 class="product-title mb-2 fw-semibold lh-sm">
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="text-decoration-none text-dark stretched-link">
                                {{ Str::limit($product->name, 55) }}
                            </a>
                        </h6>
                        
                        <!-- Color Variations -->
                        @if($product->variations->count() > 1)
                            @php
                                $colors = $product->variations->flatMap->attributeValues
                                    ->where('attribute.name', 'Color')
                                    ->unique('value')
                                    ->take(6);
                            @endphp
                            
                            @if($colors->count() > 0)
                                <div class="color-swatches mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">Colors:</small>
                                        <div class="d-flex gap-1">
                                            @foreach($colors as $color)
                                                <span class="color-swatch rounded-circle border" 
                                                      style="width: 20px; height: 20px; background-color: {{ strtolower($color->value) }};"
                                                      title="{{ $color->value }}"></span>
                                            @endforeach
                                            @if($colors->count() >= 6)
                                                <small class="text-muted">+{{ $product->variations->count() - 6 }} more</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        
                        <!-- Price & Rating -->
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="price-section">
                                @php
                                    $minPrice = $product->variations->min('price');
                                    $maxPrice = $product->variations->max('price');
                                @endphp
                                
                                @if($minPrice == $maxPrice)
                                    <h5 class="text-primary mb-0 fw-bold">₹{{ number_format($minPrice, 0) }}</h5>
                                @else
                                    <h6 class="text-primary mb-0 fw-bold">
                                        ₹{{ number_format($minPrice, 0) }} - ₹{{ number_format($maxPrice, 0) }}
                                    </h6>
                                @endif
                                
                                @if($totalStock > 0)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>In Stock
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>Out of Stock
                                    </small>
                                @endif
                            </div>
                            
                            <!-- Rating -->
                            <div class="rating text-end">
                                <div class="d-flex align-items-center text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= 4)
                                            <i class="bi bi-star-fill"></i>
                                        @else
                                            <i class="bi bi-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <small class="text-muted">(4.2)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Enhanced Styles for Visual Search Results -->
    <style>
    /* Product Card Hover Effects */
    .product-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px !important;
    }
    
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.08);
    }
    
    .product-card:hover .product-overlay {
        transform: translateY(0) !important;
    }
    
    /* Image Styling */
    .product-image {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    /* Visual Search Header Gradient */
    .visual-search-results-header .card {
        border-radius: 20px !important;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    /* Visual Match Badge Animation */
    .visual-match-badge {
        animation: pulse-glow 2s infinite;
    }
    
    @keyframes pulse-glow {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    /* Action Buttons */
    .product-actions .btn {
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.95) !important;
        transition: all 0.2s ease;
    }
    
    .product-actions .btn:hover {
        transform: scale(1.1);
        background-color: rgba(255, 255, 255, 1) !important;
    }
    
    /* Color Swatches */
    .color-swatch {
        border: 2px solid #fff !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .color-swatch:hover {
        transform: scale(1.2);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .product-card:hover {
            transform: none;
        }
        
        .product-overlay {
            transform: translateY(0) !important;
            background: rgba(0,0,0,0.6) !important;
        }
        
        .visual-search-results-header .row > div {
            text-align: center !important;
            margin-bottom: 1rem;
        }
    }
    
    /* Typography Enhancements */
    .product-title a {
        transition: color 0.2s ease;
    }
    
    .product-title a:hover {
        color: var(--bs-primary) !important;
    }
    
    /* Badge Styling */
    .badge {
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    
    /* Price Text */
    .price-section h5, .price-section h6 {
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    </style>

@else
    <!-- No Results Found -->
    <div class="no-results text-center py-5">
        <div class="mb-4">
            <i class="bi bi-search display-1 text-muted"></i>
        </div>
        <h4 class="mb-3">No products found</h4>
        @if($results['query_info']['query'])
            <p class="text-muted mb-4">
                Sorry, we couldn't find any products matching "<strong>{{ $results['query_info']['query'] }}</strong>".
            </p>
        @else
            <p class="text-muted mb-4">
                Try adjusting your filters or search terms.
            </p>
        @endif
        
        <!-- Search Suggestions -->
        @if(!empty($results['suggestions']))
            <div class="search-suggestions mb-4">
                <h6 class="mb-3">Did you mean?</h6>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    @foreach($results['suggestions'] as $suggestion)
                        <a href="{{ route('search', ['q' => $suggestion]) }}" 
                           class="btn btn-outline-primary btn-sm">
                            {{ $suggestion }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Popular Categories -->
        <div class="popular-categories">
            <h6 class="mb-3">Browse Popular Categories</h6>
            <div class="row g-3">
                @php
                    $popularCategories = \App\Models\Category::withCount('products')
                        ->orderByDesc('products_count')
                        ->limit(6)
                        ->get();
                @endphp
                
                @foreach($popularCategories as $category)
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('search', ['categories' => [$category->id]]) }}" 
                           class="card border text-decoration-none h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-tag-fill text-primary mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="card-title mb-1">{{ $category->name }}</h6>
                                <small class="text-muted">{{ $category->products_count }} products</small>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<style>
.product-card:hover .position-absolute.bottom-0 {
    opacity: 1 !important;
}

.product-title:hover {
    color: #0d6efd !important;
}

.color-swatch {
    transition: transform 0.2s ease;
}

.color-swatch:hover {
    transform: scale(1.2);
}

.variation-options {
    font-size: 0.875rem;
}

.stock-info {
    font-size: 0.875rem;
}

.no-results .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

@media (max-width: 768px) {
    .products-grid .col-xl-4,
    .products-grid .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* List view styles */
.list-view .products-grid {
    display: block;
}

.list-view .product-item {
    width: 100%;
    max-width: none;
    margin-bottom: 1rem;
}

.list-view .product-card {
    flex-direction: row;
}

.list-view .product-image {
    width: 200px;
    flex-shrink: 0;
}

.list-view .product-image img,
.list-view .product-image > div {
    height: 150px !important;
}

.list-view .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
</style>

<script>
// Ensure jQuery is available for search results
(function() {
    function initSearchResults() {
        if (typeof jQuery === 'undefined') {
            console.warn('jQuery not available for search results functionality');
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Add to cart functionality
            $('.add-to-cart-btn').click(function() {
                const variationId = $(this).data('variation-id');
                const button = $(this);
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_variation_id: variationId,
                qty: 1
            },
            beforeSend: function() {
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');
            },
            success: function(response) {
                if (response.success) {
                    button.html('<i class="bi bi-check me-2"></i>Added!').removeClass('btn-primary').addClass('btn-success');
                    
                    // Update cart count in header if exists
                    updateCartCount();
                    
                    setTimeout(() => {
                        button.html('<i class="bi bi-cart-plus me-2"></i>Add to Cart').removeClass('btn-success').addClass('btn-primary').prop('disabled', false);
                    }, 2000);
                } else {
                    showToast(response.message || 'Failed to add to cart', 'error');
                    button.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Add to Cart');
                }
            },
            error: function(xhr) {
                showToast('Failed to add to cart', 'error');
                button.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Add to Cart');
            }
        });
    });
    
    // Wishlist functionality
    $('.wishlist-btn').click(function() {
        const productId = $(this).data('product-id');
        const button = $(this);
        
        $.ajax({
            url: '{{ route("wishlist.toggle") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    if (response.action === 'added') {
                        button.find('i').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                        showToast('Added to wishlist', 'success');
                    } else {
                        button.find('i').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                        showToast('Removed from wishlist', 'info');
                    }
                }
            },
            error: function() {
                showToast('Failed to update wishlist', 'error');
            }
        });
    });
    
    // Quick view functionality
    $('.quick-view-btn').click(function() {
        const productId = $(this).data('product-id');
        // Implementation would open a modal with product quick view
        // For now, redirect to product page
        window.location.href = `/products/${productId}`;
    });
    
    // Notify when available
    $('.notify-when-available-btn, .notify-btn').click(function() {
        const productId = $(this).data('product-id');
        // Implementation would open a modal to collect email
        showToast('Notification feature coming soon!', 'info');
    });
    
    function updateCartCount() {
        $.get('{{ route("cart.summary") }}')
        .done(function(response) {
            if (response.success) {
                $('.cart-count').text(response.count);
            }
        });
    }
    
    function showToast(message, type = 'info') {
        // Simple toast implementation
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        $('.toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
        });
    }
    
    // Initialize when jQuery is available
    if (typeof jQuery !== 'undefined') {
        initSearchResults();
    } else {
        // Wait for jQuery to load
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                initSearchResults();
            }
        }, 50);
        
        setTimeout(function() {
            clearInterval(checkJQuery);
        }, 2000);
    }
})();
</script>
