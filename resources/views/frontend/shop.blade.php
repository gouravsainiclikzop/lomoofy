@extends('layouts.frontend')

@section('title', 'Shop - Lomoofy Industries')

@section('content')
@push('styles')
<style>
	.form-check-input:checked[type="checkbox"] {
		--bs-form-check-bg-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
		display: none !important;
	}
</style>
@endpush
			<!-- ======================= Shop Style 1 ======================== -->
 
			<section class="bg-cover d-none d-md-block" style="background:url({{ asset('frontend/images/banner-2.png') }}) no-repeat;">
				<div class="container">
					<div class="row align-items-center justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="text-left py-5 mt-3 mb-3">
								<h1 class="ft-medium mb-3">{{ $selectedCategory ? $selectedCategory->name : 'Shop' }}</h1>
								@if($parentCategories->count() > 0)
								<ul class="shop_categories_list m-0 p-0">
									@foreach($parentCategories as $parentCategory)
									<li>
										<a href="{{ route('frontend.shop') }}?category={{ $parentCategory->slug }}">
											{{ $parentCategory->name }}
										</a>
									</li>
									@endforeach
								</ul>
								@endif
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Shop Style 1 ======================== -->
			
			
			<!-- ======================= Filter Wrap Style 1 ======================== --> 
			<section class="py-3 br-bottom br-top d-none d-md-block">
				<div class="container">
					<div class="row align-items-center justify-content-between">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item">
										<a href="{{ route('frontend.index') }}">Home</a>
									</li>
									<li class="breadcrumb-item">
										<a href="{{ route('frontend.shop') }}">Shop</a>
									</li>
									@if($selectedCategory && count($breadcrumb) > 0)
										@foreach($breadcrumb as $index => $crumb)
											@if($index < count($breadcrumb) - 1)
												<li class="breadcrumb-item">
													<a href="{{ route('frontend.shop') }}?category={{ $crumb['slug'] }}">
														{{ $crumb['name'] }}
													</a>
												</li>
											@else
												<li class="breadcrumb-item active" aria-current="page">
													{{ $crumb['name'] }}
												</li>
											@endif
										@endforeach
									@endif
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>
			<!-- ============================= Filter Wrap ============================== -->
			
			<!-- ======================= Shop By Categories ======================== -->
			@if($selectedCategory && $childCategories->count() > 0)
			<section class="py-4 bg-light">
				<div class="container">
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<h5 class="ft-medium mb-4 shop-shop-by">Shop By</h5>
							<div class="shop-by-categories">
								<!-- Category Carousel -->
								<div class="category-carousel">
									@foreach($childCategories as $childCategory)
									<div class="category-carousel-item">
										<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}" class="category-tab-item">
											<div class="category-image-wrapper">
												@if($childCategory->image)
													<img src="{{ asset('storage/' . $childCategory->image) }}" alt="{{ $childCategory->name }}" class="category-image">
												@else
													<img src="{{ asset('frontend/images/logo.webp') }}" alt="{{ $childCategory->name }}" class="category-image">
												@endif
											</div>
											<div class="category-label">{{ $childCategory->name }}</div>
										</a>
									</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			@endif
			<!-- ======================= Shop By Categories ======================== -->
			
			<!-- ======================= All Product List ======================== -->
			<section class="middle">
				<div class="container">
					<div class="row">
						
						<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 p-xl-0">
							<div class="search-sidebar sm-sidebar border">
								<div class="search-sidebar-header p-3 border-bottom">
									<div class="d-flex justify-content-between align-items-center">
										<h5 class="mb-0">Filters</h5>
										<button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllFilters">
											<i class="fas fa-times me-1"></i>Clear All
										</button>
									</div>
								</div>
								<div class="search-sidebar-body">
								
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header px-3">
											<h4 class="mt-3">Categories</h4>
										</div>
										<div class="widget-boxed-body">
											<div class="side-list no-border">
												<div class="filter-card" id="shop-categories">
													@php
														// Use child categories if available, otherwise use parent categories
														$categoriesToShow = ($selectedCategory && $childCategories->count() > 0) 
															? $childCategories 
															: $parentCategories;
													@endphp
													
													@if($categoriesToShow->count() > 0)
														@foreach($categoriesToShow as $category)
															@php
																$hasChildren = $category->relationLoaded('children') && $category->children && $category->children->count() > 0;
																$categoryId = 'category-' . $category->id;
																$isExpanded = ($selectedCategory && $selectedCategory->id == $category->id) ? 'show' : '';
																$isCollapsed = ($selectedCategory && $selectedCategory->id == $category->id) ? '' : 'collapsed';
															@endphp
															
															<!-- Single Filter Card -->
															<div class="single_filter_card">
																@if($hasChildren)
																	<h5>
																		<a href="#{{ $categoryId }}" data-bs-toggle="collapse" class="{{ $isCollapsed }}" aria-expanded="{{ $isExpanded ? 'true' : 'false' }}" role="button">
																			{{ $category->name }}
																			<i class="accordion-indicator ti-angle-down"></i>
																		</a>
																	</h5> 
																	<div class="collapse {{ $isExpanded }}" id="{{ $categoryId }}" data-parent="#shop-categories">
																		<div class="card-body">
																			<div class="inner_widget_link">
																				<ul>
																					@foreach($category->children as $childCategory)
																						<li>
																							<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}">
																								{{ $childCategory->name }}
																							</a>
																						</li>
																					@endforeach
																				</ul>
																			</div>
																		</div>
																	</div>
																@else
																	<h5>
																		<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}">
																			{{ $category->name }}
																		</a>
																	</h5>
																@endif
															</div>
														@endforeach
													@else
														<div class="single_filter_card">
															<p class="text-muted mb-0">No categories available</p>
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#pricing" data-bs-toggle="collapse" aria-expanded="false" role="button">Pricing</a></h4>
										</div>
										<div class="widget-boxed-body collapse show" id="pricing" data-parent="#pricing">
											<div class="side-list no-border mb-4">
												<div class="rg-slider">
													<input type="text" class="js-range-slider" name="price_range" id="priceRangeSlider" value="" data-min="{{ $minPrice }}" data-max="{{ $maxPrice }}" />
												</div>		
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#size" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Size</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="size" data-parent="#size">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="text-left pb-0 pt-2">
															@if($availableSizes->count() > 0)
																@foreach($availableSizes as $size)
																	<div class="form-check form-option form-check-inline mb-2">
																		<input class="form-check-input size-filter" type="checkbox" name="sizes[]" id="size_{{ $loop->index }}" value="{{ $size }}">
																		<label class="form-option-label" for="size_{{ $loop->index }}">{{ $size }}</label>
																	</div>
																@endforeach
															@else
																<p class="text-muted mb-0">No sizes available</p>
															@endif
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#brands" data-bs-toggle="collapse" aria-expanded="false" role="button">Brands</a></h4>
										</div>
										<div class="widget-boxed-body collapse show" id="brands" data-parent="#brands">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															@if($brands->count() > 0)
																<ul class="no-ul-list">
																	@foreach($brands as $brand)
																		<li>
																			<input id="brand_{{ $brand['id'] }}" class="checkbox-custom brand-filter" name="brands[]" type="checkbox" value="{{ $brand['id'] }}">
																			<label for="brand_{{ $brand['id'] }}" class="checkbox-custom-label">{{ $brand['name'] }}<span>{{ $brand['count'] }}</span></label>
																		</li>
																	@endforeach
																</ul>
															@else
																<p class="text-muted mb-0">No brands available</p>
															@endif
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									 
 
									 
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#colors" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Colors</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="colors" data-parent="#colors">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="text-left">
															@if($availableColors && $availableColors->count() > 0)
																@foreach($availableColors as $color)
																	<div class="form-check form-option form-check-inline mb-1">
																		<input class="form-check-input" type="radio" name="colora8" id="{{ $color['id'] }}" data-color-name="{{ strtolower($color['name']) }}">
																		<label class="form-option-label rounded-circle" for="{{ $color['id'] }}">
																			<span class="form-option-color rounded-circle" style="background-color: {{ $color['code'] }}"></span>
																		</label>
																	</div>
																@endforeach
															@else
																<p class="text-muted small">No colors available</p>
															@endif
														</div>
													</div>
												</div>
											</div>
										</div>
									</div> 
								</div>
							</div>
						</div>
						
						<div class="col-xl-9 col-lg-8 col-md-12 col-sm-12"> 
							<div class="row">
								<div class="col-xl-12 col-lg-12 col-md-12">
									<div class="border mb-3 mfliud">
										<div class="row align-items-center py-2 m-0">
											<div class="col-xl-3 col-lg-4 col-md-5 col-sm-12">
												<h6 class="mb-0">{{ $products->count() }} {{ $products->count() == 1 ? 'Item' : 'Items' }} Found</h6>
											</div>
											
											<div class="col-xl-9 col-lg-8 col-md-7 col-sm-12">
												<div class="filter_wraps d-flex align-items-center justify-content-end m-start">
													<div class="single_fitres me-2 br-right">
														<select class="custom-select simple" id="sortSelect" name="sort">
														  <option value="1" {{ request('sort', '1') == '1' ? 'selected' : '' }}>Default Sorting</option>
														  <option value="2" {{ request('sort') == '2' ? 'selected' : '' }}>Sort by price: Low price</option>
														  <option value="3" {{ request('sort') == '3' ? 'selected' : '' }}>Sort by price: High price</option>
														  <option value="4" {{ request('sort') == '4' ? 'selected' : '' }}>Sort by rating</option>
														  <option value="5" {{ request('sort') == '5' ? 'selected' : '' }}>Sort by trending</option>
														</select>
													</div>
													<!-- <div class="single_fitres">
														<a href="shop-style-5.html" class="simple-button active me-1"><i class="ti-layout-grid2"></i></a> -->
														<!-- <a href="shop-list-sidebar.html" class="simple-button"><i class="ti-view-list"></i></a> -->
													<!-- </div> -->
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<!-- row -->
							<div class="row align-items-center rows-products">
								@if($products->count() > 0)
									@foreach($products as $index => $product)
										<div class="col-xl-4 col-lg-4 col-md-6 col-6">
											<div class="product_grid card b-0">
												@if($product['has_sale'])
													<div class="badge bg-success text-white position-absolute ft-regular ab-left text-upper">Sale</div>
												@elseif($product['is_new'])
													<div class="badge bg-info text-white position-absolute ft-regular ab-left text-upper">New</div>
												@elseif($product['is_featured'])
													<div class="badge bg-warning text-white position-absolute ft-regular ab-left text-upper">Hot</div>
												@endif
												
												<div class="card-body p-0">
													<div class="shop_thumb position-relative">
														<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">
															@php
																// Use product's featured/primary image by default (from product_images table)
																$productImage = $product['image_url'];
															@endphp
															<img class="card-img-top product-image-{{ $index }}" src="{{ $productImage }}" alt="{{ $product['name'] }}" data-default-image="{{ $productImage }}">
														</a>
														<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
															<div class="edlio">
																<a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium quick-view-btn" 
																   data-product-slug="{{ $product['slug'] }}"
																   data-product-index="{{ $index }}"
																   data-selected-color="">
																	<i class="fas fa-eye me-1"></i>Quick View
																</a>
															</div>
														</div>
													</div>
												</div>
												
												<div class="card-footer b-0 p-0 pt-2">
													<div class="d-flex align-items-start justify-content-between">
														<div class="text-left">
															@php
																// Priority: Show color if available, otherwise show one variable attribute (preferably size)
																$hasColor = $product['color_variants']->count() > 0;
																$firstColorVariant = $hasColor ? $product['color_variants']->first() : null;
																$availableVariableValues = $firstColorVariant['available_variable_values'] ?? [];
																
																// If no color, get variable attributes from product data
																if (!$hasColor && isset($product['variable_attributes'])) {
																	$availableVariableValues = $product['variable_attributes'];
																}
																
																// Determine which attribute to show (prefer size, otherwise first available)
																$attributeToShow = null;
																if (!$hasColor && !empty($availableVariableValues)) {
																	// Prefer size, otherwise get first attribute
																	if (isset($availableVariableValues['size'])) {
																		$attributeToShow = ['key' => 'size', 'values' => $availableVariableValues['size']];
																	} else {
																		$firstKey = array_key_first($availableVariableValues);
																		if ($firstKey) {
																			$attributeToShow = ['key' => $firstKey, 'values' => $availableVariableValues[$firstKey]];
																		}
																	}
																}
															@endphp
															
															@if($hasColor)
																{{-- Show Color Options Only --}}
																<div class="mb-2">
																	@foreach($product['color_variants'] as $colorIndex => $colorVariant)
																		@php
																			$colorId = strtolower(str_replace(' ', '', $colorVariant['color'] ?? 'color' . $colorIndex));
																		@endphp
																		<div class="form-check form-option form-check-inline mb-1">
																			<input 
																				class="form-check-input color-option" 
																				type="radio" 
																				name="color{{ $index + 1 }}" 
																				id="{{ $colorId }}{{ $index + 1 }}"
																				data-price="{{ $colorVariant['display_price'] ?? $colorVariant['price'] ?? 0 }}"
																				data-sale-price="{{ $colorVariant['sale_price'] ?? '' }}"
																				data-regular-price="{{ $colorVariant['price'] ?? 0 }}"
																				data-has-sale="{{ $colorVariant['has_sale'] ? '1' : '0' }}"
																				data-product-index="{{ $index }}"
																				data-variant-image="{{ $colorVariant['image'] ?? '' }}"
																				data-color-value="{{ $colorVariant['color'] ?? '' }}">
																			<label class="form-option-label small rounded-circle" for="{{ $colorId }}{{ $index + 1 }}">
																				<span class="form-option-color rounded-circle" style="background-color: {{ $colorVariant['color_code'] ?? '#ccc' }}"></span>
																			</label>
																		</div>
																	@endforeach 
																</div>
															@elseif($attributeToShow)
																{{-- Show One Variable Attribute (preferably size) --}}
																<div class="mb-2">
																	<div class="d-flex flex-wrap gap-1">
																		@foreach($attributeToShow['values'] as $attrValue)
																			<span class="badge bg-light text-dark border" style="font-size: 0.7rem; font-weight: normal;">
																				{{ $attrValue }}
																			</span>
																		@endforeach
																	</div>
																</div>
															@endif
														</div>
														<div class="text-right">
															@php
																$inWishlist = isset($product['in_wishlist']) && $product['in_wishlist'];
															@endphp
															<button class="btn auto btn_love snackbar-wishlist {{ $inWishlist ? 'wishlist-active' : '' }}" data-product-id="{{ $product['id'] }}" data-in-wishlist="{{ $inWishlist ? '1' : '0' }}">
																<i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart{{ $inWishlist ? ' text-danger wishlist-heart-red' : '' }}" style="{{ $inWishlist ? 'color: #dc3545 !important;' : '' }}"></i>
															</button> 
														</div>
													</div>
													<div class="text-left">
														<h5 class="fw-nornal fs-md mb-0 lh-1 mb-1">
															<a href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">{{ $product['name'] }}</a>
														</h5>
														<div class="elis_rty product-price-{{ $index }}">
															@php
																// Use first color variant if available for better discount display
																// Otherwise use product-level min/max prices
																$firstColorVariant = $product['color_variants']->first();
																$minDisplayPrice = $product['min_display_price'] ?? $product['min_price'] ?? 0;
																$maxDisplayPrice = $product['max_display_price'] ?? $product['max_price'] ?? 0;
																$hasPriceRange = ($minDisplayPrice != $maxDisplayPrice && $maxDisplayPrice > 0);
															@endphp
															
															@if($firstColorVariant && !$hasPriceRange)
																{{-- Use variant-level pricing when single variant --}}
																@include('frontend.partials.product-pricing-compact', [
																	'price' => $firstColorVariant['price'] ?? $minDisplayPrice,
																	'sale_price' => $firstColorVariant['sale_price'] ?? null,
																	'original_price' => $firstColorVariant['price'] ?? $minDisplayPrice,
																	'discount_type' => $firstColorVariant['discount_type'] ?? null,
																	'discount_value' => $firstColorVariant['discount_value'] ?? null,
																	'discount_active' => $firstColorVariant['discount_active'] ?? false,
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
																					₹{{ number_format($minPrice, 0) }}
																					@if($hasPriceRange) - ₹{{ number_format($maxPrice, 0) }} @endif
																				</span>
																				<span class="final-price-compact theme-cl fw-bold fs-md" style="color: #dc3545;">
																					₹{{ number_format($minSalePrice, 0) }}
																					@if($product['max_sale_price'] && $minSalePrice != $product['max_sale_price'])
																						- ₹{{ number_format($product['max_sale_price'], 0) }}
																					@endif
																				</span>
																			</div>
																		</div>
																	</div>
																@else
																	<span class="ft-medium text-dark fs-sm">
																		₹{{ number_format($minDisplayPrice, 0) }}
																		@if($hasPriceRange) - ₹{{ number_format($maxDisplayPrice, 0) }} @endif
																	</span>
																@endif
															@endif
														</div>
													</div>
												</div>
											</div>
										</div>
									@endforeach 
								@else
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
										<div class="text-center py-5">
											<p class="text-muted">No products found.</p>
										</div>
									</div>
								@endif
							</div>
							<!-- row -->
							
							@if(isset($hasMoreProducts) && $hasMoreProducts)
							<div class="row">
								<div class="col-xl-12 col-lg-12 col-md-12 text-center">
									<button type="button" id="loadMoreProducts" class="btn stretched-links borders m-auto" data-page="1" data-category="{{ $selectedCategory ? $selectedCategory->slug : '' }}" data-search="{{ request('search', '') }}">
										<i class="lni lni-reload me-2"></i>Load More
									</button>
								</div>
							</div>
							@endif
						</div>
						
					</div>
				</div>
			</section>
			<!-- ======================= All Product List ======================== -->
@endsection

@push('scripts')
{{-- Include cart pricing JavaScript helper --}}
@include('frontend.partials.product-pricing-cart-js')

<script>
    
$(document).ready(function() {
    let currentPage = 1;
    let isLoading = false;
    let filterTimeout = null;
    let initialMinPrice = parseInt('{{ $minPrice ?? 0 }}') || 0;
    let initialMaxPrice = parseInt('{{ $maxPrice ?? 1000 }}') || 1000;
    let currentMinPrice = initialMinPrice;
    let currentMaxPrice = initialMaxPrice;
    
    // Store the initial URL when page loads (before any filters are applied)
    const initialUrl = window.location.href;
    const initialUrlObj = new URL(initialUrl);
    const initialCategory = initialUrlObj.searchParams.get('category') || '';
    const initialSearch = initialUrlObj.searchParams.get('search') || ''; 
    // Get session ID function (used for wishlist checks)
    function getSessionId() {
        // First, try to get from cookie (set by server on initial page load)
        let sessionId = getCookie('wishlist_session_id');
        
        // If not in cookie, try localStorage
        if (!sessionId) {
            sessionId = localStorage.getItem('session_id');
        }
        
        // If still not found, generate a new one
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        
        // Sync to both cookie and localStorage for consistency
        document.cookie = 'wishlist_session_id=' + sessionId + '; path=/; max-age=31536000'; // 1 year
        localStorage.setItem('session_id', sessionId);
        
        return sessionId;
    }
    
    // Helper function to get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    // Initialize session ID on page load to ensure cookie is set
    getSessionId();
    
    // Initialize Category Carousel
    if ($('.category-carousel').length && typeof $.fn.slick !== 'undefined') {
        if ($('.category-carousel').children().length > 0 && !$('.category-carousel').hasClass('slick-initialized')) {
            $('.category-carousel').slick({
                slidesToShow: 6,
                slidesToScroll: 1,
                arrows: true,
                dots: false,
                infinite: true,
                autoplay: false,
                speed: 300,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
    }
    
    // Initialize Price Range Slider with dynamic values
    // Wait a bit to ensure custom.js has loaded, then override its initialization
    setTimeout(function() {
        const $priceSlider = $('#priceRangeSlider');
        if ($priceSlider.length && typeof $.fn.ionRangeSlider !== 'undefined') {
            const minPrice = parseInt($priceSlider.data('min')) || 0;
            const maxPrice = parseInt($priceSlider.data('max')) || 1000;
            
            // Destroy any existing slider instance
            if ($priceSlider.data('ionRangeSlider')) {
                $priceSlider.data('ionRangeSlider').destroy();
            }
            
            // Get current filter values from URL
            const urlParams = new URLSearchParams(window.location.search);
            const urlMinPrice = urlParams.get('min_price') ? parseInt(urlParams.get('min_price')) : minPrice;
            const urlMaxPrice = urlParams.get('max_price') ? parseInt(urlParams.get('max_price')) : maxPrice;
            
            // Initialize with dynamic values (use URL params if available, otherwise use defaults)
            $priceSlider.ionRangeSlider({
                type: "double",
                min: minPrice,
                max: maxPrice,
                from: urlMinPrice,
                to: urlMaxPrice,
                step: 1,
                grid: true,
                grid_num: 4,
                prefix: "₹",
                prettify_enabled: true,
                prettify_separator: ",",
                onChange: function(data) {
                    // Don't filter on every change, only on finish
                },
                onFinish: function(data) {
                    // Handle price filter change
                    console.log('Price slider changed:', data.from, '-', data.to);
                    filterProductsByPrice(data.from, data.to);
                }
            });
        }
    }, 100);
    
    // Collect all active filters
    function collectFilters(customMinPrice = null, customMaxPrice = null, clearCategory = false) {
        // Use initial category/search if clearing filters, otherwise use current
        let categoryValue = clearCategory ? (initialCategory || null) : ('{{ $selectedCategory ? $selectedCategory->slug : "" }}');
        let searchValue = clearCategory ? (initialSearch || '') : ('{{ request("search", "") }}');
        
        const filters = {
            category: categoryValue,
            search: searchValue,
            min_price: null,
            max_price: null,
            sizes: [],
            brands: [],
            colors: [],
            sort: $('#sortSelect').val() || '1'
        };
        
        // Use custom price values if provided, otherwise get from slider
        if (customMinPrice !== null && customMaxPrice !== null) {
            filters.min_price = parseInt(customMinPrice);
            filters.max_price = parseInt(customMaxPrice);
            console.log('Using custom price filter:', filters.min_price, '-', filters.max_price);
        } else {
            // Get price range from slider or use stored current values
            const $priceSlider = $('#priceRangeSlider');
            if ($priceSlider.length && $priceSlider.data('ionRangeSlider')) {
                const sliderData = $priceSlider.data('ionRangeSlider');
                const fromPrice = parseInt(sliderData.from) || currentMinPrice;
                const toPrice = parseInt(sliderData.to) || currentMaxPrice;
                
                // Update stored values
                currentMinPrice = fromPrice;
                currentMaxPrice = toPrice;
                
                // Only include price filter if it's different from initial values
                if (Math.abs(fromPrice - initialMinPrice) > 1 || Math.abs(toPrice - initialMaxPrice) > 1) {
                    filters.min_price = fromPrice;
                    filters.max_price = toPrice;
                    console.log('Price filter from slider:', fromPrice, '-', toPrice);
                }
            } else {
                // Fallback to stored values
                if (Math.abs(currentMinPrice - initialMinPrice) > 1 || Math.abs(currentMaxPrice - initialMaxPrice) > 1) {
                    filters.min_price = currentMinPrice;
                    filters.max_price = currentMaxPrice;
                    console.log('Price filter from stored values:', currentMinPrice, '-', currentMaxPrice);
                }
            }
        }
        
        // Get selected sizes
        $('.size-filter:checked').each(function() {
            filters.sizes.push($(this).val());
        });
        
        // Get selected brands
        $('.brand-filter:checked').each(function() {
            filters.brands.push($(this).val());
        });
        
        // Get selected colors (radio buttons)
        $('input[name^="colora8"]:checked').each(function() {
            // Get color name from data attribute - use attr() to get exact value
            let colorName = $(this).attr('data-color-name') || $(this).data('color-name') || '';
            
            // Convert to lowercase and trim to ensure consistency
            if (colorName) {
                colorName = colorName.toLowerCase().trim();
            }
            
            // Fallback: Extract color name from radio button ID if data attribute not available
            if (!colorName) {
                const radioId = $(this).attr('id') || '';
                // Map IDs to color names (fallback for old hardcoded colors)
                if (radioId.includes('white')) {
                    colorName = 'white';
                } else if (radioId.includes('blue')) {
                    colorName = 'blue';
                } else if (radioId.includes('yellow')) {
                    colorName = 'yellow';
                } else if (radioId.includes('pink')) {
                    colorName = 'pink';
                } else if (radioId.includes('red')) {
                    colorName = 'red';
                } else if (radioId.includes('green')) {
                    colorName = 'green';
                } else if (radioId.includes('black')) {
                    colorName = 'black';
                } else if (radioId.includes('gray') || radioId.includes('grey')) {
                    colorName = 'gray';
                } else if (radioId.includes('orange')) {
                    colorName = 'orange';
                } else if (radioId.includes('purple')) {
                    colorName = 'purple';
                } else if (radioId.includes('brown')) {
                    colorName = 'brown';
                }
            }
            
            if (colorName) {
                filters.colors.push(colorName);
                console.log('Color filter added:', colorName, 'from ID:', $(this).attr('id'), 'data-color-name:', $(this).attr('data-color-name'));
            }
        });
        
        return filters;
    }
    
    // Apply filters and reload products
    function applyFilters(customMinPrice = null, customMaxPrice = null, clearCategory = false) {
        if (isLoading) return;
        
        isLoading = true;
        const filters = collectFilters(customMinPrice, customMaxPrice, clearCategory);
        
        // Show loading state
        $('.rows-products').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Filtering products...</p></div>');
        
        // Build query string for URL
        const params = new URLSearchParams();
        if (filters.category) params.append('category', filters.category);
        if (filters.search) params.append('search', filters.search);
        if (filters.sort && filters.sort !== '1') params.append('sort', filters.sort);
        if (filters.min_price !== null && filters.min_price !== undefined) {
            params.append('min_price', filters.min_price);
        }
        if (filters.max_price !== null && filters.max_price !== undefined) {
            params.append('max_price', filters.max_price);
        }
        filters.sizes.forEach(size => params.append('sizes[]', size));
        filters.brands.forEach(brand => params.append('brands[]', brand));
        filters.colors.forEach(color => {
            params.append('colors[]', color);
            console.log('Adding color to filter:', color);
        });
        
        console.log('Applying filters:', filters);
        console.log('Colors array:', filters.colors);
        
        // Update URL without reload
        const newUrl = '{{ route("frontend.shop") }}?' + params.toString();
        window.history.pushState({}, '', newUrl);
        
        // Clean up filters - remove empty arrays and null values to avoid backend issues
        const cleanFilters = {};
        
        // Only include non-empty values
        if (filters.category) {
            cleanFilters.category = filters.category;
        }
        if (filters.search) {
            cleanFilters.search = filters.search;
        }
        if (filters.sort && filters.sort !== '1') {
            cleanFilters.sort = filters.sort;
        }
        if (filters.min_price !== null && filters.min_price !== undefined) {
            cleanFilters.min_price = filters.min_price;
        }
        if (filters.max_price !== null && filters.max_price !== undefined) {
            cleanFilters.max_price = filters.max_price;
        }
        
        // Only include arrays if they have values
        if (filters.sizes && filters.sizes.length > 0) {
            cleanFilters.sizes = filters.sizes;
        }
        if (filters.brands && filters.brands.length > 0) {
            cleanFilters.brands = filters.brands;
        }
        if (filters.colors && filters.colors.length > 0) {
            cleanFilters.colors = filters.colors;
        }
        
        console.log('Sending clean filters:', cleanFilters);
        
        // Add session_id to filters for wishlist check
        cleanFilters.session_id = getSessionId();
        
        // Make AJAX request with X-Requested-With header to identify as AJAX
        console.log('Sending AJAX request with filters:', cleanFilters);
        
        $.ajax({
            url: '{{ route("frontend.shop") }}',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Session-ID': cleanFilters.session_id
            },
            data: cleanFilters,
            success: function(response) {
                console.log('AJAX response received, length:', response.length);
                try {
                    // Remove DOCTYPE and html/head/body tags if present to avoid Quirks Mode issues
                    let cleanResponse = response.toString();
                    
                    // Remove DOCTYPE declaration if present
                    cleanResponse = cleanResponse.replace(/<!DOCTYPE[^>]*>/i, '');
                    // Remove html, head, body tags but keep their content
                    cleanResponse = cleanResponse.replace(/<\/?html[^>]*>/gi, '');
                    cleanResponse = cleanResponse.replace(/<\/?head[^>]*>[\s\S]*?<\/head>/gi, '');
                    cleanResponse = cleanResponse.replace(/<\/?body[^>]*>/gi, '');
                    
                    // Parse HTML response to extract products section using jQuery
                    const $response = $('<div>').html(cleanResponse);
                    
                    // Find the products container - be very specific to avoid header content
                    // Look for .row.align-items-center.rows-products which is the exact structure
                    let $productsContainer = $response.find('.row.align-items-center.rows-products');
                    
                    // If not found, try .rows-products and find the row inside it
                    if ($productsContainer.length === 0) {
                        const $rowsProductsDiv = $response.find('.rows-products');
                        if ($rowsProductsDiv.length > 0) {
                            // Find the row inside .rows-products that has product cards
                            $productsContainer = $rowsProductsDiv.find('.row').has('.product_grid, .col-xl-4').first();
                        }
                    }
                    
                    // If still not found, find any row that has product_grid cards (but exclude header rows)
                    if ($productsContainer.length === 0 || $productsContainer.find('.product_grid').length === 0) {
                        const $allRows = $response.find('.row');
                        $allRows.each(function() {
                            const $row = $(this);
                            // Skip header rows (they have specific classes or don't have product_grid)
                            if ($row.hasClass('hide-ipad') || $row.find('.top_first, .top_second').length > 0) {
                                return; // Skip this row
                            }
                            // Check if this row has product cards
                            if ($row.find('.product_grid').length > 0) {
                                $productsContainer = $row;
                                return false; // Break the loop
                            }
                        });
                    }
                    
                    // Get the HTML from the container - verify it contains product_grid
                    let $productsHtml = null;
                    if ($productsContainer.length > 0) {
                        // Count actual product_grid elements (not just col-xl-4 which might be header)
                        const productCardCount = $productsContainer.find('.product_grid').length;
                        
                        // Verify the HTML contains product_grid before using it
                        const containerHtml = $productsContainer.html();
                        if (productCardCount > 0 && containerHtml && containerHtml.includes('product_grid')) {
                            $productsHtml = containerHtml;
                            console.log('Extracted HTML from container with', productCardCount, 'product cards');
                        } else {
                            console.warn('Container found but no product_grid in HTML. Card count:', productCardCount, 'HTML contains product_grid:', containerHtml ? containerHtml.includes('product_grid') : false);
                        }
                    }
                    
                    // Final fallback: directly find all product_grid elements and get their parent row
                    if (!$productsHtml || $productsHtml.length < 500 || !$productsHtml.includes('product_grid')) {
                        const $firstProductCard = $response.find('.product_grid').first();
                        if ($firstProductCard.length > 0) {
                            const $productRow = $firstProductCard.closest('.row');
                            // Make sure it's not a header row
                            if ($productRow.length > 0 && 
                                !$productRow.hasClass('hide-ipad') && 
                                $productRow.find('.top_first, .top_second').length === 0 &&
                                $productRow.find('.product_grid').length > 0) {
                                $productsContainer = $productRow;
                                $productsHtml = $productRow.html();
                                console.log('Found products using product_grid parent row (fallback method)');
                            }
                        }
                    }
                    
                    // Find item count more specifically - look for the one that contains "Item" or "Items"
                    let $itemCountElement = $response.find('h6.mb-0').filter(function() {
                        const text = $(this).text();
                        return text.includes('Item') || text.includes('Found');
                    }).first();
                    
                    // Fallback: find any element with mb-0 that contains "Item" or "Found"
                    if ($itemCountElement.length === 0) {
                        $itemCountElement = $response.find('.mb-0').filter(function() {
                            const text = $(this).text();
                            return text.includes('Item') || text.includes('Found');
                        }).first();
                    }
                    
                    const $itemCount = $itemCountElement.length > 0 ? $itemCountElement.text() : null;
                    const $loadMoreSection = $response.find('#loadMoreProducts').closest('.row');
                    
                    console.log('Products container found:', $productsContainer.length > 0 ? 'Yes' : 'No');
                    console.log('Products container class:', $productsContainer.length > 0 ? $productsContainer.attr('class') : 'N/A');
                    console.log('Product cards in container:', $productsContainer.length > 0 ? $productsContainer.find('.col-xl-4, .product_grid').length : 0);
                    console.log('Products HTML found:', $productsHtml ? 'Yes' : 'No');
                    console.log('Products HTML length:', $productsHtml ? $productsHtml.length : 0);
                    console.log('Products HTML preview:', $productsHtml ? $productsHtml.substring(0, 500) : 'N/A');
                    console.log('Item count element found:', $itemCountElement.length > 0 ? 'Yes' : 'No');
                    console.log('Item count text:', $itemCount);
                    
                    // Also check for products in the entire response
                    const totalProductCards = $response.find('.product_grid, .col-xl-4').length;
                    console.log('Total product cards in response:', totalProductCards);
                    
                    // Check if products HTML actually contains product cards
                    const productsHtmlHasCards = $productsHtml && ($productsHtml.includes('product_grid') || $productsHtml.includes('col-xl-4'));
                    
                    if ($productsHtml && $productsHtml.trim() !== '' && productsHtmlHasCards) {
                        // Update products
                        $('.rows-products').html($productsHtml);
                        
                        // Count actual product cards
                        const actualProductCount = $('.rows-products .col-xl-4').length;
                        console.log('Actual product count:', actualProductCount);
                        
                        // Update item count - use actual count if available, otherwise use response count
                        if (actualProductCount > 0) {
                            $('h6.mb-0').filter(function() {
                                return $(this).text().includes('Item') || $(this).text().includes('Found');
                            }).first().text(actualProductCount + ' ' + (actualProductCount == 1 ? 'Item' : 'Items') + ' Found');
                        } else if ($itemCount) {
                            $('h6.mb-0').filter(function() {
                                return $(this).text().includes('Item') || $(this).text().includes('Found');
                            }).first().text($itemCount);
                        }
                        
                        // Update load more button
                        if ($loadMoreSection.length > 0) {
                            if ($('#loadMoreProducts').length === 0) {
                                $('.rows-products').after($loadMoreSection);
                            } else {
                                $('#loadMoreProducts').closest('.row').replaceWith($loadMoreSection);
                            }
                        } else {
                            $('#loadMoreProducts').closest('.row').remove();
                        }
                        
                        // Reset current page for load more
                        currentPage = 1;
                        
                        // Reinitialize product interactions
                        initializeProductInteractions();
                    } else {
                        console.warn('No products HTML found in response');
                        console.log('Products container HTML:', $productsContainer.length > 0 ? $productsContainer[0].outerHTML.substring(0, 500) : 'Not found');
                        
                        // Check if there are actually products but HTML extraction failed
                        const productCards = $response.find('.col-xl-4, .product_grid').length;
                        console.log('Product cards found in response:', productCards);
                        
                        if (productCards > 0) {
                            // Products exist in response, extract them directly
                            let extractedHtml = null;
                            
                            // Method 1: Find all product cards and collect their HTML
                            const $allProductCards = $response.find('.product_grid').closest('.col-xl-4');
                            if ($allProductCards.length > 0) {
                                // Collect HTML of all product cards
                                extractedHtml = '';
                                $allProductCards.each(function() {
                                    extractedHtml += this.outerHTML;
                                });
                                console.log('Collected', $allProductCards.length, 'product cards directly');
                            }
                            
                            // Method 2: If Method 1 failed, try to get from the row containing product_grid
                            if (!extractedHtml || extractedHtml.length < 500) {
                                const $productRow = $response.find('.row').filter(function() {
                                    const $row = $(this);
                                    // Must have product_grid and not be a header row
                                    return $row.find('.product_grid').length > 0 && 
                                           !$row.hasClass('hide-ipad') && 
                                           $row.find('.top_first, .top_second').length === 0;
                                }).first();
                                
                                if ($productRow.length > 0) {
                                    extractedHtml = $productRow.html();
                                    console.log('Extracted from product row');
                                }
                            }
                            
                            // Method 3: Find the rows-products container and get its content
                            if (!extractedHtml || extractedHtml.length < 500 || !extractedHtml.includes('product_grid')) {
                                const $rowsProducts = $response.find('.rows-products');
                                if ($rowsProducts.length > 0) {
                                    // Get all product cards from within rows-products
                                    const $cardsInRowsProducts = $rowsProducts.find('.product_grid').closest('.col-xl-4');
                                    if ($cardsInRowsProducts.length > 0) {
                                        extractedHtml = '';
                                        $cardsInRowsProducts.each(function() {
                                            extractedHtml += this.outerHTML;
                                        });
                                        console.log('Extracted from rows-products container');
                                    }
                                }
                            }
                            
                            if (extractedHtml && extractedHtml.length > 500 && extractedHtml.includes('product_grid')) {
                                // Wrap in a row div if needed
                                if (!extractedHtml.includes('<div class="row')) {
                                    extractedHtml = '<div class="row align-items-center">' + extractedHtml + '</div>';
                                }
                                
                                $('.rows-products').html(extractedHtml);
                                const actualCount = $('.rows-products .product_grid').length;
                                console.log('Successfully loaded', actualCount, 'products');
                                
                                $('h6.mb-0').filter(function() {
                                    return $(this).text().includes('Item') || $(this).text().includes('Found');
                                }).first().text(actualCount + ' ' + (actualCount == 1 ? 'Item' : 'Items') + ' Found');
                                
                                // Update load more button if needed
                                const $loadMoreSection = $response.find('#loadMoreProducts').closest('.row');
                                if ($loadMoreSection.length > 0) {
                                    if ($('#loadMoreProducts').length === 0) {
                                        $('.rows-products').after($loadMoreSection);
                                    } else {
                                        $('#loadMoreProducts').closest('.row').replaceWith($loadMoreSection);
                                    }
                                } else {
                                    $('#loadMoreProducts').closest('.row').remove();
                                }
                                
                                initializeProductInteractions();
                            } else {
                                console.error('Failed to extract products HTML. Extracted length:', extractedHtml ? extractedHtml.length : 0, 'Contains product_grid:', extractedHtml ? extractedHtml.includes('product_grid') : false);
                                $('.rows-products').html('<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"><div class="text-center py-5"><p class="text-muted">No products found matching your filters.</p><p class="text-muted small">Try adjusting your filter criteria.</p></div></div>');
                                $('h6.mb-0').filter(function() {
                                    return $(this).text().includes('Item') || $(this).text().includes('Found');
                                }).first().text('0 Items Found');
                            }
                        } else {
                            $('.rows-products').html('<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"><div class="text-center py-5"><p class="text-muted">No products found matching your filters.</p><p class="text-muted small">Try adjusting your filter criteria.</p></div></div>');
                            $('h6.mb-0').filter(function() {
                                return $(this).text().includes('Item') || $(this).text().includes('Found');
                            }).first().text('0 Items Found');
                        }
                        $('#loadMoreProducts').closest('.row').remove();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    $('.rows-products').html('<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"><div class="text-center py-5"><p class="text-danger">Error loading products. Please refresh the page.</p></div></div>');
                }
                
                isLoading = false;
            },
            error: function(xhr) {
                console.error('Error filtering products:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                $('.rows-products').html('<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"><div class="text-center py-5"><p class="text-danger">Error loading products. Please try again.</p><button class="btn btn-primary mt-2" onclick="location.reload()">Refresh Page</button></div></div>');
                isLoading = false;
            }
        });
    }
    
    // Filter products by price range
    function filterProductsByPrice(min, max) {
        // Store the price values
        currentMinPrice = parseInt(min) || initialMinPrice;
        currentMaxPrice = parseInt(max) || initialMaxPrice;
        
        console.log('filterProductsByPrice called with:', min, '-', max);
        console.log('Stored values:', currentMinPrice, '-', currentMaxPrice);
        
        // Always apply filter when slider changes (user explicitly moved it)
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            // Pass price values directly to applyFilters
            applyFilters(min, max);
        }, 500); // Wait 500ms after user stops adjusting slider
    }
    
   

    // Clear all filters
    function clearAllFilters() {
        console.log('Clearing all filters...');
        
        // Reset stored price values
        currentMinPrice = initialMinPrice;
        currentMaxPrice = initialMaxPrice;
        
        // Reset price slider
        const $priceSlider = $('#priceRangeSlider');
        if ($priceSlider.length && $priceSlider.data('ionRangeSlider')) {
            const sliderInstance = $priceSlider.data('ionRangeSlider');
            sliderInstance.update({
                from: initialMinPrice,
                to: initialMaxPrice
            });
        }
        
        // Uncheck all size filters
        $('.size-filter').prop('checked', false);
        
        // Uncheck all brand filters
        $('.brand-filter').prop('checked', false);
        
        // Uncheck all color filters
        $('input[name^="colora8"]').prop('checked', false);
        
        // Reset sort to default
        $('#sortSelect').val('1');
        
        // Restore URL to initial state (keep category and search from initial URL)
        const restoreUrl = new URL(initialUrl);
        const params = new URLSearchParams();
        
        // Only keep category and search from initial URL
        if (initialCategory) {
            params.append('category', initialCategory);
        }
        if (initialSearch) {
            params.append('search', initialSearch);
        }
        
        const newUrl = '{{ route("frontend.shop") }}' + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
        
        console.log('Restored URL to:', newUrl);
        
        // Apply filters (which will now have no filters selected, but keep initial category/search)
        applyFilters(null, null, false); // Don't clear category, keep initial one
    }
    
    // Initialize product interactions (color options, wishlist, quick view)
    function initializeProductInteractions() {
        // Color option change handlers
        $(document).off('change', '.color-option').on('change', '.color-option', function() {
            return ; // return for now
            const $this = $(this);
            const productIndex = $this.data('product-index');
            const variantImage = $this.data('variant-image');
            const price = $this.data('price');
            const salePrice = $this.data('sale-price');
            const regularPrice = $this.data('regular-price');
            const hasSale = $this.data('has-sale') == '1';
            
            // Update product image
            if (variantImage) {
                $('.product-image-' + productIndex).attr('src', variantImage);
            }
            
            // Update product price using pricing component
            const priceContainer = $('.product-price-' + productIndex);
            if (priceContainer.length) {
                // Create a virtual variant object for pricing function
                const variantData = {
                    price: regularPrice,
                    sale_price: salePrice,
                    discount_type: null,
                    discount_value: null,
                    discount_active: false,
                    gst_type: true,
                    gst_percentage: 0
                };
                
                // Use pricing component JavaScript if available
                if (typeof generateCartItemPricing === 'function') {
                    const pricingHtml = generateCartItemPricing(variantData);
                    priceContainer.html(pricingHtml);
                } else {
                    // Fallback to simple price display
                    let priceDisplay = '₹' + Math.round(price);
                    if (hasSale && salePrice) {
                        priceDisplay = '<span class="text-muted text-decoration-line-through me-1">₹' + Math.round(regularPrice) + '</span>' +
                                       '<span class="theme-cl">₹' + Math.round(salePrice) + '</span>';
                    }
                    priceContainer.html(priceDisplay);
                }
            }
        });
    }
    
    // Initialize on page load
    initializeProductInteractions();
    
    // Filter event handlers with debouncing
    $('.size-filter').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    $('.brand-filter').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    // Color filter toggle - allow unselecting radio buttons
    // Store previous checked state to detect when user clicks on already-checked radio
    let previousCheckedColor = null;
    
    $('input[name^="colora8"]').on('mousedown', function(e) {
        const $this = $(this);
        previousCheckedColor = $this.is(':checked') ? $this.attr('id') : null;
    });
    
    $('input[name^="colora8"]').on('click', function(e) {
        const $this = $(this);
        // If this radio was already checked before mousedown, uncheck it
        if (previousCheckedColor === $this.attr('id') && $this.is(':checked')) {
            e.preventDefault();
            e.stopPropagation();
            // Uncheck the radio button
            $this.prop('checked', false);
            previousCheckedColor = null;
            // Trigger change event to apply filters
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
            return false;
        }
        // Otherwise, allow normal radio button behavior
    });
    
    $('input[name^="colora8"]').on('change', function() {
        // Only apply filters if the radio is actually checked (not unchecked)
        if ($(this).is(':checked')) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        }
    });
    
    // Sort select change handler
    $('#sortSelect').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    // Clear all filters button
    $('#clearAllFilters').on('click', function(e) {
        e.preventDefault();
        clearAllFilters();
    });
    
    // Load More Products
    $('#loadMoreProducts').on('click', function(e) {
        e.preventDefault();
        
        if (isLoading) return;
        
        isLoading = true;
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="lni lni-reload me-2 fa-spin"></i>Loading...');
        
        currentPage++;
        const category = $btn.data('category') || '';
        const search = $btn.data('search') || '';
        const sessionId = getSessionId();
        
        $.ajax({
            url: '{{ route("frontend.shop.load-more") }}',
            method: 'GET',
            data: {
                page: currentPage,
                category: category,
                search: search,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success && response.products && response.products.length > 0) {
                    // Get the current product index
                    const currentProductCount = $('.rows-products .col-xl-4').length;
                    
                    // Append new products
                    response.products.forEach(function(product, index) {
                        const productIndex = currentProductCount + index;
                        const productHtml = generateProductHtml(product, productIndex);
                        $('.rows-products').append(productHtml);
                    });
                    
                    // Update button state
                    if (!response.hasMore) {
                        $btn.closest('.row').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        $btn.prop('disabled', false).html(originalText);
                    }
                } else {
                    $btn.closest('.row').fadeOut(300, function() {
                        $(this).remove();
                    });
                }
                isLoading = false;
            },
            error: function(xhr) {
                console.error('Error loading more products:', xhr);
                $btn.prop('disabled', false).html(originalText);
                isLoading = false;
            }
        });
    });
    
    // Generate product HTML (same structure as in blade template)
    function generateProductHtml(product, index) {
        let badgeHtml = '';
        if (product.has_sale) {
            badgeHtml = '<div class="badge bg-success text-white position-absolute ft-regular ab-left text-upper">Sale</div>';
        } else if (product.is_new) {
            badgeHtml = '<div class="badge bg-info text-white position-absolute ft-regular ab-left text-upper">New</div>';
        } else if (product.is_featured) {
            badgeHtml = '<div class="badge bg-warning text-white position-absolute ft-regular ab-left text-upper">Hot</div>';
        }
        
        let attributesHtml = '';
        const hasColor = product.color_variants && product.color_variants.length > 0;
        
        if (hasColor) {
            // Show Color Options Only
            attributesHtml += '<div class="mb-2">';
            product.color_variants.forEach(function(colorVariant, colorIndex) {
                const colorId = (colorVariant.color || 'color' + colorIndex).toLowerCase().replace(/\s+/g, '') + (index + 1);
                attributesHtml += `
                    <div class="form-check form-option form-check-inline mb-1">
                        <input class="form-check-input color-option" type="radio" name="color${index + 1}" id="${colorId}"
                            data-price="${colorVariant.display_price || colorVariant.price || 0}"
                            data-sale-price="${colorVariant.sale_price || ''}"
                            data-regular-price="${colorVariant.price || 0}"
                            data-has-sale="${colorVariant.has_sale ? '1' : '0'}"
                            data-product-index="${index}"
                            data-variant-image="${colorVariant.image || ''}"
                            data-color-value="${colorVariant.color || ''}">
                        <label class="form-option-label small rounded-circle" for="${colorId}">
                            <span class="form-option-color rounded-circle" style="background-color: ${colorVariant.color_code || '#ccc'}"></span>
                        </label>
                    </div>
                `;
            });
            attributesHtml += '</div>';
        } else {
            // Show One Variable Attribute (preferably size)
            const firstColorVariant = product.color_variants && product.color_variants[0];
            const availableVariableValues = (firstColorVariant && firstColorVariant.available_variable_values) || product.variable_attributes || {};
            
            let attributeToShow = null;
            if (Object.keys(availableVariableValues).length > 0) {
                // Prefer size, otherwise get first attribute
                if (availableVariableValues.size && availableVariableValues.size.length > 0) {
                    attributeToShow = { key: 'size', values: availableVariableValues.size };
                } else {
                    const firstKey = Object.keys(availableVariableValues)[0];
                    if (firstKey && availableVariableValues[firstKey] && availableVariableValues[firstKey].length > 0) {
                        attributeToShow = { key: firstKey, values: availableVariableValues[firstKey] };
                    }
                }
            }
            
            if (attributeToShow) {
                attributesHtml += '<div class="mb-2">';
                attributesHtml += '<div class="d-flex flex-wrap gap-1">';
                attributeToShow.values.forEach(function(attrValue) {
                    attributesHtml += `
                        <span class="badge bg-light text-dark border" style="font-size: 0.7rem; font-weight: normal;">
                            ${attrValue}
                        </span>
                    `;
                });
                attributesHtml += '</div></div>';
            }
        }
        
        const wishlistClass = product.in_wishlist ? 'wishlist-active' : '';
        const wishlistIcon = product.in_wishlist ? 'fas' : 'far';
        const wishlistStyle = product.in_wishlist ? 'style="color: #dc3545 !important;"' : '';
        const wishlistData = product.in_wishlist ? 'data-in-wishlist="1"' : 'data-in-wishlist="0"';
        
        // Generate pricing HTML using first variant or price range
        let pricingHtml = '';
        const firstVariant = product.color_variants && product.color_variants.length > 0 ? product.color_variants[0] : null;
        const hasPriceRange = (product.min_display_price != product.max_display_price && product.max_display_price > 0);
        
        if (firstVariant && !hasPriceRange && typeof generateCartItemPricing === 'function') {
            // Use variant-level pricing with discount support
            const variantData = {
                price: firstVariant.price || product.min_display_price || 0,
                sale_price: firstVariant.sale_price || null,
                discount_type: firstVariant.discount_type || null,
                discount_value: firstVariant.discount_value || null,
                discount_active: firstVariant.discount_active || false,
                gst_type: true,
                gst_percentage: 0
            };
            pricingHtml = generateCartItemPricing(variantData);
        } else {
            // Use price range or simple display
            if (hasPriceRange) {
                if (product.has_sale && product.min_sale_price) {
                    pricingHtml = '<div class="product-pricing-compact compact"><div class="pricing-main-compact">' +
                        '<div class="d-flex align-items-baseline flex-wrap gap-1">' +
                        '<span class="base-price-compact text-muted text-decoration-line-through fs-sm fw-normal me-1">' +
                        '₹' + Math.round(product.min_price) + ' - ₹' + Math.round(product.max_price) + '</span>' +
                        '<span class="final-price-compact theme-cl fw-bold fs-md" style="color: #dc3545;">' +
                        '₹' + Math.round(product.min_sale_price) + ' - ₹' + Math.round(product.max_sale_price || product.min_sale_price) + '</span></div></div></div>';
                } else {
                    pricingHtml = '<span class="ft-medium text-dark fs-sm">₹' + Math.round(product.min_display_price) + ' - ₹' + Math.round(product.max_display_price) + '</span>';
                }
            } else {
                if (product.has_sale && product.min_sale_price && typeof generateCartItemPricing === 'function') {
                    const variantData = {
                        price: product.min_price || 0,
                        sale_price: product.min_sale_price,
                        discount_type: null,
                        discount_value: null,
                        discount_active: false,
                        gst_type: true,
                        gst_percentage: 0
                    };
                    pricingHtml = generateCartItemPricing(variantData);
                } else {
                    pricingHtml = '<span class="ft-medium text-dark fs-sm">₹' + Math.round(product.min_display_price) + '</span>';
                }
            }
        }
        
        return `
            <div class="col-xl-4 col-lg-4 col-md-6 col-6">
                <div class="product_grid card b-0">
                    ${badgeHtml}
                    <div class="card-body p-0">
                        <div class="shop_thumb position-relative">
                            <a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}?product=${product.slug}">
                                <img class="card-img-top product-image-${index}" src="${product.image_url}" alt="${product.name}" data-default-image="${product.image_url}">
                            </a>
                            <div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
                                <div class="edlio">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium quick-view-btn" 
                                       data-product-slug="${product.slug}"
                                       data-product-index="${index}"
                                       data-selected-color="">
                                        <i class="fas fa-eye me-1"></i>Quick View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer b-0 p-0 pt-2">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="text-left">
                                ${attributesHtml}
                            </div>
                            <div class="text-right">
                                <button class="btn auto btn_love snackbar-wishlist ${wishlistClass}" data-product-id="${product.id}" ${wishlistData}>
                                    <i class="${wishlistIcon} fa-heart${product.in_wishlist ? ' text-danger wishlist-heart-red' : ''}" ${wishlistStyle}></i>
                                </button> 
                            </div>
                        </div>
                        <div class="text-left">
                            <h5 class="fw-nornal fs-md mb-0 lh-1 mb-1">
                                <a href="{{ route('frontend.product') }}?product=${product.slug}">${product.name}</a>
                            </h5>
                            <div class="elis_rty product-price-${index}">
                                ${pricingHtml}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});
</script>
@endpush

@push('styles')
<style>
/* Clear Filters Button Styles */
#clearAllFilters {
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

#clearAllFilters:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.search-sidebar-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Product Card Image Consistency - 4:5 Aspect Ratio */
.shop_thumb {
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

.shop_thumb .card-img-top,
.shop_thumb > a {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shop_thumb .card-img-top,
.shop_thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}

.shop_thumb:hover .card-img-top,
.shop_thumb:hover img {
    transform: scale(1.05);
}

.product_grid .card-img-top {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Category Carousel Styles */
.category-carousel {
    position: relative;
}

.category-carousel .category-carousel-item {
    padding: 0 10px;
}

.category-carousel .slick-prev,
.category-carousel .slick-next {
    z-index: 1;
    width: 40px;
    height: 40px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.category-carousel .slick-prev {
    left: -20px;
}

.category-carousel .slick-next {
    right: -20px;
}

.category-carousel .slick-prev:before,
.category-carousel .slick-next:before {
    color: #333;
    font-size: 20px;
}

.category-carousel .slick-prev:hover,
.category-carousel .slick-next:hover {
    background: #151515;
    border-color: #151515;
}

.category-carousel .slick-prev:hover:before,
.category-carousel .slick-next:hover:before {
    color: #fff;
}

.category-carousel .category-tab-item {
    display: block;
    text-align: center;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.category-carousel .category-tab-item:hover {
    transform: translateY(-5px);
}

.category-carousel .category-image-wrapper {
    margin-bottom: 10px;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e0e0e0;
}

.category-carousel .category-image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.category-carousel .category-tab-item:hover .category-image {
    transform: scale(1.05);
}

.category-carousel .category-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-top: 8px;
}

.category-carousel .category-tab-item:hover .category-label {
    color: #151515;
}

@media (max-width: 768px) {
    .category-carousel .slick-prev {
        left: -10px;
    }
    
    .category-carousel .slick-next {
        right: -10px;
    }
    
    .category-carousel .slick-prev,
    .category-carousel .slick-next {
        width: 35px;
        height: 35px;
    }
}
</style>
@endpush