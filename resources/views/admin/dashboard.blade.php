@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-bold">Total Products</div>
                        <div class="h3 mb-0">{{ number_format($stats['total_products']) }}</div>
                    </div>
                    <div class="display-6 opacity-75">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-bold">Total Orders</div>
                        <div class="h3 mb-0">{{ number_format($stats['total_orders']) }}</div>
                    </div>
                    <div class="display-6 opacity-75">
                        <i class="bi bi-bag-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-bold">Total Revenue</div>
                        <div class="h3 mb-0">₹{{ number_format($stats['total_revenue'], 2) }}</div>
                    </div>
                    <div class="display-6 opacity-75">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-bold">Total Users</div>
                        <div class="h3 mb-0">{{ number_format($stats['total_users']) }}</div>
                    </div>
                    <div class="display-6 opacity-75">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Orders
                </h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                                               class="text-decoration-none fw-bold">
                                                #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle me-2" 
                                                     style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                    {{ strtoupper(substr($order->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $order->user->name }}</div>
                                                    <small class="text-muted">{{ $order->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $order->items->count() }} items</td>
                                        <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                                        <td>
                                            <span class="badge badge-status bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $order->created_at->format('M j, Y') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-2">No orders yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats & Low Stock -->
    <div class="col-lg-4 mb-4">
        <!-- Quick Actions -->
        <div class="card admin-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-gradient">
                        <i class="bi bi-plus-circle me-2"></i>Add New Product
                    </a>
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                        <i class="bi bi-clock me-2"></i>Pending Orders ({{ $stats['pending_orders'] }})
                    </a>
                    <a href="{{ route('admin.products.index', ['status' => 'inactive']) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-eye-slash me-2"></i>Inactive Products
                    </a>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Alert
                </h5>
            </div>
            <div class="card-body">
                @if($lowStockProducts->count() > 0)
                    @foreach($lowStockProducts as $product)
                        @foreach($product->variations->where('stock', '<=', 5)->where('stock', '>', 0) as $variation)
                            <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-2' : '' }}">
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $variation->variation_name ?: 'Default' }}</small>
                                </div>
                                <span class="badge bg-{{ $variation->stock <= 2 ? 'danger' : 'warning' }}">
                                    {{ $variation->stock }} left
                                </span>
                            </div>
                            @if(!$loop->last && $loop->parent->last)
                                <hr class="my-2">
                            @endif
                        @endforeach
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success display-4"></i>
                        <p class="text-muted mt-2 mb-0">All products are well stocked!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Performance -->
<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Monthly Performance {{ date('Y') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-box display-4 text-primary"></i>
                            <h4 class="mt-2">{{ $stats['active_products'] }}</h4>
                            <small class="text-muted">Active Products</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-tags display-4 text-success"></i>
                            <h4 class="mt-2">{{ $stats['total_categories'] }}</h4>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-currency-rupee display-4 text-warning"></i>
                            <h4 class="mt-2">₹{{ number_format($stats['monthly_revenue'], 0) }}</h4>
                            <small class="text-muted">This Month Revenue</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-clock display-4 text-info"></i>
                            <h4 class="mt-2">{{ $stats['pending_orders'] }}</h4>
                            <small class="text-muted">Pending Orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);

    // Status update notifications
    $(document).ready(function() {
        // Show welcome message if first time
        if (localStorage.getItem('admin_welcome') !== 'shown') {
            showToast('Welcome to the Admin Panel! 🎉', 'success');
            localStorage.setItem('admin_welcome', 'shown');
        }
    });
</script>
@endpush
