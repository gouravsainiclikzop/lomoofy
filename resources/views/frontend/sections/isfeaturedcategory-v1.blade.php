		 
			@if(isset($collections) && $collections->count() > 0)
			<section class="middle" id="isfeaturedcategory-v1">
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