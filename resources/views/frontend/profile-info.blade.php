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
							@foreach($fields as $field)
								@php
									// Determine column width based on field type
									$colClass = 'col-xl-12 col-lg-12 col-md-12 col-sm-12';
									if (in_array($field->input_type, ['text', 'email', 'tel', 'number', 'date'])) {
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
										
										@if($field->input_type === 'textarea')
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
						
						<!-- Quality-of-Life Fields Section -->
						@if($qolFields && $qolFields->count() > 0)
							<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-4">
								<hr class="my-4">
								<h5 class="mb-3">Quality-of-Life Fields</h5>
							</div>
							
							@foreach($qolFields as $field)
								@php
									// Determine column width based on field type
									$colClass = 'col-xl-12 col-lg-12 col-md-12 col-sm-12';
									if (in_array($field->input_type, ['text', 'email', 'tel', 'number', 'date'])) {
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
									
									// For file fields, get the full URL
									$fileUrl = null;
									if ($field->input_type === 'file' && $fieldValue) {
										$fileUrl = \Storage::disk('public')->url($fieldValue);
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
											<!-- File Upload with Preview -->
											<div class="file-upload-wrapper">
												<div class="mb-3" id="preview_container_{{ $field->field_key }}" style="{{ $fileUrl ? '' : 'display: none;' }}">
													<img src="{{ $fileUrl ?: '#' }}" 
														 alt="Current {{ $field->label }}" 
														 class="img-thumbnail" 
														 style="max-width: 200px; max-height: 200px; object-fit: cover;"
														 id="preview_{{ $field->field_key }}"
														 onerror="this.onerror=null;this.src='{{ asset('frontend/images/user-image.webp') }}';">
													<p class="small text-muted mt-2 mb-0" id="preview_label_{{ $field->field_key }}">
														{{ $fileUrl ? 'Current ' : 'New ' }}{{ $field->label }}
													</p>
												</div>
												
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
														Leave empty to keep current image. Accepted formats: JPEG, JPG, PNG, GIF, WEBP (Max: 2MB)
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
@endsection

@push('scripts')
<script>
(function($) {
	'use strict';
	
	// Get CSRF token
	const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
	
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
				
				// Update label
				var label = document.getElementById(labelId);
				if (label) {
					label.textContent = 'New ' + fieldLabel;
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
	
	// Form submission handler
	$('#profileInfoForm').on('submit', function(e) {
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
							$('#preview_label_profile_image').text('Current Profile Image');
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
					showValidationErrors(errors);
					showMessage('Please correct the errors below.', 'error');
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
