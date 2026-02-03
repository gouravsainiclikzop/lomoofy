@extends('layouts.frontend')

@section('title', 'Home - Lomoofy Industries')

@section('content')


@php 
		$sections = \App\Models\Section::where('is_active', true)
				->orderBy('sort_order')
				->get();
@endphp 

@foreach($sections as $section)

    @switch($section->section_id)

        @case('issliderbanner-v1')
            @include('frontend.sections.issliderbanner-v1')
            @break

        @case('issliderbanner-v2')
            @include('frontend.sections.issliderbanner-v2')
            @break	 

        @case('isfeaturedcategory-v1')
			@include('frontend.sections.isfeaturedcategory-v1')
			@break

        @case('isfeaturedcategory-v2')
			@include('frontend.sections.isfeaturedcategory-v2')
			@break

				@case('isfeaturedcategory-v3')
			@include('frontend.sections.isfeaturedcategory-v3')
			@break

        @case('isfeaturedcategory-v4')
			@include('frontend.sections.isfeaturedcategory-v4')
			@break
			
			@case('isfeaturedcategory-v5')
			@include('frontend.sections.isfeaturedcategory-v5')
			@break

      @case('isfeaturedcategory-v6')
			@include('frontend.sections.isfeaturedcategory-v6')
			@break
 
			@case('isdealsoftheday-v1')
			@include('frontend.sections.isdealsoftheday-v1')
			@break

        @case('isproductwithcategorytabs-v1')
			@include('frontend.sections.isproductwithcategorytabs-v1')
			@break

			@case('isbestseller-v1')
			@include('frontend.sections.isbestseller-v1')
			@break

			@case('istrendingcategories-v1')
			@include('frontend.sections.istrendingcategories-v1')
			@break

        @case('isourcollection-v1')
			@include('frontend.sections.isourcollection-v1')
			@break

        @case('isourcollection-v2')
			@include('frontend.sections.isourcollection-v2')
			@break

        @case('isnewarrivals-v1')
			@include('frontend.sections.isnewarrivals-v1')
			@break

        @case('isparentcategoriescards-v1')
				@include('frontend.sections.isparentcategoriescards-v1')
				@break

        @case('isrecentlyviewed-v1')
			@include('frontend.sections.isrecentlyviewed-v1')
			@break

			@case('istestimonials-v1')
			@include('frontend.sections.istestimonials-v1')
			@break

        @case('isblog-v1')
			@include('frontend.sections.isblog-v1')
			@break

        @case('isinstagram-v1')
			@include('frontend.sections.isinstagram-v1')
			@break
 
            @case('ishighlights-v1')
                @include('frontend.sections.ishighlights-v1')
                @break
                
    @endswitch
@endforeach 

@endsection
<!-- instagram section ends here -->
  

@push('scripts')
{{-- Include cart pricing JavaScript helper --}}
@include('frontend.partials.product-pricing-cart-js')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset all color options to unchecked state on page load
    // This prevents browser from restoring previous selection state
    document.querySelectorAll('.color-option').forEach(function(radio) {
        radio.checked = false;
    });
    
    // Reset all product images to default state on page load
    document.querySelectorAll('[class*="product-image-"]').forEach(function(img) {
        const defaultImage = img.getAttribute('data-default-image');
        if (defaultImage) {
            img.src = defaultImage;
        }
    });
    
    // Handle color selection and price updates for new arrivals
    document.querySelectorAll('.color-option').forEach(function(radio) {
			return  ; // return for now
        radio.addEventListener('change', function() {
            if (this.checked) {
                const productIndex = this.getAttribute('data-product-index');
                const priceElement = document.querySelector('.product-price-' + productIndex);
                const imageElement = document.querySelector('.product-image-' + productIndex);
                const variantImage = this.getAttribute('data-variant-image');
                const selectedColorValue = this.getAttribute('data-color-value');
                
                // Update product image if variant image is available, otherwise keep default
                if (imageElement) {
                    if (variantImage) {
                        imageElement.src = variantImage;
                    } else {
                        // Revert to default product image if no variant image
                        const defaultImage = imageElement.getAttribute('data-default-image');
                        if (defaultImage) {
                            imageElement.src = defaultImage;
                        }
                    }
                }
                
                // Update price to show selected variant price using pricing component
                if (priceElement) {
                    // Create a virtual variant object for pricing function
                    const variantData = {
                        price: parseFloat(this.getAttribute('data-regular-price')) || 0,
                        sale_price: this.getAttribute('data-sale-price') || null,
                        discount_type: this.getAttribute('data-discount-type') || null,
                        discount_value: parseFloat(this.getAttribute('data-discount-value')) || 0,
                        discount_active: this.getAttribute('data-discount-active') === '1',
                        gst_type: true,
                        gst_percentage: 0
                    };
                    
                    // Use pricing component JavaScript if available
                    if (typeof generateCartItemPricing === 'function') {
                        const pricingHtml = generateCartItemPricing(variantData);
                        priceElement.innerHTML = pricingHtml;
                    } else {
                        // Fallback to simple price display
                        const displayPrice = parseFloat(this.getAttribute('data-price')) || 0;
                        const regularPrice = parseFloat(this.getAttribute('data-regular-price')) || 0;
                        const salePrice = this.getAttribute('data-sale-price');
                        const hasSale = this.getAttribute('data-has-sale') === '1' && salePrice;
                        
                        let priceHtml = '';
                        if (hasSale && salePrice) {
                            priceHtml = '<span class="text-decoration-line-through text-muted me-1">₹' + 
                                       Math.round(regularPrice).toLocaleString() + '</span>' +
                                       '₹' + Math.round(displayPrice).toLocaleString();
                        } else {
                            priceHtml = '₹' + Math.round(displayPrice).toLocaleString();
                        }
                        priceElement.innerHTML = priceHtml;
                    }
                }

                // Update the data-selected-color attribute on the quick view button
                const quickViewButton = document.querySelector('.quick-view-btn[data-product-index="' + productIndex + '"]');
                if (quickViewButton && selectedColorValue) {
                    quickViewButton.setAttribute('data-selected-color', selectedColorValue);
                }
            }
        });
    });
});

