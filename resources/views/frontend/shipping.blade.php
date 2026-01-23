@extends('layouts.frontend')

@section('title', 'Shipping Policy - Lomoofy Industries')

@section('breadcrumbs')
<!-- ======================= Top Breadcrubms ======================== -->
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Pages</a></li>
						<li class="breadcrumb-item active" aria-current="page">Shipping Policy</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Shipping Detail ======================== -->
<section class="middle">
	<div class="container">
		<div class="row align-items-center justify-content-between">
		
			<div class="col-xl-11 col-lg-12 col-md-12 col-sm-12">
				<div class="abt_caption">
					<h2 class="ft-medium mb-4">Shipping Policy</h2>
					@if($legalPages && $legalPages->shipping_status && $legalPages->shipping)
						<div class="legal-content">
							{!! nl2br(e($legalPages->shipping)) !!}
						</div>
					@else
						<div class="alert alert-info">
							<i class="fas fa-info-circle"></i> Shipping policy content is not available at the moment.
						</div>
					@endif
				</div>
			</div>
			
		</div>
	</div>
</section>
<!-- ======================= Shipping End ======================== -->
@endsection
