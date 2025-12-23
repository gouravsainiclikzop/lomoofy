@extends('layouts.frontend')

@section('title', 'Change Password - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
						<li class="breadcrumb-item active" aria-current="page">Change Password</li>
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
		
			@include('frontend.partials.customer-sidebar')
			
			<div class="col-12 col-md-12 col-lg-8 col-xl-8">
				<!-- Success/Error Messages -->
				@if(session('success'))
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						{{ session('success') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				@endif
				
				@if(session('error'))
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						{{ session('error') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				@endif
				
				@if($errors->any())
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<ul class="mb-0">
							@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				@endif
				
				<!-- Change Password Form -->
				<div class="row align-items-start">
					<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
						<div class="card-wrap border rounded mb-4">
							<div class="card-wrap-header px-3 py-2 br-bottom">
								<h4 class="fs-md ft-bold mb-0">Change Password</h4>
							</div>
							<div class="card-wrap-body px-3 py-3">
								<form id="changePasswordForm" class="row g-3" method="POST" action="{{ route('frontend.change-password.update') }}">
									@csrf
									
									@if($passwordFields && $passwordFields->count() > 0)
										@foreach($passwordFields as $field)
											@php
												$colClass = 'col-xl-12 col-lg-12 col-md-12 col-sm-12';
												$fieldValue = old($field->field_key, '');
											@endphp
											
											<div class="{{ $colClass }}">
												<div class="form-group">
													<label class="small text-dark ft-medium mb-2">
														{{ $field->label }}
														@if($field->is_required)
															<span class="text-danger">*</span>
														@endif
													</label>
													
													<input 
														type="{{ $field->input_type }}"
														class="form-control"
														id="{{ $field->field_key }}"
														name="{{ $field->field_key }}"
														value="{{ $fieldValue }}"
														placeholder="{{ $field->placeholder ?? '' }}"
														@if($field->is_required) required @endif
														autocomplete="{{ $field->field_key === 'old_password' ? 'current-password' : ($field->field_key === 'password' ? 'new-password' : 'new-password') }}"
													>
													
													@if($field->help_text)
														<small class="form-text text-muted">
															{{ $field->help_text }}
														</small>
													@endif
													
													@error($field->field_key)
														<div class="text-danger small">{{ $message }}</div>
													@enderror
												</div>
											</div>
										@endforeach
									@else
										<div class="col-12">
											<p class="text-muted">Password fields not configured. Please contact administrator.</p>
										</div>
									@endif
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
										<div class="form-group d-flex gap-2">
											<button type="submit" class="btn btn-dark">Change Password</button>
											<a href="{{ route('frontend.profile-info') }}" class="btn btn-secondary">Cancel</a>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				
			</div>
			
		</div>
	</div>
</section>
<!-- ======================= Dashboard Detail End ======================== -->
@endsection

