
			
		<!-- ============================ Hero Banner Variant second Start================================== -->
		@if($homeSliders->count() > 0)
		<div class="home-slider hide-navigation margin-bottom-0" id="issliderbanner-v2">
			@foreach($homeSliders as $index => $slider)
			<!-- Slide -->
			<div data-background-image="{{ $slider['image'] }}" class="item" data-overlay="3">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div class="home-slider-container">

								<!-- Slide Title -->
								<div class="home-slider-desc {{ $index % 2 == 0 ? 'text-center' : '' }}">
									<div class="home-slider-title mb-4">
										@if($slider['category']) 
											<h5 class="text-light fs-lg ft-medium mb-0">{{ $slider['category']['name'] }} Collection</h5>
										@elseif($slider['tagline'])
											<h5 class="text-light fs-lg ft-medium mb-0">{{ $slider['tagline'] }}</h5>
										@endif
										@if($slider['title'])
											<h1 class="mb-1 ft-bold text-light lg-heading">{!! $slider['title'] !!}</h1>
										@endif
										@if($slider['tagline'] && $slider['category'])
											<span class="trending text-light">{{ $slider['tagline'] }}</span>
										@endif
									</div>
									
									@if($slider['category'])
										<a href="{{ route('frontend.shop') }}?category={{ $slider['category']['slug'] }}" class="btn btn-white stretched-links">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
									@else
										<a href="{{ route('frontend.shop') }}" class="btn btn-white stretched-links">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
									@endif
									
								</div>
								<!-- Slide Title / End -->

							</div>
						</div>
					</div>
				</div>
			</div>
			@endforeach
		</div>
		@endif
		<!-- ============================ Hero Banner variant second End ================================== -->
		 
		@if($parentCategories->count() > 0)
		<section class="p-0 issliderbanner-v2" >
			<div class="container">
				<div class="row overlio">
					@foreach($parentCategories->take(4) as $category)
					@php
						// Get all category IDs including children for product count
						$categoryIds = $category->getDescendantIds();
						$categoryIds[] = $category->id;
						
						// Count products in this category and all its children
						$productCount = \App\Models\Product::where('status', 'published')
							->whereHas('variants', function($q) {
								$q->where('is_active', true);
							})
							->where(function($q) use ($categoryIds) {
								$q->whereIn('category_id', $categoryIds)
								  ->orWhereHas('categories', function($catQuery) use ($categoryIds) {
									  $catQuery->whereIn('categories.id', $categoryIds);
								  });
							})
							->count();
							
						// Get category image
						$categoryImage = $category->image 
							? asset('storage/' . $category->image) 
							: asset('frontend/images/placeholder-category.png');
					@endphp
					<div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
						<div class="cats_caption_wrap">
							<div class="cats_caption_thumb mb-2">
								<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}" class="d-block">
									<img src="{{ $categoryImage }}" class="img-fluid rounded" alt="{{ $category->name }}">
								</a>
							</div>
							<div class="cats_caption text-center">
								<h4 class="m-0">{{ $category->name }}</h4>
								<span class="text-muted">{{ number_format($productCount) }} Collections</span>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</section>
		@endif
		<!-- =======================Banner second variant category style 1 ======================== -->
			 