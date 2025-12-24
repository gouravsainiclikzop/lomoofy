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
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <div class="bg-light p-3 rounded">
                                <h5 class="mb-1">Order Total</h5>
                                <h3 class="text-dark mb-0">₹{{ number_format($order->total_amount, 2) }}</h3>
                            </div>
                        </div>
                    </div>
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
                                                <tr>
                                                    <td class="align-middle" style="width: 80px;">
                                                        @if($item->product && $item->product->primaryImage)
                                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                                                 alt="{{ $item->product_name }}" 
                                                                 class="img-fluid rounded" style="max-width: 60px;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px;">
                                                                <i class="lni lni-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                        @if($item->variant_name)
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
                                                        <strong>₹{{ number_format($item->total_price, 2) }}</strong>
                                                        @if($item->quantity > 1)
                                                            <br><small class="text-muted">₹{{ number_format($item->unit_price, 2) }} each</small>
                                                        @endif
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
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span>₹{{ number_format($order->subtotal, 2) }}</span>
                                        </div>
                                        @if($order->discount_amount > 0)
                                        <div class="d-flex justify-content-between mb-2 text-success">
                                            <span>Discount:</span>
                                            <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                                        </div>
                                        @endif
                                        @if($order->tax_amount > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tax:</span>
                                            <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Shipping:</span>
                                            <span>₹{{ number_format($order->shipping_amount, 2) }}</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total:</span>
                                            <span>₹{{ number_format($order->total_amount, 2) }}</span>
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
