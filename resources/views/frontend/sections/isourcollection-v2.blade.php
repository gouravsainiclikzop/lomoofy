

<!-- our collection old normal section starts here -->
@if($ourCollection && ($ourCollection->heading || $ourCollection->description))
			<section class="bg-cover" style="background:url({{ $ourCollection->background_image ? asset('storage/' . $ourCollection->background_image) : asset('frontend/images/bg-2.jpg') }}) no-repeat;" data-overlay="1" id="isourcollection-v2">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-xl-8 col-lg-9 col-md-12 col-sm-12">
							
							<div class="deals_wrap text-center"> 
								@if($ourCollection->heading)
									<h2 class="ft-bold text-light">{{ $ourCollection->heading }}</h2>
								@endif
								@if($ourCollection->description)
									<p class="text-light">{{ $ourCollection->description }}</p>
								@endif
								<div class="mt-5">
									@if($ourCollection->category)
										<a href="{{ route('frontend.shop') }}?category={{ $ourCollection->category->slug }}" class="btn btn-white stretched-links">Start Shopping <i class="lni lni-arrow-right"></i></a>
									@else
										<a href="{{ route('frontend.shop') }}" class="btn btn-white stretched-links">Start Shopping <i class="lni lni-arrow-right"></i></a>
									@endif
								</div>
							</div>
							
						</div>
					</div>
				</div>
			</section>
			@endif
<!-- our collection old normal section end here -->