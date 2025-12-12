@extends('layouts.frontend')

@section('title', 'Profile Info - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
						<li class="breadcrumb-item active" aria-current="page">Profile Info</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Dashboard Detail ======================== -->
<section class="middle">
	<div class="container">
		<div class="row align-items-start justify-content-between">
		
			<div class="col-12 col-md-12 col-lg-4 col-xl-4 text-center miliods">
				<div class="d-block border rounded mfliud-bot">
					<div class="dashboard_author px-2 py-5">
						<div class="dash_auth_thumb circle p-1 border d-inline-flex mx-auto mb-2">
							<img src="{{ asset('frontend/images/team-1.jpg') }}" class="img-fluid circle" width="100" alt="" />
						</div>
						<div class="dash_caption">
							<h4 class="fs-md ft-medium mb-0 lh-1">Adam Wishnoi</h4>
							<span class="text-muted smalls">Australia</span>
						</div>
					</div>
					
					<div class="dashboard_author">
						<h4 class="px-3 py-2 mb-0 lh-2 gray fs-sm ft-medium text-muted text-uppercase text-left">Dashboard Navigation</h4>
						<ul class="dahs_navbar">
							<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
							<li><a href="{{ route('frontend.wishlist') }}"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
							<li><a href="{{ route('frontend.profile-info') }}" class="active"><i class="lni lni-user me-2"></i>Profile Info</a></li>
							<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
							<li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li>
							<li><a href="login.html"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
						</ul>
					</div>
					
				</div>
			</div>
			
			<div class="col-12 col-md-12 col-lg-8 col-xl-8">
				<!-- row -->
				<div class="row align-items-center">
					<form class="row g-3 m-0">
						
						<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">First Name *</label>
								<input type="text" class="form-control" value="John" />
							</div>
						</div>
						
						<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">Last Name *</label>
								  <input type="text" class="form-control" value="Doe" />
							</div>
						</div>
						
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">Email ID *</label>
								<input type="text" class="form-control" value="Johndoe@gmail.com" />
							</div>
						</div>
						
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">About Us *</label>
								<textarea class="form-control ht-80">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias</textarea>
							</div>
						</div>
						
						<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">Current Password *</label>
								<input type="text" class="form-control" value="Current Password" />
							</div>
						</div>
						
						<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
							<div class="form-group">
								<label class="small text-dark ft-medium mb-2">New Password *</label>
								<input type="text" class="form-control" value="New Password" />
							</div>
						</div>
						
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="form-group">
								<button type="button" class="btn btn-dark">Save Changes</button>
							</div>
						</div>
						
					</form>
				</div>
				<!-- row -->
			</div>
			
		</div>
	</div>
</section>
<!-- ======================= Dashboard Detail End ======================== -->
@endsection
