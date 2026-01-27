<!-- ============================= Customer Features =============================== -->
@php
    $highlights = [];
    
    if ($serviceHighlight->highlight1_active && $serviceHighlight->highlight1_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight1_title,
            'text' => $serviceHighlight->highlight1_text,
            'icon' => $serviceHighlight->highlight1_icon ?: 'fas fa-shopping-basket'
        ];
    }
    if ($serviceHighlight->highlight2_active && $serviceHighlight->highlight2_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight2_title,
            'text' => $serviceHighlight->highlight2_text,
            'icon' => $serviceHighlight->highlight2_icon ?: 'far fa-credit-card'
        ];
    }
    if ($serviceHighlight->highlight3_active && $serviceHighlight->highlight3_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight3_title,
            'text' => $serviceHighlight->highlight3_text,
            'icon' => $serviceHighlight->highlight3_icon ?: 'fas fa-shield-alt'
        ];
    }
    if ($serviceHighlight->highlight4_active && $serviceHighlight->highlight4_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight4_title,
            'text' => $serviceHighlight->highlight4_text,
            'icon' => $serviceHighlight->highlight4_icon ?: 'fas fa-headphones-alt'
        ];
    }
    
    $highlightCount = count($highlights);
    $colClass = $highlightCount > 0 ? 'col-xl-' . (12 / min($highlightCount, 4)) . ' col-lg-' . (12 / min($highlightCount, 4)) . ' col-md-6 col-sm-6' : 'col-xl-3 col-lg-3 col-md-6 col-sm-6';
@endphp

@if($highlightCount > 0)
<section class="px-0 py-3 br-top">
	<div class="container">
		<div class="row">
			@foreach($highlights as $highlight)
			<div class="{{ $colClass }}">
				<div class="d-flex align-items-center justify-content-start py-2">
					<div class="d_ico">
						<i class="{{ $highlight['icon'] }} theme-cl"></i>
					</div>
					<div class="d_capt">
						<h5 class="mb-0">{{ $highlight['title'] }}</h5>
						@if($highlight['text'])
							<span class="text-muted">{{ $highlight['text'] }}</span>
						@endif
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</section>
<!-- ======================= Customer Features ======================== -->
@endif

<!-- ============================ Footer Start ================================== -->
<footer class="dark-footer skin-dark-footer style-2">
	<div class="footer-middle">
		<div class="container">
			<div class="row">
				
				<div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
					<div class="footer_widget">
						<img src="{{ $settings->company_logo ? asset('storage/' . $settings->company_logo) : asset('assets/images/favicon.png') }}" class="img-footer small mb-2" alt="" />
						
						<div class="address mt-3">
							{{ $settings->address }}	
						</div>
						<div class="address mt-3">
							{{ $settings->phone }}<br>{{ $settings->email }}
						</div>
						<div class="address mt-3">
							<ul class="list-inline">
								<li class="list-inline-item"><a href="#"><i class="lni lni-facebook-filled"></i></a></li>
								<li class="list-inline-item"><a href="#"><i class="lni lni-twitter-filled"></i></a></li>
								<li class="list-inline-item"><a href="#"><i class="lni lni-youtube"></i></a></li>
								<li class="list-inline-item"><a href="#"><i class="lni lni-instagram-filled"></i></a></li>
								<li class="list-inline-item"><a href="#"><i class="lni lni-linkedin-original"></i></a></li>
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
