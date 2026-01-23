@extends('layouts.frontend')

@section('title', $blog->title . ' - Lomoofy Industries')

@section('breadcrumbs')
<!-- ======================= Top Breadcrubms ======================== -->
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="{{ route('frontend.blog') }}">Blog</a></li>
						<li class="breadcrumb-item active" aria-current="page">{{ $blog->title }}</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Blog Detail ======================== -->
<section class="py-5">
	<div class="container">
		<!-- row Start -->
		<div class="row">
			<!-- Blog Detail -->
			<div class="col-lg-8 col-md-12 col-sm-12 col-12">
				<div class="article_detail_wrapss single_article_wrap format-standard">
					<div class="article_body_wrap">
						
						<!-- Featured Image -->
						<div class="article_featured_image mb-4">
							<img class="img-fluid rounded" src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" style="width: 100%; max-height: 500px; object-fit: cover;">
						</div>
						
						<!-- Article Info -->
						<div class="article_top_info mb-3">
							<ul class="article_middle_info d-flex flex-wrap">
								@if($blog->added_by)
								<li class="me-3"><a href="#"><span class="icons"><i class="ti-user"></i></span>by {{ $blog->added_by }}</a></li>
								@endif
								@if($blog->published_date)
								<li class="me-3"><a href="#"><span class="icons"><i class="ti-calendar"></i></span>{{ $blog->published_date->format('d M Y') }}</a></li>
								@endif
							</ul>
						</div>
						
						<!-- Article Title -->
						<h2 class="post-title mb-4">{{ $blog->title }}</h2>
						
						<!-- Article Content -->
						<div class="article-content">
							{!! $blog->description !!}
						</div>
						
						<!-- Share Buttons -->
						<div class="article_share_box mt-5 pt-4 border-top">
							<h6 class="mb-3">Share this article:</h6>
							<div class="d-flex gap-2">
								<a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('frontend.blog-detail', $blog->slug)) }}" target="_blank" class="btn btn-sm btn-primary">
									<i class="fab fa-facebook-f"></i> Facebook
								</a>
								<a href="https://twitter.com/intent/tweet?url={{ urlencode(route('frontend.blog-detail', $blog->slug)) }}&text={{ urlencode($blog->title) }}" target="_blank" class="btn btn-sm btn-info text-white">
									<i class="fab fa-twitter"></i> Twitter
								</a>
								<a href="https://api.whatsapp.com/send?text={{ urlencode($blog->title . ' ' . route('frontend.blog-detail', $blog->slug)) }}" target="_blank" class="btn btn-sm btn-success">
									<i class="fab fa-whatsapp"></i> WhatsApp
								</a>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			
			<!-- Sidebar -->
			<div class="col-lg-4 col-md-12 col-sm-12 col-12">
				
				<!-- Search -->
				<div class="single_widgets widget_search mb-4">
					<h4 class="title">Search</h4>
					<form action="{{ route('frontend.blog') }}" method="GET" class="sidebar-search-form">
						<input type="search" name="search" placeholder="Search blogs...">
						<button type="submit"><i class="ti-search"></i></button>
					</form>
				</div>
				
				<!-- Related/Latest Posts -->
				@if($relatedBlogs && $relatedBlogs->count() > 0)
				<div class="single_widgets widget_thumb_post">
					<h4 class="title">Related Posts</h4>
					<ul>
						@foreach($relatedBlogs as $relatedBlog)
						<li>
							<span class="left">
								<img src="{{ $relatedBlog->thumbnail_url }}" alt="{{ $relatedBlog->title }}" style="width: 80px; height: 60px; object-fit: cover;">
							</span>
							<span class="right">
								<a class="feed-title" href="{{ route('frontend.blog-detail', $relatedBlog->slug) }}">{{ Str::limit($relatedBlog->title, 50) }}</a> 
								<span class="post-date"><i class="ti-calendar"></i>{{ $relatedBlog->published_date ? $relatedBlog->published_date->diffForHumans() : '' }}</span>
							</span>
						</li>
						@endforeach
					</ul>
				</div>
				@endif
				
				<!-- Back to Blog List -->
				<div class="single_widgets mt-4">
					<a href="{{ route('frontend.blog') }}" class="btn btn-dark btn-block w-100">
						<i class="ti-arrow-left me-2"></i> Back to All Blogs
					</a>
				</div>
				
			</div>
			
		</div>
		<!-- /row -->					
		
	</div>
</section>
<!-- ======================= Blog Detail End ======================== -->

@endsection

@push('styles')
<style>
.article-content {
	font-size: 16px;
	line-height: 1.8;
	color: #333;
}
.article-content img {
	max-width: 100%;
	height: auto;
	margin: 20px 0;
	border-radius: 8px;
}
.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
	margin-top: 25px;
	margin-bottom: 15px;
	font-weight: 600;
}
.article-content p {
	margin-bottom: 15px;
}
.article-content ul,
.article-content ol {
	margin-bottom: 20px;
	padding-left: 30px;
}
.article-content blockquote {
	border-left: 4px solid #007bff;
	padding-left: 20px;
	margin: 20px 0;
	font-style: italic;
	color: #666;
}
.article-content a {
	color: #007bff;
	text-decoration: underline;
}
.article-content a:hover {
	color: #0056b3;
}
.article-content table {
	width: 100%;
	margin: 20px 0;
	border-collapse: collapse;
}
.article-content table th,
.article-content table td {
	border: 1px solid #ddd;
	padding: 12px;
	text-align: left;
}
.article-content table th {
	background-color: #f8f9fa;
	font-weight: 600;
}
</style>
@endpush
