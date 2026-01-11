{{-- Compact Pricing Component - Uses Centralized Pricing from ProductVariant --}}
@php
    // This component accepts either:
    // 1. A ProductVariant instance ($variant) - uses centralized getPricingData() method
    // 2. Individual values (for backward compatibility) - price, sale_price, original_price, discount_type, discount_value, discount_active, gstType, gstPercentage
    
    // Check if variant instance is provided (preferred method)
    if (isset($variant) && $variant instanceof \App\Models\ProductVariant) {
        // Use centralized pricing method from ProductVariant
        // Get GST settings - priority: passed params > product relationship > defaults
        $gstTypeParam = $gstType ?? null;
        $gstPercentageParam = $gstPercentage ?? null;
        
        // If not provided, try to get from product relationship
        if ($gstTypeParam === null && $variant->relationLoaded('product')) {
            $gstTypeParam = $variant->product->gst_type ?? true;
            $gstPercentageParam = $variant->product->gst_percentage ?? 0;
        } elseif ($gstTypeParam === null && isset($product) && $product instanceof \App\Models\Product) {
            $gstTypeParam = $product->gst_type ?? true;
            $gstPercentageParam = $product->gst_percentage ?? 0;
        }
        
        $pricing = $variant->getPricingData($gstTypeParam, $gstPercentageParam);
        
        // Extract values from pricing data
        // Use rounded values for display, unrounded values are kept in pricing array for calculations
        $basePrice = $pricing['base_price'] ?? ($variant->price ?? 0);
        $salePrice = $pricing['sale_price'] ?? null;
        $gstType = $gstTypeParam ?? true;
        $gstPercentage = $gstPercentageParam ?? 0;
        $discount_type = $variant->discount_type ?? null;
        $discount_value = $variant->discount_value ?? 0;
        $discount_active = $variant->discount_active ?? false;
        $displayBasePrice = $pricing['display_base_price_rounded'] ?? round($pricing['display_base_price']);
        $displayFinalPrice = $pricing['display_final_price_rounded'] ?? round($pricing['display_final_price']);
        $displaySavings = $pricing['display_savings_rounded'] ?? round($pricing['display_savings']);
        $showBasePrice = $pricing['show_base_price'];
        $taxLabel = $pricing['tax_label'];
        $discountBadgeText = $pricing['discount_badge_text'];
        $hasDiscount = $pricing['has_discount_or_sale'];
    } else {
        // Fallback to individual values (for backward compatibility - will be removed in future)
        // Use 2-decimal precision for all calculations, round only for display (matches centralized method)
        $basePrice = round($original_price ?? $price ?? 0, 2);
        $salePrice = $sale_price ? round($sale_price, 2) : null;
        $unitPrice = $unit_price ?? $basePrice;
        
        $isOnSale = $salePrice !== null && $salePrice < $basePrice;
        $priceToDiscount = $isOnSale ? $salePrice : $basePrice;
        
        $finalPrice = $priceToDiscount;
        $hasDiscount = false;
        $discountBadgeText = null;
        $totalSavings = 0;
        
        // Apply discount to the determined price (sale_price if available, otherwise base price)
        if (isset($discount_active) && $discount_active && isset($discount_type) && isset($discount_value) && $discount_value > 0) {
            if ($discount_type === 'percentage') {
                $discountAmount = round(($priceToDiscount * $discount_value) / 100, 2);
                $finalPrice = round(max(0, $priceToDiscount - $discountAmount), 2);
            } elseif (in_array($discount_type, ['amount', 'flat'])) {
                $finalPrice = round(max(0, $priceToDiscount - $discount_value), 2);
            }
            
            if ($finalPrice < $priceToDiscount) {
                $hasDiscount = true;
                // Calculate savings from base price to final price (after discount on sale)
                $totalSavings = round($basePrice - $finalPrice, 2);
            }
        } elseif ($isOnSale) {
            // Only sale price, no active discount
            $finalPrice = $salePrice;
            $hasDiscount = true;
            $totalSavings = round($basePrice - $salePrice, 2);
        }
        
        // GST settings
        $gstType = $gstType ?? true;
        $gstPercentage = $gstPercentage ?? 0;
        
        // Determine price to use (sale price if available, otherwise base price) - keep 2 decimals
        $priceToUse = $isOnSale ? $salePrice : $basePrice;
    
    // Check if product has exclusive tax
    $hasExclusiveTax = ($gstType === false && $gstPercentage > 0);
    
    // Check if has extra discount
    $hasExtraDiscount = isset($discount_active) && $discount_active && isset($discount_type) && isset($discount_value) && $discount_value > 0;
    
    // Initialize display variables
    $displayBasePrice = $basePrice;
    $displayFinalPrice = $finalPrice;
    $displaySavings = $totalSavings;
    $showBasePrice = false;
    $taxLabel = 'Inclusive of all taxes';
    
    // Condition One: Exclusive tax without extra discount
    if ($hasExclusiveTax && !$hasExtraDiscount) {
        // Calculate tax on sale price (or base price if no sale) - keep 2 decimals
        $gstAmount = round($priceToUse * ($gstPercentage / 100), 2);
        $displayFinalPrice = round($priceToUse + $gstAmount, 2);
        
        // Don't show base price
        $showBasePrice = false;
        $taxLabel = 'Inclusive of taxes';
    }
    // Condition Two: Exclusive tax with extra discount
    elseif ($hasExclusiveTax && $hasExtraDiscount) {
        // Step 1: Calculate tax-inclusive price first - keep 2 decimals
        $gstAmount = round($priceToUse * ($gstPercentage / 100), 2);
        $taxInclusivePrice = round($priceToUse + $gstAmount, 2);
        
        // Step 2: Apply extra discount on tax-inclusive price - keep 2 decimals
        if ($discount_type === 'percentage') {
            $discountAmount = round(($taxInclusivePrice * $discount_value) / 100, 2);
            $displayFinalPrice = round(max(0, $taxInclusivePrice - $discountAmount), 2);
        } elseif (in_array($discount_type, ['amount', 'flat'])) {
            $displayFinalPrice = round(max(0, $taxInclusivePrice - $discount_value), 2);
        } else {
            $displayFinalPrice = $taxInclusivePrice;
        }
        
        // Show tax-inclusive price as base price (strikethrough)
        $displayBasePrice = $taxInclusivePrice;
        $showBasePrice = true;
        
        // Calculate savings from base price to final price - keep 2 decimals
        $displaySavings = round($basePrice - $displayFinalPrice, 2);
        
        $taxLabel = 'Inclusive of taxes';
    }
    // Default: Inclusive tax or no tax
    else {
        // Use prices as-is (already in 2-decimal precision)
        $displayBasePrice = $basePrice;
        $displayFinalPrice = $finalPrice;
        $displaySavings = $totalSavings;
        $showBasePrice = ($displayBasePrice > $displayFinalPrice);
        $taxLabel = ($gstType === false) ? 'Exclusive of taxes' : 'Inclusive of all taxes';
    }
    
    // Round display values only for display (keep calculations in 2 decimals)
    $displayBasePrice = round($displayBasePrice);
    $displayFinalPrice = round($displayFinalPrice);
    $displaySavings = round($displaySavings);
        
        // Calculate OFF badge text based on actual savings (base_price vs display_final_price)
        // This shows the total discount percentage, regardless of how it was achieved (sale + discount, etc.)
        if ($displayFinalPrice < $basePrice && $basePrice > 0 && $hasDiscount) {
            $offPercentage = round((($basePrice - $displayFinalPrice) / $basePrice) * 100);
            if ($offPercentage > 0) {
                $discountBadgeText = $offPercentage . '% OFF';
            }
        }
    }
    
    $compact = $compact ?? false; // Compact mode for smaller displays
