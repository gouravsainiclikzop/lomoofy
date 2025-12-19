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
									@if($product->category)
										<li class="breadcrumb-item"><a href="{{ route('frontend.shop') }}?category={{ $product->category->slug }}">{{ $product->category->name }}</a></li>
										@if($product->category->parent)
											<li class="breadcrumb-item"><a href="{{ route('frontend.shop') }}?category={{ $product->category->parent->slug }}">{{ $product->category->parent->name }}</a></li>
										@endif
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
							<div class="sp-loading"><img src="{{ $productImages->first()['url'] ?? asset('frontend/images/product/1.jpg') }}" alt=""><br>LOADING IMAGES</div>
							<div class="sp-wrap" id="productImagesGallery">
								@foreach($productImages as $image)
									<a href="{{ $image['url'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}"></a>
								@endforeach
								@if($productImages->isEmpty())
									<a href="{{ asset('frontend/images/product/1.jpg') }}"><img src="{{ asset('frontend/images/product/1.jpg') }}" alt="{{ $product->name }}"></a>
								@endif
							</div>
						</div>
						
						<div class="col-xl-7 col-lg-6 col-md-12 col-sm-12">
							<div class="prd_details ps-xl-5">
								
								@if($product->category)
								<div class="prt_01 mb-2"><span class="text-success bg-light-success rounded px-2 py-1">{{ $product->category->name }}</span></div>
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
										<div class="elis_rty">
											@if($hasSale && $minSalePrice)
												<span class="ft-medium text-muted line-through fs-md me-2">${{ number_format($minPrice, 0) }}</span>
												<span class="ft-bold theme-cl fs-lg">${{ number_format($minSalePrice, 0) }}</span>
											@else
												<span class="ft-bold theme-cl fs-lg">${{ number_format($minPrice, 0) }}</span>
												@if($minPrice != $maxPrice && $maxPrice > 0)
													<span class="ft-bold theme-cl fs-lg"> - ${{ number_format($maxPrice, 0) }}</span>
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

            

								<div class="prt_04 mb-4">
									@if($product->category)
									<p class="d-flex align-items-center mb-1">Category:<strong class="fs-sm text-dark ft-medium ms-1">
										{{ $product->category->name }}{{ $product->category->parent ? ', ' . $product->category->parent->name : '' }}
									</strong></p>
									@endif
									@if($displaySku)
									<p class="d-flex align-items-center mb-0">SKU:<strong class="fs-sm text-dark ft-medium ms-1">{{ $displaySku }}</strong></p>
									@endif
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
											<a  href="{{ route('frontend.shoping-cart') }}" class="btn btn-block custom-height bg-dark mb-2 w-100 text-decoration-none">
												<i class="lni lni-shopping-basket me-2" ></i>Add to Cart 
											</a>
										</div>
										<div class="col-12 col-md-6 col-lg-3">
											<!-- Wishlist -->
											<button class="btn custom-height btn-default btn-block mb-2 text-dark w-100 snackbar-wishlist {{ $inWishlist ? 'wishlist-active' : '' }}" 
												data-product-id="{{ $product->id }}" 
												data-in-wishlist="{{ $inWishlist ? '1' : '0' }}">
												<i class="lni lni-heart me-2 {{ $inWishlist ? 'text-danger' : '' }}"></i>Wishlist
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
                        <div class="card-body pt-0">
                          <h6 class="font-size-sm mb-2">Composition</h6>
                          <ul class="lists-2 min-space">
                            <li>Elastic rib: Cotton 95%, Elastane 5%</li>
                            <li>Lining: Cotton 100%</li>
                            <li>Cotton 80%, Polyester 20%</li>
                          </ul>
                          <h6 class="font-size-sm mb-2">Design. No.</h6>
                          <ul class="lists-2 min-space mb-0">
                            <li>183260098</li>
                          </ul>
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
									<div class="description_info">
										@if($product->description)
											<p class="p-0 mb-2">{!! nl2br(e($product->description)) !!}</p>
										@elseif($product->short_description)
											<p class="p-0 mb-2">{!! nl2br(e($product->short_description)) !!}</p>
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
												@if($displaySku)
												<tr>
												  <th class="ft-medium text-dark">SKU</th>
												  <td>{{ $displaySku }}</td>
												</tr>
												@endif
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
												@if($product->category)
												<tr>
												  <th class="ft-medium text-dark">Category</th>
												  <td>{{ $product->category->name }}{{ $product->category->parent ? ' > ' . $product->category->parent->name : '' }}</td>
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
																<span class="text-muted ft-medium line-through me-2">${{ number_format($similarProduct['min_price'], 0) }}</span>
																<span class="ft-medium theme-cl fs-md">${{ number_format($similarProduct['min_sale_price'], 0) }}</span>
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
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Formal Women Lowers</a></h5>
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
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Half Running Women Suit</a></h5>
													<div class="elis_rty"><span class="ft-medium fs-md text-dark">$80.00</span></div>
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
												<a class="card-img-top d-block overflow-hidden" href="{{ route('frontend.product') }}"><img class="card-img-top" src="{{ asset('frontend/images/product/11.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-3 pb-0 d-flex align-items-start justify-content-center">
											<div class="text-left">
												<div class="text-center">
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Half Fancy Women Dress</a></h5>
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
													<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="{{ route('frontend.product') }}">Flix Flox Women Jeans</a></h5>
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
			<!-- ======================= Similar Products Start ============================ -->
			
@endsection