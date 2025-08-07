@extends('layouts.app')

@section('title', 'Home - E-Commerce Store')

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Slider Section -->
    <x-slider-new :sliders="$sliders" />
</div>

<!-- Search Hero Section -->
<div class="search-hero bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <h2 class="mb-4">Find exactly what you're looking for</h2>
                <p class="text-muted mb-4">Search through thousands of products with our intelligent search</p>
                
                <!-- Advanced Search Component -->
                <div class="search-hero-form">
                    @include('components.advanced-search')
                </div>
                
                <!-- Quick Access Categories -->
                <div class="quick-categories mt-4">
                    <small class="text-muted d-block mb-3">Popular Categories:</small>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @php
                            $popularCategories = $categories->take(6);
                        @endphp
                        @foreach($popularCategories as $category)
                            <a href="{{ route('search', ['categories' => [$category->id]]) }}" 
                               class="btn btn-outline-primary btn-sm">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">{!! "<!-- Filters -->" !!}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('home') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @if($category->children->count() > 0)
                                            @foreach($category->children as $child)
                                                <option value="{{ $child->id }}" {{ request('category') == $child->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;{{ $child->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <select name="brand" class="form-select" id="brandFilter">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="row" id="products">
        <div class="col-12">
            <h2 class="mb-4">Products</h2>
            
            @if($products->count() > 0)
                <div id="productGrid">
                    @include('partials.product-grid', ['products' => $products])
                </div>
                
                @if($products->hasMorePages())
                    <div class="text-center mt-4">
                        <button id="loadMoreBtn" class="btn btn-outline-primary btn-lg" data-page="{{ $products->currentPage() + 1 }}">
                            <i class="bi bi-arrow-down-circle"></i> Load More Products
                        </button>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="text-muted">No products found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form when filters change
    $('#categoryFilter, #brandFilter').change(function() {
        $('#filterForm').submit();
    });

    // Load more products
    let loading = false;
    $('#loadMoreBtn').click(function() {
        if (loading) return;
        
        loading = true;
        const btn = $(this);
        const page = btn.data('page');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
        
        $.get('{{ route("products.load-more") }}', {
            page: page,
            search: '{{ request("search") }}',
            category: '{{ request("category") }}',
            brand: '{{ request("brand") }}'
        })
        .done(function(response) {
            $('#productGrid .row').append(response.html);
            
            if (response.hasMore) {
                btn.data('page', page + 1);
                btn.prop('disabled', false).html('<i class="bi bi-arrow-down-circle"></i> Load More Products');
            } else {
                btn.remove();
            }
        })
        .fail(function() {
            showToast('Failed to load more products', 'danger');
            btn.prop('disabled', false).html('<i class="bi bi-arrow-down-circle"></i> Load More Products');
        })
        .always(function() {
            loading = false;
        });
    });
});
</script>
@endpush
