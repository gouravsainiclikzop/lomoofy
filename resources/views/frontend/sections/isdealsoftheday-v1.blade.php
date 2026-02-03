
			<!-- deal of the day section start -->
			@if($dealsOfTheDay->count() > 0)
			<section class="space gray" id="isdealsoftheday-v1">
				<div class="container">
					
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Good Deals</h2>
								<h3 class="ft-bold pt-3">Deals of The Day</h3>
							</div>
						</div>
					</div>
					
					<div class="row"> 
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
						
							<div class="slide_items">
								@foreach($dealsOfTheDay as $product)
								<!-- single Item -->
								<div class="single_itesm">
									<div class="product_grid card b-0 mb-0">
										@if($product['badge'] === 'sale')
											<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										@elseif($product['badge'] === 'new')
											<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										@elseif($product['badge'] === 'hot')
											<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										@endif
										<button class="snackbar-wishlist btn btn_love position-absolute ab-right {{ $product['in_wishlist'] ? 'wishlist-active' : '' }}" 
											data-product-id="{{ $product['id'] }}" 
											data-in-wishlist="{{ $product['in_wishlist'] ? '1' : '0' }}">
											<i class="far fa-heart {{ $product['in_wishlist'] ? 'text-danger' : '' }}"></i>
										</button> 
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">
													<img class="card-img-top" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
												</a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio">
														<a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium quick-view-btn" data-product-slug="{{ $product['slug'] }}">
															<i class="fas fa-eye me-1"></i>Quick View
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1">
														<a href="{{ route('frontend.product') }}?product={{ $product['slug'] }}">{{ $product['name'] }}</a>
													</h5>
													<div class="elis_rty">
														@php
															$hasPriceRange = $product['has_price_range'] ?? false;
															$firstVariantPrice = $product['first_variant_price'] ?? $product['min_price'] ?? 0;
															$firstVariantSalePrice = $product['first_variant_sale_price'] ?? null;
														@endphp
														
														@if(!$hasPriceRange && isset($product['first_variant_discount_type']))
															{{-- Use variant-level pricing when single variant with discount --}}
															@include('frontend.partials.product-pricing-compact', [
																'price' => $firstVariantPrice,
																'sale_price' => $firstVariantSalePrice,
																'original_price' => $firstVariantPrice,
																'discount_type' => $product['first_variant_discount_type'] ?? null,
																'discount_value' => $product['first_variant_discount_value'] ?? null,
																'discount_active' => $product['first_variant_discount_active'] ?? false,
																'gstType' => $product['gst_type'] ?? true,
																'gstPercentage' => $product['gst_percentage'] ?? 0,
																'compact' => true
															])
														@else
															{{-- Use price range or simple display for multiple variants --}}
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
																			<span class="final-price-compact theme-cl fw-bold fs-md" style="color: #e52d2d;">
																				₹{{ number_format($minSalePrice, 0) }}
																				@if($product['max_sale_price'] && $minSalePrice != $product['max_sale_price'])
																					- ₹{{ number_format($product['max_sale_price'], 0) }}
																				@endif
																			</span>
																		</div>
																	</div>
																</div>
															@else
																<span class="ft-medium fs-md text-dark">
																	₹{{ number_format($product['min_display_price'] ?? $product['min_price'] ?? 0, 0) }}
																	@if($hasPriceRange) - ₹{{ number_format($product['max_display_price'] ?? $product['max_price'] ?? 0, 0) }} @endif
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
							</div>
						</div>
					</div>
					
				</div>
			</section>
			@endif
			 <!-- deal of the day section ends -->
