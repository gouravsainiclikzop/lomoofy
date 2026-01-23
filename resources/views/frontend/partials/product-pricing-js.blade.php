{{-- JavaScript Helper for Dynamic Pricing Updates - Uses Centralized Pricing from Backend --}}
<script>
/**
 * Update product pricing display based on variant data
 * Now consumes pricing data from centralized ProductVariant::getPricingData() method
 * No calculation logic - only UI rendering
 */
function updateProductPricing(variant) {
    const pricingComponent = document.querySelector('.product-pricing-component');
    if (!pricingComponent) return;
    
    // Check if variant has pricing data from centralized method (preferred)
    let pricing = null;
    if (variant.pricing && typeof variant.pricing === 'object') {
        // Use pricing data from backend (centralized method)
        pricing = variant.pricing;
        renderPricingHTML(pricing, pricingComponent);
    } else if (variant.id) {
        // If variant has ID but no pricing, fetch it via API
        fetch(`/api/catalog/variants/${variant.id}/pricing`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.pricing) {
                renderPricingHTML(data.data.pricing, pricingComponent);
            } else {
                // Fallback to legacy calculation if API fails
                calculatePricingLegacy(variant, pricingComponent);
            }
        })
        .catch(error => {
            console.warn('Failed to fetch pricing from API, using fallback:', error);
            calculatePricingLegacy(variant, pricingComponent);
        });
        return; // Exit early, will update via callback
    } else {
        // Fallback: Legacy calculation for backward compatibility (should be removed eventually)
        calculatePricingLegacy(variant, pricingComponent);
        return;
    }
}

/**
 * Render pricing HTML from centralized pricing data (UI only, no calculation)
 */
function renderPricingHTML(pricing, pricingComponent) {
    // Extract values from centralized pricing data
    const displayBasePrice = pricing.display_base_price || 0;
    const displayFinalPrice = pricing.display_final_price || 0;
    const displaySavings = pricing.display_savings || 0;
    const showBasePrice = pricing.show_base_price || false;
    const taxLabel = pricing.tax_label || 'Inclusive of all taxes';
    const discountBadgeText = pricing.discount_badge_text || null;
    const hasDiscount = pricing.has_discount_or_sale || false;
    const isOnSale = pricing.is_on_sale || false;
    const hasActiveDiscount = pricing.has_active_discount || false;
    
    // Helper function to format price with 2 decimals
    const formatPriceDisplay = (price) => {
        const value = parseFloat(price) || 0;
        return '₹' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
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
        html += '<div class="pricing-badge mb-2" style="display: none;"></div>';
    }
    
    // Main Price Display
    html += '<div class="pricing-main mb-2">';
    html += '<div class="d-flex align-items-baseline flex-wrap gap-2">';
    
    // Base Price (with strikethrough if needed)
    if (showBasePrice && displayBasePrice > displayFinalPrice) {
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
    
    // Savings Display
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
        
        if (isOnSale) {
            html += 'On Sale';
        } else if (hasActiveDiscount) {
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
        savingsDiv.style.display = (displaySavings > 0) ? 'block' : 'none';
    }
    if (statusDiv) {
        statusDiv.style.display = hasDiscount ? 'block' : 'none';
    }
}

/**
 * Legacy pricing calculation (fallback only - should be removed once all endpoints use centralized pricing)
 * This is kept for backward compatibility but should not be used in new code
 * @deprecated Use renderPricingHTML with pricing data from API instead
 */
function calculatePricingLegacy(variant, pricingComponent) {
    console.warn('Using legacy pricing calculation. Please update to use centralized pricing API.');
    
    // Get GST settings from component data attributes
    const gstType = pricingComponent.dataset.gstType === '1';
    const gstPercentage = parseFloat(pricingComponent.dataset.gstPercentage || 0);
    
    // Extract pricing data from variant (legacy format)
    let basePrice = parseFloat(variant.price || variant.base_price || 0);
    const salePrice = variant.sale_price ? parseFloat(variant.sale_price) : null;
    const discountType = variant.discount_type || '';
    const discountValue = parseFloat(variant.discount_value || 0);
    const discountActive = variant.discount_active === true || variant.discount_active === '1' || variant.discount_active === 1;
    
    // Keep prices in 2 decimals (don't round)
    basePrice = parseFloat(basePrice.toFixed(2));
    const roundedSalePrice = salePrice !== null ? parseFloat(salePrice.toFixed(2)) : null;
    
    // Determine price to use
    let priceToUse = basePrice;
    if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
        priceToUse = roundedSalePrice;
    }
    
    // Calculate final price (keep 2 decimals)
    let finalPrice = priceToUse;
    let hasDiscount = false;
    let discountBadgeText = null;
    let totalSavings = 0;
    
    // Apply discount if active
    if (discountActive && discountType && discountValue > 0) {
        if (discountType === 'percentage') {
            const discountAmount = parseFloat(((priceToUse * discountValue) / 100).toFixed(2));
            finalPrice = parseFloat(Math.max(0, priceToUse - discountAmount).toFixed(2));
        } else if (discountType === 'amount' || discountType === 'flat') {
            finalPrice = parseFloat(Math.max(0, priceToUse - discountValue).toFixed(2));
        }
        
        if (finalPrice < priceToUse) {
            hasDiscount = true;
            totalSavings = parseFloat((basePrice - finalPrice).toFixed(2));
        }
    } else if (roundedSalePrice !== null && roundedSalePrice < basePrice) {
        finalPrice = roundedSalePrice;
        hasDiscount = true;
        totalSavings = parseFloat((basePrice - roundedSalePrice).toFixed(2));
    }
    
    totalSavings = Math.round(totalSavings);
    
    // Simple display (no GST calculation for legacy)
    // Keep 2-decimal precise values for display
    const displayBasePrice = basePrice;
    const displayFinalPrice = finalPrice;
    const displaySavings = totalSavings;
    const showBasePrice = displayBasePrice > displayFinalPrice;
    const taxLabel = gstType ? 'Inclusive of all taxes' : 'Exclusive of taxes';
    
    // Calculate OFF badge text based on actual savings (base_price vs display_final_price)
    // This shows the total discount percentage, regardless of how it was achieved
    if (hasDiscount && displayFinalPrice < displayBasePrice && displayBasePrice > 0) {
        const offPercentage = Math.round(((displayBasePrice - displayFinalPrice) / displayBasePrice) * 100);
        if (offPercentage > 0) {
            discountBadgeText = offPercentage + '% OFF';
        }
    }
    
    // Use renderPricingHTML with simplified data
    renderPricingHTML({
        display_base_price: displayBasePrice,
        display_final_price: displayFinalPrice,
        display_savings: displaySavings,
        show_base_price: showBasePrice,
        tax_label: taxLabel,
        discount_badge_text: discountBadgeText,
        has_discount_or_sale: hasDiscount,
        is_on_sale: roundedSalePrice !== null && roundedSalePrice < basePrice,
        has_active_discount: discountActive && discountType && discountValue > 0
    }, pricingComponent);
}
</script>