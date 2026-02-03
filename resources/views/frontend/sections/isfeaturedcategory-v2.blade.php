

@php
				$variantTwoCollections = $collections->take(2)->values();
			@endphp

				@if($variantTwoCollections->count() > 0)
				<section class="p-0" id="isfeaturedcategory-v2">
					<div class="container">
						<div class="row g-0">

							@foreach($variantTwoCollections as $index => $collection)
								@php
									$positionClass = $index % 2 === 0 ? 'right lis-top' : 'left lis-bottom';
									$textAlign = $index % 2 === 0 ? 'left' : 'right';

									$bgImage = $collection->featured_image
										? asset('storage/' . $collection->featured_image)
										: asset('frontend/images/c-22.png');

									$categorySlug = $collection->category?->slug;
									$link = $categorySlug
										? route('frontend.shop') . '?category=' . $categorySlug
										: '#';
										$itemCount = $collection->category_id && isset($productCounts[$collection->category_id])
										? $productCounts[$collection->category_id]
										: null;
									
								@endphp

								<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
									<div class="single_cats">
										<a href="{{ $link }}" class="cards card-overflow card-scale lg_height">
											<div class="bg-image {{ $index % 2 === 0 ? 'right' : 'left' }}"
												style="background:url({{ $bgImage }}) no-repeat;">
											</div>

											<div class="ct_body">
												<div class="ct_body_caption {{ $textAlign }} {{ $positionClass }}">
													<h2 class="ft-bold lh-1">
														{{ $collection->title }}
													</h2>
													@if(isset($collection->min_price) && $collection->min_price > 0)
													<span class="ft-medium text-underline">
														Start From  
															₹{{ number_format($collection->min_price, 0) }}  
													</span> 
													@elseif($itemCount !== null)
													<span class="ft-medium text-underline">
														({{ $itemCount }} Items)
													</span>
													@endif
												</div>
											</div>
										</a>
									</div>
								</div>

							@endforeach

						</div>
					</div>
				</section>
				@endif