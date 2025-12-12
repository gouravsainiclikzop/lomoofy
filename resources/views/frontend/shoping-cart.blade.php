@extends('layouts.frontend')

@section('title', 'Shopping Cart - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Support</a></li>
						<li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Product Detail ======================== -->
<section class="middle">
	<div class="container">
	
		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				<div class="text-center d-block mb-5">
					<h2>Shopping Cart</h2>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-between">
			<div class="col-12 col-lg-7 col-md-12">
				<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">
					
					<li class="list-group-item">
						<div class="row align-items-center">
							<div class="col-3">
								<!-- Image -->
								<a href="{{ route('frontend.product') }}"><img src="{{ asset('frontend/images/product/7.jpg') }}" alt="..." class="img-fluid"></a>
							</div>
							<div class="col d-flex align-items-center justify-content-between">
								<div class="cart_single_caption ps-2">
									<h4 class="product_title fs-md ft-medium mb-1 lh-1">Women Striped Shirt Dress</h4>
									<p class="mb-1 lh-1"><span class="text-dark">Size: 40</span></p>
									<p class="mb-3 lh-1"><span class="text-dark">Color: Blue</span></p>
									<h4 class="fs-md ft-medium mb-3 lh-1">$129</h4>
									<select class="mb-2 custom-select w-auto">
									  <option value="1" selected="">1</option>
									  <option value="2">2</option>
									  <option value="3">3</option>
									  <option value="4">4</option>
									  <option value="5">5</option>
									</select>
								</div>
								<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
							</div>
						</div>
					</li>
					
					<li class="list-group-item">
						<div class="row align-items-center">
							<div class="col-3">
								<!-- Image -->
								<a href="{{ route('frontend.product') }}"><img src="{{ asset('frontend/images/product/7.jpg') }}" alt="..." class="img-fluid"></a>
							</div>
							<div class="col d-flex align-items-center justify-content-between">
								<div class="cart_single_caption ps-2">
									<h4 class="product_title fs-md ft-medium mb-1 lh-1">Girls Solid A-Line Dress</h4>
									<p class="mb-1 lh-1"><span class="text-dark">Size: 36</span></p>
									<p class="mb-3 lh-1"><span class="text-dark">Color: Red</span></p>
									<h4 class="fs-md ft-medium mb-3 lh-1">$129</h4>
									<select class="mb-2 custom-select w-auto">
									  <option value="1" selected="">1</option>
									  <option value="2">2</option>
									  <option value="3">3</option>
									  <option value="4">4</option>
									  <option value="5">5</option>
									</select>
								</div>
								<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
							</div>
						</div>
					</li>
					
				</ul>
				
				<div class="row align-items-end justify-content-between mb-10 mb-md-0">
					<div class="col-12 col-md-7">
						<!-- Coupon -->
						<form class="mb-7 mb-md-0">
							<label class="fs-sm ft-medium text-dark">Coupon code:</label>
							<div class="row form-row">
								<div class="col">
								  <input class="form-control" type="text" placeholder="Enter coupon code*">
								</div>
								<div class="col-auto">
									<button class="btn btn-dark" type="submit">Apply</button>
								</div>
							</div>
						</form>
					</div>
					<div class="col-12 col-md-auto mfliud">
						<button class="btn stretched-links borders">Update Cart</button>
					</div>
				</div>
			</div>
			
			<div class="col-12 col-md-12 col-lg-4">
				<div class="card mb-4 gray mfliud">
				  <div class="card-body">
					<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x">
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Subtotal</span> <span class="ms-auto text-dark ft-medium">$98.12</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Tax</span> <span class="ms-auto text-dark ft-medium">$10.10</span>
					  </li>
					  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
						<span>Total</span> <span class="ms-auto text-dark ft-medium">$108.22</span>
					  </li>
					  <li class="list-group-item fs-sm text-center">
						Shipping cost calculated at Checkout *
					  </li>
					</ul>
				  </div>
				</div>
				
				<a class="btn btn-block btn-dark w-100 mb-3" href="{{ route('frontend.checkout') }}">Proceed to Checkout</a>
				
				<a class="btn-link text-dark ft-medium" href="{{ route('frontend.shop') }}">
				  <i class="ti-back-left me-2"></i> Continue Shopping
				</a>
			</div>
			
		</div>
		
	</div>
</section>
<!-- ======================= Product Detail End ======================== -->
@endsection
