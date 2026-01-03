{{-- JavaScript Helper for Dynamic Pricing Updates --}}
<script>
/**
 * Update product pricing display based on variant data
 */
function updateProductPricing(variant) {
    const pricingComponent = document.querySelector('.product-pricing-component');
    if (!pricingComponent) return;
    
    // Get GST settings from component data attributes or defaults
    const gstType = pricingComponent.dataset.gstType === '1';
    const gstPercentage = parseFloat(pricingComponent.dataset.gstPercentage || 0);
    
    // Helper function to calculate display price
    const getDisplayPrice = (price) => {
        if (!gstType && gstPercentage > 0 && price > 0) {
            return price / (1 + (gstPercentage / 100));
        }
        return price;
    };
    
    // Extract pricing data from variant
    let basePrice = parseFloat(variant.price || variant.base_price || 0);
    const salePrice = variant.sale_price ? parseFloat(variant.sale_price) : null;
    const discountType = variant.discount_type || '';
    const discountValue = parseFloat(variant.discount_value || 0);
    const discountActive = variant.discount_active === true || variant.discount_active === '1' || variant.discount_active === 1;
    
    // Round base price first for consistent calculations
    basePrice = Math.round(basePrice);
    
    // Round sale price if it exists
    let roundedSalePrice = null;
    if (salePrice !== null && salePrice !== undefined) {
        roundedSalePrice = Math.round(salePrice);
    }
    
    // Calculate final price
    // Discount is applied to sale_price if it exists, otherwise to base price
    let priceToDiscount = basePrice;
    if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
        priceToDiscount = roundedSalePrice;
    }
    
    let finalPrice = priceToDiscount;
    let hasDiscount = false;
    let discountBadgeText = null;
    let totalSavings = 0;
    
    // Apply discount to the determined price (sale_price if available, otherwise base price)
    // Only apply discount if discount_active is true
    if (discountActive && discountType && discountValue > 0) {
        if (discountType === 'percentage') {
            const discountAmount = (priceToDiscount * discountValue) / 100;
            finalPrice = Math.max(0, priceToDiscount - discountAmount);
        } else if (discountType === 'amount' || discountType === 'flat') {
            finalPrice = Math.max(0, priceToDiscount - discountValue);
        }
        
        // Round final price to nearest whole number
        finalPrice = Math.round(finalPrice);
        
        if (finalPrice < priceToDiscount) {
            hasDiscount = true;
            // Calculate savings from base price to final price (after discount on sale)
            totalSavings = basePrice - finalPrice;
            
            // Generate badge text - show combined savings if both sale and discount exist
            if (roundedSalePrice !== null && roundedSalePrice < basePrice && basePrice > 0) {
                // Both sale price and active discount - show sale discount + extra discount
                const salePercentage = Math.round(((basePrice - roundedSalePrice) / basePrice) * 100);
                // Show the actual discount percentage that was applied (not percentage of base price)
                let extraDiscountPercentage = 0;
                if (discountType === 'percentage') {
                    extraDiscountPercentage = Math.round(discountValue);
                } else if (discountType === 'amount' || discountType === 'flat') {
                    // For flat discounts, calculate percentage from sale price
                    if (roundedSalePrice > 0) {
                        extraDiscountPercentage = Math.round(((discountValue / roundedSalePrice) * 100));
                    }
                }
                discountBadgeText = salePercentage + '% OFF';
                if (extraDiscountPercentage > 0) {
                    discountBadgeText += ' (+ extra ' + extraDiscountPercentage + '% discount)';
                }
            } else {
                // Only active discount - show discount amount/percentage
                if (discountType === 'percentage') {
                    discountBadgeText = Math.round(discountValue) + '% OFF';
                } else if (discountType === 'amount' || discountType === 'flat') {
                    discountBadgeText = '₹' + Math.round(discountValue).toLocaleString() + ' OFF';
                }
            }
        }
    } else if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
        // Only sale price, no active discount - show sale price with discount badge
        finalPrice = roundedSalePrice;
        hasDiscount = true;
        // Calculate percentage off for sale price (round properly)
        if (basePrice > 0) {
            const salePercentage = ((basePrice - roundedSalePrice) / basePrice) * 100;
            discountBadgeText = Math.round(salePercentage) + '% OFF';
        }
        totalSavings = basePrice - roundedSalePrice;
    }
    
    // Round total savings to nearest whole number
    totalSavings = Math.round(totalSavings);
    
    // Calculate display prices
    const displayBasePrice = getDisplayPrice(basePrice);
    const displayFinalPrice = getDisplayPrice(finalPrice);
    const displaySavings = getDisplayPrice(totalSavings);
    
    // Determine tax label
    const taxLabel = gstType ? 'Inclusive of all taxes' : 'Exclusive of taxes';
    
    // Helper function to format price (round to nearest whole number)
    const formatPriceDisplay = (price) => {
        const rounded = Math.round(price);
        return '₹' + rounded.toLocaleString();
    };
    
    // Build HTML
    let html = '';
    
    // Discount Badge
    if (hasDiscount && discountBadgeText) {
        html += '<div class="pricing-badge mb-2">';
        html += '<span class="badge bg-danger px-3 py-2 fs-sm fw-bold">';
        html += '<i class="fas fa-tag me-1"></i>' + discountBadgeText;
        html += '</span></div>';
    } else {
        // Clear badge area if no discount
        html += '<div class="pricing-badge mb-2" style="display: none;"></div>';
    }
    
    // Main Price Display
    html += '<div class="pricing-main mb-2">';
    html += '<div class="d-flex align-items-baseline flex-wrap gap-2">';
    
    // Base Price (with strikethrough if sale price or discount active)
    if (displayBasePrice > displayFinalPrice) {
        html += '<span class="base-price text-muted text-decoration-line-through fs-5 fw-normal">';
        html += formatPriceDisplay(displayBasePrice);
        html += '</span>';
    }
    
    // Final Price (prominent)
    html += '<span class="final-price theme-cl fw-bold ' + (hasDiscount ? 'fs-2' : 'fs-3') + '" style="color: #dc3545;">';
    html += formatPriceDisplay(displayFinalPrice);
    html += '</span>';
    
    // Tax Label
    html += '<span class="tax-label text-muted fs-sm align-self-end">';
    html += '(' + taxLabel + ')';
    html += '</span>';
    
    html += '</div></div>';
    
    // Savings Display (show if there are savings, regardless of discount badge)
    if (displaySavings > 0) {
        html += '<div class="pricing-savings mb-2">';
        html += '<span class="text-success fs-sm fw-medium">';
        html += '<i class="fas fa-wallet me-1"></i>You save ' + formatPriceDisplay(displaySavings);
        html += '</span></div>';
    } else {
        html += '<div class="pricing-savings mb-2" style="display: none;"></div>';
    }
    
    // Discount Status Row
    if (hasDiscount) {
        html += '<div class="pricing-status mt-2 pt-2 border-top border-light">';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="status-indicator">';
        html += '<span class="badge bg-success badge-dot me-1"></span>';
        html += '</span>';
        html += '<span class="status-text text-success fs-sm fw-medium">';
        
        if (salePrice && salePrice < basePrice) {
            html += 'On Sale';
        } else if (discountActive) {
            html += 'Discount Active';
        }
        
        html += '</span></div></div>';
    } else {
        html += '<div class="pricing-status mt-2 pt-2 border-top border-light" style="display: none;"></div>';
    }
    
    // Update component
    pricingComponent.innerHTML = html;
    
    // Show/hide elements based on discount status
    const badgeDiv = pricingComponent.querySelector('.pricing-badge');
    const savingsDiv = pricingComponent.querySelector('.pricing-savings');
    const statusDiv = pricingComponent.querySelector('.pricing-status');
    
    if (badgeDiv) {
        badgeDiv.style.display = (hasDiscount && discountBadgeText) ? 'block' : 'none';
    }
    if (savingsDiv) {
        savingsDiv.style.display = (hasDiscount && displaySavings > 0) ? 'block' : 'none';
    }
    if (statusDiv) {
        statusDiv.style.display = hasDiscount ? 'block' : 'none';
    }
}
</script>
