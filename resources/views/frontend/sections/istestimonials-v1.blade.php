

	<!-- testimonials section starts here -->
			<!-- ======================= Customer Review ======================== -->
			<section class="gray" id="istestimonials-v1">
				<div class="container"> 
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Testimonials</h2>
								<h3 class="ft-bold pt-3">Testimonials</h3>
							</div>
						</div>
					</div>
					
					<div class="row justify-content-center">
						<div class="col-xl-9 col-lg-10 col-md-12 col-sm-12">
							<div class="reviews-slide px-3">
								@if($testimonials->count() > 0)
									@foreach($testimonials as $testimonial)
									<div class="single_review">
										<div class="sng_rev_thumb">
											<figure>
												<img src="{{ $testimonial['image'] }}" class="img-fluid circle" alt="{{ $testimonial['name'] }}">
											</figure>
										</div>
										<div class="sng_rev_caption text-center">
											<div class="rev_desc mb-4">
												<p class="fs-md">{{ $testimonial['description'] }}</p>
											</div>
											<div class="rev_author">
												<h4 class="mb-0">{{ $testimonial['name'] }}</h4>
												@if($testimonial['title'])
													<span class="fs-sm">{{ $testimonial['title'] }}</span>
												@endif
											</div>
										</div>
									</div>
									@endforeach
								@else
									<div class="col-xl-12">
										<p class="text-center text-muted">No testimonials available.</p>
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Customer Review ======================== --> 
<!-- testimonials section ends here -->