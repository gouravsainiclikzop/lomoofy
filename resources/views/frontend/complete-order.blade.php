@extends('layouts.frontend')

@section('title', 'Order Complete - Lomoofy Industries')

@section('breadcrumbs')
			<div class="gray py-3">
				<div class="container">
					<div class="row">
						<div class="colxl-12 col-lg-12 col-md-12">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Support</a></li>
									<li class="breadcrumb-item active" aria-current="page">Complete Order</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</div>
			<!-- ======================= Top Breadcrubms ======================== -->
@endsection
			
@section('content')
<section class="middle">
    <div class="container">
        <!-- Display success/info messages -->
        @if (session('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success">
                        <h5 class="mb-2">Order Placed Successfully!</h5>
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        @if($order)
            <!-- Order Success Message -->
            <div class="row justify-content-center mb-5">
                <div class="col-12 col-md-10 col-lg-8 text-center">
                    <!-- Success Icon -->
                    <div class="p-4 d-inline-flex align-items-center justify-content-center circle bg-light-success text-success mx-auto mb-4">
                        <i class="lni lni-checkmark-circle fs-lg"></i>
                    </div>
                    <!-- Heading -->
                    <h2 class="mb-2 ft-bold text-success">Order Placed Successfully!</h2>
                    <!-- Text -->
                    <p class="ft-regular fs-md mb-4">
                        Thank you for your order! Your order <span class="text-body text-dark fw-bold">#{{ $order->order_number }}</span> 
                        has been placed successfully and is being processed.
                    </p>
                    <!-- <div class="row justify-content-center">
                        <div class="col-auto">
                            <div class="bg-light p-3 rounded">
                                <h5 class="mb-1">Order Total</h5>
                                @php
                                    $orderTotal = round($order->total_amount);
                                @endphp
                                <h3 class="text-dark mb-0">₹{{ number_format($orderTotal, 0) }} <span class="text-muted fs-sm">(Inclusive of all taxes)</span></h3>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>

            <!-- Order Details -->
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">Order Details</h5>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-warning text-dark">{{ ucfirst($order->status) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Order Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Order Information</h6>
                                    <p class="mb-1"><strong>Order Number:</strong> {{ $order->order_number }}</p>
                                    <p class="mb-1"><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                                    <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Cash on Delivery')) }}</p>
                                    <p class="mb-0"><strong>Payment Status:</strong> 
                                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Delivery Address</h6>
                                    @if($order->shipping_address)
                                        @php $shippingAddr = $order->shipping_address; @endphp
                                        <p class="mb-1">{{ $shippingAddr['address_line1'] }}</p>
                                        @if(!empty($shippingAddr['address_line2']))
                                            <p class="mb-1">{{ $shippingAddr['address_line2'] }}</p>
                                        @endif
                                        @if(!empty($shippingAddr['landmark']))
                                            <p class="mb-1"><small class="text-muted">Near: {{ $shippingAddr['landmark'] }}</small></p>
                                        @endif
                                        <p class="mb-0">{{ $shippingAddr['city'] }}, {{ $shippingAddr['state'] }} - {{ $shippingAddr['pincode'] }}</p>
                                        <p class="mb-0"><small class="text-muted">{{ $shippingAddr['country'] }}</small></p>
                                    @endif
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Order Items ({{ $order->items->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            @foreach($order->items as $item)
                                                @php
                                                    // Get variant image only - don't fallback to product image
                                                    $productImage = asset('assets/images/placeholder.jpg'); // Default placeholder
                                                    
                                                    if ($item->variant && $item->variant->images && $item->variant->images->count() > 0) {
                                                        // Try to get primary variant image first
                                                        $primaryVariantImage = $item->variant->images->where('is_primary', true)->first();
                                                        if ($primaryVariantImage) {
                                                            $productImage = asset('storage/' . $primaryVariantImage->image_path);
                                                        } else {
                                                            // Use first variant image
                                                            $firstVariantImage = $item->variant->images->first();
                                                            if ($firstVariantImage) {
                                                                $productImage = asset('storage/' . $firstVariantImage->image_path);
                                                            }
                                                        }
                                                    }
                                                    
                                                    // Parse variant attributes using new structured format - show all attributes
                                                    $parsed = null;
                                                    $allAttributes = [];
                                                    
                                                    if ($item->variant && $item->variant->attributes) {
                                                        $parsed = \App\Http\Controllers\FrontendController::parseVariantAttributes($item->variant->attributes);
                                                        
                                                        // Build all attributes array for display
                                                        if ($parsed['color'] && isset($parsed['color']['label'])) {
                                                            $allAttributes[] = ['label' => 'Color', 'value' => $parsed['color']['label']];
                                                        }
                                                        
                                                        // Add all variable attributes
                                                        if (isset($parsed['variable']) && is_array($parsed['variable'])) {
                                                            foreach ($parsed['variable'] as $key => $value) {
                                                                $attrLabel = ucfirst(str_replace('_', ' ', $key));
                                                                $attrValue = is_array($value) 
                                                                    ? (isset($value['label']) ? $value['label'] : (isset($value['value']) ? $value['value'] : ''))
                                                                    : (string)$value;
                                                                if ($attrValue) {
                                                                    $allAttributes[] = ['label' => $attrLabel, 'value' => $attrValue];
                                                                }
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="align-middle" style="width: 80px;">
                                                        <img src="{{ $productImage }}" 
                                                             alt="{{ $item->product_name }}" 
                                                             class="img-fluid rounded" style="max-width: 60px; height: 60px; object-fit: cover;"
                                                             onerror="this.src='{{ asset('assets/images/placeholder.jpg') }}'">
                                                    </td>
                                                    <td class="align-middle">
                                                        <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                        @if(!empty($allAttributes))
                                                            @foreach($allAttributes as $attr)
                                                                <small class="text-muted d-block">{{ $attr['label'] }}: {{ $attr['value'] }}</small>
                                                            @endforeach
                                                        @elseif($item->variant_name)
                                                            <small class="text-muted">{{ $item->variant_name }}</small>
                                                        @endif
                                                        @if($item->product_sku)
                                                            <br><small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-muted">Qty: {{ $item->quantity }}</span>
                                                    </td>
                                                    <td class="align-middle text-end">
                                                        @php
                                                            // Get product to check GST settings
                                                            $product = $item->product;
                                                            
                                                            // Get GST settings from product
                                                            $gstType = $product ? ($product->gst_type ?? true) : true;
                                                            $gstPercentage = $product ? ($product->gst_percentage ?? 0) : 0;
                                                            
                                                            // Use discount information from order_item (saved at checkout time)
                                                            $variantPrice = $item->original_variant_price ?? null;
                                                            $variantSalePrice = $item->variant_sale_price ?? null;
                                                            
                                                            // Calculate display price per unit
                                                            $displayPricePerUnit = $item->unit_price ?? 0;
                                                            if ($gstType && $variantPrice !== null) {
                                                                // Inclusive: use original variant price (base price)
                                                                $displayPricePerUnit = $variantPrice;
                                                            } elseif (!$gstType && $gstPercentage > 0) {
                                                                // Exclusive: extract base price from unit_price
                                                                $displayPricePerUnit = $item->unit_price / (1 + ($gstPercentage / 100));
                                                            }
                                                        @endphp
                                                        @include('frontend.partials.product-pricing-compact', [
                                                            'price' => $variantPrice ?? $displayPricePerUnit,
                                                            'sale_price' => $variantSalePrice,
                                                            'original_price' => $variantPrice ?? $displayPricePerUnit,
                                                            'discount_type' => $item->discount_type ?? null,
                                                            'discount_value' => $item->discount_value ?? null,
                                                            'discount_active' => $item->discount_active ?? false,
                                                            'gstType' => $gstType,
                                                            'gstPercentage' => $gstPercentage,
                                                            'compact' => true
                                                        ])
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="row justify-content-end">
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-light p-3 rounded">
                                        @php
                                            // Helper function to calculate final price for an order item (after discounts)
                                            $calculateItemFinalPrice = function($item) {
                                                $basePrice = $item->original_variant_price ?? $item->unit_price ?? 0;
                                                $salePrice = $item->variant_sale_price ?? null;
                                                $discountType = $item->discount_type ?? '';
                                                $discountValue = $item->discount_value ?? 0;
                                                $discountActive = $item->discount_active ?? false;
                                                
                                                // Round base price
                                                $basePrice = round($basePrice);
                                                
                                                // Round sale price if it exists
                                                $roundedSalePrice = null;
                                                if ($salePrice !== null) {
                                                    $roundedSalePrice = round($salePrice);
                                                }
                                                
                                                // Calculate final price
                                                $priceToDiscount = $basePrice;
                                                if ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                                                    $priceToDiscount = $roundedSalePrice;
                                                }
                                                
                                                $finalPrice = $priceToDiscount;
                                                
                                                // Apply discount if active
                                                if ($discountActive && $discountType && $discountValue > 0) {
                                                    if ($discountType === 'percentage') {
                                                        $discountAmount = ($priceToDiscount * $discountValue) / 100;
                                                        $finalPrice = max(0, $priceToDiscount - $discountAmount);
                                                    } elseif ($discountType === 'amount' || $discountType === 'flat') {
                                                        $finalPrice = max(0, $priceToDiscount - $discountValue);
                                                    }
                                                } elseif ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                                                    $finalPrice = $roundedSalePrice;
                                                }
                                                
                                                // Round final price
                                                return round($finalPrice);
                                            };
                                            
                                            // Calculate subtotal using inclusive prices (base + GST for exclusive items)
                                            // For exclusive items: base price + GST = inclusive price
                                            // For inclusive items: use price as-is
                                            $subtotal = 0;
                                            
                                            foreach ($order->items ?? [] as $orderItem) {
                                                // Calculate final price for this item (after variant discounts)
                                                $itemFinalPrice = $calculateItemFinalPrice($orderItem);
                                                $quantity = $orderItem->quantity ?? 1;
                                                
                                                // Get GST settings from order item (saved at checkout time)
                                                // Use saved tax info if available, otherwise fallback to product
                                                $gstType = $orderItem->tax_type ?? ($orderItem->product ? ($orderItem->product->gst_type ?? true) : true);
                                                $gstPercentage = $orderItem->tax_percentage ?? ($orderItem->product ? ($orderItem->product->gst_percentage ?? 0) : 0);
                                                
                                                // Calculate inclusive price
                                                $inclusivePrice = $itemFinalPrice;
                                                if ($gstPercentage > 0 && !$gstType) {
                                                    // Exclusive: Add GST to base price to get inclusive price
                                                    $inclusivePrice = $itemFinalPrice + ($itemFinalPrice * ($gstPercentage / 100));
                                                }
                                                // For inclusive items, $itemFinalPrice is already inclusive
                                                
                                                // Add to subtotal (inclusive price after discounts)
                                                $subtotal += $inclusivePrice * $quantity;
                                            }
                                            
                                            // Calculate totals with decimals (no rounding except for final total)
                                            // Use coupon discount from order (order-level discount)
                                            $discountAmount = $order->discount_amount ?? 0;
                                            $shippingAmount = $order->shipping_amount ?? 0;
                                            
                                            // Calculate Total (Incl. of all taxes)
                                            // Total = Subtotal - Discount + Shipping (all prices are already inclusive)
                                            $totalInclusive = $subtotal - $discountAmount + $shippingAmount;
                                            
                                            // Round total to whole number for display
                                            $totalRounded = round($totalInclusive);
                                        @endphp
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span>₹{{ number_format($subtotal, 2) }}</span>
                                        </div>
                                        <!-- Discount - always shown (order-level coupon discount) -->
                                        <div class="d-flex justify-content-between mb-2 {{ $discountAmount > 0 ? 'text-success' : '' }}">
                                            <span>Discount:</span>
                                            <span>-₹{{ number_format($discountAmount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Shipping:</span>
                                            <span>₹{{ number_format($shippingAmount, 2) }}</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total (Incl. of all taxes):</span>
                                            <span>₹{{ number_format($totalRounded, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row justify-content-center mt-4">
                <div class="col-12 col-md-6 text-center">
                    <a class="btn btn-dark me-3" href="{{ route('frontend.my-orders') }}">
                        <i class="lni lni-list me-2"></i>View All Orders
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('frontend.shop') }}">
                        <i class="lni lni-shopping-basket me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>

        @else
            <!-- No Order Found -->
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center">
                    <!-- Icon -->
                    <div class="p-4 d-inline-flex align-items-center justify-content-center circle bg-light-warning text-warning mx-auto mb-4">
                        <i class="lni lni-question-circle fs-lg"></i>
                    </div>
                    <!-- Heading -->
                    <h2 class="mb-2 ft-bold">Order Not Found</h2>
                    <!-- Text -->
                    <p class="ft-regular fs-md mb-5">
                        We couldn't find the order you're looking for. This might be because the order doesn't exist or you don't have permission to view it.
                    </p>
                    <!-- Buttons -->
                    <a class="btn btn-dark me-3" href="{{ route('frontend.my-orders') }}">View My Orders</a>
                    <a class="btn btn-outline-primary" href="{{ route('frontend.shop') }}">Continue Shopping</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
