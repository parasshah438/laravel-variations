@extends('admin.layout')

@section('title', 'Products Management')
@section('page-title', 'Products Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Products Catalog</h4>
        <p class="text-muted mb-0">Manage your product variations and attributes</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-gradient">
        <i class="bi bi-plus-circle me-2"></i>Add New Product
    </a>
</div>

<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search Products</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by name, SKU...">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="per_page" class="form-label">Per Page</label>
                <select class="form-select" id="per_page" name="per_page">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-box-seam me-2"></i>Products ({{ $products->total() }})
        </h5>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" id="bulk-actions-btn" disabled>
                <i class="bi bi-gear me-1"></i>Bulk Actions
            </button>
            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                    data-bs-toggle="dropdown" disabled>
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="bulkStatusUpdate('active')">
                    <i class="bi bi-check-circle text-success me-2"></i>Activate Selected
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="bulkStatusUpdate('inactive')">
                    <i class="bi bi-x-circle text-warning me-2"></i>Deactivate Selected
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">
                    <i class="bi bi-trash me-2"></i>Delete Selected
                </a></li>
            </ul>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="select-all">
                    </th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Variations</th>
                    <th>Price Range</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" 
                                   value="{{ $product->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" 
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">ID: {{ $product->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($product->category)
                                <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">No Category</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $product->variations->count() }} variations</span>
                            @if($product->variations->count() > 0)
                                <br><small class="text-muted">
                                    @foreach($product->variations->take(2) as $variation)
                                        {{ $variation->variation_name ?: 'Default' }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                    @if($product->variations->count() > 2)
                                        +{{ $product->variations->count() - 2 }} more
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td>
                            @php
                                $minPrice = $product->variations->min('price');
                                $maxPrice = $product->variations->max('price');
                            @endphp
                            @if($minPrice == $maxPrice)
                                <span class="fw-bold">₹{{ number_format($minPrice, 2) }}</span>
                            @else
                                <span class="fw-bold">₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $totalStock = $product->variations->sum('stock');
                                $lowStock = $product->variations->where('stock', '<=', 5)->where('stock', '>', 0)->count();
                                $outOfStock = $product->variations->where('stock', 0)->count();
                            @endphp
                            <div>
                                <span class="fw-bold {{ $totalStock <= 10 ? 'text-warning' : '' }}">
                                    {{ $totalStock }} units
                                </span>
                                @if($lowStock > 0)
                                    <br><small class="text-warning">{{ $lowStock }} low stock</small>
                                @endif
                                @if($outOfStock > 0)
                                    <br><small class="text-danger">{{ $outOfStock }} out of stock</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox" 
                                       data-product-id="{{ $product->id }}"
                                       {{ $product->status == 'active' ? 'checked' : '' }}>
                                <label class="form-check-label small">
                                    {{ ucfirst($product->status) }}
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.products.show', $product->id) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteProduct({{ $product->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-box display-1 text-muted"></i>
                            <p class="text-muted mt-3">No products found</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>Add Your First Product
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($products->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                </div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#select-all').change(function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });
    
    $('.product-checkbox').change(function() {
        toggleBulkActions();
        updateSelectAll();
    });
    
    // Status toggle functionality
    $('.status-toggle').change(function() {
        const productId = $(this).data('product-id');
        const isActive = $(this).prop('checked');
        const toggle = $(this);
        const label = $(this).next('label');
        
        $.ajax({
            url: `/admin/products/${productId}/status`,
            method: 'PATCH',
            data: {
                status: isActive ? 'active' : 'inactive',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                label.text(isActive ? 'Active' : 'Inactive');
                showToast(response.message, 'success');
            },
            error: function() {
                toggle.prop('checked', !isActive);
                showToast('Failed to update product status', 'error');
            }
        });
    });
});

function toggleBulkActions() {
    const checked = $('.product-checkbox:checked').length;
    $('#bulk-actions-btn, .dropdown-toggle-split').prop('disabled', checked === 0);
}

function updateSelectAll() {
    const total = $('.product-checkbox').length;
    const checked = $('.product-checkbox:checked').length;
    $('#select-all').prop('checked', total > 0 && total === checked);
}

function bulkStatusUpdate(status) {
    const selectedIds = $('.product-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select products to update', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'deactivate'} ${selectedIds.length} product(s)?`)) {
        $.ajax({
            url: '{{ route("admin.products.bulk-status") }}',
            method: 'PATCH',
            data: {
                ids: selectedIds,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload();
            },
            error: function() {
                showToast('Failed to update products', 'error');
            }
        });
    }
}

function bulkDelete() {
    const selectedIds = $('.product-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select products to delete', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedIds.length} product(s)? This action cannot be undone.`)) {
        $.ajax({
            url: '{{ route("admin.products.bulk-delete") }}',
            method: 'DELETE',
            data: {
                ids: selectedIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload();
            },
            error: function() {
                showToast('Failed to delete products', 'error');
            }
        });
    }
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        $.ajax({
            url: `/admin/products/${productId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload();
            },
            error: function() {
                showToast('Failed to delete product', 'error');
            }
        });
    }
}
</script>
@endpush
