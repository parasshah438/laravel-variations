@extends('layouts.app')

@section('title', $product->name . ' - Product Details')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}" class="text-decoration-none">Shop</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category->slug) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 col-md-6">
            <div class="product-gallery">
                <!-- Main Image Display -->
                <div class="main-image-container mb-3">
                    <div id="main-image-wrapper" class="position-relative">
                        @if($product->images->count() > 0)
                            <img id="main-product-image" 
                                 src="{{ $product->images->first()->image_path }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded shadow-sm main-product-image">
                        @else
                            <div class="no-image-placeholder bg-light d-flex align-items-center justify-content-center rounded" 
                                 style="height: 500px;">
                                <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                        
                        <!-- Image Zoom Icon -->
                        <button class="btn btn-light btn-sm position-absolute top-0 end-0 m-3" 
                                id="zoom-btn" title="Zoom Image">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                        
                        <!-- Navigation Arrows -->
                        @if($product->images->count() > 1)
                            <button class="btn btn-light btn-sm position-absolute top-50 start-0 translate-middle-y ms-2" 
                                    id="prev-image" style="display: none;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="btn btn-light btn-sm position-absolute top-50 end-0 translate-middle-y me-2" 
                                    id="next-image">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Thumbnail Images -->
                @if($product->images->count() > 1)
                <div class="thumbnail-container">
                    <div class="row g-2" id="image-thumbnails">
                        @foreach($product->images as $index => $image)
                            <div class="col-3">
                                <img src="{{ $image->image_path }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded thumbnail-image {{ $index === 0 ? 'active' : '' }}" 
                                     data-index="{{ $index }}"
                                     data-full-url="{{ asset('storage/' . $image->image_path) }}"
                                     style="cursor: pointer; height: 80px; object-fit: cover; width: 100%;">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6 col-md-6">
            <div class="product-details">
                <!-- Product Title & Brand -->
                <div class="product-header mb-3">
                    @if($product->brand)
                        <p class="text-muted mb-1">{{ $product->brand->name }}</p>
                    @endif
                    <h1 class="h3 mb-2">{{ $product->name }}</h1>
                    @if($product->category)
                        <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                    @endif
                </div>

                <!-- Price Display -->
                <div class="price-section mb-4">
                    @php
                        $minPrice = $product->variations->min('price');
                        $maxPrice = $product->variations->max('price');
                    @endphp
                    
                    <div id="price-display">
                        @if($minPrice == $maxPrice)
                            <h2 class="text-primary mb-1" id="current-price">₹{{ number_format($minPrice, 2) }}</h2>
                        @else
                            <h2 class="text-primary mb-1" id="current-price">₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}</h2>
                        @endif
                    </div>
                    
                    <!-- Stock Info -->
                    <div id="stock-info">
                        @if($product->variations->sum('stock') > 0)
                            <p class="text-success mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                <span id="stock-count">{{ $product->variations->sum('stock') }}</span> in stock
                            </p>
                        @else
                            <p class="text-danger mb-0">
                                <i class="bi bi-x-circle me-1"></i>Out of Stock
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Product Variations -->
                <div class="variations-section mb-4">
                    @if(count($availableAttributes) > 0)
                        @foreach($availableAttributes as $attributeName => $attributeValues)
                            <div class="variation-group mb-3">
                                <label class="form-label fw-bold">{{ $attributeName }}:</label>
                                <div class="variation-options d-flex flex-wrap gap-2">
                                    @foreach($attributeValues as $attributeValue)
                                        <button type="button" 
                                                class="btn btn-outline-secondary variation-btn" 
                                                data-attribute="{{ strtolower($attributeName) }}"
                                                data-attribute-id="{{ $attributeValue['id'] }}"
                                                data-value="{{ $attributeValue['value'] }}">
                                            {{ $attributeValue['value'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Quantity Selector -->
                <div class="quantity-section mb-4">
                    <label class="form-label fw-bold">Quantity:</label>
                    <div class="input-group" style="width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" id="qty-decrease">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="10">
                        <button class="btn btn-outline-secondary" type="button" id="qty-increase">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mb-4">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-primary btn-lg flex-fill" id="add-to-cart-btn" disabled>
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn" 
                                data-product-id="{{ $product->id }}">
                            <i class="bi bi-heart me-2"></i>Wishlist
                        </button>
                    </div>
                    
                    <!-- Customization Button -->
                    <button type="button" class="btn btn-outline-info btn-lg w-100 mt-2" id="customize-btn">
                        <i class="bi bi-palette me-2"></i>Customize This Product
                    </button>
                    
                    <!-- Buy Now Button -->
                    <button type="button" class="btn btn-success btn-lg w-100 mt-2" id="buy-now-btn" disabled>
                        <i class="bi bi-lightning me-2"></i>Buy Now
                    </button>
                </div>

                <!-- Product Features -->
                <div class="product-features mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="feature-item d-flex align-items-center">
                                <i class="bi bi-truck text-success me-2"></i>
                                <small class="text-muted">Free Delivery</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-item d-flex align-items-center">
                                <i class="bi bi-arrow-return-left text-info me-2"></i>
                                <small class="text-muted">Easy Returns</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-item d-flex align-items-center">
                                <i class="bi bi-shield-check text-warning me-2"></i>
                                <small class="text-muted">Warranty</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-item d-flex align-items-center">
                                <i class="bi bi-credit-card text-primary me-2"></i>
                                <small class="text-muted">Secure Payment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Product Description & Details -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="product-tabs">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                data-bs-target="#description" type="button" role="tab">
                            Description
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                                data-bs-target="#specifications" type="button" role="tab">
                            Specifications
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                data-bs-target="#reviews" type="button" role="tab">
                            Reviews
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content border-start border-end border-bottom p-4" id="productTabsContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        @if($product->description)
                            <div class="description-content">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        @else
                            <p class="text-muted">No description available for this product.</p>
                        @endif
                    </div>
                    
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="specifications-content">
                            <table class="table table-striped">
                                <tbody>
                                    @if($product->brand)
                                        <tr>
                                            <td><strong>Brand</strong></td>
                                            <td>{{ $product->brand->name }}</td>
                                        </tr>
                                    @endif
                                    @if($product->category)
                                        <tr>
                                            <td><strong>Category</strong></td>
                                            <td>{{ $product->category->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Available Variations</strong></td>
                                        <td>{{ $product->variations->count() }} variations</td>
                                    </tr>
                                    @foreach($availableAttributes as $attributeName => $attributeValues)
                                        <tr>
                                            <td><strong>{{ $attributeName }}</strong></td>
                                            <td>{{ collect($attributeValues)->pluck('value')->join(', ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="reviews-content">
                            <p class="text-muted">Reviews feature coming soon...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4">Related Products</h4>
            <div class="row g-4">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="position-relative">
                                @if($relatedProduct->images->count() > 0)
                                    <img src="{{ $relatedProduct->images->first()->image_path }}" 
                                         class="card-img-top" alt="{{ $relatedProduct->name }}" 
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('products.show', $relatedProduct->slug) }}" 
                                       class="text-decoration-none text-dark">
                                        {{ Str::limit($relatedProduct->name, 50) }}
                                    </a>
                                </h6>
                                
                                @php
                                    $minPrice = $relatedProduct->variations->min('price');
                                    $maxPrice = $relatedProduct->variations->max('price');
                                @endphp
                                
                                @if($minPrice == $maxPrice)
                                    <p class="text-primary mb-2">₹{{ number_format($minPrice, 2) }}</p>
                                @else
                                    <p class="text-primary mb-2">₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}</p>
                                @endif
                                
                                <a href="{{ route('products.show', $relatedProduct->slug) }}" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageZoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $product->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="zoom-modal-image" src="" alt="{{ $product->name }}" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Product Customization Modal -->
<div class="modal fade" id="customizationModal" tabindex="-1" aria-labelledby="customizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="customizationModalLabel">
                    <i class="bi bi-palette me-2"></i>Customize Your Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Customization Tools Sidebar -->
                    <div class="col-md-3 bg-light border-end">
                        <div class="p-3">
                            <div class="customization-tabs">
                                <ul class="nav nav-pills flex-column" id="customTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active w-100 mb-2" id="text-tab" data-bs-toggle="pill" 
                                                data-bs-target="#text-panel" type="button" role="tab">
                                            <i class="bi bi-fonts me-2"></i>Add Text
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link w-100 mb-2" id="image-tab" data-bs-toggle="pill" 
                                                data-bs-target="#image-panel" type="button" role="tab">
                                            <i class="bi bi-image me-2"></i>Add Image
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link w-100 mb-2" id="settings-tab" data-bs-toggle="pill" 
                                                data-bs-target="#settings-panel" type="button" role="tab">
                                            <i class="bi bi-gear me-2"></i>Settings
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tab-content mt-3" id="customTabsContent">
                                <!-- Text Panel -->
                                <div class="tab-pane fade show active" id="text-panel" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Text Content</label>
                                        <textarea class="form-control" id="text-content" rows="3" placeholder="Enter your text here..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Font Family</label>
                                        <select class="form-select" id="font-family">
                                            <option value="Arial">Arial</option>
                                            <option value="Times New Roman">Times New Roman</option>
                                            <option value="Helvetica">Helvetica</option>
                                            <option value="Comic Sans MS">Comic Sans MS</option>
                                            <option value="Impact">Impact</option>
                                            <option value="Verdana">Verdana</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label">Font Size</label>
                                            <input type="range" class="form-range" id="font-size" min="10" max="100" value="24">
                                            <small class="text-muted" id="font-size-value">24px</small>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Text Color</label>
                                            <input type="color" class="form-control form-control-color" id="text-color" value="#000000">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="text-bold">
                                            <label class="form-check-label" for="text-bold">Bold</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="text-italic">
                                            <label class="form-check-label" for="text-italic">Italic</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="text-underline">
                                            <label class="form-check-label" for="text-underline">Underline</label>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3" id="add-text-btn">
                                        <i class="bi bi-plus-circle me-2"></i>Add Text
                                    </button>
                                </div>
                                
                                <!-- Image Panel -->
                                <div class="tab-pane fade" id="image-panel" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Image</label>
                                        <input type="file" class="form-control" id="image-upload" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image Size</label>
                                        <input type="range" class="form-range" id="image-size" min="50" max="500" value="200">
                                        <small class="text-muted" id="image-size-value">200px</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image Opacity</label>
                                        <input type="range" class="form-range" id="image-opacity" min="0" max="1" step="0.1" value="1">
                                        <small class="text-muted" id="image-opacity-value">100%</small>
                                    </div>
                                    <div id="uploaded-image-preview" class="text-center" style="display: none;">
                                        <img id="preview-image" src="" alt="Preview" class="img-thumbnail mb-2" style="max-width: 150px;">
                                        <br>
                                        <button class="btn btn-success w-100" id="add-image-btn">
                                            <i class="bi bi-plus-circle me-2"></i>Add to Canvas
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Settings Panel -->
                                <div class="tab-pane fade" id="settings-panel" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Canvas Background</label>
                                        <input type="color" class="form-control form-control-color" id="canvas-bg" value="#ffffff">
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-warning w-100" id="clear-canvas-btn">
                                            <i class="bi bi-trash me-2"></i>Clear All
                                        </button>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-info w-100" id="save-design-btn">
                                            <i class="bi bi-download me-2"></i>Save Design
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Canvas Area -->
                    <div class="col-md-9">
                        <div class="canvas-container p-3 d-flex align-items-center justify-content-center" style="min-height: 600px;">
                            <div class="canvas-wrapper position-relative">
                                <canvas id="customization-canvas" width="600" height="600" style="border: 2px solid #ddd; background: white;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="apply-customization-btn">
                    <i class="bi bi-check-circle me-2"></i>Apply Customization
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.main-product-image {
    max-height: 500px;
    width: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.thumbnail-image {
    border: 2px solid transparent;
    transition: all 0.2s ease;
}

.thumbnail-image:hover,
.thumbnail-image.active {
    border-color: #007bff;
}

.variation-btn {
    min-width: 60px;
    transition: all 0.2s ease;
}

.variation-btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.variation-btn:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
}

.product-card {
    transition: transform 0.2s ease;
}

.product-card:hover {
    transform: translateY(-5px);
}

.feature-item {
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
}

.nav-tabs .nav-link {
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #007bff;
    background-color: transparent;
}

#main-image-wrapper:hover .main-product-image {
    transform: scale(1.02);
}

@media (max-width: 768px) {
    .variation-options {
        justify-content: flex-start;
    }
    
    .action-buttons .d-md-flex {
        flex-direction: column;
    }
    
    .action-buttons .flex-fill {
        margin-bottom: 10px;
    }
}

/* Customization Modal Styles */
.customization-tabs .nav-link {
    text-align: left;
    border-radius: 8px;
}

.customization-tabs .nav-link.active {
    background-color: #007bff;
    border-color: #007bff;
}

.canvas-container {
    background: #f8f9fa;
    border-radius: 8px;
}

.canvas-wrapper {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

#customization-canvas {
    border-radius: 8px;
}

.form-range::-webkit-slider-thumb {
    background: #007bff;
}

.form-range::-moz-range-thumb {
    background: #007bff;
}

.modal-fullscreen .modal-body {
    height: calc(100vh - 120px);
    overflow: hidden;
}

.tab-content {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Custom scrollbar for tab content */
.tab-content::-webkit-scrollbar {
    width: 6px;
}

.tab-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.tab-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.tab-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endpush

@push('scripts')
<!-- Fabric.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
$(document).ready(function() {
    let selectedVariations = {};
    let currentImageIndex = 0;
    let availableImages = [];
    let selectedVariationData = null;
    let customizationCanvas = null;
    let originalProductImage = null;
    
    // Initialize images array
    @if($product->images->count() > 0)
        availableImages = [
            @foreach($product->images as $image)
                {
                    url: "{{ asset('storage/' . $image->image_path) }}",
                    id: {{ $image->id }}
                },
            @endforeach
        ];
    @endif
    
    // Thumbnail click handler
    $('.thumbnail-image').click(function() {
        const index = $(this).data('index');
        const fullUrl = $(this).data('full-url');
        
        currentImageIndex = index;
        $('#main-product-image').attr('src', fullUrl);
        
        $('.thumbnail-image').removeClass('active');
        $(this).addClass('active');
        
        updateNavigationButtons();
    });
    
    // Image navigation
    $('#prev-image').click(function() {
        if (currentImageIndex > 0) {
            currentImageIndex--;
            updateMainImage();
        }
    });
    
    $('#next-image').click(function() {
        if (currentImageIndex < availableImages.length - 1) {
            currentImageIndex++;
            updateMainImage();
        }
    });
    
    // Zoom functionality
    $('#zoom-btn').click(function() {
        const currentImageSrc = $('#main-product-image').attr('src');
        $('#zoom-modal-image').attr('src', currentImageSrc);
        $('#imageZoomModal').modal('show');
    });
    
    // Variation selection
    $('.variation-btn').click(function() {
        const attribute = $(this).data('attribute');
        const attributeId = $(this).data('attribute-id');
        const value = $(this).data('value');
        
        // Toggle selection
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete selectedVariations[attribute];
        } else {
            // Remove active class from siblings
            $(`.variation-btn[data-attribute="${attribute}"]`).removeClass('active');
            $(this).addClass('active');
            selectedVariations[attribute] = {
                id: attributeId,
                value: value
            };
        }
        
        updateProductDisplay();
    });
    
    // Quantity controls
    $('#qty-decrease').click(function() {
        const currentQty = parseInt($('#quantity').val());
        if (currentQty > 1) {
            $('#quantity').val(currentQty - 1);
        }
    });
    
    $('#qty-increase').click(function() {
        const currentQty = parseInt($('#quantity').val());
        const maxStock = selectedVariationData ? selectedVariationData.stock : 10;
        if (currentQty < maxStock) {
            $('#quantity').val(currentQty + 1);
        }
    });
    
    // Add to cart

    $('#add-to-cart-btn').click(function() {
        let variationId = null;
        
        // For single variation products
        @if($product->variations->count() === 1)
            variationId = {{ $product->variations->first()->id }};
        @else
            // For multi-variation products
            if (!selectedVariationData) {
                showToast('Please select all required options', 'warning');
                return;
            }
            variationId = selectedVariationData.id;
        @endif
        
        const quantity = parseInt($('#quantity').val());
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_variation_id: variationId,
                qty: quantity
            },
            beforeSend: function() {
                $('#add-to-cart-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');
            },
            success: function(response) {
                showToast(response.message || 'Product added to cart!', 'success');
                updateCartCount();
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to add product to cart', 'danger');
            },
            complete: function() {
                $('#add-to-cart-btn').prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Add to Cart');
            }
        });
    });
    
    // Buy now
    $('#buy-now-btn').click(function() {
        let variationId = null;
        
        // For single variation products
        @if($product->variations->count() === 1)
            variationId = {{ $product->variations->first()->id }};
        @else
            // For multi-variation products
            if (!selectedVariationData) {
                showToast('Please select all required options', 'warning');
                return;
            }
            variationId = selectedVariationData.id;
        @endif
        
        const quantity = parseInt($('#quantity').val());
        
        // Add to cart first, then redirect to checkout
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_variation_id: variationId,
                qty: quantity
            },
            beforeSend: function() {
                $('#buy-now-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
            },
            success: function(response) {
                window.location.href = '{{ route("cart.index") }}';
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || 'Failed to process request', 'danger');
                $('#buy-now-btn').prop('disabled', false).html('<i class="bi bi-lightning me-2"></i>Buy Now');
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
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.added) {
                    button.removeClass('btn-outline-danger').addClass('btn-danger');
                    button.html('<i class="bi bi-heart-fill me-2"></i>Wishlist');
                    showToast('Added to wishlist', 'success');
                } else {
                    button.removeClass('btn-danger').addClass('btn-outline-danger');
                    button.html('<i class="bi bi-heart me-2"></i>Wishlist');
                    showToast('Removed from wishlist', 'info');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = '{{ route("login") }}';
                } else {
                    showToast('Failed to update wishlist', 'danger');
                }
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
    
    // Helper functions
    function updateCartCount() {
        $.get('{{ route("cart.summary") }}')
        .done(function(response) {
            if ($('.cart-count').length) {
                $('.cart-count').text(response.count || 0);
            }
        });
    }
    
    function showToast(message, type) {
        // Create and show Bootstrap toast
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : (type === 'info' ? 'info' : 'danger'))} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        $('.toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        
        // Remove toast after it's hidden
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    function updateMainImage() {
        if (availableImages[currentImageIndex]) {
            $('#main-product-image').attr('src', availableImages[currentImageIndex].url);
            $('.thumbnail-image').removeClass('active');
            $(`.thumbnail-image[data-index="${currentImageIndex}"]`).addClass('active');
        }
        updateNavigationButtons();
    }
    
    function updateNavigationButtons() {
        $('#prev-image').toggle(currentImageIndex > 0);
        $('#next-image').toggle(currentImageIndex < availableImages.length - 1);
    }
    
    function updateProductDisplay() {
        // Count total number of attribute types
        const totalAttributeTypes = {{ count($availableAttributes) }};
        
        // If we have selected all required attributes, fetch variation data
        if (Object.keys(selectedVariations).length === totalAttributeTypes && totalAttributeTypes > 0) {
            // Convert selected variations to array of attribute value IDs
            const selectedAttributeIds = [];
            Object.keys(selectedVariations).forEach(key => {
                selectedAttributeIds.push(selectedVariations[key].id);
            });
            
            // Make AJAX call to get variation-specific data
            $.ajax({
                url: '{{ route("products.variations", $product->id) }}',
                type: 'GET',
                data: {
                    attributes: selectedAttributeIds
                },
                success: function(response) {
                    if (response.variations && response.variations.length > 0) {
                        selectedVariationData = response.variations[0];
                        
                        // Update price
                        if (response.price_range.min === response.price_range.max) {
                            $('#current-price').text('₹' + parseFloat(response.price_range.min).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        } else {
                            $('#current-price').text('₹' + parseFloat(response.price_range.min).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' - ₹' + parseFloat(response.price_range.max).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        }
                        
                        // Update stock
                        const stockCount = response.stock;
                        $('#stock-count').text(stockCount);
                        
                        if (stockCount > 0) {
                            $('#stock-info').html('<p class="text-success mb-0"><i class="bi bi-check-circle me-1"></i><span id="stock-count">' + stockCount + '</span> in stock</p>');
                            $('#add-to-cart-btn, #buy-now-btn').prop('disabled', false);
                            $('#quantity').attr('max', stockCount);
                        } else {
                            $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Out of Stock</p>');
                            $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
                        }
                        
                        // Update images if available
                        if (response.images && response.images.length > 0) {
                            availableImages = response.images;
                            currentImageIndex = 0;
                            
                            // Update main image
                            $('#main-product-image').attr('src', availableImages[0].url);
                            
                            // Update thumbnails
                            let thumbnailHtml = '';
                            response.images.forEach((image, index) => {
                                thumbnailHtml += `
                                    <div class="col-3">
                                        <img src="${image.url}" 
                                             alt="{{ $product->name }}" 
                                             class="img-fluid rounded thumbnail-image ${index === 0 ? 'active' : ''}" 
                                             data-index="${index}"
                                             data-full-url="${image.url}"
                                             style="cursor: pointer; height: 80px; object-fit: cover; width: 100%;">
                                    </div>
                                `;
                            });
                            $('#image-thumbnails').html(thumbnailHtml);
                            
                            // Re-bind thumbnail click events
                            $('.thumbnail-image').click(function() {
                                const index = $(this).data('index');
                                const fullUrl = $(this).data('full-url');
                                
                                currentImageIndex = index;
                                $('#main-product-image').attr('src', fullUrl);
                                
                                $('.thumbnail-image').removeClass('active');
                                $(this).addClass('active');
                                
                                updateNavigationButtons();
                            });
                        }
                        
                        updateNavigationButtons();
                    } else {
                        selectedVariationData = null;
                        $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
                        $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Please select all required options</p>');
                    }
                },
                error: function(xhr, status, error) {
                    selectedVariationData = null;
                    $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
                    $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Error loading variation data</p>');
                }
            });
        } else {
            // Reset to default display
            selectedVariationData = null;
            $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
        }
    }
    
    // Variation change handlers - remove unused handlers
    // $('input[name="size"], input[name="color"], input[name="fabric"]').change(updateProductDisplay);

    // Initialize for single variation products
    @if($product->variations->count() === 1)
        // For single variation products, enable add to cart if in stock
        @php
            $singleVariation = $product->variations->first();
        @endphp
        @if($singleVariation->stock > 0)
            $('#add-to-cart-btn, #buy-now-btn').prop('disabled', false);
            selectedVariationData = {
                id: {{ $singleVariation->id }},
                stock: {{ $singleVariation->stock }},
                price: {{ $singleVariation->price }}
            };
            $('#quantity').attr('max', {{ $singleVariation->stock }});
            
            // Update price display for single variation
            $('#current-price').text('₹' + parseFloat({{ $singleVariation->price }}).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
        @else
            $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Out of Stock</p>');
        @endif
    @elseif($product->variations->count() === 0)
        // No variations available - this shouldn't happen in a proper e-commerce setup
        $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
        $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>No variations available</p>');
    @else
        // Multiple variations - buttons stay disabled until user selects options
        $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
        
        // Enable button temporarily if no attributes (should have at least one variation)
        @if(count($availableAttributes) === 0 && $product->variations->count() > 0)
            @php
                $firstVariation = $product->variations->first();
            @endphp
            @if($firstVariation->stock > 0)
                $('#add-to-cart-btn, #buy-now-btn').prop('disabled', false);
                selectedVariationData = {
                    id: {{ $firstVariation->id }},
                    stock: {{ $firstVariation->stock }},
                    price: {{ $firstVariation->price }}
                };
                $('#quantity').attr('max', {{ $firstVariation->stock }});
            @endif
        @endif
    @endif

    
    // ============ PRODUCT CUSTOMIZATION FUNCTIONALITY ============
    
    // Initialize Fabric.js canvas when customization modal is opened
    $('#customize-btn').click(function() {
        $('#customizationModal').modal('show');
    });

    $('#customizationModal').on('shown.bs.modal', function() {
        if (!customizationCanvas) {
            initializeCustomizationCanvas();
        }
    });

    function initializeCustomizationCanvas() {
        customizationCanvas = new fabric.Canvas('customization-canvas');
        
        // Load the current product image as background
        let currentImage = $('#main-product-image').attr('src');
        if (currentImage) {
            fabric.Image.fromURL(currentImage, function(img) {
                // Scale image to fit canvas
                img.scaleToWidth(500);
                img.scaleToHeight(500);
                img.set({
                    left: 50,
                    top: 50,
                    selectable: false, // Background image shouldn't be selectable
                    evented: false
                });
                customizationCanvas.add(img);
                customizationCanvas.sendToBack(img);
                originalProductImage = img;
            }, { crossOrigin: 'anonymous' });
        }
        
        // Canvas event handlers
        customizationCanvas.on('selection:created', function(e) {
            // Handle object selection
        });
        
        customizationCanvas.on('selection:cleared', function(e) {
            // Handle selection clearing
        });
    }

    // Add text functionality
    $('#add-text-btn').click(function() {
        const textContent = $('#text-content').val().trim();
        if (!textContent) {
            showToast('Please enter some text', 'warning');
            return;
        }

        const fontSize = parseInt($('#font-size').val());
        const fontFamily = $('#font-family').val();
        const textColor = $('#text-color').val();
        const isBold = $('#text-bold').is(':checked');
        const isItalic = $('#text-italic').is(':checked');
        const isUnderline = $('#text-underline').is(':checked');

        let fontWeight = isBold ? 'bold' : 'normal';
        let fontStyle = isItalic ? 'italic' : 'normal';

        const text = new fabric.Text(textContent, {
            left: 100,
            top: 100,
            fontFamily: fontFamily,
            fontSize: fontSize,
            fill: textColor,
            fontWeight: fontWeight,
            fontStyle: fontStyle,
            underline: isUnderline
        });

        customizationCanvas.add(text);
        customizationCanvas.setActiveObject(text);
        showToast('Text added successfully', 'success');
    });

    // Font size slider update
    $('#font-size').on('input', function() {
        $('#font-size-value').text($(this).val() + 'px');
    });

    // Image upload functionality
    $('#image-upload').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#preview-image').attr('src', event.target.result);
                $('#uploaded-image-preview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Add image to canvas
    $('#add-image-btn').click(function() {
        const imageSrc = $('#preview-image').attr('src');
        const imageSize = parseInt($('#image-size').val());
        const imageOpacity = parseFloat($('#image-opacity').val());

        fabric.Image.fromURL(imageSrc, function(img) {
            img.set({
                left: 150,
                top: 150,
                scaleX: imageSize / img.width,
                scaleY: imageSize / img.height,
                opacity: imageOpacity
            });
            customizationCanvas.add(img);
            customizationCanvas.setActiveObject(img);
        }, { crossOrigin: 'anonymous' });
        
        showToast('Image added successfully', 'success');
    });

    // Image size slider update
    $('#image-size').on('input', function() {
        $('#image-size-value').text($(this).val() + 'px');
    });

    // Image opacity slider update
    $('#image-opacity').on('input', function() {
        $('#image-opacity-value').text(Math.round($(this).val() * 100) + '%');
    });

    // Canvas background color change
    $('#canvas-bg').change(function() {
        customizationCanvas.setBackgroundColor($(this).val(), customizationCanvas.renderAll.bind(customizationCanvas));
    });

    // Clear all objects (except background image)
    $('#clear-canvas-btn').click(function() {
        if (confirm('Are you sure you want to clear all customizations?')) {
            const objects = customizationCanvas.getObjects().slice();
            objects.forEach(function(obj) {
                if (obj !== originalProductImage) {
                    customizationCanvas.remove(obj);
                }
            });
            showToast('Canvas cleared', 'info');
        }
    });

    // Save design
    $('#save-design-btn').click(function() {
        try {
            const dataURL = customizationCanvas.toDataURL({
                format: 'png',
                quality: 1
            });
            
            // Create download link
            const link = document.createElement('a');
            link.download = 'customized-product.png';
            link.href = dataURL;
            link.click();
            
            showToast('Design saved successfully', 'success');
        } catch (error) {
            if (error.name === 'SecurityError') {
                showToast('Unable to save design due to security restrictions. Try using only uploaded images.', 'warning');
            } else {
                showToast('An error occurred while saving: ' + error.message, 'danger');
            }
            console.error('Canvas save error:', error);
        }
    });

    // Apply customization
    $('#apply-customization-btn').click(function() {
        if (customizationCanvas) {
            try {
                const customizedImageURL = customizationCanvas.toDataURL({
                    format: 'png',
                    quality: 1
                });
                
                // Update the main product image with customized version
                $('#main-product-image').attr('src', customizedImageURL);
                
                // Close modal
                $('#customizationModal').modal('hide');
                
                showToast('Customization applied successfully! Your custom design is now displayed.', 'success');
            } catch (error) {
                if (error.name === 'SecurityError') {
                    // Alternative approach: create a canvas with just the customizations (no background image)
                    try {
                        // Create a temporary canvas without the background image
                        const tempCanvas = new fabric.Canvas(document.createElement('canvas'));
                        tempCanvas.setWidth(600);
                        tempCanvas.setHeight(600);
                        
                        // Add all objects except the background image
                        const objects = customizationCanvas.getObjects().slice();
                        objects.forEach(function(obj) {
                            if (obj !== originalProductImage) {
                                const clonedObj = fabric.util.object.clone(obj);
                                tempCanvas.add(clonedObj);
                            }
                        });
                        
                        const customizationOnly = tempCanvas.toDataURL({
                            format: 'png',
                            quality: 1
                        });
                        
                        showToast('Customization applied! Note: Only custom elements were exported due to image restrictions.', 'info');
                        $('#customizationModal').modal('hide');
                        
                        // You could optionally update the main image with just the customizations
                        // $('#main-product-image').attr('src', customizationOnly);
                    } catch (altError) {
                        showToast('Unable to export customized image due to security restrictions. The customizations are visible in the editor.', 'warning');
                        $('#customizationModal').modal('hide');
                    }
                } else {
                    showToast('An error occurred while applying customization: ' + error.message, 'danger');
                }
                console.error('Canvas export error:', error);
            }
        }
    });    // Handle keyboard shortcuts in customization mode
    $(document).keydown(function(e) {
        if ($('#customizationModal').hasClass('show')) {
            // Delete key to remove selected object
            if (e.key === 'Delete' && customizationCanvas) {
                const activeObject = customizationCanvas.getActiveObject();
                const activeGroup = customizationCanvas.getActiveObjects();
                
                if (activeObject) {
                    if (activeObject !== originalProductImage) {
                        customizationCanvas.remove(activeObject);
                        showToast('Object deleted', 'info');
                    }
                } else if (activeGroup.length > 0) {
                    activeGroup.forEach(function(obj) {
                        if (obj !== originalProductImage) {
                            customizationCanvas.remove(obj);
                        }
                    });
                    customizationCanvas.discardActiveObject();
                    showToast('Objects deleted', 'info');
                }
            }
            
            // Ctrl+Z for undo (basic implementation)
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                // Basic undo functionality could be implemented here
            }
        }
    });
});
</script>
@endpush
