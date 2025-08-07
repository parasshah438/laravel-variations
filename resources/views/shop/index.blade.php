@extends('layouts.app')

@section('title', 'Shop - All Products')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        @extends('layouts.app')

@section('title', 'Shop - All Products')

@section('content')
<!-- Shop Header with Search -->
<div class="shop-header bg-light py-4 border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-2">Shop All Products</h1>
                <p class="text-muted mb-0">Discover our complete collection</p>
            </div>
            <div class="col-md-6">
                <div class="shop-search">
                    @include('components.advanced-search')
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="row">{!! "<!-- Left Sidebar - Filters -->" !!}
        <div class="col-lg-3 col-md-4">
            <div class="shop-filters sticky-top" style="top: 100px;">
                <!-- Filter Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>Filters
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-all-filters">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset All
                    </button>
                </div>

                <!-- Search Filter -->
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-search me-2"></i>Search Products
                            </h6>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-input" 
                                       placeholder="Search products..." value="">
                                <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Filter -->
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-grid me-2"></i>Categories
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-categories">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($categories as $category)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" 
                                               value="{{ $category->id }}" id="cat_{{ $category->id }}">
                                        <label class="form-check-label d-flex justify-content-between" 
                                               for="cat_{{ $category->id }}">
                                            <span>{{ $category->name }}</span>
                                            <span class="badge bg-light text-dark category-count" 
                                                  data-category="{{ $category->id }}">{{ $category->products_count }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Brands Filter -->
                @if($brands->count() > 0)
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-award me-2"></i>Brands
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-brands">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($brands as $brand)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input brand-filter" type="checkbox" 
                                               value="{{ $brand->id }}" id="brand_{{ $brand->id }}">
                                        <label class="form-check-label d-flex justify-content-between" 
                                               for="brand_{{ $brand->id }}">
                                            <span>{{ $brand->name }}</span>
                                            <span class="badge bg-light text-dark brand-count" 
                                                  data-brand="{{ $brand->id }}">{{ $brand->products_count }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Price Range Filter -->
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-currency-rupee me-2"></i>Price Range
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-price">Clear</button>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="price-min" placeholder="Min" min="0" value="{{ $priceRange['min'] }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="price-max" placeholder="Max" min="0" value="{{ $priceRange['max'] }}">
                                </div>
                            </div>
                            <div class="range-values text-center small text-muted">
                                ₹<span id="current-min">{{ $priceRange['min'] }}</span> - 
                                ₹<span id="current-max">{{ $priceRange['max'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attributes Filter (Size, Color, etc.) -->
                @foreach($attributes as $attribute)
                    @if($attribute->attributeValues->count() > 0)
                    <div class="filter-section mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-tags me-2"></i>{{ $attribute->name }}
                                    </h6>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                            data-clear-attribute="{{ $attribute->id }}">Clear</button>
                                </div>
                                <div class="filter-options" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($attribute->attributeValues as $value)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input attribute-filter" type="checkbox" 
                                                   value="{{ $value->id }}" 
                                                   data-attribute="{{ $attribute->id }}"
                                                   id="attr_{{ $attribute->id }}_{{ $value->id }}">
                                            <label class="form-check-label d-flex justify-content-between" 
                                                   for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                <span>{{ $value->value }}</span>
                                                <span class="badge bg-light text-dark attribute-count" 
                                                      data-attribute="{{ $attribute->id }}" 
                                                      data-value="{{ $value->id }}">{{ $value->product_variations_count }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach

                <!-- Active Filters -->
                <div id="active-filters" class="mb-4" style="display: none;">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-check-circle me-2"></i>Active Filters
                            </h6>
                            <div id="active-filters-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content - Products -->
        <div class="col-lg-9 col-md-8">
            <!-- Products Header -->
            <div class="products-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="products-count">
                            <h4 class="mb-1">All Products</h4>
                            <p class="text-muted mb-0" id="products-showing">
                                Loading products...
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="products-controls d-flex justify-content-end align-items-center gap-3">
                            <!-- Sort Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                        id="sortDropdown" data-bs-toggle="dropdown">
                                    <i class="bi bi-sort-down me-2"></i>Sort By
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="latest">Latest</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="name">Name A-Z</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="price_low">Price Low to High</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="price_high">Price High to Low</a></li>
                                </ul>
                            </div>

                            <!-- View Toggle -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary view-toggle active" 
                                        data-view="grid" title="Grid View">
                                    <i class="bi bi-grid"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary view-toggle" 
                                        data-view="list" title="List View">
                                    <i class="bi bi-list"></i>
                                </button>
                            </div>

                            <!-- Per Page -->
                            <select class="form-select form-select-sm" id="per-page" style="width: auto;">
                                <option value="12">12 per page</option>
                                <option value="24">24 per page</option>
                                <option value="48">48 per page</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="products-loading" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading products...</p>
            </div>

            <!-- Products Grid -->
            <div id="products-container">
                <!-- Products will be loaded here via AJAX -->
            </div>

            <!-- Pagination -->
            <div id="pagination-container" class="mt-4">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.shop-filters .card {
    transition: all 0.2s ease;
}

.shop-filters .card:hover {
    transform: translateY(-2px);
}

.filter-options::-webkit-scrollbar {
    width: 4px;
}

.filter-options::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.filter-options::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.filter-options::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.products-header {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.view-toggle.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.active-filter-tag {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 12px;
    margin: 2px;
    position: relative;
    padding-right: 24px;
}

.active-filter-tag .remove-filter {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #1976d2;
    font-weight: bold;
}

.products-grid {
    /* Remove CSS Grid, let Bootstrap handle the layout */
    min-height: 400px;
}

.product-item {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.product-item:hover .product-overlay {
    opacity: 1 !important;
}

.product-overlay {
    opacity: 0;
    transition: opacity 0.3s ease;
    background: rgba(0,0,0,0.7) !important;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.product-item:hover .card-img-top {
    transform: scale(1.05);
}

.wishlist-btn {
    opacity: 0.8;
    transition: all 0.2s ease;
}

.wishlist-btn:hover {
    opacity: 1;
    transform: scale(1.1);
}

.products-list .product-card {
    display: flex;
    flex-direction: row;
    height: auto;
}

.products-list .product-card .card-body {
    flex: 1;
}

@media (max-width: 768px) {
    .shop-filters {
        position: relative !important;
        top: auto !important;
    }
    
    /* Mobile: 2 products per row */
    .products-grid .col-xl-3,
    .products-grid .col-lg-4 {
        flex: 0 0 auto;
        width: 50%;
    }
}

@media (max-width: 576px) {
    /* Small mobile: 1 product per row */
    .products-grid .col-xl-3,
    .products-grid .col-lg-4,
    .products-grid .col-md-6 {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentFilters = {
        categories: [],
        brands: [],
        attributes: {},
        price_min: '',
        price_max: '',
        search: '',
        sort: 'latest',
        per_page: 12,
        page: 1
    };
    
    let ajaxTimeout;
    let currentView = 'grid';
    
    // Load initial products
    loadProducts();
    
    // Search input with debounce
    $('#search-input').on('input', function() {
        clearTimeout(ajaxTimeout);
        currentFilters.search = $(this).val();
        ajaxTimeout = setTimeout(() => {
            currentFilters.page = 1;
            loadProducts();
            updateActiveFilters();
        }, 500);
    });
    
    // Category filters
    $('.category-filter').change(function() {
        const categoryId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.categories.push(categoryId);
        } else {
            currentFilters.categories = currentFilters.categories.filter(id => id !== categoryId);
        }
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    // Brand filters
    $('.brand-filter').change(function() {
        const brandId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.brands.push(brandId);
        } else {
            currentFilters.brands = currentFilters.brands.filter(id => id !== brandId);
        }
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    // Attribute filters
    $('.attribute-filter').change(function() {
        const attributeId = $(this).data('attribute');
        const valueId = $(this).val();
        
        if (!currentFilters.attributes[attributeId]) {
            currentFilters.attributes[attributeId] = [];
        }
        
        if ($(this).prop('checked')) {
            currentFilters.attributes[attributeId].push(valueId);
        } else {
            currentFilters.attributes[attributeId] = currentFilters.attributes[attributeId].filter(id => id !== valueId);
            if (currentFilters.attributes[attributeId].length === 0) {
                delete currentFilters.attributes[attributeId];
            }
        }
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    // Price filters
    $('#price-min, #price-max').on('input', function() {
        clearTimeout(ajaxTimeout);
        currentFilters.price_min = $('#price-min').val();
        currentFilters.price_max = $('#price-max').val();
        
        $('#current-min').text(currentFilters.price_min || '0');
        $('#current-max').text(currentFilters.price_max || '∞');
        
        ajaxTimeout = setTimeout(() => {
            currentFilters.page = 1;
            loadProducts();
            updateActiveFilters();
        }, 800);
    });
    
    // Sort options
    $('.sort-option').click(function(e) {
        e.preventDefault();
        currentFilters.sort = $(this).data('sort');
        currentFilters.page = 1;
        $('#sortDropdown').text($(this).text());
        loadProducts();
    });
    
    // Per page
    $('#per-page').change(function() {
        currentFilters.per_page = $(this).val();
        currentFilters.page = 1;
        loadProducts();
    });
    
    // View toggle
    $('.view-toggle').click(function() {
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        currentView = $(this).data('view');
        $('#products-container').removeClass('products-grid products-list').addClass('products-' + currentView);
    });
    
    // Clear individual filters
    $('#clear-search').click(function() {
        $('#search-input').val('');
        currentFilters.search = '';
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    $('#clear-categories').click(function() {
        $('.category-filter').prop('checked', false);
        currentFilters.categories = [];
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    $('#clear-brands').click(function() {
        $('.brand-filter').prop('checked', false);
        currentFilters.brands = [];
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    $('#clear-price').click(function() {
        $('#price-min, #price-max').val('');
        currentFilters.price_min = '';
        currentFilters.price_max = '';
        $('#current-min').text('0');
        $('#current-max').text('∞');
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    // Clear attribute filters
    $('[data-clear-attribute]').click(function() {
        const attributeId = $(this).data('clear-attribute');
        $(`.attribute-filter[data-attribute="${attributeId}"]`).prop('checked', false);
        delete currentFilters.attributes[attributeId];
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
    
    // Reset all filters
    $('#reset-all-filters').click(function() {
        // Reset form elements
        $('#search-input').val('');
        $('.category-filter, .brand-filter, .attribute-filter').prop('checked', false);
        $('#price-min, #price-max').val('');
        $('#current-min').text('0');
        $('#current-max').text('∞');
        
        // Reset filters object
        currentFilters = {
            categories: [],
            brands: [],
            attributes: {},
            price_min: '',
            price_max: '',
            search: '',
            sort: 'latest',
            per_page: 12,
            page: 1
        };
        
        loadProducts();
        updateActiveFilters();
    });
    
    // Pagination handling
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const page = new URL(url).searchParams.get('page');
        if (page) {
            currentFilters.page = page;
            loadProducts();
            $('html, body').animate({
                scrollTop: $('#products-container').offset().top - 100
            }, 500);
        }
    });
    
    function loadProducts() {
        $('#products-loading').show();
        $('#products-container').addClass('opacity-50');
        
        $.ajax({
            url: '{{ route("shop.products") }}',
            type: 'GET',
            data: currentFilters,
            success: function(response) {
                if (response.success) {
                    $('#products-container').html(response.html).removeClass('opacity-50').addClass('products-' + currentView);
                    $('#pagination-container').html(response.pagination);
                    
                    // Update products count
                    const showing = response.showing;
                    $('#products-showing').text(
                        `Showing ${showing.from}-${showing.to} of ${showing.total} products`
                    );
                }
            },
            error: function() {
                $('#products-container').html('<div class="alert alert-danger">Error loading products. Please try again.</div>').removeClass('opacity-50');
            },
            complete: function() {
                $('#products-loading').hide();
            }
        });
    }
    
    function updateActiveFilters() {
        let activeFiltersHtml = '';
        let hasActiveFilters = false;
        
        // Search filter
        if (currentFilters.search) {
            activeFiltersHtml += `<span class="active-filter-tag">Search: "${currentFilters.search}" <span class="remove-filter" data-remove="search">×</span></span>`;
            hasActiveFilters = true;
        }
        
        // Category filters
        currentFilters.categories.forEach(categoryId => {
            const categoryName = $(`.category-filter[value="${categoryId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Category: ${categoryName} <span class="remove-filter" data-remove="category" data-value="${categoryId}">×</span></span>`;
            hasActiveFilters = true;
        });
        
        // Brand filters
        currentFilters.brands.forEach(brandId => {
            const brandName = $(`.brand-filter[value="${brandId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Brand: ${brandName} <span class="remove-filter" data-remove="brand" data-value="${brandId}">×</span></span>`;
            hasActiveFilters = true;
        });
        
        // Attribute filters
        Object.keys(currentFilters.attributes).forEach(attributeId => {
            currentFilters.attributes[attributeId].forEach(valueId => {
                const attributeName = $(`.attribute-filter[data-attribute="${attributeId}"][value="${valueId}"]`).closest('.card').find('.card-title').text().replace(/.*\s/, '');
                const valueName = $(`.attribute-filter[data-attribute="${attributeId}"][value="${valueId}"]`).next('label').find('span:first').text();
                activeFiltersHtml += `<span class="active-filter-tag">${attributeName}: ${valueName} <span class="remove-filter" data-remove="attribute" data-attribute="${attributeId}" data-value="${valueId}">×</span></span>`;
                hasActiveFilters = true;
            });
        });
        
        // Price filter
        if (currentFilters.price_min || currentFilters.price_max) {
            const min = currentFilters.price_min || '0';
            const max = currentFilters.price_max || '∞';
            activeFiltersHtml += `<span class="active-filter-tag">Price: ₹${min} - ₹${max} <span class="remove-filter" data-remove="price">×</span></span>`;
            hasActiveFilters = true;
        }
        
        if (hasActiveFilters) {
            $('#active-filters-list').html(activeFiltersHtml);
            $('#active-filters').show();
        } else {
            $('#active-filters').hide();
        }
    }
    
    // Remove individual active filters
    $(document).on('click', '.remove-filter', function() {
        const removeType = $(this).data('remove');
        
        switch (removeType) {
            case 'search':
                $('#search-input').val('');
                currentFilters.search = '';
                break;
            case 'category':
                const categoryId = $(this).data('value');
                $(`.category-filter[value="${categoryId}"]`).prop('checked', false);
                currentFilters.categories = currentFilters.categories.filter(id => id !== categoryId);
                break;
            case 'brand':
                const brandId = $(this).data('value');
                $(`.brand-filter[value="${brandId}"]`).prop('checked', false);
                currentFilters.brands = currentFilters.brands.filter(id => id !== brandId);
                break;
            case 'attribute':
                const attributeId = $(this).data('attribute');
                const valueId = $(this).data('value');
                $(`.attribute-filter[data-attribute="${attributeId}"][value="${valueId}"]`).prop('checked', false);
                currentFilters.attributes[attributeId] = currentFilters.attributes[attributeId].filter(id => id !== valueId);
                if (currentFilters.attributes[attributeId].length === 0) {
                    delete currentFilters.attributes[attributeId];
                }
                break;
            case 'price':
                $('#price-min, #price-max').val('');
                currentFilters.price_min = '';
                currentFilters.price_max = '';
                $('#current-min').text('0');
                $('#current-max').text('∞');
                break;
        }
        
        currentFilters.page = 1;
        loadProducts();
        updateActiveFilters();
    });
});
</script>
@endpush
