@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Order Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Order #{{ $order->id }}</h2>
                    <p class="text-muted mb-0">
                        Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('orders.invoice', $order->id) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Download Invoice
                    </a>
                    <a href="{{ route('orders.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <!-- Order Progress -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-truck text-primary"></i> Order Tracking
                            </h5>
                            
                            <div class="progress-steps">
                                <div class="step {{ in_array($order->status, ['pending', 'confirmed', 'shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="step-icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>Order Confirmed</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M j, Y g:i A') }}</small>
                                    </div>
                                </div>
                                
                                <div class="step {{ in_array($order->status, ['confirmed', 'shipped', 'delivered']) ? 'completed' : ($order->status == 'processing' ? 'active' : '') }}">
                                    <div class="step-icon">
                                        <i class="bi bi-gear"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>Processing</h6>
                                        <small class="text-muted">
                                            @if(in_array($order->status, ['confirmed', 'shipped', 'delivered']))
                                                Processed
                                            @elseif($order->status == 'processing')
                                                In Progress
                                            @else
                                                Pending
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="step {{ in_array($order->status, ['shipped', 'delivered']) ? 'completed' : ($order->status == 'shipped' ? 'active' : '') }}">
                                    <div class="step-icon">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>Shipped</h6>
                                        <small class="text-muted">
                                            @if(in_array($order->status, ['shipped', 'delivered']))
                                                Out for delivery
                                            @else
                                                Not yet shipped
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="step {{ $order->status == 'delivered' ? 'completed' : ($order->status == 'out_for_delivery' ? 'active' : '') }}">
                                    <div class="step-icon">
                                        <i class="bi bi-house"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>Delivered</h6>
                                        <small class="text-muted">
                                            @if($order->status == 'delivered')
                                                Delivered successfully
                                            @else
                                                Expected delivery
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i>
                                <strong>Current Status:</strong> 
                                <span class="badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }} ms-2">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="bi bi-box-seam"></i> Items Ordered ({{ $order->items->count() }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Specifications</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            @if($item->productVariation->product->images->count() > 0)
                                                                <img src="{{ asset('storage/' . $item->productVariation->product->images->first()->image_path) }}" 
                                                                     alt="{{ $item->productVariation->product->name }}"
                                                                     class="rounded border" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                                                     style="width: 60px; height: 60px;">
                                                                    <i class="bi bi-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item->productVariation->product->name }}</h6>
                                                            <small class="text-muted">
                                                                SKU: {{ $item->productVariation->sku ?? 'N/A' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($item->productVariation->attributeValues && $item->productVariation->attributeValues->count() > 0)
                                                        @foreach($item->productVariation->attributeValues as $value)
                                                            <span class="badge bg-light text-dark me-1">
                                                                {{ $value->attribute->name }}: {{ $value->value }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $variations = [];
                                                            if($item->productVariation->size) $variations[] = 'Size: ' . $item->productVariation->size;
                                                            if($item->productVariation->color) $variations[] = 'Color: ' . $item->productVariation->color;
                                                            if($item->productVariation->fabric) $variations[] = 'Material: ' . $item->productVariation->fabric;
                                                        @endphp
                                                        @if(count($variations) > 0)
                                                            @foreach($variations as $variation)
                                                                <span class="badge bg-light text-dark me-1">{{ $variation }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">Default</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary rounded-pill">{{ $item->qty }}</span>
                                                </td>
                                                <td class="text-end">
                                                    ₹{{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    ₹{{ number_format($item->qty * $item->price, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <!-- Payment & Delivery Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Order Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $order->items->sum('qty') }} items)</span>
                                <span>₹{{ number_format($order->items->sum(function($item) { return $item->qty * $item->price; }), 2) }}</span>
                            </div>
                            
                            @if($order->delivery_charge && $order->delivery_charge > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Delivery Charge ({{ $order->delivery_speed_display }})</span>
                                <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
                            </div>
                            @else
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping & Handling</span>
                                <span class="text-success">FREE</span>
                            </div>
                            @endif
                            
                            @if($order->gift_wrap_charge && $order->gift_wrap_charge > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Gift Wrapping</span>
                                <span>₹{{ number_format($order->gift_wrap_charge, 2) }}</span>
                            </div>
                            @endif
                            
                            @php
                                $itemsTotal = $order->items->sum(function($item) { return $item->qty * $item->price; });
                                $chargesTotal = ($order->delivery_charge ?? 0) + ($order->gift_wrap_charge ?? 0);
                                $subtotalWithCharges = $itemsTotal + $chargesTotal;
                                $discount = $subtotalWithCharges - $order->final_total;
                            @endphp
                            
                            @if($discount > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <span class="text-success">-₹{{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between fw-bold fs-5 text-primary">
                                <span>Grand Total</span>
                                <span>₹{{ number_format($order->final_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    @if($order->delivery_speed)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-truck"></i> Delivery Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Delivery Speed:</span>
                                    <span class="badge bg-primary">{{ $order->delivery_speed_display }}</span>
                                </div>
                                @if($order->delivery_date)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Delivery Date:</span>
                                    <span>{{ \Carbon\Carbon::parse($order->delivery_date)->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($order->delivery_time_slot)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Time Slot:</span>
                                    <span>{{ $order->delivery_time_slot }}</span>
                                </div>
                                @endif
                                @if($order->estimated_delivery)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Estimated Delivery:</span>
                                    <span class="text-muted">{{ $order->estimated_delivery }}</span>
                                </div>
                                @endif
                            </div>
                            
                            @if($order->delivery_instructions)
                            <div class="border-top pt-3">
                                <small class="text-muted fw-semibold">Special Instructions:</small>
                                <p class="small mb-0 mt-1">{{ $order->delivery_instructions }}</p>
                            </div>
                            @endif
                            
                            @if($order->sms_updates || $order->email_updates)
                            <div class="border-top pt-3 mt-3">
                                <small class="text-muted fw-semibold">Notifications:</small>
                                <div class="mt-1">
                                    @if($order->sms_updates)
                                        <span class="badge bg-success me-1"><i class="bi bi-phone"></i> SMS Updates</span>
                                    @endif
                                    @if($order->email_updates)
                                        <span class="badge bg-info"><i class="bi bi-envelope"></i> Email Updates</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Gift Information -->
                    @if($order->hasGiftOptions())
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-gift"></i> Gift Details
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($order->is_gift)
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-gift-fill"></i> This is a gift order
                            </div>
                            @endif
                            
                            @if($order->gift_wrap)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Gift Wrapping:</span>
                                <span class="badge bg-warning text-dark">Included</span>
                            </div>
                            @endif
                            
                            @if($order->gift_recipient_name)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Recipient:</span>
                                <span>{{ $order->gift_recipient_name }}</span>
                            </div>
                            @endif
                            
                            @if($order->gift_message)
                            <div class="border-top pt-3">
                                <small class="text-muted fw-semibold">Gift Message:</small>
                                <div class="bg-light p-2 rounded mt-1">
                                    <small>"{{ $order->gift_message }}"</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-credit-card"></i> Payment Method
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-coin text-success fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">{{ strtoupper($order->payment_method) }}</h6>
                                    <small class="text-muted">
                                        @if($order->payment_method == 'cod')
                                            Cash on Delivery
                                        @else
                                            {{ ucfirst($order->payment_method) }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-geo-alt"></i> Delivery Address
                            </h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                <strong>{{ $order->user->name }}</strong><br>
                                {{ $order->address }}
                            </address>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($order->status != 'delivered' && $order->status != 'cancelled')
                                    <button class="btn btn-outline-danger" onclick="confirmCancel()">
                                        <i class="bi bi-x-circle"></i> Cancel Order
                                    </button>
                                @endif
                                
                                <a href="{{ route('orders.invoice', $order->id) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-download"></i> Download Invoice
                                </a>
                                
                                <button class="btn btn-outline-secondary" onclick="printOrder()">
                                    <i class="bi bi-printer"></i> Print Order
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Help & Support -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <i class="bi bi-headset text-primary fs-1 mb-3"></i>
                            <h6>Need Help?</h6>
                            <p class="small text-muted mb-3">
                                Have questions about this order? Contact our support team.
                            </p>
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-chat-dots"></i> Live Chat
                                </a>
                                <a href="tel:+911234567890" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-telephone"></i> Call Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 20px 0;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    color: #6c757d;
    border: 2px solid #e9ecef;
}

.step.completed .step-icon {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.step.active .step-icon {
    background: #007bff;
    color: white;
    border-color: #007bff;
    animation: pulse 2s infinite;
}

.step.completed::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    right: -50%;
    height: 2px;
    background: #28a745;
    z-index: 1;
}

.step:last-child::before {
    display: none;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

@media (max-width: 768px) {
    .progress-steps {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .progress-steps::before {
        display: none;
    }
    
    .step {
        flex-direction: row;
        text-align: left;
        margin-bottom: 20px;
        width: 100%;
    }
    
    .step-icon {
        margin-bottom: 0;
        margin-right: 15px;
    }
    
    .step.completed::before {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
function confirmCancel() {
    if (confirm('Are you sure you want to cancel this order?')) {
        // Add cancel order functionality
        alert('Order cancellation feature will be implemented.');
    }
}

function printOrder() {
    window.print();
}
</script>
@endpush
