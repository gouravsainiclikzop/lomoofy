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
						<img src="{{ asset('frontend/images/logo.png') }}" class="img-footer small mb-2" alt="" />
						
						<div class="address mt-3">
							3298 Grant Street Longview, TX<br>United Kingdom 75601	
						</div>
						<div class="address mt-3">
							1-202-555-0106<br>help@lomoofyindustries.com
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
							<li><a href="{{ route('frontend.shop') }}">Men</a></li>
							<li><a href="{{ route('frontend.shop') }}">Women</a></li>
							<li><a href="{{ route('frontend.shop') }}">Kids</a></li>  
						</ul>
					</div>
				</div>
			
				<div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">Userfull Links</h4>
						<ul class="footer-menu">
							<li><a href="{{ route('frontend.privacy') }}">Terms & Conditions</a></li>
							<li><a href="#">Shipping </a></li>
							<li><a href="#">Cancellation & Refund</a></li> 
							<li><a href="#">Return & Refund Policy</a></li> 
							<li><a href="#">Privacy Policy</a></li> 
							<li><a href="#">Disclaimer</a></li>   
						</ul>
						
					</div>
				</div>

				<div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">&nbsp;</h4>
						<ul class="footer-menu"> 
							<li><a href="{{ route('frontend.contact') }}">Contact</a></li>
							<li><a href="{{ route('frontend.faq') }}">FAQs</a></li>
							<li><a href="#">Blog</a></li>
							<li><a href="{{ route('frontend.about-us') }}">About</a></li>
							<li><a href="#">Careers</a></li>
							<li><a href="{{ route('frontend.privacy') }}">Privacy Policy</a></li>
						</ul>
						
					</div>
				</div>
				
				<div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
					<div class="footer_widget">
						<h4 class="widget_title">Subscribe</h4>
						<p>Receive updates, hot deals, discounts sent straignt in your inbox daily</p>
						<div class="foot-news-last">
							<div class="input-group">
							  <input type="text" class="form-control" placeholder="Email Address">
								<div class="input-group-append">
									<button type="button" class="input-group-text rounded-0 text-light"><i class="lni lni-arrow-right"></i></button>
								</div>
							</div>
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
					<p class="mb-0">© {{ date('Y') }} Lomoofy Industries All Rights Reserved.</p>
				</div>
			</div>
		</div>
	</div>
</footer>
<!-- ============================ Footer End ================================== -->

