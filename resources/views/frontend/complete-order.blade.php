@extends('layouts.frontend')

@section('title', 'Order Complete - Lomoofy Industries')

@section('breadcrumbs')
			<div class="gray py-3">
				<div class="container">
					<div class="row">
						<div class="colxl-12 col-lg-12 col-md-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Support</a></li>
									<li class="breadcrumb-item active" aria-current="page">Complete Order</li>
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
				
					<div class="row justify-content-center">
						<div class="col-12 col-md-10 col-lg-8 col-xl-5 text-center">

							<!-- Icon -->
							<div class="p-4 d-inline-flex align-items-center justify-content-center circle bg-light-success text-success mx-auto mb-4"><i class="lni lni-heart-filled fs-lg"></i></div>
							<!-- Heading -->
							<h2 class="mb-2 ft-bold">Your Order is Completed!</h2>
							<!-- Text -->
							<p class="ft-regular fs-md mb-5">Your order <span class="text-body text-dark">#965471202</span> has been completed. Your order details are shown for your personal accont.</p>
							<!-- Button -->
				<a class="btn btn-dark" href="{{ route('frontend.my-orders') }}">Track Your Orders</a>
						</div>
					</div>
					
				</div>
			</section>
			<!-- ======================= Product Detail End ======================== -->
@endsection
