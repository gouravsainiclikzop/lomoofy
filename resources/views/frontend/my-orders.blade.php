@extends('layouts.frontend')

@section('title', 'My Orders - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
						<li class="breadcrumb-item active" aria-current="page">My Order</li>
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
							<div class="d-block border rounded">
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
										<li><a href="{{ route('frontend.my-orders') }}" class="active"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
										<li><a href="{{ route('frontend.wishlist') }}"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
										<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
										<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
										<li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li>
										<li><a href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
									</ul>
								</div> 
							</div>
						</div>
						
						<div class="col-12 col-md-12 col-lg-8 col-xl-8 text-center">
						
							<!-- Single Order List -->
							<div class="ord_list_wrap border mb-4 mfliud">
								<div class="ord_list_head gray d-flex align-items-center justify-content-between px-3 py-3">
									<div class="olh_flex">
										<p class="m-0 p-0"><span class="text-muted">Order Number</span></p>
										<h6 class="mb-0 ft-medium">#1250004123</h6>
									</div>	
									<div class="olh_flex">
										<a href="javascript:void(0);" class="btn btn-sm btn-dark">Track Order</a>
									</div>	
								</div>
								<div class="ord_list_body text-left">
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4 br-bottom">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-start mfliud-bot">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/4.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Dresses</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Women Striped Shirt Dress</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-warning bg-light-warning rounded px-3 py-1">In Progress</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">22 September 2021</h6>
										</div>
									</div>
									
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4 br-bottom">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-startmfliud-bot">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/8.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Boys</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Boys Solid Sweatshirt</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-success bg-light-success rounded px-3 py-1">Completed</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">10 August 2021</h6>
										</div>
									</div>
									
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-start mfliud-bot">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/7.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Men's</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Printed Straight Kurta</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-danger bg-light-danger rounded px-3 py-1">On Hold</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">12 November 2021</h6>
										</div>
									</div>
									
								</div>
								<div class="ord_list_footer d-flex align-items-center justify-content-between br-top px-3  text-start">
									<div class="col-xl-3 col-lg-3 col-md-4 olf_flex text-left px-0 py-2 br-right"><a href="javascript:void(0);" class="ft-medium fs-sm"><i class="ti-close me-2"></i>Cancel Order</a></div>
									<div class="col-xl-9 col-lg-9 col-md-8 pe-0 ps-2 py-2 olf_flex d-flex align-items-center justify-content-between">
										<div class="olf_flex_inner hide_mob"><p class="m-0 p-0"><span class="text-muted medium">Paid using debit card ending with 6472</span></p></div>
										<div class="olf_inner_right"><h5 class="mb-0 fs-sm ft-bold">Total: $4510</h5></div>
									</div>
								</div>
							</div>
							<!-- End Order List -->
							
							<!-- Single Order List -->
							<div class="ord_list_wrap border mb-4">
								<div class="ord_list_head gray d-flex align-items-center justify-content-between px-3 py-3">
									<div class="olh_flex">
										<p class="m-0 p-0"><span class="text-muted">Order Number</span></p>
										<h6 class="mb-0 ft-medium">#1250004122</h6>
									</div>		
								</div>
								<div class="ord_list_body text-left">
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4 br-bottom">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-start mfliud-bot">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/2.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Dresses</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Women Striped Shirt Dress</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-warning bg-light-warning rounded px-3 py-1">Completed</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">22 February 2021</h6>
										</div>
									</div>
									
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4 br-bottom">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-start mfliud-bot">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/8.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Boys</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Boys Solid Sweatshirt</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-success bg-light-success rounded px-3 py-1">Completed</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">10 January 2021</h6>
										</div>
									</div>
									
									<!-- First Product -->
									<div class="row align-items-center justify-content-center m-0 py-4">
										<div class="col-xl-5 col-lg-5 col-md-5 col-12">
											<div class="cart_single d-flex align-items-start">
												<div class="cart_selected_single_thumb">
													<a href="#"><img src="{{ asset('frontend/images/product/1.jpg') }}" width="75" class="img-fluid rounded" alt=""></a>
												</div>
												<div class="cart_single_caption text-start ps-3">
													<p class="mb-0"><span class="text-muted small">Men's</span></p>
													<h4 class="product_title fs-sm ft-medium mb-1 lh-1">Printed Straight Kurta</h4>
													<p class="mb-2"><span class="text-dark medium">Size: 36</span>, <span class="text-dark medium">Color: Red</span></p>
													<h4 class="fs-sm ft-bold mb-0 lh-1">$129</h4>
												</div>
											</div>
										</div>
										<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
											<div class="delv_status"><span class="ft-medium small text-danger bg-light-danger rounded px-3 py-1">Canceled</span></div>
										</div>
										<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
											<p class="mb-1 p-0"><span class="text-muted">Expected date by:</span></p>
											<h6 class="mb-0 ft-medium fs-sm">12 November 2021</h6>
										</div>
									</div>
									
								</div>
								<div class="ord_list_footer d-flex align-items-center justify-content-between br-top px-3 row">
									<div class="col-xl-12 col-lg-12 col-md-12 ps-0 py-2 olf_flex d-flex align-items-center justify-content-between">
										<div class="olf_flex_inner"><p class="m-0 p-0"><span class="text-muted medium text-left">Paid using debit card ending with 6472</span></p></div>
										<div class="olf_inner_right"><h5 class="mb-0 fs-sm ft-bold">Total: $4510</h5></div>
									</div>
								</div>
							</div>
							<!-- End Order List -->
							
						</div>
						
					</div>
				</div>
			</section>
<!-- ======================= Dashboard Detail End ======================== -->
@endsection
