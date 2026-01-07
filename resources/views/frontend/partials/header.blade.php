<!-- ============================================================== -->
<!-- Top header  -->
<!-- ============================================================== -->
<style>
	/* Hide Google Translate Banner */
	.goog-te-banner-frame,
	.goog-te-banner,
	#google_translate_element .goog-te-banner-frame,
	#google_translate_element .goog-te-banner,
	.skiptranslate {
		display: none !important;
	}
	
	/* Fix body top margin when banner is hidden */
	body {
		top: 0 !important;
	}
	
	/* Hide the Google Translate top bar */
	.goog-te-banner-frame.skiptranslate {
		display: none !important;
	}
	
	/* Ensure page content is not pushed down */
	body.goog-te-banner-frame {
		top: 0 !important;
	}
	
	/* Active/Current Language Background Color */
	.language-selector-wrapper .dropdown-menu li.current,
	.language-selector-wrapper .dropdown-menu li.current a {
		background-color: #f8f9fa !important;
	}
	
	.language-selector-wrapper .dropdown-menu li.current a {
		font-weight: 600;
		color: #007bff !important;
	}
	
	.language-selector-wrapper .dropdown-menu li:hover:not(.current) {
		background-color: #e9ecef;
	}
	
	.language-selector-wrapper .dropdown-menu li:hover:not(.current) a {
		color: #495057;
	}
</style>
<div class="py-2 bg-dark">
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

<!-- Start Navigation -->
<div class="header header-light dark-text">
	<div class="container">
		<nav id="navigation" class="navigation navigation-landscape">
			<div class="nav-header">
				<a class="nav-brand" href="{{ route('frontend.index') }}">
					<img src="{{ asset('frontend/images/logo.png') }}" class="logo" alt="" />
				</a>
				<div class="nav-toggle"></div>
				<div class="mobile_nav">
					<ul>
						<li class="search-input-wrapper">
							<div class="header-search-box">
								<form action="{{ route('frontend.shop') }}" method="GET" class="search-form">
									<input type="text" name="search" class="form-control search-input" placeholder="Search products..." value="{{ request('search') }}">
									<button type="submit" class="search-btn">
										<i class="lni lni-search-alt"></i>
									</button>
								</form>
							</div>
						</li> 
				 
				  	@if(!Auth::guard('customer')->check())
							<li>
								<a href="#" data-bs-toggle="modal" data-bs-target="#login"> 
									<i class="lni lni-user"></i>
								</a>
							</li> 
						@else
							<li>
								<a href="{{ route('frontend.profile-info') }}"> 
									<i class="lni lni-user" style="color: #1db000;"></i>
								</a>
							</li>
						@endif 

						<li>
							<a href="{{ route('frontend.wishlist') }}" class="wishlist-link">
								<i class="lni lni-heart"></i><span class="dn-counter">0</span>
							</a>
						</li>
						<li>
							<a href="{{ route('frontend.shoping-cart') }}">
								<i class="lni lni-shopping-basket"></i><span class="dn-counter">0</span>
							</a>
						</li> 
					</ul>
				</div>
			</div>

					@php 
						// Get parent categories (level 0) with their children (level 1) and grandchildren (level 2)
						// Limit to maximum 4 parent categories
						$parentCategories = App\Models\Category::whereNull('parent_id')
							->where(function($q) {
								$q->where('is_active', true)->orWhereNull('is_active');
							})
							->with(['children' => function($query) {
								$query->where(function($q) {
									$q->where('is_active', true)->orWhereNull('is_active');
								})
								->orderBy('sort_order')
								->with(['children' => function($q) {
									$q->where(function($query) {
										$query->where('is_active', true)->orWhereNull('is_active');
									})
									->orderBy('sort_order');
								}]);
							}])
							->orderBy('sort_order')
							->limit(4)
							->get();
					@endphp

			<div class="nav-menus-wrapper" style="transition-property: none;">
				<ul class="nav-menu">   
			
				<!-- mobile menu starts here -->
				<!-- mobile menu starts here -->
				<!-- mobile menu starts here -->
				<!-- mobile menu starts here -->
				<li class="main-menu-item">
				  <a href="javascript:void(0);">Lomoofy <span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
					<ul class="nav-dropdown nav-submenu" style="right: auto; display: none;">
						<li class=""><a href="{{ route('frontend.about-us') }}">About Us</a></li>
						<li class=""><a href="{{ route('frontend.contact') }}">Contact Us</a></li> 
					</ul>
				</li>  
				
