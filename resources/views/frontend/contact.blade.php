@extends('layouts.frontend')

@section('title', 'Contact Us - Lomoofy Industries')

@section('breadcrumbs')
			<!-- ======================= Top Breadcrubms ======================== -->
		 
			<!-- ======================= Top Breadcrubms ======================== -->
@endsection
			
@section('content')
@php
	$settings = \App\Models\CompanySetting::getSettings();
@endphp
			<!-- ======================= Contact Page Detail ======================== -->
			<section class="middle">
				<div class="container">
				
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">Contact Us</h2>
								<h3 class="ft-bold pt-3">Get In Touch</h3>
							</div>
						</div>
					</div>
					
					<div class="row align-items-start justify-content-between">
					
						<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
							@if($settings->address || $settings->email)
								<div class="card-wrap-body mb-4">
									<h4 class="ft-medium mb-3 theme-cl">Make a Call</h4>
									<p>{{ $settings->address }}</p>
									<p class="lh-1"><span class="text-dark ft-medium">Email:</span> {{ $settings->email }}</p>
								</div>
							@endif

							@if($settings->customer_care_phone || $settings->careers_phone)
								<div class="card-wrap-body mb-3">
									<h4 class="ft-medium mb-3 theme-cl">Make a Call</h4>
									@if($settings->customer_care_phone)	
									<h6 class="ft-medium mb-1">Customer Care:</h6>
									<p class="mb-2">{{ $settings->customer_care_phone }}</p>
									@endif
									@if($settings->careers_phone)
									<h6 class="ft-medium mb-1">Careers::</h6>
									<p>{{ $settings->careers_phone }}</p>
									@endif
								</div>
							@endif
							
							@if($settings->secondary_email)
								<div class="card-wrap-body mb-3">
									<h4 class="ft-medium mb-3 theme-cl">Drop A Mail</h4>
									<p>Fill out our form and we will contact you within 24 hours.</p> 
									<p class="lh-1 text-dark">{{ $settings->secondary_email ?? $settings->email }}</p>
								</div>
							@endif
						</div>
						
						<div class="col-xl-7 col-lg-8 col-md-12 col-sm-12">
							<form id="contactForm" class="row g-3">
								@csrf
									
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
										<label class="small text-dark ft-medium mb-2">Your Name *</label>
										<input type="text" name="name" class="form-control" placeholder="Your Name" required>
									</div>
								</div>
								
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
										<label class="small text-dark ft-medium mb-2">Your Email *</label>
										<input type="email" name="email" class="form-control" placeholder="Your Email" required>
									</div>
								</div>
								
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
										<label class="small text-dark ft-medium mb-2">Subject</label>
										<input type="text" name="subject" class="form-control" placeholder="Type Your Subject">
									</div>
								</div>
								
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
										<label class="small text-dark ft-medium mb-2">Message *</label>
										<textarea name="message" class="form-control ht-80" placeholder="Your Message" required></textarea>
									</div>
								</div>
								
								<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
									<div class="form-group">
										<div id="contactMessage" class="alert" style="display: none;"></div>
										<button type="submit" class="btn btn-dark" id="contactSubmitBtn">
											<span class="btn-text">Send Message</span>
											<span class="btn-spinner" style="display: none;">
												<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
												Sending...
											</span>
										</button>
									</div>
								</div>
								
							</form>
						</div>
						
					</div>
				</div>
			</section>
			<!-- ======================= Contact Page End ======================== -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#contactSubmitBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnSpinner = submitBtn.find('.btn-spinner');
        const messageDiv = $('#contactMessage');
        
        // Disable submit button and show spinner
        submitBtn.prop('disabled', true);
        btnText.hide();
        btnSpinner.show();
        messageDiv.hide();
        
        $.ajax({
            url: '{{ route("frontend.contact.submit") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    messageDiv.removeClass('alert-danger').addClass('alert-success');
                    messageDiv.text(response.message).fadeIn();
                    form[0].reset();
                } else {
                    messageDiv.removeClass('alert-success').addClass('alert-danger');
                    messageDiv.text(response.message || 'Something went wrong. Please try again.').fadeIn();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Something went wrong. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                messageDiv.removeClass('alert-success').addClass('alert-danger');
                messageDiv.html(errorMessage).fadeIn();
            },
            complete: function() {
                // Re-enable submit button and hide spinner
                submitBtn.prop('disabled', false);
                btnText.show();
                btnSpinner.hide();
            }
        });
    });
});
</script>
@endpush
