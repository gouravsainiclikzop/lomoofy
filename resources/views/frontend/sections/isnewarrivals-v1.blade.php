
<!-- new arrivals section starts here -->
<section class="middle gray" id="isnewarrivals-v1">
				<div class="container"> 
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">New Arrivals</h2>
								<h3 class="ft-bold pt-3">New Arrivals</h3>
							</div>
						</div>
					</div>  
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
																		data-discount-type="{{ $colorVariant['discount_type'] ?? '' }}"
																		data-discount-value="{{ $colorVariant['discount_value'] ?? 0 }}"
																		data-discount-active="{{ ($colorVariant['discount_active'] ?? false) ? '1' : '0' }}"
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
														<i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart{{ $inWishlist ? ' text-danger wishlist-heart-red' : '' }}" style="{{ $inWishlist ? 'color: #e52d2d !important;' : '' }}"></i>
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
																			₹{{ number_format($minPrice, 2) }}
																			@if($hasPriceRange) - ₹{{ number_format($maxPrice, 2) }} @endif
																		</span>
																		<span class="final-price-compact theme-cl fw-bold fs-md" style="color: #e52d2d;">
																			₹{{ number_format($minSalePrice, 2) }}
																			@if($product['max_sale_price'] && $minSalePrice != $product['max_sale_price'])
																				- ₹{{ number_format($product['max_sale_price'], 2) }}
																			@endif
																		</span>
																	</div>
																</div>
															</div>
														@else
															<span class="ft-medium text-dark fs-sm">
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
							@endforeach 
						@endif
					</div> 
				</div>
			</section> 
<!-- new arrivals section ends here -->