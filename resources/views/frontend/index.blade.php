@extends('layouts.frontend')

@section('title', 'Home - Lomoofy Industries')

@section('content')
<!-- ============================ Hero Banner  Start================================== -->
			<div class="home-slider margin-bottom-0">

				<!-- Slide -->
				<div data-background-image="{{ asset('frontend/images/banner-2.png') }}" class="item">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<div class="home-slider-container">

									<!-- Slide Title -->
									<div class="home-slider-desc">
										<div class="home-slider-title mb-4">
											<h5 class="theme-cl fs-sm ft-ragular mb-0">Winter Collection</h5>
											<h1 class="mb-1 ft-bold lg-heading">New Winter<br>Collections 2021</h1>
											<span class="trending">There's nothing like trend</span>
										</div> 
										<a href="{{ route('frontend.shop') }}" class="btn stretched-links borders">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
									</div>
									<!-- Slide Title / End -->

								</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Slide -->
				<div data-background-image="{{ asset('frontend/images/banner-5.png') }}" class="item">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<div class="home-slider-container">

									<!-- Slide Title -->
									<div class="home-slider-desc">
										<div class="home-slider-title mb-4">
											<h5 class="theme-cl fs-sm ft-ragular mb-0">Winter Collection</h5>
											<h1 class="mb-1 ft-bold lg-heading">New Winter<br>Collections 2021</h1>
											<span class="trending">There's nothing like trend</span>
										</div>

										<a href="{{ route('frontend.shop') }}" class="btn stretched-links borders">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
									</div>
									<!-- Slide Title / End -->

								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Slide -->
				<div data-background-image="{{ asset('frontend/images/banner-3.png') }}" class="item">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<div class="home-slider-container">

									<!-- Slide Title -->
									<div class="home-slider-desc">
										<div class="home-slider-title mb-4">
											<h5 class="theme-cl fs-sm ft-ragular mb-0">Winter Collection</h5>
											<h1 class="mb-1 ft-bold lg-heading">New Winter<br>Collections 2021</h1>
											<span class="trending">There's nothing like trend</span>
										</div>

										<a href="{{ route('frontend.shop') }}" class="btn stretched-links borders">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
									</div>
									<!-- Slide Title / End -->

								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
			<!-- ============================ Hero Banner End ================================== -->
			

			
			<!-- ========================= Discover Our Collections section ========================== -->
			@if(isset($collections) && $collections->count() > 0)
			<section class="middle">
				<div class="container">
					<div class="row g-0">
						@php
							$collectionsList = $collections->take(4)->values();
							$categoryIds = $collectionsList->pluck('category_id')->filter()->unique()->toArray();
							$productCounts = [];
							
							if (!empty($categoryIds)) {
								// For each category, get unique published product count
								foreach($categoryIds as $catId) {
									// Get product IDs from primary category_id
									$primaryProductIds = \App\Models\Product::where('category_id', $catId)
										->where('status', 'published')
										->pluck('id')
										->toArray();
									
									// Get product IDs from product_categories pivot table (only published products)
									$pivotProductIds = \DB::table('product_categories')
										->join('products', 'product_categories.product_id', '=', 'products.id')
										->where('product_categories.category_id', $catId)
										->where('products.status', 'published')
										->distinct()
										->pluck('product_categories.product_id')
										->toArray();
									
									// Merge and get unique count (avoid double counting)
									$uniqueProductIds = array_unique(array_merge($primaryProductIds, $pivotProductIds));
									$productCounts[$catId] = count($uniqueProductIds);
								}
							}
						@endphp
						
						<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
							@if($collectionsList->count() > 0)
								@php $collection = $collectionsList[0]; @endphp
								<div class="single_cats">
									<a href="{{ $collection->category_id && $collection->category ? route('frontend.shop') . '?category=' . $collection->category->slug : '#' }}" class="cards card-overflow card-scale lg_height">
										<div class="bg-image" style="background:url({{ $collection->featured_image ? asset('storage/' . $collection->featured_image) : asset('frontend/images/b-8.png') }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $collection->title }}</h2>
												@if($collection->category_id && isset($productCounts[$collection->category_id]))
													<span>{{ $productCounts[$collection->category_id] }} Items</span>
												@endif
											</div>
											<div class="ct_footer left">
												<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							@endif
							
							@if($collectionsList->count() > 1)
								@php $collection = $collectionsList[1]; @endphp
								<div class="single_cats">
									<a href="{{ $collection->category_id && $collection->category ? route('frontend.shop') . '?category=' . $collection->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
										<div class="bg-image" style="background:url({{ $collection->featured_image ? asset('storage/' . $collection->featured_image) : asset('frontend/images/b-5.png') }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $collection->title }}</h2>
												@if($collection->category_id && isset($productCounts[$collection->category_id]))
													<span>{{ $productCounts[$collection->category_id] }} Items</span>
												@endif
											</div>
											<div class="ct_footer left">
												<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							@endif
						</div>
						
						<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12"> 
							<div class="row no-gutters"> 
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									@if($collectionsList->count() > 2)
										@php $collection = $collectionsList[2]; @endphp
										<div class="single_cats">
											<a href="{{ $collection->category_id && $collection->category ? route('frontend.shop') . '?category=' . $collection->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $collection->featured_image ? asset('storage/' . $collection->featured_image) : asset('frontend/images/b-3.png') }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $collection->title }}</h2>
														@if($collection->category_id && isset($productCounts[$collection->category_id]))
															<span>{{ $productCounts[$collection->category_id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									@endif
									
									@if($collectionsList->count() > 3)
										@php $collection = $collectionsList[3]; @endphp
										<div class="single_cats">
											<a href="{{ $collection->category_id && $collection->category ? route('frontend.shop') . '?category=' . $collection->category->slug : '#' }}" class="cards card-overflow card-scale lg_height">
												<div class="bg-image" style="background:url({{ $collection->featured_image ? asset('storage/' . $collection->featured_image) : asset('frontend/images/b-7.png') }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $collection->title }}</h2>
														@if($collection->category_id && isset($productCounts[$collection->category_id]))
															<span>{{ $productCounts[$collection->category_id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									@endif
								</div>
							</div>
							<!-- /row -->
						</div>
					</div>
				</div>
			</section>
			@endif
			<!-- ========================= Discover Our Collections section end ========================== -->
 

			<section class="space gray">
				<div class="container"> 
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Best Seller</h2>
								<h3 class="ft-bold pt-3">Best Seller</h3>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="slide_items">
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/8.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Half Running Set</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$119.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/9.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Formal Men Lowers</a></h5>
													<div class="elis_rty"><span class="text-muted ft-medium line-through me-2">$129.00</span><span class="ft-medium theme-cl fs-md">$79.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/10.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Half Running Suit</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$80.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-sold text-white position-absolute ft-regular ab-left text-upper">Sold Out</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/11.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Half Fancy Lady Dress</a></h5>
													<div class="elis_rty"><span class="text-muted ft-medium line-through me-2">$149.00</span><span class="ft-medium theme-cl fs-md">$110.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/12.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Flix Flox Jeans</a></h5>
													<div class="elis_rty"><span class="text-muted ft-medium line-through me-2">$90.00</span><span class="ft-medium theme-cl fs-md">$49.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/13.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Fancy Salwar Suits</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$114.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/14.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Collot Full Dress</a></h5>
													<div class="elis_rty"><span class="ft-medium theme-cl fs-md text-dark">$120.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</section>


			<section class="bg-cover" style="background:url({{ asset('frontend/images/bg-2.jpg') }}) no-repeat;" data-overlay="1">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-xl-8 col-lg-9 col-md-12 col-sm-12">
							
							<div class="deals_wrap text-center"> 
								<h2 class="ft-bold text-light">Only Summer Collections</h2>
								<p class="text-light">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio.</p>
								<div class="mt-5">
									<a href="{{ route('frontend.shop') }}" class="btn btn-white stretched-links">Start Shopping <i class="lni lni-arrow-right"></i></a>
								</div>
							</div>
							
						</div>
					</div>
				</div>
			</section>


			<section class="middle gray">
				<div class="container">
				
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">New Arrivals</h2>
								<h3 class="ft-bold pt-3">New Arrivals</h3>
							</div>
						</div>
					</div>
					
			<!-- row -->
			<div class="row align-items-center rows-products">
				@if($newArrivals->count() > 0)
					@foreach($newArrivals as $index => $product)
						<div class="col-xl-3 col-lg-4 col-md-6 col-6">
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
												// Debug logging
												if ($product['id'] == 14) {
													\Log::info('Product 14 Wishlist Check', [
														'product_id' => $product['id'],
														'in_wishlist' => $inWishlist,
														'product_data' => $product
													]);
												}
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
														$displayPrice = '$' . number_format($minDisplayPrice, 0) . ' - $' . number_format($maxDisplayPrice, 0);
													} else {
														$displayPrice = '$' . number_format($minDisplayPrice, 0);
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
				@endif
			</div>
					<!-- row --> 
				</div>
			</section>


			@if($parentCategories->count() > 0)
			<section class="p-0">
				<div class="container-fluid p-0">
					<div class="row g-0">
						@php
							// Calculate column class based on number of categories
							$categoryCount = $parentCategories->count();
							$colClass = 'col-xl-' . (12 / min($categoryCount, 3)) . ' col-lg-' . (12 / min($categoryCount, 3)) . ' col-md-' . (12 / min($categoryCount, 2)) . ' col-sm-12';
							$defaultImages = ['a-1.png', 'a-2.png', 'a-3.png'];
						@endphp
					
						@foreach($parentCategories as $index => $category)
						<div class="{{ $colClass }}">
							<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}" class="card card-overflow card-scale no-radius mb-0">
								<div class="bg-image" style="background:url({{ $category->image ? asset('storage/' . $category->image) : asset('frontend/images/' . ($defaultImages[$index] ?? 'a-1.png')) }})no-repeat;" data-overlay="2"></div>
								<div class="ct_body">
									<div class="ct_body_caption">	
										<h1 class="mb-0 ft-bold text-light">{{ $category->name }}</h1>
									</div>
									<div class="ct_footer">
										<span class="btn btn-white stretched-links">Shop {{ $category->name }} <i class="lni lni-arrow-right"></i>
										</span>
									</div>
								</div>
							</a>
						</div>
						@endforeach
						
					</div>
				</div>
			</section>
			@endif


			
			<!-- ======================= Recently Viewed ======================== -->
			<section class="space gray">
				<div class="container">
					
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Recently Viewed</h2>
								<h3 class="ft-bold pt-3">Recently Viewed </h3>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="slide_items">
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/1.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Stylish Leather Jacket</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$199.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/2.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Designer T-Shirt</a></h5>
													<div class="elis_rty"><span class="text-muted ft-medium line-through me-2">$89.00</span><span class="ft-medium theme-cl fs-md">$59.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/3.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Classic Denim Jeans</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$99.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/4.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Elegant Summer Dress</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$149.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/5.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Sporty Sneakers</a></h5>
													<div class="elis_rty"><span class="text-muted ft-medium line-through me-2">$129.00</span><span class="ft-medium theme-cl fs-md">$89.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/6.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Casual Polo Shirt</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$69.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right"><i class="far fa-heart"></i></button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/7.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Winter Wool Sweater</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$159.00</span></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</section>

 
			<!-- ======================= Customer Review ======================== -->
			<section class="gray">
				<div class="container">
					
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Testimonials</h2>
								<h3 class="ft-bold pt-3">Testimonials</h3>
							</div>
						</div>
					</div>
					
					<div class="row justify-content-center">
						<div class="col-xl-9 col-lg-10 col-md-12 col-sm-12">
							<div class="reviews-slide px-3">
								 
								<div class="single_review">
									<div class="sng_rev_thumb"><figure><img src="{{ asset('frontend/images/team-1.jpg') }}" class="img-fluid circle" alt=""></figure></div>
									<div class="sng_rev_caption text-center">
										<div class="rev_desc mb-4">
											<p class="fs-md">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>
										</div>
										<div class="rev_author">
											<h4 class="mb-0">Mark Jevenue</h4>
											<span class="fs-sm">CEO of Addle</span>
										</div>
									</div>
								</div>
								 
								<div class="single_review">
									<div class="sng_rev_thumb"><figure><img src="{{ asset('frontend/images/team-2.jpg') }}" class="img-fluid circle" alt=""></figure></div>
									<div class="sng_rev_caption text-center">
										<div class="rev_desc mb-4">
											<p class="fs-md">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>
										</div>
										<div class="rev_author">
											<h4 class="mb-0">Henna Bajaj</h4>
											<span class="fs-sm">Aqua Founder</span>
										</div>
									</div>
								</div>
								 
								<div class="single_review">
									<div class="sng_rev_thumb"><figure><img src="{{ asset('frontend/images/team-3.jpg') }}" class="img-fluid circle" alt=""></figure></div>
									<div class="sng_rev_caption text-center">
										<div class="rev_desc mb-4">
											<p class="fs-md">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>
										</div>
										<div class="rev_author">
											<h4 class="mb-0">John Cenna</h4>
											<span class="fs-sm">CEO of Plike</span>
										</div>
									</div>
								</div>
								 
								<div class="single_review">
									<div class="sng_rev_thumb"><figure><img src="{{ asset('frontend/images/team-4.jpg') }}" class="img-fluid circle" alt=""></figure></div>
									<div class="sng_rev_caption text-center">
										<div class="rev_desc mb-4">
											<p class="fs-md">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>
										</div>
										<div class="rev_author">
											<h4 class="mb-0">Madhu Sharma</h4>
											<span class="fs-sm">Team Manager</span>
										</div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Customer Review ======================== --> 
 
			<!-- ======================= Blog Start ============================ -->
			<section class="space min">
				<div class="container"> 
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Latest News</h2>
								<h3 class="ft-bold pt-3">Latest Updates</h3>
							</div>
						</div>
					</div>
					
					<div class="row">
						
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
							<div class="_blog_wrap">
								<div class="_blog_thumb mb-2">
									<a href="blog-detail.html" class="d-block"><img src="{{ asset('frontend/images/bl-1.png') }}" class="img-fluid rounded" alt=""></a>
								</div>
								<div class="_blog_caption">
									<span class="text-muted">26 Jan 2021</span>
									<h5 class="bl_title lh-1"><a href="blog-detail.html">Let's start bring sale on this saummer vacation.</a></h5>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
									<a href="blog-detail.html" class="text-dark fs-sm">Continue Reading..</a>
								</div>
							</div>
						</div>
						
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
							<div class="_blog_wrap">
								<div class="_blog_thumb mb-2">
									<a href="blog-detail.html" class="d-block"><img src="{{ asset('frontend/images/bl-2.png') }}" class="img-fluid rounded" alt=""></a>
								</div>
								<div class="_blog_caption">
									<span class="text-muted">17 July 2021</span>
									<h5 class="bl_title lh-1"><a href="blog-detail.html">Let's start bring sale on this saummer vacation.</a></h5>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
									<a href="blog-detail.html" class="text-dark fs-sm">Continue Reading..</a>
								</div>
							</div>
						</div>
						
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
							<div class="_blog_wrap">
								<div class="_blog_thumb mb-2">
									<a href="blog-detail.html" class="d-block"><img src="{{ asset('frontend/images/bl-3.png') }}" class="img-fluid rounded" alt=""></a>
								</div>
								<div class="_blog_caption">
									<span class="text-muted">10 Aug 2021</span>
									<h5 class="bl_title lh-1"><a href="blog-detail.html">Let's start bring sale on this saummer vacation.</a></h5>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
									<a href="blog-detail.html" class="text-dark fs-sm">Continue Reading..</a>
								</div>
							</div>
						</div>
						
					</div>
					
				</div>
			</section>
			<!-- ======================= Blog Start ============================ -->
			



			<!-- ======================= Instagram Start ============================ -->
			<!-- <section class="p-0">
				<div class="container-fluid p-0">
					
					<div class="row no-gutters">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Instagram Gallery</h2>
								<span class="fs-lg ft-bold theme-cl pt-3">@mahak_71</span>
								<h3 class="ft-bold lh-1">From Instagram</h3>
							</div>
						</div>
					</div>
					
					<div class="row no-gutters">
						
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-1.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-2.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-3.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-7.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-8.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-4.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-5.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="javascript:void(0);" class="d-block"><img src="{{ asset('frontend/images/i-6.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
						
					</div>
					
				</div>
			</section> -->
			<!-- ======================= Instagram Start ============================ -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset all color options to unchecked state on page load
    // This prevents browser from restoring previous selection state
    document.querySelectorAll('.color-option').forEach(function(radio) {
        radio.checked = false;
    });
    
    // Reset all product images to default state on page load
    document.querySelectorAll('[class*="product-image-"]').forEach(function(img) {
        const defaultImage = img.getAttribute('data-default-image');
        if (defaultImage) {
            img.src = defaultImage;
        }
    });
    
    // Handle color selection and price updates for new arrivals
    document.querySelectorAll('.color-option').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const productIndex = this.getAttribute('data-product-index');
                const priceElement = document.querySelector('.product-price-' + productIndex);
                const imageElement = document.querySelector('.product-image-' + productIndex);
                const variantImage = this.getAttribute('data-variant-image');
                const selectedColorValue = this.getAttribute('data-color-value');
                
                // Update product image if variant image is available, otherwise keep default
                if (imageElement) {
                    if (variantImage) {
                        imageElement.src = variantImage;
                    } else {
                        // Revert to default product image if no variant image
                        const defaultImage = imageElement.getAttribute('data-default-image');
                        if (defaultImage) {
                            imageElement.src = defaultImage;
                        }
                    }
                }
                
                // Update price to show selected variant price
                if (priceElement) {
                    const displayPrice = parseFloat(this.getAttribute('data-price')) || 0;
                    const regularPrice = parseFloat(this.getAttribute('data-regular-price')) || 0;
                    const salePrice = this.getAttribute('data-sale-price');
                    const hasSale = this.getAttribute('data-has-sale') === '1' && salePrice;
                    
                    // Format price display for selected variant
                    let priceHtml = '';
                    if (hasSale && salePrice) {
                        priceHtml = '<span class="text-decoration-line-through text-muted me-1">$' + 
                                   Math.round(regularPrice).toLocaleString() + '</span>' +
                                   '$' + Math.round(displayPrice).toLocaleString();
                    } else {
                        priceHtml = '$' + Math.round(displayPrice).toLocaleString();
                    }
                    
                    priceElement.innerHTML = priceHtml;
                }

                // Update the data-selected-color attribute on the quick view button
                const quickViewButton = document.querySelector('.quick-view-btn[data-product-index="' + productIndex + '"]');
                if (quickViewButton && selectedColorValue) {
                    quickViewButton.setAttribute('data-selected-color', selectedColorValue);
                }
            }
        });
    });
});

