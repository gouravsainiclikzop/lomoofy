
 
		<!-- instagram section starts here -->
    <section class="p-0" id="isinstagram-v1">
				<div class="container-fluid p-0">
					
					<div class="row no-gutters">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Instagram Gallery</h2>
								<!-- <span class="fs-lg ft-bold theme-cl pt-3">@mahak_71</span> -->
								<h3 class="ft-bold lh-1">From Instagram</h3>
							</div>
						</div>
					</div>
					
					<div class="row no-gutters">
					@foreach($instagramGallery as $item)
						<div class="col">
							<div class="_insta_wrap">
								<div class="_insta_thumb">
									<a href="{{ $item['instagram_link'] }}" class="d-block">
										<img src="{{ $item['thumbnail_image'] ?? asset('frontend/images/placeholder.png') }}" class="img-fluid" alt=""></a>
								</div>
							</div>
						</div>
					@endforeach 	
						
					</div>
					
				</div>
			</section>