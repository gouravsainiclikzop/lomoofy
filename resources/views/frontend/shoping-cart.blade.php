@extends('layouts.frontend')

@section('title', 'Shopping Cart - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Support</a></li>
						<li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Product Detail ======================== -->
<section class="middle">
	<div class="container">
	
		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				<div class="text-center d-block mb-5">
					<h2>Shopping Cart</h2>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-between">
			<div class="col-12 col-lg-7 col-md-12">
				<!-- Cart Loading Spinner -->
				<div id="cartLoader" class="text-center py-5">
					<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
						<span class="visually-hidden">Loading...</span>
					</div>
					<p class="mt-3 text-muted">Loading your cart...</p>
				</div>
				
				<div id="cartItemsContainer" style="display: none;">
					@if($cart->items && $cart->items->count() > 0)
						<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">
							@foreach($cart->items as $item)
								@php
									$product = $item->product;
									$variant = $item->variant;
									
									// Get variant image first (primary or first), fallback to product image
									$imageUrl = asset('frontend/images/product/sample-product.jpg'); // Default fallback
									
									if ($variant && $variant->images && $variant->images->count() > 0) {
										// Try to get primary variant image first
										$primaryVariantImage = $variant->images->where('is_primary', true)->first();
										if ($primaryVariantImage) {
											$imageUrl = asset('storage/' . $primaryVariantImage->image_path);
										} else {
											// Use first variant image
											$firstVariantImage = $variant->images->first();
											if ($firstVariantImage) {
												$imageUrl = asset('storage/' . $firstVariantImage->image_path);
											}
										}
									} elseif ($product) {
										// Fallback to product image if no variant image
										$imageUrl = $product->primaryImage 
											? asset('storage/' . $product->primaryImage->image_path)
											: ($product->images && $product->images->count() > 0
												? asset('storage/' . $product->images->first()->image_path)
												: asset('frontend/images/product/sample-product.jpg'));
									}
									
									// Get variant attributes
									$variantAttrs = $variant && $variant->attributes 
										? (is_string($variant->attributes) ? json_decode($variant->attributes, true) : $variant->attributes)
										: [];
									
									$colorValue = '';
									$sizeValue = '';
									
									// Get color and size attributes from product category or global
									$colorAttribute = null;
									$sizeAttribute = null;
									if ($product->category) {
										$colorAttribute = $product->category->getAllProductAttributes()->where('type', 'color')->first();
										$sizeAttribute = $product->category->getAllProductAttributes()->where('type', 'size')->first();
									}
									if (!$colorAttribute) {
										$colorAttribute = \App\Models\ProductAttribute::where('type', 'color')->first();
									}
									if (!$sizeAttribute) {
										$sizeAttribute = \App\Models\ProductAttribute::where('type', 'size')->first();
									}
									
									foreach($variantAttrs as $key => $value) {
										if (empty($value)) continue;
										
										// Check for color by attribute ID
										if (is_numeric($key) && $colorAttribute && $key == $colorAttribute->id) {
											$colorValue = $value;
										}
										// Check for color by attribute name
										elseif (strtolower($key) === 'color' || ($colorAttribute && $key === $colorAttribute->name)) {
											$colorValue = $value;
										}
										
										// Check for size by attribute ID
										if (is_numeric($key) && $sizeAttribute && $key == $sizeAttribute->id) {
											$sizeValue = $value;
										}
										// Check for size by attribute name
										elseif (strtolower($key) === 'size' || ($sizeAttribute && $key === $sizeAttribute->name)) {
											$sizeValue = $value;
										}
									}
								@endphp
								<li class="list-group-item" data-cart-item-id="{{ $item->id }}">
									<div class="row align-items-center">
										<div class="col-3">
											<!-- Image -->
											<a href="{{ route('frontend.product') }}?product={{ $product->slug }}"><img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-fluid"></a>
										</div>
										<div class="col d-flex align-items-center justify-content-between">
											<div class="cart_single_caption ps-2">
												<h4 class="product_title fs-md ft-medium mb-1 lh-1">
													<a href="{{ route('frontend.product') }}?product={{ $product->slug }}">{{ $product->name }}</a>
												</h4>
												@if($sizeValue)
													<p class="mb-1 lh-1"><span class="text-dark">Size: {{ $sizeValue }}</span></p>
												@endif
												@if($colorValue)
													<p class="mb-3 lh-1"><span class="text-dark">Color: {{ $colorValue }}</span></p>
												@endif
												<h4 class="fs-md ft-medium mb-3 lh-1">₹{{ number_format($item->unit_price, 2) }}</h4>
												<select class="mb-2 custom-select w-auto cart-item-quantity" data-cart-item-id="{{ $item->id }}" data-variant-id="{{ $item->product_variant_id }}">
													@php
    // Use stock_quantity directly (warehouse logic disabled for now)
    $availableStock = $variant ? ($variant->stock_quantity ?? 0) : ($product->stock_quantity ?? 10);
    $maxQty = min(10, max(1, $availableStock));
@endphp
@for($qty = 1; $qty <= $maxQty; $qty++)
														<option value="{{ $qty }}" {{ $item->quantity == $qty ? 'selected' : '' }}>{{ $qty }}</option>
													@endfor
												</select>
											</div>
											<div class="fls_last">
												<button class="close_slide gray remove-cart-item" data-cart-item-id="{{ $item->id }}" data-variant-id="{{ $item->product_variant_id }}">
													<i class="ti-close"></i>
												</button>
											</div>
										</div>
									</div>
								</li>
							@endforeach
						</ul>
					@else
						<div id="emptyCartMessage" class="alert alert-info text-center" style="display: none;">
							<p class="mb-0">Your cart is empty.</p>
							<a href="{{ route('frontend.shop') }}" class="btn btn-dark mt-3">Continue Shopping</a>
						</div>
					@endif
				</div>
				
				<!-- Coupon Section - Will be shown/hidden dynamically -->
				<div id="couponSection" class="row align-items-end justify-content-between mb-10 mb-md-0" style="display: none;">
					<div class="col-12 col-md-7">
						<!-- Coupon -->
						<form id="couponForm" class="mb-7 mb-md-0">
							<label class="fs-sm ft-medium text-dark">Coupon code:</label>
							<div class="row form-row">
								<div class="col">
									<input class="form-control" type="text" id="couponCode" placeholder="Enter coupon code*" value="">
								</div>
								<div class="col-auto">
									<button class="btn btn-dark" type="submit">Apply</button>
								</div>
							</div>
							<div class="mt-2" id="couponMessage" style="display: none;">
							</div>
						</form>
					</div>
					<div class="col-12 col-md-auto mfliud">
						<button class="btn stretched-links borders" id="updateCartBtn">Update Cart</button>
					</div>
				</div>
			</div>
			
			<div class="col-12 col-md-12 col-lg-4">
				<div class="card mb-4 gray mfliud">
				  <div class="card-body">
					<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x">
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Subtotal</span> <span class="ms-auto text-dark ft-medium" id="cartSubtotal">₹{{ number_format($cart->subtotal ?? 0, 2) }}</span>
					  </li>
					  <!-- Discount row - shown/hidden dynamically -->
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular" id="cartDiscountRow" style="display: {{ ($cart->discount_amount ?? 0) > 0 ? '' : 'none' }};">
						<span>Discount</span> <span class="ms-auto text-dark ft-medium text-success" id="cartDiscount">-₹{{ number_format($cart->discount_amount ?? 0, 2) }}</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Tax</span> <span class="ms-auto text-dark ft-medium" id="cartTax">₹{{ number_format($cart->tax_amount ?? 0, 2) }}</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Shipping</span> <span class="ms-auto text-dark ft-medium" id="cartShipping">₹{{ number_format($cart->shipping_amount ?? 0, 2) }}</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Total</span> <span class="ms-auto text-dark ft-medium" id="cartTotal">₹{{ number_format($cart->total_amount ?? 0, 2) }}</span>
					  </li>
					  <li class="list-group-item fs-sm text-center">
						Shipping cost calculated at Checkout *
					  </li>
					</ul>
				  </div>
				</div>
				
				<!-- Proceed to Checkout button - shown/hidden dynamically -->
				<a class="btn btn-block btn-dark w-100 mb-3" href="{{ route('frontend.checkout') }}" id="checkoutBtn" style="display: none;">
					<i class="lni lni-shopping-basket me-2"></i>Proceed to Checkout  
				</a>
				
				<!-- Static checkout button for when cart has items (server-side rendered) -->
				<!-- @if($cart && $cart->items->count() > 0)
				<noscript>
					<a class="btn btn-block btn-dark w-100 mb-3" href="{{ route('frontend.checkout') }}">
						<i class="lni lni-shopping-basket me-2"></i>Proceed to Checkout  dd
					</a>
				</noscript>
				@endif -->
				
				<a class="btn-link text-dark ft-medium" href="{{ route('frontend.shop') }}">
				  <i class="ti-back-left me-2"></i> Continue Shopping
				</a>
			</div>
			
		</div>
		
	</div>
</section>
<!-- ======================= Product Detail End ======================== -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check if user is logged in
    let isLoggedIn = false;
    
    // Check authentication status first
    $.ajax({
        url: '/api/auth/me',
        method: 'GET',
        async: false, // Synchronous to get result before proceeding
        success: function(response) {
            isLoggedIn = response.success && response.data;
        }
    });
    
    // Get session ID from localStorage (only needed for guest users)
    let sessionId = null;
    if (!isLoggedIn) {
        sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
    }
    
    // Check if session_id is in URL - remove it if user is logged in
    const urlParams = new URLSearchParams(window.location.search);
    const sessionIdParam = urlParams.get('session_id');
    
    if (isLoggedIn && sessionIdParam) {
        // User is logged in but URL has session_id - remove it
        urlParams.delete('session_id');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    } else if (!isLoggedIn && !sessionIdParam && sessionId) {
        // Guest user - add session_id to URL if not present
        const newUrl = window.location.pathname + '?session_id=' + sessionId;
        window.history.replaceState({}, '', newUrl);
    }
    
    // Load cart data
    function loadCartData() {
        console.log('Loading cart - isLoggedIn:', isLoggedIn, 'sessionId:', sessionId);
        
        // Show loader, hide cart content
        $('#cartLoader').show();
        $('#cartItemsContainer').hide();
        
        const ajaxData = {};
        const ajaxHeaders = {};
        
        // Only include session_id if user is not logged in
        if (!isLoggedIn && sessionId) {
            ajaxData.session_id = sessionId;
            ajaxHeaders['X-Session-ID'] = sessionId;
        }
        
        $.ajax({
            url: '/api/cart',
            method: 'GET',
            headers: ajaxHeaders,
            data: ajaxData,
            success: function(response) {
                console.log('Cart API response:', response);
                
                // Hide loader
                $('#cartLoader').hide();
                
                if (response.success && response.data) {
                    const items = response.data.items || [];
                    console.log('Cart items count:', items.length);
                    
                    // Always update display with API data (it has the correct session_id)
                    updateCartDisplay(response.data);
                } else {
                    // If no items from API, show empty cart message
                    updateCartDisplay({ items: [], summary: {} });
                }
            },
            error: function(xhr) {
                console.error('Error loading cart:', xhr);
                
                // Hide loader
                $('#cartLoader').hide();
                
                // If AJAX fails, check if backend rendered items
                const backendItems = $('#cartItemsContainer').find('.list-group-item').length;
                console.log('Backend rendered items:', backendItems);
                
                if (backendItems === 0) {
                    // Show empty cart message only if no items found
                    $('#cartItemsContainer').show();
                    $('#emptyCartMessage').show();
                } else {
                    // Show backend rendered items as fallback
                    $('#cartItemsContainer').show();
                }
            }
        });
    }
    
    // Load cart data on page load (this will override backend-rendered content with correct session_id data)
    loadCartData();
    
    // Update cart display with API data
    function updateCartDisplay(cartData) {
        const items = cartData.items || [];
        const summary = cartData.summary || {};
        
        // Update cart items
        let itemsHtml = '';
        if (items.length > 0) {
            itemsHtml = '<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">';
            items.forEach(function(item) {
                const isOutOfStock = item.manage_stock && !item.in_stock;
                const stockBadge = isOutOfStock 
                    ? '<span class="badge bg-danger ms-2">Out of Stock</span>' 
                    : (item.manage_stock && item.available_stock !== null 
                        ? '<span class="badge bg-success ms-2">In Stock (' + item.available_stock + ')</span>' 
                        : '');
                
                itemsHtml += '<li class="list-group-item' + (isOutOfStock ? ' border-danger' : '') + '" data-cart-item-id="' + item.id + '">' +
                    '<div class="row align-items-center">' +
                    '<div class="col-3">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' +
                    '<img src="' + (item.image_url || '/frontend/images/product/sample-product.jpg') + '" alt="' + (item.product_name || '') + '" class="img-fluid">' +
                    '</a>' +
                    '</div>' +
                    '<div class="col d-flex align-items-center justify-content-between">' +
                    '<div class="cart_single_caption ps-2">' +
                    '<h4 class="product_title fs-md ft-medium mb-1 lh-1">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' + (item.product_name || '') + '</a>' +
                    stockBadge +
                    '</h4>' +
                    (item.variant_name ? '<p class="mb-3 lh-1"><span class="text-dark">' + item.variant_name + '</span></p>' : '') +
                    (isOutOfStock ? '<p class="text-danger mb-2"><small>Available stock: ' + (item.available_stock || 0) + ', Requested: ' + item.quantity + '</small></p>' : '') +
                 '<h4 class="fs-md ft-medium mb-3 lh-1">₹' + parseFloat(item.unit_price).toFixed(2) + '</h4>' +
                '<select class="mb-2 custom-select w-auto cart-item-quantity' 
                    + (isOutOfStock ? ' border-danger' : '') 
                    + '" data-cart-item-id="' + item.id + '" data-variant-id="' + (item.variant_id || '') + '">';

                const maxQty = item.manage_stock && item.available_stock !== null
                ? Math.max(1, item.available_stock)
                : 10;

                for (let i = 1; i <= maxQty; i++) {
                    itemsHtml += '<option value="' + i + '"' + (i === item.quantity ? ' selected' : '') + '>' + i + '</option>';
                }



                itemsHtml += '</select>' +
                    '</div>' +
                    '<div class="fls_last">' +
                    '<button class="close_slide gray remove-cart-item" data-cart-item-id="' + item.id + '" data-variant-id="' + (item.variant_id || '') + '">' +
                    '<i class="ti-close"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</li>';
            });
            itemsHtml += '</ul>';
        } else {
            itemsHtml = '<div class="alert alert-info text-center">' +
                '<p class="mb-0">Your cart is empty.</p>' +
                '<a href="{{ route("frontend.shop") }}" class="btn btn-dark mt-3">Continue Shopping</a>' +
                '</div>';
        }
        
        $('#cartItemsContainer').html(itemsHtml);
        
        // Show cart container after updating
        $('#cartItemsContainer').show();
        
        // Check if any items are out of stock
        const hasOutOfStockItems = items.some(item => item.manage_stock && !item.in_stock);
        
        // Show/hide coupon section and checkout button based on items and stock availability
        if (items.length > 0 && !hasOutOfStockItems) {
            $('#couponSection').show();
            $('#checkoutBtn').show().removeClass('disabled').prop('disabled', false);
            updateCouponSection(cartData.coupon, summary.discount_amount);
        } else {
            $('#couponSection').hide();
            if (hasOutOfStockItems) {
                // Show checkout button but disable it
                $('#checkoutBtn').show().addClass('disabled').prop('disabled', true);
                // Add warning message
                if ($('#outOfStockWarning').length === 0) {
                    $('#cartItemsContainer').prepend('<div id="outOfStockWarning" class="alert alert-warning mb-3"><i class="lni lni-warning me-2"></i>Some items in your cart are out of stock. Please remove them or adjust quantities before proceeding to checkout.</div>');
                }
            } else {
                $('#checkoutBtn').hide();
            }
        }
        
        // Update summary
        $('#cartSubtotal').text('₹' + parseFloat(summary.subtotal || 0).toFixed(2));
        
        // Show/hide discount row
        if (summary.discount_amount > 0) {
            if ($('#cartDiscount').length === 0) {
                $('#cartSubtotal').parent().after('<li class="list-group-item d-flex text-dark fs-sm ft-regular" id="cartDiscountRow"><span>Discount</span> <span class="ms-auto text-dark ft-medium text-success" id="cartDiscount">-₹' + parseFloat(summary.discount_amount || 0).toFixed(2) + '</span></li>');
            } else {
                $('#cartDiscount').text('-₹' + parseFloat(summary.discount_amount || 0).toFixed(2));
                $('#cartDiscountRow').show();
            }
        } else {
            // Hide discount row if no discount
            if ($('#cartDiscountRow').length > 0) {
                $('#cartDiscountRow').hide();
            }
        }
        
        $('#cartTax').text('₹' + parseFloat(summary.tax_amount || 0).toFixed(2));
        $('#cartShipping').text('₹' + parseFloat(summary.shipping_amount || 0).toFixed(2));
        $('#cartTotal').text('₹' + parseFloat(summary.total_amount || 0).toFixed(2));
        
        // Update cart count in header
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        } else if (window.updateCartCount) {
            window.updateCartCount();
        }
    }
    
    // Update coupon section
    function updateCouponSection(coupon, discountAmount) {
        if (!coupon) {
            // No coupon applied
            $('#couponCode').val('');
            // Ensure Apply button is shown
            if ($('#removeCouponBtn').length > 0) {
                $('#removeCouponBtn').replaceWith('<button class="btn btn-dark" type="submit">Apply</button>');
            }
            if ($('#couponForm button[type="submit"]').length === 0) {
                $('#couponForm .col-auto').html('<button class="btn btn-dark" type="submit">Apply</button>');
            }
            $('#couponMessage').hide();
        } else {
            // Coupon applied
            $('#couponCode').val(coupon.code);
            // Replace Apply button with Remove button if needed
            if ($('#removeCouponBtn').length === 0) {
                $('#couponForm button[type="submit"]').replaceWith('<button class="btn btn-danger" type="button" id="removeCouponBtn">Remove</button>');
            }
            $('#couponMessage').html('<small class="text-success">Coupon "' + coupon.code + '" applied. Discount: ₹' + parseFloat(discountAmount || 0).toFixed(2) + '</small>').show();
        }
    }
    
    // Load cart on page load
    loadCartData();
    
    // Update cart item quantity
    $(document).on('change', '.cart-item-quantity', function() {
        const $select = $(this);
        const cartItemId = $select.data('cart-item-id');
        const variantId = $select.data('variant-id');
        const quantity = parseInt($select.val());
        
        if (quantity < 1) {
            $select.val(1);
            return;
        }
        
        // Disable select while updating
        $select.prop('disabled', true);
        
        const updateData = {
            quantity: quantity,
            variant_id: variantId
        };
        const updateHeaders = {};
        
        // Only include session_id if user is not logged in
        if (!isLoggedIn && sessionId) {
            updateData.session_id = sessionId;
            updateHeaders['X-Session-ID'] = sessionId;
        }
        
        $.ajax({
            url: '/api/cart/items/' + (cartItemId || variantId),
            method: 'PUT',
            headers: updateHeaders,
            data: updateData,
            success: function(response) {
                if (response.success) {
                    // Reload cart data instead of full page reload
                    loadCartData();
                }
            },
            error: function(xhr) {
                $select.prop('disabled', false);
                const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message 
                    ? xhr.responseJSON.error.message 
                    : 'Failed to update cart item';
                if (typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: message,
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                }
            }
        });
    });
    
    // Remove cart item
    $(document).on('click', '.remove-cart-item', function() {
        const $btn = $(this);
        const cartItemId = $btn.data('cart-item-id');
        const variantId = $btn.data('variant-id');
        
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        $btn.prop('disabled', true);
        
        const removeData = {
            variant_id: variantId
        };
        const removeHeaders = {};
        
        // Only include session_id if user is not logged in
        if (!isLoggedIn && sessionId) {
            removeData.session_id = sessionId;
            removeHeaders['X-Session-ID'] = sessionId;
        }
        
        $.ajax({
            url: '/api/cart/items/' + (cartItemId || variantId),
            method: 'DELETE',
            headers: removeHeaders,
            data: removeData,
            success: function(response) {
                if (response.success) {
                    // Reload cart data instead of full page reload
                    loadCartData();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message 
                    ? xhr.responseJSON.error.message 
                    : 'Failed to remove cart item';
                if (typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: message,
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                }
            }
        });
    });
    
    // Apply coupon
    $('#couponForm').on('submit', function(e) {
        e.preventDefault();
        const couponCode = $('#couponCode').val().trim();
        
        if (!couponCode) {
            if (typeof Snackbar !== 'undefined') {
                Snackbar.show({
                    text: 'Please enter a coupon code',
                    pos: 'top-right',
                    showAction: false,
                    duration: 3000,
                    textColor: '#fff',
                    backgroundColor: '#dc3545'
                });
            }
            return;
        }
        
        const couponData = {
            coupon_code: couponCode
        };
        const couponHeaders = {};
        
        // Only include session_id if user is not logged in
        if (!isLoggedIn && sessionId) {
            couponData.session_id = sessionId;
            couponHeaders['X-Session-ID'] = sessionId;
        }
        
        $.ajax({
            url: '/api/cart/coupon',
            method: 'POST',
            headers: couponHeaders,
            data: couponData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({
                            text: response.message || 'Coupon applied successfully!',
                            pos: 'top-right',
                            showAction: false,
                            duration: 3000,
                            textColor: '#fff',
                            backgroundColor: '#28a745'
                        });
                    }
                    // Reload cart data to show updated totals
                    loadCartData();
                }
            },
            error: function(xhr) {
                const errorData = xhr.responseJSON && xhr.responseJSON.error;
                let message = 'Failed to apply coupon';
                
                if (errorData) {
                    if (errorData.message) {
                        message = errorData.message;
                    } else if (errorData.code) {
                        // Provide user-friendly messages based on error code
                        switch(errorData.code) {
                            case 'COUPON_NOT_FOUND':
                                message = 'Coupon code not found. Please check and try again.';
                                break;
                            case 'COUPON_INACTIVE':
                                message = 'This coupon is not active.';
                                break;
                            case 'COUPON_EXPIRED':
                                message = 'This coupon has expired.';
                                break;
                            case 'COUPON_NOT_STARTED':
                                message = 'This coupon is not yet valid.';
                                break;
                            case 'COUPON_LIMIT_REACHED':
                                message = 'This coupon has reached its usage limit.';
                                break;
                            case 'COUPON_MIN_ORDER_NOT_MET':
                                message = errorData.message || 'Minimum order amount not met for this coupon.';
                                break;
                            default:
                                message = errorData.message || message;
                        }
                    }
                }
                
                if (typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: message,
                        pos: 'top-right',
                        showAction: false,
                        duration: 4000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                }
            }
        });
    });
    
    // Remove coupon - use event delegation for dynamically created button
    $(document).on('click', '#removeCouponBtn', function(e) {
        e.preventDefault();
        
        const removeCouponData = {};
        const removeCouponHeaders = {};
        
        // Only include session_id if user is not logged in
        if (!isLoggedIn && sessionId) {
            removeCouponData.session_id = sessionId;
            removeCouponHeaders['X-Session-ID'] = sessionId;
        }
        
        $.ajax({
            url: '/api/cart/coupon',
            method: 'DELETE',
            headers: removeCouponHeaders,
            data: removeCouponData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({
                            text: response.message || 'Coupon removed successfully',
                            pos: 'top-right',
                            showAction: false,
                            duration: 3000,
                            textColor: '#fff',
                            backgroundColor: '#28a745'
                        });
                    }
                    // Reload cart data to show updated totals
                    loadCartData();
                }
            },
            error: function(xhr) {
                const errorData = xhr.responseJSON && xhr.responseJSON.error;
                const message = errorData && errorData.message 
                    ? errorData.message 
                    : 'Failed to remove coupon';
                if (typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: message,
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                }
            }
        });
    });
    
    // Update cart button (reloads cart data)
    $('#updateCartBtn').on('click', function() {
        loadCartData();
    });
    
    // Handle checkout button click - simple redirect (let backend handle auth)
    $(document).on('click', '#checkoutBtn', function(e) {
    e.preventDefault();

    const $btn = $(this);
    const url = $btn.attr('href');

    console.log('Navigating to:', url);

    $btn.prop('disabled', true)
        .html('<i class="fa fa-spinner fa-spin me-2"></i>Loading...');

    setTimeout(function () {
        window.location.href = url;
    }, 300);
});


});
</script>
@endpush