// Debug wishlist status on page load
$(document).ready(function() {
    console.log('=== Wishlist Debug ===');
    $('.snackbar-wishlist').each(function() {
        const $btn = $(this);
        const productId = $btn.data('product-id');
        const inWishlist = $btn.data('in-wishlist') === '1';
        const hasFas = $btn.find('i').hasClass('fas');
        const hasActive = $btn.hasClass('wishlist-active');
        
        if (productId == 14) {
            console.log('Product 14 Details:', {
                productId: productId,
                inWishlist: inWishlist,
                hasFas: hasFas,
                hasActive: hasActive,
                iconClasses: $btn.find('i').attr('class'),
                buttonClasses: $btn.attr('class'),
                iconStyle: $btn.find('i').attr('style')
            });
        }
    });
    
    // Also check localStorage session_id
    const sessionId = localStorage.getItem('session_id');
    console.log('Session ID from localStorage:', sessionId);
    
    // Check wishlist via API
    if (sessionId) {
        $.ajax({
            url: '/api/wishlist',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                console.log('Wishlist API Response:', response);
                if (response.success && response.data) {
                    const productIds = response.data.map(item => item.product_id);
                    console.log('Products in wishlist:', productIds);
                    console.log('Is product 14 in wishlist?', productIds.includes(14));
                    
                    // Update hearts based on API response
                    response.data.forEach(function(item) {
                        const $btn = $('.snackbar-wishlist[data-product-id="' + item.product_id + '"]');
                        if ($btn.length) {
                            $btn.find('i').removeClass('far').addClass('fas').css('color', '#dc3545');
                            $btn.addClass('wishlist-active').attr('data-in-wishlist', '1');
                            console.log('Updated product ' + item.product_id + ' to show in wishlist');
                        }
                    });
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    .btn_love .fa-heart.fas,
    .btn_love .fa-heart.text-danger,
    .wishlist-active .fa-heart,
    .wishlist-active i.fa-heart,
    button.wishlist-active .fa-heart,
    .wishlist-heart-red,
    .btn_love .wishlist-heart-red {
        color: #dc3545 !important;
    }
    .btn_love i.fas.fa-heart {
        color: #dc3545 !important;
    }
</style>
@endpush
