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
							<div class="card-wrap-header px-3 py-2 br-bottom d-flex justify-content-between align-items-center">
								<h4 class="fs-md ft-bold mb-0">Change Password</h4>
								<a href="#" id="showForgotPasswordSection" class="text-primary small">Forgot Password?</a>
							</div>
							<div class="card-wrap-body px-3 py-3">
								<!-- Regular Change Password Form -->
								<div id="changePasswordSection">
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

								<!-- Forgot Password Section (2-Step Process) -->
								<div id="forgotPasswordSection" style="display: none;">
									<!-- Back Link -->
									<div class="mb-3">
										<a href="#" id="backToChangePassword" class="text-primary small">
											<i class="fas fa-arrow-left"></i> Back to Change Password
										</a>
									</div>

									<!-- Error/Success Messages -->
									<div id="fpMessage" style="display: none;"></div>

									<!-- Step 1: Send OTP (No Email Input - Uses Logged-in Email) -->
									<div id="fpEmailStep">
										@if(Auth::guard('customer')->check())
										<div class="alert alert-info py-2 mb-3">
											<i class="fas fa-info-circle"></i> <small>We'll send a verification code to your registered email: <strong>{{ Auth::guard('customer')->user()->email }}</strong></small>
										</div>
										
										<div class="form-group">
											<button type="button" id="fpSendOtpBtn" class="btn btn-dark">
												<span id="fpSendOtpBtnText">Send Verification Code</span>
												<span id="fpSendOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
											</button>
										</div>
										@else
										<div class="alert alert-danger py-2 mb-3">
											<i class="fas fa-exclamation-circle"></i> <small>Please login to reset your password.</small>
										</div>
										@endif
									</div>

									<!-- Step 2: OTP Verification -->
									<div id="fpOtpStep" style="display: none;">
										<div class="alert alert-info py-2 mb-3">
											<i class="fas fa-envelope"></i> <small>We've sent a verification code to <strong id="fpEmailDisplay"></strong></small>
										</div>
										<form id="fpOtpForm">
											<div class="form-group mb-3">
												<label for="fpOtp" class="small text-dark ft-medium mb-2">Verification Code <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="fpOtp" name="otp" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required>
												<div class="invalid-feedback" id="fpOtpError"></div>
												<div class="valid-feedback" id="fpOtpSuccess"></div>
												<small class="text-muted d-block mt-1">Didn't receive? <a href="#" id="fpResendOtp" class="text-primary fw-bold">Resend Code</a></small>
											</div>
											
											<div class="form-group">
												<button type="submit" id="fpVerifyOtpBtn" class="btn btn-dark">
													<span id="fpVerifyOtpBtnText">Verify Code</span>
													<span id="fpVerifyOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
												</button>
											</div>
										</form>
									</div>

									<!-- Step 3: Reset Password -->
									<div id="fpResetStep" style="display: none;">
										<div class="alert alert-success py-2 mb-3">
											<i class="fas fa-check-circle"></i> <small>Email verified! Set your new password</small>
										</div>
										<form id="fpResetForm">
											<div class="form-group mb-3">
												<label for="fpNewPassword" class="small text-dark ft-medium mb-2">New Password <span class="text-danger">*</span></label>
												<div class="position-relative">
													<input type="password" class="form-control pe-5" id="fpNewPassword" name="password" placeholder="Enter new password" required minlength="8">
													<button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted fp-password-toggle" data-target="#fpNewPassword">
														<i class="fas fa-eye"></i>
													</button>
												</div>
												<div class="invalid-feedback"></div>
												<small class="text-muted">Minimum 8 characters</small>
											</div>
											
											<div class="form-group mb-3">
												<label for="fpConfirmPassword" class="small text-dark ft-medium mb-2">Confirm Password <span class="text-danger">*</span></label>
												<div class="position-relative">
													<input type="password" class="form-control pe-5" id="fpConfirmPassword" name="password_confirmation" placeholder="Confirm new password" required minlength="8">
													<button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted fp-password-toggle" data-target="#fpConfirmPassword">
														<i class="fas fa-eye"></i>
													</button>
												</div>
												<div class="invalid-feedback"></div>
											</div>
											
											<div class="form-group">
												<button type="submit" id="fpResetBtn" class="btn btn-dark">
													<span id="fpResetBtnText">Reset Password</span>
													<span id="fpResetBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
												</button>
											</div>
										</form>
									</div>
								</div>
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