@foreach($parentCategories as $parent)
    @php
        $hasChildren = $parent->children && $parent->children->count();
    @endphp

    <li class="main-menu-item">
        <a href="{{ route('frontend.shop') }}?category={{ $parent->slug }}">
            {{ $parent->name }}
            @if($hasChildren)
                <span class="submenu-indicator">
                    <span class="submenu-indicator-chevron"></span>
                </span>
            @endif
        </a>

        @if($hasChildren)
            <ul class="nav-dropdown nav-submenu" style="display:none; right:auto;">

                @foreach($parent->children as $child)
                    @php
                        $hasGrand = $child->children && $child->children->count();
                    @endphp

                    <li>
                        <a href="javascript:void(0);">
                            {{ $child->name }}
                            @if($hasGrand)
                                <span class="submenu-indicator">
                                    <span class="submenu-indicator-chevron"></span>
                                </span>
                            @endif
                        </a>

                        <ul class="nav-dropdown nav-submenu" style="display:none;">
                            @if($hasGrand)
                                @foreach($child->children as $grand)
                                    <li>
                                        <a href="{{ route('frontend.shop') }}?category={{ $grand->slug }}">
                                            {{ $grand->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a href="{{ route('frontend.shop') }}?category={{ $child->slug }}">
                                        View All
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                @endforeach

            </ul>
        @endif
    </li> 
@endforeach
  
								
					<li class="main-menu-item"><a href="{{ route('frontend.profile-info') }}"> <i class="lni lni-user"></i> {{ ucfirst(Auth::guard('customer')->user()->full_name ?? "Guest User") }}</a></li>
							 
					@if(Auth::guard('customer')->check())
					<li class="main-menu-item"><a href="{{ route('frontend.my-orders') }}"> <i class="lni lni-dashboard"></i> My Orders </a></li>
					<li class="main-menu-item"><a href="{{ route('frontend.wishlist') }}" class="wishlist-link"> <i class="lni lni-heart"></i> Wishlist</a></li>
					<li class="main-menu-item"><a href="{{ route('frontend.addresses') }}"> <i class="lni lni-map-marker"></i> Addresses</a></li>
				
					<li class="main-menu-item">
						<a href="#" id="customerLogoutBtn3"> 
							<i class="lni lni-power-switch"></i> Log Out
						</a>
					</li>   
					@endif


				<!-- mobile menu ends here -->
				
				<!-- desktop menu starts here -->
				<!-- desktop menu starts here -->
				<!-- desktop menu starts here -->
				<!-- desktop menu starts here -->
				<li class="mega-menu-item">
				  <a href="javascript:void(0);">Lomoofy <span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
					<ul class="nav-dropdown nav-submenu" style="right: auto; display: none;">
						<li class=""><a href="{{ route('frontend.about-us') }}">About Us</a></li>
						<li class=""><a href="{{ route('frontend.contact') }}">Contact Us</a></li> 
					</ul>
				</li>  
					@foreach($parentCategories as $parentCategory)
						@php
							$hasChildren = $parentCategory->children && $parentCategory->children->count() > 0;
						@endphp
						<li class="mega-menu-item">
							<a href="{{ route('frontend.shop') }}?category={{ $parentCategory->slug }}">
								{{ $parentCategory->name }}
								@if($hasChildren)
									<span class="submenu-indicator">
										<span class="submenu-indicator-chevron"></span>
									</span>
								@endif
							</a>
							@if($hasChildren)
								<!-- Mega Menu Panel -->
								<div class="mega-menu-panel">
									<div class="mega-menu-container">
										<div class="mega-menu-row">
											@php
												// Group level 1 children into columns (max 5 columns)
												$children = $parentCategory->children;
												$columnCount = min(5, max(1, $children->count()));
												$itemsPerColumn = ceil($children->count() / $columnCount);
												$columns = $children->chunk($itemsPerColumn);
											@endphp
											
											@foreach($columns as $columnIndex => $columnCategories)
												<div class="mega-menu-column">
													@foreach($columnCategories as $childCategory)
														@php
															$hasGrandchildren = $childCategory->children && $childCategory->children->count() > 0;
														@endphp
														<div class="mega-menu-title">
															<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}">
																{{ $childCategory->name }}
															</a>
														</div>
														<ul class="mega-menu-list">
															@if($hasGrandchildren)
																@foreach($childCategory->children as $grandchildCategory)
																	<li>
																		<a href="{{ route('frontend.shop') }}?category={{ $grandchildCategory->slug }}">
																			{{ $grandchildCategory->name }}
																		</a>
																	</li>
																@endforeach
															@else
																<li>
																	<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}">
																		View All
																	</a>
																</li>
															@endif
														</ul>
														@if(!$loop->last && $columnCategories->count() > 1)
															<div style="margin-top: 25px;"></div>
														@endif
													@endforeach
												</div>
											@endforeach
										</div>
									</div>
								</div>
							@endif
						</li>
					@endforeach  
				</ul>
				
				<ul class="nav-menu nav-menu-social align-to-right">
					<li class="search-input-wrapper">
						<div class="header-search-box">
							<form action="{{ route('frontend.shop') }}" method="GET" class="search-form">
								<input type="text" name="search" class="form-control search-input" placeholder="Search products..." value="{{ request('search') }}">
								<button type="submit" class="search-btn">
									<i class="lni lni-search-alt"></i>
								</button>
							</form>
						</div>
					</li> 
					<!-- Customer Auth: Checked via JavaScript (session-based) -->
					<!-- Guest User Icon (shown by default, hidden when customer logged in) --> 
					<li id="guestUserIcon" class="customer-auth-element">
						<a href="#" id="guestUserIconLink">
							<i class="lni lni-user"></i>
						</a>
					</li>
				 
					<!-- Logged In Customer Menu (hidden by default, shown when customer logged in) -->
					<li id="customerUserMenu" class="has-submenu customer-auth-element" style="display: none;">
						<a href="javascript:void(0);">
							<i class="lni lni-user lnis-user-4" style="color: #1db000"></i>
							<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span>
						</a>
						<ul class="nav-dropdown nav-submenu">
						<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i> {{ ucfirst(Auth::guard('customer')->user()->full_name ?? "Guest User") }}</a></li>
						 
							<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
								<li><a href="{{ route('frontend.wishlist') }}" class="wishlist-link"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
								<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Saved Addresses</a></li>
								<!-- <li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li> -->
								<li><a href="#" id="customerLogoutBtn"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
							</ul> 
					</li>
					<!-- wishlist icon desktop -->
					<li>
						<a href="{{ route('frontend.wishlist') }}" class="wishlist-link">
							<i class="lni lni-heart"></i><span class="dn-counter"></span>
						</a>
					</li>
					<li>
						<a href="{{ route('frontend.shoping-cart') }}">
							<i class="lni lni-shopping-basket"></i><span class="dn-counter"></span>
						</a>
					</li> 
				</ul>
			</div>
		</nav>
	</div>
