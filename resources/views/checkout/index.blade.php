@extends('layouts.app')

@section('title', 'Checkout - E-Commerce Store')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Checkout Progress -->
            <div class="mb-4">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-success fw-bold">1. Login</span>
                    <span class="text-success fw-bold">2. Address</span>
                    <span class="text-success fw-bold">3. Payment</span>
                    <span class="text-muted">4. Review</span>
                </div>
            </div>

            <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
                @csrf
                <div class="row">
                    <!-- Main Checkout -->
                    <div class="col-lg-8">
                        
                        <!-- Delivery Address Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">1. DELIVERY ADDRESS</h5>
                            </div>
                            <div class="card-body">
                                @if($addresses->count() > 0)
                                    <div class="row">
                                        @foreach($addresses as $address)
                                            <div class="col-md-6 mb-3">
                                                <div class="card address-card {{ $loop->first ? 'border-primary' : 'border-light' }}" 
                                                     style="cursor: pointer;">
                                                    <div class="card-body p-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="address_id" value="{{ $address->id }}"
                                                                   id="address_{{ $address->id }}" 
                                                                   {{ $loop->first ? 'checked' : '' }} required>
                                                            <label class="form-check-label w-100" for="address_{{ $address->id }}">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <div class="fw-bold">{{ $address->full_name }}</div>
                                                                        <div class="text-muted">{{ $address->phone_number }}</div>
                                                                    </div>
                                                                    @if($address->is_default_shipping)
                                                                        <span class="badge bg-success">Default</span>
                                                                    @endif
                                                                    @if($address->type)
                                                                        <span class="badge bg-secondary">{{ ucfirst($address->type) }}</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-muted small mt-2">
                                                                    {{ $address->short_address }}
                                                                </div>
                                                                @if($address->landmark)
                                                                    <div class="text-muted small">
                                                                        <i class="bi bi-geo-alt"></i> {{ $address->landmark }}
                                                                    </div>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Add New Address Button -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                        <i class="bi bi-plus-circle"></i> Add New Address
                                    </button>
                                </div>

                                @if($addresses->count() === 0)
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Please add a delivery address to continue.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Delivery Options Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">2. DELIVERY OPTIONS</h5>
                            </div>
                            <div class="card-body">
                                <!-- Delivery Speed Options -->
                                <h6 class="fw-bold mb-3">Choose Delivery Speed</h6>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <div class="card delivery-option" style="cursor: pointer;">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="delivery_speed" 
                                                           value="standard" id="standard_delivery" checked>
                                                    <label class="form-check-label w-100" for="standard_delivery">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <div class="fw-bold text-success">FREE</div>
                                                                <div class="small">Standard Delivery</div>
                                                                <div class="text-muted small">5-7 business days</div>
                                                            </div>
                                                            <i class="bi bi-truck text-success"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card delivery-option" style="cursor: pointer;">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="delivery_speed" 
                                                           value="express" id="express_delivery">
                                                    <label class="form-check-label w-100" for="express_delivery">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <div class="fw-bold text-primary">₹99</div>
                                                                <div class="small">Express Delivery</div>
                                                                <div class="text-muted small">2-3 business days</div>
                                                            </div>
                                                            <i class="bi bi-lightning text-primary"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card delivery-option" style="cursor: pointer;">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="delivery_speed" 
                                                           value="same_day" id="same_day_delivery">
                                                    <label class="form-check-label w-100" for="same_day_delivery">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <div class="fw-bold text-warning">₹199</div>
                                                                <div class="small">Same Day Delivery</div>
                                                                <div class="text-muted small">Within 24 hours</div>
                                                            </div>
                                                            <i class="bi bi-clock text-warning"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Date Selection -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">Preferred Delivery Date</h6>
                                        <input type="date" class="form-control" name="delivery_date" 
                                               id="delivery_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                               max="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                        <small class="text-muted">Leave blank for earliest available date</small>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">Preferred Time Slot</h6>
                                        <select class="form-select" name="delivery_time_slot" id="delivery_time_slot">
                                            <option value="">Any time</option>
                                            <option value="morning">Morning (9 AM - 12 PM)</option>
                                            <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                                            <option value="evening">Evening (4 PM - 8 PM)</option>
                                            <option value="night">Night (6 PM - 9 PM)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Quick Date Options -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Quick Select</h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary quick-date-btn" 
                                                data-date="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                            Tomorrow
                                        </button>
                                        <button type="button" class="btn btn-outline-primary quick-date-btn" 
                                                data-date="{{ date('Y-m-d', strtotime('+2 days')) }}">
                                            Day After Tomorrow
                                        </button>
                                        <button type="button" class="btn btn-outline-primary quick-date-btn" 
                                                data-date="{{ date('Y-m-d', strtotime('next weekend')) }}">
                                            This Weekend
                                        </button>
                                    </div>
                                </div>

                                <!-- Special Instructions -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Special Instructions</h6>
                                    <textarea class="form-control" name="delivery_instructions" rows="3" 
                                              placeholder="Any special delivery instructions? (e.g., Leave with security, Call before delivery, etc.)"></textarea>
                                </div>

                                <!-- Gift Options -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Gift Options</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_gift" 
                                                       id="is_gift" value="1">
                                                <label class="form-check-label" for="is_gift">
                                                    <i class="bi bi-gift text-danger"></i> This is a gift
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="gift_wrap" 
                                                       id="gift_wrap" value="1">
                                                <label class="form-check-label" for="gift_wrap">
                                                    <i class="bi bi-box text-info"></i> Gift wrap (+₹50)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 gift-options" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Gift Message</label>
                                                <textarea class="form-control" name="gift_message" rows="2" 
                                                          placeholder="Your gift message here..."></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Recipient Name</label>
                                                <input type="text" class="form-control" name="gift_recipient_name" 
                                                       placeholder="Recipient's name">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Preferences -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Contact Preferences</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sms_updates" 
                                                       id="sms_updates" value="1" checked>
                                                <label class="form-check-label" for="sms_updates">
                                                    <i class="bi bi-phone text-success"></i> SMS updates
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_updates" 
                                                       id="email_updates" value="1" checked>
                                                <label class="form-check-label" for="email_updates">
                                                    <i class="bi bi-envelope text-primary"></i> Email updates
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">3. ORDER REVIEW</h5>
                            </div>
                            <div class="card-body">
                                @foreach($cartItems as $item)
                                    <div class="row align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <!-- Product Image -->
                                        <div class="col-md-2">
                                            @if($item->productVariation->product->mainImage())
                                                <img src="{{ $item->productVariation->product->mainImage()->image_path }}" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}"
                                                     style="height: 80px; width: 80px; object-fit: cover;"
                                                     onerror="this.src='https://via.placeholder.com/80x80?text=Product'">
                                            @else
                                                <img src="https://via.placeholder.com/80x80?text=Product" 
                                                     class="img-fluid rounded" alt="{{ $item->productVariation->product->name }}">
                                            @endif
                                        </div>
                                        
                                        <!-- Product Details -->
                                        <div class="col-md-6">
                                            <h6 class="mb-1">{{ $item->productVariation->product->name }}</h6>
                                            <small class="text-muted">by {{ $item->productVariation->product->brand->name }}</small>
                                            @if($item->productVariation->variation_name)
                                                <div class="small text-muted">{{ $item->productVariation->variation_name }}</div>
                                            @endif
                                            <div class="small text-success">
                                                <i class="bi bi-shield-check"></i> Eligible for return & exchange
                                            </div>
                                        </div>
                                        
                                        <!-- Quantity & Price -->
                                        <div class="col-md-2 text-center">
                                            <span class="badge bg-primary fs-6">Qty: {{ $item->qty }}</span>
                                        </div>
                                        
                                        <div class="col-md-2 text-end">
                                            <div class="fw-bold fs-5">₹{{ number_format($item->qty * $item->productVariation->price, 2) }}</div>
                                            <small class="text-muted">₹{{ number_format($item->productVariation->price, 2) }} each</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">3. PAYMENT OPTIONS</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod" checked>
                                    <label class="form-check-label w-100" for="cod">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash fs-3 text-success me-3"></i>
                                            <div>
                                                <div class="fw-bold">Cash on Delivery</div>
                                                <div class="text-muted small">Pay with cash when your order is delivered</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Note:</strong> Currently, we only accept Cash on Delivery. Online payment options will be available soon.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary Sidebar -->
                    <div class="col-lg-4">
                        <div class="sticky-top" style="top: 20px;">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Price Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Price ({{ $cartItems->sum('qty') }} items)</span>
                                        <span>₹{{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Delivery Charges</span>
                                        <span id="delivery-charges" class="text-success">FREE</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2" id="gift-wrap-row" style="display: none;">
                                        <span>Gift Wrap</span>
                                        <span>₹50.00</span>
                                    </div>
                                    
                                    @if($discount > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount ({{ $couponCode }})</span>
                                            <span class="text-success">-₹{{ number_format($discount, 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                                        <span>Total Amount</span>
                                        <span id="total-amount">₹{{ number_format($total, 2) }}</span>
                                    </div>
                                    
                                    @if($discount > 0)
                                        <div class="alert alert-success p-2">
                                            <small>You will save ₹{{ number_format($discount, 2) }} on this order</small>
                                        </div>
                                    @endif
                                    
                                    <!-- Delivery Estimate -->
                                    <div class="alert alert-info p-2 mb-3" id="delivery-estimate">
                                        <small id="estimate-text">
                                            <i class="bi bi-info-circle"></i> 
                                            Estimated delivery: 5-7 business days
                                        </small>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-warning btn-lg fw-bold" 
                                                {{ $addresses->count() === 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-lightning-charge"></i> PLACE ORDER
                                        </button>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <i class="bi bi-shield-check"></i> 
                                            Safe and Secure Payments. Easy returns. 100% Authentic products.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delivery Info -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-truck text-primary"></i> Delivery Information
                                    </h6>
                                    <ul class="list-unstyled small mb-0" id="delivery-features">
                                        <li><i class="bi bi-check-circle text-success"></i> Free standard delivery</li>
                                        <li><i class="bi bi-check-circle text-success"></i> 5-7 business days</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Cash on delivery available</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Easy 30-day returns</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Real-time tracking</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Contactless delivery available</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Trust Badges -->
                            <div class="card mt-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">
                                        <i class="bi bi-shield-check text-success"></i> Why Shop With Us?
                                    </h6>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <i class="bi bi-award text-warning"></i><br>
                                            <strong>Quality</strong><br>
                                            Guaranteed
                                        </div>
                                        <div class="col-4">
                                            <i class="bi bi-arrow-clockwise text-info"></i><br>
                                            <strong>Easy</strong><br>
                                            Returns
                                        </div>
                                        <div class="col-4">
                                            <i class="bi bi-headset text-primary"></i><br>
                                            <strong>24/7</strong><br>
                                            Support
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAddressForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone_number" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="address_line_1" 
                               placeholder="House/Flat/Building Name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" name="address_line_2" 
                               placeholder="Area, Street, Sector, Village">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Landmark</label>
                        <input type="text" class="form-control" name="landmark" 
                               placeholder="Near famous shop, mall, etc.">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select" name="country_id" id="country" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select" name="state_id" id="state" required disabled>
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select" name="city_id" id="city" required disabled>
                                <option value="">Select City</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="postal_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Type</label>
                            <select class="form-select" name="type">
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Add Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.delivery-option {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef !important;
}

.delivery-option:hover {
    border-color: #007bff !important;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.delivery-option.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

.address-card {
    transition: all 0.3s ease;
}

.address-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.quick-date-btn {
    transition: all 0.3s ease;
}

.gift-options {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.sticky-top {
    position: -webkit-sticky;
    position: sticky;
}

.progress {
    background-color: #e9ecef;
}

.form-check-input:checked + .form-check-label {
    font-weight: 500;
}

.delivery-option .form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.card-header.bg-primary,
.card-header.bg-success,
.card-header.bg-info {
    border: none;
}

@media (max-width: 768px) {
    .delivery-option .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .quick-date-btn {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .btn-group {
        flex-direction: column;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Address card selection
    $('.address-card').click(function() {
        $('.address-card').removeClass('border-primary').addClass('border-light');
        $(this).removeClass('border-light').addClass('border-primary');
        $(this).find('input[type="radio"]').prop('checked', true);
    });

    // Country change handler
    $('#country').change(function() {
        const countryId = $(this).val();
        const stateSelect = $('#state');
        const citySelect = $('#city');
        
        stateSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        citySelect.html('<option value="">Select City</option>').prop('disabled', true);
        
        if (countryId) {
            $.get(`/api/states/${countryId}`)
                .done(function(states) {
                    stateSelect.html('<option value="">Select State</option>');
                    states.forEach(state => {
                        stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                    });
                    stateSelect.prop('disabled', false);
                })
                .fail(function() {
                    stateSelect.html('<option value="">Error loading states</option>');
                });
        }
    });

    // State change handler
    $('#state').change(function() {
        const stateId = $(this).val();
        const citySelect = $('#city');
        
        citySelect.html('<option value="">Loading...</option>').prop('disabled', true);
        
        if (stateId) {
            $.get(`/api/cities/${stateId}`)
                .done(function(cities) {
                    citySelect.html('<option value="">Select City</option>');
                    cities.forEach(city => {
                        citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                    });
                    citySelect.prop('disabled', false);
                })
                .fail(function() {
                    citySelect.html('<option value="">Error loading cities</option>');
                });
        }
    });

    // Add address form submission
    $('#addAddressForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("addresses.quick-store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Add new address to the form
                    const addressHtml = `
                        <div class="col-md-6 mb-3">
                            <div class="card address-card border-primary" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_id" 
                                               value="${response.address.id}" id="address_${response.address.id}" checked required>
                                        <label class="form-check-label w-100" for="address_${response.address.id}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold">${response.address.full_name}</div>
                                                    <div class="text-muted">${response.address.phone_number}</div>
                                                </div>
                                                <span class="badge bg-success">New</span>
                                            </div>
                                            <div class="text-muted small mt-2">
                                                ${response.address.short_address}
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Uncheck all existing addresses
                    $('.address-card').removeClass('border-primary').addClass('border-light');
                    $('.address-card input[type="radio"]').prop('checked', false);
                    
                    // Add new address
                    $('.address-card').parent().parent().first().append(addressHtml);
                    
                    // Enable place order button
                    $('button[type="submit"]').prop('disabled', false);
                    
                    // Close modal
                    $('#addAddressModal').modal('hide');
                    form[0].reset();
                    
                    showToast(response.message, 'success');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response.errors) {
                    Object.keys(response.errors).forEach(key => {
                        showToast(response.errors[key][0], 'error');
                    });
                } else {
                    showToast(response?.message || 'Error adding address', 'error');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Form validation
    $('#checkoutForm').submit(function(e) {
        const selectedAddress = $('input[name="address_id"]:checked').val();
        
        if (!selectedAddress) {
            e.preventDefault();
            showToast('Please select a delivery address', 'error');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Placing Order...');
    });

    // Reset modal when closed
    $('#addAddressModal').on('hidden.bs.modal', function() {
        $('#addAddressForm')[0].reset();
        $('#state, #city').prop('disabled', true).html('<option value="">Select State</option>');
    });

    // ============ DELIVERY OPTIONS FUNCTIONALITY ============
    
    // Delivery speed selection
    $('input[name="delivery_speed"]').change(function() {
        const speed = $(this).val();
        let charge = 0;
        let estimate = '';
        let features = '';
        
        switch(speed) {
            case 'standard':
                charge = 0;
                estimate = 'Estimated delivery: 5-7 business days';
                features = `
                    <li><i class="bi bi-check-circle text-success"></i> Free standard delivery</li>
                    <li><i class="bi bi-check-circle text-success"></i> 5-7 business days</li>
                    <li><i class="bi bi-check-circle text-success"></i> Cash on delivery available</li>
                    <li><i class="bi bi-check-circle text-success"></i> Easy 30-day returns</li>
                    <li><i class="bi bi-check-circle text-success"></i> Real-time tracking</li>
                    <li><i class="bi bi-check-circle text-success"></i> Contactless delivery available</li>
                `;
                break;
            case 'express':
                charge = 99;
                estimate = 'Estimated delivery: 2-3 business days';
                features = `
                    <li><i class="bi bi-lightning text-primary"></i> Express delivery (₹99)</li>
                    <li><i class="bi bi-check-circle text-success"></i> 2-3 business days</li>
                    <li><i class="bi bi-check-circle text-success"></i> Priority handling</li>
                    <li><i class="bi bi-check-circle text-success"></i> SMS & Email updates</li>
                    <li><i class="bi bi-check-circle text-success"></i> Real-time tracking</li>
                    <li><i class="bi bi-check-circle text-success"></i> Contactless delivery</li>
                `;
                break;
            case 'same_day':
                charge = 199;
                estimate = 'Estimated delivery: Within 24 hours';
                features = `
                    <li><i class="bi bi-clock text-warning"></i> Same day delivery (₹199)</li>
                    <li><i class="bi bi-check-circle text-success"></i> Within 24 hours</li>
                    <li><i class="bi bi-check-circle text-success"></i> Premium handling</li>
                    <li><i class="bi bi-check-circle text-success"></i> Live tracking</li>
                    <li><i class="bi bi-check-circle text-success"></i> Call before delivery</li>
                    <li><i class="bi bi-check-circle text-success"></i> Contactless delivery</li>
                `;
                break;
        }
        
        // Update delivery charges
        $('#delivery-charges').html(charge === 0 ? '<span class="text-success">FREE</span>' : '₹' + charge.toFixed(2));
        
        // Update estimate
        $('#estimate-text').html('<i class="bi bi-info-circle"></i> ' + estimate);
        
        // Update delivery features
        $('#delivery-features').html(features);
        
        // Update total
        updateTotal();
    });

    // Quick date selection
    $('.quick-date-btn').click(function() {
        const date = $(this).data('date');
        $('#delivery_date').val(date);
        $('.quick-date-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });

    // Gift options toggle
    $('#is_gift, #gift_wrap').change(function() {
        if ($('#is_gift').is(':checked')) {
            $('.gift-options').slideDown();
        } else {
            $('.gift-options').slideUp();
            $('#gift_wrap').prop('checked', false);
        }
        
        // Show/hide gift wrap charge
        if ($('#gift_wrap').is(':checked')) {
            $('#gift-wrap-row').show();
        } else {
            $('#gift-wrap-row').hide();
        }
        
        updateTotal();
    });

    // Delivery card selection styling
    $('.delivery-option').click(function() {
        $('.delivery-option').removeClass('border-primary').addClass('border-light');
        $(this).removeClass('border-light').addClass('border-primary');
        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
    });

    // Update total amount calculation
    function updateTotal() {
        let subtotal = {{ $subtotal }};
        let discount = {{ $discount }};
        let deliveryCharge = 0;
        let giftWrapCharge = 0;
        
        // Get delivery charge
        const selectedDelivery = $('input[name="delivery_speed"]:checked').val();
        switch(selectedDelivery) {
            case 'express':
                deliveryCharge = 99;
                break;
            case 'same_day':
                deliveryCharge = 199;
                break;
        }
        
        // Get gift wrap charge
        if ($('#gift_wrap').is(':checked')) {
            giftWrapCharge = 50;
        }
        
        const total = subtotal - discount + deliveryCharge + giftWrapCharge;
        $('#total-amount').text('₹' + total.toFixed(2));
    }

    // Delivery date validation
    $('#delivery_date').change(function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        const dayOfWeek = selectedDate.getDay();
        
        // Check if weekend (Saturday = 6, Sunday = 0)
        if (dayOfWeek === 0) {
            showToast('Sunday delivery may have limited availability', 'warning');
        }
        
        // Check if too far in future
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 30);
        
        if (selectedDate > maxDate) {
            showToast('Please select a date within 30 days', 'error');
            $(this).val('');
        }
    });

    // Form submission with delivery data
    $('#checkoutForm').submit(function(e) {
        const selectedAddress = $('input[name="address_id"]:checked').val();
        
        if (!selectedAddress) {
            e.preventDefault();
            showToast('Please select a delivery address', 'error');
            return false;
        }
        
        // Validate delivery date if same day is selected
        const deliverySpeed = $('input[name="delivery_speed"]:checked').val();
        const deliveryDate = $('#delivery_date').val();
        
        if (deliverySpeed === 'same_day' && deliveryDate) {
            const selectedDate = new Date(deliveryDate);
            const today = new Date();
            const tomorrow = new Date();
            tomorrow.setDate(today.getDate() + 1);
            
            if (selectedDate > tomorrow) {
                e.preventDefault();
                showToast('Same day delivery is only available for today or tomorrow', 'error');
                return false;
            }
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Placing Order...');
    });

    // Initialize delivery options styling
    $('.delivery-option').each(function() {
        if ($(this).find('input[type="radio"]').is(':checked')) {
            $(this).removeClass('border-light').addClass('border-primary');
        }
    });
});
</script>
@endpush
