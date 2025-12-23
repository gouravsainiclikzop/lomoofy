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
									<div class="star-rating align-items-center d-flex justify-content-left mb-1 p-0">
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star filled"></i>
										<i class="fas fa-star"></i>
										<span class="small">(0 Reviews)</span>
									</div>
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
							
							<div class="prt_04 mb-4" id="quickViewSizesContainer" style="display: none;">
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
						<label class="mb-2">Email</label>
						<input type="email" id="customerLoginEmail" name="email" class="form-control" placeholder="Email*" required>
						<div class="invalid-feedback"></div>
					</div>
					
					<div class="form-group mb-3">
						<label class="mb-2">Password</label>
						<input type="password" id="customerLoginPassword" name="password" class="form-control" placeholder="Password*" required>
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
						<p class="extra">Not a member?<a href="#et-register-wrap" class="text-dark"> Register</a></p>
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
</style>
@endpush

@push('scripts')
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
                    const containerHtml = '<div class="prt_04 mb-4" id="' + containerId + '">' +
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
        updateQuickViewSku();
        updateQuickViewHighlights();
        
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
                            const attrIdMatch = String(attr.attribute_id) === String(attrId) || 
                                               String(attr.attribute_name) === String(attrId) ||
                                               String(attr.attribute_slug) === String(attrId);
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
        
        // Update price
        let priceHtml = '';
        if (matchingVariant) {
            const price = parseFloat(matchingVariant.price) || 0;
            const salePrice = matchingVariant.sale_price ? parseFloat(matchingVariant.sale_price) : null;
            const hasSale = salePrice && salePrice < price;
            
            if (hasSale && salePrice) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">₹' + 
                           Math.round(price) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg me-2">₹' + Math.round(salePrice) + '</span>';
            } else {
                priceHtml = '<span class="ft-bold theme-cl fs-lg me-2">₹' + Math.round(price) + '</span>';
            }
            
            // Stock status
            const isInStock = matchingVariant.is_in_stock !== false && matchingVariant.is_in_stock !== 0;
            if (!isInStock) {
                priceHtml += '<span class="ft-regular text-danger bg-light-danger py-1 px-2 fs-sm ms-2">Out of Stock</span>';
            }
        } else {
            // Fallback to product price range
            if (currentProductData.has_sale && currentProductData.min_sale_price) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">₹' + 
                           Math.round(currentProductData.min_price) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg me-2">₹' + 
                           Math.round(currentProductData.min_sale_price);
                if (currentProductData.max_sale_price && currentProductData.min_sale_price != currentProductData.max_sale_price) {
                    priceHtml += ' - ₹' + Math.round(currentProductData.max_sale_price);
                }
                priceHtml += '</span>';
            } else {
                priceHtml = '<span class="ft-bold theme-cl fs-lg me-2">' + currentProductData.price_display + '</span>';
            }
            
            if (!currentProductData.in_stock) {
                priceHtml += '<span class="ft-regular text-danger bg-light-danger py-1 px-2 fs-sm ms-2">Out of Stock</span>';
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
                            return String(attr.attribute_id) === String(attrId) && String(attr.value) === String(selectedValue);
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
                imagesToLoad = [{url: '{{ asset("frontend/images/product/1.jpg") }}', alt: currentProductData.name}];
            }
        }
        
        // Build HTML with images
        let imagesHtml = '';
        imagesToLoad.forEach(function(image) {
            imagesHtml += '<div class="single_view_slide"><img src="' + image.url + '" class="img-fluid" alt="' + (image.alt || currentProductData.name) + '" /></div>';
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
        const email = $('#customerLoginEmail').val().trim();
        const password = $('#customerLoginPassword').val();
        const remember = $('#customerRememberMe').is(':checked');
        
        // Basic validation
        if (!email || !password) {
            showCustomerLoginError('Please fill in all fields');
            return;
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
                'Accept': 'application/json'
            },
            data: {
                email: email,
                password: password,
                remember: remember
            },
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
                        if (errors.email) {
                            $('#customerLoginEmail').addClass('is-invalid');
                            $('#customerLoginEmail').next('.invalid-feedback').text(errors.email[0]);
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
</script>
@endpush
