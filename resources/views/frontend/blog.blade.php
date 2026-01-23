@extends('layouts.frontend')

@section('title', 'Blogs - Lomoofy Industries')

@section('breadcrumbs')
<!-- ======================= Top Breadcrubms ======================== -->
 
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Blogs Listing ======================== -->
<section class="space min">
	<div class="container"> 
		<div class="row justify-content-center">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				<div class="sec_title position-relative text-center">
					<h2 class="off_title">Latest News</h2>
					<h3 class="ft-bold pt-3">Our Blog</h3>
				</div>
			</div>
		</div>
		
		@if($blogs && $blogs->count() > 0)
		<div class="row">
			@foreach($blogs as $blog)
			<div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
				<div class="_blog_wrap">
					<div class="_blog_thumb mb-2">
						<a href="{{ route('frontend.blog-detail', $blog->slug) }}" class="d-block">
							<img src="{{ $blog->thumbnail_url }}" class="img-fluid rounded" alt="{{ $blog->title }}" style="width: 100%; height: 250px; object-fit: cover;">
						</a>
					</div>
					<div class="_blog_caption">
						<span class="text-muted">{{ $blog->published_date ? $blog->published_date->format('d M Y') : '' }}</span>
						@if($blog->added_by)
						<span class="text-muted ms-2">| By {{ $blog->added_by }}</span>
						@endif
						<h5 class="bl_title lh-1 mt-2">
							<a href="{{ route('frontend.blog-detail', $blog->slug) }}">{{ $blog->title }}</a>
						</h5>
						<p>{{ Str::limit(strip_tags($blog->description), 150) }}</p>
						<a href="{{ route('frontend.blog-detail', $blog->slug) }}" class="text-dark fs-sm">Continue Reading..</a>
					</div>
				</div>
			</div>
			@endforeach
		</div>
		
		<!-- Pagination -->
		@if($blogs->hasPages())
		<div class="row">
			<div class="col-12">
				<div class="d-flex justify-content-center">
					{{ $blogs->links() }}
				</div>
			</div>
		</div>
		@endif
		@else
		<div class="row">
			<div class="col-12">
				<div class="alert alert-info text-center">
					<p class="mb-0">No blog posts available at the moment. Check back soon!</p>
				</div>
			</div>
		</div>
		@endif
		
	</div>
</section>
<!-- ======================= Blog Listing End ============================ -->
  
@endsection
