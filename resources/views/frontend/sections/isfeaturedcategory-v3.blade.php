

<!-- featured categories sections variants three -->
@php
	// Get FeaturedCategoryStyle items for variant three (4 items: 2 small, 1 large, 2 small)
	$variantThreeStyles = \App\Models\FeaturedCategoryStyle::where('is_active', true)
		->with('category')
		->orderBy('sort_order')
		->limit(5)
		->get();
	
	// Calculate product counts for variant three categories
	$variantThreeProductCounts = [];
	if ($variantThreeStyles->count() > 0) {
		foreach($variantThreeStyles as $style) {
			if ($style->category) {
				$catId = $style->category->id;
				$category = $style->category;
				$categoryIdsForCount = $category->getDescendantIds();
				$categoryIdsForCount[] = $catId;
				
				$primaryProductIds = \App\Models\Product::where('category_id', $catId)
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
				$variantThreeProductCounts[$catId] = count($uniqueProductIds);
			}
		}
	}
@endphp
@if($variantThreeStyles->count() >= 4)
<section class="p-0" id="isfeaturedcategory-v3">
				<div class="container-fluid">
					<div class="row g-0">
						
						<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
							<!-- row -->
							<div class="row no-gutters">
								@if($variantThreeStyles->count() > 0)
									@php $style1 = $variantThreeStyles[0]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-6 col-sm-6">
										<div class="single_cats">
											<a href="{{ $style1->category ? route('frontend.shop') . '?category=' . $style1->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style1->featured_image ? asset('storage/' . $style1->featured_image) : ($style1->category && $style1->category->image ? asset('storage/' . $style1->category->image) : asset('frontend/images/c-3.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style1->title }}</h2>
														@if($style1->category && isset($variantThreeProductCounts[$style1->category->id]))
															<span>{{ $variantThreeProductCounts[$style1->category->id] }} Items</span>
														@endif
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								<!-- /row -->
							
								<!-- row -->
								@if($variantThreeStyles->count() > 1)
									@php $style2 = $variantThreeStyles[1]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-6 col-sm-6">
										<div class="single_cats">
											<a href="{{ $style2->category ? route('frontend.shop') . '?category=' . $style2->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style2->featured_image ? asset('storage/' . $style2->featured_image) : ($style2->category && $style2->category->image ? asset('storage/' . $style2->category->image) : asset('frontend/images/c-5.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style2->title }}</h2>
														@if($style2->category && isset($variantThreeProductCounts[$style2->category->id]))
															<span>{{ $variantThreeProductCounts[$style2->category->id] }} Items</span>
														@endif
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								<!-- /row -->
							</div>
							
						</div>
						
						<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
							@if($variantThreeStyles->count() > 2)
								@php $style3 = $variantThreeStyles[2]; @endphp
								<div class="single_cats">
									<a href="{{ $style3->category ? route('frontend.shop') . '?category=' . $style3->category->slug : '#' }}" class="cards card-overflow card-scale lg_height">
										<div class="bg-image" style="background:url({{ $style3->featured_image ? asset('storage/' . $style3->featured_image) : ($style3->category && $style3->category->image ? asset('storage/' . $style3->category->image) : asset('frontend/images/c-1.png')) }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style3->title }}</h2>
												@if($style3->category && isset($variantThreeProductCounts[$style3->category->id]))
													<span>{{ $variantThreeProductCounts[$style3->category->id] }} Items</span>
												@endif
											</div>
										</div>
									</a>
								</div>
							@endif
						</div>
						
						<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
							<!-- row -->
							<div class="row no-gutters">
								@if($variantThreeStyles->count() > 3)
									@php $style4 = $variantThreeStyles[3]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-6 col-sm-6">
										<div class="single_cats">
											<a href="{{ $style4->category ? route('frontend.shop') . '?category=' . $style4->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style4->featured_image ? asset('storage/' . $style4->featured_image) : ($style4->category && $style4->category->image ? asset('storage/' . $style4->category->image) : asset('frontend/images/c-11.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style4->title }}</h2>
														@if($style4->category && isset($variantThreeProductCounts[$style4->category->id]))
															<span>{{ $variantThreeProductCounts[$style4->category->id] }} Items</span>
														@endif
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
								
								@if($variantThreeStyles->count() > 4)
									@php $style5 = $variantThreeStyles[4]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-6 col-sm-6">
										<div class="single_cats">
											<a href="{{ $style5->category ? route('frontend.shop') . '?category=' . $style5->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style5->featured_image ? asset('storage/' . $style5->featured_image) : ($style5->category && $style5->category->image ? asset('storage/' . $style5->category->image) : asset('frontend/images/c-12.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style5->title }}</h2>
														@if($style5->category && isset($variantThreeProductCounts[$style5->category->id]))
															<span>{{ $variantThreeProductCounts[$style5->category->id] }} Items </span>
														@endif
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