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
				<div class="top_second text-center"><p class="medium text-light m-0 p-0">Get Free delivery from $2000 <a href="{{ route('frontend.shop') }}" class="medium text-light text-underline">Shop Now</a></p></div>
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
					<li>
						<a href="#" data-bs-toggle="modal" data-bs-target="#login"> 
              <i class="lni lni-user-4"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('frontend.wishlist') }}">
							<i class="lni lni-heart"></i><span class="dn-counter">2</span>
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
								<li class="main-menu-item"><a href="{{ route('frontend.wishlist') }}"> <i class="lni lni-heart"></i> Wishlist</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.profile-info') }}"> <i class="lni lni-user"></i> Profile Info</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.addresses') }}"> <i class="lni lni-map-marker"></i> Addresses</a></li>
								<li class="main-menu-item"><a href="{{ route('frontend.payment-methode') }}"> <i class="lni lni-mastercard"></i> Payment Methode</a></li>
								<li class="main-menu-item"><a href="#" data-bs-toggle="modal" data-bs-target="#login"> 
									<i class="lni lni-power-switch"></i>   Log Out</a></li>
								


					 

					<li class="mega-menu-item">
						<a href="{{ route('frontend.shop') }}?category=men">
							Men
							<span class="submenu-indicator">
								<span class="submenu-indicator-chevron"></span>
							</span>
						</a>
						<!-- Mega Menu Panel -->
						<div class="mega-menu-panel">
							<div class="mega-menu-container">
								<div class="mega-menu-row">
									<!-- Column 1: T-Shirts & Shirts -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">T-Shirts</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-essential-t-shirts">Essential</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-fashion-t-shirts">Fashion</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-stripes-t-shirts">Stripes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-pocket-t-shirts">Pocket T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-graphic-t-shirts">Graphic T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-slogan-t-shirts">Slogan T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-fullsleeve-t-shirts">Fullsleeve</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-vneck-t-shirts">Fullsleeve V-Necks</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Shirts</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-casual-shirts">Casual Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-formal-shirts">Formal Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-slim-fit-shirts">Slim Fit Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-regular-fit-shirts">Regular Fit Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-checked-shirts">Checked Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-striped-shirts">Striped Shirts</a></li>
										</ul>
									</div>

									<!-- Column 2: Bottomwear & Winter Wear -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Bottomwear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-jeans">Jeans</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-trousers">Trousers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-chinos">Chinos</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-cargos">Cargos</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-shorts">Shorts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-track-pants">Track Pants</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Winter Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-sweatshirt-hoodies">Sweatshirt and Hoodies</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sweater-cardigans">Sweater and Cardigans</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-jackets-coats">Jackets and Coats</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-tracksuits">Tracksuits</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-thermal-wear">Thermal Wear</a></li>
										</ul>
									</div>

									<!-- Column 3: Footwear & Sports -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Footwear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-casual-shoes">Casual Shoes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-formal-shoes">Formal Shoes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sports-shoes">Sports Shoes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sneakers">Sneakers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sandals">Sandals</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-loafers">Loafers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-boots">Boots</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Sports And Gym Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-sports-t-shirts">T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sports-shorts">Shorts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sports-pants">Track Pants</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-gym-wear">Gym Wear</a></li>
										</ul>
									</div>

									<!-- Column 4: Innerwear & Ethnic Wear -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Innerwear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-vests">Vests</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-briefs">Briefs</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-boxers">Boxers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-thermals">Thermals</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-nightwear">Nightwear</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Ethnic Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-kurtas">Kurtas</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sherwanis">Sherwanis</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-dhotis">Dhotis</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-pyjamas">Pyjamas</a></li>
										</ul>
									</div>

									<!-- Column 5: Accessories & Personal Care -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Accessories</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-watches">Watches</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-belts">Belts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-wallets">Wallets</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-sunglasses">Sunglasses</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-caps">Caps & Hats</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-bags">Bags & Backpacks</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-ties">Ties & Cufflinks</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Personal Care</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=men-fragrance">Fragrance</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-grooming">Grooming</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=men-skincare">Skincare</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</li>

					<!-- Level 1: Women - Mega Menu -->
					<li class="mega-menu-item">
						<a href="{{ route('frontend.shop') }}?category=women">
							Women
							<span class="submenu-indicator">
								<span class="submenu-indicator-chevron"></span>
							</span>
						</a>
						<!-- Mega Menu Panel -->
						<div class="mega-menu-panel">
							<div class="mega-menu-container">
								<div class="mega-menu-row">
									<!-- Column 1: Western Wear & Ethnic Wear -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Western Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-dresses">Dress</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-tops">Tops</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-tees-shirts">Tees & Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-skirts">Skirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-trousers">Trousers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-shorts">Shorts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-jeans">Jeans</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Ethnic Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-ethnic-suits">Ethnic Suits</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-stitched-suits">Stitched Suits</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-kurtis">Kurtis</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-sarees">Sarees</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-tunics">Tunics</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-premium-suits">Premium Suits Fabric</a></li>
										</ul>
									</div>

									<!-- Column 2: Winter Wear & Sports -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Winter Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-sweatshirt-hoodies">Sweatshirt and Hoodies</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-sweater-cardigans">Sweater and Cardigans</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-jackets-coats">Jackets and Coats</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-tracksuits-pjs">Tracksuits and PJs</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-poncho-shawls">Poncho and Shawls</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-winter-bottomwear">Winter Bottomwear</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Sports And Gym Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-sports-t-shirts">T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-track-pants-capris">Track Pants and Capris</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-sports-bras">Sports Bras</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-sports-shorts">Shorts</a></li>
										</ul>
									</div>

									<!-- Column 3: Jewellery, Leggings & Footwear -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Jewellery</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-jewellery-all">All Jewellery</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Leggings / Jeggings / Palazzos</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-palazzos">Palazzos</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-printed-leggings">Printed Leggings/Jeggings</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-solid-leggings">Solid Color Leggings</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Footwear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-flats">Flats</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-heels">Heels</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-flip-flops">Flip Flops</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-loafers">Loafers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-sneakers">Sneakers</a></li>
										</ul>
									</div>

									<!-- Column 4: Lingerie -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Lingerie</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-night-wear">Night Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-brassiere">Brassiere</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-panties">Panties</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-plus-size">Plus Size</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-stockings">Stockings</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-swimwear-beachwear">Swimwear / Beachwear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-shapewear">Shapewear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-g-strings-thongs">G-Strings & Thongs</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-thermal-wear">Thermal Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-lingerie-bags">Lingerie Bags</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-camisoles">Camisoles</a></li>
										</ul>
									</div>

									<!-- Column 5: Home Decor, Gifts, Bags, Accessories, Personal Care -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Home Decor</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-home-decor-all">All Home Decor</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Gifts</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-gifts-all">All Gifts</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Bags</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-bags-all">All Bags</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Accessories</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-accessories-all">All Accessories</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Personal Care</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=women-fragrance">Fragrance</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-beauty">Beauty</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=women-masks">Masks</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</li>

					<!-- Level 1: Kids - Mega Menu -->
					<li class="mega-menu-item">
						<a href="{{ route('frontend.shop') }}?category=kids">
							Kids
							<span class="submenu-indicator">
								<span class="submenu-indicator-chevron"></span>
							</span>
						</a>
						<!-- Mega Menu Panel -->
						<div class="mega-menu-panel">
							<div class="mega-menu-container">
								<div class="mega-menu-row">
									<!-- Column 1: Kids Clothing -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Kids Clothing</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-boys">Boys</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-girls">Girls</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-babies">Babies</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-toddlers">Toddlers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-newborn">Newborn</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-t-shirts">T-Shirts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-dresses">Dresses</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-jeans">Jeans</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-shorts">Shorts</a></li>
										</ul>
									</div>

									<!-- Column 2: Kids Shoes & Winter Wear -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Kids Shoes</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-casual-shoes">Casual Shoes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-sports-shoes">Sports Shoes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-sandals">Sandals</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-boots">Boots</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-sneakers">Sneakers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-flip-flops">Flip Flops</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Winter Wear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-jackets">Jackets</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-sweaters">Sweaters</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-thermal-wear">Thermal Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-gloves">Gloves & Mittens</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-hoodies">Hoodies</a></li>
										</ul>
									</div>

									<!-- Column 3: Toys & Games -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Toys & Games</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-action-figures">Action Figures</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-dolls">Dolls</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-building-blocks">Building Blocks</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-board-games">Board Games</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-puzzles">Puzzles</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-remote-control">Remote Control Toys</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-educational-toys">Educational Toys</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-outdoor-toys">Outdoor Toys</a></li>
										</ul>
									</div>

									<!-- Column 4: Accessories & School Essentials -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Kids Accessories</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-bags">Bags & Backpacks</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-watches">Watches</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-hats">Hats & Caps</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-sunglasses">Sunglasses</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-belts">Belts</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">School Essentials</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-uniforms">Uniforms</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-stationery">Stationery</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-lunch-boxes">Lunch Boxes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-water-bottles">Water Bottles</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-school-bags">School Bags</a></li>
										</ul>
									</div>

									<!-- Column 5: Swimwear, Baby Care & More -->
									<div class="mega-menu-column">
										<div class="mega-menu-title">Swimwear</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-swimsuits">Swimsuits</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-swim-shorts">Swim Shorts</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-swim-accessories">Swim Accessories</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Baby Care</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-diapers">Diapers</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-baby-clothes">Baby Clothes</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-feeding">Feeding</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-baby-toys">Baby Toys</a></li>
										</ul>
										<div class="mega-menu-title" style="margin-top: 25px;">Sports & Active</div>
										<ul class="mega-menu-list">
											<li><a href="{{ route('frontend.shop') }}?category=kids-sports-wear">Sports Wear</a></li>
											<li><a href="{{ route('frontend.shop') }}?category=kids-active-wear">Active Wear</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</li>

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
					<!-- <li>
						<a href="#" data-bs-toggle="modal" data-bs-target="#login">
							<i class="lni lni-user"></i>
						</a>
					</li> -->

					<li class="has-submenu">
					<a href="javascript:void(0);">
						<i class="lni lni-user"></i>
						<span class="submenu-indicator"><span class="submenu-indicator-chevron"></span></span>
					</a>
					<ul class="nav-dropdown nav-submenu">
						<li><a href="{{ route('frontend.my-orders') }}"><i class="lni lni-shopping-basket me-2"></i>My Order</a></li>
						<li><a href="{{ route('frontend.wishlist') }}"><i class="lni lni-heart me-2"></i>Wishlist</a></li>
						<li><a href="{{ route('frontend.profile-info') }}"><i class="lni lni-user me-2"></i>Profile Info</a></li>
						<li><a href="{{ route('frontend.addresses') }}"><i class="lni lni-map-marker me-2"></i>Addresses</a></li>
						<li><a href="{{ route('frontend.payment-methode') }}"><i class="lni lni-mastercard me-2"></i>Payment Methode</a></li>
						<li><a href="#" data-bs-toggle="modal" data-bs-target="#login"><i class="lni lni-power-switch me-2"></i>Log Out</a></li>
					</ul>
				</li>
					<li>
						<a href="{{ route('frontend.wishlist') }}">
							<i class="lni lni-heart"></i><span class="dn-counter">2</span>
						</a>
					</li>
					<li>
						<a href="{{ route('frontend.shoping-cart') }}">
							<i class="lni lni-shopping-basket"></i><span class="dn-counter">3</span>
						</a>
					</li> 
				</ul>
			</div>
		</nav>
	</div>
</div>
<!-- End Navigation -->
<div class="clearfix"></div>

