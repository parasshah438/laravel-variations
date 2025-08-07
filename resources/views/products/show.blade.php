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
                        @php
                            // Get only general images (not linked to any variation)
                            $variationImageIds = $product->variations->flatMap->variationImages->pluck('product_image_id');
                            $generalImages = $product->images->whereNotIn('id', $variationImageIds);
                        @endphp
                        
                        @if($generalImages->count() > 0)
                            <img id="main-product-image" 
                                 src="{{ asset('storage/' . $generalImages->first()->image_path) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded shadow-sm main-product-image"
                                 data-zoom-src="{{ asset('storage/' . $generalImages->first()->image_path) }}">
                            
                            <!-- Magnification Lens -->
                            <div id="zoom-lens" style="display: none; position: absolute; border: 2px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.5); pointer-events: none; background: rgba(255,255,255,0.1); backdrop-filter: blur(1px);"></div>
                            
                            <!-- Zoom Result Container -->
                            <div id="zoom-result" style="display: none; position: absolute; top: 0; right: -320px; width: 300px; height: 300px; border: 1px solid #ddd; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 1000;">
                                <img id="zoom-result-img" src="" alt="Zoomed view" style="position: absolute;">
                            </div>
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
                        
                        <!-- Additional Gallery Controls -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <div class="btn-group-vertical">
                                <button class="btn btn-light btn-sm mb-1" id="slideshow-btn" title="Start Slideshow">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                                <button class="btn btn-light btn-sm mb-1" id="fullscreen-btn" title="Fullscreen">
                                    <i class="bi bi-fullscreen"></i>
                                </button>
                                <button class="btn btn-light btn-sm mb-1" id="share-image-btn" title="Share Image">
                                    <i class="bi bi-share"></i>
                                </button>
                                @if($generalImages->count() > 1)
                                <button class="btn btn-light btn-sm" id="compare-mode-btn" title="Compare Images">
                                    <i class="bi bi-columns-gap"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Navigation Arrows -->
                        @if($generalImages->count() > 1)
                            <button class="btn btn-light btn-sm position-absolute top-50 start-0 translate-middle-y ms-2" 
                                    id="prev-image" style="display: none;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="btn btn-light btn-sm position-absolute top-50 end-0 translate-middle-y me-2" 
                                    id="next-image">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            
                            <!-- Dots Indicator -->
                            <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2">
                                <div class="d-flex gap-1" id="image-dots">
                                    @foreach($generalImages as $index => $image)
                                        <span class="dot {{ $index === 0 ? 'active' : '' }}" 
                                              data-index="{{ $index }}"
                                              style="width: 8px; height: 8px; border-radius: 50%; background: {{ $index === 0 ? '#007bff' : 'rgba(255,255,255,0.5)' }}; cursor: pointer; transition: all 0.2s ease;"></span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Thumbnail Images -->
                @if($generalImages->count() > 1)
                <div class="thumbnail-container">
                    <!-- View All Images Button -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">{{ $generalImages->count() }} Images</small>
                        <button class="btn btn-outline-secondary btn-sm" id="view-all-images" data-bs-toggle="modal" data-bs-target="#imageGalleryModal">
                            <i class="bi bi-grid me-1"></i>View All
                        </button>
                    </div>
                    
                    <div class="row g-2" id="image-thumbnails">
                        @foreach($generalImages->take(8) as $index => $image)
                            <div class="col-3">
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid rounded thumbnail-image {{ $index === 0 ? 'active' : '' }}" 
                                         data-index="{{ $index }}"
                                         data-full-url="{{ asset('storage/' . $image->image_path) }}"
                                         style="cursor: pointer; height: 80px; object-fit: cover; width: 100%;">
                                    
                                    @if($index === 7 && $generalImages->count() > 8)
                                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 rounded d-flex align-items-center justify-content-center text-white" 
                                             style="cursor: pointer;" 
                                             data-bs-toggle="modal" data-bs-target="#imageGalleryModal">
                                            <span class="fw-bold">+{{ $generalImages->count() - 8 }}</span>
                                        </div>
                                    @endif
                                </div>
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
                        @php
                            $totalStock = $product->variations->sum('stock');
                        @endphp
                        
                        @if($totalStock > 0)
                            <p class="text-success mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                <span id="stock-count">{{ $totalStock }}</span> in stock
                            </p>
                        @else
                            <p class="text-danger mb-0">
                                <i class="bi bi-x-circle me-1"></i>Currently Out of Stock
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Product Variations -->
                <div class="variations-section mb-4">
                    @if(count($availableAttributes) > 0)
                        @php
                            // Check if all variations are out of stock
                            $allOutOfStock = true;
                            foreach($availableAttributes as $attrName => $attrValues) {
                                foreach($attrValues as $attrValue) {
                                    if (($attrValue['stock_count'] ?? 0) > 0) {
                                        $allOutOfStock = false;
                                        break 2;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($allOutOfStock)
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Currently Out of Stock</strong> - All variations are temporarily unavailable. You can see available options below:
                            </div>
                        @endif
                        
                        @foreach($availableAttributes as $attributeName => $attributeValues)
                            <div class="variation-group mb-3">
                                <label class="form-label fw-bold">{{ $attributeName }}:</label>
                                <div class="variation-options d-flex flex-wrap gap-2">
                                    @foreach($attributeValues as $attributeValue)
                                        @php
                                            $stockCount = $attributeValue['stock_count'] ?? 0;
                                            $isOutOfStock = $stockCount <= 0;
                                            $inStock = $attributeValue['in_stock'] ?? false;
                                        @endphp
                                        <button type="button" 
                                                class="btn {{ $isOutOfStock ? 'btn-secondary' : 'btn-outline-secondary' }} variation-btn {{ $isOutOfStock ? 'disabled' : '' }}" 
                                                data-attribute="{{ strtolower($attributeName) }}"
                                                data-attribute-id="{{ $attributeValue['id'] }}"
                                                data-value="{{ $attributeValue['value'] }}"
                                                data-stock-count="{{ $stockCount }}"
                                                data-in-stock="{{ $inStock ? 'true' : 'false' }}"
                                                {{ $isOutOfStock ? 'disabled' : '' }}
                                                title="{{ $isOutOfStock ? 'Out of stock' : ($stockCount <= 5 ? 'Only ' . $stockCount . ' left' : '') }}"
                                                style="{{ $isOutOfStock ? 'opacity: 0.4; text-decoration: line-through;' : '' }}">
                                            {{ $attributeValue['value'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            This product has no variations available.
                        </div>
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
                        @php
                            $totalStock = $product->variations->sum('stock');
                            // Check if all variations in current filter are out of stock
                            $allCurrentVariationsOutOfStock = true;
                            if (count($availableAttributes) > 0) {
                                foreach($availableAttributes as $attrName => $attrValues) {
                                    foreach($attrValues as $attrValue) {
                                        if (($attrValue['stock_count'] ?? 0) > 0) {
                                            $allCurrentVariationsOutOfStock = false;
                                            break 2;
                                        }
                                    }
                                }
                            } else {
                                $allCurrentVariationsOutOfStock = ($totalStock <= 0);
                            }
                        @endphp
                        
                        @if($allCurrentVariationsOutOfStock)
                            <button type="button" class="btn btn-outline-primary btn-lg flex-fill" id="notify-me-btn">
                                <i class="bi bi-bell me-2"></i>Notify When Available
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg w-100 mt-2" disabled>
                                <i class="bi bi-lightning me-2"></i>Currently Unavailable
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-lg flex-fill" id="add-to-cart-btn" disabled>
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                            <button type="button" class="btn btn-success btn-lg w-100 mt-2" id="buy-now-btn" disabled>
                                <i class="bi bi-lightning me-2"></i>Buy Now
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn" 
                                data-product-id="{{ $product->id }}">
                            <i class="bi bi-heart me-2"></i>Wishlist
                        </button>
                    </div>
                    
                    <!-- Customization Button -->
                    <button type="button" class="btn btn-outline-info btn-lg w-100 mt-2" id="customize-btn">
                        <i class="bi bi-palette me-2"></i>Customize This Product
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
                                    <img src="{{ asset('storage/' . $relatedProduct->images->first()->image_path) }}" 
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

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-images me-2"></i>{{ $product->name }} - Gallery
                </h5>
                <div class="ms-auto me-3">
                    <button class="btn btn-outline-light btn-sm me-2" id="gallery-slideshow-btn">
                        <i class="bi bi-play-circle me-1"></i>Slideshow
                    </button>
                    <button class="btn btn-outline-light btn-sm" id="gallery-compare-btn">
                        <i class="bi bi-columns-gap me-1"></i>Compare
                    </button>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-dark">
                <!-- Normal Gallery View -->
                <div id="normal-gallery-view">
                    <div class="container-fluid h-100">
                        <div class="row h-100">
                            <!-- Main Image Area -->
                            <div class="col-lg-9 d-flex align-items-center justify-content-center">
                                <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                                    <img id="gallery-main-image" 
                                         src="" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid" 
                                         style="max-height: 80vh; object-fit: contain;">
                                    
                                    <!-- Navigation Controls -->
                                    <button class="btn btn-light position-absolute start-0 top-50 translate-middle-y ms-3" 
                                            id="gallery-prev" style="opacity: 0.8;">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button class="btn btn-light position-absolute end-0 top-50 translate-middle-y me-3" 
                                            id="gallery-next" style="opacity: 0.8;">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                    
                                    <!-- Image Counter -->
                                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3">
                                        <span class="badge bg-secondary bg-opacity-75 fs-6" id="gallery-counter">
                                            1 / {{ $generalImages->count() }}
                                        </span>
                                    </div>
                                    
                                    <!-- Slideshow Timer -->
                                    <div class="position-absolute top-0 start-50 translate-middle-x mt-3" id="slideshow-timer" style="display: none;">
                                        <div class="progress" style="width: 200px; height: 4px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thumbnail Sidebar -->
                            <div class="col-lg-3 border-start border-secondary">
                                <div class="p-3">
                                    <h6 class="text-white mb-3">All Images</h6>
                                    <div id="gallery-thumbnails" class="row g-2" style="max-height: 70vh; overflow-y: auto;">
                                        @foreach($generalImages as $index => $image)
                                            <div class="col-12">
                                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="img-fluid rounded gallery-thumb {{ $index === 0 ? 'active' : '' }}" 
                                                     data-index="{{ $index }}"
                                                     data-full-url="{{ asset('storage/' . $image->image_path) }}"
                                                     style="cursor: pointer; height: 80px; object-fit: cover; width: 100%; border: 2px solid transparent;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comparison View -->
                <div id="comparison-view" style="display: none;">
                    <div class="container-fluid h-100">
                        <div class="text-center mb-3">
                            <h5 class="text-white">Image Comparison</h5>
                            <p class="text-muted">Select two images to compare</p>
                        </div>
                        <div class="row h-75">
                            <div class="col-6 border-end border-secondary">
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center">
                                    <div id="compare-image-1" class="comparison-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" 
                                         style="width: 100%; height: 70%; cursor: pointer;">
                                        <span class="text-white">Click thumbnail to select first image</span>
                                    </div>
                                    <p class="text-white mt-2">Image 1</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center">
                                    <div id="compare-image-2" class="comparison-placeholder bg-secondary rounded d-flex align-items-center justify-content-center" 
                                         style="width: 100%; height: 70%; cursor: pointer;">
                                        <span class="text-white">Click thumbnail to select second image</span>
                                    </div>
                                    <p class="text-white mt-2">Image 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div id="compare-thumbnails" class="d-flex gap-2 justify-content-center flex-wrap">
                                    @foreach($product->images as $index => $image)
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             alt="{{ $product->name }}" 
                                             class="compare-thumb rounded" 
                                             data-index="{{ $index }}"
                                             data-full-url="{{ asset('storage/' . $image->image_path) }}"
                                             style="cursor: pointer; height: 60px; width: 80px; object-fit: cover; border: 2px solid transparent;">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-dark border-0 justify-content-center">
                <button type="button" class="btn btn-outline-light" id="gallery-zoom-in">
                    <i class="bi bi-zoom-in me-1"></i>Zoom In
                </button>
                <button type="button" class="btn btn-outline-light" id="gallery-download">
                    <i class="bi bi-download me-1"></i>Download
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Share Image Modal -->
<div class="modal fade" id="shareImageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-share me-2"></i>Share This Image
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="share-preview-image" src="" alt="Preview" class="img-fluid mb-3 rounded" style="max-height: 200px;">
                <div class="row g-2">
                    <div class="col-6">
                        <button class="btn btn-primary w-100" id="share-facebook">
                            <i class="bi bi-facebook me-2"></i>Facebook
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-info w-100" id="share-twitter">
                            <i class="bi bi-twitter me-2"></i>Twitter
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-success w-100" id="share-whatsapp">
                            <i class="bi bi-whatsapp me-2"></i>WhatsApp
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-secondary w-100" id="copy-image-link">
                            <i class="bi bi-clipboard me-2"></i>Copy Link
                        </button>
                    </div>
                </div>
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

.variation-btn:hover:not(:disabled) {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
}

.variation-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    text-decoration: line-through;
}

.variation-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.variation-btn.disabled:hover,
.variation-btn:disabled:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: white !important;
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

/* Enhanced Gallery Styles */
.gallery-thumb {
    transition: all 0.3s ease;
    cursor: pointer;
}

.gallery-thumb:hover {
    transform: scale(1.05);
    border-color: #0d6efd !important;
}

.gallery-thumb.active {
    border-color: #0d6efd !important;
    transform: scale(1.02);
}

#imageGalleryModal .modal-content {
    background: #1a1a1a !important;
}

#gallery-main-image {
    transition: all 0.3s ease;
    cursor: zoom-in;
}

.thumbnail-container .position-absolute {
    transition: opacity 0.2s ease;
}

.thumbnail-container .position-absolute:hover {
    opacity: 0.9;
}

@media (max-width: 768px) {
    #imageGalleryModal .col-lg-3 {
        display: none;
    }
    
    #imageGalleryModal .col-lg-9 {
        width: 100%;
    }
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
    @if($generalImages->count() > 0)
        availableImages = [
            @foreach($generalImages as $image)
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
    
    // Dots indicator click handler
    $('.dot').click(function() {
        const index = $(this).data('index');
        currentImageIndex = index;
        updateMainImage();
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
        // Prevent clicking on disabled buttons (out of stock)
        if ($(this).prop('disabled') || $(this).hasClass('disabled')) {
            const buttonValue = $(this).data('value');
            showToast(`${buttonValue} is currently out of stock`, 'warning');
            return false;
        }
        
        const attribute = $(this).data('attribute');
        const attributeId = $(this).data('attribute-id');
        const value = $(this).data('value');
        const stockCount = $(this).data('stock-count') || 0;
        const inStock = $(this).data('in-stock') === 'true';
        
        console.log('Variation button clicked:', {attribute, attributeId, value, stockCount, inStock});
        
        // Double-check if this option has stock
        if (!inStock || stockCount <= 0) {
            showToast(`${value} is currently out of stock`, 'warning');
            return false;
        }
        
        // Toggle selection
        if ($(this).hasClass('active')) {
            // Deselecting - remove from selectedVariations
            $(this).removeClass('active');
            delete selectedVariations[attribute];
            console.log('Deselected:', attribute);
        } else {
            // Selecting - remove active class from siblings and add to this one
            $(`.variation-btn[data-attribute="${attribute}"]`).removeClass('active');
            $(this).addClass('active');
            selectedVariations[attribute] = {
                id: attributeId,
                value: value,
                stock_count: stockCount,
                in_stock: inStock
            };
            console.log('Selected:', attribute, selectedVariations[attribute]);
        }
        
        console.log('Current selected variations:', selectedVariations);
        
        // Update available options based on current selection (Amazon/Flipkart style)
        updateAvailableOptions();
        updateProductDisplay();
    });
    
    // Amazon/Flipkart style variation filtering
    function updateAvailableOptions() {
        const selectedIds = Object.values(selectedVariations).map(v => v.id);
        
        console.log('=== UPDATING AVAILABLE OPTIONS ===');
        console.log('Currently selected attribute IDs:', selectedIds);
        console.log('Selected variations object:', selectedVariations);
        
        // Get filtered attributes based on current selection
        $.ajax({
            url: '{{ route("products.filtered-attributes", $product->id) }}',
            type: 'GET',
            data: {
                selected: selectedIds
            },
            success: function(response) {
                console.log('🔄 Filtered attributes response:', response);
                
                if (response.success) {
                    console.log('📊 Available variations count:', response.available_variations_count);
                    
                    // Update each attribute group
                    Object.keys(response.attributes).forEach(attributeName => {
                        const attributeOptions = response.attributes[attributeName];
                        
                        console.log(`🏷️ Processing ${attributeName} options:`, attributeOptions);
                        
                        // Enable/disable buttons based on availability
                        $(`.variation-btn[data-attribute="${attributeName.toLowerCase()}"]`).each(function() {
                            const buttonId = $(this).data('attribute-id');
                            const buttonValue = $(this).data('value');
                            const optionData = attributeOptions.find(opt => opt.id === buttonId);
                            
                            if (optionData) {
                                console.log(`🔘 ${attributeName} "${buttonValue}":`, optionData.available ? '✅ Available' : '❌ Not available', `(Stock: ${optionData.stock_count || 0})`);
                                
                                // Check if available AND has stock
                                const isAvailableWithStock = (optionData.available || optionData.already_selected) && (optionData.stock_count > 0);
                                
                                if (isAvailableWithStock) {
                                    // Available option with stock - enable
                                    $(this).prop('disabled', false)
                                          .removeClass('disabled')
                                          .addClass('btn-outline-secondary')
                                          .css('opacity', '1')
                                          .attr('title', '');
                                    
                                    // Add stock info if low stock
                                    if (optionData.stock_count <= 5) {
                                        $(this).attr('title', `Only ${optionData.stock_count} left`);
                                    }
                                } else {
                                    // Unavailable option OR out of stock - disable
                                    if (!$(this).hasClass('active')) {
                                        $(this).prop('disabled', true)
                                              .addClass('disabled')
                                              .css('opacity', '0.4');
                                        
                                        // Set appropriate title based on reason for disabling
                                        if (optionData.stock_count === 0) {
                                            $(this).attr('title', 'Out of stock');
                                            console.log(`🚫 Disabled ${attributeName} "${buttonValue}" - out of stock`);
                                        } else {
                                            $(this).attr('title', 'Not available with current selection');
                                            console.log(`🚫 Disabled ${attributeName} "${buttonValue}" - not available with current selection`);
                                        }
                                    }
                                }
                            } else {
                                console.log(`⚠️ No data found for ${attributeName} "${buttonValue}"`);
                            }
                        });
                    });
                    
                    console.log('✅ Variation filtering complete');
                    
                    // Update the stock info based on available variations
                    if (response.available_variations_count === 0) {
                        $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>No variations available with current selection</p>');
                    } else if (Object.keys(selectedVariations).length < {{ count($availableAttributes) }}) {
                        $('#stock-info').html('<p class="text-info mb-0"><i class="bi bi-info-circle me-1"></i>' + response.available_variations_count + ' variations available</p>');
                    }
                }
            },
            error: function(xhr) {
                console.error('❌ Failed to get filtered attributes:', xhr);
            }
        });
    }
    
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

    // Notify when available
    $('#notify-me-btn').click(function() {
        // Simple implementation - you can enhance this with email collection
        const button = $(this);
        
        // For now, just add to wishlist and show notification
        $.ajax({
            url: '{{ route("wishlist.toggle") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: {{ $product->id }}
            },
            beforeSend: function() {
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
            },
            success: function(response) {
                if (response.added) {
                    button.html('<i class="bi bi-bell-fill me-2"></i>You\'ll be Notified!');
                    button.removeClass('btn-outline-primary').addClass('btn-success');
                    showToast('Added to wishlist! You\'ll be notified when back in stock.', 'success');
                } else {
                    button.html('<i class="bi bi-bell me-2"></i>Notify When Available');
                    button.removeClass('btn-success').addClass('btn-outline-primary');
                    showToast('Notification removed.', 'info');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    // Show login modal or redirect
                    showToast('Please login to get stock notifications', 'info');
                    window.location.href = '{{ route("login") }}';
                } else {
                    showToast('Failed to set up notification', 'danger');
                }
            },
            complete: function() {
                button.prop('disabled', false);
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
            
            // Update dots indicator
            $('.dot').each(function() {
                const dotIndex = $(this).data('index');
                if (dotIndex === currentImageIndex) {
                    $(this).addClass('active').css('background', '#007bff');
                } else {
                    $(this).removeClass('active').css('background', 'rgba(255,255,255,0.5)');
                }
            });
        }
        updateNavigationButtons();
    }
    
    function updateNavigationButtons() {
        $('#prev-image').toggle(currentImageIndex > 0);
        $('#next-image').toggle(currentImageIndex < availableImages.length - 1);
    }
    
    // Bind image-related events (for dynamic content)
    function bindImageEvents() {
        // Thumbnail click handler
        $('.thumbnail-image').off('click').click(function() {
            const index = $(this).data('index');
            const fullUrl = $(this).data('full-url');
            
            currentImageIndex = index;
            $('#main-product-image').attr('src', fullUrl)
                                   .attr('data-zoom-src', fullUrl);
            
            $('.thumbnail-image').removeClass('active');
            $(this).addClass('active');
            
            updateNavigationButtons();
        });
        
        // Dots indicator click handler
        $('.dot').off('click').click(function() {
            const index = $(this).data('index');
            currentImageIndex = index;
            updateMainImage();
        });
        
        // Gallery thumbnail clicks
        $('.gallery-thumb').off('click').click(function() {
            if (!isComparisonMode) {
                const index = parseInt($(this).data('index'));
                galleryCurrentIndex = index;
                updateGalleryDisplay();
            }
        });
    }
    
    function updateProductDisplay() {
        // Count total number of attribute types
        const totalAttributeTypes = {{ count($availableAttributes) }};
        
        console.log('=== UPDATE PRODUCT DISPLAY ===');
        console.log('Selected variations:', selectedVariations);
        console.log('Total attribute types:', totalAttributeTypes);
        console.log('Selected count:', Object.keys(selectedVariations).length);
        
        // Check if we need all attributes selected or if some variations don't require all
        const hasAllRequiredAttributes = Object.keys(selectedVariations).length === totalAttributeTypes;
        
        console.log('Has all required attributes:', hasAllRequiredAttributes);
        
        // If we have selected all required attributes, fetch variation data
        if (hasAllRequiredAttributes && totalAttributeTypes > 0) {
            // Convert selected variations to array of attribute value IDs
            const selectedAttributeIds = [];
            Object.keys(selectedVariations).forEach(key => {
                selectedAttributeIds.push(selectedVariations[key].id);
            });
            
            console.log('Fetching variation for attribute IDs:', selectedAttributeIds);
            
            // Make AJAX call to get variation-specific data
            $.ajax({
                url: '{{ route("products.variations", $product->id) }}',
                type: 'GET',
                data: {
                    attributes: selectedAttributeIds
                },
                success: function(response) {
                    console.log('Variation response:', response);
                    
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
                            
                            // Show Add to Cart buttons and hide Notify buttons
                            $('#add-to-cart-btn, #buy-now-btn').show().prop('disabled', false);
                            $('#notify-me-btn').hide();
                            $('.action-buttons button:contains("Currently Unavailable")').hide();
                            
                            $('#quantity').attr('max', stockCount);
                            console.log('✅ BUTTONS ENABLED - Stock available:', stockCount);
                        } else {
                            $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Out of Stock</p>');
                            
                            // Show Notify buttons and hide Add to Cart buttons
                            $('#add-to-cart-btn, #buy-now-btn').hide();
                            $('#notify-me-btn').show();
                            $('.action-buttons button:contains("Currently Unavailable")').show();
                            
                            console.log('❌ Buttons switched to Notify - out of stock');
                        }
                        
                        // Update images if available
                        if (response.images && response.images.length > 0) {
                            availableImages = response.images;
                            currentImageIndex = 0;
                            
                            // Update main image
                            $('#main-product-image').attr('src', availableImages[0].url)
                                                   .attr('data-zoom-src', availableImages[0].url);
                            
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
                            
                            // Update dots indicator
                            let dotsHtml = '';
                            response.images.forEach((image, index) => {
                                dotsHtml += `
                                    <span class="dot ${index === 0 ? 'active' : ''}" 
                                          data-index="${index}"
                                          style="width: 8px; height: 8px; border-radius: 50%; background: ${index === 0 ? '#007bff' : 'rgba(255,255,255,0.5)'}; cursor: pointer; transition: all 0.2s ease;"></span>
                                `;
                            });
                            $('#image-dots').html(dotsHtml);
                            
                            // Update gallery thumbnails in modal
                            let galleryThumbnailHtml = '';
                            response.images.forEach((image, index) => {
                                galleryThumbnailHtml += `
                                    <div class="col-12">
                                        <img src="${image.url}" 
                                             alt="{{ $product->name }}" 
                                             class="img-fluid rounded gallery-thumb ${index === 0 ? 'active' : ''}" 
                                             data-index="${index}"
                                             data-full-url="${image.url}"
                                             style="cursor: pointer; height: 80px; object-fit: cover; width: 100%; border: 2px solid transparent;">
                                    </div>
                                `;
                            });
                            $('#gallery-thumbnails').html(galleryThumbnailHtml);
                            
                            // Update comparison thumbnails
                            let comparisonThumbnailHtml = '';
                            response.images.forEach((image, index) => {
                                comparisonThumbnailHtml += `
                                    <img src="${image.url}" 
                                         alt="{{ $product->name }}" 
                                         class="compare-thumb rounded" 
                                         data-index="${index}"
                                         data-full-url="${image.url}"
                                         style="cursor: pointer; height: 60px; width: 80px; object-fit: cover; border: 2px solid transparent;">
                                `;
                            });
                            $('#compare-thumbnails').html(comparisonThumbnailHtml);
                            
                            // Update image count display
                            $('.text-muted').text(response.images.length + ' Images');
                            $('#gallery-counter').text('1 / ' + response.images.length);
                            
                            // Show/hide navigation elements based on image count
                            if (response.images.length > 1) {
                                $('#image-dots, #next-image').show();
                                $('.thumbnail-container, #view-all-images').show();
                            } else {
                                $('#image-dots, #prev-image, #next-image').hide();
                                $('.thumbnail-container, #view-all-images').hide();
                            }
                            
                            console.log('✅ Updated images for variation:', response.images.length, 'images loaded');
                            
                            // Re-bind click events for new elements
                            bindImageEvents();
                        }
                        
                        updateNavigationButtons();
                    } else {
                        console.log('❌ No variations found for selected attributes');
                        selectedVariationData = null;
                        
                        // Show Notify buttons and hide Add to Cart buttons
                        $('#add-to-cart-btn, #buy-now-btn').hide();
                        $('#notify-me-btn').show();
                        $('.action-buttons button:contains("Currently Unavailable")').show();
                        
                        $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>This combination is not available</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error fetching variation:', error);
                    selectedVariationData = null;
                    
                    // Show Notify buttons and hide Add to Cart buttons
                    $('#add-to-cart-btn, #buy-now-btn').hide();
                    $('#notify-me-btn').show();
                    $('.action-buttons button:contains("Currently Unavailable")').show();
                    
                    $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>Error loading variation data</p>');
                }
            });
        } else if (totalAttributeTypes > 0) {
            // Not all attributes selected yet
            console.log('⏳ Not all attributes selected yet - need', totalAttributeTypes - Object.keys(selectedVariations).length, 'more');
            selectedVariationData = null;
            $('#stock-info').html('<p class="text-warning mb-0"><i class="bi bi-exclamation-circle me-1"></i>Please select all required options (' + Object.keys(selectedVariations).length + '/' + totalAttributeTypes + ')</p>');
            
            // Keep buttons disabled until all selections made
            $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
        } else {
            // No attributes required, product should be available
            console.log('ℹ️ No attributes required');
            selectedVariationData = null;
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
        console.log('Multiple variations detected - {{ $product->variations->count() }} variations');
        console.log('Available attributes: {{ count($availableAttributes) }}');
        
        @php
            $totalStock = $product->variations->sum('stock');
        @endphp
        
        @if($totalStock > 0)
            // Has stock - show normal add to cart flow
            if ($('#add-to-cart-btn').length) {
                $('#add-to-cart-btn, #buy-now-btn').prop('disabled', true);
                $('#stock-info').html('<p class="text-warning mb-0"><i class="bi bi-exclamation-circle me-1"></i>Please select all required options</p>');
            }
        @else
            // No stock - notify button is already shown in blade template
            $('#stock-info').html('<p class="text-danger mb-0"><i class="bi bi-x-circle me-1"></i>All variations are currently out of stock</p>');
        @endif
        
        // Enable button temporarily if no attributes (should have at least one variation)
        @if(count($availableAttributes) === 0 && $product->variations->count() > 0)
            console.log('Edge case: variations without attributes');
            @php
                $firstVariation = $product->variations->first();
            @endphp
            @if($firstVariation->stock > 0)
                if ($('#add-to-cart-btn').length) {
                    $('#add-to-cart-btn, #buy-now-btn').prop('disabled', false);
                }
                selectedVariationData = {
                    id: {{ $firstVariation->id }},
                    stock: {{ $firstVariation->stock }},
                    price: {{ $firstVariation->price }}
                };
                $('#quantity').attr('max', {{ $firstVariation->stock }});
                console.log('Edge case handled - buttons enabled');
            @endif
        @endif
    @endif
    
    console.log('Page initialization complete - Selected variations:', selectedVariations);
    
    // Initialize available options on page load (Amazon/Flipkart style)
    @if(count($availableAttributes) > 0)
        updateAvailableOptions();
    @endif
    
    // Initialize button states based on current variation availability
    function initializeButtonStates() {
        @php
            // Check if all current variations are out of stock
            $allCurrentVariationsOutOfStock = true;
            if (count($availableAttributes) > 0) {
                foreach($availableAttributes as $attrName => $attrValues) {
                    foreach($attrValues as $attrValue) {
                        if (($attrValue['stock_count'] ?? 0) > 0) {
                            $allCurrentVariationsOutOfStock = false;
                            break 2;
                        }
                    }
                }
            } else {
                $totalStock = $product->variations->sum('stock');
                $allCurrentVariationsOutOfStock = ($totalStock <= 0);
            }
        @endphp
        
        @if($allCurrentVariationsOutOfStock)
            // All variations are out of stock - show notify button
            if ($('#add-to-cart-btn').length && $('#notify-me-btn').length) {
                $('#add-to-cart-btn, #buy-now-btn').hide();
                $('#notify-me-btn').show();
                $('.action-buttons button:contains("Currently Unavailable")').show();
            }
        @else
            // Some variations have stock - show add to cart (initially disabled)
            if ($('#add-to-cart-btn').length && $('#notify-me-btn').length) {
                $('#add-to-cart-btn, #buy-now-btn').show().prop('disabled', true);
                $('#notify-me-btn').hide();
                $('.action-buttons button:contains("Currently Unavailable")').hide();
            }
        @endif
    }
    
    // Call initialization
    initializeButtonStates();

    
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
    
    // Gallery Modal Functionality
    let galleryCurrentIndex = 0;
    let galleryImages = [];
    
    // Initialize gallery when modal opens
    $('#imageGalleryModal').on('show.bs.modal', function() {
        // Get current images (either product images or variation images)
        galleryImages = availableImages.length > 0 ? availableImages : [];
        if (galleryImages.length === 0) {
            // Fallback to product images
            @if($product->images->count() > 0)
                galleryImages = [
                    @foreach($product->images as $image)
                        {
                            url: "{{ asset('storage/' . $image->image_path) }}",
                            id: {{ $image->id }}
                        },
                    @endforeach
                ];
            @endif
        }
        
        galleryCurrentIndex = currentImageIndex || 0;
        updateGalleryDisplay();
    });
    
    // Gallery navigation
    $('#gallery-prev').click(function() {
        if (galleryCurrentIndex > 0) {
            galleryCurrentIndex--;
            updateGalleryDisplay();
        }
    });
    
    $('#gallery-next').click(function() {
        if (galleryCurrentIndex < galleryImages.length - 1) {
            galleryCurrentIndex++;
            updateGalleryDisplay();
        }
    });
    
    // Gallery thumbnail clicks
    $(document).on('click', '.gallery-thumb', function() {
        galleryCurrentIndex = $(this).data('index');
        updateGalleryDisplay();
    });
    
    // Gallery zoom functionality
    $('#gallery-zoom-in').click(function() {
        const currentImage = $('#gallery-main-image').attr('src');
        $('#zoom-modal-image').attr('src', currentImage);
        $('#imageGalleryModal').modal('hide');
        $('#imageZoomModal').modal('show');
    });
    
    // Download current image
    $('#gallery-download').click(function() {
        const currentImage = $('#gallery-main-image').attr('src');
        const link = document.createElement('a');
        link.href = currentImage;
        link.download = '{{ $product->name }}-image-' + (galleryCurrentIndex + 1) + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
    
    // Keyboard navigation in gallery
    $(document).keydown(function(e) {
        if ($('#imageGalleryModal').hasClass('show')) {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    if (galleryCurrentIndex > 0) {
                        galleryCurrentIndex--;
                        updateGalleryDisplay();
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    if (galleryCurrentIndex < galleryImages.length - 1) {
                        galleryCurrentIndex++;
                        updateGalleryDisplay();
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    $('#imageGalleryModal').modal('hide');
                    break;
            }
        }
    });
    
    function updateGalleryDisplay() {
        if (galleryImages.length === 0) return;
        
        const currentImage = galleryImages[galleryCurrentIndex];
        $('#gallery-main-image').attr('src', currentImage.url);
        $('#gallery-counter').text(`${galleryCurrentIndex + 1} / ${galleryImages.length}`);
        
        // Update thumbnail highlights
        $('.gallery-thumb').removeClass('active').css('border-color', 'transparent');
        $(`.gallery-thumb[data-index="${galleryCurrentIndex}"]`)
            .addClass('active')
            .css('border-color', '#0d6efd');
        
        // Update navigation buttons
        $('#gallery-prev').toggleClass('disabled', galleryCurrentIndex === 0);
        $('#gallery-next').toggleClass('disabled', galleryCurrentIndex === galleryImages.length - 1);
    }
    
    // ============ ENHANCED GALLERY FEATURES ============
    
    // Slideshow functionality
    let slideshowInterval = null;
    let isComparisonMode = false;
    let selectedCompareImages = [];
    
    $('#gallery-slideshow-btn').click(toggleSlideshow);
    
    function toggleSlideshow() {
        if (slideshowInterval) {
            stopSlideshow();
        } else {
            startSlideshow();
        }
    }

    function startSlideshow() {
        if (isComparisonMode) return;
        
        $('#gallery-slideshow-btn').html('<i class="bi bi-pause-circle me-1"></i>Pause');
        $('#slideshow-timer').show();
        
        let progress = 0;
        const duration = 3000; // 3 seconds
        const interval = 50;
        const increment = (100 / duration) * interval;

        slideshowInterval = setInterval(() => {
            progress += increment;
            $('.progress-bar').css('width', progress + '%');
            
            if (progress >= 100) {
                progress = 0;
                if (galleryCurrentIndex < galleryImages.length - 1) {
                    galleryCurrentIndex++;
                } else {
                    galleryCurrentIndex = 0; // Loop back to start
                }
                updateGalleryDisplay();
            }
        }, interval);
    }

    function stopSlideshow() {
        if (slideshowInterval) {
            clearInterval(slideshowInterval);
            slideshowInterval = null;
        }
        $('#gallery-slideshow-btn').html('<i class="bi bi-play-circle me-1"></i>Slideshow');
        $('#slideshow-timer').hide();
        $('.progress-bar').css('width', '0%');
    }

    // Comparison mode functionality
    $('#gallery-compare-btn').click(toggleComparisonMode);

    function toggleComparisonMode() {
        isComparisonMode = !isComparisonMode;
        selectedCompareImages = [];
        
        if (isComparisonMode) {
            $('#normal-gallery-view').hide();
            $('#comparison-view').show();
            $('#gallery-compare-btn').html('<i class="bi bi-grid me-1"></i>Gallery');
            stopSlideshow();
            resetComparisonView();
        } else {
            $('#normal-gallery-view').show();
            $('#comparison-view').hide();
            $('#gallery-compare-btn').html('<i class="bi bi-columns-gap me-1"></i>Compare');
        }
    }

    // Comparison image selection
    $(document).on('click', '.compare-thumb', function() {
        const imageUrl = $(this).data('full-url');
        const imageIndex = $(this).data('index');
        
        if (selectedCompareImages.length < 2) {
            const compareSlot = selectedCompareImages.length === 0 ? '#compare-image-1' : '#compare-image-2';
            
            $(compareSlot).html(`<img src="${imageUrl}" class="img-fluid rounded" style="max-height: 100%; max-width: 100%; object-fit: contain;">`);
            
            selectedCompareImages.push({ url: imageUrl, index: imageIndex });
            $(this).css('border-color', '#0d6efd');
            
            if (selectedCompareImages.length === 2) {
                $('.compare-thumb').css('opacity', '0.5').css('pointer-events', 'none');
                $(this).css('opacity', '1');
                $(`.compare-thumb[data-index="${selectedCompareImages[0].index}"]`).css('opacity', '1');
                
                // Add comparison controls
                $('#comparison-view .text-center').append(`
                    <div class="mt-3" id="comparison-controls">
                        <button class="btn btn-outline-light btn-sm me-2" onclick="resetComparisonView()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="swapComparisonImages()">
                            <i class="bi bi-arrow-left-right me-1"></i>Swap
                        </button>
                    </div>
                `);
            }
        }
    });

    window.resetComparisonView = function() {
        selectedCompareImages = [];
        $('#compare-image-1, #compare-image-2').html(`
            <span class="text-white">Click thumbnail to select image</span>
        `);
        $('.compare-thumb').css('border-color', 'transparent').css('opacity', '1').css('pointer-events', 'auto');
        $('#comparison-controls').remove();
    };

    window.swapComparisonImages = function() {
        if (selectedCompareImages.length === 2) {
            const temp = $('#compare-image-1').html();
            $('#compare-image-1').html($('#compare-image-2').html());
            $('#compare-image-2').html(temp);
            
            [selectedCompareImages[0], selectedCompareImages[1]] = [selectedCompareImages[1], selectedCompareImages[0]];
        }
    };
    
    // Share functionality enhancements
    $('#share-facebook').click(function() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Check out this {{ $product->name }}!');
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
    });

    $('#share-twitter').click(function() {
        const text = encodeURIComponent('Check out this amazing {{ $product->name }}!');
        const url = encodeURIComponent(window.location.href);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
    });

    $('#share-whatsapp').click(function() {
        const text = encodeURIComponent(`Check out this {{ $product->name }}: ${window.location.href}`);
        window.open(`https://wa.me/?text=${text}`, '_blank');
    });

    $('#copy-image-link').click(function() {
        const currentImageUrl = $('.main-image').attr('src');
        const btn = $(this);
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(currentImageUrl).then(function() {
                btn.html('<i class="bi bi-check me-2"></i>Copied!').addClass('btn-success').removeClass('btn-secondary');
                setTimeout(() => {
                    btn.html('<i class="bi bi-clipboard me-2"></i>Copy Link').removeClass('btn-success').addClass('btn-secondary');
                }, 2000);
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = currentImageUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            btn.html('<i class="bi bi-check me-2"></i>Copied!').addClass('btn-success').removeClass('btn-secondary');
            setTimeout(() => {
                btn.html('<i class="bi bi-clipboard me-2"></i>Copy Link').removeClass('btn-success').addClass('btn-secondary');
            }, 2000);
        }
    });

    // Image magnification functionality
    const zoomLens = document.querySelector('.zoom-lens');
    const zoomResult = document.querySelector('.zoom-result');
    const mainImage = document.querySelector('.main-image');

    if (mainImage && zoomLens && zoomResult) {
        mainImage.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Position lens
            const lensWidth = zoomLens.offsetWidth;
            const lensHeight = zoomLens.offsetHeight;
            
            let lensX = x - (lensWidth / 2);
            let lensY = y - (lensHeight / 2);
            
            // Keep lens within image bounds
            lensX = Math.max(0, Math.min(lensX, rect.width - lensWidth));
            lensY = Math.max(0, Math.min(lensY, rect.height - lensHeight));
            
            zoomLens.style.left = lensX + 'px';
            zoomLens.style.top = lensY + 'px';
            
            // Update zoom result
            const zoomX = (lensX / (rect.width - lensWidth)) * 100;
            const zoomY = (lensY / (rect.height - lensHeight)) * 100;
            
            const zoomSrc = this.dataset.zoomSrc || this.src;
            zoomResult.style.backgroundPosition = `${zoomX}% ${zoomY}%`;
            zoomResult.style.backgroundImage = `url(${zoomSrc})`;
        });

        mainImage.addEventListener('mouseenter', function() {
            zoomLens.style.display = 'block';
            zoomResult.style.display = 'block';
        });

        mainImage.addEventListener('mouseleave', function() {
            zoomLens.style.display = 'none';
            zoomResult.style.display = 'none';
        });
    }
    
    // Enhanced keyboard navigation
    $(document).keydown(function(e) {
        if ($('#imageGalleryModal').hasClass('show')) {
            switch(e.key) {
                case ' ':
                    e.preventDefault();
                    toggleSlideshow();
                    break;
                case 'c':
                case 'C':
                    e.preventDefault();
                    toggleComparisonMode();
                    break;
            }
        }
    });
    
    // Clean up on modal close
    $('#imageGalleryModal').on('hidden.bs.modal', function() {
        stopSlideshow();
        if (isComparisonMode) {
            toggleComparisonMode();
        }
    });
    
    // Enhanced download with toast notification
    $('#gallery-download').click(function() {
        const currentImage = $('#gallery-main-image').attr('src');
        const link = document.createElement('a');
        link.href = currentImage;
        link.download = '{{ $product->name }}-image-' + (galleryCurrentIndex + 1) + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('Image downloaded successfully!', 'success');
    });
});
</script>
@endpush
