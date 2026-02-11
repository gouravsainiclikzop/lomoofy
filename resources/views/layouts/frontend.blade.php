<!DOCTYPE html>
<html lang="zxx">
<head>
	<meta charset="utf-8" />
	<meta name="author" content="Lomoofy Industries" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
		
	@php 
	 $settings = \App\Models\CompanySetting::getSettings();
 	 $color_theme = $settings->active_color_theme ?? null;
	 $color_themes = $settings->color_themes ?? [];
	 $active_theme_data = null;
	 
	 if ($color_theme && isset($color_themes[$color_theme])) {
		 $active_theme_data = $color_themes[$color_theme];
	 }
	@endphp

	<title>@yield('title', $settings->company_name ?? 'Lomoofy Industries')</title>
	 

	<link rel="icon" type="image/png" href="{{ $settings->company_logo ? asset('storage/' . $settings->company_logo) : asset('assets/images/favicon.png') }}">
	
	<!-- Custom CSS -->
	<link href="{{ asset('frontend/css/styles.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customresponsive.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customresponsive_second.css') }}" rel="stylesheet">
	
	@stack('styles')
</head>
<body>

<!-- <div id="page-loader">
    <img src="{{ asset('shirt-shoe.gif') }}" alt="Loading..." class="page-loader-img"> 
</div> -->

<div id="page-loader">
    <div class="page-loader"></div>
</div>

<style>
:root {
    --secondary-color: {{ $settings->primary_color ?? '#f4f5f7' }};
    @if($active_theme_data)
        /* Color Theme: {{ $color_theme }} */
        --theme-bg-primary: {{ $active_theme_data['backgrounds'][0] ?? '#FFFFFF' }};
        --theme-bg-secondary: {{ $active_theme_data['backgrounds'][1] ?? '#F9FAFB' }};
        --theme-bg-tertiary: {{ $active_theme_data['backgrounds'][2] ?? '#F3F4F6' }};
        --theme-text-primary: {{ $active_theme_data['text'][0] ?? '#111827' }};
        --theme-text-secondary: {{ $active_theme_data['text'][0] ?? '#1F2937' }};
        --theme-text-muted: {{ $active_theme_data['muted_text'][0] ?? '#6B7280' }};
        --theme-text-muted-secondary: {{ $active_theme_data['muted_text'][0] ?? '#9CA3AF' }};
        --theme-anchor: {{ $active_theme_data['anchors'][0] ?? '#111827' }};
        --theme-anchor-hover: {{ $active_theme_data['hover'][0] ?? '#DC2626' }};
        --theme-span: {{ $active_theme_data['span'][0] ?? '#DC2626' }};
        @if(isset($active_theme_data['borders']) && !empty($active_theme_data['borders']))
        --theme-border: {{ $active_theme_data['borders'][0] ?? '#E5E7EB' }};
        @else
        --theme-border: #E5E7EB;
        @endif
    @endif
}
</style>

 
<style>
	#page-loader {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;

  background-color: color-mix(
    in srgb,
    var(--theme-bg-primary, #ffffff) 90%,
    transparent
  );

  z-index: 9999;
}

