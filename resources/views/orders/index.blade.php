@extends('layouts.app')

@section('title', 'My Orders - E-Commerce Store')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Orders</h2>
            
            @if($orders->count() > 0)
                @foreach($orders as $order)
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>Order #{{ $order->id }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'delivered' ? 'success' : 'primary') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <strong>₹{{ number_format($order->final_total, 2) }}</strong>
                                    @if($order->delivery_speed)
                                        <br><small class="text-muted">{{ $order->delivery_speed_display }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>Items ({{ $order->items->count() }})</h6>
                                    @foreach($order->items->take(3) as $item)
                                        <div class="d-flex align-items-center mb-2">
                                            @if($item->productVariation->product->mainImage())
                                                <img src="{{ asset('storage/' . $item->productVariation->product->mainImage()->image_path) }}" 
                                                     class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" 
                                                     alt="{{ $item->productVariation->product->name }}"
                                                     onerror="this.src='https://via.placeholder.com/50x50?text=Product'">
                                            @else
                                                <img src="https://via.placeholder.com/50x50?text=Product" 
                                                     class="rounded me-3" style="width: 50px; height: 50px;" 
                                                     alt="{{ $item->productVariation->product->name }}">
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $item->productVariation->product->name }}</div>
                                                <small class="text-muted">
                                                    Qty: {{ $item->qty }} × ₹{{ number_format($item->price, 2) }}
                                                    @if($item->productVariation->variation_name)
                                                        | {{ $item->productVariation->variation_name }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($order->items->count() > 3)
                                        <small class="text-muted">and {{ $order->items->count() - 3 }} more items...</small>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <h6>Delivery Information</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Address:</small>
                                        <p class="small mb-1">{{ $order->address }}</p>
                                    </div>
                                    
                                    @if($order->delivery_speed)
                                    <div class="mb-2">
                                        <small class="text-muted">Delivery:</small>
                                        <div>
                                            <span class="badge bg-primary">{{ $order->delivery_speed_display }}</span>
                                            @if($order->delivery_date)
                                                <small class="d-block text-muted">{{ \Carbon\Carbon::parse($order->delivery_date)->format('M j, Y') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($order->is_gift)
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark"><i class="bi bi-gift"></i> Gift Order</span>
                                    </div>
                                    @endif
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                        @if($order->status === 'pending')
                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                Track Order (Coming Soon)
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Pagination -->
                {{ $orders->links() }}
            @else
                <div class="text-center py-5">
                    <i class="bi bi-bag-x display-1 text-muted"></i>
                    <h3 class="text-muted mt-3">No orders yet</h3>
                    <p>Start shopping to place your first order</p>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-shop"></i> Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
