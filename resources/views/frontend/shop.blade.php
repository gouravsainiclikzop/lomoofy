@extends('layouts.frontend')

@section('title', 'Shop - Lomoofy Industries')

@section('content')
			<!-- ======================= Shop Style 1 ======================== -->
 
			<section class="bg-cover d-none d-md-block" style="background:url({{ asset('frontend/images/banner-2.png') }}) no-repeat;">
				<div class="container">
					<div class="row align-items-center justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="text-left py-5 mt-3 mb-3">
								<h1 class="ft-medium mb-3">{{ $selectedCategory ? $selectedCategory->name : 'Shop' }}</h1>
								@if($parentCategories->count() > 0)
								<ul class="shop_categories_list m-0 p-0">
									@foreach($parentCategories as $parentCategory)
									<li>
										<a href="{{ route('frontend.shop') }}?category={{ $parentCategory->slug }}">
											{{ $parentCategory->name }}
										</a>
									</li>
									@endforeach
								</ul>
								@endif
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= Shop Style 1 ======================== -->
			
			
			<!-- ======================= Filter Wrap Style 1 ======================== --> 
			<section class="py-3 br-bottom br-top d-none d-md-block">
				<div class="container">
					<div class="row align-items-center justify-content-between">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item">
										<a href="{{ route('frontend.index') }}">Home</a>
									</li>
									<li class="breadcrumb-item">
										<a href="{{ route('frontend.shop') }}">Shop</a>
									</li>
									@if($selectedCategory && count($breadcrumb) > 0)
										@foreach($breadcrumb as $index => $crumb)
											@if($index < count($breadcrumb) - 1)
												<li class="breadcrumb-item">
													<a href="{{ route('frontend.shop') }}?category={{ $crumb['slug'] }}">
														{{ $crumb['name'] }}
													</a>
												</li>
											@else
												<li class="breadcrumb-item active" aria-current="page">
													{{ $crumb['name'] }}
												</li>
											@endif
										@endforeach
									@endif
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>
			<!-- ============================= Filter Wrap ============================== -->
			
			<!-- ======================= Shop By Categories ======================== -->
			@if($selectedCategory && $childCategories->count() > 0)
			<section class="py-4 bg-light">
				<div class="container">
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<h5 class="ft-medium mb-4 shop-shop-by">Shop By</h5>
							<div class="shop-by-categories">
								<!-- Desktop Grid View -->
								<div class="row g-3 category-grid-view">
									@foreach($childCategories as $childCategory)
									<div class="col-xl-2 col-lg-2 col-md-4 col-sm-4 col-6">
										<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}" class="category-tab-item">
											<div class="category-image-wrapper">
												@if($childCategory->image)
													<img src="{{ asset('storage/' . $childCategory->image) }}" alt="{{ $childCategory->name }}" class="category-image">
												@else
													<img src="{{ asset('frontend/images/logo.webp') }}" alt="{{ $childCategory->name }}" class="category-image">
												@endif
											</div>
											<div class="category-label">{{ $childCategory->name }}</div>
										</a>
									</div>
									@endforeach
								</div>
								
								<!-- Mobile Slider View -->
								<div class="category-slider-view">
									<div class="category-slider">
										@foreach($childCategories as $childCategory)
										<div>
											<a href="{{ route('frontend.shop') }}?category={{ $childCategory->slug }}" class="category-tab-item">
												<div class="category-image-wrapper">
													@if($childCategory->image)
														<img src="{{ asset('storage/' . $childCategory->image) }}" alt="{{ $childCategory->name }}" class="category-image">
													@else
														<img src="{{ asset('frontend/images/1.jpg') }}" alt="{{ $childCategory->name }}" class="category-image">
													@endif
												</div>
												<div class="category-label">{{ $childCategory->name }}</div>
											</a>
										</div>
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			@endif
			<!-- ======================= Shop By Categories ======================== -->
			
			<!-- ======================= All Product List ======================== -->
			<section class="middle">
				<div class="container">
					<div class="row">
						
						<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 p-xl-0">
							<div class="search-sidebar sm-sidebar border">
								<div class="search-sidebar-body">
								
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header px-3">
											<h4 class="mt-3">Categories</h4>
										</div>
										<div class="widget-boxed-body">
											<div class="side-list no-border">
												<div class="filter-card" id="shop-categories">
													
													<!-- Single Filter Card -->
													<div class="single_filter_card">
														<h5><a href="#shoes" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Shoes<i class="accordion-indicator ti-angle-down"></i></a></h5>
														
														<div class="collapse" id="shoes" data-parent="#shop-categories" style="">
															<div class="card-body">
																<div class="inner_widget_link">
																	<ul>
																		<li><a href="#">Pumps &amp; high Heals<span>112</span></a></li>
																		<li><a href="#">Sandels<span>82</span></a></li>
																		<li><a href="#">Sneakers<span>56</span></a></li>
																		<li><a href="#">Boots<span>101</span></a></li>
																		<li><a href="#">Casual Shoes<span>212</span></a></li>
																		<li><a href="#">Flats Sandel<span>92</span></a></li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													
													<!-- Single Filter Card -->
													<div class="single_filter_card">
														<h5><a href="#clothing" data-bs-toggle="collapse" class="" aria-expanded="false" role="button">Clothing<i class="accordion-indicator ti-angle-down"></i></a></h5>
														
														<div class="collapse show" id="clothing" data-parent="#shop-categories">
															<div class="card-body">
																<div class="inner_widget_link">
																	<ul>
																		<li><a href="#">Blazers<span>82</span></a></li>
																		<li><a href="#">Men Suits<span>110</span></a></li>
																		<li><a href="#">Blouses<span>103</span></a></li>
																		<li><a href="#">Coat Pant<span>72</span></a></li>
																		<li><a href="#">T-Shirts<span>36</span></a></li>
																		<li><a href="#">Men Shirts<span>122</span></a></li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													
													<!-- Single Filter Card -->
													<div class="single_filter_card">
														<h5><a href="#watches" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Watches<i class="accordion-indicator ti-angle-down"></i></a></h5>
														
														<div class="collapse" id="watches" data-parent="#shop-categories">
															<div class="card-body">
																<div class="inner_widget_link">
																	<ul>
																		<li><a href="#">Sport Watches<span>112</span></a></li>
																		<li><a href="#">Casual Watches<span>112</span></a></li>
																		<li><a href="#">Fashion Watches<span>112</span></a></li>
																		<li><a href="#">Girls Watches<span>112</span></a></li>
																		<li><a href="#">Smart Watches<span>112</span></a></li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													
													<!-- Single Filter Card -->
													<div class="single_filter_card">
														<h5><a href="#bags" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Bags<i class="accordion-indicator ti-angle-down"></i></a></h5>
														
														<div class="collapse" id="bags" data-parent="#shop-categories">
															<div class="card-body">
																<div class="inner_widget_link">
																	<ul>
																		<li><a href="#">Casual Bags<span>212</span></a></li>
																		<li><a href="#">Sport Bags<span>212</span></a></li>
																		<li><a href="#">Lugges bags<span>82</span></a></li>
																		<li><a href="#">Fashion Bags<span>212</span></a></li>
																		<li><a href="#">Small bags<span>113</span></a></li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													
													<!-- Single Filter Card -->
													<div class="single_filter_card">
														<h5><a href="#accessories" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Accessories<i class="accordion-indicator ti-angle-down"></i></a></h5>
														
														<div class="collapse" id="accessories" data-parent="#shop-categories">
															<div class="card-body">
																<div class="inner_widget_link">
																	<ul>
																		<li><a href="#">Men Wallet<span>432</span></a></li>
																		<li><a href="#">Men Belt<span>82</span></a></li>
																		<li><a href="#">Hats<span>541</span></a></li>
																		<li><a href="#">Jwelery<span>312</span></a></li>
																		<li><a href="#">Beauty<span>65</span></a></li>
																		<li><a href="#">Cosmetic<span>242</span></a></li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#pricing" data-bs-toggle="collapse" aria-expanded="false" role="button">Pricing</a></h4>
										</div>
										<div class="widget-boxed-body collapse show" id="pricing" data-parent="#pricing">
											<div class="side-list no-border mb-4">
												<div class="rg-slider">
													 <span class="irs irs--flat js-irs-0 irs-with-grid"><span class="irs"><span class="irs-line" tabindex="0"></span><span class="irs-min" style="visibility: visible;">0</span><span class="irs-max" style="visibility: visible;">1 000</span><span class="irs-from" style="visibility: visible; left: 6.63347%;">100</span><span class="irs-to" style="visibility: visible; left: 68.4495%;">750</span><span class="irs-single" style="visibility: hidden; left: 31.2865%;">100 — 750</span></span><span class="irs-grid" style="width: 95.2191%; left: 2.29044%;"><span class="irs-grid-pol" style="left: 0%"></span><span class="irs-grid-text js-grid-text-0" style="left: 0%; margin-left: -2.19124%;">0</span><span class="irs-grid-pol small" style="left: 20%"></span><span class="irs-grid-pol small" style="left: 15%"></span><span class="irs-grid-pol small" style="left: 10%"></span><span class="irs-grid-pol small" style="left: 5%"></span><span class="irs-grid-pol" style="left: 25%"></span><span class="irs-grid-text js-grid-text-1" style="left: 25%; visibility: visible; margin-left: -4.18327%;">250</span><span class="irs-grid-pol small" style="left: 45%"></span><span class="irs-grid-pol small" style="left: 40%"></span><span class="irs-grid-pol small" style="left: 35%"></span><span class="irs-grid-pol small" style="left: 30%"></span><span class="irs-grid-pol" style="left: 50%"></span><span class="irs-grid-text js-grid-text-2" style="left: 50%; visibility: visible; margin-left: -4.18327%;">500</span><span class="irs-grid-pol small" style="left: 70%"></span><span class="irs-grid-pol small" style="left: 65%"></span><span class="irs-grid-pol small" style="left: 60%"></span><span class="irs-grid-pol small" style="left: 55%"></span><span class="irs-grid-pol" style="left: 75%"></span><span class="irs-grid-text js-grid-text-3" style="left: 75%; visibility: visible; margin-left: -4.18327%;">750</span><span class="irs-grid-pol small" style="left: 95%"></span><span class="irs-grid-pol small" style="left: 90%"></span><span class="irs-grid-pol small" style="left: 85%"></span><span class="irs-grid-pol small" style="left: 80%"></span><span class="irs-grid-pol" style="left: 100%"></span><span class="irs-grid-text js-grid-text-4" style="left: 100%; margin-left: -5.67729%;">1 000</span></span><span class="irs-bar" style="left: 11.9124%; width: 61.8924%;"></span><span class="irs-shadow shadow-from" style="display: none;"></span><span class="irs-shadow shadow-to" style="display: none;"></span><span class="irs-handle from" style="left: 9.52191%;"><i></i><i></i><i></i></span><span class="irs-handle to type_last" style="left: 71.4143%;"><i></i><i></i><i></i></span></span><input type="text" class="js-range-slider irs-hidden-input" name="my_range" value="" tabindex="-1" readonly="">
												</div>		
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#size" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Size</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="size" data-parent="#size">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="text-left pb-0 pt-2">
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="26s">
																<label class="form-option-label" for="26s">26</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="28s">
																<label class="form-option-label" for="28s">28</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="30s" checked="">
																<label class="form-option-label" for="30s">30</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="32s">
																<label class="form-option-label" for="32s">32</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="34s">
																<label class="form-option-label" for="34s">34</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="36s">
																<label class="form-option-label" for="36s">36</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="38s">
																<label class="form-option-label" for="38s">38</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="40s">
																<label class="form-option-label" for="40s">40</label>
															</div>
															<div class="form-check form-option form-check-inline mb-2">
																<input class="form-check-input" type="radio" name="sizes" id="42s">
																<label class="form-option-label" for="42s">42</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#brands" data-bs-toggle="collapse" aria-expanded="false" role="button">Brands</a></h4>
										</div>
										<div class="widget-boxed-body collapse show" id="brands" data-parent="#brands">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="b1" class="checkbox-custom" name="b1" type="checkbox">
																	<label for="b1" class="checkbox-custom-label">Sumsung<span>142</span></label>
																</li>
																<li>
																	<input id="b2" class="checkbox-custom" name="b2" type="checkbox">
																	<label for="b2" class="checkbox-custom-label">Apple<span>652</span></label>
																</li>
																<li>
																	<input id="b3" class="checkbox-custom" name="b3" type="checkbox">
																	<label for="b3" class="checkbox-custom-label">Nike<span>232</span></label>
																</li>
																<li>
																	<input id="b4" class="checkbox-custom" name="b4" type="checkbox">
																	<label for="b4" class="checkbox-custom-label">Reebok<span>192</span></label>
																</li>
																<li>
																	<input id="b5" class="checkbox-custom" name="b5" type="checkbox">
																	<label for="b5" class="checkbox-custom-label">Hawai<span>265</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#gender" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Gender</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="gender" data-parent="#gender">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="g1" class="checkbox-custom" name="g1" type="checkbox">
																	<label for="g1" class="checkbox-custom-label">All<span>22</span></label>
																</li>
																<li>
																	<input id="g2" class="checkbox-custom" name="g2" type="checkbox">
																	<label for="g2" class="checkbox-custom-label">Male<span>472</span></label>
																</li>
																<li>
																	<input id="g3" class="checkbox-custom" name="g3" type="checkbox">
																	<label for="g3" class="checkbox-custom-label">Female<span>170</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#discount" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Discount</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="discount" data-parent="#discount">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="d1" class="checkbox-custom" name="d1" type="checkbox">
																	<label for="d1" class="checkbox-custom-label">80% Discount<span>22</span></label>
																</li>
																<li>
																	<input id="d2" class="checkbox-custom" name="d2" type="checkbox">
																	<label for="d2" class="checkbox-custom-label">60% Discount<span>472</span></label>
																</li>
																<li>
																	<input id="d3" class="checkbox-custom" name="d3" type="checkbox">
																	<label for="d3" class="checkbox-custom-label">50% Discount<span>170</span></label>
																</li>
																<li>
																	<input id="d4" class="checkbox-custom" name="d4" type="checkbox">
																	<label for="d4" class="checkbox-custom-label">40% Discount<span>170</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#types" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Type</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="types" data-parent="#types">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="t1" class="checkbox-custom" name="t1" type="checkbox">
																	<label for="t1" class="checkbox-custom-label">All Type<span>422</span></label>
																</li>
																<li>
																	<input id="t2" class="checkbox-custom" name="t2" type="checkbox">
																	<label for="t2" class="checkbox-custom-label">Normal Type<span>472</span></label>
																</li>
																<li>
																	<input id="t3" class="checkbox-custom" name="t3" type="checkbox">
																	<label for="t3" class="checkbox-custom-label">Simple Type<span>170</span></label>
																</li>
																<li>
																	<input id="t4" class="checkbox-custom" name="t4" type="checkbox">
																	<label for="t4" class="checkbox-custom-label">Modern type<span>140</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#occation" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Occation</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="occation" data-parent="#occation">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="inner_widget_link">
															<ul class="no-ul-list">
																<li>
																	<input id="o1" class="checkbox-custom" name="o1" type="checkbox">
																	<label for="o1" class="checkbox-custom-label">All Occation<span>422</span></label>
																</li>
																<li>
																	<input id="o2" class="checkbox-custom" name="o2" type="checkbox">
																	<label for="o2" class="checkbox-custom-label">Normal Occation<span>472</span></label>
																</li>
																<li>
																	<input id="t33" class="checkbox-custom" name="o33" type="checkbox">
																	<label for="t33" class="checkbox-custom-label">Winter Occation<span>170</span></label>
																</li>
																<li>
																	<input id="o4" class="checkbox-custom" name="o4" type="checkbox">
																	<label for="o4" class="checkbox-custom-label">Summer Occation<span>140</span></label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Single Option -->
									<div class="single_search_boxed">
										<div class="widget-boxed-header">
											<h4><a href="#colors" data-bs-toggle="collapse" class="collapsed" aria-expanded="false" role="button">Colors</a></h4>
										</div>
										<div class="widget-boxed-body collapse" id="colors" data-parent="#colors">
											<div class="side-list no-border">
												<!-- Single Filter Card -->
												<div class="single_filter_card">
													<div class="card-body pt-0">
														<div class="text-left">
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="whitea8">
																<label class="form-option-label rounded-circle" for="whitea8"><span class="form-option-color rounded-circle blc7"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="bluea8">
																<label class="form-option-label rounded-circle" for="bluea8"><span class="form-option-color rounded-circle blc2"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="yellowa8">
																<label class="form-option-label rounded-circle" for="yellowa8"><span class="form-option-color rounded-circle blc5"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="pinka8">
																<label class="form-option-label rounded-circle" for="pinka8"><span class="form-option-color rounded-circle blc3"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="reda">
																<label class="form-option-label rounded-circle" for="reda"><span class="form-option-color rounded-circle blc4"></span></label>
															</div>
															<div class="form-check form-option form-check-inline mb-1">
																<input class="form-check-input" type="radio" name="colora8" id="greena">
																<label class="form-option-label rounded-circle" for="greena"><span class="form-option-color rounded-circle blc6"></span></label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								
								</div>
							</div>
						</div>
						
						<div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
							
							<div class="row">
								<div class="col-xl-12 col-lg-12 col-md-12">
									<div class="border mb-3 mfliud">
										<div class="row align-items-center py-2 m-0">
											<div class="col-xl-3 col-lg-4 col-md-5 col-sm-12">
												<h6 class="mb-0">315 Items Found</h6>
											</div>
											
											<div class="col-xl-9 col-lg-8 col-md-7 col-sm-12">
												<div class="filter_wraps d-flex align-items-center justify-content-end m-start">
													<div class="single_fitres me-2 br-right">
														<select class="custom-select simple">
														  <option value="1" selected="">Default Sorting</option>
														  <option value="2">Sort by price: Low price</option>
														  <option value="3">Sort by price: Hight price</option>
														  <option value="4">Sort by rating</option>
														  <option value="5">Sort by trending</option>
														</select>
													</div>
													<div class="single_fitres">
														<a href="shop-style-5.html" class="simple-button active me-1"><i class="ti-layout-grid2"></i></a>
														<a href="shop-list-sidebar.html" class="simple-button"><i class="ti-view-list"></i></a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<!-- row -->
							<div class="row align-items-center rows-products">
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/12.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color2" id="white2">
														<label class="form-option-label small rounded-circle" for="white2"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color2" id="blue2">
														<label class="form-option-label small rounded-circle" for="blue2"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color2" id="yellow2">
														<label class="form-option-label small rounded-circle" for="yellow2"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color2" id="pink2">
														<label class="form-option-label small rounded-circle" for="pink2"><span class="form-option-color rounded-circle blc4"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Formal Men Lowers</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/13.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color3" id="white3" checked="">
														<label class="form-option-label small rounded-circle" for="white3"><span class="form-option-color rounded-circle blc7"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color3" id="blue3">
														<label class="form-option-label small rounded-circle" for="blue3"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color3" id="yellow3">
														<label class="form-option-label small rounded-circle" for="yellow3"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color3" id="pink3">
														<label class="form-option-label small rounded-circle" for="pink3"><span class="form-option-color rounded-circle blc3"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Half Running Suit</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/14.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color4" id="white4">
														<label class="form-option-label small rounded-circle" for="white4"><span class="form-option-color rounded-circle blc8"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color4" id="blue4">
														<label class="form-option-label small rounded-circle" for="blue4"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color4" id="yellow4">
														<label class="form-option-label small rounded-circle" for="yellow4"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color4" id="pink4">
														<label class="form-option-label small rounded-circle" for="pink4"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Half Fancy Lady Dress</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
							
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-sale text-white position-absolute ft-regular ab-left text-upper">Sale</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/1.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color1" id="white" checked="">
														<label class="form-option-label small rounded-circle" for="white"><span class="form-option-color rounded-circle blc1"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color1" id="blue">
														<label class="form-option-label small rounded-circle" for="blue"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color1" id="yellow">
														<label class="form-option-label small rounded-circle" for="yellow"><span class="form-option-color rounded-circle blc3"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color1" id="pink">
														<label class="form-option-label small rounded-circle" for="pink"><span class="form-option-color rounded-circle blc4"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Half Running Set</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-new text-white position-absolute ft-regular ab-left text-upper">New</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
													<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/2.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color22" id="white22">
														<label class="form-option-label small rounded-circle" for="white22"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color22" id="blue22">
														<label class="form-option-label small rounded-circle" for="blue22"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color22" id="yellow22">
														<label class="form-option-label small rounded-circle" for="yellow22"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color22" id="pink22">
														<label class="form-option-label small rounded-circle" for="pink2"><span class="form-option-color rounded-circle blc4"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Formal Men Lowers</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/3.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color33" id="white33" checked="">
														<label class="form-option-label small rounded-circle" for="white33"><span class="form-option-color rounded-circle blc7"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color33" id="blue33">
														<label class="form-option-label small rounded-circle" for="blue33"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color33" id="yellow33">
														<label class="form-option-label small rounded-circle" for="yellow33"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color33" id="pink33">
														<label class="form-option-label small rounded-circle" for="pink33"><span class="form-option-color rounded-circle blc3"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Half Running Suit</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/4.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color44" id="white44">
														<label class="form-option-label small rounded-circle" for="white44"><span class="form-option-color rounded-circle blc8"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color44" id="blue44">
														<label class="form-option-label small rounded-circle" for="blue44"><span class="form-option-color rounded-circle blc2"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color44" id="yellow44">
														<label class="form-option-label small rounded-circle" for="yellow44"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color44" id="pink44">
														<label class="form-option-label small rounded-circle" for="pink44"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Half Fancy Lady Dress</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
													<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/5.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color55" id="white55">
														<label class="form-option-label small rounded-circle" for="white55"><span class="form-option-color rounded-circle blc8"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color55" id="blue55">
														<label class="form-option-label small rounded-circle" for="blue55"><span class="form-option-color rounded-circle blc5"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color55" id="yellow55">
														<label class="form-option-label small rounded-circle" for="yellow55"><span class="form-option-color rounded-circle blc3"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color55" id="pink55">
														<label class="form-option-label small rounded-circle" for="pink55"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Flix Flox Jeans</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Single -->
								<div class="col-xl-4 col-lg-4 col-md-6 col-6">
									<div class="product_grid card b-0">
										<div class="badge bg-hot text-white position-absolute ft-regular ab-left text-upper">Hot</div>
										<div class="card-body p-0">
											<div class="shop_thumb position-relative">
												<a class="card-img-top d-block overflow-hidden" href="shop-single-v1.html"><img class="card-img-top" src="{{ asset('frontend/images/product/6.jpg') }}" alt="..."></a>
												<div class="product-hover-overlay bg-dark d-flex align-items-center justify-content-center">
													<div class="edlio"><a href="#" data-bs-toggle="modal" data-bs-target="#quickview" class="text-white fs-sm ft-medium"><i class="fas fa-eye me-1"></i>Quick View</a></div>
												</div>
											</div>
										</div>
										<div class="card-footer b-0 p-0 pt-2 bg-white">
											<div class="d-flex align-items-start justify-content-between">
												<div class="text-left">
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color66" id="white66">
														<label class="form-option-label small rounded-circle" for="white66"><span class="form-option-color rounded-circle blc1"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color66" id="blue66">
														<label class="form-option-label small rounded-circle" for="blue66"><span class="form-option-color rounded-circle blc7"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color6" id="yellow66">
														<label class="form-option-label small rounded-circle" for="yellow66"><span class="form-option-color rounded-circle blc3"></span></label>
													</div>
													<div class="form-check form-option form-check-inline mb-1">
														<input class="form-check-input" type="radio" name="color66" id="pink66">
														<label class="form-option-label small rounded-circle" for="pink66"><span class="form-option-color rounded-circle blc6"></span></label>
													</div>
												</div>
												<div class="text-right">
													<button class="btn auto btn_love snackbar-wishlist"><i class="far fa-heart"></i></button> 
												</div>
											</div>
											<div class="text-left">
												<h5 class="fw-normal fs-md mb-0 lh-1 mb-1"><a href="shop-single-v1.html">Fancy Salwar Suits</a></h5>
												<div class="elis_rty"><span class="ft-medium text-dark fs-sm">$99 - $129</span></div>
											</div>
										</div>
									</div>
								</div>
								
							</div>
							<!-- row -->
							
							<div class="row">
								<div class="col-xl-12 col-lg-12 col-md-12 text-center">
									<a href="#" class="btn stretched-links borders m-auto"><i class="lni lni-reload me-2"></i>Load More</a>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</section>
			<!-- ======================= All Product List ======================== -->
@endsection