<!-- Product View Modal -->
<div class="modal fade" id="quickview" tabindex="-1" role="dialog" aria-labelledby="quickviewmodal" aria-hidden="true">
	<div class="modal-dialog modal-xl login-pop-form" role="document">
		<div class="modal-content" id="quickviewmodal">
			<div class="modal-headers">
				<button type="button" class="border-0 close" data-bs-dismiss="modal" aria-label="Close">
				  <span class="ti-close"></span>
				</button>
			  </div>
		
			<div class="modal-body">
				<div class="quick_view_wrap">
			
					<div class="quick_view_thmb">
						<div class="quick_view_slide" id="quickViewImages" style="display: none;">
							<!-- Images will be populated dynamically -->
						</div>
						<div id="quickViewImagesLoader" class="text-center py-5">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					
					<div class="quick_view_capt">
						<div class="prd_details"> 
							<div class="prt_01 mb-1" id="quickViewCategory"></div>
							<div class="prt_02 mb-2">
								<h2 class="ft-bold mb-1" id="quickViewTitle"></h2>
								<div class="text-left">
									<!-- <div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star"></i>
										<span class="small">(0 Reviews)</span>
									</div> -->
									<div class="elis_rty" id="quickViewPrice"></div>
								</div>
							</div>
							
							<div class="prt_03 mb-3">
								<p id="quickViewDescription"></p>
							</div>
							
							<div class="prt_04 mb-4" id="quickViewCategoryBrandSku" style="display: none;">
								<p class="d-flex align-items-center mb-1" id="quickViewCategoryInfo" style="display: none;">
									Category:<strong class="fs-sm text-dark ft-medium ms-1" id="quickViewCategoryText"></strong>
								</p>
								<p class="d-flex align-items-center mb-1" id="quickViewBrandInfo" style="display: none;">
									Brand:<strong class="fs-sm text-dark ft-medium ms-1" id="quickViewBrandText"></strong>
								</p>
								<p class="d-flex align-items-center mb-0">SKU:<strong class="fs-sm text-dark ft-medium ms-1" id="quickViewSku">—</strong></p>
								<div class="mt-2" id="quickViewMeasurementButton" style="display: none;">
									<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#measurementChartModal">
										<i class="fas fa-ruler me-1"></i>Measurement Chart
									</button>
								</div>
							</div>
							
							<div class="prt_04 mb-4" id="quickViewProductInfo" style="display: none;">
								<div class="single_search_boxed">
									<div class="widget-boxed-header">
										<h4><a href="#quickViewProductInfoCollapse" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed"><i class="ti-info me-2"></i>Product Info</a></h4>
									</div>
									<div class="widget-boxed-body collapse" id="quickViewProductInfoCollapse" data-parent="#quickViewProductInfoCollapse">
										<div class="side-list no-border">
											<div class="single_filter_card">
												<div class="card-body pt-0" id="quickViewHighlightsDetails">
													<!-- Highlights will be populated dynamically -->
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="prt_04 mb-2" id="quickViewColorsContainer" style="display: none;">
								<p class="d-flex align-items-center mb-0 text-dark ft-medium">Color:</p>
								<div class="text-left" id="quickViewColors">
									<!-- Colors will be populated dynamically -->
								</div>
							</div>
							
							<div class="prt_04 " id="quickViewSizesContainer" style="display: none;">
								<p class="d-flex align-items-center mb-0 text-dark ft-medium">Size:</p>
								<div class="text-left pb-0 pt-2" id="quickViewSizes">
									<!-- Sizes will be populated dynamically -->
								</div>
							</div>
							
							<div class="prt_05 mb-4">
								<div class="form-row row g-3 mb-7">
									<div class="col-12 col-md-6 col-lg-3">
										<!-- Quantity -->
										<select class="mb-2 custom-select" id="quickViewQuantity">
										  <option value="1" selected="">1</option>
										  <option value="2">2</option>
										  <option value="3">3</option>
										  <option value="4">4</option>
										  <option value="5">5</option>
										  <option value="10">10</option>
										</select>
									</div>
									<div class="col-12 col-md-12 col-lg-6">
										<!-- Submit -->
										<button type="submit" class="btn btn-block custom-height bg-dark mb-2 w-100" id="quickViewAddToCart" data-product-slug="">
											<i class="lni lni-shopping-basket me-2"></i>Add to Cart 
										</button>
										<!-- Stock Status Message -->
										<div id="quickViewStockStatusMessage" class="mt-2" style="display: none;">
											<small class="text-danger">
												<i class="lni lni-close me-1"></i><span id="quickViewStockStatusText">Out of Stock</span>
											</small>
										</div>
									</div>
									<div class="col-12 col-md-6 col-lg-3">
										<!-- Wishlist -->
										<button class="btn custom-height btn-default btn-block mb-2 text-dark" id="quickViewWishlist" data-product-id="" type="button">
											<i class="lni lni-heart me-2"></i>Wishlist
										</button>
									</div>
							  </div>
							</div>
							
							<div class="prt_06">
								<p class="mb-0 d-flex align-items-center">
								  <span class="me-4">Share:</span>
								  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted me-2" href="#!" id="quickViewShareTwitter">
									<i class="fab fa-twitter position-absolute"></i>
								  </a>
								  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted me-2" href="#!" id="quickViewShareFacebook">
									<i class="fab fa-facebook-f position-absolute"></i>
								  </a>
								  <a class="d-inline-flex align-items-center justify-content-center p-3 gray circle fs-sm text-muted" href="#!" id="quickViewSharePinterest">
									<i class="fab fa-pinterest-p position-absolute"></i>
								  </a>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->

<!-- Log In Modal -->
<div class="modal fade" id="login" tabindex="-1" role="dialog" aria-labelledby="loginmodal" aria-hidden="true">
	<div class="modal-dialog login-pop-form" role="document">
		<div class="modal-content" id="loginmodal">
			<div class="modal-headers">
				<button type="button" class="border-0 close" data-bs-dismiss="modal" aria-label="Close">
				  <span class="ti-close"></span>
				</button>
			  </div>
		
			<div class="modal-body p-5">
				<div class="text-center mb-4">
					<h2 class="m-0 ft-regular">Login</h2>
				</div>
				
				<!-- Error/Success Messages -->
				<div id="customerLoginMessage" style="display: none;"></div>
				
				<form id="customerLoginForm">				
					<div class="form-group mb-3">
						<label class="mb-2">Email or Phone</label>
						<input type="text" id="customerLoginEmailOrPhone" name="email_or_phone" class="form-control" placeholder="Email or Phone*" required>
						<div class="invalid-feedback"></div>
					</div>
					
					<div class="form-group mb-3">
						<label class="mb-2">Password</label>
						<div class="position-relative">
							<input type="password" id="customerLoginPassword" name="password" class="form-control pe-5" placeholder="Password*" required>
							<button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted password-toggle" data-target="#customerLoginPassword">
								<i class="fas fa-eye"></i>
							</button>
						</div>
						<div class="invalid-feedback"></div>
					</div>
					
					<div class="form-group mb-3">
						<div class="d-flex align-items-center justify-content-between">
							<div class="flex-1">
								<input id="customerRememberMe" class="checkbox-custom" name="remember" type="checkbox">
								<label for="customerRememberMe" class="checkbox-custom-label">Remember Me</label>
							</div>	
							<div class="eltio_k2">
								<a href="#">Lost Your Password?</a>
							</div>	
						</div>
					</div>
					
					<div class="form-group mb-3">
						<button type="submit" id="customerLoginBtn" class="btn btn-md full-width bg-dark text-light fs-md ft-medium">
							<span id="customerLoginBtnText">Login</span>
							<span id="customerLoginBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
						</button>
					</div>
					
					<div class="form-group text-center mb-0">
						<p class="extra">Not a member?<a href="#" class="text-dark" data-bs-toggle="modal" data-bs-target="#register" data-bs-dismiss="modal"> Register</a></p>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->

<!-- Register Modal -->
<div class="modal fade" id="register" tabindex="-1" role="dialog" aria-labelledby="registermodal" aria-hidden="true">
	<div class="modal-dialog login-pop-form modal-lg" role="document">
		<div class="modal-content" id="registermodal">
			<div class="modal-headers">
				<button type="button" class="border-0 close" data-bs-dismiss="modal" aria-label="Close">
				  <span class="ti-close"></span>
				</button>
			  </div>
		
			<div class="modal-body p-5">
				<div class="text-center mb-4">
					<h2 class="m-0 ft-regular">Register</h2>
				</div>
				
				<!-- Error/Success Messages -->
				<div id="customerRegisterMessage" style="display: none;"></div>
				
				<form id="customerRegisterForm">
					<!-- Dynamic fields will be loaded here -->
					<div id="customerRegisterFields">
						<div class="text-center py-3">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					
					<div class="form-group mb-3" id="customerRegisterSubmitSection" style="display: none;">
						<button type="submit" id="customerRegisterBtn" class="btn btn-md full-width bg-dark text-light fs-md ft-medium">
							<span id="customerRegisterBtnText">Register</span>
							<span id="customerRegisterBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
						</button>
					</div>
					
					<div class="form-group text-center mb-0" id="customerRegisterLoginSection" style="display: none;">
						<p class="extra">Already a member?<a href="#" class="text-dark" data-bs-toggle="modal" data-bs-target="#login" data-bs-dismiss="modal"> Login</a></p>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->

<!-- Wishlist -->
<div class="w3-ch-sideBar w3-bar-block w3-card-2 w3-animate-right" style="display:none;right:0;" id="Wishlist">
	<div class="rightMenu-scroll">
		<div class="d-flex align-items-center justify-content-between slide-head py-3 px-3">
			<h4 class="cart_heading fs-md ft-medium mb-0">Saved Products</h4>
			<button onclick="closeWishlist()" class="close_slide"><i class="ti-close"></i></button>
		</div>
		<div class="right-ch-sideBar">
			
			<div class="cart_select_items py-2">
				<!-- Sample items - will be dynamic later -->
				<div class="d-flex align-items-center justify-content-between br-bottom px-3 py-3">
					<div class="cart_single d-flex align-items-center">
						<div class="cart_selected_single_thumb">
							<a href="#"><img src="{{ asset('frontend/images/1.jpg') }}" width="60" class="img-fluid" alt="" /></a>
						</div>
						<div class="cart_single_caption ps-2">
							<h4 class="product_title fs-sm ft-medium mb-0 lh-1">Sample Product</h4>
							<p class="mb-2"><span class="text-dark ft-medium small">36</span>, <span class="text-dark small">Red</span></p>
							<h4 class="fs-md ft-medium mb-0 lh-1">₹129</h4>
						</div>
					</div>
					<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
				</div>
			</div>
			
			<div class="d-flex align-items-center justify-content-between br-top br-bottom px-3 py-3">
				<h6 class="mb-0">Subtotal</h6>
				<h3 class="mb-0 ft-medium">₹0</h3>
			</div>
			
			<div class="cart_action px-3 py-3">
				<div class="form-group mb-3">
					<a href="{{ route('frontend.shoping-cart') }}" class="btn d-block full-width btn-dark">Move To Cart</a>
				</div>
				<div class="form-group">
					<a href="{{ route('frontend.wishlist') }}" class="btn d-block full-width btn-dark-light">Edit or View</a>
				</div>
			</div>
			
		</div>
	</div>
