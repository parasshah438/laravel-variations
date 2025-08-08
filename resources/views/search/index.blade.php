@extends('layouts.app')

@section('title', 'Search Results' . (request('q') ? ' for "' . request('q') . '"' : ''))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Advanced Filters Sidebar -->
        <div class="col-lg-3 col-md-4">
            <div class="search-filters sticky-top" style="top: 80px;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-funnel me-2"></i>Filters
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-filters">
                                Clear All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('search.partials.filters')
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="col-lg-9 col-md-8">
            <!-- Search Header -->
            <div class="search-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        @if(request('visual'))
                            <!-- Visual Search Results Header -->
                            <div class="visual-search-indicator mb-3">
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <i class="bi bi-camera me-2"></i>
                                    <span>Results from visual search</span>
                                </div>
                            </div>
                        @endif
                        
                        @if($results['query_info']['has_results'])
                            <h4 class="mb-1">
                                {{ number_format($results['query_info']['total_results']) }} 
                                {{ Str::plural('result', $results['query_info']['total_results']) }}
                                @if($results['query_info']['query'])
                                    for "<strong>{{ $results['query_info']['query'] }}</strong>"
                                @endif
                                @if(request('visual'))
                                    <small class="text-muted">(Visual Search)</small>
                                @endif
                            </h4>
                            <small class="text-muted">
                                Search completed in {{ number_format($results['query_info']['execution_time'], 3) }}s
                            </small>
                        @else
                            <h4 class="mb-1">No results found</h4>
                            @if($results['query_info']['query'])
                                <p class="text-muted">
                                    No results found for "<strong>{{ $results['query_info']['query'] }}</strong>"
                                </p>
                            @endif
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center">
                            <!-- Sort Options -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                        id="sortDropdown" data-bs-toggle="dropdown">
                                    <i class="bi bi-sort-down me-2"></i>Sort by
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="relevance">Relevance</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="price_low">Price: Low to High</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="price_high">Price: High to Low</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="newest">Newest First</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="name_asc">Name: A to Z</a></li>
                                    <li><a class="dropdown-item sort-option" href="#" data-sort="popular">Most Popular</a></li>
                                </ul>
                            </div>
                            
                            <!-- View Options -->
                            <div class="btn-group ms-2" role="group">
                                <input type="radio" class="btn-check" name="view-mode" id="grid-view" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary" for="grid-view">
                                    <i class="bi bi-grid"></i>
                                </label>

                                <input type="radio" class="btn-check" name="view-mode" id="list-view" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="list-view">
                                    <i class="bi bi-list"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div class="active-filters mt-3" id="active-filters" style="display: none;">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <small class="text-muted me-2">Applied filters:</small>
                        <div id="filter-tags"></div>
                    </div>
                </div>
            </div>

            <!-- Search Results Container -->
            <div id="search-results">
                @include('search.partials.results', $results)
            </div>

            <!-- Pagination -->
            <div class="pagination-container mt-4" id="pagination-container">
                {{ $results['products']->appends(request()->all())->links() }}
            </div>
        </div>
    </div>

    <!-- Trending Searches Modal -->
    @if(!request('q'))
    <div class="trending-searches mt-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-fire me-2 text-danger"></i>Trending Searches
                </h5>
                <div class="trending-tags">
                    @foreach($trendingSearches as $trend)
                        <a href="{{ route('search', ['q' => $trend]) }}" 
                           class="badge bg-light text-dark text-decoration-none me-2 mb-2 p-2">
                            {{ $trend }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Search Loading Overlay -->
<div id="search-loading" class="position-fixed top-0 start-0 w-100 h-100 d-none" 
     style="background: rgba(255,255,255,0.8); z-index: 9999;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mb-0">Searching...</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.search-filters {
    max-height: 80vh;
    overflow-y: auto;
}

.filter-group {
    border-bottom: 1px solid #eee;
    padding: 1rem 0;
}

.filter-group:last-child {
    border-bottom: none;
}

.filter-option {
    transition: all 0.2s ease;
}

.filter-option:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

.price-range-slider {
    margin: 1rem 0;
}

.search-suggestion {
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s ease;
}

.search-suggestion:hover,
.search-suggestion.active {
    background-color: #f8f9fa;
}

.search-suggestion:last-child {
    border-bottom: none;
}

.active-filters .badge {
    font-size: 0.875rem;
}

.trending-tags a {
    transition: all 0.2s ease;
}

.trending-tags a:hover {
    background-color: #e9ecef !important;
    transform: translateY(-1px);
}

.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.list-view .product-card {
    flex-direction: row;
}

.list-view .product-image {
    max-width: 200px;
}

.list-view .card-body {
    flex: 1;
}

/* Custom scrollbar for filter sidebar */
.search-filters::-webkit-scrollbar {
    width: 6px;
}

.search-filters::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.search-filters::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.search-filters::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .search-filters {
        position: relative !important;
        max-height: none;
        margin-bottom: 2rem;
    }
    
    .filter-collapse {
        display: block !important;
    }
    
    .search-header .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Ensure jQuery is loaded before running our code
(function() {
    function initSearchPage() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        
        var $ = jQuery; // Ensure $ is available
        
        $(document).ready(function() {
            
            let currentFilters = {
                q: '{{ request("q") }}',
                sort: '{{ request("sort", "relevance") }}',
                categories: {!! json_encode(request('categories', [])) !!},
                brands: {!! json_encode(request('brands', [])) !!},
                attributes: {!! json_encode(request('attributes', [])) !!},
                price_min: {{ request('price_min') ? request('price_min') : 'null' }},
                price_max: {{ request('price_max') ? request('price_max') : 'null' }},
                in_stock: {{ request('in_stock') ? 'true' : 'false' }}
            };

            let searchTimeout;
            let isSearching = false;

            // Handle visual search results from session storage
            if (sessionStorage.getItem('visualSearchResults') && '{{ request("visual") }}' === '1') {
                try {
                    const visualResults = JSON.parse(sessionStorage.getItem('visualSearchResults'));
                    
                    if (visualResults && visualResults.length > 0) {
                        displayVisualSearchResults(visualResults);
                    }
                } catch (e) {
                    // Error parsing visual search results
                }
                
                // Clear session storage after use
                sessionStorage.removeItem('visualSearchResults');
            } else {
                console.log('No visual search results in storage or not visual search page');
                console.log('Visual search results exist:', !!sessionStorage.getItem('visualSearchResults'));
                console.log('Is visual search page:', '{{ request("visual") }}' === '1');
            }

            // Initialize page functions with safety checks
            if (typeof updateActiveFilters === 'function') {
                updateActiveFilters();
            } else {
                console.warn('updateActiveFilters function not found');
            }
            
            if (typeof initializeSliders === 'function') {
                initializeSliders();
            } else {
                console.warn('initializeSliders function not found');
            }
        });
        
        // Define functions in the global scope so they're available
        window.displayVisualSearchResults = function(results) {
            console.log('displayVisualSearchResults called with:', results);
            
            var $ = jQuery;
            let html = '<div class="products-grid row g-4" id="products-grid">';
            
            results.forEach(product => {
                html += `
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 product-item">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="position-relative product-image">
                                ${product.image ? `
                                    <img src="${product.image}" class="card-img-top" alt="${product.name}" 
                                         style="height: 250px; object-fit: cover;">
                                ` : `
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 250px;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                `}
                                
                                <!-- Visual Search Badge -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-info">
                                        <i class="bi bi-camera me-1"></i>Visual Match
                                    </span>
                                    ${product.similarity_score ? `
                                        <span class="badge bg-success ms-1">
                                            ${Math.round(product.similarity_score * 100)}% Match
                                        </span>
                                    ` : ''}
                                </div>
                                
                                <!-- Quick actions -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-sm btn-light wishlist-btn" 
                                                data-product-id="${product.id}" title="Add to Wishlist">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light quick-view-btn" 
                                                data-product-id="${product.id}" title="Quick View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Category and Brand -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    ${product.category ? `<small class="text-muted">${product.category}</small>` : ''}
                                    ${product.brand ? `<small class="text-primary fw-bold">${product.brand}</small>` : ''}
                                </div>
                                
                                <!-- Product Title -->
                                <h6 class="card-title mb-2">
                                    <a href="${product.url || '/product/' + product.slug}" class="text-decoration-none text-dark product-title">
                                        ${product.name}
                                    </a>
                                </h6>
                                
                                <!-- Price -->
                                <div class="price-section mb-3">
                                    <h6 class="text-primary mb-0">₹${parseFloat(product.price || 0).toLocaleString()}</h6>
                                    ${product.sale_price && product.sale_price < product.price ? `
                                        <small class="text-muted text-decoration-line-through">
                                            ₹${parseFloat(product.sale_price).toLocaleString()}
                                        </small>
                                    ` : ''}
                                </div>
                                
                                <!-- Action Button -->
                                <div class="d-grid">
                                    <a href="${product.url || '/product/' + product.slug}" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-2"></i>View Product
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            // Update search results container
            var searchResultsContainer = $('#search-results');
            if (searchResultsContainer.length) {
                searchResultsContainer.html(html);
                console.log('Updated search results container');
            } else {
                console.error('Search results container not found');
            }
            
            // Update header to show visual search results count
            var headerElement = $('.search-header h4');
            if (headerElement.length) {
                headerElement.html(`
                    ${results.length} ${results.length === 1 ? 'result' : 'results'} from visual search
                    <small class="text-muted">(Visual Search)</small>
                `);
                console.log('Updated header');
            }
        };
    }
    
    // Try to initialize immediately if jQuery is already loaded
    if (typeof jQuery !== 'undefined') {
        initSearchPage();
    } else {
        // Wait for jQuery to load
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                initSearchPage();
            }
        }, 100);
        
        // Fallback timeout after 5 seconds
        setTimeout(function() {
            clearInterval(checkJQuery);
            console.error('jQuery failed to load within 5 seconds');
        }, 5000);
    }
})();
</script>
@endpush
