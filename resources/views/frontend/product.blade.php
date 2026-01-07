@extends('layouts.frontend')

@section('title', 'Product Detail - Lomoofy Industries')

@section('content')
			<!-- ======================= Top Breadcrubms ======================== -->
			<div class="gray py-3">
				<div class="container">
					<div class="row">
						<div class="colxl-12 col-lg-12 col-md-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
									@if($primaryCategory)
										@if($primaryCategory->parent)
											<li class="breadcrumb-item"><a href="{{ route('frontend.shop') }}?category={{ $primaryCategory->parent->slug }}">{{ $primaryCategory->parent->name }}</a></li>
										@endif
										<li class="breadcrumb-item"><a href="{{ route('frontend.shop') }}?category={{ $primaryCategory->slug }}">{{ $primaryCategory->name }}</a></li>
									@endif
									<li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</div>
			<!-- ======================= Top Breadcrubms ======================== -->
			
			<!-- ======================= Product Detail ======================== -->
			<section class="middle">
				<div class="container">
					<div class="row align-items-center">
					
						<div class="col-xl-5 col-lg-6 col-md-12 col-sm-12">
							<!-- Main Product Image -->
							<div class="product-main-image mb-3" id="productMainImageContainer">
								<img id="productMainImage" 
									 src="{{ $productImages->first()['url'] ?? asset('frontend/images/product/sample-product.jpg') }}" 
									 alt="{{ $product->name }}" 
									 class="img-fluid w-100">
							</div>
							
							<!-- Product Thumbnails -->
							<div class="product-thumbnails" id="productThumbnailsContainer">
								@if($productImages->count() > 0)
									@foreach($productImages as $index => $image)
										<div class="product-thumbnail-item {{ $index === 0 ? 'active' : '' }}" 
											 data-image-url="{{ $image['url'] }}" 
											 data-image-alt="{{ $image['alt'] }}">
											<img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="img-fluid">
										</div>
								@endforeach
								@else
									<div class="product-thumbnail-item active" 
										 data-image-url="{{ asset('frontend/images/product/sample-product.jpg') }}" 
										 data-image-alt="{{ $product->name }}">
										<img src="{{ asset('frontend/images/product/sample-product.jpg') }}" alt="{{ $product->name }}" class="img-fluid">
									</div>
								@endif
							</div>
						</div>
						
						<div class="col-xl-7 col-lg-6 col-md-12 col-sm-12">
							<div class="prd_details ps-xl-5">
								
								@if($primaryCategory) 
								<div class="prt_01 mb-2"><span class="text-success bg-light-success rounded px-2 py-1">{{ $primaryCategory->name }}</span></div>
								@endif  

								<div class="prt_02 mb-3">
									<h2 class="ft-bold mb-1">{{ $product->name }}</h2>
                                    
								@php
									$tags = collect(explode(',', (string) $product->tags))
										->map(fn($t) => trim($t))
										->filter(fn($t) => $t !== '');
								@endphp

								@if($tags->count())
									<div class="mb-2">
										@foreach($tags as $tag)
											<small class="badge bg-primary me-1">{{ $tag }}</small>
										@endforeach
									</div>
								@endif

									<div class="text-left">
										<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0" id="productRatingDisplay">
											<i class="far fa-star"></i>
											<i class="far fa-star"></i>
											<i class="far fa-star"></i>
											<i class="far fa-star"></i>
											<i class="far fa-star"></i>
											<span class="small ms-2" id="productReviewCount">(0 Reviews)</span>
										</div>
                                    @php
                                        // Get first variant for initial pricing display
                                        $firstVariant = isset($activeVariants) && $activeVariants->count() > 0 ? $activeVariants->first() : null;
                                        
                                        // Use first variant if available, otherwise create a dummy variant from price range
                                        $displayVariant = $firstVariant;
                                        if (!$displayVariant && isset($minPrice)) {
                                            // Create a virtual variant for price range display
                                            $displayVariant = (object)[
                                                'price' => $minPrice ?? 0,
                                                'sale_price' => $minSalePrice ?? null,
                                                'discount_type' => null,
                                                'discount_value' => null,
                                                'discount_active' => false,
                                            ];
                                        }
                                    @endphp
                                    
                                    {{-- New Pricing Component --}}
                                    <div id="product-price">
                                        @if($displayVariant && $displayVariant instanceof \App\Models\ProductVariant)
                                            @include('frontend.partials.product-pricing', [
                                                'variant' => $displayVariant,
                                                'gstType' => $gstType ?? true,
                                                'gstPercentage' => $gstPercentage ?? 0
                                            ])
                                        @elseif($displayVariant)
                                            {{-- Fallback for price range display when no variant selected --}}
                                            @php
                                                // Use prices as-is without GST calculation
                                                $displayMinPrice = $minPrice ?? 0;
                                                $displayMinSalePrice = $minSalePrice ?? null;
                                                $displayMaxPrice = ($minPrice != $maxPrice && $maxPrice) ? $maxPrice : 0;
                                                $taxLabelDynamic = ($gstType === false) ? 'Exclusive of taxes' : 'Inclusive of all taxes';
                                            @endphp
                                            <div class="product-pricing-component" data-base-price="{{ $minPrice ?? 0 }}" data-sale-price="{{ $minSalePrice ?? null }}">
                                                <div class="pricing-main mb-2">
                                                    <div class="d-flex align-items-baseline flex-wrap gap-2">
                                                        @if($hasSale && $displayMinSalePrice)
                                                            <span class="base-price text-muted text-decoration-line-through fs-5 fw-normal">₹{{ number_format($displayMinPrice, 2) }}</span>
                                                            <span class="final-price theme-cl fw-bold fs-2" style="color: #dc3545;">₹{{ number_format($displayMinSalePrice, 2) }}</span>
                                                        @else
                                                            <span class="final-price theme-cl fw-bold fs-3" style="color: #dc3545;">₹{{ number_format($displayMinPrice, 2) }}</span>
                                                            @if($minPrice != $maxPrice && $displayMaxPrice > 0)
                                                                <span class="final-price theme-cl fw-bold fs-3"> - ₹{{ number_format($displayMaxPrice, 0) }}</span>
                                                            @endif
                                                        @endif
                                                        <span class="tax-label text-muted fs-sm align-self-end">({{ $taxLabelDynamic }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
										@if($recentPurchaseCount > 0)
										<div class="mt-2">
											<span class="text-success fs-sm ft-medium">
												<i class="fas fa-shopping-bag me-1"></i>{{ $recentPurchaseCount }}+ bought recently
											</span>
										</div>
										@endif
									</div>
								</div>
								
								@if($product->short_description) 
								<div class="prt_03 mb-4">
									<p>{!! $product->short_description !!}</p>
								</div>
								@endif
								
								@if(isset($attributesData) && count($attributesData) > 0)
									@foreach($attributesData as $attribute)
										@php
											$attributeSlug = $attribute['slug'] ?? strtolower(str_replace(' ', '-', $attribute['name']));
											$attributeType = $attribute['type'] ?? 'text';
										@endphp
										<div class="prt_04 mb-{{ $loop->last ? '4' : '2' }}" data-attribute-container="{{ $attribute['id'] }}">
											<p class="d-flex align-items-center mb-0 text-dark ft-medium">{{ $attribute['name'] }}:</p>
											<div class="text-left {{ $attributeType === 'color' ? '' : 'pb-0 pt-2' }}">
												@foreach($attribute['values'] as $valueIndex => $valueData)
													@php
														$value = is_array($valueData) ? ($valueData['value'] ?? '') : $valueData;
														$valueId = $attributeSlug . '_' . strtolower(str_replace(' ', '', $value)) . '_' . $valueIndex;
													@endphp
													@if($attributeType === 'color')
														@php
															$colorCode = is_array($valueData) && isset($valueData['color_code']) ? $valueData['color_code'] : '#ccc';
														@endphp
														<div class="form-check form-option form-check-inline mb-1">
															<input class="form-check-input attribute-option-product" type="radio" 
																name="productAttr_{{ $attribute['id'] }}" 
																id="{{ $valueId }}" 
																value="{{ $value }}" 
																data-attribute-id="{{ $attribute['id'] }}"
																data-attribute-name="{{ $attribute['name'] }}"
																data-attribute-slug="{{ $attributeSlug }}"
																data-value="{{ $value }}"
																{{ $valueIndex === 0 ? 'checked' : '' }}>
															<label class="form-option-label rounded-circle" for="{{ $valueId }}">
																<span class="form-option-color rounded-circle" style="background-color: {{ $colorCode }}"></span>
															</label>
														</div>
													@else
														<div class="form-check size-option form-option form-check-inline mb-2">
															<input class="form-check-input attribute-option-product" type="radio" 
																name="productAttr_{{ $attribute['id'] }}" 
																id="{{ $valueId }}" 
																value="{{ $value }}"
																data-attribute-id="{{ $attribute['id'] }}"
																data-attribute-name="{{ $attribute['name'] }}"
																data-attribute-slug="{{ $attributeSlug }}"
																data-value="{{ $value }}"
																{{ $valueIndex === 0 ? 'checked' : '' }}>
															<label class="form-option-label" for="{{ $valueId }}">{{ $value }}</label>
														</div>
													@endif
												@endforeach
											</div>
										</div>
									@endforeach
								@else
									{{-- Fallback to legacy color/size display --}}
									@if(count($colors) > 0)
										<div class="prt_04 mb-2">
											<p class="d-flex align-items-center mb-0 text-dark ft-medium">Color:</p>
											<div class="text-left">
												@foreach($colors as $colorIndex => $colorValue)
													@php
														$colorId = 'color_' . strtolower(str_replace(' ', '', $colorValue)) . '_' . $colorIndex;
														$colorVariant = $colorVariantsMap[$colorValue] ?? null;
														$colorCode = $colorVariant['color_code'] ?? '#ccc';
													@endphp
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input color-option-product" type="radio" name="productColor" id="{{ $colorId }}" value="{{ $colorValue }}" 
															data-color-value="{{ $colorValue }}"
															data-color-code="{{ $colorCode }}"
															@if($colorVariant)
																data-variant-image="{{ $colorVariant['image'] }}"
																data-price="{{ $colorVariant['display_price'] }}"
																data-sale-price="{{ $colorVariant['sale_price'] ?? '' }}"
																data-regular-price="{{ $colorVariant['price'] }}"
															@endif
															{{ $colorIndex === 0 ? 'checked' : '' }}>
														<label class="form-option-label rounded-circle" for="{{ $colorId }}">
															<span class="form-option-color rounded-circle" style="background-color: {{ $colorCode }}"></span>
														</label>
													</div>
												@endforeach
											</div>
										</div>
									@endif
									@if(count($sizes) > 0)
										<div class="prt_04 ">
											<p class="d-flex align-items-center mb-0 text-dark ft-medium">Size:</p>
											<div class="text-left pb-0 pt-2">
												@foreach($sizes as $sizeIndex => $sizeValue)
													@php
														$sizeId = 'size_' . strtolower(str_replace(' ', '', $sizeValue)) . '_' . $sizeIndex;
													@endphp
													<div class="form-check size-option form-option form-check-inline mb-2">
														<input class="form-check-input size-option-product" type="radio" name="productSize" id="{{ $sizeId }}" value="{{ $sizeValue }}" {{ $sizeIndex === 0 ? 'checked' : '' }}>
														<label class="form-option-label" for="{{ $sizeId }}">{{ $sizeValue }}</label>
													</div>
												@endforeach
											</div>
										</div>
									@endif
								@endif
 

								<div class="prt_04 ">
									@if($primaryCategory)
									<p class="d-flex align-items-center mb-1">Category:<strong class="fs-sm text-dark ft-medium ms-1">
										{{ $primaryCategory->name }}{{ $primaryCategory->parent ? ', ' . $primaryCategory->parent->name : '' }}
									</strong></p>
									@endif
									@if($primaryBrand)
									<p class="d-flex align-items-center mb-1">Brand:<strong class="fs-sm text-dark ft-medium ms-1">
										{{ $primaryBrand->name }}
									</strong></p>
									@endif
									<p class="d-flex align-items-center mb-0">SKU:<strong class="fs-sm text-dark ft-medium ms-1" id="variant-sku">{{ $displaySku ?? '' }}</strong></p>
									
                                    @php
                                        $hasMeasurements = false;
                                        if ($activeVariants && $activeVariants->count() > 0) {
                                            foreach ($activeVariants as $variant) {
                                                if ($variant->measurements) {
                                                    $measurements = is_string($variant->measurements) 
                                                        ? json_decode($variant->measurements, true) 
                                                        : $variant->measurements;
                                                    if (is_array($measurements) && count($measurements) > 0) {
                                                        $hasMeasurements = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp

                                    @if($hasMeasurements)
                                    <div class="mt-2">
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-secondary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#measurementChartModal"
                                        >
                                        Measurements
                                        </button>
                                    </div>
                                    @endif
								</div>
								
								<div class="prt_05 mb-4 mt-4">
									<div class="form-row row g-3 mb-7">
										<div class="col-12 col-md-6 col-lg-3">
											<!-- Quantity -->
											<select class="mb-2 custom-select">
											  <option value="1" selected="">1</option>
											  <option value="2">2</option>
											  <option value="3">3</option>
											  <option value="4">4</option>
											  <option value="5">5</option>
											  <option value="6">6</option>
											  <option value="7">7</option>
											  <option value="8">8</option>
											  <option value="9">9</option>
											  <option value="10">10</option>
											</select>
										</div>
										<div class="col-12 col-md-12 col-lg-6">
											<!-- Submit -->
											<button type="button" class="btn btn-block custom-height bg-dark mb-2 w-100 add-to-cart-btn" 
												data-product-id="{{ $product->id }}"
												data-product-slug="{{ $product->slug }}">
												<i class="lni lni-shopping-basket me-2"></i>Add to Cart 
											</button>
											<!-- Stock Status Message -->
											<div id="stock-status-message" class="mt-2" style="display: none;">
												<small class="text-danger">
													<i class="lni lni-close me-1"></i><span id="stock-status-text">Out of Stock</span>
												</small>
											</div>
										</div>
										<div class="col-12 col-md-6 col-lg-3">
											<!-- Wishlist -->
											<button class="btn custom-height btn-default btn-block mb-2 text-dark w-100 snackbar-wishlist {{ $inWishlist ? 'wishlist-active' : '' }}" 
												data-product-id="{{ $product->id }}" 
												data-in-wishlist="{{ $inWishlist ? '1' : '0' }}">
												<i class="{{ $inWishlist ? 'fas' : 'lni' }} {{ $inWishlist ? 'fa-heart' : 'lni-heart' }} me-2{{ $inWishlist ? ' text-danger' : '' }}" {{ $inWishlist ? 'style="color: #dc3545 !important;"' : '' }}></i>Wishlist
											</button>
										</div>
								  </div>
								</div>
								
							 

                <div class="short_products_info_body mb-4">
								
                <!-- Single Option -->
                <div class="single_search_boxed">
                  <div class="widget-boxed-header">
                    <h4><a href="#productinfo" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed"><i class="ti-info me-2ti-info"></i>Product Info</a></h4>
                  </div>
                  <div class="widget-boxed-body collapse" id="productinfo" data-parent="#productinfo">
                    <div class="side-list no-border">
                      <!-- Single Filter Card -->
                      <div class="single_filter_card">
                        <div class="card-body pt-0" id="variant-highlights-details">
                          @php
                            // Get highlights_details from the first active variant
                            $firstVariant = $activeVariants->first();
                            $highlightsDetails = [];
                            
                            if ($firstVariant && $firstVariant->highlights_details) {
                              // Ensure highlights_details is an array
                              if (is_string($firstVariant->highlights_details)) {
                                $highlightsDetails = json_decode($firstVariant->highlights_details, true) ?? [];
                              } else {
                                $highlightsDetails = is_array($firstVariant->highlights_details) ? $firstVariant->highlights_details : [];
                              }
                            }
                          @endphp
                          
                          @if(count($highlightsDetails) > 0)
                            @foreach($highlightsDetails as $highlight)
                              @if(!empty($highlight['heading_name']))
                                <h6 class="font-size-sm mb-2">{{ $highlight['heading_name'] }}</h6>
                                @if(!empty($highlight['bullet_points']) && is_array($highlight['bullet_points']))
                                  <ul class="lists-2 min-space {{ $loop->last ? 'mb-0' : '' }}">
                                    @foreach($highlight['bullet_points'] as $point)
                                      @if(!empty($point))
                                        <li>{{ $point }}</li>
                                      @endif
                                    @endforeach
                          </ul>
                                @endif
                              @endif
                            @endforeach 
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div> 
              
              </div>

              <div class="prt_06">
									<p class="mb-0 d-flex align-items-center">
									  <span class="me-4">Share:</span>
									  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted me-2" href="#!">
										<i class="fab fa-twitter position-absolute"></i>
									  </a>
									  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted me-2" href="#!">
										<i class="fab fa-facebook-f position-absolute"></i>
									  </a>
									  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted" href="#!">
										<i class="fab fa-pinterest-p position-absolute"></i>
									  </a>
									</p>
								</div>

								
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Product Detail End ======================== -->
			
			<!-- ======================= Product Description ======================= -->
			<section class="middle">
				<div class="container">
					<div class="row align-items-center justify-content-center">
						<div class="col-xl-11 col-lg-12 col-md-12 col-sm-12">
							<ul class="nav nav-tabs b-0 d-flex align-items-center justify-content-center simple_tab_links mb-4" id="myTab" role="tablist">
								<li class="nav-item" role="presentation">
									<a class="nav-link active" id="description-tab" href="#description-tab" data-bs-toggle="tab" data-bs-target="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" href="#information-tab" id="information-tab" data-bs-toggle="tab" role="tab" data-bs-target="#information" aria-controls="information" aria-selected="false">Additional information</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" href="#reviews-tab" id="reviews-tab" data-bs-toggle="tab" role="tab" data-bs-target="#reviews" aria-controls="reviews" aria-selected="false">Reviews</a>
								</li>
							</ul>
							
							<div class="tab-content" id="myTabContent">
								
								<!-- Description Content -->
								<div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
									<div class="description_info" id="variant-description">
										@php
											$firstVariant = $activeVariants->first();
											$variantDescription = $firstVariant && $firstVariant->description ? $firstVariant->description : ($product->description ?? $product->short_description ?? '');
										@endphp
										@if($variantDescription)
											<div class="p-0 mb-2">{!! $variantDescription !!}</div>
										@else
											<p class="p-0 mb-2">No description available for this product.</p>
										@endif
									</div>
								</div>
								
								<!-- Additional Content -->
								<div class="tab-pane fade" id="information" role="tabpanel" aria-labelledby="information-tab">
									<div class="additionals">
										<table class="table">
											<tbody>
												<tr>
												  <th class="ft-medium text-dark">Product ID</th>
												  <td>#{{ $product->id }}</td>
												</tr>
												<tr id="variant-sku-row" style="{{ $displaySku ? '' : 'display: none;' }}">
												  <th class="ft-medium text-dark">SKU</th>
												  <td id="variant-sku-info">{{ $displaySku ?? '' }}</td>
												</tr>
												<tr id="variant-color-row" style="display: none;">
												  <th class="ft-medium text-dark">Selected Color</th>
												  <td id="variant-color-info">-</td>
												</tr>
												<tr id="variant-size-row" style="display: none;">
												  <th class="ft-medium text-dark">Selected Size</th>
												  <td id="variant-size-info">-</td>
												</tr>
												@if(count($colors) > 0)
												<tr>
												  <th class="ft-medium text-dark">Available Colors</th>
												  <td>{{ implode(', ', $colors) }}</td>
												</tr>
												@endif
												@if(count($sizes) > 0)
												<tr>
												  <th class="ft-medium text-dark">Available Sizes</th>
												  <td>{{ implode(', ', $sizes) }}</td>
												</tr>
												@endif
												@if($primaryCategory)
												<tr>
												  <th class="ft-medium text-dark">Category</th>
												  <td>{{ $primaryCategory->name }}{{ $primaryCategory->parent ? ' > ' . $primaryCategory->parent->name : '' }}</td>
												</tr>
												@endif
											</tbody>
										</table>
									</div>
								</div>
								
								<!-- Reviews Content -->
								<div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
									<!-- Reviews Summary -->
									<div id="reviewsSummary" class="mb-4">
										<div class="d-flex align-items-center mb-3">
											<div class="me-4">
												<h3 class="mb-0" id="averageRatingDisplay">0.0</h3>
												<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0" id="averageRatingStars">
													<i class="far fa-star"></i>
													<i class="far fa-star"></i>
													<i class="far fa-star"></i>
													<i class="far fa-star"></i>
													<i class="far fa-star"></i>
												</div>
												<small class="text-muted" id="totalReviewsDisplay">0 Reviews</small>
													</div>
											<div class="flex-grow-1">
												<div id="ratingDistribution"></div>
												</div>
											</div>
										</div>
										
									<!-- Reviews List -->
									<div class="reviews_info" id="reviewsList">
										<div class="text-center py-5">
											<p class="text-muted">Select a product variant to view reviews</p>
											</div>
										</div>
										
									<!-- Review Form (shown only for logged-in customers who can review) -->
									<div class="reviews_rate" id="reviewFormContainer" style="display: none;">
										<form id="reviewForm" class="row g-3">
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<h4>Submit Rating</h4>
											</div>
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="revie_stars d-flex align-items-center justify-content-between px-2 py-2 gray rounded">
													<div class="srt_013">
														<div class="submit-rating">
														  <input id="star-5" type="radio" name="rating" value="5" required />
														  <label for="star-5" title="5 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-4" type="radio" name="rating" value="4" />
														  <label for="star-4" title="4 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-3" type="radio" name="rating" value="3" />
														  <label for="star-3" title="3 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-2" type="radio" name="rating" value="2" />
														  <label for="star-2" title="2 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-1" type="radio" name="rating" value="1" />
														  <label for="star-1" title="1 star">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														</div>
													</div>
													
													<div class="srt_014">
														<h6 class="mb-0" id="selectedRatingDisplay">Select Rating</h6>
													</div>
												</div>
											</div> 
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label class="medium text-dark ft-medium mb-2">Description</label>
													<textarea name="comment" id="reviewComment" class="form-control rounded-2" rows="4" maxlength="1000" placeholder="Share your experience with this product..."></textarea>
													<small class="text-muted"><span id="commentCharCount">0</span>/1000 characters</small>
												</div>
											</div>
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="form-group m-0">
													<button type="submit" class="btn btn-white stretched-links hover-black rounded-2">
														Submit Review <i class="lni lni-arrow-right"></i>
													</button>
												</div>
											</div>
											
											<input type="hidden" name="product_id" id="reviewProductId" value="{{ $product->id }}" />
										</form>
									</div>
									
									<!-- Login Prompt (shown when user is not logged in) -->
									<div id="reviewLoginPrompt" class="text-center py-4" style="display: none;">
										<p class="text-muted mb-3">Please <a href="#" data-bs-toggle="modal" data-bs-target="#login">login</a> to submit a review</p>
									</div>
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Product Description End ==================== -->
			
			
			<!-- ======================= Similar Products Start ============================ -->
			<section class="middle pt-0">
				<div class="container">
					
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Similar Products</h2>
								<h3 class="ft-bold pt-3">Matching Products</h3>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="slide_items">
								@if($similarProducts->count() > 0)
									@foreach($similarProducts as $similarProduct)
									<!-- single Item -->
									<div class="single_itesm">
										<div class="product_grid card b-0 mb-0">
											@if($similarProduct['has_sale'])
												<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
											@endif
											<button class="snackbar-wishlist btn btn_love position-absolute ab-right" data-product-id="{{ $similarProduct['id'] }}"><i class="far fa-heart"></i></button> 
											<div class="card-body p-0">
												<div class="shop_thumb position-relative">
													<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}?product={{ $similarProduct['slug'] }}"><img class="card-img-top" src="{{ $similarProduct['image_url'] }}" alt="{{ $similarProduct['name'] }}"></a>
													<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
														<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium quick-view-btn" data-product-slug="{{ $similarProduct['slug'] }}"><i class="fas fa-eye me-1"></i>Quick View</a></div>
													</div>
												</div>
											</div>
											<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
												<div class="text-left">
													<div class="text-center">
														<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}?product={{ $similarProduct['slug'] }}">{{ $similarProduct['name'] }}</a></h5>
														<div class="elis_rty">
															@if($similarProduct['has_sale'] && $similarProduct['min_sale_price'])
																<span class="text-muted ft-medium line-through me-2">₹{{ number_format($similarProduct['min_price'], 0) }}</span>
																<span class="ft-medium theme-cl fs-md">₹{{ number_format($similarProduct['min_sale_price'], 0) }}</span>
															@else
																<span class="ft-medium fs-md text-dark">{{ $similarProduct['display_price'] }}</span>
															@endif
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									@endforeach
								@else
									<div class="col-xl-12">
										<p class="text-center text-muted">No similar products found.</p>
									</div>
								@endif
								
							</div>
						</div>
					</div>
					
				</div>
			</section>
			<!-- ======================= Similar Products End ============================ -->
			 
<script>
(function() {
    // Variant data map from backend - make it global for add to cart function
    window.variantDataMap = @json($variantDataMap ?? []);
    const variantDataMap = window.variantDataMap;
    const colorVariantsMap = @json($colorVariantsMap ?? []);
    const productImages = @json($productImages ?? []);
    
    // Get selected variant based on all attributes
    window.getSelectedVariant = function() {
        // Try new attribute-based selection first
        const selectedAttributes = {};
        document.querySelectorAll('.attribute-option-product:checked').forEach(function(input) {
            const attrId = input.dataset.attributeId;
            const value = input.value;
            if (attrId && value) {
                selectedAttributes[attrId] = value;
            }
        });
        
        // Build key from all selected attributes
        if (Object.keys(selectedAttributes).length > 0) {
            const keyParts = [];
            Object.keys(selectedAttributes).sort().forEach(function(attrId) {
                keyParts.push(attrId + ':' + selectedAttributes[attrId]);
            });
            const key = keyParts.join('|');
            if (variantDataMap[key]) {
                return variantDataMap[key];
            }
            
            // If exact match not found, try to find variant by matching structured format
            // Get attributesData to map attribute IDs to names/slugs
            const attributesData = @json($attributesData ?? []);
            const attributeMap = {};
            if (attributesData && Array.isArray(attributesData)) {
                attributesData.forEach(function(attr) {
                    attributeMap[attr.id] = {
                        name: (attr.name || '').toLowerCase(),
                        slug: (attr.slug || '').toLowerCase(),
                        type: attr.type || 'text'
                    };
                });
            }
            
            for (let variantKey in variantDataMap) {
                const variant = variantDataMap[variantKey];
                if (variant.attributes && typeof variant.attributes === 'object') {
                    let allMatch = true;
                    
                    // Handle structured format: {"variable":{"size":"uk 6"},"color":{"label":"black","code":"#000000"}}
                    if (variant.attributes.variable || variant.attributes.color) {
                        for (let attrId in selectedAttributes) {
                            const selectedValue = String(selectedAttributes[attrId]).trim().toLowerCase();
                            const attrInfo = attributeMap[attrId];
                            
                            if (!attrInfo) {
                                allMatch = false;
                                break;
                            }
                            
                            let hasMatch = false;
                            
                            // Check color attribute
                            if (attrInfo.type === 'color' || attrInfo.name === 'color' || attrInfo.slug === 'color') {
                                if (variant.attributes.color && variant.attributes.color.label) {
                                    const variantColorLabel = String(variant.attributes.color.label).trim().toLowerCase();
                                    hasMatch = variantColorLabel === selectedValue;
                                }
                            }
                            // Check variable attributes
                            else if (variant.attributes.variable && typeof variant.attributes.variable === 'object') {
                                // Try to match by attribute name or slug
                                for (let varKey in variant.attributes.variable) {
                                    if (varKey.toLowerCase() === attrInfo.name || varKey.toLowerCase() === attrInfo.slug) {
                                        const variantValue = String(variant.attributes.variable[varKey]).trim().toLowerCase();
                                        hasMatch = variantValue === selectedValue;
                                        break;
                                    }
                                }
                            }
                            
                            if (!hasMatch) {
                                allMatch = false;
                                break;
                            }
                        }
                    }
                    // Fallback: flat format
                    else {
                        for (let attrId in selectedAttributes) {
                            const selectedValue = selectedAttributes[attrId];
                            if (variant.attributes[attrId] !== selectedValue) {
                                allMatch = false;
                                break;
                            }
                        }
                    }
                    
                    if (allMatch && Object.keys(selectedAttributes).length > 0) {
                        console.log('getSelectedVariant - Found variant by structured format match:', variant);
                        return variant;
                    }
                }
            }
        }
        
        // Fallback to legacy color/size selection
        const selectedColor = document.querySelector('input[name="productColor"]:checked')?.value || '';
        const selectedSize = document.querySelector('input[name="productSize"]:checked')?.value || '';
        const key = selectedColor + '|' + selectedSize;
        return variantDataMap[key] || null;
    };
    
    // Update product images gallery based on selected variant
    function updateProductImagesGallery() {
        const variant = window.getSelectedVariant();
        const mainImageElement = document.getElementById('productMainImage');
        const thumbnailsContainer = document.getElementById('productThumbnailsContainer');
        
        if (!mainImageElement || !thumbnailsContainer) return;
        
        // Get images from selected variant
        let imagesToShow = [];
        
        // Try to get images from exact variant match first
        if (variant && variant.images && Array.isArray(variant.images) && variant.images.length > 0) {
            imagesToShow = variant.images.filter(function(img) {
                return img && img.url && img.url !== 'undefined' && img.url !== 'null';
            });
        }
        
        // If no images from exact variant match, try to find by color-type attribute
        if (imagesToShow.length === 0 && window.variantDataMap) {
            // Find color-type attribute from selected attributes
            const selectedAttributes = {};
            const attributesData = @json($attributesData ?? []);
            
            // Build map of attribute IDs to their types
            const attributeTypeMap = {};
            if (attributesData && Array.isArray(attributesData)) {
                attributesData.forEach(function(attr) {
                    if (attr.id) {
                        attributeTypeMap[attr.id] = attr.type || 'text';
                    }
                });
            }
            
            document.querySelectorAll('.attribute-option-product:checked').forEach(function(input) {
                const attrId = input.dataset.attributeId;
                const value = input.value;
                if (attrId && value) {
                    selectedAttributes[attrId] = value;
                }
            });
            
            // Check if any selected attribute is a color-type
            let colorAttributeId = null;
            let colorValue = null;
            for (let attrId in selectedAttributes) {
                // Check if it's a color attribute by type or by looking for color swatch
                const isColorType = attributeTypeMap[attrId] === 'color';
                const attrInput = document.querySelector('.attribute-option-product[data-attribute-id="' + attrId + '"]:checked');
                const hasColorSwatch = attrInput && attrInput.closest('[data-attribute-container]')?.querySelector('.form-option-color');
                
                if (isColorType || hasColorSwatch) {
                    colorAttributeId = attrId;
                    colorValue = selectedAttributes[attrId];
                    break;
                }
            }
            
            // If we found a color attribute, try to find variant with that color
            if (colorAttributeId && colorValue) {
                // Search through all variants to find one with matching color
                for (let key in window.variantDataMap) {
                    const variantData = window.variantDataMap[key];
                    // variantData.attributes is a flat object: {attribute_id: value}
                    if (variantData.attributes && typeof variantData.attributes === 'object' && !Array.isArray(variantData.attributes)) {
                        // Check if this variant has the matching color attribute
                        if (variantData.attributes[colorAttributeId] === colorValue) {
                            if (variantData.images && Array.isArray(variantData.images) && variantData.images.length > 0) {
                                imagesToShow = variantData.images.filter(function(img) {
                                    return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                                });
                                if (imagesToShow.length > 0) {
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // If still no images, try searching all variants for any with images (fallback)
        if (imagesToShow.length === 0 && window.variantDataMap) {
            // Get all selected attributes
            const selectedAttributes = {};
            document.querySelectorAll('.attribute-option-product:checked').forEach(function(input) {
                const attrId = input.dataset.attributeId;
                const value = input.value;
                if (attrId && value) {
                    selectedAttributes[attrId] = value;
                }
            });
            
            // Try to find any variant that matches at least one selected attribute and has images
            if (Object.keys(selectedAttributes).length > 0) {
                for (let key in window.variantDataMap) {
                    const variantData = window.variantDataMap[key];
                    if (variantData.images && Array.isArray(variantData.images) && variantData.images.length > 0) {
                        // Check if this variant matches any selected attribute
                        let hasMatch = false;
                        if (variantData.attributes && typeof variantData.attributes === 'object' && !Array.isArray(variantData.attributes)) {
                            for (let attrId in selectedAttributes) {
                                if (variantData.attributes[attrId] === selectedAttributes[attrId]) {
                                    hasMatch = true;
                                    break;
                                }
                            }
                        }
                        
                        if (hasMatch) {
                            imagesToShow = variantData.images.filter(function(img) {
                                return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                            });
                            if (imagesToShow.length > 0) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        // Fallback: Try legacy color-based image selection
        if (imagesToShow.length === 0) {
            const selectedColor = document.querySelector('input[name="productColor"]:checked')?.value || '';
            if (selectedColor && colorVariantsMap[selectedColor] && colorVariantsMap[selectedColor].images && colorVariantsMap[selectedColor].images.length > 0) {
                imagesToShow = colorVariantsMap[selectedColor].images.filter(function(img) {
                    return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                });
            }
        }
        
        // If no variant images or empty, use default product images
        if (imagesToShow.length === 0) {
            if (productImages && productImages.length > 0) {
                // Filter out any undefined/null URLs from product images
                imagesToShow = productImages.filter(function(img) {
                    return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                });
            }
            
            // Final fallback
            if (imagesToShow.length === 0) {
                const fallbackUrl = '{{ asset("frontend/images/product/sample-product.jpg") }}';
                const fallbackAlt = '{{ addslashes($product->name) }}';
                imagesToShow = [{url: fallbackUrl, alt: fallbackAlt}];
            }
        }
        
        // Update main image to first image
        if (imagesToShow.length > 0 && imagesToShow[0].url) {
            mainImageElement.src = imagesToShow[0].url;
            mainImageElement.alt = imagesToShow[0].alt || '{{ addslashes($product->name) }}';
        }
        
        // Build thumbnails HTML
        let thumbnailsHtml = '';
        imagesToShow.forEach(function(image, index) {
            if (image && image.url && image.url !== 'undefined' && image.url !== 'null') {
                const imageUrl = image.url;
                const imageAlt = (image.alt || '{{ addslashes($product->name) }}').replace(/'/g, "&#39;");
                const activeClass = index === 0 ? ' active' : '';
                thumbnailsHtml += '<div class="product-thumbnail-item' + activeClass + '" ' +
                    'data-image-url="' + imageUrl + '" ' +
                    'data-image-alt="' + imageAlt + '">' +
                    '<img src="' + imageUrl + '" alt="' + imageAlt + '" class="img-fluid">' +
                    '</div>';
            }
        });
        
        // Update thumbnails container
        thumbnailsContainer.innerHTML = thumbnailsHtml;
        
        // Re-attach click handlers to thumbnails
        attachThumbnailHandlers();
    }
    
    // Handle thumbnail click to change main image
    function attachThumbnailHandlers() {
        const thumbnails = document.querySelectorAll('.product-thumbnail-item');
        const mainImage = document.getElementById('productMainImage');
        
        thumbnails.forEach(function(thumbnail) {
            thumbnail.addEventListener('click', function() {
                const imageUrl = this.getAttribute('data-image-url');
                const imageAlt = this.getAttribute('data-image-alt');
                
                if (imageUrl && mainImage) {
                    // Update main image
                    mainImage.src = imageUrl;
                    mainImage.alt = imageAlt;
                    
                    // Update active state
                    thumbnails.forEach(function(thumb) {
                        thumb.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });
    }
    
    // Update description
    function updateVariantDescription() {
        const variant = window.getSelectedVariant();
        const descriptionElement = document.getElementById('variant-description');
        
        if (!descriptionElement) return;
        
        let description = '';
        if (variant && variant.description) {
            description = variant.description;
        } else {
            // Fallback to product description
            const productDescription = @json($product->description ?? $product->short_description ?? '');
            description = productDescription;
        }
        
        if (description) {
            // Render HTML directly (description is already HTML)
            descriptionElement.innerHTML = '<div class="p-0 mb-2">' + description + '</div>';
        } else {
            descriptionElement.innerHTML = '<p class="p-0 mb-2">No description available for this product.</p>';
        }
    }
    
    // Update highlights details
    function updateVariantHighlightsDetails() {
        const variant = window.getSelectedVariant();
        const highlightsElement = document.getElementById('variant-highlights-details');
        
        if (!highlightsElement) return;
        
        let html = '';
        const highlightsDetails = variant && variant.highlights_details ? variant.highlights_details : [];
        
        if (highlightsDetails.length > 0) {
            highlightsDetails.forEach((highlight, index) => {
                if (highlight.heading_name) {
                    html += '<h6 class="font-size-sm mb-2">' + escapeHtml(highlight.heading_name) + '</h6>';
                    if (highlight.bullet_points && Array.isArray(highlight.bullet_points) && highlight.bullet_points.length > 0) {
                        const isLast = index === highlightsDetails.length - 1;
                        html += '<ul class="lists-2 min-space' + (isLast ? ' mb-0' : '') + '">';
                        highlight.bullet_points.forEach(function(point) {
                            if (point) {
                                html += '<li>' + escapeHtml(point) + '</li>';
                            }
                        });
                        html += '</ul>';
                    }
                }
            });
        } else {
            html = '<p class="text-muted mb-0">No product information available.</p>';
        }
        
        highlightsElement.innerHTML = html;
    }
    
    // Escape HTML function
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Update Price using new pricing component
    function updateVariantPrice() {
        const variant = window.getSelectedVariant();
        
        // Use new pricing component update function if available
        if (typeof updateProductPricing === 'function') {
            if (variant) {
                updateProductPricing(variant);
            } else {
                // Fallback to price range display when no variant selected
                const minPrice = @json($minPrice ?? 0);
                const maxPrice = @json($maxPrice ?? 0);
                const minSalePrice = @json($minSalePrice ?? null);
                const hasSale = @json($hasSale ?? false);
                
                // Create a virtual variant for price range
                const priceRangeVariant = {
                    price: minPrice,
                    sale_price: minSalePrice,
                    discount_type: null,
                    discount_value: null,
                    discount_active: false,
                    has_sale: hasSale
                };
                
                updateProductPricing(priceRangeVariant);
            }
        }
    }
    
    // Update SKU
    function updateVariantSku() {
        const variant = window.getSelectedVariant();
        const skuElement = document.getElementById('variant-sku');
        const skuInfoElement = document.getElementById('variant-sku-info');
        const skuRowElement = document.getElementById('variant-sku-row');
        
        const sku = variant && variant.sku ? variant.sku : '';
        
        if (skuElement) {
            skuElement.textContent = sku || '';
        }
        
        if (skuInfoElement) {
            skuInfoElement.textContent = sku || '-';
        }
        
        if (skuRowElement) {
            skuRowElement.style.display = sku ? '' : 'none';
        }
    }
    
    // Update variant additional information (color, size)
    function updateVariantAdditionalInfo() {
        const selectedColor = document.querySelector('input[name="productColor"]:checked')?.value || '';
        const selectedSize = document.querySelector('input[name="productSize"]:checked')?.value || '';
        
        const colorInfoElement = document.getElementById('variant-color-info');
        const colorRowElement = document.getElementById('variant-color-row');
        const sizeInfoElement = document.getElementById('variant-size-info');
        const sizeRowElement = document.getElementById('variant-size-row');
        
        // Update color
        if (colorInfoElement && colorRowElement) {
            if (selectedColor) {
                colorInfoElement.textContent = selectedColor;
                colorRowElement.style.display = '';
            } else {
                colorRowElement.style.display = 'none';
            }
        }
        
        // Update size
        if (sizeInfoElement && sizeRowElement) {
            if (selectedSize) {
                sizeInfoElement.textContent = selectedSize;
                sizeRowElement.style.display = '';
            } else {
                sizeRowElement.style.display = 'none';
            }
        }
    }
    
    // Update add to cart button based on stock status
    function updateAddToCartButton() {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined' && typeof $ === 'undefined') {
            // jQuery not loaded yet, try again later
            setTimeout(updateAddToCartButton, 100);
            return;
        }
        
        const $ = typeof jQuery !== 'undefined' ? jQuery : window.$;
        const variant = window.getSelectedVariant ? window.getSelectedVariant() : null;
        const $addToCartBtn = $('.add-to-cart-btn');
        const $stockStatusMsg = $('#stock-status-message');
        const $stockStatusText = $('#stock-status-text');
        
        if (!$addToCartBtn.length) {
            return;
        }
        
        if (!variant) {
            // No variant selected - disable button
            $addToCartBtn.prop('disabled', true)
                .html('<i class="lni lni-shopping-basket me-2"></i>Select Options')
                .removeClass('btn-danger')
                .addClass('bg-dark');
            if ($stockStatusMsg.length) {
                $stockStatusMsg.hide();
            }
            return;
        }
        
        // Check if variant is in stock
        const isInStock = variant.is_in_stock !== false && variant.is_in_stock !== undefined;
        
        if (!isInStock) {
            // Out of stock - disable button and show message
            $addToCartBtn.prop('disabled', true)
                .addClass('btn-danger')
                .removeClass('bg-dark')
                .html('<i class="lni lni-close me-2"></i>Out of Stock');
            if ($stockStatusMsg.length) {
                $stockStatusMsg.show();
                $stockStatusText.text('This Item is currently out of stock.');
            }
        } else {
            // In stock - enable button
            $addToCartBtn.prop('disabled', false)
                .removeClass('btn-danger')
                .addClass('bg-dark')
                .html('<i class="lni lni-shopping-basket me-2"></i>Add to Cart');
            if ($stockStatusMsg.length) {
                $stockStatusMsg.hide();
            }
        }
    }
    
    // Update all variant information including images
    function updateVariantInfo() {
        updateProductImagesGallery(); // Update images first
        updateVariantPrice(); // Update price
        updateVariantDescription();
        updateVariantHighlightsDetails();
        updateVariantSku();
        updateVariantAdditionalInfo(); // Update additional info tab
        updateAddToCartButton(); // Update add to cart button based on stock
    }
    
    // Add event listeners for all attribute changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('attribute-option-product')) {
            // Check if it's a color-type attribute
            const attributeType = e.target.closest('[data-attribute-container]')?.querySelector('.form-option-color') ? 'color' : 'other';
            // Always update images when any attribute changes, as variant images may change
            updateVariantInfo();
        } else if (e.target.classList.contains('color-option-product')) {
            // Legacy color change
            updateVariantInfo();
        } else if (e.target.classList.contains('size-option-product')) {
            // Legacy size change - update price and all variant details
            updateVariantPrice();
            updateVariantDescription();
            updateVariantHighlightsDetails();
            updateVariantSku();
            updateVariantAdditionalInfo();
        }
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Attach thumbnail handlers
        attachThumbnailHandlers();
        // Update variant info (will call updateAddToCartButton)
        updateVariantInfo();
        
        // Also update button when jQuery is ready (for add to cart button)
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function() {
                updateAddToCartButton();
            });
        }
    });
})();
</script>

@push('scripts')
<script>
// Common Add to Cart Function - Wait for jQuery
(function() {
    function initAddToCart() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initAddToCart, 50);
            return;
        }
        
        jQuery(function($) {
            // Get session ID
            function getSessionId() {
                let sessionId = localStorage.getItem('session_id');
                if (!sessionId) {
                    sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('session_id', sessionId);
                }
                return sessionId;
            }
            
            // Add to cart function - can be called from anywhere
            window.addToCart = function(productId, variantId, quantity, callback) {
                const sessionId = getSessionId();
                
                $.ajax({
                    url: '/api/cart/items',
                    method: 'POST',
                    headers: {
                        'X-Session-ID': sessionId
                    },
                    data: {
                        product_id: productId,
                        product_variant_id: variantId || null,
                        quantity: quantity || 1,
                        session_id: sessionId
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({
                                    text: 'Product added to cart successfully!',
                                    pos: 'top-right',
                                    showAction: false,
                                    duration: 3000,
                                    textColor: '#fff',
                                    backgroundColor: '#151515'
                                });
                            }
                            // Update cart count in header
                            if (typeof updateCartCount === 'function') {
                                updateCartCount();
                            } else if (window.updateCartCount) {
                                window.updateCartCount();
                            }
                            if (callback && typeof callback === 'function') {
                                callback(true, response);
                            }
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message 
                            ? xhr.responseJSON.error.message 
                            : 'Failed to add product to cart';
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
                        if (callback && typeof callback === 'function') {
                            callback(false, xhr.responseJSON);
                        }
                    }
                });
            };
            
            // Handle add to cart button click on product page
            $(document).on('click', '.add-to-cart-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const productId = $btn.data('product-id');
                
                if (!productId) {
                    console.error('Product ID not found');
                    return;
                }
                
                // Get selected variant using getSelectedVariant function
                const variant = window.getSelectedVariant ? window.getSelectedVariant() : null;
                const variantId = variant ? variant.id : null;
                
                console.log('Add to Cart - Selected Variant:', variant);
                console.log('Add to Cart - Variant ID:', variantId);
                
                if (!variant || !variantId) {
                    Snackbar.show({
                        text: 'Please select all required attributes (size, color, etc.)',
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                    $btn.prop('disabled', false);
                    return;
                }
                
                // Check if variant is in stock
                const isInStock = variant.is_in_stock !== false && variant.is_in_stock !== undefined;
                if (!isInStock) {
                    Snackbar.show({
                        text: 'This product is currently out of stock.',
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                    $btn.prop('disabled', false);
                    return;
                }
                
                // Get quantity from the select element in the same form row
                const $formRow = $btn.closest('.form-row, .row');
                const quantitySelect = $formRow.find('select.custom-select').first()[0] || document.querySelector('.custom-select');
                const quantity = quantitySelect ? parseInt(quantitySelect.value) : 1;
                
                // Disable button while processing
                $btn.prop('disabled', true);
                
                // Add to cart
                window.addToCart(productId, variantId, quantity, function(success) {
                    $btn.prop('disabled', false);
                    if (success) {
                        // Update cart count in header
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        } else if (window.updateCartCount) {
                            window.updateCartCount();
                        }
                        // Optionally redirect to cart
                        // window.location.href = '{{ route("frontend.shoping-cart") }}';
                    }
                });
            });
        });
    }
    
    // Initialize when script loads
    initAddToCart();
})();
</script>
@endpush

@push('styles')
<style>
.product-main-image {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    background: #f8f9fa;
    height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-main-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.product-thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.product-thumbnail-item {
    width: 80px;
    height: 80px;
    border: 2px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-thumbnail-item:hover {
    border-color: #333;
    transform: translateY(-2px);
}

.product-thumbnail-item.active {
    border-color: #333;
    border-width: 3px;
}

.product-thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

@media (max-width: 768px) {
    .product-thumbnail-item {
        width: 60px;
        height: 60px;
    }
}

/* Wishlist Button Styling */
.snackbar-wishlist.wishlist-active,
.snackbar-wishlist.wishlist-active i,
.snackbar-wishlist.wishlist-active .fa-heart,
.snackbar-wishlist.wishlist-active .fas {
    color: #dc3545 !important;
}

.snackbar-wishlist.wishlist-active {
    border-color: #dc3545 !important;
}

.snackbar-wishlist .fas.fa-heart.text-danger,
.snackbar-wishlist.wishlist-active .fas.fa-heart {
    color: #dc3545 !important;
}
</style>
@endpush

@push('scripts')
{{-- Include pricing component JavaScript helper --}}
@include('frontend.partials.product-pricing-js')

<script>
$(document).ready(function() {
    // Check wishlist status on page load using session_id from localStorage
    function getSessionId() {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        return sessionId;
    }
    
    // Get the main product wishlist button
    const $wishlistBtn = $('.snackbar-wishlist[data-product-id="{{ $product->id }}"]').first();
    
    if ($wishlistBtn.length > 0) {
        const productId = $wishlistBtn.data('product-id');
        const sessionId = getSessionId();
        
        // Check wishlist status via API
        $.ajax({
            url: '/api/wishlist',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                if (response.success && response.data) {
                    const isInWishlist = response.data.some(function(item) {
                        return item.product_id == productId;
                    });
                    
                    // Update button state if status changed
                    const currentState = $wishlistBtn.data('in-wishlist') == '1';
                    if (isInWishlist !== currentState) {
                        updateWishlistButton($wishlistBtn, isInWishlist);
                    }
                }
            },
            error: function(xhr) {
                // Silently fail - keep current state
                console.log('Could not check wishlist status (possibly offline)');
            }
        });
    }
    
    // Function to update wishlist button state
    function updateWishlistButton($btn, isInWishlist) {
        const $icon = $btn.find('i');
        
        if (isInWishlist) {
            // Add to wishlist state
            $btn.addClass('wishlist-active text-danger');
            $btn.attr('data-in-wishlist', '1');
            $btn.css('color', '#dc3545');
            $icon.removeClass('lni lni-heart').addClass('fas fa-heart text-danger').css('color', '#dc3545');
        } else {
            // Remove from wishlist state
            $btn.removeClass('wishlist-active text-danger');
            $btn.attr('data-in-wishlist', '0');
            $btn.css('color', '');
            $icon.removeClass('fas fa-heart text-danger').addClass('lni lni-heart').css('color', '');
        }
    }
    
    // ==================== Reviews Functionality ====================
    const productId = {{ $product->id }};
    
    // Load reviews for the product
    function loadReviewsForProduct() {
        if (!productId) {
            $('#reviewsList').html('<div class="text-center py-5"><p class="text-danger">Product ID not found</p></div>');
            return;
        }
        
        $('#reviewProductId').val(productId);
        
        // Load reviews
        $.ajax({
            url: '/api/reviews/product/' + productId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    displayReviews(response.data);
                    checkCanReview(productId);
                }
            },
            error: function(xhr) {
                console.error('Error loading reviews:', xhr);
                $('#reviewsList').html('<div class="text-center py-5"><p class="text-danger">Error loading reviews. Please try again.</p></div>');
                // Reset product rating display on error
                updateProductRatingDisplay(0, 0);
            }
        });
    }
    
    // Display reviews
    function displayReviews(data) {
        const reviews = data.reviews || [];
        const averageRating = data.average_rating || 0;
        const totalReviews = data.total_reviews || 0;
        const ratingDistribution = data.rating_distribution || {5: 0, 4: 0, 3: 0, 2: 0, 1: 0};
        
        // Update summary
        $('#averageRatingDisplay').text(averageRating.toFixed(1));
        updateStarRating('#averageRatingStars', averageRating);
        $('#totalReviewsDisplay').text(totalReviews + ' Review' + (totalReviews !== 1 ? 's' : ''));
        
        // Update product rating display (top of page)
        updateProductRatingDisplay(averageRating, totalReviews);
        
        // Update rating distribution
        let distributionHtml = '';
        for (let i = 5; i >= 1; i--) {
            const count = ratingDistribution[i] || 0;
            const percentage = totalReviews > 0 ? (count / totalReviews * 100) : 0;
            distributionHtml += `
                <div class="d-flex align-items-center mb-2">
                    <small class="me-2" style="width: 30px;">${i} <i class="fas fa-star text-warning"></i></small>
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: ${percentage}%"></div>
                    </div>
                    <small class="ms-2 text-muted">${count}</small>
                </div>
            `;
        }
        $('#ratingDistribution').html(distributionHtml);
        
        // Display reviews list
        if (reviews.length === 0) {
            $('#reviewsList').html('<div class="text-center py-5"><p class="text-muted">No reviews yet. Be the first to review this product!</p></div>');
        } else {
            let reviewsHtml = '';
            reviews.forEach(function(review) {
                const customerImage = review.customer_image || '{{ asset("frontend/images/user-placeholder.jpg") }}';
                reviewsHtml += `
                    <div class="single_rev d-flex align-items-start ${reviews.indexOf(review) < reviews.length - 1 ? 'br-bottom' : ''} py-3">
                        <div class="single_rev_thumb">
                            <img src="${customerImage}" class="img-fluid circle" width="90" alt="${review.customer_name}" />
                        </div>
                        <div class="single_rev_caption d-flex align-items-start ps-3">
                            <div class="single_capt_left">
                                <h5 class="mb-0 fs-md ft-medium lh-1">${review.customer_name}</h5>
                                <span class="small">${review.created_at}</span>
                                ${review.comment ? '<p>' + escapeHtml(review.comment) + '</p>' : ''}
                            </div>
                            <div class="single_capt_right">
                                <div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
                                    ${generateStarRating(review.rating)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#reviewsList').html(reviewsHtml);
        }
    }
    
    // Check if user can review
    function checkCanReview(productId) {
        $.ajax({
            url: '/api/reviews/can-review/' + productId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    if (response.data.can_review) {
                        $('#reviewFormContainer').show();
                        $('#reviewLoginPrompt').hide();
                    } else {
                        $('#reviewFormContainer').hide();
                        if (response.data.reason === 'not_logged_in') {
                            $('#reviewLoginPrompt').show();
                        } else {
                            $('#reviewLoginPrompt').hide();
                        }
                    }
                }
            },
            error: function(xhr) {
                // If not logged in, show login prompt
                if (xhr.status === 401 || xhr.status === 0) {
                    $('#reviewFormContainer').hide();
                    $('#reviewLoginPrompt').show();
                }
            }
        });
    }
    
    // Generate star rating HTML
    function generateStarRating(rating) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                html += '<i class="fas fa-star filled"></i>';
            } else {
                html += '<i class="far fa-star"></i>';
            }
        }
        return html;
    }
    
    // Update star rating display
    function updateStarRating(selector, rating) {
        const $stars = $(selector).find('i');
        $stars.each(function(index) {
            if (index < Math.round(rating)) {
                $(this).removeClass('far').addClass('fas filled');
            } else {
                $(this).removeClass('fas filled').addClass('far');
            }
        });
    }
    
    // Update product rating display (top of product page)
    function updateProductRatingDisplay(averageRating, totalReviews) {
        // Update stars
        updateStarRating('#productRatingDisplay', averageRating);
        
        // Update review count
        const reviewText = totalReviews === 0 
            ? '(No Reviews)' 
            : '(' + totalReviews + ' Review' + (totalReviews !== 1 ? 's' : '') + ')';
        $('#productReviewCount').text(reviewText);
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }
    
    // Handle review form submission
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        const productId = $('#reviewProductId').val();
        const rating = $('input[name="rating"]:checked').val();
        const comment = $('#reviewComment').val().trim();
        
        if (!productId) {
            Snackbar.show({
                text: 'Product ID not found',
                pos: 'top-right',
                showAction: false,
                duration: 3000,
                textColor: '#fff',
                backgroundColor: '#dc3545'
            });
            return;
        }
        
        if (!rating) {
            Snackbar.show({
                text: 'Please select a rating',
                pos: 'top-right',
                showAction: false,
                duration: 3000,
                textColor: '#fff',
                backgroundColor: '#dc3545'
            });
            return;
        }
        
        const formData = {
            product_id: productId,
            rating: rating,
            comment: comment || null,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
        
        $.ajax({
            url: '/api/reviews',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Snackbar.show({
                        text: response.message || 'Review submitted successfully! It will be visible after admin approval.',
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#28a745'
                    });
                    $('#reviewForm')[0].reset();
                    $('#selectedRatingDisplay').text('Select Rating');
                    $('#commentCharCount').text('0');
                    // Reload reviews to update display (even though new review is inactive, it updates the form state)
                    loadReviewsForProduct();
                    checkCanReview(productId);
                } else {
                    Snackbar.show({
                        text: response.error?.message || 'Failed to submit review. Please try again.',
                        pos: 'top-right',
                        showAction: false,
                        duration: 3000,
                        textColor: '#fff',
                        backgroundColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to submit review. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error.message || errorMessage;
                }
                Snackbar.show({
                    text: errorMessage,
                    pos: 'top-right',
                    showAction: false,
                    duration: 3000,
                    textColor: '#fff',
                    backgroundColor: '#dc3545'
                });
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Update selected rating display
    $('input[name="rating"]').on('change', function() {
        const rating = $(this).val();
        $('#selectedRatingDisplay').text(rating + ' Star' + (rating > 1 ? 's' : ''));
    });
    
    // Update character count
    $('#reviewComment').on('input', function() {
        const length = $(this).val().length;
        $('#commentCharCount').text(length);
    });
    
    // Load reviews when reviews tab is clicked
    $('#reviews-tab').on('click', function() {
        loadReviewsForProduct();
    });
    
    // Load initial reviews on page load
    $(document).ready(function() {
        setTimeout(function() {
            loadReviewsForProduct();
        }, 500);
    });
});
</script>
@endpush
			
@endsection