@endphp

<div class="product-pricing-compact {{ $compact ? 'compact' : '' }}" 
     data-base-price="{{ $basePrice }}" 
     data-sale-price="{{ $salePrice ?? '' }}"
     data-discount-type="{{ $discount_type ?? '' }}" 
     data-discount-value="{{ $discount_value ?? 0 }}" 
     data-discount-active="{{ ($discount_active ?? false) ? '1' : '0' }}"
     data-gst-type="{{ $gstType ? '1' : '0' }}" 
     data-gst-percentage="{{ $gstPercentage }}">
    
    {{-- Discount Badge - Show OFF badge when discount is active --}}
    @if($hasDiscount && $discountBadgeText)
    <div class="pricing-badge-compact mb-1">
        <span class="badge bg-danger px-2 py-1 {{ $compact ? 'fs-xxs' : 'fs-xs' }} fw-bold">
            {{ $discountBadgeText }}
        </span>
    </div>
    @endif
    
    {{-- Main Price Display --}}
    <div class="pricing-main-compact">
        <div class="d-flex align-items-baseline flex-wrap gap-1">
            {{-- Base Price (with strikethrough if needed) --}}
            @if($showBasePrice && $displayBasePrice > $displayFinalPrice)
            <span class="base-price-compact text-muted text-decoration-line-through {{ $compact ? 'fs-sm' : 'fs-md' }} fw-normal">
                ₹{{ number_format($displayBasePrice, 0) }}
            </span>
            @endif
            
            {{-- Final Price (prominent) --}}
            <span class="final-price-compact theme-cl fw-bold {{ $compact ? 'fs-md' : 'fs-lg' }}" style="color: #dc3545;">
                ₹{{ number_format($displayFinalPrice, 0) }}
            </span>
            
            {{-- Tax Label (only if not compact) --}}
            @if(!$compact)
            <span class="tax-label-compact text-muted fs-xs align-self-end">
                ({{ $taxLabel }})
            </span>
            @endif
        </div>
    </div>
    
    {{-- Savings Display (only if not compact) - show savings for sale price or discount --}}
    @if($displaySavings > 0 && !$compact)
    <div class="pricing-savings-compact mt-1">
        <span class="text-success fs-xs fw-medium">
            <i class="fas fa-wallet me-1"></i>You save ₹{{ number_format($displaySavings, 2) }}
        </span>
    </div>
    @endif
</div>

@push('styles')
<style>
    .product-pricing-compact {
        padding: 0.25rem 0;
    }
    
    .product-pricing-compact.compact {
        padding: 0;
    }
    
    .pricing-badge-compact .badge {
        font-size: 0.75rem;
        letter-spacing: 0.3px;
    }
    
    .pricing-main-compact .final-price-compact {
        line-height: 1.2;
    }
    
    .pricing-savings-compact i {
        font-size: 0.7rem;
    }
    
    @media (max-width: 576px) {
        .pricing-main-compact .final-price-compact {
            font-size: 1rem !important;
        }
        
        .pricing-badge-compact .badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>
@endpush