</div>

<!-- Cart -->
<div class="w3-ch-sideBar w3-bar-block w3-card-2 w3-animate-right" style="display:none;right:0;" id="Cart">
	<div class="rightMenu-scroll">
		<div class="d-flex align-items-center justify-content-between slide-head py-3 px-3">
			<h4 class="cart_heading fs-md ft-medium mb-0">Products List</h4>
			<button onclick="closeCart()" class="close_slide"><i class="ti-close"></i></button>
		</div>
		<div class="right-ch-sideBar">
			
			<div class="cart_select_items py-2">
				<!-- Sample items - will be dynamic later -->
				<div class="d-flex align-items-center justify-content-between br-bottom px-3 py-3">
					<div class="cart_single d-flex align-items-center">
						<div class="cart_selected_single_thumb">
							<a href="#"><img src="{{ asset('frontend/images/1.jpg') }}" width="60" class="img-fluid" alt="" /></a>
						</div>
						<div class="cart_single_caption ps-2">
							<h4 class="product_title fs-sm ft-medium mb-0 lh-1">Sample Product</h4>
							<p class="mb-2"><span class="text-dark ft-medium small">36</span>, <span class="text-dark small">Red</span></p>
							<h4 class="fs-md ft-medium mb-0 lh-1">₹129</h4>
						</div>
					</div>
					<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
				</div>
			</div>
			
			<div class="d-flex align-items-center justify-content-between br-top br-bottom px-3 py-3">
				<h6 class="mb-0">Subtotal</h6>
				<h3 class="mb-0 ft-medium">₹0</h3>
			</div>
			
			<div class="cart_action px-3 py-3">
				<div class="form-group mb-3">
					@auth
					<a href="{{ route('frontend.checkout') }}" class="btn d-block full-width btn-dark">Checkout Now</a>
					@else
					<a href="#" class="btn d-block full-width btn-dark" data-bs-toggle="modal" data-bs-target="#login" onclick="closeCart();">Checkout Now</a>
					@endauth
				</div>
				<div class="form-group">
					<a href="{{ route('frontend.shoping-cart') }}" class="btn d-block full-width btn-dark-light">Edit or View</a>
				</div>
			</div>
			
		</div>
	</div>
</div>

<!-- Measurement Chart Modal -->
<div class="modal fade" id="measurementChartModal" tabindex="-1" role="dialog" aria-labelledby="measurementChartModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="measurementChartModalLabel">Measurement Chart</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div id="measurementChartContent">
					<div class="text-center py-4">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
			</div> 
		</div>
	</div>
</div>
<!-- End Measurement Chart Modal -->

@push('styles')
<style>
    /* Quick View Modal - Scrollable */
    #quickview .modal-body {
        max-height: 90vh;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Quick View Modal Image Container */
    .quick_view_thmb {
        width: 100%;
        min-height: 500px;
        height: auto;
        max-height: 600px;
        overflow: hidden;
        background: #f8f9fa;
        position: relative;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    /* Quick View Images Slider Container - Centered */
    #quickViewImages {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quick_view_slide {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quick_view_slide .single_view_slide {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .quick_view_slide .single_view_slide img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
        margin: 0 auto;
    }

    /* Slick slider adjustments - Centered */
    #quickViewImages.slick-slider {
        width: 100%;
        height: 100%;
    }

    #quickViewImages.slick-slider .slick-slide {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #quickViewImages.slick-slider .slick-slide > div {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ensure single_view_slide within slick has proper dimensions and centering */
    #quickViewImages.slick-slider .slick-slide .single_view_slide {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    #quickViewImages.slick-slider .slick-slide .single_view_slide img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
    }

    #quickViewWishlist.active,
    #quickViewWishlist.active i,
    #quickViewWishlist.active .fa-heart {
        color: #dc3545 !important;
    }
    #quickViewWishlist.active {
        border-color: #dc3545 !important;
    }

    /* Ensure modal content doesn't overflow */
    #quickview .modal-content {
        max-height: 95vh;
    }

    #quickview .quick_view_wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    @media (max-width: 768px) {
        #quickview .modal-body {
            max-height: 85vh;
        }
        
        .quick_view_thmb {
            width: 100%;
        }
        
        #quickview .quick_view_wrap {
            flex-direction: column;
        }
    }

    /* Ensure proper layout on larger screens */
    @media (min-width: 769px) {
        .quick_view_thmb {
            flex: 0 0 45%;
            max-width: 45%;
        }
        
        .quick_view_capt {
            flex: 0 0 calc(55% - 20px);
            max-width: calc(55% - 20px);
        }
    }

    /* Password toggle button styles */
    .password-toggle {
        border: none !important;
        background: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        width: auto !important;
        height: auto !important;
        z-index: 10;
    }

    .password-toggle:hover {
        background: none !important;
        color: #495057 !important;
    }

    .password-toggle:focus {
        box-shadow: none !important;
        outline: none !important;
    }

    .password-toggle i {
        font-size: 14px;
        transition: color 0.2s ease;
    }

    /* Ensure password input has proper padding for the eye icon */
    .position-relative input.pe-5 {
        padding-right: 2.5rem !important;
    }

    /* Modal responsive improvements */
    @media (max-width: 767px) {
        .modal-dialog.login-pop-form {
            margin: 1rem;
        }
        
        .modal-body {
            padding: 1.5rem !important;
        }
    }

    /* Enhanced modal width for registration */
    @media (min-width: 768px) {
        .modal-dialog.login-pop-form.modal-lg {
            max-width: 600px;
            width: 90%;
        }
    }

    @media (min-width: 992px) {
        .modal-dialog.login-pop-form.modal-lg {
            max-width: 700px;
            width: 80%;
        }
    }

    /* Registration form field spacing */
    #customerRegisterFields .form-group {
        margin-bottom: 1rem;
    }

    #customerRegisterFields .row {
        margin-bottom: 0.5rem;
    }

    /* Form validation styles */
    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Success state for valid fields */
    .form-control.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .form-control.is-valid:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
</style>
@endpush

