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
					<h2>My Cart</h2>
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
					 <!-- cart items will be displayed here using js -->
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
			
			<div class="col-12 col-md-12 col-lg-4 cart-summary">
				<div class="card mb-4 gray mfliud">
				  <div class="card-body">
					<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x">
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Subtotal</span> <span class="ms-auto text-dark ft-medium" id="cartSubtotal"></span>
					  </li>
					  <!-- Discount row - always shown for apply coupon -->
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular" id="cartDiscountRow">
						<span>Discount</span> <span class="ms-auto text-dark ft-medium text-success" id="cartDiscount">₹0.00</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Shipping</span> <span class="ms-auto text-dark ft-medium" id="cartShipping"></span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-medium border-top">
						<span>Total (Incl. of all taxes)</span> <span class="ms-auto text-dark ft-bold" id="cartTotal"></span>
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
{{-- Include cart pricing JavaScript helper --}}
@include('frontend.partials.product-pricing-cart-js')

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
                    // If no items from API, show empty cart message with default summary
                    updateCartDisplay({ 
                        items: [], 
                        summary: {
                            subtotal: 0,
                            tax_amount: 0,
                            shipping_amount: 0,
                            discount_amount: 0,
                            total_amount: 0
                        }
                    });
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
        
        console.log('updateCartDisplay called with:', { itemsCount: items.length, summary: summary });
        
        // Update cart items
        let itemsHtml = '';
        if (items.length > 0) {
            itemsHtml = '<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">';
            items.forEach(function(item) {
                // Check out of stock: either explicitly marked as out_of_stock, or manage_stock is true and in_stock is false
                const isOutOfStock = item.out_of_stock || (item.manage_stock && !item.in_stock);
                const stockBadge = isOutOfStock 
                    ? '<span class="badge bg-danger ms-2">Out of Stock</span>' 
                    : (item.manage_stock && item.available_stock !== null && item.available_stock > 0
                        ? '<span class="badge bg-success ms-2">In Stock (' + item.available_stock + ')</span>' 
                        : '');
                
                itemsHtml += '<li class="list-group-item' + (isOutOfStock ? ' border-danger' : '') + '" data-cart-item-id="' + item.id + '">' +
                    '<div class="row align-items-center">' +
                    '<div class="col-3">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' +
                    '<img src="' + (item.image_url || '/assets/images/placeholder.jpg') + '" alt="' + (item.product_name || '') + '" class="img-fluid">' +
                    '</a>' +
                    '</div>' +
                    '<div class="col d-flex align-items-center justify-content-between">' +
                    '<div class="cart_single_caption ps-2">' +
                    '<h4 class="product_title fs-md ft-medium mb-1 lh-1">' +
                    '<a href="/product?product=' + (item.product_slug || '') + '">' + (item.product_name || '') + '</a>' +
                    stockBadge +
                    '</h4>' +
                    (item.all_attributes && item.all_attributes.length > 0 
                        ? item.all_attributes.map(function(attr) {
                            return '<p class="mb-1 lh-1"><span class="text-dark">' + (attr.label || '') + ': ' + (attr.value || '') + '</span></p>';
                        }).join('')
                        : ((item.size_value ? '<p class="mb-1 lh-1"><span class="text-dark">Size: ' + (item.size_value || '') + '</span></p>' : '') +
                           (item.color_value ? '<p class="mb-1 lh-1"><span class="text-dark">Color: ' + (item.color_value || '') + '</span></p>' : '') +
                           (item.variant_name && !item.size_value && !item.color_value ? '<p class="mb-1 lh-1"><span class="text-dark">' + item.variant_name + '</span></p>' : ''))
                    ) +
                    (isOutOfStock
                    ? '<p class="text-danger mb-2"><small>Available stock: ' + (item.available_stock || 0) + '</small></p>'
                    : ''
                    )+
                '<div class="mb-3">' + (typeof generateCartItemPricing === 'function' ? generateCartItemPricing(item) : (function(){ 
                    var up = parseFloat(item.unit_price) || 0;
                    var originalPrice = item.original_variant_price ? parseFloat(item.original_variant_price) : null;
                    var gstType = item.gst_type;
                    var gstPct = parseFloat(item.gst_percentage) || 0;
                    
                    // Normalize gstType
                    if (typeof gstType === 'string') {
                        gstType = (gstType === 'false' || gstType === '0') ? false : true;
                    }
                    
                    var displayPrice = up;
                    var taxLabel = '';
                    var gstAmount = 0;
                    
                    if (gstType === false && gstPct > 0) {
                        // Exclusive: Calculate inclusive price (base + GST)
                        // up is the base price for exclusive items
                        gstAmount = up * (gstPct / 100);
                        displayPrice = up + gstAmount; // Inclusive price
                        taxLabel = 'Incl. GST ' + Math.round(gstPct) + '% (₹' + gstAmount.toFixed(2) + ')';
                    } else {
                        // Inclusive: Use price as-is
                        if (originalPrice !== null) {
                            displayPrice = originalPrice;
                        }
                        taxLabel = 'Inclusive of all taxes';
                    }
                    
                    return '<h4 class="fs-md ft-medium mb-0 lh-1">₹' + displayPrice.toFixed(2) + ' <span class="text-muted fs-sm">(' + taxLabel + ')</span></h4>';
                })()) + '</div>' +
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
        const hasOutOfStockItems = items.some(item => item.out_of_stock || (item.manage_stock && !item.in_stock));
        
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
        
        // Helper function to calculate final price for an item (after discounts)
        const calculateItemFinalPrice = (item) => {
            let basePrice = parseFloat(item.variant_price || item.original_variant_price || item.unit_price || 0);
            const salePrice = item.variant_sale_price ? parseFloat(item.variant_sale_price) : null;
            const discountType = item.discount_type || '';
            const discountValue = parseFloat(item.discount_value || 0);
            const discountActive = item.discount_active === true || item.discount_active === '1' || item.discount_active === 1;
            
            // Round base price
            basePrice = Math.round(basePrice);
            
            // Round sale price if it exists
            let roundedSalePrice = null;
            if (salePrice !== null && salePrice !== undefined) {
                roundedSalePrice = Math.round(salePrice);
            }
            
            // Calculate final price
            let priceToDiscount = basePrice;
            if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
                priceToDiscount = roundedSalePrice;
            }
            
            let finalPrice = priceToDiscount;
            
            // Apply discount if active
            if (discountActive && discountType && discountValue > 0) {
                if (discountType === 'percentage') {
                    const discountAmount = (priceToDiscount * discountValue) / 100;
                    finalPrice = Math.max(0, priceToDiscount - discountAmount);
                } else if (discountType === 'amount' || discountType === 'flat') {
                    finalPrice = Math.max(0, priceToDiscount - discountValue);
                }
            } else if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
                finalPrice = roundedSalePrice;
            }
            
            // Round final price
            return Math.round(finalPrice);
        };
        
        // Recalculate subtotal from all items using inclusive prices (after discounts)
        // For exclusive items: base price + GST = inclusive price
        // For inclusive items: use price as-is
        let computedSubtotal = 0; // Subtotal (inclusive price after discounts)
        
        const itemsForCalc = items;
        for (let idx = 0; idx < itemsForCalc.length; idx++) {
            const it = itemsForCalc[idx];
            const quantity = parseInt(it.quantity) || 1;

            // Calculate final price for this item (after variant discounts)
            const itemFinalPrice = calculateItemFinalPrice(it);
            
            // Normalize gstType
            let gstType = (typeof it.gst_type !== 'undefined') ? it.gst_type : true;
            if (typeof gstType === 'string') {
                gstType = gstType === 'false' || gstType === '0' ? false : true;
            }
            const gstPct = parseFloat(it.gst_percentage) || 0;
            
            // Calculate inclusive price
            let inclusivePrice = itemFinalPrice;
            if (gstPct > 0 && gstType === false) {
                // Exclusive: Add GST to base price to get inclusive price
                inclusivePrice = itemFinalPrice + (itemFinalPrice * (gstPct / 100));
            }
            // For inclusive items, itemFinalPrice is already inclusive
            
            // Subtotal: inclusive price after discounts
            computedSubtotal += inclusivePrice * quantity;
        }
        
        // Update displayed subtotal
        computedSubtotal = computedSubtotal || 0;
        
        // Helper function to format price with 2 decimal places
        const formatPrice = (price) => {
            return '₹' + parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        };
        
        // Helper function to format rounded price (for payable only)
        const formatRoundedPrice = (price) => {
            const rounded = Math.round(price);
            return '₹' + rounded.toLocaleString();
        };
        
        // Keep subtotal with decimals (don't round)
        
        // Ensure all summary elements exist and update them
        const $subtotalEl = $('#cartSubtotal');
        if ($subtotalEl.length) {
            $subtotalEl.text(formatPrice(computedSubtotal));
        } else {
            console.error('cartSubtotal element not found');
        }
        
        // Always show discount row (for apply coupon functionality)
        const discountAmount = parseFloat(summary.discount_amount || 0);
        const $discountEl = $('#cartDiscount');
        if ($discountEl.length) {
            if (discountAmount > 0) {
                $discountEl.text('-' + formatPrice(discountAmount));
            } else {
                $discountEl.text('₹0.00');
            }
        }
        $('#cartDiscountRow').show();
        
        const shippingAmount = parseFloat(summary.shipping_amount || 0);
        const $shippingEl = $('#cartShipping');
        if ($shippingEl.length) {
            $shippingEl.text(formatPrice(shippingAmount));
        }
        
        // Calculate Total (Incl. of all taxes) following proper e-commerce flow:
        // 1. Subtotal (sum of all items with inclusive prices)
        // 2. Apply Discount (subtotal - discount)
        // 3. Add Shipping
        // Total = Subtotal - Discount + Shipping (all prices are already inclusive)
        const totalInclusive = computedSubtotal - discountAmount + shippingAmount;
        
        // Round total to whole number for display
        const totalRounded = Math.round(totalInclusive);
        const $totalEl = $('#cartTotal');
        if ($totalEl.length) {
            $totalEl.text(formatRoundedPrice(totalRounded));
        } else {
            console.error('cartTotal element not found');
        }
        
        console.log('Total calculation:', {
            computedSubtotal: computedSubtotal,
            discountAmount: discountAmount,
            shippingAmount: shippingAmount,
            totalInclusive: totalInclusive,
            totalRounded: totalRounded
        });
        
        // Force update visibility of summary section
        $('.cart-summary').show();
        
        // Update cart count in header
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        } else if (window.updateCartCount) {
            window.updateCartCount();
        }
        
        console.log('Cart summary update completed');
    }
    
    // Update coupon section
    function updateCouponSection(coupon, discountAmount) {
        if (!coupon) {
            // No coupon applied
            $('#couponCode').val('');
            // Ensure Apply button is shown
            const $colAuto = $('#couponForm .col-auto');
            if ($('#removeCouponBtn').length > 0) {
                $('#removeCouponBtn').remove();
            }
            // Check if submit button exists, if not add it
            if ($('#couponForm button[type="submit"]').length === 0) {
                $colAuto.html('<button class="btn btn-dark" type="submit">Apply</button>');
            } else {
                // Ensure it's an Apply button
                const $submitBtn = $('#couponForm button[type="submit"]');
                if ($submitBtn.attr('id') === 'removeCouponBtn') {
                    $submitBtn.replaceWith('<button class="btn btn-dark" type="submit">Apply</button>');
                }
            }
            $('#couponMessage').hide();
        } else {
            // Coupon applied
            $('#couponCode').val(coupon.code);
            // Replace Apply button with Remove button if needed
            const $submitBtn = $('#couponForm button[type="submit"]');
            if ($submitBtn.length > 0 && $submitBtn.attr('id') !== 'removeCouponBtn') {
                $submitBtn.replaceWith('<button class="btn btn-danger" type="button" id="removeCouponBtn">Remove</button>');
            } else if ($('#removeCouponBtn').length === 0) {
                $('#couponForm .col-auto').html('<button class="btn btn-danger" type="button" id="removeCouponBtn">Remove</button>');
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
    
    // Apply coupon - use event delegation to handle dynamically updated forms
    $(document).on('submit', '#couponForm', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
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
            return false;
        }
        
        // Disable submit button to prevent double submission
        const $submitBtn = $('#couponForm button[type="submit"]');
        const originalBtnText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Applying...');
        
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
                // Re-enable button
                $submitBtn.prop('disabled', false).text(originalBtnText);
                
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
                // Re-enable button
                $submitBtn.prop('disabled', false).text(originalBtnText);
                
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
        
        return false;
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

