{{-- JavaScript Helper for Cart Item Pricing Display --}}
<script>
/**
 * Generate pricing HTML for cart items
 */
function generateCartItemPricing(item) {
    // Use variant_price as base price, fallback to original_variant_price or unit_price
    let basePrice = parseFloat(item.variant_price || item.original_variant_price || item.unit_price || 0);
    const salePrice = item.variant_sale_price ? parseFloat(item.variant_sale_price) : null;
    const discountType = item.discount_type || '';
    const discountValue = parseFloat(item.discount_value || 0);
    const discountActive = item.discount_active === true || item.discount_active === '1' || item.discount_active === 1;
    
    // Get GST settings (for display label only, no price calculation)
    let gstType = true;
    if (typeof item.gst_type !== 'undefined') {
        gstType = (typeof item.gst_type === 'string') ? (item.gst_type !== 'false' && item.gst_type !== '0') : item.gst_type;
    }
    const gstPercentage = parseFloat(item.gst_percentage || 0);
    
    // Use prices as-is without GST calculation
    const getDisplayPrice = (price) => price;
    
    // Keep base price with decimals (don't round)
    
    // Keep sale price with decimals if it exists
    let roundedSalePrice = null;
    if (salePrice !== null && salePrice !== undefined) {
        roundedSalePrice = salePrice;
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
        
        // Keep final price with decimals (don't round)
        
        if (finalPrice < priceToDiscount) {
            hasDiscount = true;
            // Calculate savings from base price to final price (after discount on sale)
            totalSavings = basePrice - finalPrice;
            // Keep savings with decimals (don't round)
            
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
    
    // Helper function to format price with 2 decimal places
    const formatPriceDisplay = (price) => {
        return '₹' + parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    
    // Calculate inclusive prices for display
    // Base price: Show as-is (no GST calculation) - matching product page
    // Sale price and final price: Apply GST calculation only if exclusive
    let displayBasePrice = basePrice; // Always show base price as-is
    let displayFinalPrice = finalPrice;
    let displaySavings = totalSavings;
    let taxLabel = '';
    let gstAmount = 0;
    
    // Check if final price is based on sale price
    const isFinalPriceBasedOnSale = roundedSalePrice !== null && roundedSalePrice < basePrice && (finalPrice === roundedSalePrice || (discountActive && priceToDiscount === roundedSalePrice));
    
    if (!gstType && gstPercentage > 0) {
        // Exclusive: Only calculate GST on sale price or final price if based on sale price
        if (isFinalPriceBasedOnSale) {
            // Final price is based on sale price - calculate GST on final price
            gstAmount = finalPrice * (gstPercentage / 100);
            displayFinalPrice = finalPrice + gstAmount;
            
            // Calculate savings GST (savings = base - final, so GST on savings)
            const savingsGstAmount = totalSavings * (gstPercentage / 100);
            displaySavings = totalSavings + savingsGstAmount;
            
            taxLabel = 'Incl. GST ' + Math.round(gstPercentage) + '% (₹' + gstAmount.toFixed(2) + ')';
        } else {
            // Final price is based on base price - show as-is (no GST calculation)
            taxLabel = 'Exclusive of taxes';
        }
    } else {
        // Inclusive: Use prices as-is
        taxLabel = 'Inclusive of all taxes';
    }
    
    // Build HTML
    let html = '';
    
    // Discount Badge
    if (hasDiscount && discountBadgeText) {
        html += '<div class="pricing-badge-compact mb-1">';
        html += '<span class="badge bg-danger px-2 py-1 fs-xs fw-bold">';
        html += discountBadgeText;
        html += '</span></div>';
    }
    
    // Main Price Display
    html += '<div class="pricing-main-compact">';
    html += '<div class="d-flex align-items-baseline flex-wrap gap-1">';
    
    // Base Price (with strikethrough if sale price or discount active)
    if (displayBasePrice > displayFinalPrice) {
        html += '<span class="base-price-compact text-muted text-decoration-line-through fs-md fw-normal">';
        html += formatPriceDisplay(displayBasePrice);
        html += '</span>';
    }
    
    // Final Price (prominent)
    html += '<span class="final-price-compact theme-cl fw-bold fs-lg" style="color: #dc3545;">';
    html += formatPriceDisplay(displayFinalPrice);
    html += '</span>';
    
    // Tax Label
    html += '<span class="tax-label-compact text-muted fs-sm align-self-end">';
    html += '(' + taxLabel + ')';
    html += '</span>';
    
    html += '</div></div>';
    
    // Savings Display (show if there are savings, regardless of discount badge)
    if (displaySavings > 0) {
        html += '<div class="pricing-savings-compact mt-1">';
        html += '<span class="text-success fs-xs fw-medium">';
        html += '<i class="fas fa-wallet me-1"></i>You save ' + formatPriceDisplay(displaySavings);
        html += '</span></div>';
    }
    
    return html;
}
</script>

