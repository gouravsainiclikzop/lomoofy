<!-- ============================= Customer Features =============================== -->

@php
    $sections = \App\Models\Section::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
@endphp

@if(!request()->routeIs('frontend.index'))
    @foreach($sections as $section)
        @switch($section->section_id)
            @case('ishighlights-v1')
                @include('frontend.sections.ishighlights-v1')
                @break
        @endswitch
    @endforeach
@endif

<!-- ============================ Footer Start ================================== -->
<footer class="dark-footer skin-dark-footer style-2">
	<div class="footer-middle">
		<div class="container">
			<div class="row">
				
				<div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
					<div class="footer_widget">
						<img src="{{ $settings->secondary_logo ? asset('storage/' . $settings->secondary_logo) : asset('storage/' . $settings->company_logo) }}" class="img-footer small mb-2" alt="" />
						
						<div class="address mt-3">
							{{ $settings->address }}	
						</div>
						<div class="address mt-3">
							{{ $settings->phone }}<br>{{ $settings->email }}
						</div>
						<div class="address mt-3">
							<ul class="list-inline">
								@if($settings->facebook_url)
									<li class="list-inline-item"><a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-facebook-filled"></i></a></li>
								@endif
								@if($settings->twitter_url)
									<li class="list-inline-item"><a href="{{ $settings->twitter_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-twitter-filled"></i></a></li>
								@endif
								@if($settings->youtube_url)
									<li class="list-inline-item"><a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-youtube"></i></a></li>
								@endif
								@if($settings->instagram_url)
									<li class="list-inline-item"><a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-instagram-filled"></i></a></li>
								@endif
								@if($settings->linkedin_url)
									<li class="list-inline-item"><a href="{{ $settings->linkedin_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-linkedin-original"></i></a></li>
								@endif
								@if($settings->whatsapp_url)
									<li class="list-inline-item"><a href="{{ $settings->whatsapp_url }}" target="_blank" rel="noopener noreferrer"><i class="lni lni-whatsapp"></i></a></li>
								@endif
							</ul>
						</div>
					</div>
				</div>
				
			 
					
				<div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">Shop</h4>
						<ul class="footer-menu">
							@php
								$categories = App\Models\Category::whereNull('parent_id')->where('is_active', true)->get();
							@endphp
							@foreach($categories as $category)
								<li><a href="{{ route('frontend.shop') }}?category={{ $category->slug }}">{{ $category->name }}</a></li>
							@endforeach
						</ul>
					</div>
				</div>
			
			<div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
				<div class="footer_widget">
					<h4 class="widget_title">Legal</h4>
					<ul class="footer-menu">
						@if($legalPages->terms_conditions_status)
							<li><a href="{{ route('frontend.terms') }}">Terms & Conditions</a></li>
						@endif
						@if($legalPages->shipping_status)
							<li><a href="{{ route('frontend.shipping') }}">Shipping Policy</a></li>
						@endif
						@if($legalPages->cancellation_refund_status)
							<li><a href="{{ route('frontend.cancellation-refund') }}">Cancellation & Refund</a></li>
						@endif
						@if($legalPages->return_refund_policy_status)
							<li><a href="{{ route('frontend.return-refund') }}">Return & Refund Policy</a></li>
						@endif
						@if($legalPages->privacy_policy_status)
							<li><a href="{{ route('frontend.privacy') }}">Privacy Policy</a></li>
						@endif
						@if($legalPages->disclaimer_status)
							<li><a href="{{ route('frontend.disclaimer') }}">Disclaimer</a></li>
						@endif
					</ul> 
				</div>
			</div>

				<div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">&nbsp;</h4>
						<ul class="footer-menu"> 
							<li><a href="{{ route('frontend.contact') }}">Contact</a></li>
							<li><a href="{{ route('frontend.faq') }}">FAQs</a></li>
							<li><a href="{{ route('frontend.blog') }}">Blog</a></li>
							<li><a href="{{ route('frontend.about-us') }}">About</a></li>
							<!-- <li><a href="#">Careers</a></li>  -->
						</ul>
						
					</div>
				</div>
				
				<div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">Subscribe</h4>
						<p>Receive updates, hot deals, discounts sent straignt in your inbox daily</p>
						<div class="foot-news-last">
							<form id="subscribeForm">
								@csrf
								<div class="input-group">
								  <input type="email" name="email" id="subscribeEmail" class="form-control" placeholder="Email Address" required>
									<div class="input-group-append">
										<button type="submit" class="input-group-text rounded-0 text-light" id="subscribeBtn">
											<span class="subscribe-icon"><i class="lni lni-arrow-right"></i></span>
											<span class="subscribe-spinner" style="display: none;">
												<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
											</span>
										</button>
									</div>
								</div>
								<div id="subscribeMessage" class="mt-2" style="display: none;"></div>
							</form>
						</div>
						<div class="address mt-3">
							<h5 class="fs-sm text-light">Secure Payments</h5>
							<div class="scr_payment"><img src="{{ asset('frontend/images/card.png') }}" class="img-fluid" alt="" /></div>
						</div>
					</div>
				</div>
					
			</div>
		</div>
	</div>
	
	<div class="footer-bottom">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-12 col-md-12 text-center">
					<p class="mb-0">© {{ date('Y') }} {{ $settings->company_name }} All Rights Reserved.</p>
				</div>
			</div>
		</div>
	</div>
</footer>
<!-- ============================ Footer End ================================== -->

<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script>

$(document).ready(function() { 
    $('#subscribeForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const emailInput = $('#subscribeEmail');
        const submitBtn = $('#subscribeBtn');
        const icon = submitBtn.find('.subscribe-icon');
        const spinner = submitBtn.find('.subscribe-spinner');
        const messageDiv = $('#subscribeMessage');
        
        // Disable submit button and show spinner
        submitBtn.prop('disabled', true);
        icon.hide();
        spinner.show();
        messageDiv.hide();
        
        $.ajax({
            url: '{{ route("frontend.subscribe") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    messageDiv.removeClass('text-danger').addClass('text-success');
                    messageDiv.text(response.message).fadeIn();
                    emailInput.val('');
                } else {
                    messageDiv.removeClass('text-success').addClass('text-danger');
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
                messageDiv.removeClass('text-success').addClass('text-danger');
                messageDiv.html(errorMessage).fadeIn();
            },
            complete: function() {
                // Re-enable submit button and hide spinner
                submitBtn.prop('disabled', false);
                icon.show();
                spinner.hide();
            }
        });
    });
});
</script>
