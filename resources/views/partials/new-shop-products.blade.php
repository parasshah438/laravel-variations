@foreach ($products as $product)
    @php
        $priceInfo = Helper::calculatePriceInfo($product->id);
        $finalPrice = $priceInfo['final_price'];
        $originalPrice = $priceInfo['original_price'];
        $discountPercentage = $priceInfo['discount_percentage'];
        $mainImage = $product->gallery->first() ? asset($product->gallery->first()->image_path) : asset('images/no-image.png');
    @endphp
    
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="product-card h-100">
            <div class="product-image-wrapper position-relative">
                <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="product-image img-fluid w-100">
                
                @if($discountPercentage > 0)
                    <span class="discount-badge position-absolute">-{{ $discountPercentage }}%</span>
                @endif
                
                <div class="product-overlay position-absolute w-100 h-100 d-flex align-items-center justify-content-center">
                    <div class="product-actions">
                        <button class="btn btn-light btn-sm me-2 quick-view-btn" data-product-id="{{ $product->id }}">
                            <i class="fas fa-eye"></i> Quick View
                        </button>
                        <button class="btn btn-light btn-sm wishlist-btn" data-product-id="{{ $product->id }}">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="product-info p-3">
                <h5 class="product-title mb-2">
                    <a href="{{ route('product.details', $product->slug) }}" class="text-decoration-none text-dark">
                        {{ Str::limit($product->name, 50) }}
                    </a>
                </h5>
                
                <div class="product-meta mb-2">
                    @if($product->category)
                        <span class="category-badge badge bg-secondary me-2">{{ $product->category->name }}</span>
                    @endif
                    @if($product->brand)
                        <span class="brand-badge badge bg-primary">{{ $product->brand->name }}</span>
                    @endif
                </div>
                
                <div class="product-price mb-3">
                    <span class="current-price fw-bold fs-5 text-primary">₹{{ number_format($finalPrice, 2) }}</span>
                    @if($originalPrice > $finalPrice)
                        <span class="original-price text-muted text-decoration-line-through ms-2">₹{{ number_format($originalPrice, 2) }}</span>
                    @endif
                </div>
                
                @if($product->average_rating > 0)
                    <div class="product-rating mb-2">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $product->average_rating)
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-muted"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="rating-count text-muted ms-2">({{ $product->total_reviews }} reviews)</span>
                    </div>
                @endif
                
                <div class="product-actions-bottom d-flex flex-column gap-2">
                    <!-- Quantity Controls -->
                    <div class="input-group input-group-sm quantity-group" style="max-width: 140px; margin: 0 auto;">
                        <button class="btn btn-outline-secondary btn-qty-decrease" type="button" data-product-id="{{ $product->id }}">
                            <span class="qty-icon">🗑️</span>
                        </button>
                        <input type="number" 
                               class="form-control text-center product-qty-input" 
                               value="1" 
                               data-initial="1"
                               data-product-id="{{ $product->id }}"
                               data-max="99"
                               min="1" 
                               max="99"
                               readonly>
                        <button class="btn btn-outline-secondary btn-qty-increase" type="button" data-product-id="{{ $product->id }}">
                            <span class="qty-icon">+</span>
                        </button>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="flex-fill add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1" class="quantity-input">
                            <button type="submit" class="btn btn-primary w-100 add-to-cart-btn">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </form>
                        <button class="btn btn-outline-secondary buy-now-btn" data-product-id="{{ $product->id }}">
                            <i class="fas fa-bolt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
.product-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-image-wrapper {
    overflow: hidden;
    height: 250px;
}

.product-image {
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.discount-badge {
    top: 10px;
    right: 10px;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: bold;
}

.product-overlay {
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.7);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-actions .btn {
    border-radius: 20px;
    transition: all 0.3s ease;
}

.product-title a:hover {
    color: #007bff !important;
}

.category-badge {
    font-size: 0.7rem;
}

.brand-badge {
    font-size: 0.7rem;
}

.current-price {
    color: #28a745 !important;
}

.stars {
    color: #ffc107;
}

.product-actions-bottom .btn {
    font-size: 0.875rem;
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
}

.buy-now-btn:hover {
    background-color: #6c757d;
    color: white;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .product-actions-bottom {
        flex-direction: column;
    }
    
    .product-actions-bottom .btn {
        font-size: 0.8rem;
        padding: 6px 12px;
    }
}
</style>
