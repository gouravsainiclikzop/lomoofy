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
										<button class="btn custom-height btn-default btn-block mb-2 text-dark" id="quickViewWishlist" data-product-id="" data-bs-toggle="button">
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
				
				<form>				
					<div class="form-group mb-3">
						<label class="mb-2">User Name</label>
						<input type="text" class="form-control" placeholder="Username*">
					</div>
					
					<div class="form-group mb-3">
						<label class="mb-2">Password</label>
						<input type="password" class="form-control" placeholder="Password*">
					</div>
					
					<div class="form-group mb-3">
						<div class="d-flex align-items-center justify-content-between">
							<div class="flex-1">
								<input id="dd" class="checkbox-custom" name="dd" type="checkbox">
								<label for="dd" class="checkbox-custom-label">Remember Me</label>
							</div>	
							<div class="eltio_k2">
								<a href="#">Lost Your Password?</a>
							</div>	
						</div>
					</div>
					
					<div class="form-group mb-3">
						<button type="submit" class="btn btn-md full-width bg-dark text-light fs-md ft-medium">Login</button>
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
							<h4 class="fs-md ft-medium mb-0 lh-1">$129</h4>
						</div>
					</div>
					<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
				</div>
			</div>
			
			<div class="d-flex align-items-center justify-content-between br-top br-bottom px-3 py-3">
				<h6 class="mb-0">Subtotal</h6>
				<h3 class="mb-0 ft-medium">$0</h3>
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
							<h4 class="fs-md ft-medium mb-0 lh-1">$129</h4>
						</div>
					</div>
					<div class="fls_last"><button class="close_slide gray"><i class="ti-close"></i></button></div>
				</div>
			</div>
			
			<div class="d-flex align-items-center justify-content-between br-top br-bottom px-3 py-3">
				<h6 class="mb-0">Subtotal</h6>
				<h3 class="mb-0 ft-medium">$0</h3>
			</div>
			
			<div class="cart_action px-3 py-3">
				<div class="form-group mb-3">
					<a href="{{ route('frontend.checkout') }}" class="btn d-block full-width btn-dark">Checkout Now</a>
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
    #quickViewWishlist.active,
    #quickViewWishlist.active i,
    #quickViewWishlist.active .fa-heart {
        color: #dc3545 !important;
    }
    #quickViewWishlist.active {
        border-color: #dc3545 !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentProductData = null;
    let selectedColor = null;
    let selectedSize = null;
    
    // Handle Quick View click
    $(document).on('click', 'a.quick-view-btn[data-product-slug]', function(e) {
        e.preventDefault();
        const productSlug = $(this).data('product-slug');
        const productIndex = $(this).data('product-index');
        
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
        
        selectedColor = cardSelectedColor;
        
        if (!productSlug) {
            console.error('Product slug not found');
            return;
        }
        
        // Show loader initially and hide slider completely
        $('#quickViewImagesLoader').show();
        $('#quickViewImages').hide().html('');
        
        // Open modal first
        $('#quickview').modal('show');
        
        // Fetch product data
        $.ajax({
            url: '{{ route("frontend.product.quickview") }}',
            method: 'GET',
            data: { slug: productSlug },
            success: function(response) {
                if (response.success && response.data) {
                    currentProductData = response.data;
                    populateQuickView(response.data, selectedColor);
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
        currentProductData = product;
        selectedColor = initialColor || (product.colors && product.colors.length > 0 ? product.colors[0] : null);
        
        // Set initial size based on selected color
        if (selectedColor && product.variants) {
            // Find first variant with the selected color to get its size
            const colorVariant = product.variants.find(function(v) {
                return v.color && v.color.toLowerCase() === selectedColor.toLowerCase();
            });
            if (colorVariant && colorVariant.size) {
                selectedSize = colorVariant.size;
            } else {
                selectedSize = (product.sizes && product.sizes.length > 0 ? product.sizes[0] : null);
            }
        } else {
            selectedSize = (product.sizes && product.sizes.length > 0 ? product.sizes[0] : null);
        }
        
        // Category - Show parent category if available, otherwise show category
        let categoryToShow = product.parent_category || product.category;
        if (categoryToShow) {
            $('#quickViewCategory').html('<span class="text-light bg-info rounded px-2 py-1">' + categoryToShow + '</span>');
        } else {
            $('#quickViewCategory').html('');
        }
        
        // Title
        $('#quickViewTitle').text(product.name);
        
        // Description
        $('#quickViewDescription').html(product.description || 'No description available');
        
        // Update price and images based on selected variant
        updateQuickViewVariant();
        
        // Images - will be updated by updateQuickViewVariant
        updateQuickViewImages();
        
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
            
            // Set initial selected color
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
            
            // Set initial selected size
            if (selectedSize) {
                $('.qv-size-option[data-size="' + selectedSize + '"]').prop('checked', true);
            }
        } else {
            $('#quickViewSizesContainer').hide();
        }
        
        // Add event listeners for color and size changes
        $(document).off('change', '.qv-color-option').on('change', '.qv-color-option', function() {
            if ($(this).is(':checked')) {
                selectedColor = $(this).data('color');
                
                // When color changes, try to find a matching size for that color
                if (currentProductData && currentProductData.variants) {
                    const colorVariant = currentProductData.variants.find(function(v) {
                        return v.color && v.color.toLowerCase() === selectedColor.toLowerCase();
                    });
                    if (colorVariant && colorVariant.size) {
                        selectedSize = colorVariant.size;
                        // Update the size radio button
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
        
        // Update buttons
        $('#quickViewAddToCart').attr('data-product-slug', product.slug);
        $('#quickViewWishlist').attr('data-product-id', product.id);
        
        // Check if product is in wishlist and update button state
        checkWishlistStatus(product.id);
        
        // Add wishlist button click handler
        $('#quickViewWishlist').off('click').on('click', function(e) {
            e.preventDefault();
            const productId = $(this).data('product-id');
            if (!productId) return;
            
            // Get session ID
            let sessionId = localStorage.getItem('session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('session_id', sessionId);
            }
            
            const $btn = $(this);
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
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({
                                    text: 'Product removed from wishlist',
                                    pos: 'top-right',
                                    showAction: false,
                                    duration: 3000,
                                    textColor: '#fff',
                                    backgroundColor: '#151515'
                                });
                            }
                            updateWishlistCount();
                        }
                    },
                    error: function(xhr) {
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
                    },
                    error: function(xhr) {
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
        });
        
        // Update share links
        const productUrl = window.location.origin + '/product?product=' + product.slug;
        $('#quickViewShareTwitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(productUrl) + '&text=' + encodeURIComponent(product.name));
        $('#quickViewShareFacebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(productUrl));
        $('#quickViewSharePinterest').attr('href', 'https://pinterest.com/pin/create/button/?url=' + encodeURIComponent(productUrl) + '&description=' + encodeURIComponent(product.name));
    }
    
    function updateQuickViewVariant() {
        if (!currentProductData) return;
        
        // Find matching variant based on selected color and size
        let matchingVariant = null;
        if (currentProductData.variants) {
            matchingVariant = currentProductData.variants.find(function(variant) {
                const colorMatch = !selectedColor || (variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase());
                const sizeMatch = !selectedSize || variant.size === selectedSize;
                return colorMatch && sizeMatch;
            });
        }
        
        // If no exact match, find by color only and update selectedSize
        if (!matchingVariant && selectedColor && currentProductData.variants) {
            matchingVariant = currentProductData.variants.find(function(variant) {
                return variant.color && variant.color.toLowerCase() === selectedColor.toLowerCase();
            });
            
            // If we found a variant by color, update selectedSize to match
            if (matchingVariant && matchingVariant.size) {
                selectedSize = matchingVariant.size;
                // Update the size radio button
                $('.qv-size-option[data-size="' + selectedSize + '"]').prop('checked', true);
            }
        }
        
        // Update price
        let priceHtml = '';
        if (matchingVariant) {
            const price = matchingVariant.price || 0;
            const salePrice = matchingVariant.sale_price;
            const hasSale = salePrice && salePrice < price;
            
            if (hasSale && salePrice) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">$' + 
                           Math.round(price) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg me-2">$' + Math.round(salePrice) + '</span>';
            } else {
                priceHtml = '<span class="ft-bold theme-cl fs-lg me-2">$' + Math.round(price) + '</span>';
            }
            
            // Stock status
            if (!matchingVariant.is_in_stock) {
                priceHtml += '<span class="ft-regular text-danger bg-light-danger py-1 px-2 fs-sm ms-2">Out of Stock</span>';
            }
        } else {
            // Fallback to product price range
            if (currentProductData.has_sale && currentProductData.min_sale_price) {
                priceHtml = '<span class="ft-medium text-muted line-through fs-md me-2">$' + 
                           Math.round(currentProductData.min_price) + '</span>' +
                           '<span class="ft-bold theme-cl fs-lg me-2">$' + 
                           Math.round(currentProductData.min_sale_price);
                if (currentProductData.max_sale_price && currentProductData.min_sale_price != currentProductData.max_sale_price) {
                    priceHtml += ' - $' + Math.round(currentProductData.max_sale_price);
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
    
    function updateQuickViewImages() {
        if (!currentProductData) return;
        
        // Show loader and hide slider completely
        $('#quickViewImagesLoader').show();
        $('#quickViewImages').hide();
        
        // Destroy existing slider first
        destroyQuickViewSlider();
        
        // Get images to display
        let imagesToLoad = [];
        if (!selectedColor) {
            // Use product images
            if (currentProductData.images && currentProductData.images.length > 0) {
                imagesToLoad = currentProductData.images;
            } else {
                imagesToLoad = [{url: '{{ asset("frontend/images/product/1.jpg") }}', alt: currentProductData.name}];
            }
        } else {
            // Use variant images for selected color
            const colorVariant = currentProductData.color_variants && currentProductData.color_variants[selectedColor] 
                ? currentProductData.color_variants[selectedColor] 
                : null;
            
            if (colorVariant && colorVariant.images && colorVariant.images.length > 0) {
                imagesToLoad = colorVariant.images;
            } else if (currentProductData.images && currentProductData.images.length > 0) {
                imagesToLoad = currentProductData.images;
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
                    
                    const $btn = $('#quickViewWishlist');
                    const $icon = $btn.find('i');
                    if (isInWishlist) {
                        $btn.addClass('active text-danger');
                        $icon.removeClass('lni lni-heart').addClass('fas fa-heart').css('color', '#dc3545');
                        $btn.css('color', '#dc3545');
                    } else {
                        $btn.removeClass('active text-danger');
                        $icon.removeClass('fas fa-heart').addClass('lni lni-heart').css('color', '');
                        $btn.css('color', '');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error checking wishlist status:', xhr);
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
    
});
</script>
@endpush
