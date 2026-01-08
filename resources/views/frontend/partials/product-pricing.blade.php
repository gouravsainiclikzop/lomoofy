@php
    // Calculate pricing values
    $basePrice = $variant->price ?? 0;
    $salePrice = $variant->sale_price ?? null;
    $finalPrice = $variant->final_price ?? $basePrice;
    $totalSavings = $variant->total_savings ?? 0;
    $hasDiscount = $variant->hasDiscountOrSale();
    $discountBadgeText = $variant->discount_badge_text ?? null;
    $isDiscountActive = $variant->hasActiveDiscount() || $variant->isOnSale();
    
    // GST settings
    $gstType = $gstType ?? true;
    $gstPercentage = $gstPercentage ?? 0;
    
    // Round prices for consistent calculations
    $basePrice = round($basePrice);
    $salePrice = $salePrice !== null ? round($salePrice) : null;
    $finalPrice = round($finalPrice);
    
    // Determine price to use (sale price if available, otherwise base price)
    $priceToUse = $salePrice !== null && $salePrice < $basePrice ? $salePrice : $basePrice;
    
    // Check if product has exclusive tax
    $hasExclusiveTax = ($gstType === false && $gstPercentage > 0);
    
    // Check if has extra discount
    $hasExtraDiscount = $variant->hasActiveDiscount();
    
    // Initialize display variables
    $displayBasePrice = $basePrice;
    $displayFinalPrice = $finalPrice;
    $displaySavings = $totalSavings;
    $showBasePrice = false;
    $taxLabel = 'Inclusive of all taxes';
    
    // Condition One: Exclusive tax without extra discount
    if ($hasExclusiveTax && !$hasExtraDiscount) {
        // Calculate tax on sale price (or base price if no sale)
        $gstAmount = $priceToUse * ($gstPercentage / 100);
        $displayFinalPrice = round($priceToUse + $gstAmount);
        
        // Don't show base price
        $showBasePrice = false;
        $taxLabel = 'Inclusive of taxes';
    }
    // Condition Two: Exclusive tax with extra discount
    elseif ($hasExclusiveTax && $hasExtraDiscount) {
        // Step 1: Calculate tax-inclusive price first
        $gstAmount = $priceToUse * ($gstPercentage / 100);
        $taxInclusivePrice = round($priceToUse + $gstAmount);
        
        // Step 2: Apply extra discount on tax-inclusive price
        $discountType = $variant->discount_type ?? '';
        $discountValue = $variant->discount_value ?? 0;
        
        if ($discountType === 'percentage') {
            $discountAmount = ($taxInclusivePrice * $discountValue) / 100;
            $displayFinalPrice = round(max(0, $taxInclusivePrice - $discountAmount));
        } elseif (in_array($discountType, ['amount', 'flat'])) {
            $displayFinalPrice = round(max(0, $taxInclusivePrice - $discountValue));
        } else {
            $displayFinalPrice = $taxInclusivePrice;
        }
        
        // Show tax-inclusive price as base price (strikethrough)
        $displayBasePrice = $taxInclusivePrice;
        $showBasePrice = true;
        
        // Calculate savings from base price to final price
        $displaySavings = $basePrice - $displayFinalPrice;
        
        // Update discount badge text
        if ($displayFinalPrice < $taxInclusivePrice) {
            $discountPercentage = 0;
            if ($discountType === 'percentage') {
                $discountPercentage = round($discountValue);
            } elseif (in_array($discountType, ['amount', 'flat']) && $taxInclusivePrice > 0) {
                $discountPercentage = round((($discountValue / $taxInclusivePrice) * 100));
            }
            
            if ($salePrice !== null && $salePrice < $basePrice && $basePrice > 0) {
                $salePercentage = round((($basePrice - $salePrice) / $basePrice) * 100);
                $discountBadgeText = $salePercentage . '% OFF';
                if ($discountPercentage > 0) {
                    $discountBadgeText .= ' (+ extra ' . $discountPercentage . '% discount)';
                }
            } else {
                $discountBadgeText = $discountPercentage . '% OFF';
            }
        }
        
        $taxLabel = 'Inclusive of taxes';
    }
    // Default: Inclusive tax or no tax
    else {
        // Use prices as-is
        $displayBasePrice = $basePrice;
        $displayFinalPrice = $finalPrice;
        $displaySavings = $totalSavings;
        $showBasePrice = ($displayBasePrice > $displayFinalPrice);
        $taxLabel = ($gstType === false) ? 'Exclusive of taxes' : 'Inclusive of all taxes';
    }
    
    // Round display values
    $displayBasePrice = round($displayBasePrice);
    $displayFinalPrice = round($displayFinalPrice);
    $displaySavings = round($displaySavings);
