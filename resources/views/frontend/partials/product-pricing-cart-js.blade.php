{{-- JavaScript Helper for Cart Item Pricing Display - Uses Centralized Pricing from Backend --}}
<script>
/**
 * Generate pricing HTML for cart items
 * Now consumes pricing data from centralized ProductVariant::getPricingData() method
 * Cart items should include pricing data from Cart API, or fetch via variant_id if available
 */
function generateCartItemPricing(item) {
    // Check if item has pricing data from backend (preferred)
    if (item.pricing && typeof item.pricing === 'object' && item.pricing.display_final_price !== undefined) {
        // Use pricing data from backend (centralized method)
        return renderCartItemPricingHTML(item.pricing, item);
    } else if (item.variant_id) {
        // If cart item has variant_id but no pricing, fetch it via API
        // Note: This is async, so we return a placeholder and update via callback
        fetch(`/api/catalog/variants/${item.variant_id}/pricing`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.pricing) {
                // Update the pricing display if element exists
                const pricingElement = document.querySelector(`[data-item-id="${item.id}"].cart-item-pricing`);
                if (pricingElement) {
                    pricingElement.innerHTML = renderCartItemPricingHTML(data.data.pricing, item);
                }
            } else {
                // Fallback to legacy calculation
                const pricingElement = document.querySelector(`[data-item-id="${item.id}"].cart-item-pricing`);
                if (pricingElement) {
                    pricingElement.innerHTML = generateCartItemPricingLegacy(item);
                }
            }
        })
        .catch(error => {
            console.warn('Failed to fetch pricing from API for cart item, using fallback:', error);
            const pricingElement = document.querySelector(`[data-item-id="${item.id}"].cart-item-pricing`);
            if (pricingElement) {
                pricingElement.innerHTML = generateCartItemPricingLegacy(item);
            }
        });
        
        // Return loading placeholder
        return '<div class="cart-item-pricing"><span class="text-muted">Loading...</span></div>';
    } else {
        // Fallback: Legacy calculation for backward compatibility
        return generateCartItemPricingLegacy(item);
    }
}

/**
 * Render cart item pricing HTML from centralized pricing data (UI only, no calculation)
 */
function renderCartItemPricingHTML(pricing, item) {
    // Extract values from centralized pricing data
    // Use 2-decimal precise values for display (base price and final price should show 2 decimals)
    const displayBasePrice = pricing.display_base_price ?? pricing.base_price ?? 0;
    const displayFinalPrice = pricing.display_final_price ?? pricing.final_price ?? 0;
    const displaySavings = pricing.display_savings_rounded ?? (pricing.display_savings ? Math.round(pricing.display_savings) : 0);
    const taxLabel = pricing.tax_label || 'Inclusive of all taxes';
    const discountBadgeText = pricing.discount_badge_text || null;
    const hasDiscount = pricing.has_discount_or_sale || false;
    
    // Helper function to format price with 2 decimals
    const formatPriceDisplay = (price) => {
        const value = parseFloat(price) || 0;
        return '₹' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    
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
    html += '<span class="final-price-compact theme-cl fw-bold fs-lg" style="color: #e52d2d;">';
    html += formatPriceDisplay(displayFinalPrice);
    html += '</span>';
    
    // Tax Label
    html += '<span class="tax-label-compact text-muted fs-sm align-self-end">';
    html += '(' + taxLabel + ')';
    html += '</span>';
    
    html += '</div></div>';
    
    // Savings Display (show if there are savings)
    if (displaySavings > 0) {
        html += '<div class="pricing-savings-compact mt-1">';
        html += '<span class="text-success fs-xs fw-medium">';
        html += '<i class="fas fa-wallet me-1"></i>You save ' + formatPriceDisplay(displaySavings);
        html += '</span></div>';
    }
    
    return html;
}

/**
 * Legacy cart item pricing calculation (fallback only - should be removed once all cart APIs use centralized pricing)
 * This is kept for backward compatibility but should not be used in new code
 * @deprecated Use renderCartItemPricingHTML with pricing data from API instead
 */
function generateCartItemPricingLegacy(item) {
    console.warn('Using legacy cart pricing calculation. Please update cart API to include centralized pricing data.');
    
    // Extract pricing data from cart item (legacy format)
    let basePrice = parseFloat(item.variant_price || item.original_variant_price || item.unit_price || 0);
    const salePrice = item.variant_sale_price ? parseFloat(item.variant_sale_price) : null;
    const discountType = item.discount_type || '';
    const discountValue = parseFloat(item.discount_value || 0);
    const discountActive = item.discount_active === true || item.discount_active === '1' || item.discount_active === 1;
    
    // Get GST settings
    let gstType = true;
    if (typeof item.gst_type !== 'undefined') {
        gstType = (typeof item.gst_type === 'string') ? (item.gst_type !== 'false' && item.gst_type !== '0') : item.gst_type;
    }
    const gstPercentage = parseFloat(item.gst_percentage || 0);
    
    // Calculate final price (simplified legacy logic)
    let priceToDiscount = basePrice;
    if (salePrice !== null && salePrice < basePrice) {
        priceToDiscount = salePrice;
    }
    
    let finalPrice = priceToDiscount;
    let hasDiscount = false;
    let discountBadgeText = null;
    let totalSavings = 0;
    
    // Apply discount if active
    if (discountActive && discountType && discountValue > 0) {
        if (discountType === 'percentage') {
            const discountAmount = (priceToDiscount * discountValue) / 100;
            finalPrice = Math.max(0, priceToDiscount - discountAmount);
        } else if (discountType === 'amount' || discountType === 'flat') {
            finalPrice = Math.max(0, priceToDiscount - discountValue);
        }
        
        if (finalPrice < priceToDiscount) {
            hasDiscount = true;
            totalSavings = basePrice - finalPrice;
        }
    } else if (salePrice !== null && salePrice < basePrice) {
        finalPrice = salePrice;
        hasDiscount = true;
        totalSavings = basePrice - salePrice;
    }
    
    // Keep 2-decimal precise values for display (base price and final price should show 2 decimals)
    const displayBasePrice = parseFloat(basePrice.toFixed(2));
    const displayFinalPrice = parseFloat(finalPrice.toFixed(2));
    const displaySavings = Math.round(totalSavings);
    const taxLabel = gstType ? 'Inclusive of all taxes' : 'Exclusive of taxes';
    
    // Calculate OFF badge text based on actual savings (base_price vs display_final_price)
    // This shows the total discount percentage, regardless of how it was achieved
    if (hasDiscount && displayFinalPrice < displayBasePrice && displayBasePrice > 0) {
        const offPercentage = Math.round(((displayBasePrice - displayFinalPrice) / displayBasePrice) * 100);
        if (offPercentage > 0) {
            discountBadgeText = offPercentage + '% OFF';
        }
    }
    
    // Use renderCartItemPricingHTML with 2-decimal precise display values
    return renderCartItemPricingHTML({
        display_base_price: displayBasePrice,
        display_final_price: displayFinalPrice,
        display_savings: displaySavings,
        display_base_price_rounded: displayBasePrice,
        display_final_price_rounded: displayFinalPrice,
        display_savings_rounded: displaySavings,
        tax_label: taxLabel,
        discount_badge_text: discountBadgeText,
        has_discount_or_sale: hasDiscount,
    }, item);
}
</script>