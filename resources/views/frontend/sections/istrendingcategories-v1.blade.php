

			<!-- trending categories section starts -->
			<section class="middle" id="istrendingcategories-v1">
				<div class="container"> 

					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Popular Categories</h2>
								<h3 class="ft-bold pt-3">Trending Categories</h3>
							</div>
						</div>
					</div>
					
					<div class="row align-items-center justify-content-center">
						@if($trendingCategories->count() > 0)
							@foreach($trendingCategories as $category)
								<div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-4">
									<div class="cats_side_wrap text-center mx-auto mb-3">
										<div class="sl_cat_01">
											<div class="d-inline-flex align-items-center justify-content-center p-4 circle mb-2 border">
												<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}" class="d-block">
													@if($category->image)
														<img src="{{ asset('storage/' . $category->image) }}" class="img-fluid" width="40" alt="{{ $category->name }}">
													@else
														<img src="{{ asset('assets/images/placeholder.jpg') }}" class="img-fluid" width="40" alt="{{ $category->name }}">
													@endif
												</a>
											</div>
										</div>
										<div class="sl_cat_02">
											<h6 class="m-0 ft-medium fs-sm">
												<a href="{{ route('frontend.shop') }}?category={{ $category->slug }}">{{ $category->name }}</a>
											</h6>
										</div>
									</div>
								</div>
							@endforeach
						@else
							<div class="col-12">
								<p class="text-center text-muted">No trending categories available.</p>
							</div>
						@endif
					</div> 
				</div>
			</section>
			<!-- trending categories section ends --> 