

<!-- featured categories sections variants six -->
@php
	// Get FeaturedCategoryStyle items for variant six (2 offer banners)
	$variantSixStyles = \App\Models\FeaturedCategoryStyle::where('is_active', true)
		->with('category')
		->orderBy('sort_order')
		->limit(2)
		->get();
	
	// Calculate maximum discount percentage for each category
	$variantSixDiscounts = [];
	if ($variantSixStyles->count() > 0) {
		foreach($variantSixStyles as $style) {
			if ($style->category) {
				$cat = $style->category;
				$categoryIdsForCount = $cat->getDescendantIds();
				$categoryIdsForCount[] = $cat->id;
				
				// Get products in this category
				$products = \App\Models\Product::where('status', 'published')
					->where(function($q) use ($cat, $categoryIdsForCount) {
						$q->where('category_id', $cat->id)
						  ->orWhereHas('categories', function($cq) use ($categoryIdsForCount) {
							  $cq->whereIn('categories.id', $categoryIdsForCount);
						  });
					})
					->whereHas('variants', function($vq) {
						$vq->where('is_active', true)
						   ->where(function($sq) {
							   $sq->whereNotNull('sale_price')
								  ->whereColumn('sale_price', '<', 'price')
								  ->orWhere(function($dq) {
									  $dq->where('discount_active', true)
										 ->whereNotNull('discount_type')
										 ->whereNotNull('discount_value');
								  });
						   });
					})
					->with('variants')
					->get();
				
				$maxDiscount = 0;
				foreach($products as $product) {
					foreach($product->variants->where('is_active', true) as $variant) {
						$basePrice = $variant->price ?? 0;
						if ($basePrice > 0) {
							$finalPrice = $variant->final_price ?? $basePrice;
							if ($finalPrice < $basePrice) {
								$discount = round((($basePrice - $finalPrice) / $basePrice) * 100);
								$maxDiscount = max($maxDiscount, $discount);
							}
						}
					}
				}
				$variantSixDiscounts[$cat->id] = $maxDiscount > 0 ? $maxDiscount : null;
			}
		}
	}
@endphp
@if($variantSixStyles->count() >= 2)
<section class="middle" id="isfeaturedcategory-v6">
				<div class="container">
					<div class="row g-0">
						
						@if($variantSixStyles->count() > 0)
							@php $style1 = $variantSixStyles[0]; @endphp
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
								<div class="single_cats">
									<a href="{{ $style1->category ? route('frontend.shop') . '?category=' . $style1->category->slug : '#' }}" class="cards card-overflow card-scale">
										<div class="bg-image" style="background:url({{ $style1->featured_image ? asset('storage/' . $style1->featured_image) : ($style1->category && $style1->category->image ? asset('storage/' . $style1->category->image) : asset('frontend/images/offer-1.png')) }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												@if($style1->category && isset($variantSixDiscounts[$style1->category->id]) && $variantSixDiscounts[$style1->category->id])
													<div class="p-5 d-flex align-items-center justify-content-center circle theme-bg text-center">
														<h2 class="m-0 ft-bold lh-1 text-light text-upper position-absolute">{{ $variantSixDiscounts[$style1->category->id] }}%<span class="d-block fs-sm">Off</span></h2>
													</div>
												@endif
											</div>
											<div class="ct_footer left">
												<span class="stretched-link fs-md">Shop For {{ $style1->title }} <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							</div>
						@endif
						
						@if($variantSixStyles->count() > 1)
							@php $style2 = $variantSixStyles[1]; @endphp
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
								<div class="single_cats">
									<a href="{{ $style2->category ? route('frontend.shop') . '?category=' . $style2->category->slug : '#' }}" class="cards card-overflow card-scale">
										<div class="bg-image" style="background:url({{ $style2->featured_image ? asset('storage/' . $style2->featured_image) : ($style2->category && $style2->category->image ? asset('storage/' . $style2->category->image) : asset('frontend/images/offer-2.png')) }})no-repeat;"></div>
										<div class="ct_body">
											<div class="ct_body_caption left">	
												@if($style2->category && isset($variantSixDiscounts[$style2->category->id]) && $variantSixDiscounts[$style2->category->id])
													<div class="p-5 d-flex align-items-center justify-content-center circle theme-bg text-center">
														<h2 class="m-0 ft-bold lh-1 text-light text-upper position-absolute">{{ $variantSixDiscounts[$style2->category->id] }}%<span class="d-block fs-sm">Off</span></h2>
													</div>
												@endif
											</div>
											<div class="ct_footer left">
												<span class="stretched-link fs-md">Shop For {{ $style2->title }} <i class="ti-arrow-circle-right"></i></span>
											</div>
										</div>
									</a>
								</div>
							</div>
						@endif
						
					</div>
				</div>
			</section>
@endif
<!-- featured categories sections variants ended here -->
 