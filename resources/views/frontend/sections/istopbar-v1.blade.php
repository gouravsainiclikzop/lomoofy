<div class="py-2 bg-dark" id="istopbar-v1">
	<div class="container">
		<div class="row">
		
		<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 hide-ipad">
				<div class="top_first">
						<a href="tel:{{ $settings->phone }}" class="medium text-light">
								{{ $settings->phone }}
						</a>
				</div>
		</div>

			
			<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 hide-ipad">
				<div class="top_second text-center"><p class="medium text-light m-0 p-0">Get Free delivery from ₹2000 <a href="{{ route('frontend.shop') }}" class="medium text-light text-underline">Shop Now</a></p></div>
			</div>
			
			<!-- Right Menu -->
			<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12"> 
			
			<div class="language-selector-wrapper dropdown js-dropdown float-right me-3">
				<a class="popup-title" href="javascript:void(0)" data-bs-toggle="dropdown" title="Language" aria-label="Language dropdown">
					<span class="hidden-xl-down medium text-light">Language:</span>
					<span class="iso_code medium text-light" id="current-language">English</span>
					<i class="fa fa-angle-down medium text-light"></i>
				</a>
				<ul class="dropdown-menu popup-content link">
					<li class="current" data-lang="en"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="en"><img src="{{ asset('frontend/images/1.jpg') }}" alt="en" width="16" height="11" /><span>English</span></a></li>
					<li data-lang="fr"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="fr"><img src="{{ asset('frontend/images/2.jpg') }}" alt="fr" width="16" height="11" /><span>Français</span></a></li>
					<li data-lang="de"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="de"><img src="{{ asset('frontend/images/3.jpg') }}" alt="de" width="16" height="11" /><span>Deutsch</span></a></li>
					<li data-lang="it"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="it"><img src="{{ asset('frontend/images/4.jpg') }}" alt="it" width="16" height="11" /><span>Italiano</span></a></li>
					<li data-lang="es"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="es"><img src="{{ asset('frontend/images/5.jpg') }}" alt="es" width="16" height="11" /><span>Español</span></a></li>
					<li data-lang="ar"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="ar"><img src="{{ asset('frontend/images/6.jpg') }}" alt="ar" width="16" height="11" /><span>اللغة العربية</span></a></li>
					<li data-lang="hi"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="hi"><img src="{{ asset('frontend/images/7.jpg') }}" alt="hi" width="16" height="11" /><span>हिन्दी</span></a></li>
					<li data-lang="pa"><a href="javascript:void(0);" class="dropdown-item medium text-medium language-option" data-lang-code="pa"><img src="{{ asset('frontend/images/8.jpg') }}" alt="pa" width="16" height="11" /><span>ਪੰਜਾਬੀ</span></a></li>
				</ul>
			</div> 
				<div class="currency-selector dropdown js-dropdown float-right me-3">
					<a href="{{ route('frontend.profile-info') }}" class="text-light medium">My Account</a>
				</div> 
			</div>
			
		</div>
	</div>
</div> 