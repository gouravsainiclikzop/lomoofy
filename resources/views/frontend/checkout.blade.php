@extends('layouts.frontend')

@section('title', 'Checkout - Lomoofy Industries')

@section('breadcrumbs')
<div class="gray py-3">
    <div class="container">
        <div class="row">
            <div class="colxl-12 col-lg-12 col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.shoping-cart') }}">Cart</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<section class="middle">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                <div class="text-center d-block mb-5">
                    <h2>Checkout</h2>
                </div>
            </div>
        </div>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Display success/info messages -->
        @if (session('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">{{ session('info') }}</div>
                </div>
            </div>
        @endif

        <form action="{{ route('frontend.checkout.process') }}" method="POST" id="checkoutForm">
            @csrf
            <input type="hidden" name="session_id" value="{{ $sessionId }}">
            
            <div class="row justify-content-between">
                <div class="col-12 col-lg-7 col-md-12">
                    
                    <!-- Address Selection Section -->
                    <div class="checkout-section mb-5">
                        <h5 class="mb-4 ft-medium">Delivery Address</h5>
                        
                        @if(!$hasAddresses)
                            <div class="alert alert-warning">
                                <p class="mb-2">You don't have any saved addresses.</p>
                                <a href="{{ route('frontend.addresses') }}" class="btn btn-primary btn-sm">Add Address</a>
                            </div>
                        @elseif($singleAddress)
                            <!-- Single Address Scenario -->
                            <div class="alert alert-info">
                                <p class="mb-2"><i class="lni lni-information me-2"></i>You have one saved address. We'll use this for both shipping and billing.</p>
                            </div>
                            
                            @php $singleAddr = $addresses->first(); @endphp
                            <input type="hidden" name="shipping_address_id" value="{{ $singleAddr->id }}">
                            <input type="hidden" name="billing_address_id" value="{{ $singleAddr->id }}">
                            <input type="hidden" name="billing_same_as_shipping" value="1">
                            
                            <div class="single-address-display mb-4">
                                <div class="address-card border-primary">
                                    <div class="address-details">
                                        <h6 class="mb-2">
                                            <i class="lni lni-map-marker me-2"></i>Delivery Address
                                            @if($singleAddr->is_default)
                                                <span class="badge bg-primary ms-2">Default</span>
                                            @endif
                                        </h6>
                                        <p class="mb-1">{{ $singleAddr->address_line1 }}</p>
                                        @if($singleAddr->address_line2)
                                            <p class="mb-1">{{ $singleAddr->address_line2 }}</p>
                                        @endif
                                        @if($singleAddr->landmark)
                                            <p class="mb-1"><small class="text-muted">Near: {{ $singleAddr->landmark }}</small></p>
                                        @endif
                                        <p class="mb-0">{{ $singleAddr->city }}, {{ $singleAddr->state }} - {{ $singleAddr->pincode }}</p>
                                        <p class="mb-0"><small class="text-muted">{{ $singleAddr->country }}</small></p>
                                    </div>
                                </div>
                                
                                <div class="mt-3 text-center">
                                    <a href="{{ route('frontend.addresses') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="lni lni-plus me-1"></i> Add Another Address
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Shipping Address Selection -->
                            <div class="address-selection mb-4">
                                <label class="form-label fw-bold">Select Shipping Address:</label>
                                <div class="address-options">
                                    @foreach($addresses as $address)
                                        <div class="address-option mb-3">
                                            <div class="form-check address-card {{ $address->is_default ? 'border-primary' : '' }}">
                                                <input class="form-check-input" type="radio" 
                                                       name="shipping_address_id" 
                                                       value="{{ $address->id }}" 
                                                       id="shipping_{{ $address->id }}"
                                                       {{ old('shipping_address_id', $defaultShippingAddress?->id) == $address->id ? 'checked' : '' }}>
                                                <label class="form-check-label w-100" for="shipping_{{ $address->id }}">
                                                    <div class="address-details">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    {{ ucfirst($address->address_type) }} Address
                                                                    @if($address->is_default)
                                                                        <span class="badge bg-primary ms-2">Default</span>
                                                                    @endif
                                                                </h6>
                                                                <p class="mb-1">{{ $address->address_line1 }}</p>
                                                                @if($address->address_line2)
                                                                    <p class="mb-1">{{ $address->address_line2 }}</p>
                                                                @endif
                                                                @if($address->landmark)
                                                                    <p class="mb-1"><small class="text-muted">Near: {{ $address->landmark }}</small></p>
                                                                @endif
                                                                <p class="mb-0">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                                                <p class="mb-0"><small class="text-muted">{{ $address->country }}</small></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-3">
                                    <a href="{{ route('frontend.addresses') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="lni lni-plus me-1"></i> Add New Address
                                    </a>
                                </div>
                            </div>

                            <!-- Billing Address Section -->
                            <div class="billing-address-section">
                                <h6 class="mb-3 ft-medium">Billing Address</h6>
                                
                                <!-- Billing same as shipping checkbox -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           name="billing_same_as_shipping" 
                                           value="1" 
                                           id="billingSameAsShipping"
                                           {{ old('billing_same_as_shipping', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="billingSameAsShipping">
                                        Billing address same as shipping address
                                    </label>
                                </div>

                                <!-- Billing Address Selection (hidden when same as shipping) -->
                                <div id="billingAddressSelection" class="address-selection" style="{{ old('billing_same_as_shipping', true) ? 'display: none;' : '' }}">
                                    <label class="form-label fw-bold">Select Billing Address:</label>
                                    <div class="address-options">
                                        @foreach($addresses as $address)
                                            <div class="address-option mb-3">
                                                <div class="form-check address-card">
                                                    <input class="form-check-input" type="radio" 
                                                           name="billing_address_id" 
                                                           value="{{ $address->id }}" 
                                                           id="billing_{{ $address->id }}"
                                                           {{ old('billing_address_id', $defaultBillingAddress?->id) == $address->id ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="billing_{{ $address->id }}">
                                                        <div class="address-details">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <h6 class="mb-1">
                                                                        {{ ucfirst($address->address_type) }} Address
                                                                        @if($address->is_default)
                                                                            <span class="badge bg-primary ms-2">Default</span>
                                                                        @endif
                                                                    </h6>
                                                                    <p class="mb-1">{{ $address->address_line1 }}</p>
                                                                    @if($address->address_line2)
                                                                        <p class="mb-1">{{ $address->address_line2 }}</p>
                                                                    @endif
                                                                    @if($address->landmark)
                                                                        <p class="mb-1"><small class="text-muted">Near: {{ $address->landmark }}</small></p>
                                                                    @endif
                                                                    <p class="mb-0">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                                                    <p class="mb-0"><small class="text-muted">{{ $address->country }}</small></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Method Section -->
                    <div class="checkout-section mb-5">
                        <h5 class="mb-4 ft-medium">Payment Method</h5>
                        <div class="payment-methods">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" value="cash_on_delivery" id="cod" checked>
                                <label class="form-check-label" for="cod">
                                    <strong>Cash on Delivery</strong>
                                    <small class="d-block text-muted">Pay when you receive your order</small>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" value="online_payment" id="online">
                                <label class="form-check-label" for="online">
                                    <strong>Online Payment</strong>
                                    <small class="d-block text-muted">Pay securely online (Coming Soon)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes Section -->
                    <div class="checkout-section mb-5">
                        <h6 class="mb-3 ft-medium">Order Notes (Optional)</h6>
                        <div class="form-group">
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Any special instructions for your order...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-12 col-lg-4 col-md-12">
                    <div class="checkout-summary">
                        <div class="d-block mb-3">
                            <h5 class="mb-4">Order Summary ({{ $cart->items->count() }} items)</h5>
                            
                            <!-- Order Items -->
                            <ul class="list-group list-group-sm list-group-flush-y list-group-flush-x mb-4">
                                @foreach($cart->items as $item)
                                    @php
                                        $product = $item->product;
                                        $variant = $item->variant;
                                        
                                        // Get product image
                                        $imageUrl = asset('frontend/images/product/1.jpg'); // Default
                                        if ($variant && $variant->images && $variant->images->count() > 0) {
                                            $imageUrl = asset('storage/' . $variant->images->first()->image_path);
                                        } elseif ($product && $product->primaryImage) {
                                            $imageUrl = asset('storage/' . $product->primaryImage->image_path);
                                        } elseif ($product && $product->images && $product->images->count() > 0) {
                                            $imageUrl = asset('storage/' . $product->images->first()->image_path);
                                        }
                                        
                                        // Get variant attributes for display
                                        $variantAttrs = [];
                                        if ($variant && $variant->attributes) {
                                            $variantAttrs = is_string($variant->attributes) ? json_decode($variant->attributes, true) : $variant->attributes;
                                        }
                                        
                                        // Get color and size from variant attributes
                                        $colorValue = '';
                                        $sizeValue = '';
                                        $colorAttribute = null;
                                        $sizeAttribute = null;
                                        
                                        if ($product && $product->category) {
                                            $colorAttribute = $product->category->getAllProductAttributes()->where('type', 'color')->first();
                                            $sizeAttribute = $product->category->getAllProductAttributes()->where('type', 'size')->first();
                                        }
                                        if (!$colorAttribute) {
                                            $colorAttribute = \App\Models\ProductAttribute::where('type', 'color')->first();
                                        }
                                        if (!$sizeAttribute) {
                                            $sizeAttribute = \App\Models\ProductAttribute::where('type', 'size')->first();
                                        }
                                        
                                        foreach($variantAttrs as $key => $value) {
                                            if ($colorAttribute && $key == $colorAttribute->id) {
                                                $colorValue = $value;
                                            }
                                            if ($sizeAttribute && $key == $sizeAttribute->id) {
                                                $sizeValue = $value;
                                            }
                                        }
                                        
                                        // Build variant display string
                                        $variantDisplay = [];
                                        if ($sizeValue) {
                                            $variantDisplay[] = 'Size: ' . $sizeValue;
                                        }
                                        if ($colorValue) {
                                            $variantDisplay[] = 'Color: ' . $colorValue;
                                        }
                                        if ($item->variant_name && empty($variantDisplay)) {
                                            $variantDisplay[] = $item->variant_name;
                                        }
                                    @endphp
                                    <li class="list-group-item">
                                        <div class="row align-items-center">
                                            <div class="col-3">
                                                <a href="{{ route('frontend.product', ['product' => $product->slug ?? '']) }}">
                                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="img-fluid">
                                                </a>
                                            </div>
                                            <div class="col d-flex align-items-center">
                                                <div class="cart_single_caption ps-2">
                                                    <h4 class="product_title fs-md ft-medium mb-1 lh-1">{{ $item->product_name }}</h4>
                                                    @if(!empty($variantDisplay))
                                                        @foreach($variantDisplay as $display)
                                                            <p class="mb-1 lh-1"><span class="text-dark">{{ $display }}</span></p>
                                                        @endforeach
                                                    @endif
                                                    <p class="mb-1 lh-1"><span class="text-muted small">Qty: {{ $item->quantity }}</span></p>
                                                    <h4 class="fs-md ft-medium mb-3 lh-1">₹{{ number_format($item->total_price, 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Order Totals -->
                        <div class="card mb-4 gray">
                            <div class="card-body">
                                <ul class="list-group list-group-sm list-group-flush-y list-group-flush-x">
                                    <li class="list-group-item d-flex text-dark fs-sm ft-regular">
                                        <span>Subtotal</span> 
                                        <span class="ms-auto text-dark ft-medium">₹{{ number_format($cart->subtotal ?? 0, 2) }}</span>
                                    </li>
                                    @if(($cart->discount_amount ?? 0) > 0)
                                    <li class="list-group-item d-flex text-dark fs-sm ft-regular">
                                        <span>Discount</span> 
                                        <span class="ms-auto text-dark ft-medium text-success">-₹{{ number_format($cart->discount_amount, 2) }}</span>
                                    </li>
                                    @endif
                                    <li class="list-group-item d-flex text-dark fs-sm ft-regular">
                                        <span>Tax</span> 
                                        <span class="ms-auto text-dark ft-medium">₹{{ number_format($cart->tax_amount ?? 0, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex text-dark fs-sm ft-regular">
                                        <span>Shipping</span> 
                                        <span class="ms-auto text-dark ft-medium">₹{{ number_format($cart->shipping_amount ?? 0, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex text-dark fs-sm ft-regular border-top">
                                        <span class="fw-bold">Total</span> 
                                        <span class="ms-auto text-dark ft-medium fw-bold">₹{{ number_format($cart->total_amount ?? 0, 2) }}</span>
                                    </li>
                                    <li class="list-group-item fs-sm text-center">
                                        Shipping cost calculated at Checkout *
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-dark w-100 mb-3" id="placeOrderBtn">
                            <span class="btn-text">Place Your Order</span>
                            <span class="btn-loading d-none">
                                <i class="fa fa-spinner fa-spin me-2"></i>Processing...
                            </span>
                        </button>

                        <!-- Security Notice -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="lni lni-lock me-1"></i>
                                Your order information is secure and encrypted
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
.checkout-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.address-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.address-card:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.address-card.border-primary {
    border-color: #007bff !important;
}

.form-check-input:checked + .form-check-label .address-card {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.address-details h6 {
    color: #333;
    font-weight: 600;
}

.address-details p {
    color: #666;
    margin-bottom: 0.25rem;
}

.checkout-summary {
    position: sticky;
    top: 2rem;
}

.payment-methods .form-check {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.payment-methods .form-check:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.payment-methods .form-check-input:checked + .form-check-label {
    color: #007bff;
}

.btn-loading {
    pointer-events: none;
}

.single-address-display .address-card {
    background-color: #f8f9ff;
    border-color: #007bff;
}

.single-address-display .address-details h6 {
    color: #007bff;
}

@media (max-width: 991.98px) {
    .checkout-summary {
        position: static;
        margin-top: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<!-- Enhanced Checkout JavaScript -->
<script src="{{ asset('frontend/js/checkout.js') }}"></script>

<!-- Fallback jQuery implementation for older browsers -->
<script>
// Fallback for browsers that don't support modern JavaScript
if (!window.checkoutManager) {
    $(document).ready(function() {
        console.log('Using jQuery fallback for checkout functionality');
        
        // Handle billing same as shipping checkbox
        $('#billingSameAsShipping').on('change', function() {
            const billingSection = $('#billingAddressSelection');
            const billingRadios = $('input[name="billing_address_id"]');
            
            if ($(this).is(':checked')) {
                billingSection.slideUp();
                billingRadios.prop('required', false);
            } else {
                billingSection.slideDown();
                billingRadios.prop('required', true);
            }
        });

        // Address selection feedback
        $('input[name="shipping_address_id"], input[name="billing_address_id"]').on('change', function() {
            $(this).closest('.address-card').addClass('border-primary');
            $(this).closest('.address-options').find('.address-card').not($(this).closest('.address-card')).removeClass('border-primary');
        });

        // Form validation and submission
        $('#checkoutForm').on('submit', function(e) {
            const submitBtn = $('#placeOrderBtn');
            const btnText = submitBtn.find('.btn-text');
            const btnLoading = submitBtn.find('.btn-loading');
            
            // Show loading state
            submitBtn.prop('disabled', true);
            btnText.addClass('d-none');
            btnLoading.removeClass('d-none');
        });
    });
}
</script>
@endpush