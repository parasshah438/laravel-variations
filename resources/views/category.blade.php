@extends('layouts.app')

@section('title', $category->name . ' - E-Commerce Store')

@section('content')
<div class="container">
    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    @if($category->parent)
                        <li class="breadcrumb-item">
                            <a href="{{ route('category.show', $category->parent->slug) }}">
                                {{ $category->parent->name }}
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>
            
            <div class="d-flex align-items-center mb-3">
                @if($category->icon)
                    <i class="{{ $category->icon }} me-3 fs-1 text-primary"></i>
                @endif
                <div>
                    <h1>{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="text-muted mb-0">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Child Categories -->
    @if($category->children->count() > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">Shop by Category</h3>
                <div class="row">
                    @foreach($category->children as $child)
                        <div class="col-6 col-md-4 col-lg-3 mb-3">
                            <a href="{{ route('category.show', $child->slug) }}" class="text-decoration-none">
                                <div class="card h-100 text-center border-0 shadow-sm category-card">
                                    <div class="card-body d-flex flex-column">
                                        @if($child->icon)
                                            <i class="{{ $child->icon }} fs-1 text-primary mb-3"></i>
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                                <i class="bi bi-tag fs-4 text-muted"></i>
                                            </div>
                                        @endif
                                        <h6 class="card-title mb-2">{{ $child->name }}</h6>
                                        <small class="text-muted">{{ $child->getAllProducts()->count() }} products</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('category.show', $category->slug) }}" id="filterForm">
                        <div class="row g-3">
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
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select" id="sortFilter">
                                    <option value="">Default</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search in {{ $category->name }}</label>
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
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Products ({{ $products->total() }})</h3>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="gridView">
                        <i class="bi bi-grid"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="listView">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
            
            @if($products->count() > 0)
                <div id="productGrid">
                    @include('partials.product-grid', ['products' => $products])
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="text-muted">No products found in {{ $category->name }}</h3>
                    <p>Try adjusting your search criteria or browse other categories</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">Browse All Products</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.category-card {
    transition: transform 0.2s ease-in-out;
}

.category-card:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form when filters change
    $('#brandFilter, #sortFilter').change(function() {
        $('#filterForm').submit();
    });

    // View toggle
    $('#gridView, #listView').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        
        if ($(this).attr('id') === 'listView') {
            $('#productGrid .col-6, #productGrid .col-md-4, #productGrid .col-lg-3').removeClass('col-6 col-md-4 col-lg-3').addClass('col-12');
            $('#productGrid .card').addClass('mb-3').find('.row').removeClass('row').addClass('d-flex align-items-center');
        } else {
            $('#productGrid .col-12').removeClass('col-12').addClass('col-6 col-md-4 col-lg-3');
            $('#productGrid .card').removeClass('mb-3').find('.d-flex').addClass('row').removeClass('d-flex align-items-center');
        }
    });
});
</script>
@endpush
