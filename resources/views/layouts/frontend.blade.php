<!DOCTYPE html>
<html lang="zxx">
<head>
	<meta charset="utf-8" />
	<meta name="author" content="Lomoofy Industries" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<title>@yield('title', 'Lomoofy Industries')</title>
	<link	favicon href="{{ asset('frontend/images/logo.png') }}" rel="icon">
	 
	<!-- Custom CSS -->
	<link href="{{ asset('frontend/css/styles.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('frontend/css/customresponsive.css') }}" rel="stylesheet">
	
	@stack('styles')
</head>

<body>

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
	
	@stack('scripts')
	@yield('scripts')

</body>
</html>

