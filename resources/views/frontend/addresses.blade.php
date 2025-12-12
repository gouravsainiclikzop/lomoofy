@extends('layouts.frontend')

@section('title', 'Addresses - Lomoofy Industries')

@section('breadcrumbs')
			<div class="gray py-3">
				<div class="container">
					<div class="row">
						<div class="colxl-12 col-lg-12 col-md-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
									<li class="breadcrumb-item active" aria-current="page">Addresses</li>
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
							<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
							<li><a href="{{ route('frontend.addresses') }}" class="active"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
							<li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li>
							<li><a href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
									</ul>
								</div>
								
							</div>
						</div>
						
						<div class="col-12 col-md-12 col-lg-8 col-xl-8">
							<!-- row -->
							<div class="row align-items-start">
							
								<!-- Single -->
								<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
									<div class="card-wrap border rounded mb-4">
										<div class="card-wrap-header px-3 py-2 br-bottom d-flex align-items-center justify-content-between">
											<div class="card-header-flex">
												<h4 class="fs-md ft-bold mb-1">Shipping Address</h4>
												<p class="m-0 p-0"><span class="text-success bg-light-success small ft-medium px-2 py-1">Primary Account</span></p>
											</div>
											<div class="card-head-last-flex d-flex align-items-center gap-2">
												<!-- Button -->
									<a class="border p-3 circle text-dark d-inline-flex align-items-center justify-content-center" href="#"><i class="fas fa-pen-nib position-absolute"></i></a>
												 <!-- Button -->
												 <button class="border bg-white text-danger p-3 circle text-dark d-inline-flex align-items-center justify-content-center"><i class="fas fa-times position-absolute"></i></button>
											</div>
										</div>
										<div class="card-wrap-body px-3 py-3">
											<h5 class="ft-medium mb-1">Henry V. Derr</h5>
											<p>1354 Green Street Nashville<br>Drive Dodge City, KS 67801<br>United States</p>
											<p class="lh-1"><span class="text-dark ft-medium">Email:</span> dhananjaypreet@gmail.com</p>
											<p><span class="text-dark ft-medium">Call:</span> +91 458 753 6924</p>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
									<div class="card-wrap border rounded mb-4">
										<div class="card-wrap-header px-3 py-2 br-bottom d-flex align-items-center justify-content-between">
											<div class="card-header-flex">
												<h4 class="fs-md ft-bold mb-1">Shipping Address</h4>
											</div>
											<div class="card-head-last-flex d-flex align-items-center gap-2">
												<!-- Button -->
									<a class="border p-3 circle text-dark d-inline-flex align-items-center justify-content-center" href="#"><i class="fas fa-pen-nib position-absolute"></i></a>
												 <!-- Button -->
												 <button class="border bg-white text-danger p-3 circle text-dark d-inline-flex align-items-center justify-content-center"><i class="fas fa-times position-absolute"></i></button>
											</div>
										</div>
										<div class="card-wrap-body px-3 py-3">
											<h5 class="ft-medium mb-1">Willie A. Diebold</h5>
											<p>1354 Green Street Nashville<br>Drive Dodge City, KS 67801<br>United States</p>
											<p class="lh-1"><span class="text-dark ft-medium">Email:</span> dhananjaypreet@gmail.com</p>
											<p><span class="text-dark ft-medium">Call:</span> +91 458 753 6924</p>
										</div>
									</div>
								</div>
								
							</div>
							<!-- row -->
							
							<!-- row -->
							<div class="row align-items-start">
							
								<!-- Single -->
								<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
									<div class="card-wrap border rounded mb-4">
										<div class="card-wrap-header px-3 py-2 br-bottom d-flex align-items-center justify-content-between">
											<div class="card-header-flex">
												<h4 class="fs-md ft-bold mb-1">Billing Address</h4>
												<p class="m-0 p-0"><span class="text-warning bg-light-warning small ft-medium px-2 py-1">Primary Account</span></p>
											</div>
											<div class="card-head-last-flex d-flex align-items-center gap-2">
												<!-- Button -->
									<a class="border p-3 circle text-dark d-inline-flex align-items-center justify-content-center" href="#"><i class="fas fa-pen-nib position-absolute"></i></a>
												 <!-- Button -->
												 <button class="border bg-white text-danger p-3 circle text-dark d-inline-flex align-items-center justify-content-center"><i class="fas fa-times position-absolute"></i></button>
											</div>
										</div>
										<div class="card-wrap-body px-3 py-3">
											<h5 class="ft-medium mb-1">Henry V. Derr</h5>
											<p>1354 Green Street Nashville<br>Drive Dodge City, KS 67801<br>United States</p>
											<p class="lh-1"><span class="text-dark ft-medium">Email:</span> Johndoe@gmail.com</p>
											<p><span class="text-dark ft-medium">Call:</span> +91 9876543210</p>
										</div>
									</div>
								</div>
								
							</div>
							<!-- row -->
							
							<!-- row -->
							<div class="row align-items-start">
							
								<!-- Single -->
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
							<a href="#" class="btn stretched-links borders full-width"><i class="fas fa-plus me-2"></i>Add New Address</a>
									</div>
								</div>
								
							</div>
							<!-- row -->
							
						</div>
						
					</div>
				</div>
			</section>
			<!-- ======================= Dashboard Detail End ======================== -->
@endsection
