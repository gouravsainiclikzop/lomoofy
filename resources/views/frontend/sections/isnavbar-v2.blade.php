
<!-- Start Navigation variant two -->
 
<div class="header header-light dark-text" id="isnavbar-v2"  >
	<div class="container">
		<nav id="navigation" class="navigation navigation-landscape">
			<div class="nav-header">
				<a class="nav-brand" href="{{ route('frontend.index') }}">
					<img src="{{ asset('frontend/images/logo.png') }}" class="logo" alt="" />
				</a>
				<div class="nav-toggle"></div>
				<div class="mobile_nav">
					<ul> 
						<li>
							<a href="#" onclick="openSearch()">
								<i class="lni lni-search-alt"></i>
							</a>
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

				<li class="">
									<a href="#" onclick="openSearch()">
										<i class="lni lni-search-alt"></i>
									</a>
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

<!-- this section related to isnavbar-v2 -->
<div class="w3-ch-sideBar w3-bar-block w3-card-2 w3-animate-right" style="display: none; right: -320px;  " id="Search">
				<div class="rightMenu-scroll">
					<div class="d-flex align-items-center justify-content-between slide-head py-3 px-3">
						<h4 class="cart_heading fs-md ft-medium mb-0">Search Products</h4>
						<button onclick="closeSearch()" class="close_slide"><i class="ti-close"></i></button>
					</div>
						
					<div class="cart_action px-3 py-4">
						<form class="form m-0 p-0" id="searchForm" action="{{ route('frontend.shop') }}" method="GET">
							<div class="form-group mb-3">
								<input type="text" name="search" id="searchKeyword" class="form-control" placeholder="Product Keyword.." value="{{ request('search') }}">
							</div>
							
							<div class="form-group mb-3">
								<select name="category" id="searchCategory" class="custom-select form-select">
									<option value="">Choose Category</option>
									@php
										$allCategories = \App\Models\Category::where(function($q) {
											$q->where('is_active', true)->orWhereNull('is_active');
										})
										->orderBy('sort_order')
										->orderBy('name')
										->get();
									@endphp
									@foreach($allCategories as $cat)
										<option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
											{{ $cat->name }}
										</option>
									@endforeach
								</select>
							</div>
							
							<div class="form-group mb-0">
								<button type="submit" class="btn d-block full-width btn-dark">Search Product</button>
							</div>
						</form>
					</div>
					
					<div class="d-flex align-items-center justify-content-center br-top br-bottom py-2 px-3">
						<h4 class="cart_heading fs-md mb-0">Hot Categories</h4>
					</div>
						
					<div class="cart_action px-3 py-3">
						<div class="row">
							@php
								// Get featured categories for hot categories section
								$hotCategories = \App\Models\Category::where(function($q) {
									$q->where('is_active', true)->orWhereNull('is_active');
								})
								->where('featured', true)
								->orderBy('sort_order')
								->limit(6)
								->get();
								
								// If no featured categories, use first 6 parent categories
								if ($hotCategories->count() == 0) {
									$hotCategories = \App\Models\Category::whereNull('parent_id')
										->where(function($q) {
											$q->where('is_active', true)->orWhereNull('is_active');
										})
										->orderBy('sort_order')
										->limit(6)
										->get();
								}
							@endphp
							@if($hotCategories->count() > 0)
								@foreach($hotCategories as $hotCategory)
									<div class="col-xl-4 col-lg-4 col-md-4 col-4 mb-3">
										<div class="cats_side_wrap text-center">
											<div class="sl_cat_01">
												<div class="d-inline-flex align-items-center justify-content-center p-3 circle mb-2 gray">
													<a href="{{ route('frontend.shop') }}?category={{ $hotCategory->slug }}" class="d-block">
														@if($hotCategory->image)
															<img src="{{ asset('storage/' . $hotCategory->image) }}" class="img-fluid" width="40" alt="{{ $hotCategory->name }}">
														@else
															<img src="{{ asset('assets/images/placeholder.jpg') }}" class="img-fluid" width="40" alt="{{ $hotCategory->name }}">
														@endif
													</a>
												</div>
											</div>
											<div class="sl_cat_02">
												<h6 class="m-0 ft-medium fs-sm">
													<a href="{{ route('frontend.shop') }}?category={{ $hotCategory->slug }}">{{ $hotCategory->name }}</a>
												</h6>
											</div>
										</div>
									</div>
								@endforeach
							@else
								<div class="col-12">
									<p class="text-center text-muted small">No categories available</p>
								</div>
							@endif
						</div>
					</div> 
			</div>
		</div>