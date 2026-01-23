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
		
			@include('frontend.partials.customer-sidebar')
			
			<div class="col-12 col-md-12 col-lg-8 col-xl-8 text-center">
				<!-- row -->
				<div class="row align-items-center" id="wishlistProductsContainer">
					@if(isset($wishlistProducts) && $wishlistProducts->count() > 0)
						@foreach($wishlistProducts as $index => $product)
							<!-- <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12"> -->
                            <div class="col-6 col-md-4 col-lg-3"> 
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
												<img class="card-img-top" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
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
													@php
														// Use first variant for pricing if available, otherwise use product-level pricing
														$firstColorVariant = ($product['color_variants'] && $product['color_variants']->isNotEmpty()) 
															? $product['color_variants']->first() 
															: null;
														$minDisplayPrice = $product['min_display_price'] ?? $product['min_price'] ?? 0;
														$maxDisplayPrice = $product['max_display_price'] ?? $product['max_price'] ?? 0;
														$hasPriceRange = $product['has_price_range'] ?? ($minDisplayPrice != $maxDisplayPrice && $maxDisplayPrice > 0);
														
														// Check if we have first_variant data (for single variant products)
														$hasFirstVariantData = isset($product['first_variant_price']) && !$hasPriceRange;
														
														if ($hasFirstVariantData) {
															// Use first variant data from controller
															$variantPrice = $product['first_variant_price'];
															$variantSalePrice = $product['first_variant_sale_price'] ?? null;
															$discountType = $product['first_variant_discount_type'] ?? null;
															$discountValue = $product['first_variant_discount_value'] ?? null;
															$discountActive = $product['first_variant_discount_active'] ?? false;
														} elseif ($firstColorVariant && !$hasPriceRange) {
															// Use variant-level pricing with discount from color variant
															$variantPrice = $firstColorVariant['price'] ?? $minDisplayPrice;
															$variantSalePrice = $firstColorVariant['sale_price'] ?? null;
															$discountType = $firstColorVariant['discount_type'] ?? null;
															$discountValue = $firstColorVariant['discount_value'] ?? null;
															$discountActive = $firstColorVariant['discount_active'] ?? false;
														} else {
															// Use product-level pricing for price ranges
															$variantPrice = $minDisplayPrice;
															$variantSalePrice = $product['min_sale_price'] ?? null;
															$discountType = null;
															$discountValue = null;
															$discountActive = false;
														}
													@endphp
													
													@if(($hasFirstVariantData || $firstColorVariant) && !$hasPriceRange)
														{{-- Use variant-level pricing when single variant --}}
														@include('frontend.partials.product-pricing-compact', [
															'price' => $variantPrice,
															'sale_price' => $variantSalePrice,
															'original_price' => $variantPrice,
															'discount_type' => $discountType,
															'discount_value' => $discountValue,
															'discount_active' => $discountActive,
															'gstType' => $product['gst_type'] ?? true,
															'gstPercentage' => $product['gst_percentage'] ?? 0,
															'compact' => true
														])
													@else
														{{-- Use price range display for multiple variants --}}
														@php
															$minPrice = $product['min_price'] ?? 0;
															$maxPrice = $product['max_price'] ?? 0;
															$minSalePrice = $product['min_sale_price'] ?? null;
															$hasSale = $product['has_sale'] ?? false;
														@endphp
														@if($hasSale && $minSalePrice)
															<div class="product-pricing-compact compact">
																<div class="pricing-main-compact">
																	<div class="d-flex align-items-baseline flex-wrap gap-1">
																		<span class="base-price-compact text-muted text-decoration-line-through fs-sm fw-normal me-1">
																			₹{{ number_format($minPrice, 2) }}
																			@if($hasPriceRange) - ₹{{ number_format($maxPrice, 2) }} @endif
																		</span>
																		<span class="final-price-compact theme-cl fw-bold fs-md" style="color: #dc3545;">
																			₹{{ number_format($minSalePrice, 2) }}
																			@if($product['max_sale_price'] && $minSalePrice != $product['max_sale_price'])
																				- ₹{{ number_format($product['max_sale_price'], 2) }}
																			@endif
																		</span>
																	</div>
																</div>
															</div>
														@else
															<span class="ft-medium fs-md text-dark">
																₹{{ number_format($minDisplayPrice, 2) }}
																@if($hasPriceRange) - ₹{{ number_format($maxDisplayPrice, 2) }} @endif
															</span>
														@endif
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

@push('styles')
<style>
    /* Make columns equal height */
    #wishlistProductsContainer .row {
        display: flex;
        flex-wrap: wrap;
    }
    
    #wishlistProductsContainer .row > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }
    
    /* Wishlist Product Card - Fixed Height */
    #wishlistProductsContainer .product_grid {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    #wishlistProductsContainer .product_grid .card-body {
        flex: 1 0 auto;
        display: flex;
        flex-direction: column;
    }
    
    /* Product Card Image Consistency - 4:5 Aspect Ratio */
    #wishlistProductsContainer .shop_thumb {
        width: 100%;
        padding-bottom: 125%; /* 5/4 = 1.25 = 125% for 4:5 aspect ratio */
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        aspect-ratio: 4 / 5; /* Modern browsers */
    }

    #wishlistProductsContainer .shop_thumb .card-img-top,
    #wishlistProductsContainer .shop_thumb > a {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #wishlistProductsContainer .shop_thumb .card-img-top,
    #wishlistProductsContainer .shop_thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }

    #wishlistProductsContainer .shop_thumb:hover .card-img-top,
    #wishlistProductsContainer .shop_thumb:hover img {
        transform: scale(1.05);
    }

    #wishlistProductsContainer .product_grid .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Ensure card footer stays at bottom */
    #wishlistProductsContainer .product_grid .card-footers {
        flex-shrink: 0;
        margin-top: auto;
    }
</style>
@endpush

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
    
    // Only redirect if session_id is not in URL AND we're online
    // Don't redirect when offline to avoid breaking the page
    if (!sessionIdParam && navigator.onLine) {
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
                },
                error: function(xhr, status, error) {
                    // If offline or error, don't redirect - stay on page
                    console.log('Wishlist AJAX error (possibly offline):', status, error);
                }
            });
        @else
            // If we have products but no session_id in URL, reload with it
            // Only if online
            if (navigator.onLine) {
                const newUrl = window.location.pathname + '?session_id=' + sessionId;
                window.location.href = newUrl;
            }
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
