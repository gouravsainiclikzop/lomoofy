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
				<div id="cartItemsContainer">
					@if($cart->items && $cart->items->count() > 0)
						<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">
							@foreach($cart->items as $item)
								@php
									$product = $item->product;
									$variant = $item->variant;
									
									// Get product image
									$imageUrl = $product->primaryImage 
										? asset('storage/' . $product->primaryImage->image_path)
										: ($product->images && $product->images->count() > 0
											? asset('storage/' . $product->images->first()->image_path)
											: asset('frontend/images/product/1.jpg'));
									
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
													@for($qty = 1; $qty <= 10; $qty++)
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
						<div class="alert alert-info text-center">
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
				<!-- Customer auth checked via JavaScript -->
				<a class="btn btn-block btn-dark w-100 mb-3" href="{{ route('frontend.checkout') }}" id="checkoutBtn" style="display: none;" data-requires-auth="true">Proceed to Checkout</a>
				
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
    // Get session ID from localStorage
    let sessionId = localStorage.getItem('session_id');
    if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
    }
    
    // Load cart data using session_id from localStorage
    function loadCartData() {
        $.ajax({
            url: '/api/cart',
            method: 'GET',
            headers: {
                'X-Session-ID': sessionId
            },
            data: {
                session_id: sessionId
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateCartDisplay(response.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading cart:', xhr);
            }
        });
    }
    
    // Update cart display with API data
    function updateCartDisplay(cartData) {
        const items = cartData.items || [];
        const summary = cartData.summary || {};
        
        // Update cart items
        let itemsHtml = '';
        if (items.length > 0) {
            itemsHtml = '<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">';
            items.forEach(function(item) {
                itemsHtml += '<li class="list-group-item" data-cart-item-id="' + item.id + '">' +
                    '<div class="row align-items-center">' +
                    '<div class="col-3">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' +
                    '<img src="' + (item.image_url || '/frontend/images/product/1.jpg') + '" alt="' + (item.product_name || '') + '" class="img-fluid">' +
                    '</a>' +
                    '</div>' +
                    '<div class="col d-flex align-items-center justify-content-between">' +
                    '<div class="cart_single_caption ps-2">' +
                    '<h4 class="product_title fs-md ft-medium mb-1 lh-1">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' + (item.product_name || '') + '</a>' +
                    '</h4>' +
                    (item.variant_name ? '<p class="mb-3 lh-1"><span class="text-dark">' + item.variant_name + '</span></p>' : '') +
                    '<h4 class="fs-md ft-medium mb-3 lh-1">₹' + parseFloat(item.unit_price).toFixed(2) + '</h4>' +
                    '<select class="mb-2 custom-select w-auto cart-item-quantity" data-cart-item-id="' + item.id + '" data-variant-id="' + (item.variant_id || '') + '">';
                for (let qty = 1; qty <= 10; qty++) {
                    itemsHtml += '<option value="' + qty + '" ' + (item.quantity == qty ? 'selected' : '') + '>' + qty + '</option>';
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
        
        // Show/hide coupon section and checkout button based on items
        if (items.length > 0) {
            $('#couponSection').show();
            $('#checkoutBtn').show();
            updateCouponSection(cartData.coupon, summary.discount_amount);
        } else {
            $('#couponSection').hide();
            $('#checkoutBtn').hide();
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
            $('#couponMessage').html('<small class="text-success">Coupon "' + coupon.code + '" applied. Discount: $' + parseFloat(discountAmount || 0).toFixed(2) + '</small>').show();
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
        
        $.ajax({
            url: '/api/cart/items/' + (cartItemId || variantId),
            method: 'PUT',
            headers: {
                'X-Session-ID': sessionId
            },
            data: {
                quantity: quantity,
                variant_id: variantId,
                session_id: sessionId
            },
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
        
        $.ajax({
            url: '/api/cart/items/' + (cartItemId || variantId),
            method: 'DELETE',
            headers: {
                'X-Session-ID': sessionId
            },
            data: {
                variant_id: variantId,
                session_id: sessionId
            },
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
        
        $.ajax({
            url: '/api/cart/coupon',
            method: 'POST',
            headers: {
                'X-Session-ID': sessionId
            },
            data: {
                coupon_code: couponCode,
                session_id: sessionId
            },
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
    
    // Remove coupon
    $('#removeCouponBtn').on('click', function() {
        $.ajax({
            url: '/api/cart/coupon',
            method: 'DELETE',
            headers: {
                'X-Session-ID': sessionId
            },
            data: {
                session_id: sessionId
            },
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
                    // Clear coupon section after removing
                    updateCouponSection(null, 0);
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
    
    // Handle checkout button click - check customer auth
    $(document).on('click', '#checkoutBtn[data-requires-auth="true"]', function(e) {
        e.preventDefault(); // Always prevent default first
        const checkoutUrl = $(this).attr('href');
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        
        // Verify customer is authenticated via session
        $.ajax({
            url: '/api/auth/me',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (!response.success || !response.data) {
                    // Not authenticated, show login
                    $('#login').modal('show');
                    return false;
                }
                // Authenticated, navigate to checkout
                window.location.href = checkoutUrl;
            },
            error: function(xhr) {
                // Not authenticated, show login
                $('#login').modal('show');
                return false;
            }
        });
    });
});
</script>
@endpush