.page-loader {
  width: 60px;
  aspect-ratio: 1;
  display: flex;
  position: relative;

  /* Text / neutral blocks */
  --c1: linear-gradient(
    var(--theme-text-primary, #111827) 0 0
  );

  /* Accent / brand blocks */
  --c2: linear-gradient(
    var(--theme-span, #4f46e5) 0 0
  );

  --s: calc(100% / 3) calc(100% / 3);

  background:
    var(--c1) 0   0 ,var(--c2) 50% 0   ,var(--c1) 100% 0,
    var(--c2) 0  50%,                   var(--c2) 100% 50%,
    var(--c1) 0 100%,var(--c2) 50% 100%,var(--c1) 100% 100%;

  background-repeat: no-repeat;
  animation: l8-0 1.5s infinite alternate;
}

.page-loader::before {
  content: "";
  position: absolute;
  width: calc(100% / 3);
  height: calc(100% / 3);

  background: var(
    --theme-anchor-hover,
    var(--theme-span, #4f46e5)
  );

  animation: l8-1 1.5s infinite alternate;
}



@keyframes l8-0 {
    0%,12.49%   {background-size: var(--s),0 0     ,0 0     ,0 0     ,0 0     ,0 0     ,0 0     ,0 0     }
    12.5%,24.9% {background-size: var(--s),var(--s),0 0     ,0 0     ,0 0     ,0 0     ,0 0     ,0 0     }
    25%,37.4%   {background-size: var(--s),var(--s),var(--s),0 0     ,0 0     ,0 0     ,0 0     ,0 0     }
    37.5%,49.9% {background-size: var(--s),var(--s),var(--s),0 0     ,var(--s),0 0     ,0 0     ,0 0     }
    50%,61.4%   {background-size: var(--s),var(--s),var(--s),0 0     ,var(--s),0 0     ,0 0     ,var(--s)}
    62.5%,74.9% {background-size: var(--s),var(--s),var(--s),0 0     ,var(--s),0 0     ,var(--s),var(--s)}
    75%,86.4%   {background-size: var(--s),var(--s),var(--s),0 0     ,var(--s),var(--s),var(--s),var(--s)}
    87.5%,100%  {background-size: var(--s),var(--s),var(--s),var(--s),var(--s),var(--s),var(--s),var(--s)}
}
@keyframes l8-1 {
  0%,
  5%    {transform: translate(0   ,0   )}
  12.5% {transform: translate(100%,0   )}
  25%   {transform: translate(200%,0   )}
  37.5% {transform: translate(200%,100%)}
  50%   {transform: translate(200%,200%)}
  62.5% {transform: translate(100%,200%)}
  75%   {transform: translate(0   ,200%)}
  87.5% {transform: translate(0   ,100%)}
  95%,
  100%  {transform: translate(100%,100%)}
}
</style>


@if($active_theme_data)
<style>
    /* Apply Color Theme Styles */
    body {
        background-color: var(--theme-bg-primary);
				background: var(--theme-bg-secondary) !important;
        color: var(--theme-text-primary);
    }
		.gray {
			background: var(--theme-bg-tertiary) !important;
		}
    
		.header {
			background: var(--theme-bg-secondary) !important;
			z-index: 6;
		}

		#istopbar-v1 {
			background: var(--theme-bg-tertiary) !important;
		}

		@media (min-width: 993px) {
			.header.header-fixed { 
				background: var(--theme-bg-secondary) !important; 
			}
		}


		@media (min-width: 993px) {
			.nav-menu > .active > a, .nav-menu > .focus > a, .nav-menu > li:hover > a {
				color: var(--theme-anchor-hover) !important;
			}
		}
		.nav-menu > li > a { 
			color: var(--theme-text-primary) !important;
		}

		/* // topbar bg  */
		.product-hover-overlay.bg-dark.d-flex.align-items-center.justify-content-center {
		background: var(--theme-bg-secondary) !important;
		}
		.text-light {
			color: var(--theme-anchor-hover) !important;
		}

		.dn-counter {
			background-color: var(--theme-bg-primary) !important;
			color: var(--theme-text-secondary) !important; 
			box-shadow: 0px 0px 3px  var(--theme-text-secondary) !important;
		} 

    a {
        color: var(--theme-anchor) !important;
    }
     

    a:hover, a:focus {
        color: var(--theme-anchor-hover);
    }
    
		.text-white.fs-sm.ft-medium.quick-view-btn {
			color: var(--theme-anchor) !important;
		}
		
		.text-white.fs-sm.ft-medium.quick-view-btn:hover {
			color: var(--theme-text-secondary) !important;
		}



    .text-muted, .muted-text {
        color: var(--theme-text-muted) !important;
    }
    
    .bg-light, .bg-secondary {
        background-color: var(--theme-bg-secondary) !important;
    }
    
    .border, .border-top, .border-bottom, .border-left, .border-right {
        border-color: var(--theme-border) !important;
    }
    
    .theme-bg {
        background-color: var(--theme-span) !important;
    }
    
    .theme-cl {
        color: var(--theme-span) !important;
    }
    
    span.price, .price, .highlight, .span-color {
        color: var(--theme-span) !important;
    }

		footer.skin-dark-footer .footer_widget ul li a, footer.skin-dark-footer, footer.skin-dark-footer a {
			color: var(--theme-bg-secondary) !important;
			background-color: var(--theme-text-secondary) !important;
		}

		.skin-dark-footer .footer-bottom { 
			color: var(--theme-text-secondary) !important;
			background-color: var(--theme-bg-tertiary) !important;
		} 
			
		footer.skin-dark-footer h4, footer.skin-blue-footer h4 {
					color: var(--theme-bg-tertiary) !important;
					background-color: var(--theme-text-secondary) !important;
		}
		
		#back2Top {
			background-color: var(--theme-bg-primary) !important;
			color: var(--theme-text-primary) !important;
	}

		#back2Top:hover {
			background-color: var(--theme-bg-primary) !important;
			color: var(--theme-text-primary) !important;
	}

		.skin-dark-footer .foot-news-last .form-control {
			border-color: var(--theme-bg-primary) !important;
			background: var(--theme-bg-secondary) !important;
			color: var(--theme-text-primary) !important;
	}

	.skin-dark-footer .foot-news-last .input-group-text {
				border-color: var(--theme-bg-primary) !important;
				background: var(--theme-bg-secondary) !important;
				color: var(--theme-text-primary) !important;
	}

	/* Placeholder color */
