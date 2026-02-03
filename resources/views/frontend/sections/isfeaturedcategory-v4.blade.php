


<!-- featured categories sections variants four -->
@php
	// Get FeaturedCategoryStyle items for variant four (2 large on top, 3 small below = 5 items)
	$variantFourStyles = \App\Models\FeaturedCategoryStyle::where('is_active', true)
		->with('category')
		->orderBy('sort_order')
		->limit(5)
		->get();
	
	// Calculate product counts and discount percentages for variant four categories
	$variantFourProductCounts = [];
	$variantFourDiscounts = [];
	if ($variantFourStyles->count() > 0) {
		foreach($variantFourStyles as $style) {
			if ($style->category) {
				$cat = $style->category;
				$categoryIdsForCount = $cat->getDescendantIds();
				$categoryIdsForCount[] = $cat->id;
				
				$primaryProductIds = \App\Models\Product::where('category_id', $cat->id)
					->where('status', 'published')
					->pluck('id')
					->toArray();
				
				$pivotProductIds = \DB::table('product_categories')
					->join('products', 'product_categories.product_id', '=', 'products.id')
					->whereIn('product_categories.category_id', $categoryIdsForCount)
					->where('products.status', 'published')
					->distinct()
					->pluck('product_categories.product_id')
					->toArray();
				
				$uniqueProductIds = array_unique(array_merge($primaryProductIds, $pivotProductIds));
				$variantFourProductCounts[$cat->id] = count($uniqueProductIds);
				
				// Calculate average discount percentage for products in this category
				$productsWithDiscount = \App\Models\Product::whereIn('id', $uniqueProductIds)
					->whereHas('variants', function($q) {
						$q->where('is_active', true)
						  ->where(function($vq) {
							  $vq->whereNotNull('sale_price')
								 ->whereColumn('sale_price', '<', 'price')
								 ->orWhere(function($dq) {
									 $dq->where('discount_active', true)
										->whereNotNull('discount_type')
										->whereNotNull('discount_value');
								 });
						  });
					})
					->count();
				
				// Calculate discount percentage (simplified - can be enhanced)
				if (count($uniqueProductIds) > 0) {
					$discountPercent = min(50, round(($productsWithDiscount / count($uniqueProductIds)) * 100));
					$variantFourDiscounts[$cat->id] = $discountPercent > 0 ? $discountPercent : null;
				}
			}
		}
	}
