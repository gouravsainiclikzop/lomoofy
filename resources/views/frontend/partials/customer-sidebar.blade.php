{{-- Customer Dashboard Sidebar Component --}}
@php
    // Get active route name
    $currentRoute = Route::currentRouteName();
    $activePage = '';
    
    if (str_contains($currentRoute, 'my-orders')) {
        $activePage = 'my-orders';
    } elseif (str_contains($currentRoute, 'wishlist')) {
        $activePage = 'wishlist';
    } elseif (str_contains($currentRoute, 'profile-info')) {
        $activePage = 'profile-info';
    } elseif (str_contains($currentRoute, 'change-password')) {
        $activePage = 'change-password';
    } elseif (str_contains($currentRoute, 'addresses')) {
        $activePage = 'addresses';
    } elseif (str_contains($currentRoute, 'payment-methode')) {
        $activePage = 'payment-methode';
    }
    
    // Get customer info from passed variable or Auth (server-side)
    $customer = $customer ?? Auth::guard('customer')->user();
    $customerName = $customer ? $customer->full_name : 'Guest User';
    
    // Get customer location from default address or first address
    $customerLocation = 'Not Set';
    if ($customer) {
        // Try to get country from default address first
        $defaultAddress = $customer->defaultAddress;
        if ($defaultAddress && $defaultAddress->country) {
            $customerLocation = $defaultAddress->country;
        } else {
            // If no default address, try to get from any address
            $anyAddress = $customer->addresses()->whereNotNull('country')->first();
            if ($anyAddress && $anyAddress->country) {
                $customerLocation = $anyAddress->country;
            }
        }
    }
    // Optimize image URL generation - avoid Storage facade call if possible
    if ($customer && $customer->profile_image) {
        $customerImage = asset('storage/' . $customer->profile_image);
    } else {
        $customerImage = asset('frontend/images/user-image.webp');
    }
@endphp

<div class="col-12 col-md-12 col-lg-4 col-xl-4 text-center miliods">
	<div class="d-block border rounded mfliud-bot">
		@if($customer)
		<div class="dashboard_author px-2 py-5">
			<div class="dash_auth_thumb circle p-1 border d-inline-flex mx-auto mb-2">
				<img src="{{ $customerImage }}" 
				     class="img-fluid circle" 
				     width="100" 
				     height="100"
				     alt="Customer" 
				     id="customerSidebarImage"
				     loading="eager"
				     onerror="this.src='{{ asset('frontend/images/user-image.webp') }}'; this.onerror=null;" />
			</div>
			<div class="dash_caption">
				<h4 class="fs-md ft-medium mb-0 lh-1" id="customerSidebarName">{{ $customerName }}</h4>
				<span class="text-muted smalls" id="customerSidebarLocation">{{ $customerLocation }}</span>
			</div>
		</div>
		@else
		<div class="dashboard_author px-2 py-5" id="customerSidebarInfo" style="display: none;">
			<div class="dash_auth_thumb circle p-1 border d-inline-flex mx-auto mb-2">
				<img src="" class="img-fluid circle" width="100" alt="Customer" id="customerSidebarImage" style="display: none;" />
			</div>
			<div class="dash_caption">
				<h4 class="fs-md ft-medium mb-0 lh-1" id="customerSidebarName"></h4>
				<span class="text-muted smalls" id="customerSidebarLocation"></span>
			</div>
		</div>
		<div class="dashboard_author px-2 py-5" id="customerSidebarLoading">
			<div class="text-center">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
				<p class="text-muted small mt-2 mb-0">Loading profile...</p>
			</div>
		</div>
		@endif
		
		<div class="dashboard_author">
			<h4 class="px-3 py-2 mb-0 lh-2 gray fs-sm ft-medium text-muted text-uppercase text-left">Dashboard Navigation</h4>
			<ul class="dahs_navbar">
				<li>
					<a href="{{ route('frontend.my-orders') }}" class="{{ $activePage === 'my-orders' ? 'active' : '' }}">
						<i class="lni lni-shopping-basket me-2"></i>My Order
					</a>
				</li>
				<li>
					<a href="{{ route('frontend.wishlist') }}" class="{{ $activePage === 'wishlist' ? 'active' : '' }}">
						<i class="lni lni-heart me-2"></i>Wishlist
					</a>
				</li>
				<li>
					<a href="{{ route('frontend.profile-info') }}" class="{{ $activePage === 'profile-info' ? 'active' : '' }}">
						<i class="lni lni-user me-2"></i>Profile Info
					</a>
				</li>
				<li>
					<a href="{{ route('frontend.change-password') }}" class="{{ $activePage === 'change-password' ? 'active' : '' }}">
						<i class="lni lni-lock me-2"></i>Change Password
					</a>
				</li>
				<li>
					<a href="{{ route('frontend.addresses') }}" class="{{ $activePage === 'addresses' ? 'active' : '' }}">
						<i class="lni lni-map-marker me-2"></i>Addresses
					</a>
				</li>
				{{-- <li>
					<a href="{{ route('frontend.payment-methode') }}" class="{{ $activePage === 'payment-methode' ? 'active' : '' }}">
						<i class="lni lni-mastercard me-2"></i>Payment Methode
					</a>
				</li> --}}
				<li>
					<a href="javascript:void(0)" id="customerSidebarLogout">
						<i class="lni lni-power-switch me-2"></i>Log Out
					</a>
				</li>
			</ul>
		</div> 
		
	</div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
	// Get CSRF token
	const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
	
	@if(!$customer)
	// Only load customer info via AJAX if not passed server-side
	function loadCustomerInfo() {
		$.ajax({
			url: '/api/auth/me',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function(response) {
				if (response.success && response.data) {
					const customer = response.data;
					
					// Update customer name
					$('#customerSidebarName').text(customer.full_name || 'Customer');
					
					// Update customer image if available
					if (customer.profile_image) {
						$('#customerSidebarImage').attr('src', customer.profile_image).show();
					} else {
						$('#customerSidebarImage').attr('src', '{{ asset("frontend/images/user-image.webp") }}').show();
					}
					
					// Update location from default address or any address
					let location = 'Not Set';
					if (customer.default_address && customer.default_address.country) {
						location = customer.default_address.country;
					} else if (customer.addresses && customer.addresses.length > 0) {
						// Find first address with country
						const addressWithCountry = customer.addresses.find(addr => addr.country);
						if (addressWithCountry && addressWithCountry.country) {
							location = addressWithCountry.country;
						}
					}
					$('#customerSidebarLocation').text(location);
					
					// Hide loading, show customer info
					$('#customerSidebarLoading').hide();
					$('#customerSidebarInfo').show();
				} else {
					// Not authenticated, redirect to home
					window.location.href = '{{ route("frontend.index") }}';
				}
			},
			error: function(xhr) {
				if (xhr.status === 401 || xhr.status === 200) {
					// Not authenticated, redirect to home
					window.location.href = '{{ route("frontend.index") }}';
				} else {
					// Other error, show error message
					$('#customerSidebarLoading').html('<p class="text-danger small mt-2 mb-0">Error loading profile</p>');
				}
			}
		});
	}
	
	// Load customer info on page load (only if not passed server-side)
	loadCustomerInfo();
	@endif
	
	// Handle logout
	$('#customerSidebarLogout').on('click', function(e) {
		e.preventDefault();
		
		$.ajax({
			url: '/api/auth/logout',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			success: function() {
				window.location.href = '{{ route("frontend.index") }}';
			},
			error: function() {
				// Even if logout fails, redirect
				window.location.href = '{{ route("frontend.index") }}';
			}
		});
	});
});
</script>
@endpush

