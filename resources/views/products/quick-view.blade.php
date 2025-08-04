<div class="row">
    <!-- Product Images -->
    <div class="col-md-6">
        <div class="quick-view-images">
            @if($product->images->count() > 0)
                <div id="quickViewImageCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     class="d-block w-100 quick-view-modal-img" 
                                     alt="{{ $product->name }}"
                                     onerror="this.src='https://via.placeholder.com/400x400?text={{ urlencode($product->name) }}'">
                            </div>
                        @endforeach
                    </div>
                    @if($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#quickViewImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#quickViewImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
                
                @if($product->images->count() > 1)
                    <div class="row mt-2">
                        @foreach($product->images->take(4) as $index => $image)
                            <div class="col-3">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     class="img-thumbnail quick-view-thumb {{ $index === 0 ? 'active' : '' }}" 
                                     alt="{{ $product->name }}"
                                     data-bs-target="#quickViewImageCarousel" 
                                     data-bs-slide-to="{{ $index }}"
                                     style="cursor: pointer; height: 60px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <img src="https://via.placeholder.com/400x400?text={{ urlencode($product->name) }}" 
                     class="img-fluid quick-view-modal-img" 
                     alt="{{ $product->name }}">
            @endif
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-md-6">
        <div class="quick-view-details">
            <h4 class="mb-2">{{ $product->name }}</h4>
            <p class="text-muted mb-2">{{ $product->brand->name }}</p>
            
            @php $defaultVariation = $product->variations->first(); @endphp
            <div class="mb-3">
                <h5 class="text-primary mb-1" id="quickViewPrice">
                    @if($product->variations->count() > 1)
                        ₹{{ number_format($product->minPrice(), 2) }} - ₹{{ number_format($product->maxPrice(), 2) }}
                    @else
                        ₹{{ number_format($defaultVariation->price, 2) }}
                    @endif
                </h5>
                <div id="quickViewStockInfo">
                    @if($defaultVariation->stock <= 0)
                        <span class="text-danger">Out of Stock</span>
                    @elseif($defaultVariation->stock <= 5)
                        <span class="text-warning">Only {{ $defaultVariation->stock }} left in stock!</span>
                    @else
                        <span class="text-success">In Stock</span>
                    @endif
                </div>
            </div>
            
            <div class="mb-3">
                <p class="text-muted">{{ Str::limit($product->description, 150) }}</p>
            </div>
            
            <!-- Variations -->
            @if(count($availableAttributes) > 0)
                <div class="variations-section mb-4">
                    @foreach($availableAttributes as $attributeName => $values)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ $attributeName }}:</label>
                            <select class="form-select quick-view-variation-select" 
                                    data-attribute="{{ strtolower($attributeName) }}"
                                    data-product-id="{{ $product->id }}">
                                <option value="">Choose {{ $attributeName }}</option>
                                @foreach($values as $value)
                                    <option value="{{ $value['id'] }}">{{ $value['value'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Add to Cart Form -->
            <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="quick-view-add-to-cart">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $defaultVariation->id }}">
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Quantity:</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity(this)">-</button>
                            <input type="number" 
                                   name="quantity" 
                                   id="quickViewQuantity"
                                   value="1" 
                                   min="1" 
                                   max="{{ $defaultVariation->stock }}" 
                                   class="form-control text-center"
                                   {{ $defaultVariation->stock <= 0 ? 'disabled' : '' }}>
                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity(this)">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" 
                            id="quickViewAddToCartBtn"
                            class="btn btn-primary btn-lg"
                            {{ $defaultVariation->stock <= 0 ? 'disabled' : '' }}>
                        @if($defaultVariation->stock <= 0)
                            Out of Stock
                        @else
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        @endif
                    </button>
                    <a href="{{ route('products.show', $product->slug) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Full Details
                    </a>
                </div>
            </form>
            
            <!-- Additional Info -->
            <div class="mt-4">
                <div class="row text-center">
                    <div class="col-4">
                        <i class="bi bi-truck text-muted"></i>
                        <small class="d-block text-muted">Free Shipping</small>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-arrow-repeat text-muted"></i>
                        <small class="d-block text-muted">Easy Returns</small>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-shield-check text-muted"></i>
                        <small class="d-block text-muted">Secure</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-view-thumb {
    border: 2px solid transparent;
    transition: border-color 0.2s;
}

.quick-view-thumb.active,
.quick-view-thumb:hover {
    border-color: #0d6efd;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    padding: 20px;
}

.variations-section .form-select {
    max-width: 200px;
}
</style>

<script>
// Handle thumbnail clicks
$(document).ready(function() {
    $('.quick-view-thumb').click(function() {
        $('.quick-view-thumb').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