@endphp
@if($variantFourStyles->count() >= 5)
<section class="p-0" id="isfeaturedcategory-v4">
				<div class="container-fluid">
					<div class="row g-0">
						
						@if($variantFourStyles->count() > 0)
							@php $style1 = $variantFourStyles[0]; @endphp
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
								<div class="single_cats">
									<a href="{{ $style1->category ? route('frontend.shop') . '?category=' . $style1->category->slug : '#' }}" class="cards card-overflow card-scale mid_height">
										<div class="bg-image" style="background:url({{ $style1->featured_image ? asset('storage/' . $style1->featured_image) : ($style1->category && $style1->category->image ? asset('storage/' . $style1->category->image) : asset('frontend/images/bt-1.png')) }})no-repeat;" data-overlay="4"></div>
										<div class="ct_body">
											<div class="ct_body_caption center text-center">	
												<h6 class="mb-1 text-light">New Collections</h6>
												<h1 class="m-0 ft-bold lh-1 text-light fs-md text-upper">{{ strtoupper($style1->title) }}</h1>
											</div>
											<div class="ct_footer center">
												<span class="btn btn-white stretched-links fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							</div>
						@endif
						
						@if($variantFourStyles->count() > 1)
							@php $style2 = $variantFourStyles[1]; @endphp
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
								<div class="single_cats">
									<a href="{{ $style2->category ? route('frontend.shop') . '?category=' . $style2->category->slug : '#' }}" class="cards card-overflow card-scale mid_height">
										<div class="bg-image" style="background:url({{ $style2->featured_image ? asset('storage/' . $style2->featured_image) : ($style2->category && $style2->category->image ? asset('storage/' . $style2->category->image) : asset('frontend/images/bt-2.png')) }})no-repeat;" data-overlay="4"></div>
										<div class="ct_body">
											<div class="ct_body_caption center text-center">
												<h6 class="mb-1 text-light">New Collections</h6>
												<h1 class="m-0 ft-bold lh-1 text-light fs-lg text-upper">{{ strtoupper($style2->title) }}</h1>
											</div>
											<div class="ct_footer center">
												<span class="btn btn-white stretched-links fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							</div>
						@endif
					</div>
					
					<div class="row no-gutters exlio_gutters">
						
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<!-- row -->
							<div class="row no-gutters">
								
								@if($variantFourStyles->count() > 2)
									@php $style3 = $variantFourStyles[2]; @endphp
									<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
										<div class="single_cats">
											<a href="{{ $style3->category ? route('frontend.shop') . '?category=' . $style3->category->slug : '#' }}" class="cards card-overflow card-scale mid_height">
												<div class="bg-image" style="background:url({{ $style3->featured_image ? asset('storage/' . $style3->featured_image) : ($style3->category && $style3->category->image ? asset('storage/' . $style3->category->image) : asset('frontend/images/b-8.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">
														@if($style3->category && isset($variantFourDiscounts[$style3->category->id]) && $variantFourDiscounts[$style3->category->id])
															<h6 class="mb-1">Up to {{ $variantFourDiscounts[$style3->category->id] }}% Off</h6>
														@endif
														<h1 class="mb-2 ft-bold lh-1 fs-md text-upper">{{ $style3->title }}</h1>
														@if($style3->category && isset($variantFourProductCounts[$style3->category->id]))
															<span>{{ $variantFourProductCounts[$style3->category->id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								
								@if($variantFourStyles->count() > 3)
									@php $style4 = $variantFourStyles[3]; @endphp
									<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
										<div class="single_cats">
											<a href="{{ $style4->category ? route('frontend.shop') . '?category=' . $style4->category->slug : '#' }}" class="cards card-overflow card-scale mid_height">
												<div class="bg-image" style="background:url({{ $style4->featured_image ? asset('storage/' . $style4->featured_image) : ($style4->category && $style4->category->image ? asset('storage/' . $style4->category->image) : asset('frontend/images/b-3.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														@if($style4->category && isset($variantFourDiscounts[$style4->category->id]) && $variantFourDiscounts[$style4->category->id])
															<h6 class="mb-1">Up to {{ $variantFourDiscounts[$style4->category->id] }}% Off</h6>
														@endif
														<h1 class="mb-2 ft-bold lh-1 fs-md text-upper">{{ $style4->title }}</h1>
														@if($style4->category && isset($variantFourProductCounts[$style4->category->id]))
															<span>{{ $variantFourProductCounts[$style4->category->id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								
								@if($variantFourStyles->count() > 4)
									@php $style5 = $variantFourStyles[4]; @endphp
									<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
										<div class="single_cats">
											<a href="{{ $style5->category ? route('frontend.shop') . '?category=' . $style5->category->slug : '#' }}" class="cards card-overflow card-scale mid_height">
												<div class="bg-image" style="background:url({{ $style5->featured_image ? asset('storage/' . $style5->featured_image) : ($style5->category && $style5->category->image ? asset('storage/' . $style5->category->image) : asset('frontend/images/c-8.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														@if($style5->category && isset($variantFourDiscounts[$style5->category->id]) && $variantFourDiscounts[$style5->category->id])
															<h6 class="mb-1">Up to {{ $variantFourDiscounts[$style5->category->id] }}% Off</h6>
														@endif
														<h1 class="mb-2 ft-bold lh-1 fs-md text-upper">{{ $style5->title }}</h1>
														@if($style5->category && isset($variantFourProductCounts[$style5->category->id]))
															<span>{{ $variantFourProductCounts[$style5->category->id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="stretched-link fs-md">Browse Items <i class="ti-arrow-circle-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								
							</div>
							<!-- /row -->
							
						</div>
					</div>
				</div>
			</section>
@endif

