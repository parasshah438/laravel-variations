@if($results['query_info']['has_results'])
    <div class="products-grid row g-4" id="products-grid">
        @foreach($results['products'] as $product)
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 product-item">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <div class="position-relative product-image">
                        @php
                            // Get the first image
                            $firstImage = $product->images->first();
                        @endphp
                        
                        @if($firstImage)
                            <img src="{{ asset('storage/' . $firstImage->image_path) }}" 
                                 class="card-img-top" alt="{{ $product->name }}" 
                                 style="height: 250px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 250px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        
                        <!-- Product badges -->
                        <div class="position-absolute top-0 start-0 m-2">
                            @php
                                $totalStock = $product->variations->sum('stock');
                                $minStock = $product->variations->min('stock');
                                $maxStock = $product->variations->max('stock');
                            @endphp
                            
                            @if($totalStock <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($minStock <= 5 && $minStock > 0)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @endif
                            
                            @if($product->created_at->diffInDays() <= 7)
                                <span class="badge bg-success ms-1">New</span>
                            @endif
                        </div>
                        
                        <!-- Quick actions -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <div class="btn-group-vertical">
                                <button type="button" class="btn btn-sm btn-light wishlist-btn" 
                                        data-product-id="{{ $product->id }}" 
                                        title="Add to Wishlist">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light quick-view-btn" 
                                        data-product-id="{{ $product->id }}" 
                                        title="Quick View">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hover overlay -->
                        <div class="position-absolute bottom-0 start-0 end-0 p-2 bg-gradient-to-top" 
                             style="background: linear-gradient(transparent, rgba(0,0,0,0.7)); opacity: 0; transition: opacity 0.3s ease;">
                            <div class="d-flex justify-content-center">
                                @if($totalStock > 0)
                                    <button type="button" class="btn btn-primary btn-sm quick-add-btn" 
                                            data-product-id="{{ $product->id }}">
                                        <i class="bi bi-cart-plus me-1"></i>Quick Add
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-light btn-sm notify-btn" 
                                            data-product-id="{{ $product->id }}">
                                        <i class="bi bi-bell me-1"></i>Notify Me
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Category and Brand -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            @if($product->category)
                                <small class="text-muted">{{ $product->category->name }}</small>
                            @endif
                            @if($product->brand)
                                <small class="text-primary fw-bold">{{ $product->brand->name }}</small>
                            @endif
                        </div>
                        
                        <!-- Product Title -->
                        <h6 class="card-title mb-2">
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="text-decoration-none text-dark product-title">
                                {{ Str::limit($product->name, 60) }}
                            </a>
                        </h6>
                        
                        <!-- Product Description -->
                        @if($product->description)
                            <p class="card-text text-muted small mb-2">
                                {{ Str::limit(strip_tags($product->description), 80) }}
                            </p>
                        @endif
                        
                        <!-- Variations Preview -->
                        @if($product->variations->count() > 1)
                            <div class="variations-preview mb-2">
                                <small class="text-muted d-block">Available in:</small>
                                <div class="variation-options">
                                    @php
                                        $colors = $product->variations->flatMap->attributeValues->where('attribute.name', 'Color')->take(4);
                                        $sizes = $product->variations->flatMap->attributeValues->where('attribute.name', 'Size')->take(3);
                                    @endphp
                                    
                                    @if($colors->count() > 0)
                                        <div class="color-options d-flex gap-1 mb-1">
                                            @foreach($colors as $color)
                                                <span class="color-swatch rounded-circle" 
                                                      style="width: 16px; height: 16px; background-color: {{ strtolower($color->value) }}; border: 1px solid #ddd;"
                                                      title="{{ $color->value }}"></span>
                                            @endforeach
                                            @if($colors->count() >= 4)
                                                <small class="text-muted ms-1">+more</small>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($sizes->count() > 0)
                                        <div class="size-options">
                                            <small class="text-muted">
                                                {{ $sizes->pluck('value')->implode(', ') }}
                                                @if($sizes->count() >= 3) +more @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Price -->
                        <div class="price-section mb-3">
                            @php
                                $minPrice = $product->variations->min('price');
                                $maxPrice = $product->variations->max('price');
                            @endphp
                            
                            @if($minPrice == $maxPrice)
                                <h5 class="text-primary mb-0">₹{{ number_format($minPrice, 2) }}</h5>
                            @else
                                <h5 class="text-primary mb-0">
                                    ₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}
                                </h5>
                            @endif
                            
                            <!-- Stock info -->
                            <div class="stock-info">
                                @if($totalStock > 0)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $totalStock }} in stock
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Out of stock
                                    </small>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            @if($totalStock > 0)
                                @if($product->variations->count() === 1)
                                    <button type="button" class="btn btn-primary add-to-cart-btn" 
                                            data-variation-id="{{ $product->variations->first()->id }}">
                                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                    </button>
                                @else
                                    <a href="{{ route('products.show', $product->slug) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-2"></i>View Options
                                    </a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-secondary notify-when-available-btn" 
                                        data-product-id="{{ $product->id }}">
                                    <i class="bi bi-bell me-2"></i>Notify When Available
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
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
</script>
