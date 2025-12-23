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
									<li class="breadcrumb-item"><a href="#">Support</a></li>
									<li class="breadcrumb-item active" aria-current="page">Checkout</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</div>
			<!-- ======================= Top Breadcrubms ======================== -->
@endsection
			
@section('content')
			<!-- ======================= Product Detail ======================== -->
			<section class="middle">
				<div class="container">
				
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="text-center d-block mb-5">
								<h2>Checkout</h2>
							</div>
						</div>
					</div>
					
					<div class="row justify-content-between">
						<div class="col-12 col-lg-7 col-md-12">
							<form>
								<h5 class="mb-4 ft-medium">Billing Details</h5>
								<div class="row mb-2 g-3">
									@php
										// Split customer name into first and last name
										$nameParts = explode(' ', $customer->full_name ?? '', 2);
										$firstName = $nameParts[0] ?? '';
										$lastName = $nameParts[1] ?? '';
									@endphp
									
									<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">First Name *</label>
											<input type="text" class="form-control" name="first_name" placeholder="First Name" value="{{ $firstName }}" required />
										</div>
									</div>
									
									<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Last Name *</label>
											<input type="text" class="form-control" name="last_name" placeholder="Last Name" value="{{ $lastName }}" required />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Email *</label>
											<input type="email" class="form-control" name="email" placeholder="Email" value="{{ $customer->email ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Company</label>
											<input type="text" class="form-control" name="company" placeholder="Company Name (optional)" />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Address 1 *</label>
											<input type="text" class="form-control" name="address_line1" placeholder="Address 1" value="{{ $defaultAddress->address_line1 ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Address 2</label>
											<input type="text" class="form-control" name="address_line2" placeholder="Address 2" value="{{ $defaultAddress->address_line2 ?? '' }}" />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Country *</label>
											<select class="custom-select" name="country" required>
											  <option value="India" {{ ($defaultAddress->country ?? 'India') == 'India' ? 'selected' : '' }}>India</option>
											  <option value="United States" {{ ($defaultAddress->country ?? '') == 'United States' ? 'selected' : '' }}>United States</option>
											  <option value="United Kingdom" {{ ($defaultAddress->country ?? '') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
											</select>
										</div>
									</div>
									
									<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">City / Town *</label>
											<input type="text" class="form-control" name="city" placeholder="City / Town" value="{{ $defaultAddress->city ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">State *</label>
											<input type="text" class="form-control" name="state" placeholder="State" value="{{ $defaultAddress->state ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">ZIP / Postcode *</label>
											<input type="text" class="form-control" name="pincode" placeholder="Zip / Postcode" value="{{ $defaultAddress->pincode ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Mobile Number *</label>
											<input type="text" class="form-control" name="phone" placeholder="Mobile Number" value="{{ $customer->phone ?? '' }}" required />
										</div>
									</div>
									
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
										<div class="form-group">
											<label class="text-dark mb-2">Additional Information</label>
											<textarea class="form-control ht-50" name="delivery_instructions" placeholder="Delivery instructions (optional)">{{ $defaultAddress->delivery_instructions ?? '' }}</textarea>
										</div>
									</div>
									
								</div>
								
								<div class="row mb-4">
									<div class="col-12 d-block">
										<input id="createaccount" class="checkbox-custom" name="createaccount" type="checkbox">
										<label for="createaccount" class="checkbox-custom-label">Create An Account?</label>
									</div>
								</div>
								
								<h5 class="mb-4 ft-medium">Payments</h5>
								<div class="row g-3 mb-4">
									<div class="col-12 col-lg-12 col-xl-12 col-md-12">
										<div class="panel-group pay_opy980" id="payaccordion">
									
											<!-- Pay By Paypal -->
											<div class="panel panel-default border">
												<div class="panel-heading" id="pay">
													<h4 class="panel-title">
											<a data-bs-toggle="collapse" role="button" data-parent="#payaccordion" href="#payPal" aria-expanded="true"  aria-controls="payPal" class="">PayPal<img src="{{ asset('frontend/images/paypal.png') }}" class="img-fluid" alt=""></a>
													</h4>
												</div>
												<div id="payPal" class="panel-collapse collapse show" aria-labelledby="pay" data-parent="#payaccordion">
													<div class="panel-body">
														<div class="form-group mb-3">
															<label class="text-dark mb-2">PayPal Email</label>
															<input type="text" class="form-control simple" placeholder="paypal@gmail.com">
														</div>
														<div class="form-group">
															<button type="submit" class="btn btn-dark btm-md full-width">Pay 400.00 USD</button>
														</div>
													</div>
												</div>
											</div>
											
											<!-- Pay By Strip -->
											<div class="panel panel-default border">
												<div class="panel-heading" id="stripes">
													<h4 class="panel-title">
											<a data-bs-toggle="collapse" role="button" data-parent="#payaccordion" href="#stripePay" aria-expanded="false"  aria-controls="stripePay" class="">Stripe<img src="{{ asset('frontend/images/strip.png') }}" class="img-fluid" alt=""></a>
													</h4>
												</div>
												<div id="stripePay" class="collapse" aria-labelledby="stripes" data-parent="#payaccordion">
													<div class="panel-body">
													
														<div class="row g-3">
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">Card Holder Name *</label>
																	<input type="text" class="form-control" placeholder="Dhananjay Preet" />
																</div>
															</div>
															
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">Card Number *</label>
																	<input type="text" class="form-control" placeholder="5426 4586 5485 4759" />
																</div>
															</div>									
														
															<div class="col-lg-5 col-md-5 col-sm-6">
																<div class="form-group">
																	<label class="text-dark mb-2">Expire Month *</label>
																	<select class="custom-select">
																	  <option value="1" selected="">January</option>
																	  <option value="2">February</option>
																	  <option value="3">March</option>
																	  <option value="4">April</option>
																	  <option value="5">May</option>
																	  <option value="6">June</option>
																	  <option value="7">July</option>
																	  <option value="8">August</option>
																	  <option value="9">September</option>
																	</select>
																</div>
															</div>
															
															<div class="col-lg-5 col-md-5 col-sm-6">
																<div class="form-group">
																	<label class="text-dark mb-2">Expire Year *</label>
																	<select class="custom-select">
																	  <option value="1" selected="">2010</option>
																	  <option value="2">2018</option>
																	  <option value="3">2019</option>
																	  <option value="4">2020</option>
																	  <option value="5">2021</option>
																	</select>
																</div>
															</div>
															
															<div class="col-lg-2 col-md-2 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">CVC *</label>
																	<input type="text" class="form-control" placeholder="CVV*">
																</div>
															</div>										
															
															<div class="col-lg-6 col-md-6 col-sm-12">
																<div class="form-group">
																	<input id="ak-2" class="checkbox-custom" name="ak-2" type="checkbox">
																	<label for="ak-2" class="checkbox-custom-label">By Continuing, you ar'e agree to conditions</label>
																</div>
															</div>
															
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group text-center">
																	<a href="#" class="btn btn-dark full-width">Pay 202.00 USD</a>
																</div>
															</div>
															
														</div>
														
													</div>
												</div>
											</div>
											
											<!-- Pay By Debit or credtit -->
											<div class="panel panel-default border">
												<div class="panel-heading" id="dabit">
													<h4 class="panel-title">
											<a data-bs-toggle="collapse" href="#payaccordion" data-bs-target="#debitPay" aria-expanded="false"  aria-controls="debitPay" class="">Debit Or Credit<img src="{{ asset('frontend/images/debit.png') }}" class="img-fluid" alt=""></a>
													</h4>
												</div>
												<div id="debitPay" class="panel-collapse collapse" aria-labelledby="dabit" data-parent="#payaccordion">
													<div class="panel-body">
														<div class="row g-3">
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">Card Holder Name *</label>
																	<input type="text" class="form-control" placeholder="Card Holder Name" />
																</div>
															</div>
															
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">Card Number *</label>
																	<input type="text" class="form-control" placeholder="7589 6356 8547 9120" />
																</div>
															</div>									
														
															<div class="col-lg-5 col-md-5 col-sm-6">
																<div class="form-group">
																	<label class="text-dark mb-2">Expire Month *</label>
																	<select class="custom-select">
																	  <option value="1" selected="">January</option>
																	  <option value="2">February</option>
																	  <option value="3">March</option>
																	  <option value="4">April</option>
																	  <option value="5">May</option>
																	  <option value="6">June</option>
																	  <option value="7">July</option>
																	  <option value="8">August</option>
																	  <option value="9">September</option>
																	</select>
																</div>
															</div>
															
															<div class="col-lg-5 col-md-5 col-sm-6">
																<div class="form-group">
																	<label class="text-dark mb-2">Expire Year *</label>
																	<select class="custom-select">
																	  <option value="1" selected="">2010</option>
																	  <option value="2">2018</option>
																	  <option value="3">2019</option>
																	  <option value="4">2020</option>
																	  <option value="5">2021</option>
																	</select>
																</div>
															</div>
															
															<div class="col-lg-2 col-md-2 col-sm-12">
																<div class="form-group">
																	<label class="text-dark mb-2">CVC *</label>
																	<input type="text" class="form-control" placeholder="CVV*" />
																</div>
															</div>										
															
															<div class="col-lg-6 col-md-6 col-sm-12">
																<div class="form-group">
																	<input id="al-2" class="checkbox-custom" name="al-2" type="checkbox">
																	<label for="al-2" class="checkbox-custom-label">By Continuing, you ar'e agree to conditions</label>
																</div>
															</div>
															
															<div class="col-lg-12 col-md-12 col-sm-12">
																<div class="form-group text-center">
																	<a href="#" class="btn btn-dark full-width">Pay 202.00 USD</a>
																</div>
															</div>
															
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
							</form>
						</div>
						
						<!-- Sidebar -->
						<div class="col-12 col-lg-4 col-md-12">
							<div class="d-block mb-3">
								<h5 class="mb-4">Order Items ({{ $cart->items->count() }})</h5>
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
							
							<div class="card mb-4 gray">
							  <div class="card-body">
								<ul class="list-group list-group-sm list-group-flush-y list-group-flush-x">
								  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
									<span>Subtotal</span> <span class="ms-auto text-dark ft-medium">${{ number_format($cart->subtotal ?? 0, 2) }}</span>
								  </li>
								  @if(($cart->discount_amount ?? 0) > 0)
								  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
									<span>Discount</span> <span class="ms-auto text-dark ft-medium text-success">-${{ number_format($cart->discount_amount, 2) }}</span>
								  </li>
								  @endif
								  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
									<span>Tax</span> <span class="ms-auto text-dark ft-medium">${{ number_format($cart->tax_amount ?? 0, 2) }}</span>
								  </li>
								  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
									<span>Shipping</span> <span class="ms-auto text-dark ft-medium">${{ number_format($cart->shipping_amount ?? 0, 2) }}</span>
								  </li>
								  <li class="list-group-item d-flex text-dark fs-sm ft-regular">
									<span>Total</span> <span class="ms-auto text-dark ft-medium">${{ number_format($cart->total_amount ?? 0, 2) }}</span>
								  </li>
								  <li class="list-group-item fs-sm text-center">
									Shipping cost calculated at Checkout *
								  </li>
								</ul>
							  </div>
							</div>
							
				<a class="btn btn-block btn-dark w-100 mb-3" href="{{ route('frontend.complete-order') }}">Place Your Order</a>
						</div>
						
					</div>
					
				</div>
			</section>
			<!-- ======================= Product Detail End ======================== -->
@endsection
