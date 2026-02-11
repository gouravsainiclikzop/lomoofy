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
		
			@include('frontend.partials.customer-sidebar')
			
			<div class="col-12 col-md-12 col-lg-8 col-xl-8">
				<!-- AJAX Success/Error Messages Container -->
				<div id="ajaxMessageContainer"></div>
				
				<!-- Success/Error Messages (for initial page load) -->
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
				
				<!-- row -->
				<div class="row align-items-center">
					<form id="profileInfoForm" class="row g-3 m-0" method="POST" action="{{ route('frontend.profile-info.update') }}" enctype="multipart/form-data">
						@csrf
						
						@if($fields && $fields->count() > 0)
							@php
								// Define the specific field sequence requested
								$fieldSequence = ['full_name', 'profile_image', 'phone', 'email', 'date_of_birth', 'gender'];
								
								// Combine basic_info and qol fields for processing
								$allFields = collect($fields);
								if($qolFields && $qolFields->count() > 0) {
									$allFields = $allFields->merge($qolFields);
								}
								
								// Create a keyed collection for easy lookup
								$fieldsKeyed = $allFields->keyBy('field_key');
								
								// Sort fields according to the defined sequence
								$sortedFields = collect();
								foreach($fieldSequence as $fieldKey) {
									if($fieldsKeyed->has($fieldKey)) {
										$sortedFields->push($fieldsKeyed->get($fieldKey));
									}
								}
								
								// Add any remaining fields that weren't in the sequence
								foreach($fieldsKeyed as $field) {
									if(!in_array($field->field_key, $fieldSequence)) {
										$sortedFields->push($field);
									}
								}
								$hasEmailField = $fieldsKeyed->has('email');
							@endphp
							
							@foreach($sortedFields as $field)
								@php
									// Determine column width based on specific field requirements
									$colClass = 'col-xl-12 col-lg-12 col-md-12 col-sm-12';
									
									// Phone and Email should be in the same row (6 columns each)
									if (in_array($field->field_key, ['phone', 'email'])) {
										$colClass = 'col-xl-6 col-lg-6 col-md-12 col-sm-12';
									}
									// Other fields that can be half-width
									elseif (in_array($field->input_type, ['text', 'number', 'date', 'select']) && 
											!in_array($field->field_key, ['full_name', 'profile_image'])) {
										$colClass = 'col-xl-6 col-lg-6 col-md-12 col-sm-12';
									}
									
									// Get field value from customer
									$fieldValue = null;
									if ($customer) {
										// For date fields, get raw value to avoid Carbon instance issues
										if ($field->input_type === 'date') {
											// Try to get original/raw value first, then fall back to cast value
											$rawValue = $customer->getOriginal($field->field_key);
											if ($rawValue === null) {
												$castValue = $customer->{$field->field_key};
												$fieldValue = $castValue ? $castValue : old($field->field_key, '');
											} else {
												$fieldValue = $rawValue ? $rawValue : old($field->field_key, '');
											}
										} else {
											$fieldValue = isset($customer->{$field->field_key}) && $customer->{$field->field_key} !== null
												? $customer->{$field->field_key} 
												: old($field->field_key, '');
										}
									} else {
										$fieldValue = old($field->field_key, '');
									}
									
									// Format date fields for HTML date input (requires Y-m-d format)
									if ($field->input_type === 'date' && $fieldValue) {
										try {
											// Handle Carbon instances (from model date casting)
											if (is_object($fieldValue) && method_exists($fieldValue, 'format')) {
												$fieldValue = $fieldValue->format('Y-m-d');
											} elseif (is_string($fieldValue)) {
												// Parse string dates - handle both Y-m-d and other formats
												$parsed = \Carbon\Carbon::parse($fieldValue);
												$fieldValue = $parsed->format('Y-m-d');
											}
										} catch (\Exception $e) {
											$fieldValue = '';
										}
									}
									
									// Build input attributes
									$inputAttrs = [
										'class' => 'form-control',
										'id' => $field->field_key,
										'name' => $field->field_key,
										'placeholder' => $field->placeholder ?? '',
									];
									
									if ($field->is_required) {
										$inputAttrs['required'] = 'required';
									}
									
									if ($field->help_text) {
										$inputAttrs['aria-describedby'] = $field->field_key . '_help';
									}
								@endphp
								
								<div class="{{ $colClass }}">
									<div class="form-group">
										<label class="small text-dark ft-medium mb-2">
											{{ $field->label }}
											@if($field->is_required)
												<span class="text-danger">*</span>
											@endif
										</label>
										
										@if($field->input_type === 'file')
											<!-- File Upload Field -->
											@php
									$fileUrl = null;
												if ($fieldValue) {
										$fileUrl = \Storage::disk('public')->url($fieldValue);
									}
								@endphp
											<div class="file-upload-wrapper">
												@if($fileUrl && file_exists(public_path('storage/' . $fieldValue)))
													<div class="mb-3" id="preview_container_{{ $field->field_key }}">
														<img src="{{ $fileUrl }}" 
															 alt="{{ $field->label }}" 
														 class="img-thumbnail" 
														 style="max-width: 200px; max-height: 200px; object-fit: cover;"
														 id="preview_{{ $field->field_key }}"
														 onerror="this.onerror=null;this.src='{{ asset('frontend/images/user-image.webp') }}';">
														<p class="small text-muted mt-2 mb-0" id="preview_label_{{ $field->field_key }}" style="display: none;">
															{{ $field->label }}
													</p>
												</div>
												@endif
												
												<input 
													type="file"
													class="form-control"
													id="{{ $field->field_key }}"
													name="{{ $field->field_key }}"
													accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
													@if($field->is_required && !$fileUrl) required @endif
													@if($field->help_text) aria-describedby="{{ $field->field_key }}_help" @endif
													onchange="previewImage(this, 'preview_{{ $field->field_key }}', 'preview_container_{{ $field->field_key }}', 'preview_label_{{ $field->field_key }}', '{{ $field->label }}')"
												>
												
												@if(!$fileUrl)
													<small class="text-muted d-block mt-1">
														Accepted formats: JPEG, JPG, PNG, GIF, WEBP (Max: 2MB)
													</small>
												@else
													<small class="text-muted d-block mt-1">
														Accepted formats: JPEG, JPG, PNG, GIF, WEBP (Max: 2MB)
													</small>
												@endif
											</div>
										
										@elseif($field->input_type === 'textarea')
											<textarea 
												class="form-control {{ $field->field_key === 'about_us' ? 'ht-80' : '' }}"
												id="{{ $field->field_key }}"
												name="{{ $field->field_key }}"
												placeholder="{{ $field->placeholder ?? '' }}"
												@if($field->is_required) required @endif
												@if($field->help_text) aria-describedby="{{ $field->field_key }}_help" @endif
											>{{ $fieldValue }}</textarea>
										
										@elseif($field->input_type === 'select')
											<select 
												class="form-control"
												id="{{ $field->field_key }}"
												name="{{ $field->field_key }}"
												@if($field->is_required) required @endif
												@if($field->help_text) aria-describedby="{{ $field->field_key }}_help" @endif
											>
												<option value="">Select {{ $field->label }}</option>
												@if($field->options && is_array($field->options))
													@foreach($field->options as $option)
														<option value="{{ $option['value'] ?? $option }}" 
															{{ $fieldValue == ($option['value'] ?? $option) ? 'selected' : '' }}>
															{{ $option['label'] ?? $option }}
														</option>
													@endforeach
												@endif
											</select>
										
										@elseif($field->input_type === 'checkbox')
											<div class="form-check">
												<input 
													type="checkbox"
													class="form-check-input"
													id="{{ $field->field_key }}"
													name="{{ $field->field_key }}"
													value="1"
													{{ $fieldValue ? 'checked' : '' }}
													@if($field->is_required) required @endif
												>
												<label class="form-check-label" for="{{ $field->field_key }}">
													{{ $field->placeholder ?? 'Yes' }}
												</label>
											</div>
										
										@elseif($field->input_type === 'radio')
											<div>
												@if($field->options && is_array($field->options))
													@foreach($field->options as $option)
														<div class="form-check">
															<input 
																type="radio"
																class="form-check-input"
																id="{{ $field->field_key }}_{{ $loop->index }}"
																name="{{ $field->field_key }}"
																value="{{ $option['value'] ?? $option }}"
																{{ $fieldValue == ($option['value'] ?? $option) ? 'checked' : '' }}
																@if($field->is_required) required @endif
															>
															<label class="form-check-label" for="{{ $field->field_key }}_{{ $loop->index }}">
																{{ $option['label'] ?? $option }}
															</label>
														</div>
													@endforeach
												@endif
											</div>
										
										@else
											<input 
												type="{{ $field->input_type }}"
												class="form-control"
												id="{{ $field->field_key }}"
												name="{{ $field->field_key }}"
												value="{{ $fieldValue }}"
												placeholder="{{ $field->placeholder ?? '' }}"
												@if($field->is_required) required @endif
												@if($field->help_text) aria-describedby="{{ $field->field_key }}_help" @endif
											>
										@endif
										
										@if($field->help_text)
											<small id="{{ $field->field_key }}_help" class="form-text text-muted">
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
								<p class="text-muted">No profile fields configured. Please contact administrator.</p>
							</div>
						@endif
						
						<!-- Email verification (OTP) when changing email -->
						@if(!empty($hasEmailField))
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12" id="emailVerifySection" style="display: none;">
							<div class="card-wrap border rounded mb-3">
								<div class="card-wrap-body px-3 py-3">
									<div class="alert alert-info py-2 mb-2 small">
										<i class="fas fa-info-circle"></i> You changed your email. Verify the new email to save your profile.
									</div>
									<div id="emailVerifyMessage" class="mb-2" style="display: none;"></div>
									<div id="emailVerifyStep1">
										<button type="button" id="profileSendEmailOtpBtn" class="btn btn-outline-dark btn-sm">
											<span id="profileSendOtpBtnText">Send verification code to new email</span>
											<span id="profileSendOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
										</button>
									</div>
									<div id="emailVerifyStep2" style="display: none;">
										<label for="profileEmailOtp" class="small text-dark ft-medium mb-1">Verification code (6 digits)</label>
										<div class="d-flex flex-wrap align-items-center gap-2">
											<input type="text" class="form-control form-control-sm" id="profileEmailOtp" placeholder="Enter code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" style="max-width: 140px;">
											<button type="button" id="profileVerifyEmailOtpBtn" class="btn btn-dark btn-sm">
												<span id="profileVerifyOtpBtnText">Verify</span>
												<span id="profileVerifyOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
											</button>
										</div>
										<small class="text-muted d-block mt-1">Didn't receive? <a href="#" id="profileResendEmailOtp" class="text-primary">Resend code</a> <span id="profileResendTimer" class="text-muted"></span></small>
									</div>
									<div id="emailVerifySuccess" style="display: none;">
										<span class="text-success small"><i class="fas fa-check-circle"></i> New email verified. You can save your profile.</span>
									</div>
								</div>
							</div>
						</div>
						@endif
						
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="form-group">
								<button type="submit" class="btn btn-dark" id="submitBtn">
									<span class="btn-text">Save Changes</span>
									<span class="btn-spinner" style="display: none;">
										<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
										Saving...
									</span>
								</button>
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