@push('scripts')
<script>
$(document).ready(function() {
	let fpEmail = '';
	let fpOtpVerified = false;
	let fpResendTimer = null;
	let fpResendSeconds = 0;

	// Show/Hide Message Functions
	function showFpSuccess(message) {
		$('#fpMessage').html('<div class="alert alert-success alert-dismissible fade show py-2" role="alert">' +
			'<i class="fas fa-check-circle"></i> ' + message +
			'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
			'</div>').show();
	}

	function showFpError(message) {
		$('#fpMessage').html('<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">' +
			'<i class="fas fa-exclamation-circle"></i> ' + message +
			'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
			'</div>').show();
	}

	// Toggle between Change Password and Forgot Password sections
	$('#showForgotPasswordSection').on('click', function(e) {
		e.preventDefault();
		$('#changePasswordSection').hide();
		$('#forgotPasswordSection').show();
		// Reset forgot password form
		resetForgotPasswordForm();
	});

	$('#backToChangePassword').on('click', function(e) {
		e.preventDefault();
		$('#forgotPasswordSection').hide();
		$('#changePasswordSection').show();
		// Reset forgot password form
		resetForgotPasswordForm();
	});

	// Reset forgot password form
	function resetForgotPasswordForm() {
		// Reset all steps
		$('#fpEmailStep').show();
		$('#fpOtpStep').hide();
		$('#fpResetStep').hide();
		
		// Clear all inputs (OTP and password forms only)
		$('#fpOtpForm')[0].reset();
		$('#fpResetForm')[0].reset();
		
		// Clear validation states
		$('.form-control').removeClass('is-invalid is-valid');
		$('.invalid-feedback').text('');
		$('#fpMessage').hide().html('');
		
		// Reset variables
		@if(Auth::guard('customer')->check())
		fpEmail = '{{ Auth::guard('customer')->user()->email }}';
		@else
		fpEmail = '';
		@endif
		fpOtpVerified = false;
		
		// Clear timer
		if (fpResendTimer) {
			clearInterval(fpResendTimer);
		}
	}

	// Step 1: Send OTP to logged-in customer's email
	$('#fpSendOtpBtn').on('click', function(e) {
		e.preventDefault();
		
		// Use logged-in customer's email
		@if(Auth::guard('customer')->check())
		const email = '{{ Auth::guard('customer')->user()->email }}';
		@else
		showFpError('Please login to reset your password.');
		return false;
		@endif
		
		// Show loading
		$('#fpSendOtpBtnText').text('Sending...');
		$('#fpSendOtpBtnSpinner').removeClass('d-none');
		$('#fpSendOtpBtn').prop('disabled', true);
		$('#fpMessage').hide().html('');
		
		$.ajax({
			url: '/api/auth/forgot-password/send-otp',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			data: { email: email },
			success: function(response) {
				if (response.success) {
					fpEmail = email;
					$('#fpEmailDisplay').text(email);
					
					// Show OTP step
					$('#fpEmailStep').hide();
					$('#fpOtpStep').show();
					
					showFpSuccess('Verification code sent to your email!');
					
					// Start resend timer
					startFpResendTimer(60);
				}
			},
			error: function(xhr) {
				console.error('Send OTP Error:', xhr);
				let errorMessage = 'Failed to send verification code. Please try again.';
				
				if (xhr.responseJSON && xhr.responseJSON.error) {
					errorMessage = xhr.responseJSON.error.message || errorMessage;
				}
				
				showFpError(errorMessage);
			},
			complete: function() {
				$('#fpSendOtpBtnText').text('Send Verification Code');
				$('#fpSendOtpBtnSpinner').addClass('d-none');
				$('#fpSendOtpBtn').prop('disabled', false);
			}
		});
	});

	// Start resend countdown timer
	function startFpResendTimer(seconds) {
		fpResendSeconds = seconds;
		$('#fpResendOtp').addClass('disabled pe-none text-muted').removeClass('text-primary');
		
		if (fpResendTimer) {
			clearInterval(fpResendTimer);
		}
		
		fpResendTimer = setInterval(function() {
			fpResendSeconds--;
			$('#fpResendOtp').text('Resend Code (' + fpResendSeconds + 's)');
			
			if (fpResendSeconds <= 0) {
				clearInterval(fpResendTimer);
				$('#fpResendOtp').text('Resend Code').removeClass('disabled pe-none text-muted').addClass('text-primary');
			}
		}, 1000);
	}

	// Resend OTP
	$(document).on('click', '#fpResendOtp', function(e) {
		e.preventDefault();
		
		if ($(this).hasClass('disabled')) {
			return false;
		}
		
		// Use logged-in customer's email
		@if(Auth::guard('customer')->check())
		const email = '{{ Auth::guard('customer')->user()->email }}';
		@else
		showFpError('Please login to reset your password.');
		return false;
		@endif
		
		$.ajax({
			url: '/api/auth/forgot-password/send-otp',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			data: { email: email },
			success: function(response) {
				if (response.success) {
					showFpSuccess('New verification code sent!');
					startFpResendTimer(60);
				}
			},
			error: function(xhr) {
				showFpError('Failed to resend code. Please try again.');
			}
		});
	});

	// Step 2: Verify OTP
	$('#fpOtpForm').on('submit', function(e) {
		e.preventDefault();
		
		const otp = $('#fpOtp').val().trim();
		
		if (!otp || otp.length !== 6) {
			$('#fpOtp').addClass('is-invalid');
			$('#fpOtpError').text('Please enter a valid 6-digit code');
			return false;
		}
		
		// Show loading
		$('#fpVerifyOtpBtnText').text('Verifying...');
		$('#fpVerifyOtpBtnSpinner').removeClass('d-none');
		$('#fpVerifyOtpBtn').prop('disabled', true);
		$('#fpMessage').hide().html('');
		
		$.ajax({
			url: '/api/auth/forgot-password/verify-otp',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			data: {
				email: fpEmail,
				otp: otp
			},
			success: function(response) {
				if (response.success) {
					fpOtpVerified = true;
					
					// Show reset password step
					$('#fpOtpStep').hide();
					$('#fpResetStep').show();
					$('#fpMessage').hide().html('');
				}
			},
			error: function(xhr) {
				console.error('Verify OTP Error:', xhr);
				let errorMessage = 'Invalid or expired verification code';
				
				if (xhr.responseJSON && xhr.responseJSON.error) {
					errorMessage = xhr.responseJSON.error.message || errorMessage;
				}
				
				$('#fpOtp').addClass('is-invalid');
				$('#fpOtpError').text(errorMessage);
				showFpError(errorMessage);
			},
			complete: function() {
				$('#fpVerifyOtpBtnText').text('Verify Code');
				$('#fpVerifyOtpBtnSpinner').addClass('d-none');
				$('#fpVerifyOtpBtn').prop('disabled', false);
			}
		});
	});

	// Step 3: Reset Password
	$('#fpResetForm').on('submit', function(e) {
		e.preventDefault();
		
		const password = $('#fpNewPassword').val().trim();
		const confirmPassword = $('#fpConfirmPassword').val().trim();
		
		// Use logged-in customer's email
		@if(Auth::guard('customer')->check())
		const email = '{{ Auth::guard('customer')->user()->email }}';
		@else
		showFpError('Please login to reset your password.');
		return false;
		@endif
		
		// Clear previous errors
		$('.form-control', this).removeClass('is-invalid');
		$('.invalid-feedback').text('');
		
		// Validate
		let hasError = false;
		
		if (!password || password.length < 8) {
			$('#fpNewPassword').addClass('is-invalid');
			$('#fpNewPassword').parent().next('.invalid-feedback').text('Password must be at least 8 characters');
			hasError = true;
		}
		
		if (password !== confirmPassword) {
			$('#fpConfirmPassword').addClass('is-invalid');
			$('#fpConfirmPassword').parent().next('.invalid-feedback').text('Passwords do not match');
			hasError = true;
		}
		
		if (hasError) {
			return false;
		}
		
		// Show loading
		$('#fpResetBtnText').text('Resetting...');
		$('#fpResetBtnSpinner').removeClass('d-none');
		$('#fpResetBtn').prop('disabled', true);
		$('#fpMessage').hide().html('');
		
		$.ajax({
			url: '/api/auth/forgot-password/reset',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				'Accept': 'application/json'
			},
			data: {
				email: email,
				password: password,
				password_confirmation: confirmPassword
			},
			success: function(response) {
				if (response.success) {
					showFpSuccess('Password reset successfully! You can now use your new password.');
					
					// Reset form and go back to change password section after 3 seconds
					setTimeout(function() {
						resetForgotPasswordForm();
						$('#forgotPasswordSection').hide();
						$('#changePasswordSection').show();
					}, 3000);
				}
			},
			error: function(xhr) {
				console.error('Reset Password Error:', xhr);
				let errorMessage = 'Failed to reset password. Please try again.';
				
				if (xhr.responseJSON && xhr.responseJSON.error) {
					errorMessage = xhr.responseJSON.error.message || errorMessage;
					
					if (xhr.status === 422 && xhr.responseJSON.error.errors) {
						const errors = xhr.responseJSON.error.errors;
						
						if (errors.password) {
							$('#fpNewPassword').addClass('is-invalid');
							$('#fpNewPassword').parent().next('.invalid-feedback').text(errors.password[0]);
						}
					}
				}
				
				showFpError(errorMessage);
			},
			complete: function() {
				$('#fpResetBtnText').text('Reset Password');
				$('#fpResetBtnSpinner').addClass('d-none');
				$('#fpResetBtn').prop('disabled', false);
			}
		});
	});

	// Password toggle visibility
	$(document).on('click', '.fp-password-toggle', function() {
		const targetInput = $($(this).data('target'));
		const icon = $(this).find('i');
		
		if (targetInput.attr('type') === 'password') {
			targetInput.attr('type', 'text');
			icon.removeClass('fa-eye').addClass('fa-eye-slash');
		} else {
			targetInput.attr('type', 'password');
			icon.removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});
});
</script>
@endpush
