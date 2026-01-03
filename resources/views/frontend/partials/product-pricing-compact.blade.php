{{-- Compact Pricing Component for Cart/Order Items --}}
@php
    // This component is for cart items or order items that may not have full variant data
    // Accepts: price, sale_price, original_price, discount_type, discount_value, discount_active, gstType, gstPercentage
    
    $basePrice = $original_price ?? $price ?? 0;
    $salePrice = $sale_price ?? null;
    $unitPrice = $unit_price ?? $basePrice;
    
    // Calculate final price
    // Discount is applied to sale_price if it exists, otherwise to base price
    $priceToDiscount = $basePrice;
    if ($salePrice && $salePrice < $basePrice) {
        $priceToDiscount = $salePrice;
    }
    
    $finalPrice = $priceToDiscount;
    $hasDiscount = false;
    $discountBadgeText = null;
    $totalSavings = 0;
    
    // Apply discount to the determined price (sale_price if available, otherwise base price)
    if (isset($discount_active) && $discount_active && isset($discount_type) && isset($discount_value) && $discount_value > 0) {
        if ($discount_type === 'percentage') {
            $discountAmount = ($priceToDiscount * $discount_value) / 100;
            $finalPrice = max(0, $priceToDiscount - $discountAmount);
        } elseif (in_array($discount_type, ['amount', 'flat'])) {
            $finalPrice = max(0, $priceToDiscount - $discount_value);
        }
        
        if ($finalPrice < $priceToDiscount) {
            $hasDiscount = true;
            // Calculate savings from base price to final price (after discount on sale)
            $totalSavings = $basePrice - $finalPrice;
            
            // Generate badge text - show combined savings if both sale and discount exist
            if ($salePrice && $salePrice < $basePrice && $basePrice > 0) {
                // Both sale price and active discount - show total savings percentage
                $totalPercentage = round((($basePrice - $finalPrice) / $basePrice) * 100);
                $discountBadgeText = $totalPercentage . '% OFF';
            } else {
                // Only active discount - show discount amount/percentage
                if ($discount_type === 'percentage') {
                    $discountBadgeText = round($discount_value) . '% OFF';
                } elseif (in_array($discount_type, ['amount', 'flat'])) {
                    $discountBadgeText = '₹' . number_format($discount_value, 0) . ' OFF';
                }
            }
        }
    } elseif ($salePrice && $salePrice < $basePrice) {
        // Only sale price, no active discount - show sale price but NO discount badge
        $finalPrice = $salePrice;
        // Don't set $hasDiscount = true here to avoid showing discount badge
        // Just show the price difference
        $totalSavings = $basePrice - $salePrice;
        // No discountBadgeText - don't show discount badge for sale price alone
    }
    
    // Apply GST calculations if needed
    $gstType = $gstType ?? true;
    $gstPercentage = $gstPercentage ?? 0;
    
    $getDisplayPrice = function($price) use ($gstType, $gstPercentage) {
        if ($gstType === false && $gstPercentage > 0 && $price > 0) {
            return $price / (1 + ($gstPercentage / 100));
        }
        return $price;
    };
    
    $displayBasePrice = $getDisplayPrice($basePrice);
    $displayFinalPrice = $getDisplayPrice($finalPrice);
    $displaySavings = $getDisplayPrice($totalSavings);
    
    $taxLabel = ($gstType === false) ? 'Exclusive of taxes' : 'Inclusive of all taxes';
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
    
    {{-- Discount Badge --}}
    @if($hasDiscount && $discountBadgeText && !$compact)
    <div class="pricing-badge-compact mb-1">
        <span class="badge bg-danger px-2 py-1 fs-xs fw-bold">
            {{ $discountBadgeText }}
        </span>
    </div>
    @endif
    
    {{-- Main Price Display --}}
    <div class="pricing-main-compact">
        <div class="d-flex align-items-baseline flex-wrap gap-1">
            {{-- Base Price (with strikethrough if sale price or discount active) --}}
            @if($displayBasePrice > $displayFinalPrice)
            <span class="base-price-compact text-muted text-decoration-line-through {{ $compact ? 'fs-sm' : 'fs-md' }} fw-normal">
                ₹{{ number_format($displayBasePrice, 2) }}
            </span>
            @endif
            
            {{-- Final Price (prominent) --}}
            <span class="final-price-compact theme-cl fw-bold {{ $compact ? 'fs-md' : 'fs-lg' }}" style="color: #dc3545;">
                ₹{{ number_format($displayFinalPrice, 2) }}
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

