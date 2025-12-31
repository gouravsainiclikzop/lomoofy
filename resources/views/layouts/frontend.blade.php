<!DOCTYPE html>
<html lang="zxx">
<head>
	<meta charset="utf-8" />
	<meta name="author" content="Lomoofy Industries" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
		
	@php 
	 $settings = \App\Models\CompanySetting::getSettings();
 
		@endphp

	<title>@yield('title', $settings->company_name ?? 'Lomoofy Industries')</title>
	 

	<link rel="icon" type="image/png" href="{{ $settings->company_logo ? asset('storage/' . $settings->company_logo) : asset('assets/images/favicon.png') }}">
	
	<!-- Custom CSS -->
	<link href="{{ asset('frontend/css/styles.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customresponsive.css') }}" rel="stylesheet">
	
	@stack('styles')
</head>

<body>
<div id="page-loader">
    <div class="loader"></div>
</div>


<style>
	#page-loader {
  position: fixed;
  inset: 0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* From Uiverse.io by alexruix */
.loader {
  width: 48px;
  height: 48px;
  position: relative;
}

.loader:before {
  content: '';
  width: 48px;
  height: 5px;
  background: #f0808050;
  position: absolute;
  top: 60px;
  left: 0;
  border-radius: 50%;
  animation: shadow324 0.5s linear infinite;
}

.loader:after {
  content: '';
  width: 100%;
  height: 100%;
  background: #a7a7a7;
  position: absolute;
  top: 0;
  left: 0;
  border-radius: 4px;
  animation: jump7456 0.5s linear infinite;
}

@keyframes jump7456 {
  15% { border-bottom-right-radius: 3px; }
  25% { transform: translateY(9px) rotate(22.5deg); }
  50% { transform: translateY(18px) scale(1, .9) rotate(45deg); border-bottom-right-radius: 40px; }
  75% { transform: translateY(9px) rotate(67.5deg); }
  100% { transform: translateY(0) rotate(90deg); }
}

@keyframes shadow324 {
  0%,100% { transform: scale(1, 1); }
  50% { transform: scale(1.2, 1); }
}

	
</style>

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

	<!-- ============================================================== -->
	<!-- All Jquery -->
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