.skin-dark-footer .foot-news-last .form-control::placeholder {
  color: var(--theme-text-muted) !important;
  opacity: 1; /* Firefox thinks fading is a good idea */
}

/* Legacy browsers that refuse to retire */
.skin-dark-footer .foot-news-last .form-control::-webkit-input-placeholder {
  color: var(--theme-text-muted) !important;
}

.skin-dark-footer .foot-news-last .form-control::-moz-placeholder {
  color: var(--theme-text-muted) !important;
}

.skin-dark-footer .foot-news-last .form-control:-ms-input-placeholder {
  color: var(--theme-text-muted) !important;
}

		.address  > .fs-sm.text-light { 
				color: var(--theme-bg-secondary) !important;
		} 
		/* testimonial author */
		.rev_author > h4 {
			color:var(--theme-anchor) !important;
		}

	/* nvabar  dropdowns start */
	.nav-dropdown > li > a { 
		color: var(--theme-text-primary) !important; 
	} 
	.mega-menu-title > a {
		color: var(--theme-text-primary) !important;
	}
	.mega-menu-list li a { 
		color: var(--theme-text-secondary) !important;
	}
	/* nvabar  dropdowns ends */
	.top_first > .medium.text-light {
		color: var(--theme-text-secondary) !important;
	}

	.top_second  > .medium.text-light {
		color: var(--theme-text-secondary) !important;
	}


	.currency-selector > .text-light {
		color: var(--theme-text-secondary) !important;
	}

	.popup-title > .text-light {
		color: var(--theme-text-secondary) !important;
	}
	
	.sec_title  > .ft-bold   { 
		color: var(--theme-text-secondary) !important; 
	}
	._blog_caption > .text-dark{
		color: var(--theme-text-secondary) !important;  
	}
	.btn-dark { 
		/* background-color: #151515;
		border-color: #151515; */
		color: var(--theme-bg-primary)   !important;
	}

	.btn.btn_love {
		background:  var(--theme-bg-tertiary) !important;
	}

	#wishlistProductsContainer .card-footers.bg-white {
			background-color: var(--theme-bg-tertiary) !important;
	} 

	.checkout-section {
	background: var(--theme-bg-tertiary) !important; 
	border: 1px solid var(--theme-border) !important;
}


/* ===============================
   QUICK VIEW MODAL THEME STYLES
   Scope: .modal
   =============================== */

.modal {
    color: var(--theme-text-primary);
}

/* Modal shell */
.modal .modal-content {
    background-color: var(--theme-bg-secondary);
    border: 1px solid var(--theme-border);
}

/* Header / close button */
/* .modal .modal-headers {
    border-bottom: 1px solid var(--theme-border);
} */

.modal .modal-headers .close {
    color: var(--theme-bg-primary) !important;
}

.modal .modal-headers .close:hover {
    color: var(--theme-anchor-hover) !important;
}

/* Left image area */
.modal .quick_view_thmb {
    background: var(--theme-bg-secondary);
}

/* Product category pill */
.modal #quickViewCategory span {
    background: var(--theme-bg-tertiary) !important;
    color: var(--theme-anchor) !important;
		border: 1px solid var(--theme-anchor) !important;
}

/* Product title */
.modal #quickViewTitle {
    color: var(--theme-text-primary);
}

/* Price block */
.modal .final-price,
.modal .final-price-compact {
    color: var(--theme-span) !important;
}

.modal .base-price,
.modal .base-price-compact {
    color: var(--theme-text-muted);
}

/* Discount badge */
.modal .badge.bg-danger {
    background-color: var(--theme-anchor-hover) !important;
}

/* Muted meta text */
.modal .text-muted,
.modal .tax-label {
    color: var(--theme-text-muted) !important;
}

/* Borders inside modal */
.modal .border-top,
.modal .border-bottom,
.modal .border {
    border-color: var(--theme-border) !important;
}

/* Attribute labels (Color, Size, etc.) */
.modal .prt_04 p {
    color: var(--theme-text-secondary);
}

/* Size options */
.modal .form-option-label {
    color: var(--theme-text-primary);
    border-color: var(--theme-border);
}