@push('scripts')
{{-- Include cart pricing JavaScript helper --}}
@include('frontend.partials.product-pricing-cart-js')
<script>
$(document).ready(function() {
    let currentProductData = null;
    let selectedColor = null;
    let selectedSize = null;
    
    // Function to clear all old attribute containers and reset state
    function clearQuickViewState() {
        // Clear all dynamically created attribute containers
        $('[id^="qv_attr_"][id$="_container"]').remove();
        
        // Reset all state variables
        currentProductData = null;
        selectedColor = null;
        selectedSize = null;
        window.selectedAttributeValues = {};
        
        // Clear all old content
        $('#quickViewCategory').html('');
        $('#quickViewTitle').text('');
        $('#quickViewDescription').html('');
        $('#quickViewImages').html('');
        $('#quickViewPrice').html('');
        $('#quickViewSku').text('');
        $('#quickViewHighlights').html('');
        
        // Clear and hide legacy containers
        $('#quickViewColors').html('');
        $('#quickViewSizes').html('');
        $('#quickViewColorsContainer, #quickViewSizesContainer').hide();
        
        // Remove all checked states from any remaining radio buttons
        $('.qv-attribute-option, .qv-color-option, .qv-size-option').prop('checked', false);
    }
    
    // Handle Quick View click
    $(document).on('click', 'a.quick-view-btn[data-product-slug]', function(e) {
        e.preventDefault();
        const productSlug = $(this).data('product-slug');
        const productIndex = $(this).data('product-index');
        
        // Clear all previous state first
        clearQuickViewState();
        
        // Get currently selected color from the card
        let cardSelectedColor = $(this).data('selected-color') || null;
        if (productIndex !== undefined) {
            const checkedColorInput = $('input[name="color' + (parseInt(productIndex) + 1) + '"]:checked');
            if (checkedColorInput.length) {
                // Get color value from data attribute
                const colorValue = checkedColorInput.data('color-value');
                if (colorValue) {
                    cardSelectedColor = colorValue;
                }
            }
        }
        
        if (!productSlug) {
            console.error('Product slug not found');
            return;
        }
        
        // Show loader initially and hide slider completely
        $('#quickViewImagesLoader').show();
        $('#quickViewImages').hide().html('');
        
        // Open modal first - use Bootstrap 5 API if available, otherwise fallback to jQuery
        try {
            const modalElement = document.getElementById('quickview');
            if (modalElement) {
                // Try Bootstrap 5 API first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    // Use getOrCreateInstance to avoid conflicts (consistent with frontend.blade.php)
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    // Only show if not already shown
                    if (!modalElement.classList.contains('show')) {
                        modal.show();
                    }
                } else if (typeof $.fn.modal !== 'undefined') {
                    // Fallback to jQuery/Bootstrap 4
                    if (!$('#quickview').hasClass('show')) {
                        $('#quickview').modal('show');
                    }
                } else {
                    // Last resort: show via CSS
                    if (!$(modalElement).hasClass('show')) {
                        $(modalElement).addClass('show').css('display', 'block');
                        $('body').addClass('modal-open');
                        $('.modal-backdrop').remove();
                        $('body').append('<div class="modal-backdrop fade show"></div>');
                    }
                }
            }
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback: show via CSS
            const modalElement = document.getElementById('quickview');
            if (modalElement && !$(modalElement).hasClass('show')) {
                $(modalElement).addClass('show').css('display', 'block');
                $('body').addClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').append('<div class="modal-backdrop fade show"></div>');
            }
        }
        
        // Fetch product data
        $.ajax({
            url: '{{ route("frontend.product.quickview") }}',
            method: 'GET',
            data: { slug: productSlug },
            success: function(response) {
                if (response.success && response.data) {
                    populateQuickView(response.data, cardSelectedColor);
                } else {
                    console.error('Product not found');
                    $('#quickViewImagesLoader').hide();
                }
            },
            error: function(xhr) {
                console.error('Error loading product:', xhr);
                $('#quickViewImagesLoader').hide();
            }
        });
    });
    
    function populateQuickView(product, initialColor) {
        // Reset all state variables for fresh start
        currentProductData = product;
        selectedColor = null;
        selectedSize = null;
        window.selectedAttributeValues = {};
        
        // Category - Show badge same as product page
        if (product.category) {
            $('#quickViewCategory').html('<span class="text-success bg-light-success rounded px-2 py-1">' + product.category + '</span>');
        } else {
            $('#quickViewCategory').html('');
        }
        
        // Category, Brand, SKU Info
        if (product.category || product.brand) {
            $('#quickViewCategoryBrandSku').show();
            
            // Category
            if (product.category) {
                let categoryText = product.category;
                if (product.parent_category) {
                    categoryText = product.category + ', ' + product.parent_category;
                }
                $('#quickViewCategoryText').text(categoryText);
                $('#quickViewCategoryInfo').show();
            } else {
                $('#quickViewCategoryInfo').hide();
            }
            
            // Brand
            if (product.brand) {
                $('#quickViewBrandText').text(product.brand);
                $('#quickViewBrandInfo').show();
            } else {
                $('#quickViewBrandInfo').hide();
            }
        } else {
            $('#quickViewCategoryBrandSku').hide();
        }
        
        // Title
        $('#quickViewTitle').text(product.name);
        
        // Description
        $('#quickViewDescription').html(product.description || 'No description available');
        
        // Hide legacy containers
        $('#quickViewColorsContainer, #quickViewSizesContainer').hide();
        
        // Render all attributes dynamically first
        if (product.attributes && product.attributes.length > 0) {
            // Try to use initialColor if provided and matches an attribute value
            let initialColorMatched = false;
            
            // Create containers for each attribute
            product.attributes.forEach(function(attribute, attrIndex) {
                const attributeSlug = attribute.slug || attribute.name.toLowerCase().replace(/\s+/g, '-');
                const attributeName = attribute.name;
                const attributeType = attribute.type || 'text';
                const containerId = 'qv_attr_' + attributeSlug + '_container';
                const optionsId = 'qv_attr_' + attributeSlug + '_options';
                
                // Check if container already exists, if not create it
                let $container = $('#' + containerId);
                if ($container.length === 0) {
                    // Insert before quantity selector
                    const containerHtml = '<div class="prt_04 " id="' + containerId + '">' +
                        '<p class="d-flex align-items-center mb-0 text-dark ft-medium">' + attributeName + ':</p>' +
                        '<div class="text-left pb-0 pt-2" id="' + optionsId + '"></div>' +
                        '</div>';
                    $('#quickViewSizesContainer').before(containerHtml);
                    $container = $('#' + containerId);
                }
                
                // Check if initialColor should be used for this attribute
                let shouldUseInitialColor = false;
                if (initialColor && !initialColorMatched && (attributeType === 'color' || attributeSlug === 'color')) {
                    // Check if initialColor matches any value in this attribute
                    const matchingValue = attribute.values.find(function(v) {
                        const val = v.value || v;
                        return String(val).toLowerCase() === String(initialColor).toLowerCase();
                    });
                    if (matchingValue) {
                        shouldUseInitialColor = true;
                        initialColorMatched = true;
                    }
                }
                
                let optionsHtml = '';
                attribute.values.forEach(function(valueData, valueIndex) {
                    const value = valueData.value || valueData;
                    const valueId = 'qv_' + attributeSlug + '_' + Date.now() + '_' + valueIndex;
                    
                    // Determine if this option should be selected
                    let isSelected = false;
                    if (shouldUseInitialColor && String(value).toLowerCase() === String(initialColor).toLowerCase()) {
                        isSelected = true;
                    } else if (!shouldUseInitialColor && valueIndex === 0) {
                        isSelected = true; // Select first by default if no initial color match
                    }
                    
                    if (attributeType === 'color') {
                        const colorCode = valueData.color_code || '#ccc';
                        const blcClass = 'blc' + ((valueIndex % 8) + 1);
                        optionsHtml += '<div class="form-check form-option form-check-inline mb-1">' +
                            '<input class="form-check-input qv-attribute-option" type="radio" name="qv_attr_' + attributeSlug + '" id="' + valueId + '" ' + (isSelected ? 'checked' : '') + 
                            ' data-attribute-id="' + attribute.id + '" data-attribute-name="' + attributeName + '" data-attribute-slug="' + attributeSlug + '" data-attribute-type="' + attributeType + '" data-value="' + value + '">' +
                            '<label class="form-option-label rounded-circle" for="' + valueId + '">' +
                            '<span class="form-option-color rounded-circle ' + blcClass + '" style="background-color: ' + colorCode + '"></span>' +
                            '</label>' +
                            '</div>';
                    } else {
                        optionsHtml += '<div class="form-check size-option form-option form-check-inline mb-2">' +
                            '<input class="form-check-input qv-attribute-option" type="radio" name="qv_attr_' + attributeSlug + '" id="' + valueId + '" ' + (isSelected ? 'checked' : '') + 
                            ' data-attribute-id="' + attribute.id + '" data-attribute-name="' + attributeName + '" data-attribute-slug="' + attributeSlug + '" data-attribute-type="' + attributeType + '" data-value="' + value + '">' +
                            '<label class="form-option-label" for="' + valueId + '">' + value + '</label>' +
                            '</div>';
                    }
                    
                    // Store initial selected value
                    if (isSelected) {
                        window.selectedAttributeValues[attribute.id] = value;
                        // Legacy support for color/size
                        if (attributeType === 'color' || attributeSlug === 'color') {
                            selectedColor = value;
                        }
                        if (attributeSlug === 'size') {
                            selectedSize = value;
                        }
                    }
                });
                
                $('#' + optionsId).html(optionsHtml);
                $container.show();
            });
        } else {
            // Fallback to legacy color/size support
            // Colors
            if (product.colors && product.colors.length > 0) {
                let colorsHtml = '';
                product.colors.forEach(function(color, index) {
                    const colorId = 'qv_color_' + Date.now() + '_' + index;
                    const isSelected = (selectedColor && selectedColor.toLowerCase() === color.toLowerCase()) || (!selectedColor && index === 0);
                    const colorVariant = product.color_variants && product.color_variants[color] ? product.color_variants[color] : null;
                    const colorCode = colorVariant && colorVariant.color_code ? colorVariant.color_code : '#ccc';
                    const blcClass = 'blc' + ((index % 8) + 1);
                    
                    colorsHtml += '<div class="form-check form-option form-check-inline mb-1">' +
                        '<input class="form-check-input qv-color-option" type="radio" name="qv_color" id="' + colorId + '" ' + (isSelected ? 'checked' : '') + ' data-color="' + color + '">' +
                        '<label class="form-option-label rounded-circle" for="' + colorId + '">' +
                        '<span class="form-option-color rounded-circle ' + blcClass + '" style="background-color: ' + colorCode + '"></span>' +
                        '</label>' +
                        '</div>';
                });
                $('#quickViewColors').html(colorsHtml);
                $('#quickViewColorsContainer').show();
                
                if (selectedColor) {
                    $('.qv-color-option[data-color="' + selectedColor + '"]').prop('checked', true);
                }
            } else {
                $('#quickViewColorsContainer').hide();
            }
            
            // Sizes
            if (product.sizes && product.sizes.length > 0) {
                let sizesHtml = '';
                product.sizes.forEach(function(size, index) {
                    const sizeId = 'qv_size_' + Date.now() + '_' + index;
                    const isSelected = (selectedSize && selectedSize === size) || (!selectedSize && index === 0);
                    sizesHtml += '<div class="form-check size-option form-option form-check-inline mb-2">' +
                        '<input class="form-check-input qv-size-option" type="radio" name="qv_size" id="' + sizeId + '" ' + (isSelected ? 'checked' : '') + ' data-size="' + size + '">' +
                        '<label class="form-option-label" for="' + sizeId + '">' + size + '</label>' +
                        '</div>';
                });
                $('#quickViewSizes').html(sizesHtml);
                $('#quickViewSizesContainer').show();
                
                if (selectedSize) {
                    $('.qv-size-option[data-size="' + selectedSize + '"]').prop('checked', true);
                }
            } else {
                $('#quickViewSizesContainer').hide();
            }
        }
        
        // Update price, SKU, highlights and images based on selected variant (after attributes are rendered)
        updateQuickViewVariant();
        updateQuickViewImages();
        
        // Add event listeners for all attribute changes (dynamic)
        $(document).off('change', '.qv-attribute-option').on('change', '.qv-attribute-option', function() {
            if ($(this).is(':checked')) {
                const attributeId = $(this).data('attribute-id');
                const attributeSlug = $(this).data('attribute-slug');
                const value = $(this).data('value');
                
                // Update selected attribute value
                window.selectedAttributeValues[attributeId] = value;
                
                // Legacy support for color/size
                if ($(this).data('attribute-type') === 'color' || attributeSlug === 'color') {
                    selectedColor = value;
                }
                if (attributeSlug === 'size') {
                    selectedSize = value;
                }
                
                updateQuickViewVariant();
                updateQuickViewImages();
            }
        });
        
        // Legacy event listeners for color and size (backward compatibility)
        $(document).off('change', '.qv-color-option').on('change', '.qv-color-option', function() {
            if ($(this).is(':checked')) {
                selectedColor = $(this).data('color');
                
                if (currentProductData && currentProductData.variants) {
                    const colorVariant = currentProductData.variants.find(function(v) {
                        return v.color && v.color.toLowerCase() === selectedColor.toLowerCase();
                    });
                    if (colorVariant && colorVariant.size) {
                        selectedSize = colorVariant.size;
                        $('.qv-size-option[data-size="' + selectedSize + '"]').prop('checked', true);
                    }
                }
                
                updateQuickViewVariant();
                updateQuickViewImages();
            }
        });
        
        $(document).off('change', '.qv-size-option').on('change', '.qv-size-option', function() {
            if ($(this).is(':checked')) {
                selectedSize = $(this).data('size');
                updateQuickViewVariant();
            }
        });
        
        // Initialize SKU and Highlights on load
        // Note: updateQuickViewVariant() was already called at line 941 and it updates
        // the add to cart button internally, so no need to call it again here
        updateQuickViewVariant();
        
        // Update measurement button visibility
        updateQuickViewMeasurementButton();
        
        // Update buttons
        $('#quickViewAddToCart').attr('data-product-slug', product.slug);
        $('#quickViewWishlist').attr('data-product-id', product.id);
        
        // Check if product is in wishlist and update button state
        checkWishlistStatus(product.id);
        
        // Add wishlist button click handler
        $('#quickViewWishlist').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            
            // Prevent multiple clicks while processing
            if ($btn.data('processing')) {
                return false;
            }
            
            const productId = $btn.data('product-id');
            if (!productId) return false;
            
            // Set processing flag
            $btn.data('processing', true);
            
            // Get session ID
            let sessionId = localStorage.getItem('session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('session_id', sessionId);
            }
            
            // Check current state before making request
            const isActive = $btn.hasClass('active');
            
            if (isActive) {
                // Remove from wishlist
                $.ajax({
                    url: '/api/wishlist/product/' + productId,
                    method: 'DELETE',
                    data: { session_id: sessionId },
                    success: function(response) {
                        if (response.success) {
                            $btn.removeClass('active');
                            const $icon = $btn.find('i');
                            $icon.removeClass('fas fa-heart').addClass('lni lni-heart').css('color', '');
                            $btn.removeClass('text-danger').css('color', '');
                            
                            // Only show message if product was actually removed
                            // If message says "Product not in wishlist", don't show any message (already in desired state)
                            if (response.message && !response.message.includes('not in wishlist')) {
                                if (typeof Snackbar !== 'undefined') {
                                    Snackbar.show({
                                        text: response.message || 'Product removed from wishlist',
                                        pos: 'top-right',
                                        showAction: false,
                                        duration: 3000,
                                        textColor: '#fff',
                                        backgroundColor: '#151515'
                                    });
                                }
                            }
                            updateWishlistCount();
                        }
                        // Clear processing flag
                        $btn.data('processing', false);
                    },
                    error: function(xhr) {
                        // Clear processing flag
                        $btn.data('processing', false);
                        
                        // Even if product not found, treat as success (idempotent)
                        if (xhr.status === 404 || (xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('not in wishlist'))) {
                            $btn.removeClass('active text-danger');
                            const $icon = $btn.find('i');
                            $icon.removeClass('fas fa-heart').addClass('lni lni-heart').css('color', '');
                            $btn.css('color', '');
                            updateWishlistCount();
                        } else {
                            console.error('Error removing from wishlist:', xhr);
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({
                                    text: 'Failed to remove from wishlist',
                                    pos: 'top-right',
                                    showAction: false,
                                    duration: 3000,
                                    textColor: '#fff',
                                    backgroundColor: '#dc3545'
                                });
                            }
                        }
                    }
                });
            } else {
                // Add to wishlist
                $.ajax({
                    url: '/api/wishlist',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        session_id: sessionId
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.addClass('active text-danger');
                            const $icon = $btn.find('i');
                            $icon.removeClass('lni lni-heart').addClass('fas fa-heart').css('color', '#dc3545');
                            $btn.css('color', '#dc3545');
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({
                                    text: 'Product added to wishlist successfully!',
                                    pos: 'top-right',
                                    showAction: false,
                                    duration: 3000,
                                    textColor: '#fff',
                                    backgroundColor: '#151515'
                                });
                            }
                            updateWishlistCount();
                        }
                        // Clear processing flag
                        $btn.data('processing', false);
                    },
                    error: function(xhr) {
                        // Clear processing flag
                        $btn.data('processing', false);
                        
                        console.error('Error adding to wishlist:', xhr);
                        const message = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'Failed to add to wishlist';
                        if (typeof Snackbar !== 'undefined') {
                            Snackbar.show({
                                text: message,
                                pos: 'top-right',
                                showAction: false,
                                duration: 3000,
                                textColor: '#fff',
                                backgroundColor: '#dc3545'
                            });
                        }
                    }
                });
            }
            
            return false;
        });
        
        // Add to cart button click handler
        $('#quickViewAddToCart').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            
            // Prevent multiple clicks while processing
            if ($btn.data('processing')) {
                return false;
            }
            
            const productId = product.id;
            if (!productId) {
                console.error('Product ID not found');
                return false;
            }
            
            // Set processing flag
            $btn.data('processing', true);
            $btn.prop('disabled', true);
            
            // Get selected variant using the same logic as updateQuickViewVariant
            let matchingVariant = null;
            if (currentProductData && currentProductData.variants) {
                // Use dynamic attribute matching if available
                if (window.selectedAttributeValues && Object.keys(window.selectedAttributeValues).length > 0) {
                    matchingVariant = currentProductData.variants.find(function(variant) {
                        if (!variant.attributes || !Array.isArray(variant.attributes)) {
                            return false;
                        }
                        
                        const selectedCount = Object.keys(window.selectedAttributeValues).length;
                        if (variant.attributes.length !== selectedCount) {
                            return false;
                        }
                        
                        let allMatch = true;
                        for (let attrId in window.selectedAttributeValues) {
                            const selectedValue = window.selectedAttributeValues[attrId];
                            const variantAttr = variant.attributes.find(function(attr) {
                                return String(attr.attribute_id) === String(attrId) && String(attr.value) === String(selectedValue);
                            });
                            if (!variantAttr) {
                                allMatch = false;
                                break;
                            }
                        }
                        return allMatch;
                    });
                }
                
                // Fallback to legacy color/size matching
                if (!matchingVariant) {
                    matchingVariant = currentProductData.variants.find(function(variant) {
                        const colorMatch = !selectedColor || (variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase());
                        const sizeMatch = !selectedSize || variant.size === selectedSize;
                        return colorMatch && sizeMatch;
                    });
                }
                
                // If still no match, use first available variant
                if (!matchingVariant && currentProductData.variants.length > 0) {
                    matchingVariant = currentProductData.variants[0];
                }
            }
            
            const variantId = matchingVariant ? matchingVariant.id : null;
            
            // Check if variant is in stock
            if (!matchingVariant) {
                alert('Please select all required attributes (size, color, etc.)');
                $btn.data('processing', false);
                $btn.prop('disabled', false);
                return false;
            }
            
            const isInStock = matchingVariant.is_in_stock !== false && matchingVariant.is_in_stock !== undefined;
            if (!isInStock) {
                alert('This product is currently out of stock.');
                $btn.data('processing', false);
                $btn.prop('disabled', false);
                return false;
            }
            
            // Get quantity
            const quantitySelect = $('#quickViewQuantity');
            const quantity = quantitySelect ? parseInt(quantitySelect.val()) : 1;
            
            // Use common add to cart function if available, otherwise make direct call
            if (window.addToCart) {
                window.addToCart(productId, variantId, quantity, function(success) {
                    $btn.data('processing', false);
                    $btn.prop('disabled', false);
                    // Cart count will be updated by addToCart function
                });
            } else {
                // Fallback: direct API call
                let sessionId = localStorage.getItem('session_id');
                if (!sessionId) {
                    sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('session_id', sessionId);
                }
                
                $.ajax({
                    url: '/api/cart/items',
                    method: 'POST',
                    headers: {
                        'X-Session-ID': sessionId
                    },
                    data: {
                        product_id: productId,
                        product_variant_id: variantId || null,
                        quantity: quantity,
                        session_id: sessionId
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({
                                    text: 'Product added to cart successfully!',
                                    pos: 'top-right',
                                    showAction: false,
                                    duration: 3000,
                                    textColor: '#fff',
                                    backgroundColor: '#151515'
                                });
                            }
                            // Update cart count in header
                            if (typeof updateCartCount === 'function') {
                                updateCartCount();
                            } else if (window.updateCartCount) {
                                window.updateCartCount();
                            }
                        }
                        $btn.data('processing', false);
                        $btn.prop('disabled', false);
                    },
                    error: function(xhr) {
                        $btn.data('processing', false);
                        $btn.prop('disabled', false);
                        const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message 
                            ? xhr.responseJSON.error.message 
                            : 'Failed to add product to cart';
                        if (typeof Snackbar !== 'undefined') {
                            Snackbar.show({
                                text: message,
                                pos: 'top-right',
                                showAction: false,
                                duration: 3000,
                                textColor: '#fff',
                                backgroundColor: '#dc3545'
                            });
                        }
                    }
                });
            }
            
            return false;
        });
        
        // Update share links
        const productUrl = window.location.origin + '/product?product=' + product.slug;
        $('#quickViewShareTwitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(productUrl) + '&text=' + encodeURIComponent(product.name));
        $('#quickViewShareFacebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(productUrl));
        $('#quickViewSharePinterest').attr('href', 'https://pinterest.com/pin/create/button/?url=' + encodeURIComponent(productUrl) + '&description=' + encodeURIComponent(product.name));
    }
    
    function updateQuickViewVariant() {
        if (!currentProductData) return;
        
        // Find matching variant based on all selected attributes
        let matchingVariant = null;
        if (currentProductData.variants) {
            matchingVariant = currentProductData.variants.find(function(variant) {
                // If variant has attributes array (new format)
                if (variant.attributes && Array.isArray(variant.attributes)) {
                    // Check if all selected attributes match
                    let allMatch = true;
                    const selectedCount = Object.keys(window.selectedAttributeValues || {}).length;
                    
                    // If no attributes selected, don't match
                    if (selectedCount === 0) {
                        return false;
                    }
                    
                    // Check if all selected attributes match
                    for (let attrId in window.selectedAttributeValues) {
                        const selectedValue = window.selectedAttributeValues[attrId];
                        const variantAttr = variant.attributes.find(function(attr) {
                            // Compare both attribute_id and value as strings
                            // Also handle dynamic attributes (dynamic_length, etc.)
                            const attrIdMatch = String(attr.attribute_id) === String(attrId) || 
                                               String(attr.attribute_name) === String(attrId) ||
                                               String(attr.attribute_slug) === String(attrId) ||
                                               (attrId.startsWith('dynamic_') && String(attr.attribute_name).toLowerCase() === attrId.replace('dynamic_', '').toLowerCase()) ||
                                               (attrId.startsWith('dynamic_') && String(attr.attribute_slug).toLowerCase() === attrId.replace('dynamic_', '').toLowerCase());
                            const valueMatch = String(attr.value) === String(selectedValue);
                            return attrIdMatch && valueMatch;
                        });
                        if (!variantAttr) {
                            allMatch = false;
                            break;
                        }
                    }
                    // Also check that we have at least one attribute match if attributes are selected
                    if (allMatch && Object.keys(window.selectedAttributeValues).length > 0 && variant.attributes.length === 0) {
                        allMatch = false;
                    }
                    return allMatch;
                } else {
                    // Legacy: match by color and size
                    const colorMatch = !selectedColor || (variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase());
                    const sizeMatch = !selectedSize || variant.size === selectedSize;
                    return colorMatch && sizeMatch;
                }
            });
        }
        
        // If no exact match with new format, try legacy color/size matching
        if (!matchingVariant && selectedColor && currentProductData.variants) {
            matchingVariant = currentProductData.variants.find(function(variant) {
                return variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase();
            });
            
            if (matchingVariant && matchingVariant.size) {
                selectedSize = matchingVariant.size;
                $('.qv-size-option[data-size="' + selectedSize + '"]').prop('checked', true);
            }
        }
        
        
        // Update SKU
        updateQuickViewSku(matchingVariant);
        
        // Update Highlights
        updateQuickViewHighlights(matchingVariant);
        
        // Update Add to Cart button based on stock status
        updateQuickViewAddToCartButton(matchingVariant);
        
        // Helper function to format price with 2 decimal places
        const formatPrice = (price) => {
            return parseFloat(price).toFixed(2);
        };
        
        // Helper function to get display price (no GST calculation, use price as-is)
        const getDisplayPrice = (price) => {
            // Use price as-is without GST calculation
            return price;
        };
        
        // Determine tax label
        const getTaxLabel = () => {
            const gstType = currentProductData.gst_type ?? true;
            let normalizedGstType = gstType;
            if (typeof gstType === 'string') {
                normalizedGstType = (gstType === 'false' || gstType === '0') ? false : true;
            }
            return (normalizedGstType === false) ? 'Exclusive of taxes' : 'Inclusive of taxes';
        };
        
        // Update price using pricing component
        let priceHtml = '';
        const taxLabel = getTaxLabel();
        
        // Get GST settings
        const gstType = currentProductData.gst_type ?? true;
        let normalizedGstType = gstType;
        if (typeof gstType === 'string') {
            normalizedGstType = (gstType === 'false' || gstType === '0') ? false : true;
        }
        const gstPercentage = parseFloat(currentProductData.gst_percentage) || 0;
        
        if (matchingVariant) {
            // Use variant pricing with discount support
            const variantData = {
                price: parseFloat(matchingVariant.price) || 0,
                variant_sale_price: matchingVariant.sale_price ? parseFloat(matchingVariant.sale_price) : null,
                original_variant_price: parseFloat(matchingVariant.price) || 0,
                unit_price: parseFloat(matchingVariant.price) || 0,
                sale_price: matchingVariant.sale_price ? parseFloat(matchingVariant.sale_price) : null,
                discount_type: matchingVariant.discount_type || null,
                discount_value: matchingVariant.discount_value ? parseFloat(matchingVariant.discount_value) : null,
                discount_active: matchingVariant.discount_active === true || matchingVariant.discount_active === '1' || matchingVariant.discount_active === 1,
                gst_type: normalizedGstType,
                gst_percentage: gstPercentage
            };
            
            // Use pricing component JavaScript if available
            if (typeof generateCartItemPricing === 'function') {
                priceHtml = generateCartItemPricing(variantData);
            } else {
                // Fallback to simple display
                const price = parseFloat(matchingVariant.price) || 0;
                const salePrice = matchingVariant.sale_price ? parseFloat(matchingVariant.sale_price) : null;
                const hasSale = salePrice && salePrice < price;
                
                const displayPrice = getDisplayPrice(price);
                const displaySalePrice = salePrice ? getDisplayPrice(salePrice) : null;
                
                if (hasSale && displaySalePrice) {
                    priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2"> ₹' + 
                               formatPrice(displayPrice) + '</span>' +
                               '<span class="ft-bold theme-cl fs-lg me-2"> ₹' + formatPrice(displaySalePrice) + 
                               ' <span class="text-muted fs-sm">(' + taxLabel + ')</span></span>';
                } else {
                    priceHtml = '<span class="ft-bold theme-cl fs-lg me-2"> ₹' + formatPrice(displayPrice) + 
                               ' <span class="text-muted fs-sm">(' + taxLabel + ')</span></span>';
                }
            }
            
            // Stock status
            const isInStock = matchingVariant.is_in_stock !== false && matchingVariant.is_in_stock !== 0;
            if (!isInStock) {
                priceHtml += '<div class="mt-2"><span class="ft-regular text-danger bg-light-danger py-1 px-2 fs-sm">Out of Stock</span></div>';
            }
        } else {
            // Fallback to product price range
            const productData = {
                price: parseFloat(currentProductData.min_price) || 0,
                variant_sale_price: currentProductData.min_sale_price ? parseFloat(currentProductData.min_sale_price) : null,
                original_variant_price: parseFloat(currentProductData.min_price) || 0,
                unit_price: parseFloat(currentProductData.min_price) || 0,
                sale_price: currentProductData.min_sale_price ? parseFloat(currentProductData.min_sale_price) : null,
                discount_type: null,
                discount_value: null,
                discount_active: false,
                gst_type: normalizedGstType,
                gst_percentage: gstPercentage,
                has_price_range: (currentProductData.min_price != currentProductData.max_price && currentProductData.max_price > 0),
                max_price: currentProductData.max_price ? parseFloat(currentProductData.max_price) : null,
                max_sale_price: currentProductData.max_sale_price ? parseFloat(currentProductData.max_sale_price) : null
            };
            
            if (typeof generateCartItemPricing === 'function' && !productData.has_price_range) {
                // Use pricing component for single price
                priceHtml = generateCartItemPricing(productData);
            } else {
                // Fallback: show price range
                if (currentProductData.has_sale && currentProductData.min_sale_price) {
                    const displayMinPrice = getDisplayPrice(currentProductData.min_price);
                    const displayMinSalePrice = getDisplayPrice(currentProductData.min_sale_price);
                    const displayMaxSalePrice = currentProductData.max_sale_price ? getDisplayPrice(currentProductData.max_sale_price) : null;
                    
                    priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2"> ₹' + 
                               formatPrice(displayMinPrice) + '</span>' +
                               '<span class="ft-bold theme-cl fs-lg me-2"> ₹' + 
                               formatPrice(displayMinSalePrice);
                    if (displayMaxSalePrice && displayMinSalePrice != displayMaxSalePrice) {
                        priceHtml += ' - ₹' + formatPrice(displayMaxSalePrice);
                    }
                    priceHtml += ' <span class="text-muted fs-sm">(' + taxLabel + ')</span></span>';
                } else {
                    const displayMinPrice = getDisplayPrice(currentProductData.min_price);
                    const displayMaxPrice = currentProductData.max_price ? getDisplayPrice(currentProductData.max_price) : null;
                    
                    priceHtml = '<span class="ft-bold theme-cl fs-lg me-2"> ₹' + formatPrice(displayMinPrice);
                    if (displayMaxPrice && displayMinPrice != displayMaxPrice) {
                        priceHtml += ' - ₹' + formatPrice(displayMaxPrice);
                    }
                    priceHtml += ' <span class="text-muted fs-sm">(' + taxLabel + ')</span></span>';
                }
            }
            
            if (!currentProductData.in_stock) {
                priceHtml += '<div class="mt-2"><span class="ft-regular text-danger bg-light-danger py-1 px-2 fs-sm">Out of Stock</span></div>';
            }
        }
        
        $('#quickViewPrice').html(priceHtml);
    }
    
    function updateQuickViewSku(matchingVariant) {
        if (!currentProductData) return;
        
        let sku = '—';
        if (matchingVariant) {
            if (matchingVariant.sku) {
                sku = matchingVariant.sku;
            } else if (currentProductData.default_sku) {
                sku = currentProductData.default_sku;
            }
        } else if (currentProductData.default_sku) {
            sku = currentProductData.default_sku;
        } else if (currentProductData.variants && currentProductData.variants.length > 0) {
            // Fallback to first variant's SKU
            const firstVariant = currentProductData.variants[0];
            if (firstVariant && firstVariant.sku) {
                sku = firstVariant.sku;
            }
        }
        
        $('#quickViewSku').text(sku);
    }
    
    function updateQuickViewHighlights(matchingVariant) {
        if (!currentProductData) return;
        
        // Get highlights from matching variant or first variant
        let highlightsDetails = [];
        if (matchingVariant && matchingVariant.highlights_details) {
            highlightsDetails = matchingVariant.highlights_details;
        } else if (currentProductData.variants && currentProductData.variants.length > 0) {
            const firstVariant = currentProductData.variants[0];
            if (firstVariant && firstVariant.highlights_details) {
                highlightsDetails = firstVariant.highlights_details;
            }
        }
        
        if (highlightsDetails && highlightsDetails.length > 0) {
            let highlightsHtml = '';
            highlightsDetails.forEach(function(highlight, index) {
                if (highlight.heading_name) {
                    highlightsHtml += '<h6 class="font-size-sm mb-2">' + escapeHtml(highlight.heading_name) + '</h6>';
                    if (highlight.bullet_points && Array.isArray(highlight.bullet_points)) {
                        highlightsHtml += '<ul class="lists-2 min-space' + (index === highlightsDetails.length - 1 ? ' mb-0' : '') + '">';
                        highlight.bullet_points.forEach(function(point) {
                            if (point) {
                                highlightsHtml += '<li>' + escapeHtml(point) + '</li>';
                            }
                        });
                        highlightsHtml += '</ul>';
                    }
                }
            });
            $('#quickViewHighlightsDetails').html(highlightsHtml);
            $('#quickViewProductInfo').show();
        } else {
            $('#quickViewHighlightsDetails').html('');
            $('#quickViewProductInfo').hide();
        }
    }
    
    // Update add to cart button based on stock status
    function updateQuickViewAddToCartButton(matchingVariant) {
        const $addToCartBtn = $('#quickViewAddToCart');
        const $stockStatusMsg = $('#quickViewStockStatusMessage');
        const $stockStatusText = $('#quickViewStockStatusText');
        
        if (!$addToCartBtn.length) {
            return;
        }
        
        if (!matchingVariant) {
            // No variant selected - disable button
            // $addToCartBtn.prop('disabled', true)
            //     .html('<i class="lni lni-shopping-basket me-2"></i>Select Options')
            //     .removeClass('btn-danger')
            //     .addClass('bg-dark');
            // if ($stockStatusMsg.length) {
            //     $stockStatusMsg.hide();
            // }
            return;
        }
        
        // Check if variant is in stock
        const isInStock = matchingVariant.is_in_stock !== false && matchingVariant.is_in_stock !== undefined;
        
        if (!isInStock) {
            // Out of stock - disable button and show message
            $addToCartBtn.prop('disabled', true)
                .addClass('btn-danger')
                .removeClass('bg-dark')
                .html('<i class="lni lni-close me-2"></i>Out of Stock');
            if ($stockStatusMsg.length) {
                $stockStatusMsg.show();
                $stockStatusText.text('This Item is currently out of stock.');
            }
        } else {
            // In stock - enable button
            $addToCartBtn.prop('disabled', false)
                .removeClass('btn-danger')
                .addClass('bg-dark')
                .html('<i class="lni lni-shopping-basket me-2"></i>Add to Cart');
            if ($stockStatusMsg.length) {
                $stockStatusMsg.hide();
            }
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function updateQuickViewImages() {
        if (!currentProductData) return;
        
        // Show loader and hide slider completely
        $('#quickViewImagesLoader').show();
        $('#quickViewImages').hide();
        
        // Destroy existing slider first
        destroyQuickViewSlider();
        
        // Get matching variant first
        let matchingVariant = null;
        if (currentProductData.variants) {
            // First try: exact match with all selected attributes
            matchingVariant = currentProductData.variants.find(function(variant) {
                if (variant.attributes && Array.isArray(variant.attributes)) {
                    const selectedCount = Object.keys(window.selectedAttributeValues || {}).length;
                    if (selectedCount === 0) {
                        return false;
                    }
                    let allMatch = true;
                    for (let attrId in window.selectedAttributeValues) {
                        const selectedValue = window.selectedAttributeValues[attrId];
                        const variantAttr = variant.attributes.find(function(attr) {
                            // Compare both attribute_id and value as strings
                            // Also handle dynamic attributes (dynamic_length, etc.)
                            const attrIdMatch = String(attr.attribute_id) === String(attrId) || 
                                               String(attr.attribute_name) === String(attrId) ||
                                               String(attr.attribute_slug) === String(attrId) ||
                                               (attrId.startsWith('dynamic_') && String(attr.attribute_name).toLowerCase() === attrId.replace('dynamic_', '').toLowerCase()) ||
                                               (attrId.startsWith('dynamic_') && String(attr.attribute_slug).toLowerCase() === attrId.replace('dynamic_', '').toLowerCase());
                            const valueMatch = String(attr.value) === String(selectedValue);
                            return attrIdMatch && valueMatch;
                        });
                        if (!variantAttr) {
                            allMatch = false;
                            break;
                        }
                    }
                    return allMatch;
                } else {
                    const colorMatch = !selectedColor || (variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase());
                    const sizeMatch = !selectedSize || variant.size === selectedSize;
                    return colorMatch && sizeMatch;
                }
            });
            
            // If no exact match, try to find by color-type attribute only (for image display)
            if (!matchingVariant && window.selectedAttributeValues) {
                // Find color-type attribute
                let colorAttributeId = null;
                let colorValue = null;
                for (let attrId in window.selectedAttributeValues) {
                    // Check if this is a color attribute by looking at the attribute data
                    const attrInput = document.querySelector('.qv-attribute-option[data-attribute-id="' + attrId + '"]:checked');
                    if (attrInput && attrInput.closest('[data-attribute-container]')?.querySelector('.form-option-color')) {
                        colorAttributeId = attrId;
                        colorValue = window.selectedAttributeValues[attrId];
                        break;
                    }
                }
                
                // If we found a color attribute, try to match by it
                if (colorAttributeId && colorValue) {
                    matchingVariant = currentProductData.variants.find(function(variant) {
                        if (variant.attributes && Array.isArray(variant.attributes)) {
                            const variantAttr = variant.attributes.find(function(attr) {
                                return String(attr.attribute_id) === String(colorAttributeId) && String(attr.value) === String(colorValue);
                            });
                            return !!variantAttr;
                        }
                        return false;
                    });
                }
            }
        }
        
        // Fallback: Legacy color matching
        if (!matchingVariant && selectedColor && currentProductData.variants) {
            matchingVariant = currentProductData.variants.find(function(variant) {
                return variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase();
            });
        }
        
        // Get images to display
        let imagesToLoad = [];
        
        // Try to get images from matching variant first
        if (matchingVariant && matchingVariant.images && matchingVariant.images.length > 0) {
            imagesToLoad = matchingVariant.images.filter(function(img) {
                return img && img.url && img.url !== 'undefined' && img.url !== 'null';
            });
        }
        
        // If no exact match, try to find variant by color-type attribute only
        if (imagesToLoad.length === 0 && window.selectedAttributeValues && currentProductData.variants) {
            // Find color-type attribute
            let colorAttributeId = null;
            let colorValue = null;
            for (let attrId in window.selectedAttributeValues) {
                // Check if this is a color attribute
                const attrInput = $('.qv-attribute-option[data-attribute-id="' + attrId + '"]:checked');
                if (attrInput.length && attrInput.closest('[data-attribute-container]').find('.form-option-color').length > 0) {
                    colorAttributeId = attrId;
                    colorValue = window.selectedAttributeValues[attrId];
                    break;
                }
            }
            
            // If we found a color attribute, find any variant with that color
            if (colorAttributeId && colorValue) {
                const colorVariant = currentProductData.variants.find(function(variant) {
                    if (variant.attributes && Array.isArray(variant.attributes)) {
                        const variantAttr = variant.attributes.find(function(attr) {
                            return String(attr.attribute_id) === String(colorAttributeId) && String(attr.value) === String(colorValue);
                        });
                        return !!variantAttr;
                    }
                    return false;
                });
                
                if (colorVariant && colorVariant.images && colorVariant.images.length > 0) {
                    imagesToLoad = colorVariant.images.filter(function(img) {
                        return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                    });
                }
            }
        }
        
        // Fallback: Use variant images for selected color (legacy)
        if (imagesToLoad.length === 0 && selectedColor) {
            const colorVariant = currentProductData.color_variants && currentProductData.color_variants[selectedColor] 
                ? currentProductData.color_variants[selectedColor] 
                : null;
            
            if (colorVariant && colorVariant.images && colorVariant.images.length > 0) {
                imagesToLoad = colorVariant.images.filter(function(img) {
                    return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                });
            }
        }
        
        // Final fallback: Use product images
        if (imagesToLoad.length === 0) {
            if (currentProductData.images && currentProductData.images.length > 0) {
                imagesToLoad = currentProductData.images.filter(function(img) {
                    return img && img.url && img.url !== 'undefined' && img.url !== 'null';
                });
            } else {
                imagesToLoad = [{url: '{{ asset("assets/images/placeholder.jpg") }}', alt: currentProductData.name}];
            }
        }
        
        // Build HTML with images
        let imagesHtml = '';
        imagesToLoad.forEach(function(image) {
            imagesHtml += '<div  class="single_view_slide"><img style="max-width: 450px; height:auto;max-height: 580px; object-fit: contain;"  src="' + image.url + '" class="img-fluid mx-auto w-100" alt="' + (image.alt || currentProductData.name) + '" /></div>';
        });
        $('#quickViewImages').html(imagesHtml);
        
        // Initialize slider after DOM is updated
        // Use a small delay to ensure HTML is rendered, then initialize
        setTimeout(function() {
            initializeQuickViewSlider();
        }, 200);
    }
    
    function preloadImages(images, callback) {
        if (!images || images.length === 0) {
            callback();
            return;
        }
        
        let loadedCount = 0;
        let hasCalledCallback = false;
        const totalImages = images.length;
        
        // Set a timeout fallback in case images don't load
        const timeout = setTimeout(function() {
            if (!hasCalledCallback) {
                hasCalledCallback = true;
                callback();
            }
        }, 2000); // 2 second timeout fallback
        
        function checkComplete() {
            loadedCount++;
            if (loadedCount >= totalImages && !hasCalledCallback) {
                hasCalledCallback = true;
                clearTimeout(timeout);
                callback();
            }
        }
        
        images.forEach(function(image) {
            const img = new Image();
            img.onload = checkComplete;
            img.onerror = checkComplete;
            // Set src after attaching handlers
            img.src = image.url;
            
            // Check if image is already cached (after setting src)
            if (img.complete) {
                checkComplete();
            }
        });
    }
    
    function destroyQuickViewSlider() {
        if (typeof $.fn.slick !== 'undefined' && $.fn.slick) {
            var $slider = $('#quickViewImages');
            if ($slider.hasClass('slick-initialized')) {
                try {
                    $slider.slick('unslick');
                } catch(e) {
                    // If unslick fails, manually clean up
                    $slider.removeClass('slick-initialized');
                    $slider.find('.slick-list, .slick-track, .slick-slide, .slick-arrow, .slick-dots').remove();
                }
            }
        }
    }
    
    function initializeQuickViewSlider() {
        var $slider = $('#quickViewImages');
        
        // Always hide loader first
        $('#quickViewImagesLoader').hide();
        
        if (typeof $.fn.slick !== 'undefined' && $.fn.slick) {
            // Make sure slider is not already initialized
            if ($slider.hasClass('slick-initialized')) {
                // Already initialized, just show it
                $slider.css('display', 'block');
                return;
            }
            
            if ($slider.children().length > 0) {
                try {
                    // Show slider before initializing
                    $slider.css('display', 'block');
                    
                    $slider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: true,
                        dots: true,
                        infinite: true,
                        autoplaySpeed: 2000,
                        autoplay: false,
                        fade: false,
                        speed: 300,
                        responsive: [
                            {
                                breakpoint: 1024,
                                settings: {
                                    arrows: true,
                                    dots: true,
                                    slidesToShow: 1
                                }
                            },
                            {
                                breakpoint: 600,
                                settings: {
                                    arrows: true,
                                    dots: true,
                                    slidesToShow: 1
                                }
                            }
                        ]
                    });
                } catch(e) {
                    console.error('Error initializing slider:', e);
                    // If slick fails, show images anyway
                    $slider.css('display', 'block');
                }
            } else {
                // No images, show empty slider
                $slider.css('display', 'block');
            }
        } else {
            // Slick not available, show images anyway
            $slider.css('display', 'block');
        }
    }
    
    // Check if product is in wishlist and update button state
    function checkWishlistStatus(productId) {
        if (!productId) return;
        
        // Reset button state first
        const $btn = $('#quickViewWishlist');
        const $icon = $btn.find('i');
        $btn.removeClass('active text-danger');
        $icon.removeClass('fas fa-heart').addClass('lni lni-heart').css('color', '');
        $btn.css('color', '');
        
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        
        $.ajax({
            url: '/api/wishlist',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                if (response.success && response.data) {
                    const isInWishlist = response.data.some(function(item) {
                        return item.product_id == productId;
                    });
                    
                    if (isInWishlist) {
                        $btn.addClass('active text-danger');
                        $icon.removeClass('lni lni-heart').addClass('fas fa-heart').css('color', '#dc3545');
                        $btn.css('color', '#dc3545');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error checking wishlist status:', xhr);
                // On error, ensure button is in inactive state
                $btn.removeClass('active text-danger');
                $icon.removeClass('fas fa-heart').addClass('lni lni-heart').css('color', '');
                $btn.css('color', '');
            }
        });
    }
    
    // Update wishlist count helper
    function updateWishlistCount() {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        
        $.ajax({
            url: '/api/wishlist/count',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                if (response.success) {
                    $('.dn-counter').text(response.count || '0');
                }
            }
        });
    }
    
    // Customer Login Form Handler
    $('#customerLoginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('#customerLoginForm .is-invalid').removeClass('is-invalid');
        $('#customerLoginForm .invalid-feedback').text('');
        $('#customerLoginMessage').hide().html('');
        
        // Get form data
        const emailOrPhone = $('#customerLoginEmailOrPhone').val().trim();
        const password = $('#customerLoginPassword').val();
        const remember = $('#customerRememberMe').is(':checked');
        
        // Basic validation
        if (!emailOrPhone || !password) {
            showCustomerLoginError('Please fill in all fields');
            return;
        }
        
        // Determine if input is email or phone
        const isEmail = emailOrPhone.includes('@');
        const loginData = {
            password: password,
            remember: remember
        };
        
        // Add session_id for cart merging
        let sessionId = localStorage.getItem('session_id');
        if (sessionId) {
            loginData.session_id = sessionId;
        }
        
        if (isEmail) {
            loginData.email = emailOrPhone;
        } else {
            loginData.phone = emailOrPhone;
        }
        
        // Show loading state
        $('#customerLoginBtnText').text('Logging in...');
        $('#customerLoginBtnSpinner').removeClass('d-none');
        $('#customerLoginBtn').prop('disabled', true);
        
        // Get CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        
        // Make API call with CSRF token
        $.ajax({
            url: '/api/auth/login',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Session-ID': sessionId
            },
            data: loginData,
            dataType: 'json',
            success: function(response) {
                console.log('Login Response:', response);
                
                if (response.success) {
                    // Show success message
                    showCustomerLoginSuccess('Login successful! Redirecting...');
                    
                    // Close modal
                    $('#login').modal('hide');
                    
                    // Reload page to update UI with session
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    console.error('Login failed:', response);
                    showCustomerLoginError(response.error?.message || 'Login failed. Please try again.');
                    resetLoginButton();
                }
            },
            error: function(xhr) {
                console.error('Login Error:', xhr);
                
                let errorMessage = 'Login failed. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error.message || errorMessage;
                    
                    // Handle validation errors
                    if (xhr.status === 422 && xhr.responseJSON.error.errors) {
                        const errors = xhr.responseJSON.error.errors;
                        if (errors.email || errors.phone) {
                            $('#customerLoginEmailOrPhone').addClass('is-invalid');
                            $('#customerLoginEmailOrPhone').next('.invalid-feedback').text(errors.email?.[0] || errors.phone?.[0]);
                        }
                        if (errors.password) {
                            $('#customerLoginPassword').addClass('is-invalid');
                            $('#customerLoginPassword').next('.invalid-feedback').text(errors.password[0]);
                        }
                        errorMessage = 'Please correct the errors above.';
                    }
                }
                
                showCustomerLoginError(errorMessage);
                resetLoginButton();
            }
        });
    });
    
    function showCustomerLoginError(message) {
        $('#customerLoginMessage').html(
            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<strong>Error!</strong> ' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        ).show();
    }
    
    function showCustomerLoginSuccess(message) {
        $('#customerLoginMessage').html(
            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<strong>Success!</strong> ' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        ).show();
    }
    
    function resetLoginButton() {
        $('#customerLoginBtnText').text('Login');
        $('#customerLoginBtnSpinner').addClass('d-none');
        $('#customerLoginBtn').prop('disabled', false);
    }

    // Load registration fields when register modal is shown
    $('#register').on('show.bs.modal', function() {
        loadRegistrationFields();
    });

    function loadRegistrationFields() {
        $('#customerRegisterFields').html(
            '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '</div>'
        );
        $('#customerRegisterSubmitSection, #customerRegisterLoginSection').hide();

        $.ajax({
            url: '/api/auth/register-fields',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.fields) {
                    renderRegistrationFields(response.data.fields);
                } else {
                    showRegistrationError('Failed to load registration form');
                }
            },
            error: function(xhr) {
                console.error('Error loading registration fields:', xhr);
                showRegistrationError('Failed to load registration form');
            }
        });
    }

    function renderRegistrationFields(fields) {
        let fieldsHtml = '';
        
        // Define the correct field order for registration
        const fieldOrder = ['full_name', 'phone', 'email', 'password', 'password_confirmation'];
        
        // Sort fields according to the defined order
        const sortedFields = fieldOrder.map(function(fieldKey) {
            return fields.find(function(field) {
                return field.field_key === fieldKey;
            });
        }).filter(function(field) {
            return field !== undefined; // Remove undefined fields
        });
        
        // Create the specific layout requested:
        // Row 1: Full Name * Phone (two columns)
        // Row 2: Email (full width)
        // Row 3: Password and Confirm Password (full width each)
        
        let fullNameField = sortedFields.find(f => f.field_key === 'full_name');
        let phoneField = sortedFields.find(f => f.field_key === 'phone');
        let emailField = sortedFields.find(f => f.field_key === 'email');
        let passwordField = sortedFields.find(f => f.field_key === 'password');
        let confirmPasswordField = sortedFields.find(f => f.field_key === 'password_confirmation');
        
        // Row 1: Full Name * Phone
        if (fullNameField && phoneField) {
            fieldsHtml += '<div class="row mb-3">';
            fieldsHtml += '<div class="col-md-6">';
            fieldsHtml += renderSingleField(fullNameField);
            fieldsHtml += '</div>';
            fieldsHtml += '<div class="col-md-6">';
            fieldsHtml += renderSingleField(phoneField);
            fieldsHtml += '</div>';
            fieldsHtml += '</div>';
        }
        
        // Row 2: Email (complete row)
        if (emailField) {
            fieldsHtml += '<div class="row mb-3">';
            fieldsHtml += '<div class="col-12">';
            fieldsHtml += renderSingleField(emailField);
            fieldsHtml += '</div>';
            fieldsHtml += '</div>';
        }
        
      // Row 3: Password + Confirm Password in one row
if (passwordField || confirmPasswordField) {
    fieldsHtml += '<div class="row mb-3">';

    if (passwordField) {
        fieldsHtml += '<div class="col-md-6">';
        fieldsHtml += renderSingleField(passwordField);
        fieldsHtml += '</div>';
    }

    if (confirmPasswordField) {
        fieldsHtml += '<div class="col-md-6">';
        fieldsHtml += renderSingleField(confirmPasswordField);
        fieldsHtml += '</div>';
    }

    fieldsHtml += '</div>';
}

        
        // Add note about email OR phone requirement
        fieldsHtml += '<div class="alert alert-info mb-3">' +
            '<small><i class="fas fa-info-circle me-1"></i> Either email or phone number is required for registration.</small>' +
            '</div>';
        
        $('#customerRegisterFields').html(fieldsHtml);
        $('#customerRegisterSubmitSection, #customerRegisterLoginSection').show();
    }

    function renderSingleField(field) {
        let fieldHtml = '<div class="form-group">';
        
        // Label
        fieldHtml += '<label class="mb-2">' + field.label;
        if (field.is_required) {
            fieldHtml += ' <span class="text-danger">*</span>';
        }
        fieldHtml += '</label>';
        
        // Input field
        let inputType = field.input_type;
        let placeholder = field.placeholder || field.label;
        let required = field.is_required ? 'required' : '';
        let fieldId = 'customerRegister' + capitalizeFirst(field.field_key.replace('_', ''));
        
        // Special handling for different input types
        if (inputType === 'email') {
            fieldHtml += '<input type="email" id="' + fieldId + '" name="' + field.field_key + '" class="form-control" placeholder="' + placeholder + '" ' + required + '>';
        } else if (inputType === 'tel') {
            fieldHtml += '<input type="tel" id="' + fieldId + '" name="' + field.field_key + '" class="form-control" placeholder="' + placeholder + '" ' + required + ' pattern="[0-9]{10}" maxlength="15">';
        } else if (inputType === 'password') {
            let minLength = '';
            if (field.validation_rules && field.validation_rules.includes('min:')) {
                const minMatch = field.validation_rules.match(/min:(\d+)/);
                if (minMatch) {
                    minLength = 'minlength="' + minMatch[1] + '"';
                }
            }
            fieldHtml += '<div class="position-relative">';
            fieldHtml += '<input type="password" id="' + fieldId + '" name="' + field.field_key + '" class="form-control pe-5" placeholder="' + placeholder + '" ' + required + ' ' + minLength + '>';
            fieldHtml += '<button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted password-toggle" data-target="#' + fieldId + '">';
            fieldHtml += '<i class="fas fa-eye"></i>';
            fieldHtml += '</button>';
            fieldHtml += '</div>';
        } else {
            let maxLength = '';
            if (field.validation_rules && field.validation_rules.includes('max:')) {
                const maxMatch = field.validation_rules.match(/max:(\d+)/);
                if (maxMatch) {
                    maxLength = 'maxlength="' + maxMatch[1] + '"';
                }
            }
            fieldHtml += '<input type="text" id="' + fieldId + '" name="' + field.field_key + '" class="form-control" placeholder="' + placeholder + '" ' + required + ' ' + maxLength + '>';
        }
        
        fieldHtml += '<div class="invalid-feedback"></div>';
        
        // Help text
        if (field.help_text) {
            fieldHtml += '<small class="form-text text-muted">' + field.help_text + '</small>';
        }
        
        fieldHtml += '</div>';
        
        return fieldHtml;
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Customer Registration Form Handler
    $('#customerRegisterForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('#customerRegisterForm .is-invalid').removeClass('is-invalid');
        $('#customerRegisterForm .invalid-feedback').text('');
        $('#customerRegisterMessage').hide().html('');
        
        // Validate form before submission
        if (!validateRegistrationForm()) {
            return false;
        }
        
        // Get form data
        const formData = {};
        $('#customerRegisterForm input').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val().trim();
            if (name) {
                // Include all fields, even empty ones (server will validate)
                formData[name] = value;
            }
        });
        
        // Add session_id for cart merging
        let sessionId = localStorage.getItem('session_id');
        if (sessionId) {
            formData.session_id = sessionId;
        }
        
        // Show loading state
        $('#customerRegisterBtnText').text('Registering...');
        $('#customerRegisterBtnSpinner').removeClass('d-none');
        $('#customerRegisterBtn').prop('disabled', true);
        
        // Get CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        
        // Make API call
        $.ajax({
            url: '/api/auth/register',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Session-ID': sessionId
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Registration Response:', response);
                
                if (response.success) {
                    // Show success message
                    showRegistrationSuccess('Registration successful! Redirecting...');
                    
                    // Close modal
                    $('#register').modal('hide');
                    
                    // Reload page to update UI with session
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    console.error('Registration failed:', response);
                    showRegistrationError(response.error?.message || 'Registration failed. Please try again.');
                    resetRegisterButton();
                }
            },
            error: function(xhr) {
                console.error('Registration Error:', xhr);
                
                let errorMessage = 'Registration failed. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error.message || errorMessage;
                    
                    // Handle validation errors
                    if (xhr.status === 422 && xhr.responseJSON.error.errors) {
                        const errors = xhr.responseJSON.error.errors;
                        let hasFieldErrors = false;
                        
                        Object.keys(errors).forEach(function(fieldName) {
                            // Handle field name conversion for ID matching
                            let fieldId = 'customerRegister' + capitalizeFirst(fieldName.replace('_', ''));
                            const fieldInput = $('#' + fieldId);
                            if (fieldInput.length) {
                                fieldInput.addClass('is-invalid');
                                fieldInput.next('.invalid-feedback').text(errors[fieldName][0]);
                                hasFieldErrors = true;
                            }
                        });
                        
                        if (hasFieldErrors) {
                            errorMessage = 'Please correct the errors above.';
                        }
                    }
                }
                
                showRegistrationError(errorMessage);
                resetRegisterButton();
            }
        });
    });

    function showRegistrationError(message) {
        $('#customerRegisterMessage').html(
            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<strong>Error!</strong> ' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        ).show();
    }
    
    function showRegistrationSuccess(message) {
        $('#customerRegisterMessage').html(
            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<strong>Success!</strong> ' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        ).show();
    }
    
    function resetRegisterButton() {
        $('#customerRegisterBtnText').text('Register');
        $('#customerRegisterBtnSpinner').addClass('d-none');
        $('#customerRegisterBtn').prop('disabled', false);
    }

    // Registration form validation function
    function validateRegistrationForm() {
        let isValid = true;
        
        // Debug: Log available form fields
        console.log('Available form fields:', $('#customerRegisterForm input').map(function() { 
            return this.id + ' (' + this.name + ')'; 
        }).get());
        
        // Get form values safely
        const fullName = $('#customerRegisterFullName').val() ? $('#customerRegisterFullName').val().trim() : '';
        const email = $('#customerRegisterEmail').val() ? $('#customerRegisterEmail').val().trim() : '';
        const phone = $('#customerRegisterPhone').val() ? $('#customerRegisterPhone').val().trim() : '';
        const password = $('#customerRegisterPassword').val() ? $('#customerRegisterPassword').val() : '';
        const confirmPassword = $('#customerRegisterPasswordConfirmation').val() ? $('#customerRegisterPasswordConfirmation').val() : '';
        
        // Validate Full Name (only if field exists)
        if ($('#customerRegisterFullName').length) {
            if (!fullName) {
                showFieldError('#customerRegisterFullName', 'Full name is required');
                isValid = false;
            } else if (fullName.length < 2) {
                showFieldError('#customerRegisterFullName', 'Full name must be at least 2 characters');
                isValid = false;
            } else if (fullName.length > 255) {
                showFieldError('#customerRegisterFullName', 'Full name cannot exceed 255 characters');
                isValid = false;
            }
        }
        
        // Validate Email OR Phone (at least one required)
        const hasEmail = email && email.length > 0;
        const hasPhone = phone && phone.length > 0;
        const emailFieldExists = $('#customerRegisterEmail').length > 0;
        const phoneFieldExists = $('#customerRegisterPhone').length > 0;
        
        if ((emailFieldExists || phoneFieldExists) && !hasEmail && !hasPhone) {
            if (emailFieldExists) showFieldError('#customerRegisterEmail', 'Either email or phone number is required');
            if (phoneFieldExists) showFieldError('#customerRegisterPhone', 'Either email or phone number is required');
            isValid = false;
        }
        
        // Validate Email (if provided and field exists)
        if (hasEmail && $('#customerRegisterEmail').length) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError('#customerRegisterEmail', 'Please enter a valid email address');
                isValid = false;
            } else if (email.length > 255) {
                showFieldError('#customerRegisterEmail', 'Email cannot exceed 255 characters');
                isValid = false;
            }
        }
        
        // Validate Phone (if provided and field exists)
        if (hasPhone && $('#customerRegisterPhone').length) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,20}$/;
            if (!phoneRegex.test(phone)) {
                showFieldError('#customerRegisterPhone', 'Please enter a valid phone number (10-20 digits)');
                isValid = false;
            } else if (phone.length > 20) {
                showFieldError('#customerRegisterPhone', 'Phone number cannot exceed 20 characters');
                isValid = false;
            }
        }
        
        // Validate Password (only if field exists)
        if ($('#customerRegisterPassword').length) {
            if (!password) {
                showFieldError('#customerRegisterPassword', 'Password is required');
                isValid = false;
            } else if (password.length < 8) {
                showFieldError('#customerRegisterPassword', 'Password must be at least 8 characters');
                isValid = false;
            } else if (password.length > 255) {
                showFieldError('#customerRegisterPassword', 'Password cannot exceed 255 characters');
                isValid = false;
            }
        }
        
        // Validate Password Confirmation (only if field exists)
        if ($('#customerRegisterPasswordConfirmation').length) {
            if (!confirmPassword) {
                showFieldError('#customerRegisterPasswordConfirmation', 'Please confirm your password');
                isValid = false;
            } else if (password !== confirmPassword) {
                showFieldError('#customerRegisterPasswordConfirmation', 'Passwords do not match');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Helper function to show field-specific errors
    function showFieldError(fieldSelector, message) {
        const field = $(fieldSelector);
        if (field.length) {
            field.addClass('is-invalid');
            field.next('.invalid-feedback').text(message);
            
            // If field is inside a position-relative div (password fields), find the feedback div
            if (field.parent().hasClass('position-relative')) {
                field.parent().next('.invalid-feedback').text(message);
            }
        }
    }
    
    // Real-time validation on input events
    $(document).on('input blur', '#customerRegisterForm input', function() {
        const fieldId = $(this).attr('id');
        const fieldValue = $(this).val() ? $(this).val().trim() : '';
        
        // Clear previous states for this field
        $(this).removeClass('is-invalid is-valid');
        $(this).next('.invalid-feedback').text('');
        if ($(this).parent().hasClass('position-relative')) {
            $(this).parent().next('.invalid-feedback').text('');
        }
        
        // Field-specific real-time validation
        switch(fieldId) {
            case 'customerRegisterFullName':
                if (fieldValue && fieldValue.length < 2) {
                    showFieldError('#' + fieldId, 'Full name must be at least 2 characters');
                } else if (fieldValue && fieldValue.length > 255) {
                    showFieldError('#' + fieldId, 'Full name cannot exceed 255 characters');
                } else if (fieldValue && fieldValue.length >= 2) {
                    $(this).addClass('is-valid');
                }
                break;
                
            case 'customerRegisterEmail':
                if (fieldValue) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        showFieldError('#' + fieldId, 'Please enter a valid email address');
                    } else if (fieldValue.length > 255) {
                        showFieldError('#' + fieldId, 'Email cannot exceed 255 characters');
                    } else {
                        $(this).addClass('is-valid');
                    }
                }
                break;
                
            case 'customerRegisterPhone':
                if (fieldValue) {
                    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,20}$/;
                    if (!phoneRegex.test(fieldValue)) {
                        showFieldError('#' + fieldId, 'Please enter a valid phone number');
                    } else if (fieldValue.length > 20) {
                        showFieldError('#' + fieldId, 'Phone number cannot exceed 20 characters');
                    } else {
                        $(this).addClass('is-valid');
                    }
                }
                break;
                
            case 'customerRegisterPassword':
                if (fieldValue && fieldValue.length < 8) {
                    showFieldError('#' + fieldId, 'Password must be at least 8 characters');
                } else if (fieldValue && fieldValue.length > 255) {
                    showFieldError('#' + fieldId, 'Password cannot exceed 255 characters');
                } else if (fieldValue && fieldValue.length >= 8) {
                    $(this).addClass('is-valid');
                }
                // Also validate confirm password if it has a value
                const confirmPasswordField = $('#customerRegisterPasswordConfirmation');
                if (confirmPasswordField.length) {
                    const confirmPassword = confirmPasswordField.val() ? confirmPasswordField.val() : '';
                    if (confirmPassword && fieldValue !== confirmPassword) {
                        showFieldError('#customerRegisterPasswordConfirmation', 'Passwords do not match');
                    } else if (confirmPassword && fieldValue === confirmPassword && fieldValue.length >= 8) {
                        confirmPasswordField.removeClass('is-invalid').addClass('is-valid');
                        confirmPasswordField.parent().next('.invalid-feedback').text('');
                    }
                }
                break;
                
            case 'customerRegisterPasswordConfirmation':
                const passwordField = $('#customerRegisterPassword');
                if (passwordField.length) {
                    const password = passwordField.val() ? passwordField.val() : '';
                    if (fieldValue && password !== fieldValue) {
                        showFieldError('#' + fieldId, 'Passwords do not match');
                    } else if (fieldValue && password === fieldValue && password.length >= 8) {
                        $(this).addClass('is-valid');
                    }
                }
                break;
        }
    });

    // Password toggle functionality
    $(document).on('click', '.password-toggle', function(e) {
        e.preventDefault();
        
        const targetSelector = $(this).data('target');
        const targetInput = $(targetSelector);
        const icon = $(this).find('i');
        
        if (targetInput.attr('type') === 'password') {
            targetInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            targetInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Check if we should show login modal (from session redirect)
    @if(session('show_login'))
        $(document).ready(function() {
            // Show login modal after a short delay to ensure page is loaded
            setTimeout(function() {
                $('#login').modal('show');
            }, 500);
        });
    @endif
    
});

// Measurement Chart Modal
// Store product measurements data globally
let productMeasurementsData = null;

// Function to build measurement chart table
function buildMeasurementChart(variants) {
    if (!variants || variants.length === 0) {
        return '<p class="text-muted text-center py-4">No measurement data available.</p>';
    }
    
    // Collect all unique measurement attributes across all variants
    const measurementAttributes = new Set();
    const variantsWithMeasurements = [];
    
    variants.forEach(variant => {
        if (variant.measurements && Array.isArray(variant.measurements) && variant.measurements.length > 0) {
            const variantData = {
                variant: variant,
                measurements: {}
            };
            
            variant.measurements.forEach(measurement => {
                const attrName = measurement.attribute_name || measurement.name || 'Unknown';
                measurementAttributes.add(attrName);
                variantData.measurements[attrName] = {
                    value: measurement.value || '',
                    unit: measurement.unit_symbol || measurement.unit_name || measurement.unit || '',
                    unitName: measurement.unit_name || measurement.unit || ''
                };
            });
            
            variantsWithMeasurements.push(variantData);
        }
    });
    
    if (variantsWithMeasurements.length === 0) {
        return '<p class="text-muted text-center py-4">No measurement data available.</p>';
    }
    
    // Build table HTML
    let tableHtml = '<div class="table-responsive"><table class="table table-bordered table-striped">';
    
    // Header row - Measurement attributes
    tableHtml += '<thead><tr>';
    tableHtml += '<th class="bg-light">Variant</th>';
    Array.from(measurementAttributes).sort().forEach(attrName => {
        tableHtml += '<th class="bg-light">' + escapeHtml(attrName) + '</th>';
    });
    tableHtml += '</tr></thead>';
    
    // Body rows - Variant measurements
    tableHtml += '<tbody>';
    variantsWithMeasurements.forEach(variantData => {
        const variant = variantData.variant;
        
        // Build variant identifier (color, size, etc.)
        let variantIdentifier = '';
        const identifierParts = [];
        
        // Try to get color and size first (legacy support)
        if (variant.color) {
            identifierParts.push(variant.color);
        }
        if (variant.size && variant.size !== variant.color) {
            identifierParts.push(variant.size);
        }
        
        // If we have attributes object (from variantDataMap), extract values
        if (variant.attributes && typeof variant.attributes === 'object') {
            // variant.attributes is a map of attribute_id => value
            // Get all unique values (excluding color/size if already added)
            const attrValues = Object.values(variant.attributes).filter(val => 
                val && val !== variant.color && val !== variant.size
            );
            identifierParts.push(...attrValues);
        }
        
        // If still no identifier, use SKU or variant ID
        if (identifierParts.length > 0) {
            variantIdentifier = identifierParts.join(' - ');
        } else if (variant.sku) {
            variantIdentifier = 'SKU: ' + variant.sku;
        } else {
            variantIdentifier = 'Variant #' + variant.id;
        }
        
        tableHtml += '<tr>';
        tableHtml += '<td><strong>' + escapeHtml(variantIdentifier) + '</strong></td>';
        
        Array.from(measurementAttributes).sort().forEach(attrName => {
            const measurement = variantData.measurements[attrName];
            if (measurement) {
                const displayValue = measurement.value + (measurement.unit ? ' ' + measurement.unit : '');
                tableHtml += '<td>' + escapeHtml(displayValue) + '</td>';
            } else {
                tableHtml += '<td class="text-muted">—</td>';
            }
        });
        
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table></div>';
    
    return tableHtml;
}

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Handle measurement chart modal open for product page
$(document).on('show.bs.modal', '#measurementChartModal', function() {
    const $content = $('#measurementChartContent');
    
    // Check if we have variantDataMap (product page) or currentProductData (quick view)
    if (typeof window.variantDataMap !== 'undefined' && window.variantDataMap) {
        // Product page - get variants from variantDataMap
        const variants = Object.values(window.variantDataMap).map(variant => {
            // Extract color and size from attributes if available
            let color = null;
            let size = null;
            
            if (variant.attributes && typeof variant.attributes === 'object') {
                const attrValues = Object.values(variant.attributes);
                // Try to identify color and size (first two values, or use all)
                if (attrValues.length > 0) {
                    color = attrValues[0];
                }
                if (attrValues.length > 1) {
                    size = attrValues[1];
                }
            }
            
            return {
                id: variant.id,
                sku: variant.sku,
                color: color,
                size: size,
                attributes: variant.attributes,
                measurements: variant.measurements || []
            };
        });
        
        const chartHtml = buildMeasurementChart(variants);
        $content.html(chartHtml);
    } else if (typeof currentProductData !== 'undefined' && currentProductData && currentProductData.variants) {
        // Quick view - use currentProductData
        const chartHtml = buildMeasurementChart(currentProductData.variants);
        $content.html(chartHtml);
    } else if (productMeasurementsData) {
        // Use stored data
        const chartHtml = buildMeasurementChart(productMeasurementsData);
        $content.html(chartHtml);
    } else {
        $content.html('<p class="text-muted text-center py-4">No measurement data available.</p>');
    }
});

// Update quick view measurement button visibility
function updateQuickViewMeasurementButton() {
    if (typeof currentProductData !== 'undefined' && currentProductData && currentProductData.variants) {
        const hasMeasurements = currentProductData.variants.some(variant => 
            variant.measurements && Array.isArray(variant.measurements) && variant.measurements.length > 0
        );
        
        if (hasMeasurements) {
            $('#quickViewMeasurementButton').show();
        } else {
            $('#quickViewMeasurementButton').hide();
        }
    } else {
        $('#quickViewMeasurementButton').hide();
    }
}
</script>
@endpush
