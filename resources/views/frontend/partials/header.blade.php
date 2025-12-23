<!-- ============================================================== -->
<!-- Top header  -->
<!-- ============================================================== -->
<div class="py-2 bg-dark">
	<div class="container">
		<div class="row">
			
			<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 hide-ipad">
				<div class="top_first"><a href="callto:(+84)0123456789" class="medium text-light">(+84) 0123 456 789</a></div>
			</div>
			
			<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 hide-ipad">
				<div class="top_second text-center"><p class="medium text-light m-0 p-0">Get Free delivery from ₹2000 <a href="{{ route('frontend.shop') }}" class="medium text-light text-underline">Shop Now</a></p></div>
			</div>
			
			<!-- Right Menu -->
			<div class="col-xl-4 col-lg-4 col-md-5 col-sm-12">

				<div class="currency-selector dropdown js-dropdown float-right">
					<a href="javascript:void(0);" data-bs-toggle="dropdown" class="popup-title"  title="Currency" aria-label="Currency dropdown">
						<span class="hidden-xl-down medium text-light">Currency:</span>
						<span class="iso_code medium text-light">$USD</span>
						<i class="fa fa-angle-down medium text-light"></i>
					</a>
					<ul class="popup-content dropdown-menu">  
						<li><a title="Euro" href="#" class="dropdown-item medium text-medium">EUR €</a></li>
						<li class="current"><a title="US Dollar" href="#" class="dropdown-item medium text-medium">USD $</a></li>
					</ul>
				</div>
				
				<!-- Choose Language -->
			
				<div class="language-selector-wrapper dropdown js-dropdown float-right me-3">
					<a class="popup-title" href="javascript:void(0)" data-bs-toggle="dropdown" title="Language" aria-label="Language dropdown">
						<span class="hidden-xl-down medium text-light">Language:</span>
						<span class="iso_code medium text-light">English</span>
						<i class="fa fa-angle-down medium text-light"></i>
					</a>
					<ul class="dropdown-menu popup-content link">
						<li class="current"><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/1.jpg') }}" alt="en" width="16" height="11" /><span>English</span></a></li>
						<li><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/2.jpg') }}" alt="fr" width="16" height="11" /><span>Français</span></a></li>
						<li><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/3.jpg') }}" alt="de" width="16" height="11" /><span>Deutsch</span></a></li>
						<li><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/4.jpg') }}" alt="it" width="16" height="11" /><span>Italiano</span></a></li>
						<li><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/5.jpg') }}" alt="es" width="16" height="11" /><span>Español</span></a></li>
						<li ><a href="javascript:void(0);" class="dropdown-item medium text-medium"><img src="{{ asset('frontend/images/6.jpg') }}" alt="ar" width="16" height="11" /><span>اللغة العربية</span></a></li>
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
					@guest
					<li>
						<a href="#" data-bs-toggle="modal" data-bs-target="#login"> 
              <i class="lni lni-user-4"></i>
						</a>
					</li>
					@else
					<li class="has-submenu">
						<a href="{{ route('frontend.profile-info') }}">
							<i class="lni lni-user-4"></i>
							<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span>
						</a>
						<ul class="nav-dropdown nav-submenu">
							<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
							<li><a href="{{ route('frontend.wishlist') }}" class="wishlist-link"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
							<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
							<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
							<!-- <li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li> -->
							<li><a href="#" id="customerLogoutBtn2"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
						</ul>
					</li>
					@endguest
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
					<li>
						<a href="{{ route('frontend.my-orders') }}">
							<i class="lni lni-dashboard"></i><span class="dn-counter">0</span>
						</a>
					</li>
					</ul>
				</div>
			</div>

			<div class="nav-menus-wrapper" style="transition-property: none;">
				<ul class="nav-menu">  
				<li class=""><a href="javascript:void(0);">Lomoofy <span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
						<ul class="nav-dropdown nav-submenu" style="right: auto; display: none;">
							<li class=""><a href="{{ route('frontend.about-us') }}">About Us</a></li>
							<li class=""><a href="{{ route('frontend.contact') }}">Contact Us</a></li> 
						</ul>
					</li>
					
					<li class="main-menu-item"><a href="javascript:void(0);">Men<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
									<ul class="nav-dropdown nav-submenu" style="display: none; right: auto;">
										<li class=""><a href="javascript:void(0);">T-Shirts<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu" style="display: none;">
												<li><a href="{{ route('frontend.shop') }}?category=men-essential-t-shirts">Essential</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-fashion-t-shirts">Fashion</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-stripes-t-shirts">Stripes</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-pocket-t-shirts">Pocket T-Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-graphic-t-shirts">Graphic T-Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-slogan-t-shirts">Slogan T-Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-fullsleeve-t-shirts">Fullsleeve</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-vneck-t-shirts">Fullsleeve V-Necks</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Shirts<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=men-casual-shoes">Casual Shoes</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-formal-shirts">Formal Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-slim-fit-shirts">Slim Fit Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-regular-fit-shirts">Regular Fit Shirts</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Bottomwear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=men-jeans">Jeans</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-trousers">Trousers</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-chinos">Chinos</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-cargos">Cargos</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Winter Wear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=men-sweatshirt-hoodies">Sweatshirt and Hoodies</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-sweater-cardigans">Sweater and Cardigans</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-jackets-coats">Jackets and Coats</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-tracksuits">Tracksuits</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Footwear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=men-casual-shoes">Casual Shoes</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-formal-shoes">Formal Shoes</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-sports-shoes">Sports Shoes</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Sports And Gym Wear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=men-sports-t-shirts">T-Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-sports-shorts">Shorts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=men-sports-pants">Track Pants</a></li>
											</ul>
										</li> 
									</ul>
								</li>

								<li class="main-menu-item"><a href="javascript:void(0);">Women<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
									<ul class="nav-dropdown nav-submenu" style="display: none; right: auto;">
										<li class=""><a href="javascript:void(0);">Western Wear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu" style="display: none;">
												<li><a href="{{ route('frontend.shop') }}?category=women-western-wear">Western Wear</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-wear">Ethnic Wear</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=women-western-wear">Western Wear</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Ethnic Wear<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-wear">Ethnic Wear</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=women-western-wear">Western Wear</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-wear">Ethnic Wear</a></li>
											</ul>
										</li> 
											<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-wear">Ethnic Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-western-wear">Western Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-wear">Ethnic Wear</a></li>
									</ul>
								</li>


								<li class="main-menu-item">
									<a href="javascript:void(0);">Kids<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
									<ul class="nav-dropdown nav-submenu" style="display: none; right: auto;">
										<li class=""><a href="javascript:void(0);">Boys<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu" style="display: none;">
												<li><a href="{{ route('frontend.shop') }}?category=kids-boys-shirts">Shirts</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=kids-boys-trousers">Trousers</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=kids-boys-chinos">Chinos</a></li>
												<li><a href="{{ route('frontend.shop') }}?category=kids-boys-cargos">Cargos</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Girls<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=kids-girls-dresses">Dresses</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Babies<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=kids-babies-dresses">Dresses</a></li>
											</ul>
										</li>
										<li><a href="javascript:void(0);">Toddlers<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span></a>
											<ul class="nav-dropdown nav-submenu">
												<li><a href="{{ route('frontend.shop') }}?category=kids-toddlers-dresses">Dresses</a></li>
											</ul>
										</li>
									</ul>
								</li> 
								
								<li class="main-menu-item"><a href="{{ route('frontend.my-orders') }}"> <i class="lni lni-dashboard"></i> My Orders</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.wishlist') }}" class="wishlist-link"> <i class="lni lni-heart"></i> Wishlist</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.profile-info') }}"> <i class="lni lni-user"></i> Profile Info</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.addresses') }}"> <i class="lni lni-map-marker"></i> Addresses</a></li>
								<!-- <li class="main-menu-item"><a href="{{ route('frontend.payment-methode') }}"> <i class="lni lni-mastercard"></i>Payment Methode</a></li> -->
								<li class="main-menu-item"><a href="#" id="customerLogoutBtn3"> 
									<i class="lni lni-power-switch"></i>   Log Out</a></li>
								

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
									LL$q->where(function($query) {
										$query->where('is_active', true)->orWhereNull('is_active');
									})
									->orderBy('sort_order');
								}]);
							}])
							->orderBy('sort_order')
							->limit(4)
							->get();
					@endphp

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

					<!-- 
					Dynamic Category Code (Commented - Uncomment when ready to use):
					@php
						use App\Models\Category;
						// Get parent categories (level 1) with their children and grandchildren
						$parentCategories = Category::whereNull('parent_id')
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
							->get();
					@endphp

					@if($parentCategories->count() > 0)
						@foreach($parentCategories as $parentCategory)
							@php
								$hasChildren = $parentCategory->children->count() > 0;
							@endphp
							<li>
								<a href="{{ route('frontend.shop') }}?category={{ $parentCategory->slug }}">
									{{ $parentCategory->name }}
									@if($hasChildren)
										<span class="submenu-indicator">
											<span class="submenu-indicator-chevron"></span>
										</span>
									@endif
								</a>
								@if($hasChildren)
									<ul class="nav-dropdown nav-submenu">
										@foreach($parentCategory->children as $childCategory)
											@php
												$hasGrandchildren = $childCategory->children->count() > 0;
											@endphp
											<li class="{{ $hasGrandchildren ? 'has-submenu' : '' }}">
												<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}">
													{{ $childCategory->name }}
												</a>
												@if($hasGrandchildren)
													<ul class="nav-dropdown nav-submenu">
														@foreach($childCategory->children as $grandchildCategory)
															<li>
																<a href="{{ route('frontend.shop') }}?category={{ $grandchildCategory->slug }}">
																	{{ $grandchildCategory->name }}
																</a>
															</li>
														@endforeach
													</ul>
												@endif
											</li>
										@endforeach
									</ul>
								@endif
							</li>
						@endforeach
					@endif
					-->

					<!-- <li><a href="{{ route('frontend.index') }}">Home</a></li>
					
					<li><a href="{{ route('frontend.shop') }}">Shop</a></li>   -->

          <!-- <li><a href="{{ route('frontend.profile-info') }}">My Account</a></li> -->

         <!-- <li><a href="{{ route('frontend.privacy') }}">Privacy Policy</a></li> -->
					
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
							<i class="lni lni-user"></i>
							<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span>
						</a>
						<ul class="nav-dropdown nav-submenu">
							<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
							<li><a href="{{ route('frontend.wishlist') }}" class="wishlist-link"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
							<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
							<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
							<!-- <li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li> -->
							<li><a href="#" id="customerLogoutBtn"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
						</ul>
					</li>
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

		}); // End jQuery ready
	} // End initCustomerAuth
	
	// Start initialization
	initCustomerAuth();
})(); // End IIFE
</script>
@endpush

