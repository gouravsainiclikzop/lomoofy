
			 <!-- categories with items section  starts here-->
       <section class="space min pt-0" id="isproductwithcategorytabs-v1" style="margin-top: 30px;">
				<div class="container"> 
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							
							<ul class="nav nav-tabs b-0 d-flex align-items-center justify-content-center simple_tab_links mb-4" id="myTab" role="tablist">
								@if($parentCategories->count() > 0)
									<li class="nav-item" role="presentation">
										<a class="nav-link active" id="all-tab" href="#all" data-bs-toggle="tab" role="tab" aria-controls="all" aria-selected="true">All</a>
									</li>
									@foreach($parentCategories as $index => $category)
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ $index === 0 ? '' : '' }}" href="#category-{{ $category->id }}" id="category-{{ $category->id }}-tab" data-bs-toggle="tab" role="tab" aria-controls="category-{{ $category->id }}" aria-selected="false" tabindex="-1">{{ $category->name }}</a>
										</li>
									@endforeach
								@endif
							</ul>
							
							<div class="tab-content" id="myTabContent">
								
								<!-- All Content -->
								<div class="tab-pane fade active show" id="all" role="tabpanel" aria-labelledby="all-tab">
									<div class="tab_product">
										<div class="row rows-products">
											@php
												// Collect all products from all categories for "All" tab
												$allProducts = collect();
												foreach ($parentCategories as $category) {
													if (isset($categoryProducts[$category->id])) {
														$allProducts = $allProducts->merge($categoryProducts[$category->id]);
													}
												}
												$allProducts = $allProducts->unique('id')->take(8);
											@endphp
											@if($allProducts->count() > 0)
												@foreach($allProducts as $product)
													<div class="col-xl-3 col-lg-4 col-md-6 col-6">
														<div class="product_grid card b-0">
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
											@else
												<div class="col-12">
													<p class="text-center">No products available.</p>
												</div>
											@endif
										</div>
									</div>
								</div>
								
								@foreach($parentCategories as $index => $category)
									<div class="tab-pane fade" id="category-{{ $category->id }}" role="tabpanel" aria-labelledby="category-{{ $category->id }}-tab">
										<div class="tab_product">
											<div class="row rows-products">
												@if(isset($categoryProducts[$category->id]) && $categoryProducts[$category->id]->count() > 0)
													@foreach($categoryProducts[$category->id] as $product)
														<div class="col-xl-3 col-lg-4 col-md-6 col-6">
															<div class="product_grid card b-0">
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
																					@php
																						$minPrice = $product['min_price'] ?? 0;
																						$maxPrice = $product['max_price'] ?? 0;
																						$minSalePrice = $product['min_sale_price'] ?? null;
																						$hasSale = $product['has_sale'] ?? false;
																					@endphp
																					@if($hasSale && $minSalePrice)
																						<div class="product-pricing-compact compact">
																							<div class="pricing-main-compact">
																								<div class="d-flex align-items-baseline flex-	wrap gap-1">
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
												@else
													<div class="col-12">
														<p class="text-center">No products available in this category.</p>
													</div>
												@endif
											</div>
										</div>
									</div>
								@endforeach
								
							</div>
							
						</div>
					</div>
					
				</div>
			</section>
<!-- categories with items section  starts here-->
