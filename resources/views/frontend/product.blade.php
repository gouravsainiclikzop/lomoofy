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
									 src="{{ $productImages->first()['url'] ?? asset('frontend/images/product/1.jpg') }}" 
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
										 data-image-url="{{ asset('frontend/images/product/1.jpg') }}" 
										 data-image-alt="{{ $product->name }}">
										<img src="{{ asset('frontend/images/product/1.jpg') }}" alt="{{ $product->name }}" class="img-fluid">
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
									<div class="text-left">
										<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
											<i class="fas fa-star filled"></i>
											<i class="fas fa-star filled"></i>
											<i class="fas fa-star filled"></i>
											<i class="fas fa-star filled"></i>
											<i class="fas fa-star"></i>
											<span class="small">(0 Reviews)</span>
										</div>
										<div class="elis_rty" id="product-price">
											@if($hasSale && $minSalePrice)
												<span class="ft-medium text-muted line-through fs-md me-2">₹{{ number_format($minPrice, 0) }}</span>
												<span class="ft-bold theme-cl fs-lg">₹{{ number_format($minSalePrice, 0) }}</span>
											@else
												<span class="ft-bold theme-cl fs-lg">₹{{ number_format($minPrice, 0) }}</span>
												@if($minPrice != $maxPrice && $maxPrice > 0)
													<span class="ft-bold theme-cl fs-lg"> - ₹{{ number_format($maxPrice, 0) }}</span>
												@endif
											@endif
										</div>
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
										<div class="prt_04 mb-4">
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

            

								<div class="prt_04 mb-4">
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
								</div>
								
								<div class="prt_05 mb-4">
									<div class="form-row row g-3 mb-7">
										<div class="col-12 col-md-6 col-lg-3">
											<!-- Quantity -->
											<select class="mb-2 custom-select">
											  <option value="1" selected="">1</option>
											  <option value="2">2</option>
											  <option value="3">3</option>
											  <option value="4">4</option>
											  <option value="5">5</option>
											</select>
										</div>
										<div class="col-12 col-md-12 col-lg-6">
											<!-- Submit -->
											<button type="button" class="btn btn-block custom-height bg-dark mb-2 w-100 add-to-cart-btn" 
												data-product-id="{{ $product->id }}"
												data-product-slug="{{ $product->slug }}">
												<i class="lni lni-shopping-basket me-2"></i>Add to Cart 
											</button>
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
									<div class="reviews_info">
										<div class="single_rev d-flex align-items-start br-bottom py-3">
											<div class="single_rev_thumb"><img src="{{ asset('frontend/images/team-1.jpg') }}" class="img-fluid circle" width="90" alt="" /></div>
											<div class="single_rev_caption d-flex align-items-start ps-3">
												<div class="single_capt_left">
													<h5 class="mb-0 fs-md ft-medium lh-1">Daniel Rajdesh</h5>
													<span class="small">30 jul 2021</span>
													<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum</p>
												</div>
												<div class="single_capt_right">
													<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
													</div>
												</div>
											</div>
										</div>
										
										<!-- Single Review -->
										<div class="single_rev d-flex align-items-start br-bottom py-3">
											<div class="single_rev_thumb"><img src="{{ asset('frontend/images/team-2.jpg') }}" class="img-fluid circle" width="90" alt="" /></div>
											<div class="single_rev_caption d-flex align-items-start ps-3">
												<div class="single_capt_left">
													<h5 class="mb-0 fs-md ft-medium lh-1">Seema Gupta</h5>
													<span class="small">30 Aug 2021</span>
													<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum</p>
												</div>
												<div class="single_capt_right">
													<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
													</div>
												</div>
											</div>
										</div>
										
										<!-- Single Review -->
										<div class="single_rev d-flex align-items-start br-bottom py-3">
											<div class="single_rev_thumb"><img src="{{ asset('frontend/images/team-3.jpg') }}" class="img-fluid circle" width="90" alt="" /></div>
											<div class="single_rev_caption d-flex align-items-start ps-3">
												<div class="single_capt_left">
													<h5 class="mb-0 fs-md ft-medium lh-1">Mark Jugermi</h5>
													<span class="small">10 Oct 2021</span>
													<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum</p>
												</div>
												<div class="single_capt_right">
													<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
													</div>
												</div>
											</div>
										</div>
										
										<!-- Single Review -->
										<div class="single_rev d-flex align-items-start py-3">
											<div class="single_rev_thumb"><img src="{{ asset('frontend/images/team-4.jpg') }}" class="img-fluid circle" width="90" alt="" /></div>
											<div class="single_rev_caption d-flex align-items-start ps-3">
												<div class="single_capt_left">
													<h5 class="mb-0 fs-md ft-medium lh-1">Meena Rajpoot</h5>
													<span class="small">17 Dec 2021</span>
													<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum</p>
												</div>
												<div class="single_capt_right">
													<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
														<i class="fas fa-star filled"></i>
													</div>
												</div>
											</div>
										</div>
										
									</div>
									
									<div class="reviews_rate">
										<form class="row g-3">
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<h4>Submit Rating</h4>
											</div>
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="revie_stars d-flex align-items-center justify-content-between px-2 py-2 gray rounded">
													<div class="srt_013">
														<div class="submit-rating">
														  <input id="star-5" type="radio" name="rating" value="star-5" />
														  <label for="star-5" title="5 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-4" type="radio" name="rating" value="star-4" />
														  <label for="star-4" title="4 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-3" type="radio" name="rating" value="star-3" />
														  <label for="star-3" title="3 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-2" type="radio" name="rating" value="star-2" />
														  <label for="star-2" title="2 stars">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														  <input id="star-1" type="radio" name="rating" value="star-1" />
														  <label for="star-1" title="1 star">
															<i class="active fa fa-star" aria-hidden="true"></i>
														  </label>
														</div>
													</div>
													
													<div class="srt_014">
														<h6 class="mb-0">4 Star</h6>
													</div>
												</div>
											</div>
											
											<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
												<div class="form-group">
													<label class="medium text-dark ft-medium mb-2">Full Name</label>
													<input type="text" class="form-control rounded-2" />
												</div>
											</div>
											
											<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
												<div class="form-group">
													<label class="medium text-dark ft-medium mb-2">Email Address</label>
													<input type="email" class="form-control rounded-2" />
												</div>
											</div>
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label class="medium text-dark ft-medium mb-2">Description</label>
													<textarea class="form-control rounded-2"></textarea>
												</div>
											</div>
											
											<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
												<div class="form-group m-0">
													<a class="btn btn-white stretched-links hover-black rounded-2">Submit Review <i class="lni lni-arrow-right"></i></a>
												</div>
											</div>
											
										</form>
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
    function getSelectedVariant() {
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
        }
        
        // Fallback to legacy color/size selection
        const selectedColor = document.querySelector('input[name="productColor"]:checked')?.value || '';
        const selectedSize = document.querySelector('input[name="productSize"]:checked')?.value || '';
        const key = selectedColor + '|' + selectedSize;
        return variantDataMap[key] || null;
    }
    
    // Update product images gallery based on selected variant
    function updateProductImagesGallery() {
        const variant = getSelectedVariant();
        const mainImageElement = document.getElementById('productMainImage');
        const thumbnailsContainer = document.getElementById('productThumbnailsContainer');
        
        if (!mainImageElement || !thumbnailsContainer) return;
        
        // Get images from selected variant
        let imagesToShow = [];
        
        // Try to get images from variant first (new dynamic attributes system)
        if (variant && variant.images && Array.isArray(variant.images) && variant.images.length > 0) {
            imagesToShow = variant.images.filter(function(img) {
                return img && img.url && img.url !== 'undefined' && img.url !== 'null';
            });
        }
        
        // If no images from exact variant match, try to find by color-type attribute
        if (imagesToShow.length === 0) {
            // Find color-type attribute from selected attributes
            const selectedAttributes = {};
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
                const attrInput = document.querySelector('.attribute-option-product[data-attribute-id="' + attrId + '"]:checked');
                if (attrInput && attrInput.closest('[data-attribute-container]')?.querySelector('.form-option-color')) {
                    colorAttributeId = attrId;
                    colorValue = selectedAttributes[attrId];
                    break;
                }
            }
            
            // If we found a color attribute, try to find variant with that color
            if (colorAttributeId && colorValue && window.variantDataMap) {
                // Search through all variants to find one with matching color
                for (let key in window.variantDataMap) {
                    const variantData = window.variantDataMap[key];
                    if (variantData.attributes && variantData.attributes[colorAttributeId] === colorValue) {
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
                const fallbackUrl = '{{ asset("frontend/images/product/1.jpg") }}';
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
        const variant = getSelectedVariant();
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
        const variant = getSelectedVariant();
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
    
    // Update Price
    function updateVariantPrice() {
        const variant = getSelectedVariant();
        const priceElement = document.getElementById('product-price');
        
        if (!priceElement) return;
        
        let priceHtml = '';
        if (variant && variant.price !== undefined) {
            const price = variant.price || 0;
            const salePrice = variant.sale_price;
            const hasSale = variant.has_sale || (salePrice && salePrice < price);
            
            if (hasSale && salePrice) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">₹' + 
                           Math.round(price) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg">₹' + Math.round(salePrice) + '</span>';
            } else {
                priceHtml = '<span class="ft-bold theme-cl fs-lg">₹' + Math.round(price) + '</span>';
            }
        } else {
            // Fallback to product price range
            const minPrice = @json($minPrice ?? 0);
            const maxPrice = @json($maxPrice ?? 0);
            const minSalePrice = @json($minSalePrice ?? null);
            const hasSale = @json($hasSale ?? false);
            
            if (hasSale && minSalePrice) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">₹' + 
                           Math.round(minPrice) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg">₹' + Math.round(minSalePrice) + '</span>';
            } else {
                priceHtml = '<span class="ft-bold theme-cl fs-lg">₹' + Math.round(minPrice) + '</span>';
                if (minPrice != maxPrice && maxPrice > 0) {
                    priceHtml += '<span class="ft-bold theme-cl fs-lg"> - ₹' + Math.round(maxPrice) + '</span>';
                }
            }
        }
        
        priceElement.innerHTML = priceHtml;
    }
    
    // Update SKU
    function updateVariantSku() {
        const variant = getSelectedVariant();
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
    
    // Update all variant information including images
    function updateVariantInfo() {
        updateProductImagesGallery(); // Update images first
        updateVariantPrice(); // Update price
        updateVariantDescription();
        updateVariantHighlightsDetails();
        updateVariantSku();
        updateVariantAdditionalInfo(); // Update additional info tab
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
        // Update variant info
        updateVariantInfo();
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
                
                // Get selected variant
                const variantDataMap = window.variantDataMap || {};
                const selectedColor = document.querySelector('input[name="productColor"]:checked')?.value || '';
                const selectedSize = document.querySelector('input[name="productSize"]:checked')?.value || '';
                const key = selectedColor + '|' + selectedSize;
                const variant = variantDataMap[key] || null;
                const variantId = variant ? variant.id : null;
                
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
    background: #fff;
}

.product-main-image img {
    display: block;
    width: 100%;
    height: auto;
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
    background: #fff;
    flex-shrink: 0;
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
			
@endsection