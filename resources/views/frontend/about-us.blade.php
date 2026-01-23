@extends('layouts.frontend')

@section('title', 'About Us - Lomoofy Industries')

@section('breadcrumbs')
<!-- ======================= Top Breadcrubms ======================== -->
 
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= About Us Detail ======================== -->
<section class="middle">
	<div class="container">
		@if($aboutUs && $aboutUs->description)
		<div class="row align-items-center justify-content-between">
		
			<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
				<div class="abt_caption">
					{!! $aboutUs->description !!}
	 
					<div class="form-group mt-4">
						<a href="{{ route('frontend.shop') }}" class="btn btn-dark">Shop Now</a>
					</div>
				</div>
			</div>
			
			<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
				<div class="abt_caption">
					@if($aboutUs->image)
						<img src="{{ asset('storage/' . $aboutUs->image) }}" class="img-fluid rounded" alt="About Us" />
					@else
						<img src="{{ asset('frontend/images/about-1.png') }}" class="img-fluid rounded" alt="About Us" />
					@endif
				</div>
			</div>
			
		</div>
		@else
		<!-- Default content when no data is available -->
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-8 col-lg-10 col-md-12 text-center">
				<div class="abt_caption">
					<h4 class="ft-medium mb-3">About Us</h4>
					<p class="mb-4">Welcome to Lomoofy Industries. Please check back soon for more information about our company.</p>
					<div class="form-group mt-4">
						<a href="{{ route('frontend.shop') }}" class="btn btn-dark">Shop Now</a>
					</div>
				</div>
			</div>
		</div>
		@endif
	</div>
</section>
<!-- ======================= About Us End ======================== -->
  
@endsection