.modal .form-check-input:checked + .form-option-label {
    background: var(--theme-bg-tertiary);
    border-color: var(--theme-anchor-hover);
}

/* Quantity select */
.modal select {
    background: var(--theme-bg-secondary);
    color: var(--theme-text-primary);
    border: 1px solid var(--theme-text-primary);
}

/* Add to cart button */
.modal #quickViewAddToCart {
    background: var(--theme-anchor-hover);
    color: var(--theme-bg-primary);
    border: none;
}

/* Wishlist button */
.modal #quickViewWishlist {
    background: var(--theme-bg-tertiary);
    color: var(--theme-text-primary);
    border: 1px solid var(--theme-text-primary);
}

/* Share icons */
.modal .gray.circle {
    background: var(--theme-bg-tertiary);
    color: var(--theme-text-muted);
}

.modal .gray.circle:hover {
    color: var(--theme-anchor-hover);
}

.modal .modal-headers .close {
	background: var(--theme-anchor-hover);
	/* color: var(--theme-bg-primary); */
}
 
#isfeaturedcategory-v6  .text-light {
	color: var(--theme-bg-secondary) !important;
} 

#cartItemsContainer .alert-info {
	color: var(--theme-text-secondary) !important;
	background-color: var(--theme-bg-tertiary) !important;
	border-color: var(--theme-bg-secondary) !important;
}

#isfeaturedcategory-v4 .text-light {
	color: var(--theme-bg-primary) !important;
}

</style>
@endif

@if(!empty($settings->font_family))
<style>
body {
    font-family: {!! htmlspecialchars_decode($settings->font_family) !!} !important;
}
 
</style>
@endif




	<!-- ============================================================== -->
	<!-- Preloader - style you can find in spinners.css -->
	<!-- ============================================================== -->
	<div class="preloader"></div>
	
	<!-- ============================================================== -->
	<!-- Main wrapper - style you can find in pages.scss -->
	<!-- ============================================================== -->
	<div id="main-wrapper"> 

		@include('frontend.partials.header')
		
		@yield('breadcrumbs')
		
		@yield('content')
		
		@include('frontend.partials.footer')
		
		@include('frontend.partials.modals')
		
		<a id="back2Top" class="top-scroll" title="Back to top" href="#"><i class="ti-arrow-up"></i></a>

	</div>
	
	<!-- ============================================================== -->
	<!-- End Wrapper -->
	<!-- ============================================================== -->
 
	<script src="{{ asset('frontend/js/jquery.min.js') }}"></script>
	<script src="{{ asset('frontend/js/popper.min.js') }}"></script>
	<script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('frontend/js/ion.rangeSlider.min.js') }}"></script>
	<script src="{{ asset('frontend/js/slick.js') }}"></script>
	<script src="{{ asset('frontend/js/slider-bg.js') }}"></script>
	<script src="{{ asset('frontend/js/lightbox.js') }}"></script> 
	<script src="{{ asset('frontend/js/smoothproducts.js') }}"></script>
	<script src="{{ asset('frontend/js/snackbar.min.js') }}"></script>
	<script src="{{ asset('frontend/js/jQuery.style.switcher.js') }}"></script>
	<script src="{{ asset('frontend/js/custom.js') }}"></script>
	
	<!-- ============================================================== -->
	<!-- This page plugins -->
	<!-- ============================================================== -->	

	<script>
		function openWishlist() {
			document.getElementById("Wishlist").style.display = "block";
		}
		function closeWishlist() {
			document.getElementById("Wishlist").style.display = "none";
		}
	</script>
	
	<script>
		function openCart() {
			document.getElementById("Cart").style.display = "block";
		}
		function closeCart() {
			document.getElementById("Cart").style.display = "none";
		}
	</script>

	
	<script>
		// Fix for Bootstrap modal initialization - ensure modals work properly
		document.addEventListener('DOMContentLoaded', function() {
			// Fix Quick View modal initialization issue
			var quickViewModal = document.getElementById('quickview');
			if (quickViewModal) {
				// Remove any existing Bootstrap instance if present
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					// Get or create modal instance
					var modalInstance = bootstrap.Modal.getOrCreateInstance(quickViewModal, {
						backdrop: true,
						keyboard: true,
						focus: true
					});
				}
			}
		});
	</script>
	

	<script>
  window.addEventListener('load', function () {
    const loader = document.getElementById('page-loader');
    if (loader) {
      loader.style.opacity = '0';
      loader.style.transition = 'opacity .25s ease';
      setTimeout(() => loader.remove(), 250);
    }
  });
</script>

	@stack('scripts')
	@yield('scripts')

</body>
</html>

