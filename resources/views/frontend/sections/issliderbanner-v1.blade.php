

<!-- ============================ Hero Banner  Start================================== -->
@if($homeSliders->count() > 0)
			<div class="home-slider margin-bottom-0" id="issliderbanner-v1">
				@foreach($homeSliders as $slider)
				<!-- Slide -->
				<div data-background-image="{{ $slider['image'] }}" class="item">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<div class="home-slider-container">

									<!-- Slide Title -->
									<div class="home-slider-desc">
										<div class="home-slider-title mb-4">
											@if($slider['category']) 
												<h5 class="theme-cl fs-sm ft-ragular mb-0">{{ $slider['category']['name'] }} Collection</h5>
											@endif
											@if($slider['title'])
												<h1 class="mb-1 ft-bold lg-heading">{!! $slider['title'] !!}</h1>
											@endif
											@if($slider['tagline'])
											<span class="trending">{{ $slider['tagline'] }}</span> 
											@endif
										</div>
										@if($slider['category'])
											<a href="{{ route('frontend.shop') }}?category={{ $slider['category']['slug'] }}" class="btn stretched-links borders">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
										@else
											<a href="{{ route('frontend.shop') }}" class="btn stretched-links borders">Shop Now<i class="lni lni-arrow-right ms-2"></i></a>
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
			<!-- ============================ Hero Banner End ================================== -->