@endphp

<div class="product-pricing-component" data-base-price="{{ $basePrice }}" data-sale-price="{{ $salePrice }}" 
     data-discount-type="{{ $variant->discount_type ?? '' }}" 
     data-discount-value="{{ $variant->discount_value ?? 0 }}" 
     data-discount-active="{{ $variant->discount_active ? '1' : '0' }}"
     data-gst-type="{{ $gstType ? '1' : '0' }}" 
     data-gst-percentage="{{ $gstPercentage }}">
    
    {{-- Discount Badge --}}
    @if($hasDiscount && $discountBadgeText)
    <div class="pricing-badge mb-2">
        <span class="badge bg-danger px-3 py-2 fs-sm fw-bold">
            <i class="fas fa-tag me-1"></i>{{ $discountBadgeText }}
        </span>
    </div>
    @endif
    
    {{-- Main Price Display --}}
    <div class="pricing-main mb-2">
        <div class="d-flex align-items-baseline flex-wrap gap-2">
            {{-- Base Price (with strikethrough if needed) --}}
            @if($showBasePrice && $displayBasePrice > $displayFinalPrice)
            <span class="base-price text-muted text-decoration-line-through fs-5 fw-normal">
                ₹{{ number_format($displayBasePrice, 0) }}
            </span>
            @endif
            
            {{-- Final/Sale Price (prominent) --}}
            <span class="final-price theme-cl fw-bold {{ $hasDiscount ? 'fs-2' : 'fs-3' }}" style="color: #dc3545;">
                ₹{{ number_format($displayFinalPrice, 0) }}
            </span>
            
            {{-- Tax Label --}}
            <span class="tax-label text-muted fs-sm align-self-end">
                ({{ $taxLabel }})
            </span>
        </div>
    </div>
    
    {{-- Savings Display (show if there are savings) --}}
    @if($displaySavings > 0)
    <div class="pricing-savings mb-2">
        <span class="text-success fs-sm fw-medium">
            <i class="fas fa-wallet me-1"></i>You save ₹{{ number_format(round($displaySavings), 0) }}
        </span>
    </div>
    @endif
    
    {{-- Discount Status Row --}}
    @if($hasDiscount)
    <div class="pricing-status mt-2 pt-2 border-top border-light">
        <div class="d-flex align-items-center gap-2">
            <span class="status-indicator">
                <span class="badge bg-success badge-dot me-1"></span>
            </span>
            <span class="status-text text-success fs-sm fw-medium">
                @if($variant->hasActiveDiscount())
                    Discount Active
                @elseif($variant->isOnSale())
                    On Sale
                @endif
            </span>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .product-pricing-component {
        padding: 1rem 0;
    }
    
    .pricing-badge .badge {
        font-size: 0.875rem;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
    }
    
    .pricing-main .final-price {
        line-height: 1.2;
        letter-spacing: -0.5px;
    }
    
    .pricing-main .base-price {
        font-size: 1.25rem;
    }
    
    .pricing-savings {
        padding: 0.5rem 0;
    }
    
    .pricing-savings i {
        font-size: 0.875rem;
    }
    
    .pricing-status {
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }
    
    .badge-dot {
        width: 8px;
        height: 8px;
        padding: 0;
        border-radius: 50%;
        display: inline-block;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    /* Mobile-first responsive adjustments */
    @media (max-width: 576px) {
        .product-pricing-component {
            padding: 0.75rem 0;
        }
        
        .pricing-main .final-price {
            font-size: 1.75rem !important;
        }
        
        .pricing-main .base-price {
            font-size: 1rem;
        }
        
        .pricing-badge .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .pricing-savings {
            font-size: 0.8125rem;
        }
    }
    
    @media (min-width: 768px) {
        .pricing-main .final-price {
            font-size: 2.5rem;
        }
    }
    
    /* Ensure proper spacing on all screen sizes */
    .pricing-main .d-flex {
        align-items: baseline;
    }
    
    .tax-label {
        font-weight: 400;
        opacity: 0.8;
    }
</style>
@endpush
