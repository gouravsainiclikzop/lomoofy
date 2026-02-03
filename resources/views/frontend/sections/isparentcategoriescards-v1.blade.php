
 
			@if($parentCategories->count() > 0)
			<section class="p-0" id="isparentcategoriescards-v1">
				<div class="container-fluid p-0">
					<div class="row g-0">
						@php
							// Calculate column class based on number of categories
							$categoryCount = $parentCategories->count();
							$colClass = 'col-xl-' . (12 / min($categoryCount, 3)) . ' col-lg-' . (12 / min($categoryCount, 3)) . ' col-md-' . (12 / min($categoryCount, 2)) . ' col-sm-12';
							$defaultImages = ['a-1.png', 'a-2.png', 'a-3.png'];
						@endphp
					
						@foreach($parentCategories as $index => $category)
						<div class="{{ $colClass }}">
							<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}" class="card card-overflow card-scale no-radius mb-0">
								<div class="bg-image" style="background:url({{ $category->image ? asset('storage/' . $category->image) : asset('frontend/images/' . ($defaultImages[$index] ?? 'a-1.png')) }})no-repeat;" data-overlay="2"></div>
								<div class="ct_body">
									<div class="ct_body_caption">	
										<h1 class="mb-0 ft-bold text-light">{{ $category->name }}</h1>
									</div>
									<div class="ct_footer">
										<span class="btn btn-white stretched-links">Shop {{ $category->name }} <i class="lni lni-arrow-right"></i>
										</span>
									</div>
								</div>
							</a>
						</div>
						@endforeach 
					</div>
				</div>
			</section>
			@endif