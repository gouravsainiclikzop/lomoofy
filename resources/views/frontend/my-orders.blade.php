@extends('layouts.frontend')

@section('title', 'My Orders - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
	<div class="container">
		<div class="row">
			<div class="colxl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
						<li class="breadcrumb-item"><a href="#">Dashboard</a></li>
						<li class="breadcrumb-item active" aria-current="page">My Order</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<!-- ======================= Top Breadcrubms ======================== -->
@endsection

@section('content')
<!-- ======================= Dashboard Detail ======================== -->
			<section class="middle">
				<div class="container">
					<div class="row align-items-start justify-content-between">
					
						@include('frontend.partials.customer-sidebar')
						
						<div class="col-12 col-md-12 col-lg-8 col-xl-8">
						
						@if($orders && $orders->count() > 0)
							@foreach($orders as $order)
								<!-- Single Order List -->
								<div class="ord_list_wrap border mb-4 mfliud">
									<div class="ord_list_head gray d-flex align-items-center justify-content-between px-3 py-3">
										<div class="olh_flex">
											<p class="m-0 p-0"><span class="text-muted">Order Number</span></p>
											<h6 class="mb-0 ft-medium">#{{ $order->order_number }}</h6>
											<p class="m-0 p-0 mt-1"><span class="text-muted small">Placed on {{ $order->created_at->format('M d, Y') }}</span></p>
										</div>	
										<div class="olh_flex d-flex gap-2">
											<a href="javascript:void(0);" class="btn btn-sm btn-dark">Track Order</a>
											<a href="{{ route('frontend.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
												<i class="lni lni-download me-1"></i>Invoice
											</a>
										</div>	
									</div>
					
									<div class="ord_list_body text-left">
										@foreach($order->items as $item)
											@php
												// Get variant image only - don't fallback to product image
												$productImage = asset('assets/images/placeholder.jpg');
					
												if ($item->variant && $item->variant->images && $item->variant->images->count() > 0) {
													$primaryVariantImage = $item->variant->images->where('is_primary', true)->first();
													if ($primaryVariantImage) {
														$productImage = asset('storage/' . $primaryVariantImage->image_path);
													} else {
														$firstVariantImage = $item->variant->images->first();
														if ($firstVariantImage) {
															$productImage = asset('storage/' . $firstVariantImage->image_path);
														}
													}
												}
					
												// Parse variant attributes
												$parsed = null;
												$allAttributes = [];
					
												if ($item->variant && $item->variant->attributes) {
													$parsed = \App\Http\Controllers\FrontendController::parseVariantAttributes($item->variant->attributes);
					
													if ($parsed['color'] && isset($parsed['color']['label'])) {
														$allAttributes[] = ['label' => 'Color', 'value' => $parsed['color']['label']];
													}
					
													if (isset($parsed['variable']) && is_array($parsed['variable'])) {
														foreach ($parsed['variable'] as $key => $value) {
															$attrLabel = ucfirst(str_replace('_', ' ', $key));
															$attrValue = is_array($value) 
																? ($value['label'] ?? ($value['value'] ?? ''))
																: (string)$value;
					
															if ($attrValue) {
																$allAttributes[] = ['label' => $attrLabel, 'value' => $attrValue];
															}
														}
													}
												}
					
												// GST + price logic
												$product = $item->product;
												$gstType = $product ? ($product->gst_type !== null ? (bool)$product->gst_type : true) : true;
												$gstPercentage = $product ? ($product->gst_percentage ?? 0) : 0;
					
												$variantPrice = $item->original_variant_price ?? null;
												$variantSalePrice = $item->variant_sale_price ?? null;
					
												$displayPricePerUnit = $item->unit_price ?? 0;
					
												if ($gstType && $variantPrice !== null) {
													$displayPricePerUnit = $variantPrice;
												} elseif (!$gstType && $gstPercentage > 0) {
													$displayPricePerUnit = $item->unit_price / (1 + ($gstPercentage / 100));
												}
					
												// Status badge
												$statusClass = 'bg-light-warning text-warning';
												$statusText = ucfirst($order->status);
					
												switch(strtolower($order->status)) {
													case 'pending':
														$statusClass = 'bg-light-warning text-warning';
														break;
													case 'processing':
														$statusClass = 'bg-light-info text-info';
														break;
													case 'shipped':
														$statusClass = 'bg-light-primary text-primary';
														break;
													case 'delivered':
														$statusClass = 'bg-light-success text-success';
														break;
													case 'cancelled':
													case 'refunded':
														$statusClass = 'bg-light-danger text-danger';
														break;
												}
											@endphp
					
											<div class="row align-items-center justify-content-center m-0 py-4 {{ !$loop->last ? 'br-bottom' : '' }}">
												<div class="col-xl-5 col-lg-5 col-md-5 col-12">
													<div class="cart_single d-flex align-items-start mfliud-bot">
														<div class="cart_selected_single_thumb">
															<a href="#"><img src="{{ $productImage }}" width="75" class="img-fluid rounded" alt="{{ $item->product_name }}"></a>
														</div>
														<div class="cart_single_caption text-start ps-3">
															@if($item->product && $item->product->category)
																<p class="mb-0"><span class="text-muted small">{{ $item->product->category->name }}</span></p>
															@endif
					
															<h4 class="product_title fs-sm ft-medium mb-1 lh-1">{{ $item->product_name }}</h4>
					
															@if(!empty($allAttributes))
																@foreach($allAttributes as $attr)
																	<p class="mb-1">
																		<span class="text-dark medium">{{ $attr['label'] }}: {{ $attr['value'] }}</span>
																	</p>
																@endforeach
															@elseif($item->variant_name)
																<p class="mb-2"><span class="text-dark medium">{{ $item->variant_name }}</span></p>
															@endif
					
															<p class="mb-1"><span class="text-muted small">Qty: {{ $item->quantity }}</span></p>
					
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
														</div>
													</div>
												</div>
					
												<div class="col-xl-3 col-lg-3 col-md-3 col-6 text-start">
													<p class="mb-1 p-0"><span class="text-muted">Status</span></p>
													<div class="delv_status">
														<span class="ft-medium small {{ $statusClass }} rounded px-3 py-1">{{ $statusText }}</span>
													</div>
												</div>
					
												<div class="col-xl-4 col-lg-4 col-md-4 col-6 text-start">
													<p class="mb-1 p-0"><span class="text-muted">Order Date:</span></p>
													<h6 class="mb-0 ft-medium fs-sm">{{ $order->created_at->format('M d, Y') }}</h6>
												</div>
											</div>
										@endforeach
									</div>
					
									<div class="ord_list_footer d-flex align-items-center justify-content-between br-top px-3 text-start">
										<div class="col-xl-3 col-lg-3 col-md-4 olf_flex text-left px-0 py-2 br-right">
											@if(in_array(strtolower($order->status), ['pending', 'processing']))
												<button type="button" 
													class="btn-cancel-order ft-medium fs-sm text-danger border-0 bg-transparent p-0"
													data-order-id="{{ $order->id }}" 
													data-order-number="{{ $order->order_number }}">
													<i class="ti-close me-2"></i>Cancel Order
												</button>
											@endif
										</div>
					
										<div class="col-xl-9 col-lg-9 col-md-8 pe-0 ps-2 py-2 olf_flex d-flex align-items-center justify-content-between">
											<div class="olf_flex_inner hide_mob">
												<p class="m-0 p-0">
													<span class="text-muted medium">
														@if($order->payment_method)
															Paid using {{ ucfirst($order->payment_method) }}
														@else
															Payment: {{ ucfirst($order->payment_status) }}
														@endif
													</span>
												</p>
											</div>
					
											<div class="olf_inner_right">
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
													
													// Recalculate subtotal and tax from order items using final prices (after discounts)
													$recalculatedSubtotal = 0;
													$recalculatedTax = 0;
													$hasExclusiveItems = false;
													
													foreach ($order->items ?? [] as $orderItem) {
														// Calculate final price for this item (after discounts)
														$itemFinalPrice = $calculateItemFinalPrice($orderItem);
														
														$orderProduct = $orderItem->product;
														$gstType = $orderProduct ? ($orderProduct->gst_type ?? true) : true;
														$gstPercentage = $orderProduct ? ($orderProduct->gst_percentage ?? 0) : 0;
														$quantity = $orderItem->quantity ?? 1;
														
														if ($gstPercentage > 0) {
															if (!$gstType) {
																// Exclusive of tax: Extract base price for tax calculation
																$baseForTax = $itemFinalPrice / (1 + ($gstPercentage / 100));
																$itemTax = $baseForTax * ($gstPercentage / 100);
																$recalculatedTax += $itemTax * $quantity;
																$hasExclusiveItems = true;
																// Use final price in subtotal (will have tax added separately)
																$recalculatedSubtotal += $itemFinalPrice * $quantity;
															} else {
																// Inclusive of tax: Use final price directly (tax already included)
																$recalculatedSubtotal += $itemFinalPrice * $quantity;
																// Tax is already included, don't add separately
															}
														} else {
															// No GST - use final price directly
															$recalculatedSubtotal += $itemFinalPrice * $quantity;
														}
													}
													
													// Round all amounts to whole numbers
													$subtotal = round($recalculatedSubtotal);
													// Use coupon discount from order (order-level discount)
													$couponDiscountAmount = round($order->discount_amount ?? 0);
													$totalAfterDiscount = $subtotal - $couponDiscountAmount;
													
													// Use recalculated tax if we have exclusive items, otherwise use saved tax
													$taxAmount = $hasExclusiveItems ? round($recalculatedTax) : round($order->tax_amount ?? 0);
													$shippingAmount = round($order->shipping_amount ?? 0);
													$grandTotal = $totalAfterDiscount + $taxAmount + $shippingAmount;
													$payableAmount = round($grandTotal);
												@endphp
												<h5 class="mb-0 fs-sm ft-bold">Payable: ₹{{ number_format($payableAmount, 0) }}</h5>

											</div>
										</div>
									</div>
								</div>
								<!-- End Order List -->
							@endforeach
						@else
							<div class="card-wrap border rounded mb-4">
								<div class="card-wrap-body px-3 py-5 text-center">
									<i class="lni lni-package fs-1 text-muted mb-3"></i>
									<h4 class="ft-medium mb-2">No Orders Yet</h4>
									<p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
									<a href="{{ route('frontend.shop') }}" class="btn btn-dark">Start Shopping</a>
								</div>
							</div>
						@endif
					
					</div>
					
						
					</div>
				</div>
			</section>
<!-- ======================= Dashboard Detail End ======================== -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle cancel order button clicks
    document.querySelectorAll('.btn-cancel-order').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const orderId = this.getAttribute('data-order-id');
            const orderNumber = this.getAttribute('data-order-number');
            
            // Confirm cancellation
            if (!confirm(`Are you sure you want to cancel order #${orderNumber}? This action cannot be undone.`)) {
                return;
            }
            
            // Disable button and show loading state
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="ti-reload me-2"></i>Cancelling...';
            
            // Make API call
            fetch(`/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Order cancelled successfully. Stock has been restored.');
                    // Reload page to reflect changes
                    window.location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'Error cancelling order. Please try again.');
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the order. Please try again.');
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
});
</script>
@endpush

@endsection