</div>
<!-- End Navigation -->
<div class="clearfix"></div>


@push('scripts')
<!-- Google Translate Script -->
<script type="text/javascript">
	// Language mapping for Google Translate
	const languageMap = {
		'en': '',
		'fr': 'fr',
		'de': 'de',
		'it': 'it',
		'es': 'es',
		'ar': 'ar',
		'hi': 'hi',
		'pa': 'pa'
	};

	const languageNames = {
		'en': 'English',
		'fr': 'Français',
		'de': 'Deutsch',
		'it': 'Italiano',
		'es': 'Español',
		'ar': 'اللغة العربية',
		'hi': 'हिन्दी',
		'pa': 'ਪੰਜਾਬੀ'
	};

	let googleTranslateLoaded = false;
	let translateWidget = null;

	// Function to hide Google Translate banner
	function hideGoogleTranslateBanner() {
		// Hide banner elements
		const bannerSelectors = [
			'.goog-te-banner-frame',
			'.goog-te-banner',
			'.skiptranslate',
			'#google_translate_element .goog-te-banner-frame',
			'#google_translate_element .goog-te-banner'
		];
		
		bannerSelectors.forEach(function(selector) {
			const elements = document.querySelectorAll(selector);
			elements.forEach(function(el) {
				el.style.display = 'none';
				el.style.visibility = 'hidden';
				el.style.height = '0';
				el.style.overflow = 'hidden';
			});
		});
		
		// Fix body top margin
		if (document.body) {
			document.body.style.top = '0';
			document.body.classList.remove('goog-te-banner-frame');
		}
	}

	// Initialize Google Translate
	function googleTranslateElementInit() {
		try {
			translateWidget = new google.translate.TranslateElement({
				pageLanguage: 'en',
				includedLanguages: 'en,fr,de,it,es,ar,hi,pa',
				layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
				autoDisplay: false
			}, 'google_translate_element');
			googleTranslateLoaded = true;
			console.log('Google Translate initialized');
			
			// Hide banner immediately after initialization
			setTimeout(hideGoogleTranslateBanner, 100);
			
			// Keep hiding it periodically (in case it reappears)
			setInterval(hideGoogleTranslateBanner, 500);
			
			// Wait for select element to be available
			setTimeout(function() {
				const select = document.querySelector('.goog-te-combo');
				if (select) {
					console.log('Google Translate select element found:', select);
					console.log('Available options:', Array.from(select.options).map(opt => opt.value + ' - ' + opt.text));
				} else {
					console.log('Google Translate select element not found yet');
				}
			}, 1000);
		} catch (e) {
			console.error('Error initializing Google Translate:', e);
		}
	}

	// Function to change language - improved version
	function changeLanguage(langCode) {
		console.log('Changing language to:', langCode);
		
		// Update UI first
		updateLanguageUI(langCode);
		
		// Save preference to localStorage
		localStorage.setItem('selectedLanguage', langCode);
		
		// If English, remove translation and reload
		if (langCode === 'en') {
			// Clear localStorage
			localStorage.removeItem('selectedLanguage');
			
			// Try to reset via select element first
			const select = document.querySelector('.goog-te-combo');
			if (select) {
				// Find English option (usually value is empty or 'en')
				for (let i = 0; i < select.options.length; i++) {
					const optionValue = select.options[i].value;
					if (optionValue === '' || optionValue === 'en' || select.options[i].text.toLowerCase().includes('english')) {
						console.log('Resetting to English via select element');
						select.selectedIndex = i;
						select.value = optionValue;
						select.dispatchEvent(new Event('change', { bubbles: true }));
						
						// Wait a moment then reload to ensure translation is reset
						setTimeout(function() {
							const url = window.location.href.split('#')[0];
							window.location.replace(url);
						}, 300);
						return;
					}
				}
			}
			
			// If select method didn't work or not found, use URL method
			const url = window.location.href.split('#')[0];
			
			// Remove any Google Translate cookies that might persist translation
			document.cookie.split(";").forEach(function(c) { 
				if (c.trim().startsWith('googtrans=')) {
					document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
				}
			});
			
			// Force reload without hash to restore original language
			if (window.location.hash.includes('googtrans')) {
				// Use replace to avoid adding to history
				window.location.replace(url);
			} else {
				// Already no hash, but might still be translated - force reload
				window.location.reload();
			}
			return;
		}
		
		// Try to trigger Google Translate select element
		function tryTriggerSelect() {
			console.log('Attempting to find Google Translate select element...');
			// Try multiple selectors
			const selectors = [
				'.goog-te-combo',
				'select.goog-te-combo',
				'#google_translate_element select',
				'select[id*="goog"]'
			];
			
			for (let selector of selectors) {
				const select = document.querySelector(selector);
				if (select) {
					console.log('Found select element with selector:', selector);
					console.log('Select element:', select);
					console.log('Available options:', Array.from(select.options).map(opt => opt.value + '=' + opt.text));
					
					if (select.options && select.options.length > 0) {
						// Find the option with the language code
						for (let i = 0; i < select.options.length; i++) {
							const optionValue = select.options[i].value;
							if (optionValue === langCode || optionValue.includes(langCode) || optionValue === '') {
								console.log('Found matching option:', optionValue, 'at index', i);
								select.selectedIndex = i;
								select.value = optionValue;
								// Trigger change event
								const changeEvent = new Event('change', { bubbles: true, cancelable: true });
								select.dispatchEvent(changeEvent);
								// Also try input event
								const inputEvent = new Event('input', { bubbles: true, cancelable: true });
								select.dispatchEvent(inputEvent);
								// Try click as well
								select.click();
								console.log('Events dispatched, waiting for translation...');
								return true;
							}
						}
					}
				}
			}
			
			// Try iframes
			const iframes = document.querySelectorAll('iframe');
			for (let iframe of iframes) {
				try {
					if (iframe.contentDocument || iframe.contentWindow) {
						const doc = iframe.contentDocument || iframe.contentWindow.document;
						const select = doc.querySelector('.goog-te-combo');
						if (select) {
							console.log('Found select in iframe');
							for (let i = 0; i < select.options.length; i++) {
								if (select.options[i].value === langCode || select.options[i].value.includes(langCode)) {
									select.selectedIndex = i;
									select.dispatchEvent(new Event('change', { bubbles: true }));
									return true;
								}
							}
						}
					}
				} catch (e) {
					// Cross-origin, skip
				}
			}
			
			return false;
		}
		
		// Try immediately
		if (tryTriggerSelect()) {
			console.log('Successfully triggered translation via select');
			return;
		}
		
		// Wait a bit and try again (give Google Translate time to load)
		setTimeout(function() {
			if (!tryTriggerSelect()) {
				// Use URL hash method - this is the standard Google Translate way
				console.log('Using URL hash method');
				const currentUrl = window.location.href.split('#')[0];
				const langHash = '#googtrans(en|' + langCode + ')';
				
				// Set the hash and reload
				window.location.hash = langHash;
				
				// Force a reload to trigger Google Translate
				setTimeout(function() {
					window.location.reload();
				}, 100);
			}
		}, 800);
	}

	// Update language selector UI
	function updateLanguageUI(langCode) {
		// Update current language display
		const currentLangElement = document.getElementById('current-language');
		if (currentLangElement) {
			currentLangElement.textContent = languageNames[langCode] || 'English';
		}
		
		// Update active state
		document.querySelectorAll('.language-selector-wrapper li').forEach(function(li) {
			li.classList.remove('current');
		});
		const activeLi = document.querySelector(`.language-selector-wrapper li[data-lang="${langCode}"]`);
		if (activeLi) {
			activeLi.classList.add('current');
		}
	}

	// Load Google Translate API
	(function() {
		// Check if script already exists
		if (document.querySelector('script[src*="translate.google.com"]')) {
			console.log('Google Translate script already loaded');
			return;
		}
		
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
		script.onerror = function() {
			console.error('Failed to load Google Translate script');
		};
		document.head.appendChild(script);
	})();

	// Handle language option clicks - use event delegation (works even if DOM already loaded)
	document.addEventListener('click', function(e) {
		const languageOption = e.target.closest('.language-option');
		if (languageOption) {
			e.preventDefault();
			e.stopPropagation();
			
			const langCode = languageOption.getAttribute('data-lang-code');
			console.log('Language option clicked:', langCode);
			
			if (langCode) {
				changeLanguage(langCode);
				
				// Close dropdown (Bootstrap 5)
				const dropdownElement = languageOption.closest('.dropdown');
				if (dropdownElement) {
					const toggleElement = dropdownElement.querySelector('[data-bs-toggle="dropdown"]');
					if (toggleElement && typeof bootstrap !== 'undefined') {
						const dropdown = bootstrap.Dropdown.getInstance(toggleElement);
						if (dropdown) {
							dropdown.hide();
						} else {
							// If no instance exists, manually hide
							dropdownElement.classList.remove('show');
							const menu = dropdownElement.querySelector('.dropdown-menu');
							if (menu) {
								menu.classList.remove('show');
							}
						}
					}
				}
			}
		}
	});

	// Initialize on page load
	function initLanguageTranslator() {
		// Check if there's a language hash in URL
		const hashMatch = window.location.hash.match(/googtrans\(en\|(\w+)\)/);
		if (hashMatch) {
			const langFromUrl = hashMatch[1];
			updateLanguageUI(langFromUrl);
			localStorage.setItem('selectedLanguage', langFromUrl);
		} else {
			// No hash means English (default) - ensure UI shows English
			updateLanguageUI('en');
			
			// Clear any saved non-English preference if URL doesn't have hash
			// This ensures that if user selected English, we don't restore a different language
			const savedLang = localStorage.getItem('selectedLanguage');
			if (savedLang && savedLang !== 'en') {
				// URL shows English but localStorage has different language - clear it
				localStorage.removeItem('selectedLanguage');
			}
			
			// Also check if page is still translated (Google Translate might persist)
			// Wait for Google Translate to load, then check and fix if needed
			setTimeout(function() {
				const select = document.querySelector('.goog-te-combo');
				if (select) {
					// Check if select is set to a non-English language but URL has no hash
					const currentValue = select.value;
					if (currentValue && currentValue !== '' && currentValue !== 'en' && 
					    !window.location.hash.includes('googtrans')) {
						// Page is translated but URL has no hash - reset to English
						console.log('Detected translation without hash, resetting to English');
						for (let i = 0; i < select.options.length; i++) {
							const optionValue = select.options[i].value;
							if (optionValue === '' || optionValue === 'en' || 
							    select.options[i].text.toLowerCase().includes('english')) {
								select.selectedIndex = i;
								select.value = optionValue;
								select.dispatchEvent(new Event('change', { bubbles: true }));
								updateLanguageUI('en');
								break;
							}
						}
					} else if (!currentValue || currentValue === '' || currentValue === 'en') {
						// Already in English, ensure UI matches
						updateLanguageUI('en');
					}
				}
			}, 2000); // Wait 2 seconds for Google Translate to fully load
		}
	}

	// Run initialization when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLanguageTranslator);
	} else {
		// DOM is already loaded
		initLanguageTranslator();
	}
