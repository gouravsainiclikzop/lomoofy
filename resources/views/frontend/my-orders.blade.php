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
											<a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
												<i class="lni lni-download me-1"></i>Invoice
											</a>
										</div>	
									</div>
									<div class="ord_list_body text-left">
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
												
												// Get status badge class
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
																	<p class="mb-1"><span class="text-dark medium">{{ $attr['label'] }}: {{ $attr['value'] }}</span></p>
																@endforeach
															@elseif($item->variant_name)
																<p class="mb-2"><span class="text-dark medium">{{ $item->variant_name }}</span></p>
															@endif
															<p class="mb-1"><span class="text-muted small">Qty: {{ $item->quantity }}</span></p>
															<h4 class="fs-sm ft-bold mb-0 lh-1">₹{{ number_format($item->total_price, 2) }}</h4>
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
												<a href="javascript:void(0);" class="ft-medium fs-sm"><i class="ti-close me-2"></i>Cancel Order</a>
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
												<h5 class="mb-0 fs-sm ft-bold">Total: ₹{{ number_format($order->total_amount, 2) }}</h5>
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
@endsection
