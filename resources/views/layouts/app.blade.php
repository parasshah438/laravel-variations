<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'E-Commerce Store'))</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome as fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .btn {
            font-weight: 500;
        }
        
        .toast-container {
            z-index: 1055;
        }
        
        .product-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .price-original {
            text-decoration: line-through;
            color: #6c757d;
        }
        
        .price-current {
            color: #dc3545;
            font-weight: 600;
        }
        
        .badge-discount {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        
        footer {
            background-color: #212529;
            color: #fff;
        }
        
        .footer-link {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-link:hover {
            color: #fff;
        }
        
        /* Advanced Search Styles */
        .search-hero .advanced-search-component {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-hero .search-input-group {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .shop-header .advanced-search-component {
            max-width: 400px;
            margin-left: auto;
        }
        
        /* Navbar search adjustments */
        .navbar .advanced-search-component .search-input-group {
            border: 2px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
        }
        
        .navbar .advanced-search-component .search-input {
            background: transparent;
            border: none;
            color: white;
        }
        
        .navbar .advanced-search-component .search-input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .navbar .advanced-search-component .input-group-text {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.7);
        }
        
        .navbar .advanced-search-component .voice-search-btn,
        .navbar .advanced-search-component .visual-search-btn {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.8);
        }
        
        .navbar .advanced-search-component .voice-search-btn:hover,
        .navbar .advanced-search-component .visual-search-btn:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        @media (max-width: 768px) {
            .search-hero {
                padding: 2rem 0 !important;
            }
            
            .shop-header {
                padding: 2rem 0 !important;
            }
            
            .shop-header .col-md-6 {
                margin-bottom: 1rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-shop me-2"></i>E-Store
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('shop.*') ? 'active' : '' }}" href="{{ route('shop.index') }}">
                            <i class="bi bi-shop me-1"></i>Shop
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-grid me-1"></i>Categories
                        </a>
                        <ul class="dropdown-menu">
                            @php
                                $categories = \App\Models\Category::whereNull('parent_id')->with('children')->get();
                            @endphp
                            @foreach($categories as $category)
                                <li>
                                    <a class="dropdown-item" href="{{ route('category.show', $category->slug) }}">
                                        @if($category->icon)
                                            <i class="{{ $category->icon }} me-2"></i>
                                        @endif
                                        {{ $category->name }}
                                    </a>
                                </li>
                                @if($category->children->count() > 0)
                                    @foreach($category->children as $child)
                                        <li>
                                            <a class="dropdown-item ps-4" href="{{ route('category.show', $child->slug) }}">
                                                <small>{{ $child->name }}</small>
                                            </a>
                                        </li>
                                    @endforeach
                                    @if(!$loop->last)
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                </ul>
                
                <!-- Advanced Search Component -->
                <div class="me-3 flex-grow-1" style="max-width: 500px;">
                    @include('components.advanced-search')
                </div>
                
                <!-- User Actions -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('wishlist.index') }}">
                            <i class="bi bi-heart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="wishlistCount">
                                0
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                            <i class="bi bi-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                0
                            </span>
                        </a>
                    </li>
                    
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                    <i class="bi bi-bag me-2"></i>My Orders
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('wishlist.index') }}">
                                    <i class="bi bi-heart me-2"></i>Wishlist
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-shop me-2"></i>E-Store
                    </h5>
                    <p class="text-muted">Your one-stop destination for quality products with amazing variations and unbeatable prices.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="footer-link fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-link fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="footer-link fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-link fs-4"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('home') }}" class="footer-link">Home</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">About Us</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Contact</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">Shipping Info</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Returns</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Size Guide</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Track Order</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">My Account</h6>
                    <ul class="list-unstyled">
                        @auth
                            <li class="mb-2"><a href="{{ route('orders.index') }}" class="footer-link">My Orders</a></li>
                            <li class="mb-2"><a href="{{ route('wishlist.index') }}" class="footer-link">Wishlist</a></li>
                        @else
                            <li class="mb-2"><a href="{{ route('login') }}" class="footer-link">Login</a></li>
                            <li class="mb-2"><a href="{{ route('register') }}" class="footer-link">Register</a></li>
                        @endauth
                        <li class="mb-2"><a href="{{ route('cart.index') }}" class="footer-link">Shopping Cart</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>123 Store Street, City</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>+1 234 567 8900</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>info@estore.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} E-Store. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">Made with <i class="bi bi-heart-fill text-danger"></i> by Laravel</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <!-- jQuery CDN (Load First) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery Check -->
    <script>
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
        } else {
            console.log('jQuery loaded successfully:', jQuery.fn.jquery);
        }
        
        // Check if Bootstrap Icons are loaded
        $(document).ready(function() {
            // Test if Bootstrap Icons are working
            const testIcon = $('<i class="bi bi-heart-fill"></i>').appendTo('body');
            const iconStyles = window.getComputedStyle(testIcon[0], ':before');
            if (iconStyles.content && iconStyles.content !== 'none') {
                console.log('Bootstrap Icons loaded successfully');
            } else {
                console.error('Bootstrap Icons may not be loaded properly');
            }
            testIcon.remove();
        });
    </script>
    
    <!-- Custom Scripts -->
    <script src="/js/guest-cart-manager.js"></script>
    <script>
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Toast function
        function showToast(message, type = 'info') {
            const toast = document.getElementById('liveToast');
            const toastBody = toast.querySelector('.toast-body');
            const toastHeader = toast.querySelector('.toast-header');
            
            // Update toast content
            toastBody.textContent = message;
            
            // Update toast icon based on type
            const icon = toastHeader.querySelector('i');
            icon.className = `bi me-2`;
            
            switch(type) {
                case 'success':
                    icon.classList.add('bi-check-circle-fill', 'text-success');
                    break;
                case 'error':
                case 'danger':
                    icon.classList.add('bi-exclamation-triangle-fill', 'text-danger');
                    break;
                case 'warning':
                    icon.classList.add('bi-exclamation-circle-fill', 'text-warning');
                    break;
                default:
                    icon.classList.add('bi-info-circle-fill', 'text-primary');
            }
            
            // Show toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Update cart and wishlist counters
        function updateCounters() {
            // Update cart counter
            $.get('/cart/summary')
                .done(function(response) {
                    $('#cartCount').text(response.count || 0);
                })
                .fail(function() {
                    $('#cartCount').text('0');
                });

            // Update wishlist counter
            $.get('/wishlist/status')
                .done(function(response) {
                    $('#wishlistCount').text(response.count || 0);
                })
                .fail(function() {
                    $('#wishlistCount').text('0');
                });
        }

        // Load counters on page load
        $(document).ready(function() {
            updateCounters();
            
            // Show welcome message for newly logged in users
            @auth
                @if(session('login_success'))
                    showToast('Welcome back! Your cart and wishlist have been updated.', 'success');
                @endif
            @endauth
        });
    </script>
    
    @stack('scripts')
    
    <!-- Amazon-Style Quantity Controls -->
    <script>
    $(document).ready(function() {
        // Amazon-Style Quantity Controls for Product Cards
        $(document).on('click', '.btn-qty-decrease, .btn-qty-increase', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $input = $button.siblings('.product-qty-input, .cart-qty-input');
            const isDecrease = $button.hasClass('btn-qty-decrease');
            const productId = $button.data('product-id') || $button.data('item-id');
            const isCartItem = $button.closest('.cart-item, .quantity-group').length > 0;
            
            let currentQty = parseInt($input.val()) || 1;
            let maxQty = parseInt($input.attr('max')) || 999;
            let newQty = currentQty;
            
            if (isDecrease) {
                if (currentQty <= 1 && isCartItem) {
                    // Remove from cart if quantity is 1 and user clicks decrease
                    if (confirm('Are you sure you want to remove this item from cart?')) {
                        removeFromCart(productId);
                    }
                    return;
                } else {
                    newQty = Math.max(1, currentQty - 1);
                }
            } else {
                newQty = Math.min(maxQty, currentQty + 1);
                if (newQty >= maxQty) {
                    showToast(`Maximum quantity available is ${maxQty}`, 'warning');
                }
            }
            
            // Update input value
            $input.val(newQty);
            
            // Update hidden quantity input in forms
            $input.closest('.product-card, .card').find('.quantity-input').val(newQty);
            
            // Update button icons
            updateQtyIcons($button.closest('.quantity-group'), newQty);
            
            // If it's a cart item, update cart via AJAX
            if (isCartItem) {
                updateCartQuantity(productId, newQty);
            }
        });
        
        // Direct input change for quantity
        $(document).on('change', '.product-qty-input, .cart-qty-input', function() {
            const $input = $(this);
            const newQty = parseInt($input.val()) || 1;
            const maxQty = parseInt($input.attr('max')) || 999;
            const minQty = parseInt($input.attr('min')) || 1;
            const productId = $input.data('product-id') || $input.data('item-id');
            const isCartItem = $input.closest('.cart-item, .quantity-group').length > 0;
            
            // Validate quantity
            let validQty = Math.max(minQty, Math.min(maxQty, newQty));
            $input.val(validQty);
            
            // Update button icons
            updateQtyIcons($input.closest('.quantity-group'), validQty);
            
            // If it's a cart item, update cart via AJAX
            if (isCartItem && validQty !== parseInt($input.data('initial'))) {
                updateCartQuantity(productId, validQty);
                $input.data('initial', validQty);
            }
        });
        
        // Update quantity icons (trash for qty=1, minus for qty>1)
        function updateQtyIcons($container, qty) {
            const $decreaseBtn = $container.find('.btn-qty-decrease .qty-icon');
            const isCartItem = $container.closest('.cart-item, .quantity-group').length > 0;
            
            if (isCartItem && qty <= 1) {
                $decreaseBtn.text('🗑️');
            } else {
                $decreaseBtn.text('−');
            }
        }
        
        // Update cart quantity via AJAX
        function updateCartQuantity(itemId, quantity) {
            $.ajax({
                url: '/cart/update-quantity',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    item_id: itemId,
                    quantity: quantity
                },
                beforeSend: function() {
                    // Show loading state
                    $('.quantity-group').addClass('updating');
                },
                success: function(response) {
                    if (response.success) {
                        // Update item total
                        const $item = $(`.cart-item[data-item-id="${itemId}"]`);
                        const price = parseFloat(response.item.price);
                        const subtotal = price * quantity;
                        
                        $item.find('.item-total').text('₹' + subtotal.toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        // Update cart totals
                        $('#subtotal').text('₹' + parseFloat(response.cart_total).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#finalTotal').text('₹' + parseFloat(response.cart_total).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        // Update cart counter in navigation
                        updateCounters();
                        
                        showToast('Cart updated successfully', 'success');
                    } else {
                        showToast(response.message || 'Failed to update cart', 'error');
                    }
                },
                error: function(xhr) {
                    showToast('Failed to update cart. Please try again.', 'error');
                    console.error('Cart update error:', xhr);
                },
                complete: function() {
                    $('.quantity-group').removeClass('updating');
                }
            });
        }
        
        // Remove item from cart
        function removeFromCart(itemId) {
            $.ajax({
                url: '/cart/remove',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    item_id: itemId
                },
                beforeSend: function() {
                    const $item = $(`.cart-item[data-item-id="${itemId}"]`);
                    $item.addClass('removing');
                },
                success: function(response) {
                    if (response.success) {
                        // Remove item from DOM
                        const $item = $(`.cart-item[data-item-id="${itemId}"]`);
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                location.reload();
                            }
                        });
                        
                        // Update cart totals
                        $('#subtotal').text('₹' + parseFloat(response.cart_total).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#finalTotal').text('₹' + parseFloat(response.cart_total).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        // Update cart counter
                        updateCounters();
                        
                        showToast('Item removed from cart', 'success');
                    } else {
                        showToast(response.message || 'Failed to remove item', 'error');
                    }
                },
                error: function(xhr) {
                    showToast('Failed to remove item. Please try again.', 'error');
                },
                complete: function() {
                    $('.removing').removeClass('removing');
                }
            });
        }
        
        // Add to cart with quantity
        $(document).on('submit', '.add-to-cart-form', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('button[type="submit"]');
            const $qtyInput = $form.find('.product-qty-input');
            const quantity = parseInt($qtyInput.val()) || 1;
            
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                beforeSend: function() {
                    $button.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Adding...');
                },
                success: function(response) {
                    if (response.success) {
                        showToast(`${quantity} item(s) added to cart!`, 'success');
                        updateCounters();
                        
                        // Reset quantity to 1
                        $qtyInput.val(1);
                        updateQtyIcons($form.find('.quantity-group'), 1);
                    } else {
                        showToast(response.message || 'Failed to add to cart', 'error');
                    }
                },
                error: function(xhr) {
                    showToast('Failed to add to cart. Please try again.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Add to Cart');
                }
            });
        });
    });
    </script>
    
    <!-- Quantity Control Styles -->
    <style>
        .quantity-group.updating {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .quantity-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border: 1px solid #dee2e6;
        }
        
        .quantity-group .form-control {
            border-left: 0;
            border-right: 0;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .quantity-group .btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .quantity-group .btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .qty-icon {
            font-size: 0.875rem;
            line-height: 1;
        }
        
        .cart-item.removing {
            opacity: 0.5;
            pointer-events: none;
        }
        
        @media (max-width: 576px) {
            .quantity-group {
                max-width: 120px !important;
            }
        }
    </style>
</body>
</html>