// Debug wishlist status on page load
$(document).ready(function() {
    console.log('=== Wishlist Debug ===');
    $('.snackbar-wishlist').each(function() {
        const $btn = $(this);
        const productId = $btn.data('product-id');
        const inWishlist = $btn.data('in-wishlist') === '1';
        const hasFas = $btn.find('i').hasClass('fas');
        const hasActive = $btn.hasClass('wishlist-active');
        
        if (productId == 14) {
            console.log('Product 14 Details:', {
                productId: productId,
                inWishlist: inWishlist,
                hasFas: hasFas,
                hasActive: hasActive,
                iconClasses: $btn.find('i').attr('class'),
                buttonClasses: $btn.attr('class'),
                iconStyle: $btn.find('i').attr('style')
            });
        }
    });
    
    // Also check localStorage session_id
    const sessionId = localStorage.getItem('session_id');
    
    // Check wishlist via API
    if (sessionId) {
        $.ajax({
            url: '/api/wishlist',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(response) {
                if (response.success && response.data) {
                    const productIds = response.data.map(item => item.product_id);
                    // Update hearts based on API response
                    response.data.forEach(function(item) {
                        const $btn = $('.snackbar-wishlist[data-product-id="' + item.product_id + '"]');
                        if ($btn.length) {
                            $btn.find('i').removeClass('far').addClass('fas').css('color', '#e52d2d');
                            $btn.addClass('wishlist-active').attr('data-in-wishlist', '1');
                        }
                    });
                }
            }
        });
    }
});


// countdown end date for collection section
(function () {
    if (!window.collectionCountdownEnd) return;

    const endDate = new Date(window.collectionCountdownEnd).getTime();

    const timer = setInterval(function () {
        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance <= 0) {
            clearInterval(timer);
            document.getElementById('countdown').style.display = 'none';
            return;
        }

        document.getElementById('days').innerText = Math.floor(distance / (1000 * 60 * 60 * 24));
        document.getElementById('hours').innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        document.getElementById('minutes').innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        document.getElementById('seconds').innerText = Math.floor((distance % (1000 * 60)) / 1000);
    }, 1000);
})();



</script>
@endpush

@push('styles')
<style>
    /* Product Card Image Consistency - 4:5 Aspect Ratio */
    .shop_thumb {
        width: 100%;
        padding-bottom: 125%; /* 5/4 = 1.25 = 125% for 4:5 aspect ratio */
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        aspect-ratio: 4 / 5; /* Modern browsers */
    }

    .shop_thumb .card-img-top,
    .shop_thumb > a {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .shop_thumb .card-img-top,
    .shop_thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }

    .shop_thumb:hover .card-img-top,
    .shop_thumb:hover img {
        transform: scale(1.05);
    }

    .product_grid .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn_love .fa-heart.fas,
    .btn_love .fa-heart.text-danger,
    .wishlist-active .fa-heart,
    .wishlist-active i.fa-heart,
    button.wishlist-active .fa-heart,
    .wishlist-heart-red,
    .btn_love .wishlist-heart-red {
        color: #e52d2d !important;
    }
    .btn_love i.fas.fa-heart {
        color: #e52d2d !important;
    }


		
</style>
@endpush
