
@extends('layouts.app')

@section('title', 'Shop - New Collection')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Left Sidebar - Filters -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="filters-sidebar bg-white p-4 rounded shadow-sm">
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
                                       placeholder="Search products..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Category Filter -->
                @if($categories->isNotEmpty())
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
                                        <span>{{ $category->category_name }}</span>
                                        <span class="badge bg-light text-dark category-count" 
                                              data-category="{{ $category->id }}">{{ $category->products_count ?? 0 }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Collection Filter -->
                @if($allCollections->isNotEmpty())
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-collection me-2"></i>Collections
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-collections">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($allCollections as $collection)
                                <div class="form-check mb-2">
                                    <input class="form-check-input collection-filter" type="checkbox" 
                                           value="{{ $collection->id }}" id="col_{{ $collection->id }}">
                                    <label class="form-check-label d-flex justify-content-between" 
                                           for="col_{{ $collection->id }}">
                                        <span>{{ $collection->name }}</span>
                                        <span class="badge bg-light text-dark collection-count" 
                                              data-collection="{{ $collection->id }}">{{ $collection->products_count ?? 0 }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Diamond Clarity Filter -->
                @if($diamondclaritycolors->isNotEmpty())
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-gem me-2"></i>Diamond Clarity
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-clarity">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($diamondclaritycolors as $clarity)
                                <div class="form-check mb-2">
                                    <input class="form-check-input clarity-filter" type="checkbox" 
                                           value="{{ $clarity->id }}" id="clarity_{{ $clarity->id }}">
                                    <label class="form-check-label d-flex justify-content-between" 
                                           for="clarity_{{ $clarity->id }}">
                                        <span>{{ $clarity->clarity_name }}</span>
                                        <span class="badge bg-light text-dark clarity-count" 
                                              data-clarity="{{ $clarity->id }}">{{ $clarity->products_count ?? 0 }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Diamond Shape Filter -->
                @if($diamondshapes->isNotEmpty())
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-diamond me-2"></i>Diamond Shapes
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-shapes">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($diamondshapes as $shape)
                                <div class="form-check mb-2">
                                    <input class="form-check-input shape-filter" type="checkbox" 
                                           value="{{ $shape->id }}" id="shape_{{ $shape->id }}">
                                    <label class="form-check-label d-flex justify-content-between" 
                                           for="shape_{{ $shape->id }}">
                                        <span>{{ $shape->shape_name }}</span>
                                        <span class="badge bg-light text-dark shape-count" 
                                              data-shape="{{ $shape->id }}">{{ $shape->products_count ?? 0 }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Gold Quality Filter -->
                @if($gold_qualities->isNotEmpty())
                <div class="filter-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-award me-2"></i>Gold Quality
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                        id="clear-qualities">Clear</button>
                            </div>
                            <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                @foreach($gold_qualities as $quality)
                                <div class="form-check mb-2">
                                    <input class="form-check-input quality-filter" type="checkbox" 
                                           value="{{ $quality->id }}" id="quality_{{ $quality->id }}">
                                    <label class="form-check-label d-flex justify-content-between" 
                                           for="quality_{{ $quality->id }}">
                                        <span>{{ $quality->quality_name }}</span>
                                        <span class="badge bg-light text-dark quality-count" 
                                              data-quality="{{ $quality->id }}">{{ $quality->products_count ?? 0 }}</span>
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
                            <div class="price-range-container">
                                <div id="price-range-slider" class="mb-3"></div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <input type="number" id="min_price_input" class="form-control form-control-sm" 
                                               placeholder="Min" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" id="max_price_input" class="form-control form-control-sm" 
                                               placeholder="Max" min="0">
                                    </div>
                                </div>
                                <div class="range-values text-center small text-muted">
                                    ₹<span id="current-min">0</span> - 
                                    ₹<span id="current-max">200000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="filter-actions mb-4">
                    <button id="apply-filters" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Apply Filters
                    </button>
                    <button id="clear-all-filters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i>Clear All Filters
                    </button>
                </div>

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
            <!-- Search and Sort Bar -->
            <div class="search-sort-bar bg-white p-3 mb-4 rounded shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-box">
                            <input type="text" id="search-input" class="form-control" 
                                   placeholder="Search products..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sort-options d-flex align-items-center justify-content-md-end">
                            <label class="me-2 mb-0">Sort by:</label>
                            <select id="sort-select" class="form-select form-select-sm" style="width: auto;">
                                <option value="default">Default</option>
                                <option value="name_asc">Name A-Z</option>
                                <option value="name_desc">Name Z-A</option>
                                <option value="price_asc">Price Low to High</option>
                                <option value="price_desc">Price High to Low</option>
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="rating">Highest Rated</option>
                                <option value="popular">Most Popular</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters -->
            <div id="active-filters" class="mb-3" style="display: none;">
                <div class="bg-light p-2 rounded">
                    <span class="me-2">Active Filters:</span>
                    <div id="filter-tags" class="d-inline-flex flex-wrap gap-1"></div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <div id="products-loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading products...</p>
                </div>

                <div id="product-results">
                    @if(count($all_products))
                        <div class="row" id="product-list">
                            @include('frontend.partials.product_grid', [
                                'all_products' => $all_products
                            ])
                        </div>
                        
                        <!-- Auto Scroll Loading -->
                        @if($all_products->hasMorePages())
                            <div id="load-more-trigger" style="height: 1px;"></div>
                            <div id="load-more-spinner" class="text-center py-4" style="display: none;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading more products...</p>
                            </div>
                            <div id="load-more-end" class="text-center py-4" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-check-circle me-2"></i>All products loaded
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="no-products text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>No Products Found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickviewmodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="quickviewmodal">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.4.0/dist/nouislider.min.css">
<style>
.filters-sidebar {
    max-height: 80vh;
    overflow-y: auto;
}

.filters-sidebar .card {
    transition: all 0.2s ease;
}

.filters-sidebar .card:hover {
    transform: translateY(-2px);
}

.filter-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.filter-section:last-child {
    border-bottom: none;
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

.form-check-input:checked {
    background-color: #3498db;
    border-color: #3498db;
}

.price-range-container .noUi-connect {
    background: linear-gradient(45deg, #3498db, #2980b9);
}

.price-range-container .noUi-handle {
    background: #fff;
    border: 2px solid #3498db;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-sort-bar {
    border-left: 4px solid #3498db;
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
    background: none;
    border: none;
    font-size: 14px;
}

.active-filter-tag .remove-filter:hover {
    color: #d32f2f;
}

.filter-tag {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin: 2px;
}

.filter-tag .remove-filter {
    background: rgba(255,255,255,0.3);
    border: none;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    cursor: pointer;
}

.no-products i {
    opacity: 0.3;
}

@media (max-width: 768px) {
    .filters-sidebar {
        margin-bottom: 2rem;
    }
    
    .search-sort-bar .row > div {
        margin-bottom: 1rem;
    }
    
    .search-sort-bar .row > div:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@15.4.0/dist/nouislider.min.js"></script>
<script>
$(document).ready(function() {
    let currentFilters = {
        categories: [],
        collections: [],
        clarity: [],
        shapes: [],
        qualities: [],
        min_price: '',
        max_price: '',
        search: '',
        sort: 'default',
        page: 1
    };
    
    let ajaxTimeout;
    let isFiltering = false;
    let totalPages = {{ $all_products->lastPage() }};

    // Initialize price range slider
    const priceSlider = document.getElementById('price-range-slider');
    if (priceSlider) {
        noUiSlider.create(priceSlider, {
            start: [0, 200000],
            connect: true,
            step: 100,
            range: {
                'min': 0,
                'max': 200000
            },
            format: {
                to: value => parseInt(value),
                from: value => parseInt(value)
            }
        });

        const minInput = document.getElementById('min_price_input');
        const maxInput = document.getElementById('max_price_input');

        // Update input when slider moves
        priceSlider.noUiSlider.on('update', function (values, handle) {
            if (handle === 0) {
                minInput.value = values[0];
                $('#current-min').text(values[0]);
            } else {
                maxInput.value = values[1];
                $('#current-max').text(values[1]);
            }
        });

        // Update slider when input changes
        minInput.addEventListener('change', function () {
            priceSlider.noUiSlider.set([this.value, null]);
        });

        maxInput.addEventListener('change', function () {
            priceSlider.noUiSlider.set([null, this.value]);
        });
    }

    // Search input with debounce
    $('#search-input').on('input', function() {
        clearTimeout(ajaxTimeout);
        currentFilters.search = $(this).val();
        ajaxTimeout = setTimeout(() => {
            resetAndFilter();
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
        resetAndFilter();
    });

    // Collection filters
    $('.collection-filter').change(function() {
        const collectionId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.collections.push(collectionId);
        } else {
            currentFilters.collections = currentFilters.collections.filter(id => id !== collectionId);
        }
        resetAndFilter();
    });

    // Clarity filters
    $('.clarity-filter').change(function() {
        const clarityId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.clarity.push(clarityId);
        } else {
            currentFilters.clarity = currentFilters.clarity.filter(id => id !== clarityId);
        }
        resetAndFilter();
    });

    // Shape filters
    $('.shape-filter').change(function() {
        const shapeId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.shapes.push(shapeId);
        } else {
            currentFilters.shapes = currentFilters.shapes.filter(id => id !== shapeId);
        }
        resetAndFilter();
    });

    // Quality filters
    $('.quality-filter').change(function() {
        const qualityId = $(this).val();
        if ($(this).prop('checked')) {
            currentFilters.qualities.push(qualityId);
        } else {
            currentFilters.qualities = currentFilters.qualities.filter(id => id !== qualityId);
        }
        resetAndFilter();
    });

    // Price filters
    $('#min_price_input, #max_price_input').on('input', function() {
        clearTimeout(ajaxTimeout);
        currentFilters.min_price = $('#min_price_input').val();
        currentFilters.max_price = $('#max_price_input').val();
        
        ajaxTimeout = setTimeout(() => {
            resetAndFilter();
        }, 800);
    });

    // Sort select
    $('#sort-select').change(function() {
        currentFilters.sort = $(this).val();
        resetAndFilter();
    });

    // Clear individual filters
    $('#clear-search').click(function() {
        $('#search-input').val('');
        currentFilters.search = '';
        resetAndFilter();
    });

    $('#clear-categories').click(function() {
        $('.category-filter').prop('checked', false);
        currentFilters.categories = [];
        resetAndFilter();
    });

    $('#clear-collections').click(function() {
        $('.collection-filter').prop('checked', false);
        currentFilters.collections = [];
        resetAndFilter();
    });

    $('#clear-clarity').click(function() {
        $('.clarity-filter').prop('checked', false);
        currentFilters.clarity = [];
        resetAndFilter();
    });

    $('#clear-shapes').click(function() {
        $('.shape-filter').prop('checked', false);
        currentFilters.shapes = [];
        resetAndFilter();
    });

    $('#clear-qualities').click(function() {
        $('.quality-filter').prop('checked', false);
        currentFilters.qualities = [];
        resetAndFilter();
    });

    $('#clear-price').click(function() {
        $('#min_price_input, #max_price_input').val('');
        currentFilters.min_price = '';
        currentFilters.max_price = '';
        $('#current-min').text('0');
        $('#current-max').text('200000');
        if (priceSlider && priceSlider.noUiSlider) {
            priceSlider.noUiSlider.set([0, 200000]);
        }
        resetAndFilter();
    });

    // Reset all filters
    $('#reset-all-filters, #clear-all-filters').click(function() {
        // Reset form elements
        $('#search-input').val('');
        $('.category-filter, .collection-filter, .clarity-filter, .shape-filter, .quality-filter').prop('checked', false);
        $('#min_price_input, #max_price_input').val('');
        $('#sort-select').val('default');
        $('#current-min').text('0');
        $('#current-max').text('200000');
        
        // Reset price slider
        if (priceSlider && priceSlider.noUiSlider) {
            priceSlider.noUiSlider.set([0, 200000]);
        }
        
        // Reset filters object
        currentFilters = {
            categories: [],
            collections: [],
            clarity: [],
            shapes: [],
            qualities: [],
            min_price: '',
            max_price: '',
            search: '',
            sort: 'default',
            page: 1
        };
        
        resetAndFilter();
    });

    // Reset page and apply filters (for new filter selections)
    function resetAndFilter() {
        currentFilters.page = 1;
        $('#product-list').empty();
        $('#load-more-end').hide();
        applyFilters();
        updateActiveFilters();
        initializeInfiniteScroll();
    }

    // Apply filters function
    function applyFilters() {
        if (isFiltering) return; // Prevent multiple simultaneous requests
        
        isFiltering = true;
        $('#products-loading').show();
        if (currentFilters.page === 1) {
            $('#product-results').hide();
        }

        $.ajax({
            url: '{{ route("shop.newShopPage") }}',
            type: 'GET',
            data: currentFilters,
            success: function(response) {
                if (response.success) {
                    if (currentFilters.page === 1) {
                        // First page - replace content
                        $('#product-list').html(response.html);
                    } else {
                        // Additional pages - append content
                        $('#product-list').append(response.html);
                    }
                    
                    // Update pagination info
                    totalPages = response.last_page || 1;
                    
                    // Show/hide load more trigger
                    if (currentFilters.page >= totalPages) {
                        $('#load-more-trigger').hide();
                        $('#load-more-end').show();
                    } else {
                        $('#load-more-trigger').show();
                        $('#load-more-end').hide();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Filter error:', error);
                if (window.$ && $.toast) {
                    $.toast({
                        heading: 'Error',
                        text: 'Failed to load products. Please try again.',
                        showHideTransition: 'slide',
                        icon: 'error',
                        position: 'top-right'
                    });
                }
            },
            complete: function() {
                $('#products-loading').hide();
                $('#product-results').show();
                $('#load-more-spinner').hide();
                isFiltering = false;
            }
        });
    }

    // Load more products (for infinite scroll)
    function loadMoreProducts() {
        if (isFiltering || currentFilters.page >= totalPages) return;
        
        currentFilters.page++;
        $('#load-more-spinner').show();
        applyFilters();
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
        
        // Collection filters
        currentFilters.collections.forEach(collectionId => {
            const collectionName = $(`.collection-filter[value="${collectionId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Collection: ${collectionName} <span class="remove-filter" data-remove="collection" data-value="${collectionId}">×</span></span>`;
            hasActiveFilters = true;
        });

        // Clarity filters
        currentFilters.clarity.forEach(clarityId => {
            const clarityName = $(`.clarity-filter[value="${clarityId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Clarity: ${clarityName} <span class="remove-filter" data-remove="clarity" data-value="${clarityId}">×</span></span>`;
            hasActiveFilters = true;
        });

        // Shape filters
        currentFilters.shapes.forEach(shapeId => {
            const shapeName = $(`.shape-filter[value="${shapeId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Shape: ${shapeName} <span class="remove-filter" data-remove="shape" data-value="${shapeId}">×</span></span>`;
            hasActiveFilters = true;
        });

        // Quality filters
        currentFilters.qualities.forEach(qualityId => {
            const qualityName = $(`.quality-filter[value="${qualityId}"]`).next('label').find('span:first').text();
            activeFiltersHtml += `<span class="active-filter-tag">Quality: ${qualityName} <span class="remove-filter" data-remove="quality" data-value="${qualityId}">×</span></span>`;
            hasActiveFilters = true;
        });
        
        // Price filter
        if (currentFilters.min_price || currentFilters.max_price) {
            const min = currentFilters.min_price || '0';
            const max = currentFilters.max_price || '∞';
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
        const value = $(this).data('value');
        
        switch (removeType) {
            case 'search':
                $('#search-input').val('');
                currentFilters.search = '';
                break;
            case 'category':
                $(`.category-filter[value="${value}"]`).prop('checked', false);
                currentFilters.categories = currentFilters.categories.filter(id => id !== value);
                break;
            case 'collection':
                $(`.collection-filter[value="${value}"]`).prop('checked', false);
                currentFilters.collections = currentFilters.collections.filter(id => id !== value);
                break;
            case 'clarity':
                $(`.clarity-filter[value="${value}"]`).prop('checked', false);
                currentFilters.clarity = currentFilters.clarity.filter(id => id !== value);
                break;
            case 'shape':
                $(`.shape-filter[value="${value}"]`).prop('checked', false);
                currentFilters.shapes = currentFilters.shapes.filter(id => id !== value);
                break;
            case 'quality':
                $(`.quality-filter[value="${value}"]`).prop('checked', false);
                currentFilters.qualities = currentFilters.qualities.filter(id => id !== value);
                break;
            case 'price':
                $('#min_price_input, #max_price_input').val('');
                currentFilters.min_price = '';
                currentFilters.max_price = '';
                $('#current-min').text('0');
                $('#current-max').text('200000');
                if (priceSlider && priceSlider.noUiSlider) {
                    priceSlider.noUiSlider.set([0, 200000]);
                }
                break;
        }
        
        resetAndFilter();
    });

    // Initialize infinite scroll
    function initializeInfiniteScroll() {
        const loadMoreTrigger = document.getElementById('load-more-trigger');
        if (!loadMoreTrigger) return;

        // Disconnect existing observer if any
        if (window.scrollObserver) {
            window.scrollObserver.disconnect();
        }

        // Create new intersection observer
        window.scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isFiltering && currentFilters.page < totalPages) {
                    loadMoreProducts();
                }
            });
        }, {
            rootMargin: '100px 0px', // Start loading 100px before reaching the trigger
            threshold: 0.1
        });

        window.scrollObserver.observe(loadMoreTrigger);
    }

    // Initialize infinite scroll on page load
    initializeInfiniteScroll();
});

// Quick view functionality
$(document).on('click', '.quickview-trigger, .quick-view-btn', function(e) {
    e.preventDefault();
    const productId = $(this).data('product-id') || $(this).data('product_id');
    
    $.ajax({
        url: "/product/quickview",
        data: { product_id: productId },
        type: 'GET',
        dataType: 'html',
        beforeSend: function() {
            $('#quickviewmodal .quickviewmodal').html('<div class="p-4 text-center">Loading...</div>');
        },
        success: function(res) {
            $('#quickviewmodal .quickviewmodal').html(res);
            $('#quickviewmodal').modal('show');
        },
        error: function() {
            $.toast({
                heading: 'Error',
                text: 'Failed to load product details.',
                showHideTransition: 'slide',
                icon: 'error',
                position: 'top-right'
            });
        }
    });
});

// Wishlist functionality
$(document).on('click', '.wishlist_action, .wishlist-btn', function() {
    const $this = $(this);
    const productId = $this.data('product-id') || $this.data('product_id');
    
    $.ajax({
        url: '/wishlist_action',
        type: 'POST',
        data: { 
            product_id: productId,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $this.addClass('active');
                $.toast({
                    heading: 'Success',
                    text: response.message,
                    showHideTransition: 'slide',
                    icon: 'success',
                    loaderBg: '#28a745',
                    position: 'top-right'
                });
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                $.toast({
                    heading: 'Error',
                    text: "Please log in to add this product to your wishlist.",
                    showHideTransition: 'fade',
                    icon: 'error',
                    loaderBg: '#dc3545',
                    position: 'top-right'
                });
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            }
        }
    });
});
</script>
@endpush
@endsection
