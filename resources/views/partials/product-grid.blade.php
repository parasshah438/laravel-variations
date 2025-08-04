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
                    
                    <!-- Wishlist Button -->
                    <button class="btn btn-outline-danger wishlist-btn position-absolute top-0 end-0 m-2 rounded-circle" 
                            data-product-id="{{ $product->id }}" title="Add to Wishlist">
                        <i class="bi bi-heart"></i>
                    </button>
                    
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
                                    <span class="fw-bold">${{ number_format($product->minPrice(), 2) }} - ${{ number_format($product->maxPrice(), 2) }}</span>
                                @else
                                    <span class="fw-bold">${{ number_format($product->variations->first()->price, 2) }}</span>
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
</script>
@endpush
