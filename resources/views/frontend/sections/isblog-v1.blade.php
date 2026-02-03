
<!-- blog section starts here -->
			<!-- ======================= Blog Start ============================ -->
			@if($latestBlogs && $latestBlogs->count() > 0)
			<section class="space min" id="isblog-v1">
				<div class="container"> 
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Latest News</h2>
								<h3 class="ft-bold pt-3">Latest Updates</h3>
							</div>
						</div>
					</div>
					
					<div class="row">
						@foreach($latestBlogs as $blog)
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
							<div class="_blog_wrap">
								<div class="_blog_thumb mb-2">
									<a href="{{ route('frontend.blog-detail', $blog->slug) }}" class="d-block">
										<img src="{{ $blog->thumbnail_url }}" class="img-fluid rounded" alt="{{ $blog->title }}" style="width: 100%; height: 250px; object-fit: cover;">
									</a>
								</div>
								<div class="_blog_caption">
									<span class="text-muted">{{ $blog->published_date ? $blog->published_date->format('d M Y') : '' }}</span>
									<h5 class="bl_title lh-1">
										<a href="{{ route('frontend.blog-detail', $blog->slug) }}">{{ $blog->title }}</a>
									</h5>
									<p>{{ Str::limit(strip_tags($blog->description), 150) }}</p>
									<a href="{{ route('frontend.blog-detail', $blog->slug) }}" class="text-dark fs-sm">Continue Reading..</a>
								</div>
							</div>
						</div>
						@endforeach
					</div>
					
				</div>
			</section>
			@endif
			<!-- ======================= Blog end ============================ -->
			<!-- blog section ends here -->