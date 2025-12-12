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
		<div class="row align-items-center justify-content-between">
		
			<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
				<div class="abt_caption">
					<h4 class="ft-medium ">Lorem ipsum dolor sit amet,    </h4>
					<p class="mb-4">Lorem ipsum dolo mmodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
					<h4 class="ft-medium ">Lorem ipsum dolor sit amet, consectetur ?</h4>
					<p class="mb-4">Lorem ipsu  incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
					<h4 class="ft-medium ">Lorem ipsum  consectetur ?</h4>
					<p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip   nulla pariatur.</p>
					<h4 class="ft-medium ">Lorem  it amet, consectetur ?</h4>
					<p class="mb-4">Lorem ipsum dolor sit amet,  uis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
						<h4 class="ft-medium ">Lorem ipsum dolor sit amet, consectetur ?</h4>
					<p class="mb-4">Lorem ipsum   nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
	 
					<div class="form-group mt-4">
						<a href="{{ route('frontend.shop') }}" class="btn btn-dark">See More Info</a>
					</div>
				</div>
			</div>
			
			<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
				<div class="abt_caption">
					<img src="{{ asset('frontend/images/about-1.png') }}" class="img-fluid rounded" alt="" />
				</div>
			</div>
			
		</div>
	</div>
</section>
<!-- ======================= About Us End ======================== -->
  
@endsection
