{{-- Shipping Section --}}
<div class="card mb-3" id="shippingSection" style="display: none;">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-shipping-fast me-2"></i>Shipping
            </h6>
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#shippingInfoModal">
                <i class="fas fa-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            {{-- Requires Shipping --}}
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="requiresShipping" name="requires_shipping" 
                           {{ old('requires_shipping', $product->requires_shipping ?? true) ? '' : '' }}>
                    <label class="form-check-label form-label-sm" for="requiresShipping">
                        <strong>Requires Shipping</strong>
                        <small class="text-muted d-block">This product needs to be shipped to customers</small>
                    </label>
                </div>
            </div>

            {{-- Free Shipping --}}
            <div class="col-12" id="freeShippingField">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="freeShipping" name="free_shipping" 
                           {{ old('free_shipping', $product->free_shipping ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label form-label-sm" for="freeShipping">
                        <strong>Free Shipping</strong>
                        <small class="text-muted d-block">Offer free shipping for this product</small>
                    </label>
                </div>
            </div>

            {{-- Physical Dimensions --}}
            <div class="col-12" id="physicalDimensions">
                <h6 class="mb-2 form-label-sm">Physical Dimensions</h6>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label for="productWeight" class="form-label form-label-sm">Weight (lbs)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm" id="productWeight" name="weight" 
                                   step="0.01" min="0" value="{{ old('weight', $product->weight ?? '') }}">
                            <span class="input-group-text">lbs</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label for="productLength" class="form-label form-label-sm">Length (in)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm" id="productLength" name="length" 
                                   step="0.01" min="0" value="{{ old('length', $product->length ?? '') }}">
                            <span class="input-group-text">in</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label for="productWidth" class="form-label form-label-sm">Width (in)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm" id="productWidth" name="width" 
                                   step="0.01" min="0" value="{{ old('width', $product->width ?? '') }}">
                            <span class="input-group-text">in</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label for="productHeight" class="form-label form-label-sm">Height (in)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm" id="productHeight" name="height" 
                                   step="0.01" min="0" value="{{ old('height', $product->height ?? '') }}">
                            <span class="input-group-text">in</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            {{-- Shipping Class --}}
            <div class="col-md-6" id="shippingClassField">
                <label for="shippingClass" class="form-label form-label-sm">Shipping Class</label>
                <select class="form-select form-select-sm" id="shippingClass" name="shipping_class">
                    <option value="">Standard Shipping</option>
                    <option value="fragile" {{ old('shipping_class', $product->shipping_class ?? '') == 'fragile' ? 'selected' : '' }}>
                        Fragile
                    </option>
                    <option value="oversized" {{ old('shipping_class', $product->shipping_class ?? '') == 'oversized' ? 'selected' : '' }}>
                        Oversized
                    </option>
                    <option value="hazardous" {{ old('shipping_class', $product->shipping_class ?? '') == 'hazardous' ? 'selected' : '' }}>
                        Hazardous
                    </option>
                    <option value="express" {{ old('shipping_class', $product->shipping_class ?? '') == 'express' ? 'selected' : '' }}>
                        Express
                    </option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Shipping Cost Override --}}
            <div class="col-md-6" id="shippingCostField">
                <label for="shippingCost" class="form-label form-label-sm">Shipping Cost Override</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" class="form-control form-control-sm" id="shippingCost" name="shipping_cost" 
                           step="0.01" min="0" value="{{ old('shipping_cost', $product->shipping_cost ?? '') }}">
                </div>
                <small class="text-muted small">Override default shipping rates for this product</small>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<!-- Shipping Information Modal -->
<div class="modal fade" id="shippingInfoModal" tabindex="-1" aria-labelledby="shippingInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shippingInfoModalLabel">
                    <i class="fas fa-shipping-fast me-2"></i>Shipping Classes Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-truck text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Standard Shipping:</strong> Regular shipping method with no special handling <small class="text-muted">(Best for: Regular products, books, clothing, electronics)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Fragile Shipping:</strong> For items that can easily break or get damaged during transit <small class="text-muted">(Best for: Glass items, ceramics, electronics, artwork)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cube text-info me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Oversized Shipping:</strong> For large or heavy items that don't fit into regular parcel sizes <small class="text-muted">(Best for: Furniture, appliances, large electronics, equipment)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-skull-crossbones text-danger me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Hazardous Shipping:</strong> For products containing dangerous or regulated materials <small class="text-muted">(Best for: Chemicals, batteries, flammable items, medical supplies)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-rocket text-success me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Express Shipping:</strong> Fast shipping method with priority handling <small class="text-muted">(Best for: Urgent deliveries, time-sensitive products, premium service)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Select appropriate shipping class</strong> based on product characteristics <small class="text-muted">(Ensures proper handling and accurate costs)</small></div>
                        <div><strong>Use fragile shipping</strong> for breakable items <small class="text-muted">(Prevents damage during transit)</small></div>
                        <div><strong>Choose oversized shipping</strong> for large items <small class="text-muted">(Avoids shipping complications)</small></div>
                        <div><strong>Consider express shipping</strong> for urgent orders <small class="text-muted">(Improves customer satisfaction)</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requiresShippingCheckbox = document.getElementById('requiresShipping');
    const freeShippingField = document.getElementById('freeShippingField');
    const physicalDimensions = document.getElementById('physicalDimensions');
    const shippingClassField = document.getElementById('shippingClassField');
    const shippingCostField = document.getElementById('shippingCostField');

    function toggleShippingFields() {
        const isRequired = requiresShippingCheckbox.checked;
        
        freeShippingField.style.display = isRequired ? 'block' : 'none';
        physicalDimensions.style.display = isRequired ? 'block' : 'none';
        shippingClassField.style.display = isRequired ? 'block' : 'none';
        shippingCostField.style.display = isRequired ? 'block' : 'none';
    }

    requiresShippingCheckbox.addEventListener('change', toggleShippingFields);
    toggleShippingFields(); // Initial call
});
</script>