</script>

<!-- Google Translate widget - hidden but accessible -->
<div id="google_translate_element" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;"></div>

<script>
// Wait for jQuery to be available
(function() {
	function initCustomerAuth() {
		if (typeof jQuery === 'undefined') {
			setTimeout(initCustomerAuth, 100);
			return;
		}
		
jQuery(document).ready(function($) {
	// Get CSRF token
	const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
	
	// Update UI based on customer auth status
	function updateCustomerAuthUI(isLoggedIn, customerData) {
		if (isLoggedIn) {
			// Hide guest icon, show customer menu
			$('#guestUserIcon').hide();
			$('#customerUserMenu').show(); 
		} else {
			// Show guest icon, hide customer menu
			$('#guestUserIcon').show();
			$('#customerUserMenu').hide();
		}
	}
	
	// Check customer auth and update UI
	function checkAndUpdateCustomerAuth() {
		$.ajax({
			url: '/api/auth/me',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success && response.data) {
					updateCustomerAuthUI(true, response.data);
				} else {
					updateCustomerAuthUI(false);
				}
			},
			error: function(xhr) {
				updateCustomerAuthUI(false);
			}
		});
	}
	
	// Make function globally accessible
	window.checkAndUpdateCustomerAuth = checkAndUpdateCustomerAuth;
	
	// Handle guest user icon click - check auth before showing modal
	$('#guestUserIconLink').on('click', function(e) {
		e.preventDefault();
		
		// Check if user is actually logged in before showing modal
		$.ajax({
			url: '/api/auth/me',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success && response.data) {
					// User is logged in, update UI and don't show modal
					updateCustomerAuthUI(true, response.data);
				} else {
					// User is not logged in, show login modal
					$('#login').modal('show');
				}
			},
			error: function(xhr) {
				// On error, assume not logged in and show modal
				$('#login').modal('show');
			}
		});
	});
	
	// Run on page load (with delay to ensure DOM is ready)
	setTimeout(function() {
		checkAndUpdateCustomerAuth();
	}, 500);
	
	// Trigger check when modal is closed (in case user just logged in)
	$('#login').on('hidden.bs.modal', function() {
		setTimeout(function() {
			checkAndUpdateCustomerAuth();
		}, 500);
	});
	
	// Handle customer logout - unified handler for all logout buttons
	function handleCustomerLogout(e) {
		if (e) {
			e.preventDefault();
		}
		
		$.ajax({
			url: '/api/auth/logout',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function() {
				updateCustomerAuthUI(false);
				window.location.href = '{{ route("frontend.index") }}';
			},
			error: function() {
				// Even if logout fails, redirect to home
				updateCustomerAuthUI(false);
				window.location.href = '{{ route("frontend.index") }}';
			}
		});
	}
	
	// Attach logout handler to all logout buttons
	$('#customerLogoutBtn, #customerLogoutBtn2, #customerLogoutBtn3').on('click', handleCustomerLogout);
	
	// Update cart links based on auth status
	function updateCartLinks(isLoggedIn) {
		const $cartLinks = $('a[href*="shoping-cart"]');
		$cartLinks.each(function() {
			const $link = $(this);
			let href = $link.attr('href');
			
			if (isLoggedIn) {
				// User is logged in - remove session_id from URL if present
				if (href.includes('session_id=')) {
					const url = new URL(href, window.location.origin);
					url.searchParams.delete('session_id');
					href = url.pathname + (url.search ? url.search : '');
					$link.attr('href', href);
				}
			} else {
				// User is not logged in - ensure session_id is in URL
				if (!href.includes('session_id=')) {
					let sessionId = localStorage.getItem('session_id');
					if (!sessionId) {
						sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
						localStorage.setItem('session_id', sessionId);
					}
					const url = new URL(href, window.location.origin);
					url.searchParams.set('session_id', sessionId);
					href = url.pathname + url.search;
					$link.attr('href', href);
				}
			}
		});
	}
	
	// Update cart links when auth status is checked - override the function
	const originalCheckAuth = checkAndUpdateCustomerAuth;
	window.checkAndUpdateCustomerAuth = function() {
		originalCheckAuth();
		// Also update cart links
		$.ajax({
			url: '/api/auth/me',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				updateCartLinks(response.success && response.data);
			},
			error: function() {
				updateCartLinks(false);
			}
		});
	};
	
	// Update cart links on page load
	setTimeout(function() {
		$.ajax({
			url: '/api/auth/me',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				updateCartLinks(response.success && response.data);
			},
			error: function() {
				updateCartLinks(false);
			}
		});
	}, 100);

		}); // End jQuery ready
	} // End initCustomerAuth
	
	// Start initialization
	initCustomerAuth();
})(); // End IIFE
</script>
@endpush

