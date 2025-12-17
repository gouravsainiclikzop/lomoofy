@extends('layouts.frontend')

@section('title', 'Wishlist - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
						<li class="breadcrumb-item active" aria-current="page">Wishlist</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Dashboard Detail ======================== -->
<section class="middle">
	<div class="container">
		<div class="row justify-content-center justify-content-between">
		
			<div class="col-12 col-md-12 col-lg-4 col-xl-4 text-center miliods">
				<div class="d-block border rounded mfliud-bot">
					<div class="dashboard_author px-2 py-5">
						<div class="dash_auth_thumb circle p-1 border d-inline-flex mx-auto mb-2">
							<img src="{{ asset('frontend/images/team-1.jpg') }}" class="img-fluid circle" width="100" alt="" />
						</div>
						<div class="dash_caption">
							<h4 class="fs-md ft-medium mb-0 lh-1">Adam Wishnoi</h4>
							<span class="text-muted smalls">Australia</span>
						</div>
					</div>
					
					<div class="dashboard_author">
						<h4 class="px-3 py-2 mb-0 lh-2 gray fs-sm ft-medium text-muted text-uppercase text-left">Dashboard Navigation</h4>
						<ul class="dahs_navbar">
							<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
							<li><a href="{{ route('frontend.wishlist') }}" class="active"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
							<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
							<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
							<li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li>
							<li><a href="login.html"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
						</ul>
					</div>
					
				</div>
			</div>
			
			<div class="col-12 col-md-12 col-lg-8 col-xl-8 text-center">
				<!-- row -->
				<div class="row align-items-center" id="wishlistProductsContainer">
					@if(isset($wishlistProducts) && $wishlistProducts->count() > 0)
						@foreach($wishlistProducts as $index => $product)
							<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
								<div class="product_grid card b-0">
									@if($product['has_sale'])
										<div class="badge bg-success text-white position-absolute ft-regular ab-left text-upper">Sale</div>
									@elseif($product['is_new'])
										<div class="badge bg-info text-white position-absolute ft-regular ab-left text-upper">New</div>
									@elseif($product['is_featured'])
										<div class="badge bg-warning text-white position-absolute ft-regular ab-left text-upper">Hot</div>
									@endif
									<button class="btn btn_love position-absolute ab-right theme-cl remove-wishlist-btn" 
											data-wishlist-id="{{ $product['wishlist_id'] }}" 
											data-product-id="{{ $product['id'] }}">
										<i class="fas fa-times"></i>
									</button> 
									<div class="card-body p-0">
										<div class="shop_thumb position-relative">
											<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">
												@php
													$firstVariantImage = $product['color_variants']->first()['image'] ?? $product['image_url'];
												@endphp
												<img class="card-img-top" src="{{ $firstVariantImage }}" alt="{{ $product['name'] }}">
											</a>
											<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
												<div class="edlio">
													<a href="#" data-bs-toggle="modal" data-bs-target="#quickview" 
													   class="text-white fs-sm ft-medium quick-view-btn" 
													   data-product-slug="{{ $product['slug'] }}">
														<i class="fas fa-eye me-1"></i>Quick View
													</a>
												</div>
											</div>
										</div>
									</div>
									<div class="card-footers b-0 pt-3 px-2 bg-white d-flex align-items-start justify-content-center">
										<div class="text-left">
											<div class="text-center">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1">
													<a href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">{{ $product['name'] }}</a>
												</h5>
												<div class="elis_rty">
													@if($product['has_sale'] && $product['min_sale_price'])
														<span class="text-muted ft-medium line-through me-2">${{ number_format($product['min_price'], 0) }}</span>
														<span class="ft-medium theme-cl fs-md">${{ number_format($product['min_sale_price'], 0) }}</span>
													@else
														<span class="ft-medium fs-md text-dark">{{ $product['price_display'] }}</span>
													@endif
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						@endforeach
					@else
						<div class="col-12">
							<div class="text-center py-5">
								<i class="lni lni-heart" style="font-size: 64px; color: #ddd;"></i>
								<h4 class="ft-medium mt-3">Your wishlist is empty</h4>
								<p class="text-muted">Start adding products to your wishlist!</p>
								<a href="{{ route('frontend.shop') }}" class="btn btn-dark mt-3">Continue Shopping</a>
							</div>
						</div>
					@endif
				</div>
				<!-- row -->
			</div>
			
		</div>
	</div>
</section>
<!-- ======================= Dashboard Detail End ======================== -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Get session ID and load wishlist on page load
    function getSessionId() {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        return sessionId;
    }
    
    // Load wishlist items via AJAX if page is empty
    const sessionId = getSessionId();
    
    // Check if session_id is already in URL - if so, don't redirect
    const urlParams = new URLSearchParams(window.location.search);
    const sessionIdParam = urlParams.get('session_id');
    
    // Only redirect if session_id is not in URL
    if (!sessionIdParam) {
        @if(!isset($wishlistProducts) || $wishlistProducts->count() == 0)
            // Load wishlist via AJAX first
            $.ajax({
                url: '/api/wishlist',
                method: 'GET',
                data: { session_id: sessionId },
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        // Reload page with session_id to show products
                        const newUrl = window.location.pathname + '?session_id=' + sessionId;
                        window.location.href = newUrl;
                    }
                }
            });
        @else
            // If we have products but no session_id in URL, reload with it
            const newUrl = window.location.pathname + '?session_id=' + sessionId;
            window.location.href = newUrl;
        @endif
    }
    
    // Remove from wishlist
    $(document).on('click', '.remove-wishlist-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const wishlistId = $btn.data('wishlist-id');
        const productId = $btn.data('product-id');
        
        // Get session ID
        const sessionId = getSessionId();
        
        $.ajax({
            url: '/api/wishlist/' + wishlistId,
            method: 'DELETE',
            data: {
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    // Remove the product card
                    $btn.closest('.col-xl-4').fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if wishlist is now empty
                        if ($('.remove-wishlist-btn').length === 0) {
                            location.reload(); // Reload to show empty state
                        }
                    });
                    
                    // Show success message
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({
                            text: 'Product removed from wishlist',
                            pos: 'top-right',
                            showAction: false,
                            duration: 3000,
                            textColor: '#fff',
                            backgroundColor: '#151515'
                        });
                    }
                    
                    // Update wishlist count in header if exists
                    updateWishlistCount();
                }
            },
            error: function(xhr) {
                console.error('Error removing from wishlist:', xhr);
                alert('Failed to remove product from wishlist');
            }
        });
    });
    
    // Update wishlist count in header
    function updateWishlistCount() {
        const sessionId = getSessionId();
        $.ajax({
            url: '/api/wishlist/count',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                if (response.success) {
                    $('.dn-counter').text(response.count || '0');
                }
            }
        });
    }
});
</script>
@endpush
