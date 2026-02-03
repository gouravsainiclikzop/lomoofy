

			
			<!-- countdown our collection section start here -->
			@if($ourCollection && ($ourCollection->heading || $ourCollection->description))
			<section class="bg-cover" style="background:url({{ $ourCollection->background_image ? asset('storage/' . $ourCollection->background_image) : asset('frontend/images/bg-2.jpg') }}) no-repeat;"
			onclick="window.location.href='{{ route('frontend.shop') }}?category={{ $ourCollection->category->slug }}'" id="isourcollection-v1">
				<div class="container">
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12"> 
							<div class="deals_wrap text-center">
							@if($ourCollection->heading)
								<h4 class="ft-medium">{{ $ourCollection->heading }}</h4>
								@endif
								@if($ourCollection->description)
								<h2 class="ft-bold">{{ $ourCollection->description }}</h2>
								@endif
								<div id="countdown" class="mt-5">
										<ul>
												<li><span id="days">0</span> Days</li>
												<li><span id="hours">0</span> Hours</li>
												<li><span id="minutes">0</span> Minutes</li>
												<li><span id="seconds">0</span> Seconds</li>
										</ul>
								</div> 
							</div> 
						</div>
					</div>
				</div>
			</section> 
			@if($ourCollection->countdown_end_at)
			<script>
					window.collectionCountdownEnd = "{{ $ourCollection->countdown_end_at }}";
			</script>
			@endif

			@endif
			<!-- countdown our collection section ends here -->