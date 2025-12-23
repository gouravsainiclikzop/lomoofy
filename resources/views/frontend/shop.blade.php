@extends('layouts.frontend')

@section('title', 'Shop - Lomoofy Industries')

@section('content')
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
									<!-- <div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#discount" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Discount</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="discount" data-parent="#discount">
											<div class="side-list no-border"> 
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="d1" class="checkbox-custom" name="d1" type="checkbox">
																	<label for="d1" class="checkbox-custom-label">80% Discount<span>22</span></label>
																</li>
																<li>
																	<input id="d2" class="checkbox-custom" name="d2" type="checkbox">
																	<label for="d2" class="checkbox-custom-label">60% Discount<span>472</span></label>
																</li>
																<li>
																	<input id="d3" class="checkbox-custom" name="d3" type="checkbox">
																	<label for="d3" class="checkbox-custom-label">50% Discount<span>170</span></label>
																</li>
																<li>
																	<input id="d4" class="checkbox-custom" name="d4" type="checkbox">
																	<label for="d4" class="checkbox-custom-label">40% Discount<span>170</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								  -->
									 
									
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
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="whitea8">
																<label class="form-option-label rounded-circle" for="whitea8"><span class="form-option-color rounded-circle blc7"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="bluea8">
																<label class="form-option-label rounded-circle" for="bluea8"><span class="form-option-color rounded-circle blc2"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="yellowa8">
																<label class="form-option-label rounded-circle" for="yellowa8"><span class="form-option-color rounded-circle blc5"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="pinka8">
																<label class="form-option-label rounded-circle" for="pinka8"><span class="form-option-color rounded-circle blc3"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="reda">
																<label class="form-option-label rounded-circle" for="reda"><span class="form-option-color rounded-circle blc4"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="greena">
																<label class="form-option-label rounded-circle" for="greena"><span class="form-option-color rounded-circle blc6"></span></label>
															</div>
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
														<select class="custom-select simple">
														  <option value="1" selected="">Default Sorting</option>
														  <option value="2">Sort by price: Low price</option>
														  <option value="3">Sort by price: Hight price</option>
														  <option value="4">Sort by rating</option>
														  <option value="5">Sort by trending</option>
														</select>
													</div>
													<div class="single_fitres">
														<a href="shop-style-5.html" class="simple-button active me-1"><i class="ti-layout-grid2"></i></a>
														<!-- <a href="shop-list-sidebar.html" class="simple-button"><i class="ti-view-list"></i></a> -->
													</div>
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
															@if($product['color_variants']->count() > 0)
																@foreach($product['color_variants'] as $colorIndex => $colorVariant)
																	@php
																		// Generate color ID from color name (lowercase, no spaces)
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
														<div class="elis_rty">
															<span class="ft-medium text-dark fs-sm product-price-{{ $index }}">
																@php
																	// Show price range from all variants using display prices (sale price if available, otherwise regular)
																	// When showing default product image, don't show strikethrough price
																	$minDisplayPrice = $product['min_display_price'] ?? $product['min_price'] ?? 0;
																	$maxDisplayPrice = $product['max_display_price'] ?? $product['max_price'] ?? 0;
																	
																	// Determine display price range (no strikethrough for default view)
																	if ($minDisplayPrice != $maxDisplayPrice && $maxDisplayPrice > 0) {
																		$displayPrice = '₹' . number_format($minDisplayPrice, 0) . ' - ₹' . number_format($maxDisplayPrice, 0);
																	} else {
																		$displayPrice = '₹' . number_format($minDisplayPrice, 0);
																	}
																@endphp
																{{ $displayPrice }}
															</span>
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
<script>
$(document).ready(function() {
    let currentPage = 1;
    let isLoading = false;
    
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
            
            // Initialize with dynamic values
            $priceSlider.ionRangeSlider({
                type: "double",
                min: minPrice,
                max: maxPrice,
                from: minPrice,
                to: maxPrice,
                step: 1,
                grid: true,
                grid_num: 4,
                prefix: "₹",
                prettify_enabled: true,
                prettify_separator: ",",
                onFinish: function(data) {
                    // Handle price filter change
                    filterProductsByPrice(data.from, data.to);
                }
            });
        }
    }, 100);
    
    // Filter products by price range
    function filterProductsByPrice(min, max) {
        // This will be called when price range changes
        // You can implement filtering logic here or reload products
        console.log('Price filter:', min, '-', max);
        // TODO: Implement price filtering to reload products with price filter
    }
    
    // Get session ID
    function getSessionId() {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        return sessionId;
    }
    
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
        
        let colorVariantsHtml = '';
        if (product.color_variants && product.color_variants.length > 0) {
            product.color_variants.forEach(function(colorVariant, colorIndex) {
                const colorId = (colorVariant.color || 'color' + colorIndex).toLowerCase().replace(/\s+/g, '') + (index + 1);
                colorVariantsHtml += `
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
        }
        
        const wishlistClass = product.in_wishlist ? 'wishlist-active' : '';
        const wishlistIcon = product.in_wishlist ? 'fas' : 'far';
        const wishlistStyle = product.in_wishlist ? 'style="color: #dc3545 !important;"' : '';
        const wishlistData = product.in_wishlist ? 'data-in-wishlist="1"' : 'data-in-wishlist="0"';
        
        let priceDisplay = '';
        if (product.min_display_price != product.max_display_price && product.max_display_price > 0) {
            priceDisplay = '₹' + Math.round(product.min_display_price) + ' - ₹' + Math.round(product.max_display_price);
        } else {
            priceDisplay = '₹' + Math.round(product.min_display_price);
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
                                ${colorVariantsHtml}
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
                            <div class="elis_rty">
                                <span class="ft-medium text-dark fs-sm product-price-${index}">${priceDisplay}</span>
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