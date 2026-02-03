@php
    // Use centralized pricing method from ProductVariant
    // This component now uses the centralized getPricingData() method
    
    if (isset($variant) && $variant instanceof \App\Models\ProductVariant) {
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
        
        // Use centralized pricing method
        $pricing = $variant->getPricingData($gstTypeParam, $gstPercentageParam);
        
        // Extract values from pricing data
        // Raw values (in 2 decimals) for calculations
        $basePrice = $pricing['base_price'];
        $salePrice = $pricing['sale_price'];
        $finalPrice = $pricing['final_price'];
        // Use 2-decimal precise values for display (base price and final price should show 2 decimals)
        $displayBasePrice = $pricing['display_base_price'] ?? $pricing['base_price'] ?? 0;
        $displayFinalPrice = $pricing['display_final_price'] ?? $pricing['final_price'] ?? 0;
        $displaySavings = $pricing['display_savings_rounded'] ?? round($pricing['display_savings']);
        $showBasePrice = $pricing['show_base_price'];
        $taxLabel = $pricing['tax_label'];
        $discountBadgeText = $pricing['discount_badge_text'];
        $hasDiscount = $pricing['has_discount_or_sale'];
        $totalSavings = $pricing['total_savings'];
        $isDiscountActive = $pricing['has_discount_or_sale'];
        
        // For backward compatibility with old accessors
        $gstType = $pricing['gst_type'];
        $gstPercentage = $pricing['gst_percentage'];
    } else {
        // Fallback if variant is not provided (should not happen, but keep for safety)
        $basePrice = $variant->price ?? 0;
        $salePrice = $variant->sale_price ?? null;
        $finalPrice = $variant->final_price ?? $basePrice;
        $totalSavings = $variant->total_savings ?? 0;
        $hasDiscount = $variant->hasDiscountOrSale();
        $discountBadgeText = $variant->discount_badge_text ?? null;
        $isDiscountActive = $variant->hasActiveDiscount() || $variant->isOnSale();
        $gstType = $gstType ?? true;
        $gstPercentage = $gstPercentage ?? 0;
        // Keep 2-decimal precise values for display (don't round base price and final price)
        $displayBasePrice = round($basePrice, 2);
        $displayFinalPrice = round($finalPrice, 2);
        $displaySavings = round($totalSavings);
        $showBasePrice = ($displayBasePrice > $displayFinalPrice);
        $taxLabel = ($gstType === false) ? 'Exclusive of taxes' : 'Inclusive of all taxes';
    }
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
            {{-- Base Price (with strikethrough if needed) - Show 2 decimals --}}
            @if($showBasePrice && $displayBasePrice > $displayFinalPrice)
            <span class="base-price text-muted text-decoration-line-through fs-5 fw-normal">
                ₹{{ number_format($displayBasePrice, 2) }}
            </span>
            @endif
            
            {{-- Final/Sale Price (prominent) - Show 2 decimals --}}
            <span class="final-price theme-cl fw-bold {{ $hasDiscount ? 'fs-2' : 'fs-3' }}" style="color: #e52d2d;">
                ₹{{ number_format($displayFinalPrice, 2) }}
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
