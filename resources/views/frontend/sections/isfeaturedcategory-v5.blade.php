
	
<!-- featured categories sections variants five -->
@php
	// Get FeaturedCategoryStyle items for variant five (1 large + 2 small = 3 items)
	$variantFiveStyles = \App\Models\FeaturedCategoryStyle::where('is_active', true)
		->with('category')
		->orderBy('sort_order')
		->limit(3)
		->get();
	
	// Calculate product counts for variant five categories
	$variantFiveProductCounts = [];
	if ($variantFiveStyles->count() > 0) {
		foreach($variantFiveStyles as $style) {
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
				$variantFiveProductCounts[$cat->id] = count($uniqueProductIds);
			}
		}
	}
@endphp
@if($variantFiveStyles->count() >= 3)
<section class="middle" id="isfeaturedcategory-v5">
				<div class="container-fluid">
					<div class="row g-0">
						
						@if($variantFiveStyles->count() > 0)
							@php $style1 = $variantFiveStyles[0]; @endphp
							<div class="col-xl-7 col-lg-6 col-md-6 col-sm-12">
								<div class="single_cats">
									<a href="{{ $style1->category ? route('frontend.shop') . '?category=' . $style1->category->slug : '#' }}" class="cards card-overflow card-scale lg_height">
										<div class="bg-image" style="background:url({{ $style1->featured_image ? asset('storage/' . $style1->featured_image) : ($style1->category && $style1->category->image ? asset('storage/' . $style1->category->image) : asset('frontend/images/b-1.png')) }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style1->title }}</h2>
												@if($style1->category && isset($variantFiveProductCounts[$style1->category->id]))
													<span>{{ $variantFiveProductCounts[$style1->category->id] }} Items</span>
												@endif
											</div>
											<div class="ct_footer left">
												<span class="btn stretched-links borders">Browse Items <i class="lni lni-arrow-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							</div>
						@endif
						
						<div class="col-xl-5 col-lg-6 col-md-6 col-sm-12">
							<!-- row -->
							<div class="row no-gutters">
								@if($variantFiveStyles->count() > 1)
									@php $style2 = $variantFiveStyles[1]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
										<div class="single_cats">
											<a href="{{ $style2->category ? route('frontend.shop') . '?category=' . $style2->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style2->featured_image ? asset('storage/' . $style2->featured_image) : ($style2->category && $style2->category->image ? asset('storage/' . $style2->category->image) : asset('frontend/images/b-3.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style2->title }}</h2>
														@if($style2->category && isset($variantFiveProductCounts[$style2->category->id]))
															<span>{{ $variantFiveProductCounts[$style2->category->id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="btn stretched-links borders">Browse Items <i class="lni lni-arrow-right"></i></span>
													</div>
												</div>
											</a>
										</div>
									</div>
								@endif
							</div>
							<!-- /row -->
							
							<!-- row -->
							<div class="row no-gutters">
								@if($variantFiveStyles->count() > 2)
									@php $style3 = $variantFiveStyles[2]; @endphp
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
										<div class="single_cats">
											<a href="{{ $style3->category ? route('frontend.shop') . '?category=' . $style3->category->slug : '#' }}" class="cards card-overflow card-scale md_height">
												<div class="bg-image" style="background:url({{ $style3->featured_image ? asset('storage/' . $style3->featured_image) : ($style3->category && $style3->category->image ? asset('storage/' . $style3->category->image) : asset('frontend/images/b-5.png')) }})no-repeat;"></div>
												<div class="ct_body">
													<div class="ct_body_caption left">	
														<h2 class="m-0 ft-bold lh-1 fs-md text-upper">{{ $style3->title }}</h2>
														@if($style3->category && isset($variantFiveProductCounts[$style3->category->id]))
															<span>{{ $variantFiveProductCounts[$style3->category->id] }} Items</span>
														@endif
													</div>
													<div class="ct_footer left">
														<span class="btn stretched-links borders">Browse Items <i class="lni lni-arrow-right"></i></span>
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