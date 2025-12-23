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
				
				<!-- Existing Addresses -->
				@if(isset($addresses) && count($addresses) > 0)
					<div class="row align-items-start mb-4">
						@foreach($addresses as $address)
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4" data-address-container-id="{{ $address->id }}">
								<div class="card-wrap border rounded {{ ($address->is_default ?? false) ? 'border-success border-2' : '' }}" 
									 style="{{ ($address->is_default ?? false) ? 'box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);' : '' }}">
									<div class="card-wrap-header px-3 py-2 br-bottom d-flex align-items-center justify-content-between">
										<div class="card-header-flex">
											<h4 class="fs-md ft-bold mb-1">{{ $address->address_type ?? 'Address' }}</h4>
											@if($address->is_default ?? false)
												<p class="m-0 p-0">
													<span class="text-success bg-light-success small ft-medium px-2 py-1">Current Address</span>
												</p>
											@endif
											@if($address->is_primary ?? false)
												<p class="m-0 p-0">
													<span class="text-success bg-light-success small ft-medium px-2 py-1">Primary Account</span>
												</p>
											@endif
										</div>
										<div class="card-head-last-flex d-flex align-items-center gap-2">
											<a class="border p-3 circle text-dark d-inline-flex align-items-center justify-content-center edit-address" 
											   href="#" 
											   data-address-id="{{ $address->id }}">
												<i class="fas fa-pen-nib position-absolute"></i>
											</a>
											<button class="border bg-white text-danger p-3 circle text-dark d-inline-flex align-items-center justify-content-center delete-address" 
													data-address-id="{{ $address->id }}">
												<i class="fas fa-times position-absolute"></i>
											</button>
										</div>
									</div>
									<div class="card-wrap-body px-3 py-3">
										@if($addressFields && $addressFields->count() > 0)
											@foreach($addressFields as $field)
												@php
													$fieldValue = $address->{$field->field_key} ?? '';
												@endphp
												@if($fieldValue && $field->field_key !== 'address_type')
													@if($field->field_key === 'full_name' || $field->field_key === 'name')
														<h5 class="ft-medium mb-1">{{ $fieldValue }}</h5>
													@elseif(in_array($field->field_key, ['address_line1', 'address_line2', 'landmark', 'city', 'state', 'country', 'pincode']))
														<p class="mb-1">{{ $fieldValue }}</p>
													@elseif($field->field_key === 'email')
														<p class="lh-1 mb-1">
															<span class="text-dark ft-medium">Email:</span> {{ $fieldValue }}
														</p>
													@elseif($field->field_key === 'phone' || $field->field_key === 'alternate_phone')
														<p class="mb-1">
															<span class="text-dark ft-medium">Call:</span> {{ $fieldValue }}
														</p>
													@endif
												@endif
											@endforeach
										@endif
									</div>
								</div>
							</div>
						@endforeach
					</div>
				@endif
				
				<!-- Add/Edit Address Form -->
				<div class="row align-items-start">
					<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
						<div class="card-wrap border rounded mb-4">
							<div class="card-wrap-header px-3 py-2 br-bottom">
								<h4 class="fs-md ft-bold mb-0" id="addressFormTitle">Add New Address</h4>
							</div>
							<div class="card-wrap-body px-3 py-3">
								<form id="addressForm" class="row g-3" method="POST" action="{{ route('frontend.addresses.save') }}">
									@csrf
									<input type="hidden" name="address_id" id="address_id" value="">
									<input type="hidden" name="country" id="address_country" value="India">
									
									@if($addressFields && $addressFields->count() > 0)
										@php
											// Reorder fields: pincode first, then others (optimized - do it once)
											$pincodeField = null;
											$locationFields = [];
											$otherFields = [];
											
											foreach ($addressFields as $field) {
												if ($field->field_key === 'pincode') {
													$pincodeField = $field;
												} elseif (in_array($field->field_key, ['country', 'state', 'city'])) {
													$locationFields[] = $field;
												} else {
													$otherFields[] = $field;
												}
											}
											
											// Combine in order
											$sortedFields = collect();
											if ($pincodeField) {
												$sortedFields->push($pincodeField);
											}
											$sortedFields = $sortedFields->merge($locationFields)->merge($otherFields);
										@endphp
										@foreach($sortedFields as $field)
											@php
												// Skip country field - hide it
												if ($field->field_key === 'country') {
													continue;
												}
												
												// Determine column width
												$colClass = 'col-xl-12 col-lg-12 col-md-12 col-sm-12';
												if (in_array($field->input_type, ['text', 'email', 'tel', 'number', 'date', 'select'])) {
													$colClass = 'col-xl-6 col-lg-6 col-md-12 col-sm-12';
												}
												
												$fieldValue = old($field->field_key, '');
												
												$inputAttrs = [
													'class' => 'form-control',
													'id' => 'address_' . $field->field_key,
													'name' => $field->field_key,
													'placeholder' => $field->placeholder ?? '',
												];
												
												if ($field->is_required) {
													$inputAttrs['required'] = 'required';
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
															class="form-control"
															id="address_{{ $field->field_key }}"
															name="{{ $field->field_key }}"
															placeholder="{{ $field->placeholder ?? '' }}"
															@if($field->is_required) required @endif
														>{{ $fieldValue }}</textarea>
													
													@elseif($field->input_type === 'select')
														<select 
															class="form-control"
															id="address_{{ $field->field_key }}"
															name="{{ $field->field_key }}"
															@if($field->is_required) required @endif
														>
															<option value="">Select {{ $field->label }}</option>
															@if($field->field_key === 'country')
																{{-- Show only India for country field --}}
																<option value="India" {{ $fieldValue == 'India' ? 'selected' : '' }}>India</option>
															@elseif($field->options && is_array($field->options))
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
																id="address_{{ $field->field_key }}"
																name="{{ $field->field_key }}"
																value="1"
																{{ $fieldValue ? 'checked' : '' }}
																@if($field->is_required) required @endif
															>
															<label class="form-check-label" for="address_{{ $field->field_key }}">
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
																			id="address_{{ $field->field_key }}_{{ $loop->index }}"
																			name="{{ $field->field_key }}"
																			value="{{ $option['value'] ?? $option }}"
																			{{ $fieldValue == ($option['value'] ?? $option) ? 'checked' : '' }}
																			@if($field->is_required) required @endif
																		>
																		<label class="form-check-label" for="address_{{ $field->field_key }}_{{ $loop->index }}">
																			{{ $option['label'] ?? $option }}
																		</label>
																	</div>
																@endforeach
															@endif
														</div>
													
													@else
														@if($field->field_key === 'country')
															{{-- Country field - show only India --}}
															<input 
																type="text"
																class="form-control"
																id="address_{{ $field->field_key }}"
																name="{{ $field->field_key }}"
																value="India"
																readonly
																@if($field->is_required) required @endif
															>
														@else
															<input 
																type="{{ $field->input_type }}"
																class="form-control"
																id="address_{{ $field->field_key }}"
																name="{{ $field->field_key }}"
																value="{{ $fieldValue }}"
																placeholder="{{ $field->placeholder ?? '' }}"
																@if($field->is_required) required @endif
															>
														@endif
													@endif
													
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
											<p class="text-muted">No address fields configured. Please contact administrator.</p>
										</div>
									@endif
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
										<div class="form-group d-flex gap-2">
											<button type="submit" class="btn btn-dark">Save Address</button>
											<button type="button" class="btn btn-secondary" id="cancelAddressForm" style="display: none;">Cancel</button>
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

@push('scripts')
<script>
(function($) {
	'use strict';
	
	// Get CSRF token
	const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
	
	// Pincode lookup functionality
	let pincodeLookupTimeout;
	let isSearchingPincode = false;
	let lastSearchedPincode = null;
	const pincodeInput = $('#address_pincode');
	
	if (pincodeInput.length) {
		// Listen for pincode input
		pincodeInput.on('input', function() {
			const pincode = $(this).val().trim();
			
			// Clear previous timeout
			clearTimeout(pincodeLookupTimeout);
			
			// Only lookup if pincode is 4-10 digits and not already searched
			if (pincode.length >= 4 && pincode.length <= 10 && /^\d+$/.test(pincode)) {
				// Don't search if we're already searching or if this is the same pincode we just searched
				if (!isSearchingPincode && pincode !== lastSearchedPincode) {
					// Debounce: wait 800ms after user stops typing
					pincodeLookupTimeout = setTimeout(function() {
						lookupLocationByPincode(pincode);
					}, 800);
				}
			} else if (pincode.length === 0) {
				// Clear location fields if pincode is cleared
				clearLocationFields();
				lastSearchedPincode = null;
			}
		});
		
		// Also trigger on blur for immediate lookup
		pincodeInput.on('blur', function() {
			const pincode = $(this).val().trim();
			if (pincode.length >= 4 && pincode.length <= 10 && /^\d+$/.test(pincode)) {
				// Don't search if we're already searching or if this is the same pincode we just searched
				if (!isSearchingPincode && pincode !== lastSearchedPincode) {
					clearTimeout(pincodeLookupTimeout);
					lookupLocationByPincode(pincode);
				}
			}
		});
	}
	
	// Function to lookup location by pincode
	function lookupLocationByPincode(pincode) {
		// Prevent duplicate searches
		if (isSearchingPincode || pincode === lastSearchedPincode) {
			return;
		}
		
		// Set flag to prevent duplicate requests
		isSearchingPincode = true;
		lastSearchedPincode = pincode;
		
		// Show loading indicator
		const pincodeField = $('#address_pincode');
		pincodeField.addClass('is-loading');
		pincodeField.after('<span class="pincode-loader ms-2"><i class="fas fa-spinner fa-spin text-primary"></i></span>');
		
		// Remove any previous error messages
		$('.pincode-error').remove();
		
		$.ajax({
			url: '{{ route("frontend.location-by-pincode") }}',
			method: 'GET',
			data: {
				pincode: pincode
			},
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success && response.data) {
					const data = response.data;
					
					// Step 1: Show alert with the found pincode object
					const pincodeObject = {
						pincode: data.pincode,
						officeName: data.officeName || '',
						taluk: data.taluk || '',
						districtName: data.districtName || '',
						stateName: data.state || '',
						city: data.city || '',
						country: data.country || 'India'
					};
					
					// alert('Pincode found in pincodes.json!\n\n' + 
					// 	'Pincode: ' + pincodeObject.pincode + '\n' +
					// 	'Office Name: ' + pincodeObject.officeName + '\n' +
					// 	'Taluk: ' + pincodeObject.taluk + '\n' +
					// 	'District: ' + pincodeObject.districtName + '\n' +
					// 	'State: ' + pincodeObject.stateName + '\n' +
					// 	'City: ' + pincodeObject.city + '\n' +
					// 	'Country: ' + pincodeObject.country + '\n\n' +
					// 	'Full Object:\n' + JSON.stringify(pincodeObject, null, 2));
					
					// Auto-fill country (always India)
					if ($('#address_country').length) {
						const countryField = $('#address_country');
						const countryValue = 'India'; // Always set to India
						
						if (countryField.is('select')) {
							// For select dropdown, try to find India option
							const option = countryField.find('option').filter(function() {
								return $(this).text().toLowerCase().includes('india') || 
									   $(this).val().toLowerCase() === 'india';
							}).first();
							if (option.length) {
								countryField.val(option.val());
								// Trigger change without bubbling to prevent pincode search retrigger
								countryField[0].dispatchEvent(new Event('change', { bubbles: false }));
							} else {
								// If no match, set value directly
								countryField.val(countryValue);
							}
						} else {
							// For text input (readonly), just set the value
							countryField.val(countryValue);
						}
						countryField.addClass('border-success');
						setTimeout(function() {
							countryField.removeClass('border-success');
						}, 2000);
					}
					
					// Auto-fill state (without triggering change event to avoid loops)
					if (data.state && $('#address_state').length) {
						const stateField = $('#address_state');
						if (stateField.is('select')) {
							const option = stateField.find('option').filter(function() {
								return $(this).text().toLowerCase().includes(data.state.toLowerCase()) || 
									   $(this).val().toLowerCase() === data.state.toLowerCase();
							}).first();
							if (option.length) {
								stateField.val(option.val());
								// Trigger change without bubbling to prevent pincode search retrigger
								stateField[0].dispatchEvent(new Event('change', { bubbles: false }));
							} else {
								stateField.val(data.state);
							}
						} else {
							stateField.val(data.state);
						}
						stateField.addClass('border-success');
						setTimeout(function() {
							stateField.removeClass('border-success');
						}, 2000);
					}
					
					// Auto-fill city (without triggering change event to avoid loops)
					if (data.city && $('#address_city').length) {
						const cityField = $('#address_city');
						if (cityField.is('select')) {
							const option = cityField.find('option').filter(function() {
								return $(this).text().toLowerCase().includes(data.city.toLowerCase()) || 
									   $(this).val().toLowerCase() === data.city.toLowerCase();
							}).first();
							if (option.length) {
								cityField.val(option.val());
								// Trigger change without bubbling to prevent pincode search retrigger
								cityField[0].dispatchEvent(new Event('change', { bubbles: false }));
							} else {
								cityField.val(data.city);
							}
						} else {
							cityField.val(data.city);
						}
						cityField.addClass('border-success');
						setTimeout(function() {
							cityField.removeClass('border-success');
						}, 2000);
					}
					
					// Show success message
					showPincodeMessage('Location found and auto-filled!', 'success');
				} else {
					showPincodeMessage(response.message || 'Location not found for this pincode', 'error');
				}
			},
			error: function(xhr) {
				if (xhr.status === 404) {
					showPincodeMessage('Location not found for this pincode', 'error');
				} else {
					showPincodeMessage('Error fetching location data. Please try again.', 'error');
				}
			},
			complete: function() {
				// Remove loading indicator
				pincodeField.removeClass('is-loading');
				$('.pincode-loader').remove();
				// Reset searching flag
				isSearchingPincode = false;
			}
		});
	}
	
	// Function to clear location fields
	function clearLocationFields() {
		$('#address_country, #address_state, #address_city').val('').trigger('change');
		$('.pincode-error').remove();
	}
	
	// Function to show pincode lookup message
	function showPincodeMessage(message, type) {
		// Remove previous messages
		$('.pincode-error, .pincode-success').remove();
		
		const messageClass = type === 'success' ? 'pincode-success text-success' : 'pincode-error text-danger';
		const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
		
		const messageHtml = `<small class="${messageClass} d-block mt-1">${icon} ${message}</small>`;
		pincodeInput.closest('.form-group').append(messageHtml);
		
		// Auto-remove success messages after 3 seconds
		if (type === 'success') {
			setTimeout(function() {
				$('.pincode-success').fadeOut(function() {
					$(this).remove();
				});
			}, 3000);
		}
	}
	
	// Edit Address functionality
	$(document).on('click', '.edit-address', function(e) {
		e.preventDefault();
		const addressId = $(this).data('address-id');
		loadAddressForEdit(addressId);
	});
	
	// Load address data for editing
	function loadAddressForEdit(addressId) {
		$.ajax({
			url: '/api/address/' + addressId,
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success && response.data) {
					const address = response.data;
					
					// Populate form fields
					$('#address_id').val(address.id || '');
					
					@if($addressFields && $addressFields->count() > 0)
						@foreach($addressFields as $field)
							@if($field->field_key !== 'country')
								const fieldInput{{ $loop->index }} = $('#address_{{ $field->field_key }}');
								if (fieldInput{{ $loop->index }}.length) {
									@if($field->input_type === 'checkbox')
										// Handle checkbox fields - check if value exists in address object
										// Map database is_default to form field (could be is_default, make_default_address, default, make_default, etc.)
										let checkboxValue = null;
										const fieldKey{{ $loop->index }} = '{{ $field->field_key }}';
										const isDefaultField = fieldKey{{ $loop->index }} === 'is_default' || 
															  fieldKey{{ $loop->index }} === 'make_default_address' || 
															  fieldKey{{ $loop->index }} === 'default' || 
															  fieldKey{{ $loop->index }} === 'make_default';
										
										if (address[fieldKey{{ $loop->index }}] !== undefined) {
											checkboxValue = address[fieldKey{{ $loop->index }}];
										} else if (address.is_default !== undefined && isDefaultField) {
											// Map is_default from database to the form field
											checkboxValue = address.is_default;
										}
										
										if (checkboxValue !== null && checkboxValue !== undefined) {
											const isChecked = checkboxValue == 1 || checkboxValue === true || checkboxValue === '1';
											fieldInput{{ $loop->index }}.prop('checked', isChecked);
										} else {
											// If field doesn't exist, uncheck it
											fieldInput{{ $loop->index }}.prop('checked', false);
										}
									@elseif($field->input_type === 'select')
										if (address['{{ $field->field_key }}'] !== undefined && address['{{ $field->field_key }}'] !== null) {
											fieldInput{{ $loop->index }}.val(address['{{ $field->field_key }}']).trigger('change');
										}
									@else
										if (address['{{ $field->field_key }}'] !== undefined && address['{{ $field->field_key }}'] !== null) {
											fieldInput{{ $loop->index }}.val(address['{{ $field->field_key }}']);
										}
									@endif
								}
							@endif
						@endforeach
					@endif
					
					// Update form title
					$('#addressFormTitle').text('Edit Address');
					$('#cancelAddressForm').show();
					
					// Scroll to form
					$('html, body').animate({
						scrollTop: $('#addressForm').offset().top - 100
					}, 500);
				}
			},
			error: function(xhr) {
				console.error('Error loading address:', xhr);
				alert('Error loading address data. Please try again.');
			}
		});
	}
	
	// Delete Address functionality
	$(document).on('click', '.delete-address', function(e) {
		e.preventDefault();
		const addressId = $(this).data('address-id');
		
		if (!confirm('Are you sure you want to delete this address?')) {
			return;
		}
		
		$.ajax({
			url: '/api/address/' + addressId,
			method: 'DELETE',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success) {
					// Remove the address card from DOM
					$('[data-address-container-id="' + addressId + '"]').fadeOut(300, function() {
						$(this).remove();
					});
					
					// Show success message
					alert('Address deleted successfully!');
					
					// Reload page after a moment to refresh the list
					setTimeout(function() {
						window.location.reload();
					}, 500);
				} else {
					alert(response.message || 'Error deleting address');
				}
			},
			error: function(xhr) {
				console.error('Error deleting address:', xhr);
				if (xhr.responseJSON && xhr.responseJSON.message) {
					alert(xhr.responseJSON.message);
				} else {
					alert('Error deleting address. Please try again.');
				}
			}
		});
	});
	
	// Cancel edit form
	$('#cancelAddressForm').on('click', function() {
		$('#addressForm')[0].reset();
		$('#address_id').val('');
		$('#addressFormTitle').text('Add New Address');
		$(this).hide();
		clearLocationFields();
	});
	
})(jQuery);
</script>
@endpush
