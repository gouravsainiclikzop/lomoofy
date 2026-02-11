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

@php 
		$sections = \App\Models\Section::where('is_active', true)
				->orderBy('sort_order')
				->get();
@endphp 

@foreach($sections as $section)

    @switch($section->section_id)

        @case('istopbar-v1')
            @include('frontend.sections.istopbar-v1')
            @break

        @case('isnavbar-v1')
            @include('frontend.sections.isnavbar-v1')
            @break	

        @case('isnavbar-v2')
            @include('frontend.sections.isnavbar-v2')
            @break

    @endswitch
@endforeach

 
 


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
			 
			const savedLang = localStorage.getItem('selectedLanguage');
			if (savedLang && savedLang !== 'en') { 
				localStorage.removeItem('selectedLanguage');
			}
			 
			setTimeout(function() {
				const select = document.querySelector('.goog-te-combo');
				if (select) { 
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
						updateLanguageUI('en');
					}
				}
			}, 2000);  
		}
	}
 
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLanguageTranslator);
	} else {
		// DOM is already loaded
		initLanguageTranslator();
	}
</script>
 
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
	 
	function updateCustomerAuthUI(isLoggedIn, customerData) {
		if (isLoggedIn) { 
			$('#guestUserIcon').hide();
			$('#customerUserMenu').show(); 
		} else { 
			$('#guestUserIcon').show();
			$('#customerUserMenu').hide();
		}
	}
	 
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
	 
	window.checkAndUpdateCustomerAuth = checkAndUpdateCustomerAuth;
	 
	$('#guestUserIconLink').on('click', function(e) {
		e.preventDefault();
		 
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
					$('#login').modal('show');
				}
			},
			error: function(xhr) { 
				$('#login').modal('show');
			}
		});
	});
	 
	setTimeout(function() {
		checkAndUpdateCustomerAuth();
	}, 500);
	 
	$('#login').on('hidden.bs.modal', function() {
		setTimeout(function() {
			checkAndUpdateCustomerAuth();
		}, 500);
	});
	 
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
				updateCustomerAuthUI(false);
				window.location.href = '{{ route("frontend.index") }}';
			}
		});
	}
	
	// Attach logout handler to all logout buttons
	$('#customerLogoutBtn, #customerLogoutBtn2, #customerLogoutBtn3').on('click', handleCustomerLogout);
	 
	function updateCartLinks(isLoggedIn) {
		const $cartLinks = $('a[href*="shoping-cart"]');
		$cartLinks.each(function() {
			const $link = $(this);
			let href = $link.attr('href');
			
			if (isLoggedIn) { 
				if (href.includes('session_id=')) {
					const url = new URL(href, window.location.origin);
					url.searchParams.delete('session_id');
					href = url.pathname + (url.search ? url.search : '');
					$link.attr('href', href);
				}
			} else { 
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

		}); 
	}  
	 
	initCustomerAuth();
})(); 

// Search Sidebar Functions
function openSearch() {
	var searchSidebar = document.getElementById('Search');
	if (searchSidebar) {
		searchSidebar.style.display = 'block';
		searchSidebar.style.right = '0px'; 
		var overlay = document.createElement('div');
		overlay.id = 'searchOverlay';
		overlay.className = 'search-overlay';
		overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;';
		overlay.onclick = closeSearch;
		document.body.appendChild(overlay);
		document.body.style.overflow = 'hidden';
	}
}

function closeSearch() {
	var searchSidebar = document.getElementById('Search');
	if (searchSidebar) {
		searchSidebar.style.display = 'none';
		searchSidebar.style.right = '-320px';
		// Remove overlay
		var overlay = document.getElementById('searchOverlay');
		if (overlay) {
			overlay.remove();
		}
		document.body.style.overflow = '';
	}
}

// Close search on ESC key
document.addEventListener('keydown', function(e) {
	if (e.key === 'Escape') {
		closeSearch();
	}
});
</script>
@endpush