<!-- Email verification modal (shown when user must verify new email) -->
@if(!empty($hasEmailField))
<div class="modal fade" id="emailVerifyModal" tabindex="-1" aria-labelledby="emailVerifyModalLabel" aria-hidden="true" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="emailVerifyModalLabel">Verify your new email</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="small text-muted mb-3">A verification code was sent to your new email address. Enter it below to confirm the change.</p>
				<div id="modalEmailVerifyMessage" class="mb-2" style="display: none;"></div>
				<div id="modalEmailVerifyStep1">
					<button type="button" id="modalSendEmailOtpBtn" class="btn btn-dark">
						<span id="modalSendOtpBtnText">Send verification code</span>
						<span id="modalSendOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
					</button>
				</div>
				<div id="modalEmailVerifyStep2" style="display: none;">
					<label for="modalProfileEmailOtp" class="form-label small">Verification code (6 digits)</label>
					<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
						<input type="text" class="form-control" id="modalProfileEmailOtp" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" style="max-width: 140px;">
						<button type="button" id="modalVerifyEmailOtpBtn" class="btn btn-dark">
							<span id="modalVerifyOtpBtnText">Verify</span>
							<span id="modalVerifyOtpBtnSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
						</button>
					</div>
					<small class="text-muted">Didn't receive? <a href="#" id="modalResendEmailOtp" class="text-primary">Resend code</a> <span id="modalResendTimer" class="text-muted"></span></small>
				</div>
				<div id="modalEmailVerifySuccess" style="display: none;">
					<div class="alert alert-success py-2 mb-0 small"><i class="fas fa-check-circle"></i> Email verified. You can now save your profile.</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function($) {
	'use strict';
	
	// Get CSRF token
	const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
	
	// Email verification state (when changing email)
	const originalEmail = @json($customer->email ?? '');
	let emailOtpVerified = false;
	let verifiedEmail = '';
	let profileResendTimer = null;
	let profileResendSeconds = 0;
	
	function profileEmailChanged() {
		const emailEl = $('#email');
		if (!emailEl.length) return false;
		const current = (emailEl.val() || '').trim().toLowerCase();
		const orig = (originalEmail || '').trim().toLowerCase();
		return current !== '' && current !== orig;
	}
	
	function updateEmailVerifySection() {
		if ($('#emailVerifySection').length === 0) return;
		if (profileEmailChanged()) {
			$('#emailVerifySection').show();
			emailOtpVerified = false;
			verifiedEmail = '';
			$('#emailVerifyStep1').show();
			$('#emailVerifyStep2').hide();
			$('#emailVerifySuccess').hide();
			$('#profileEmailOtp').val('');
			$('#emailVerifyMessage').hide().html('');
		} else {
			$('#emailVerifySection').hide();
			emailOtpVerified = false;
			verifiedEmail = '';
		}
	}
	
		function startProfileResendTimer(seconds) {
		profileResendSeconds = seconds;
		$('#profileResendEmailOtp').addClass('disabled pe-none text-muted').removeClass('text-primary');
		$('#modalResendEmailOtp').addClass('disabled pe-none text-muted').removeClass('text-primary');
		if (profileResendTimer) clearInterval(profileResendTimer);
		profileResendTimer = setInterval(function() {
			profileResendSeconds--;
			$('#profileResendTimer').text('(' + profileResendSeconds + 's)');
			$('#modalResendTimer').text('(' + profileResendSeconds + 's)');
			if (profileResendSeconds <= 0) {
				clearInterval(profileResendTimer);
				$('#profileResendTimer').text('');
				$('#modalResendTimer').text('');
				$('#profileResendEmailOtp').text('Resend code').removeClass('disabled pe-none text-muted').addClass('text-primary');
				$('#modalResendEmailOtp').text('Resend code').removeClass('disabled pe-none text-muted').addClass('text-primary');
			}
		}, 1000);
	}
	
	function showEmailVerifyMessage(html, type) {
		type = type || 'success';
		const cls = type === 'success' ? 'alert-success' : 'alert-danger';
		$('#emailVerifyMessage').html('<div class="alert ' + cls + ' py-2 mb-0 small">' + html + '</div>').show();
	}
	
	function openEmailVerifyModal() {
		if ($('#emailVerifyModal').length === 0) return;
		$('#emailVerifyStep1').show();
		$('#emailVerifyStep2').hide();
		$('#emailVerifySuccess').hide();
		$('#emailVerifyMessage').hide().html('');
		$('#modalEmailVerifyStep1').show();
		$('#modalEmailVerifyStep2').hide();
		$('#modalEmailVerifySuccess').hide();
		$('#modalEmailVerifyMessage').hide().html('');
		$('#modalProfileEmailOtp').val('');
		var modalEl = document.getElementById('emailVerifyModal');
		if (typeof bootstrap !== 'undefined' && modalEl) {
			var modal = new bootstrap.Modal(modalEl);
			modal.show();
		}
	}
	
	function showModalEmailVerifyMessage(html, type) {
		type = type || 'success';
		const cls = type === 'success' ? 'alert-success' : 'alert-danger';
		$('#modalEmailVerifyMessage').html('<div class="alert ' + cls + ' py-2 mb-0 small">' + html + '</div>').show();
	}
	
	// Image preview function
	function previewImage(input, previewId, containerId, labelId, fieldLabel) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			
			reader.onload = function(e) {
				// Update preview image
				var preview = document.getElementById(previewId);
				if (preview) {
					preview.src = e.target.result;
				}
				
				// Show preview container
				var container = document.getElementById(containerId);
				if (container) {
					container.style.display = 'block';
				}
				
				// Hide label
				var label = document.getElementById(labelId);
				if (label) {
					label.style.display = 'none';
				}
			};
			
			reader.readAsDataURL(input.files[0]);
		} else {
			// Hide preview if file input is cleared
			var container = document.getElementById(containerId);
			if (container && !container.querySelector('img').src.includes('storage/')) {
				container.style.display = 'none';
			}
		}
	}
	
	// Make previewImage available globally
	window.previewImage = previewImage;
	
	// Show message function
	function showMessage(message, type) {
		type = type || 'success';
		const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
		const icon = type === 'success' ? '✓' : '✗';
		
		const messageHtml = `
			<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
				<strong>${icon}</strong> ${message}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		`;
		
		$('#ajaxMessageContainer').html(messageHtml);
		
		// Auto-hide after 5 seconds
		setTimeout(function() {
			$('#ajaxMessageContainer .alert').fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
		
		// Scroll to top to show message
		$('html, body').animate({
			scrollTop: $('#ajaxMessageContainer').offset().top - 100
		}, 300);
	}
	
	// Clear validation errors
	function clearValidationErrors() {
		$('.text-danger.small').remove();
		$('.form-control.is-invalid').removeClass('is-invalid');
		$('.form-check-input.is-invalid').removeClass('is-invalid');
	}
	
	// Show validation errors
	function showValidationErrors(errors) {
		clearValidationErrors();
		
		$.each(errors, function(field, messages) {
			const fieldElement = $('#' + field);
			const fieldContainer = fieldElement.closest('.form-group');
			
			// Add invalid class
			fieldElement.addClass('is-invalid');
			
			// Show error message
			let errorHtml = '<div class="text-danger small mt-1">';
			if (Array.isArray(messages)) {
				errorHtml += messages.join('<br>');
			} else {
				errorHtml += messages;
			}
			errorHtml += '</div>';
			
			fieldContainer.append(errorHtml);
		});
	}
	
	// Update sidebar image if profile_image was updated
	function updateSidebarImage(imageUrl) {
		if (imageUrl) {
			$('#customerSidebarImage').attr('src', imageUrl);
		}
	}
	
	// Email verification: show/hide section when email field changes
	$(document).on('input change', '#email', function() {
		updateEmailVerifySection();
	});
	updateEmailVerifySection();
	
	// If page loaded with email verification error (e.g. form submit without AJAX), open modal
	@if(session('error') && strpos(session('error', ''), 'verify your new email') !== false)
	if ($('#emailVerifyModal').length && profileEmailChanged()) {
		openEmailVerifyModal();
	}
	@endif
	
	// Send OTP to new email
	$('#profileSendEmailOtpBtn').on('click', function() {
		const email = $('#email').val().trim();
		if (!email || !profileEmailChanged()) return;
		$('#profileSendOtpBtnText').text('Sending...');
		$('#profileSendOtpBtnSpinner').removeClass('d-none');
		$('#profileSendEmailOtpBtn').prop('disabled', true);
		$('#emailVerifyMessage').hide().html('');
		$.ajax({
			url: '/api/auth/profile/send-email-otp',
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
			data: { email: email },
			success: function(res) {
				if (res.success) {
					$('#emailVerifyStep1').hide();
					$('#emailVerifyStep2').show();
					showEmailVerifyMessage('<i class="fas fa-check-circle"></i> Verification code sent to ' + email, 'success');
					startProfileResendTimer(60);
				}
			},
			error: function(xhr) {
				const msg = (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message) ? xhr.responseJSON.error.message : 'Failed to send code. Try again.';
				if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.errors && xhr.responseJSON.error.errors.email) {
					showEmailVerifyMessage(xhr.responseJSON.error.errors.email[0], 'error');
				} else {
					showEmailVerifyMessage(msg, 'error');
				}
			},
			complete: function() {
				$('#profileSendOtpBtnText').text('Send verification code to new email');
				$('#profileSendOtpBtnSpinner').addClass('d-none');
				$('#profileSendEmailOtpBtn').prop('disabled', false);
			}
		});
	});
	
	$(document).on('click', '#profileResendEmailOtp', function(e) {
		e.preventDefault();
		if ($(this).hasClass('disabled')) return;
		$('#profileSendEmailOtpBtn').click();
	});
	
	$('#profileVerifyEmailOtpBtn').on('click', function() {
		const email = $('#email').val().trim();
		const otp = $('#profileEmailOtp').val().trim();
		if (!email || otp.length !== 6) {
			showEmailVerifyMessage('Please enter the 6-digit code.', 'error');
			$('#profileEmailOtp').focus();
			return;
		}
		$('#profileVerifyOtpBtnText').text('Verifying...');
		$('#profileVerifyOtpBtnSpinner').removeClass('d-none');
		$('#profileVerifyEmailOtpBtn').prop('disabled', true);
		$('#emailVerifyMessage').hide().html('');
		$.ajax({
			url: '/api/auth/profile/verify-email-otp',
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
			data: { email: email, otp: otp },
			success: function(res) {
				if (res.success) {
					emailOtpVerified = true;
					verifiedEmail = email;
					$('#emailVerifyStep2').hide();
					$('#emailVerifySuccess').show();
					if (profileResendTimer) { clearInterval(profileResendTimer); profileResendTimer = null; }
					$('#profileResendTimer').text('');
					showEmailVerifyMessage('<i class="fas fa-check-circle"></i> Email verified. You can save your profile.', 'success');
				}
			},
			error: function(xhr) {
				const msg = (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message) ? xhr.responseJSON.error.message : 'Invalid or expired code.';
				showEmailVerifyMessage(msg, 'error');
			},
			complete: function() {
				$('#profileVerifyOtpBtnText').text('Verify');
				$('#profileVerifyOtpBtnSpinner').addClass('d-none');
				$('#profileVerifyEmailOtpBtn').prop('disabled', false);
			}
		});
	});
	
	// Modal: Send OTP to new email
	$('#modalSendEmailOtpBtn').on('click', function() {
		const email = $('#email').val().trim();
		if (!email || !profileEmailChanged()) return;
		$('#modalSendOtpBtnText').text('Sending...');
		$('#modalSendOtpBtnSpinner').removeClass('d-none');
		$('#modalSendEmailOtpBtn').prop('disabled', true);
		$('#modalEmailVerifyMessage').hide().html('');
		$.ajax({
			url: '/api/auth/profile/send-email-otp',
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
			data: { email: email },
			success: function(res) {
				if (res.success) {
					$('#modalEmailVerifyStep1').hide();
					$('#modalEmailVerifyStep2').show();
					showModalEmailVerifyMessage('<i class="fas fa-check-circle"></i> Code sent to ' + email, 'success');
					startProfileResendTimer(60);
				}
			},
			error: function(xhr) {
				const msg = (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message) ? xhr.responseJSON.error.message : 'Failed to send code. Try again.';
				if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.errors && xhr.responseJSON.error.errors.email) {
					showModalEmailVerifyMessage(xhr.responseJSON.error.errors.email[0], 'error');
				} else {
					showModalEmailVerifyMessage(msg, 'error');
				}
			},
			complete: function() {
				$('#modalSendOtpBtnText').text('Send verification code');
				$('#modalSendOtpBtnSpinner').addClass('d-none');
				$('#modalSendEmailOtpBtn').prop('disabled', false);
			}
		});
	});
	
	$(document).on('click', '#modalResendEmailOtp', function(e) {
		e.preventDefault();
		if ($(this).hasClass('disabled')) return;
		$('#modalSendEmailOtpBtn').click();
	});
	
	$('#modalVerifyEmailOtpBtn').on('click', function() {
		const email = $('#email').val().trim();
		const otp = $('#modalProfileEmailOtp').val().trim();
		if (!email || otp.length !== 6) {
			showModalEmailVerifyMessage('Please enter the 6-digit code.', 'error');
			$('#modalProfileEmailOtp').focus();
			return;
		}
		$('#modalVerifyOtpBtnText').text('Verifying...');
		$('#modalVerifyOtpBtnSpinner').removeClass('d-none');
		$('#modalVerifyEmailOtpBtn').prop('disabled', true);
		$('#modalEmailVerifyMessage').hide().html('');
		$.ajax({
			url: '/api/auth/profile/verify-email-otp',
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
			data: { email: email, otp: otp },
			success: function(res) {
				if (res.success) {
					emailOtpVerified = true;
					verifiedEmail = email;
					$('#modalEmailVerifyStep2').hide();
					//$('#modalEmailVerifySuccess').show();
					if (profileResendTimer) { clearInterval(profileResendTimer); profileResendTimer = null; }
					$('#profileResendTimer').text('');
					$('#modalResendTimer').text('');
					showModalEmailVerifyMessage('<i class="fas fa-check-circle"></i> Email verified. You can save your profile.', 'success');
				}
			},
			error: function(xhr) {
				const msg = (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message) ? xhr.responseJSON.error.message : 'Invalid or expired code.';
				showModalEmailVerifyMessage(msg, 'error');
			},
			complete: function() {
				$('#modalVerifyOtpBtnText').text('Verify');
				$('#modalVerifyOtpBtnSpinner').addClass('d-none');
				$('#modalVerifyEmailOtpBtn').prop('disabled', false);
			}
		});
	});
	
	// Form submission handler
	$('#profileInfoForm').on('submit', function(e) {
		// Require email OTP when email was changed
		if (profileEmailChanged()) {
			const currentEmail = $('#email').val().trim().toLowerCase();
			if (!emailOtpVerified || verifiedEmail.toLowerCase() !== currentEmail) {
				e.preventDefault();
				showMessage('Please verify your new email with the code sent to it before saving.', 'error');
				openEmailVerifyModal();
				return;
			}
		}
		
		e.preventDefault();
		
		const form = $(this);
		const submitBtn = $('#submitBtn');
		const btnText = submitBtn.find('.btn-text');
		const btnSpinner = submitBtn.find('.btn-spinner');
		
		// Clear previous messages and errors
		clearValidationErrors();
		$('#ajaxMessageContainer').empty();
		
		// Disable submit button and show loading
		submitBtn.prop('disabled', true);
		btnText.hide();
		btnSpinner.show();
		
		// Create FormData for file uploads
		const formData = new FormData(form[0]);
		formData.append('_token', csrfToken);
		
		// AJAX request
		$.ajax({
			url: form.attr('action'),
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				// Re-enable submit button
				submitBtn.prop('disabled', false);
				btnText.show();
				btnSpinner.hide();
				
				if (response.success) {
					showMessage(response.message || 'Profile updated successfully!', 'success');
					
					// Update form field values from response data
					if (response.data) {
						// Update text, email, tel, number, date inputs
						$.each(response.data, function(key, value) {
							if (key === 'profile_image' || key === 'profile_image_url' || key === 'full_name') {
								return; // Handle these separately
							}
							
							const fieldElement = $('#' + key);
							if (fieldElement.length) {
								const fieldType = fieldElement.attr('type') || fieldElement.prop('tagName').toLowerCase();
								
								if (fieldType === 'checkbox') {
									fieldElement.prop('checked', value == 1 || value === true);
								} else if (fieldType === 'radio') {
									$('input[name="' + key + '"][value="' + value + '"]').prop('checked', true);
								} else if (fieldType === 'select') {
									fieldElement.val(value).trigger('change');
								} else if (fieldType === 'textarea') {
									fieldElement.val(value);
								} else if (fieldType === 'date' && value) {
									// Format date for HTML date input (Y-m-d)
									try {
										const dateValue = new Date(value);
										if (!isNaN(dateValue.getTime())) {
											const formattedDate = dateValue.getFullYear() + '-' + 
												String(dateValue.getMonth() + 1).padStart(2, '0') + '-' + 
												String(dateValue.getDate()).padStart(2, '0');
											fieldElement.val(formattedDate);
										}
									} catch(e) {
										// If value is already in Y-m-d format, use it directly
										if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
											fieldElement.val(value);
										}
									}
								} else {
									fieldElement.val(value);
								}
								
								// Add visual feedback (brief highlight)
								fieldElement.addClass('border-success');
								setTimeout(function() {
									fieldElement.removeClass('border-success');
								}, 2000);
							}
						});
					}
					
					// Update sidebar image if profile_image was updated
					if (response.data && (response.data.profile_image_url || response.data.profile_image)) {
						const imageUrl = response.data.profile_image_url || 
							(response.data.profile_image.startsWith('http') 
								? response.data.profile_image 
								: '{{ asset("storage/") }}/' + response.data.profile_image);
						updateSidebarImage(imageUrl);
						
						// Update preview image if it exists
						const previewImg = $('#preview_profile_image');
						if (previewImg.length) {
							previewImg.attr('src', imageUrl);
							$('#preview_container_profile_image').show();
							$('#preview_label_profile_image').text('');
						}
					}
					
					// Update customer name in sidebar if full_name was updated
					if (response.data && response.data.full_name) {
						$('#customerSidebarName').text(response.data.full_name);
					}
					
					// Remove validation error classes on success
					form.find('.is-invalid').removeClass('is-invalid');
					
					// Clear file inputs (optional - you might want to keep them)
					// form.find('input[type="file"]').val('');
				} else {
					showMessage(response.message || 'Failed to update profile. Please try again.', 'error');
					
					if (response.errors) {
						showValidationErrors(response.errors);
					}
				}
			},
			error: function(xhr) {
				// Re-enable submit button
				submitBtn.prop('disabled', false);
				btnText.show();
				btnSpinner.hide();
				
				if (xhr.status === 422) {
					// Validation errors
					const errors = xhr.responseJSON.errors || {};
					const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '';
					const isEmailVerifyError = msg.indexOf('verify your new email') !== -1 || (errors.email && (errors.email[0] + '').indexOf('verify') !== -1);
					showValidationErrors(errors);
					showMessage(msg || 'Please correct the errors below.', 'error');
					if (isEmailVerifyError) {
						openEmailVerifyModal();
					}
				} else if (xhr.status === 401) {
					// Unauthorized
					showMessage('Your session has expired. Please login again.', 'error');
					setTimeout(function() {
						window.location.href = '{{ route("frontend.index") }}';
					}, 2000);
				} else {
					// Other errors
					const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
					showMessage(errorMessage, 'error');
				}
			}
		});
	});
	
})(jQuery);
</script>
@endpush
