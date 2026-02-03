
 
<!-- Start Navigation variant one-->
<div class="header header-light dark-text" id="isnavbar-v1"  >
	<div class="container">
		<nav id="navigation" class="navigation navigation-landscape">
			<div class="nav-header">
				<a class="nav-brand" href="{{ route('frontend.index') }}">
					<img src="{{ asset('frontend/images/logo.png') }}" class="logo" alt="" />
				</a>
				<div class="nav-toggle"></div>
				<div class="mobile_nav">
					<ul> 
				 
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
			<div class="nav-menus-wrapper" style="transition-property: none;">
				<ul class="nav-menu">   
			
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