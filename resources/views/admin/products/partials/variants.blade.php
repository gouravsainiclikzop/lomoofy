{{-- RichTextEditor for variant description and additional information --}}
<link rel="stylesheet" href="{{ asset('frontend/js/richtexteditor/rte_theme_default.css') }}" />
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/lang/rte-lang-en.js') }}"></script>

@php
    $measurementAttributes = isset($attributes)
        ? collect($attributes)->filter(function ($attribute) {
            return $attribute->type === 'number';
        })->values()
        : collect();

    $unitsCollection = isset($units)
        ? collect($units)
        : collect();

    $measurementAttributesPayload = $measurementAttributes->map(function ($attribute) {
        return [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'description' => $attribute->description,
        ];
    })->values();

    $unitsPayload = $unitsCollection->map(function ($unit) {
        return [
            'id' => $unit->id,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'type' => $unit->type,
        ];
    })->values();
@endphp

{{-- Variants Section for Configurable Products --}}
<div class="card mb-3">
    <div class="card-header py-2">
        <h6 class="card-title mb-0">
            <i class="fas fa-layer-group me-2"></i>Product Variants
        </h6>
    </div>
    <div class="card-body py-3">
        {{-- Attribute Selection and Add Variant Form Side by Side --}}
        <div class="row g-2 mb-3">
            {{-- Available Attributes Section --}}
            <div class="col-md-6"> 
                <div id="attributeSelection">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label form-label-sm">Select Available Attributes for Variants</label>
                            <div class="attributes-checkbox-list" id="availableAttributesContainer">
                                @if(isset($attributes) && count($attributes) > 0)
                                    @foreach($attributes as $attribute)
                                        <div class="form-check attribute-checkbox-item" data-attribute-id="{{ $attribute->id }}">
                                            <input class="form-check-input attribute-checkbox" 
                                                   type="checkbox" 
                                                   value="{{ $attribute->id }}" 
                                                   id="attr_{{ $attribute->id }}"
                                                   data-attribute-type="{{ $attribute->type }}"
                                                   data-attribute-description="{{ $attribute->description }}">
                                            <label class="form-check-label" for="attr_{{ $attribute->id }}">
                                                <strong>{{ $attribute->name }}</strong>
                                                <span class="text-muted ms-2">({{ ucfirst($attribute->type) }})</span>
                                                @if($attribute->description)
                                                    <small class="text-muted d-block">{{ $attribute->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-muted text-center p-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>No attributes available. Please select a category or create attributes first.</small>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select one or more attributes to create product variants.
                                </small>
                                <button type="button" 
                                        class="btn btn-sm btn-primary" 
                                        id="loadAttributeValuesBtn"
                                        style="display: none;">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Load Values
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Variant Form Section --}}
            <div class="col-md-6">
                <div id="addVariantForm" class="card" style="display: none;">
                    <div class="card-header py-2">
                        <h6 class="mb-0 form-label-sm">
                            <i class="fas fa-plus-circle me-2"></i>Add New Variant
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div id="variantAttributeSelectors" class="col-12">
                                <!-- Dynamic attribute value selectors will be inserted here -->
                                 
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-primary" id="addVariantBtn">
                                    <i class="fas fa-magic me-1"></i> Generate All Variants
                                </button>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select multiple values for each attribute to generate all combinations
                                </small>
                            </div>
                        </div>
                    </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Attribute Value -->
<div class="modal fade" id="addAttributeValueModal" tabindex="-1" aria-labelledby="addAttributeValueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAttributeValueModalLabel">Add New Attribute Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAttributeValueForm">
                    <input type="hidden" id="modalAttributeId" name="attribute_id">
                    <input type="hidden" id="modalAttributeType" name="attribute_type">
                    
                    <div class="mb-3">
                        <label for="modalAttributeName" class="form-label">Attribute</label>
                        <input type="text" class="form-control" id="modalAttributeName" readonly>
                    </div>
                    
                    <div class="mb-3" id="modalValueInputWrapper">
                        <!-- Dynamic input will be inserted here based on attribute type -->
                    </div>
                    
                    <div class="mb-3 d-none" id="modalColorInputWrapper">
                        <label for="modalColorCode" class="form-label">Color Code</label>
                        <input type="hidden" class="form-control" id="modalColorCode" value="#000000">
                        <button type="button" class="btn btn-sm w-100" id="modalColorCodeBtn" style="background-color: #000000; height: 38px; border: 1px solid #ced4da; border-radius: 0.375rem; cursor: pointer;"></button>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modalSortOrder" class="form-label">Sort Order <span class="text-muted">(optional)</span></label>
                        <input type="number" class="form-control" id="modalSortOrder" name="sort_order" min="0" placeholder="Auto">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAttributeValueBtn">
                    <i class="fas fa-save me-1"></i>Save Value
                </button>
            </div>
        </div>
    </div>
</div>

        <style>
            /* Checkbox list styling with scrollbar */
            .attributes-checkbox-list {
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                background-color: #fff;
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 0.3rem 1.6rem;
            }
            
            .attributes-checkbox-list::-webkit-scrollbar {
                width: 8px;
            }
            
            .attributes-checkbox-list::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            
            .attributes-checkbox-list::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            
            .attributes-checkbox-list::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            
            /* Attribute values checkbox container scrollbar styling */
            .attribute-values-checkbox-container {
                max-height: 100px !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                padding: 10px;
                background-color: #fff;
                /* Firefox scrollbar styling */
                scrollbar-width: thin;
                scrollbar-color: #888 #f1f1f1;
            }
            
            .attribute-values-checkbox-container::-webkit-scrollbar {
                width: 10px !important;
                height: 10px;
            }
            
            .attribute-values-checkbox-container::-webkit-scrollbar-track {
                background: #f1f1f1 !important;
                border-radius: 4px;
            }
            
            .attribute-values-checkbox-container::-webkit-scrollbar-thumb {
                background: #888 !important;
                border-radius: 4px;
                border: 1px solid #f1f1f1;
            }
            
            .attribute-values-checkbox-container::-webkit-scrollbar-thumb:hover {
                background: #555 !important;
            }
            
            .attribute-checkbox-item {
                padding: 0.5rem;
                margin-bottom: 0.25rem;
                border-radius: 0.25rem;
                transition: background-color 0.2s;
            }
            
            .attribute-checkbox-item:hover {
                background-color: #f8f9fa;
            }
            
            .attribute-checkbox-item:last-child {
                margin-bottom: 0;
            }
            
            .attribute-checkbox-item .form-check-label {
                cursor: pointer;
                width: 100%;
            }
            
            /* Variant Toast z-index fix */
            #variantToast {
                z-index: 1100 !important;
            }
            
            .attribute-checkbox-item .form-check-input {
                cursor: pointer;
                margin-top: 0.25rem;
            }
        </style>

        {{-- Variants Table -- Always Visible --}}
        <div id="variantsTableContainer" class="variants-table-container">
            <h6 class="form-label-sm mb-2">Product Variants</h6>
            <div class="table-responsive" style=" overflow-x: auto; white-space: nowrap;">
                <table class="table table-bordered" id="variantsTable">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th>Regular Price</th>
                            <th>Sale Price</th>
                            <th>Dimensions</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="variantsTableBody">
                        <!-- Dynamic variant rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bulk Actions --}}
        <div id="bulkActions" class="mt-4" style="display: none;">
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllBtn">
                    <i data-feather="check-square" class="me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                    <i data-feather="square" class="me-1"></i> Deselect All
                </button>
                <div class="vr"></div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="bulkEditBtn">
                    <i data-feather="edit" class="me-1"></i> Bulk Edit
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" id="bulkActivateBtn">
                    <i data-feather="check" class="me-1"></i> Activate Selected
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" id="bulkDeactivateBtn">
                    <i data-feather="slash" class="me-1"></i> Deactivate Selected
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn">
                    <i data-feather="trash-2" class="me-1"></i> Delete Selected
                </button>
            </div>
        </div>
</div>
</div>

{{-- Variant Edit Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="variantEditModal" aria-labelledby="variantEditModalLabel" style="width: 80%;">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title" id="variantEditModalLabel">Edit Variant</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
                <form id="variantEditForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="variantSku" name="variant_sku" required>
                            <small class="text-muted">SKU is required for each variant</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Barcode</label>
                            <input type="text" class="form-control form-control-sm" id="variantBarcode" name="variant_barcode">
                            <small class="text-muted">Optional barcode for this variant</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Variant Name</label>
                            <input type="text" class="form-control form-control-sm" id="variantName" name="variant_name">
                            <small class="text-muted">Editable variant name (auto-generated from attributes)</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">MRP</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="variantPrice" name="variant_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Sell Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="variantSalePrice" name="variant_sale_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-12"> 
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Discount Type</label>
                                    <select class="form-select form-select-sm" id="variantDiscountType" name="variant_discount_type">
                                        <option value="">None</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="amount">Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Discount Value</label>
                                    <div class="input-group input-group-sm" id="variantDiscountValueGroup">
                                        <span class="input-group-text" id="variantDiscountPrefix">₹</span>
                                        <input type="number" class="form-control form-control-sm" id="variantDiscountValue" name="variant_discount_value" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Discount Active</label>
                                    <select class="form-select form-select-sm" id="variantDiscountActive" name="variant_discount_active">
                                        <option value="0">Inactive</option>
                                        <option value="1">Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Inventory Section --}}
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3"><i data-feather="package" class="me-2"></i> Inventory</h6>
                          
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Low Stock Threshold</label>
                            <input type="number" class="form-control form-control-sm" id="variantLowStockThreshold" name="variant_low_stock_threshold" min="0" value="0">
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Alert when stock falls below this</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label form-label-sm d-block">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="variantStatusToggle" name="variant_is_active">
                                <label class="form-check-label" for="variantStatusToggle">Active</label>
                            </div>
                        </div>
                        
                        {{-- Measurements Section --}}
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i data-feather="sliders" class="me-2"></i> Measurements</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addMeasurementRowBtn">
                                    <i data-feather="plus" class="me-1"></i> Add Measurement
                                </button>
                            </div>
                            <div id="variantMeasurementData" class="d-none"
                                 data-measurement-attributes="{{ $measurementAttributesPayload->toJson(JSON_UNESCAPED_UNICODE) }}"
                                 data-measurement-units="{{ $unitsPayload->toJson(JSON_UNESCAPED_UNICODE) }}"
                                 data-attributes-url="{{ route('attributes.index') }}">
                            </div>
                            <div id="variantMeasurementsEmptyState" class="alert alert-light border d-none">
                                <div class="d-flex align-items-center">
                                    <i data-feather="info" class="text-primary me-2"></i>
                                    <span>No measurement attributes configured. <a href="{{ route('attributes.index') }}" target="_blank">Create a numeric attribute</a> to capture variant measurements.</span>
                                </div>
                            </div>
                            <div id="variantMeasurementRows" class="measurement-rows-container"></div>
                            <small id="measurementHelpText" class="text-muted d-block mt-2">Enter measurement attribute names, choose appropriate units, and provide the values for this variant. Units are loaded from the <a href="{{ route('units.index') }}" target="_blank">Units module</a>.</small>
                        </div>
                        
                        <div class="col-12">
                            <hr>
                            <label class="form-label form-label-sm d-block">Variant Images</label>
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="variantManageImagesBtn">
                                    <i data-feather="upload" class="me-1"></i> Select Images
                                </button>
                                <small class="text-muted">Uploads support multiple files. Existing files remain unless replaced.</small>
                            </div>
                            <div id="variantImagesPreview" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        {{-- Variant Highlights & Details Section --}}
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i data-feather="star" class="me-2"></i> Variant Highlights & Details</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addHeadingBtn">
                                    <i data-feather="plus" class="me-1"></i> Add Heading
                                </button>
                            </div>
                            <div id="variantHighlightsDetailsContainer" class="highlights-details-container">
                                <!-- Headings and bullet points will be dynamically added here -->
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Add structured information with headings and bullet points. Previously used headings will appear as suggestions.
                            </small>
                        </div>
                        
                        {{-- Detailed Description Section --}}
                        <div class="col-12">
                            <hr>
                            <label class="form-label form-label-sm d-block mb-2">Detailed Description</label>
                            <textarea class="form-control" id="variantDescription" name="variant_description" placeholder="Detailed description for this variant..."></textarea>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Provide a detailed description specific to this variant.
                            </small>
                        </div>
                        
                        {{-- Additional Information Section --}}
                        <div class="col-12">
                            <hr>
                            <label class="form-label form-label-sm d-block mb-2">Additional Information</label>
                            <textarea class="form-control" id="variantAdditionalInfo" name="variant_additional_information" placeholder="Additional information for this variant..."></textarea>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Add any additional information specific to this variant.
                            </small>
                        </div>
                    </div>
                </form>
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveVariantBtn">Save Changes</button>
                    </div>
                </div>
    </div>
</div>

@php
    $existingVariantsJson = '[]';
    if (isset($product) && $product->exists && $product->variants->count() > 0) {
        $existingVariantsJson = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'sale_price' => $variant->sale_price,
                'attributes' => $variant->attributes,
                'is_active' => $variant->is_active,
                'weight' => $variant->weight,
                'length' => $variant->length,
                'width' => $variant->width,
                'height' => $variant->height,
                'diameter' => $variant->diameter,
                'discount_type' => $variant->discount_type,
                'discount_value' => $variant->discount_value,
                'discount_active' => $variant->discount_active,
                'stock_quantity' => $variant->stock_quantity ?? 0,
                'stock_status' => $variant->stock_status ?? 'in_stock',
                'manage_stock' => $variant->manage_stock ?? true,
                'low_stock_threshold' => $variant->low_stock_threshold ?? 0,
                'measurements' => $variant->measurements ?? [],
                'highlights_details' => $variant->highlights_details ?? [],
                'description' => $variant->description ?? null,
                'additional_information' => $variant->additional_information ?? null,
                'images' => $variant->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path,
                        'url' => asset('storage/' . $image->image_path),
                    ];
                })->values(),
            ];
        })->values()->toJson(JSON_UNESCAPED_UNICODE);
    }
@endphp

<script id="existingVariantsPayload" type="application/json">{!! $existingVariantsJson !!}</script>

<!-- Variant Images View Modal -->
<div class="modal fade" id="variantImagesViewModal" tabindex="-1" aria-labelledby="variantImagesViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variantImagesViewModalLabel">
                    <i class="fas fa-images me-2"></i>Variant Images
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="variantImagesViewContainer" class="row g-3">
                    <!-- Images will be loaded here -->
                </div>
                <div id="variantImagesViewEmpty" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p>No images available for this variant.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Bulk Edit Variants</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkEditForm">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Regular Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="bulkPriceValue" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Sale Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="bulkSalePriceValue" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bulkStatusValue">
                                <label class="form-check-label form-label-sm" for="bulkStatusValue">Set Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Discount Type</label>
                            <select class="form-select form-select-sm" id="bulkDiscountTypeValue">
                                <option value="">None</option>
                                <option value="percentage">Percentage</option>
                                <option value="amount">Amount</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Discount Value</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="bulkDiscountPrefix">₹</span>
                                <input type="number" class="form-control form-control-sm" id="bulkDiscountValue" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Discount Active</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bulkDiscountActiveValue">
                                <label class="form-check-label form-label-sm" for="bulkDiscountActiveValue">Enable Discount</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="bulkEditApplyBtn">Apply Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- jsDelivr -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- CDNJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
 

<script>
function refreshFeatherIcons() {
    if (window.feather && typeof window.feather.replace === 'function') {
        window.feather.replace();
    }
}

// Generate attribute-specific placeholder
function generateAttributePlaceholder(attributeName, attributeType) {
    const placeholders = {
        'Color': 'Enter colors (e.g., Red, Blue, Green, Black)',
        'Size': 'Enter sizes (e.g., S, M, L, XL, XXL)',
        'Storage': 'Enter storage (e.g., 64GB, 128GB, 256GB, 512GB)',
        'Material': 'Enter materials (e.g., Cotton, Polyester, Leather)',
        'Brand': 'Enter brands (e.g., Nike, Adidas, Puma)',
        'Style': 'Enter styles (e.g., Casual, Formal, Sport)',
        'Weight': 'Enter weights (e.g., 100g, 250g, 500g)',
        'Length': 'Enter lengths (e.g., 30cm, 50cm, 100cm)',
        'Width': 'Enter widths (e.g., 10cm, 20cm, 30cm)',
        'Height': 'Enter heights (e.g., 5cm, 10cm, 15cm)',
        'Capacity': 'Enter capacity (e.g., 1L, 2L, 5L)',
        'Power': 'Enter power (e.g., 100W, 200W, 500W)',
        'Voltage': 'Enter voltage (e.g., 110V, 220V, 240V)',
        'Model': 'Enter models (e.g., Pro, Plus, Max, Lite)',
        'Edition': 'Enter editions (e.g., Standard, Premium, Deluxe)'
    };
    
    // Check for exact match first
    if (placeholders[attributeName]) {
        return placeholders[attributeName];
    }
    
    // Check for partial matches
    const lowerName = attributeName.toLowerCase();
    for (const [key, value] of Object.entries(placeholders)) {
        if (lowerName.includes(key.toLowerCase()) || key.toLowerCase().includes(lowerName)) {
            return value;
        }
    }
    
    // Default placeholder based on attribute type
    if (lowerName.includes('color') || lowerName.includes('colour')) {
        return 'Enter colors (e.g., Red, Blue, Green)';
    } else if (lowerName.includes('size')) {
        return 'Enter sizes (e.g., S, M, L, XL)';
    } else if (lowerName.includes('storage') || lowerName.includes('memory')) {
        return 'Enter storage options (e.g., 64GB, 128GB)';
    } else if (lowerName.includes('material') || lowerName.includes('fabric')) {
        return 'Enter materials (e.g., Cotton, Leather)';
    } else if (lowerName.includes('weight')) {
        return 'Enter weights (e.g., 100g, 250g)';
    } else if (lowerName.includes('length') || lowerName.includes('width') || lowerName.includes('height')) {
        return 'Enter dimensions (e.g., 10cm, 20cm)';
    } else if (lowerName.includes('brand') || lowerName.includes('manufacturer')) {
        return 'Enter brands (e.g., Brand A, Brand B)';
    } else if (lowerName.includes('model') || lowerName.includes('version')) {
        return 'Enter models (e.g., Pro, Plus, Max)';
    } else {
        return `Enter ${attributeName.toLowerCase()} values (e.g., Option 1, Option 2)`;
    }
}

// Generate input field based on attribute type
function generateAttributeInput(attributeType, placeholder, attributeId) {
    switch(attributeType) {
        case 'color':
            const colorInputId = `colorInput_${attributeId}_${Date.now()}`;
            return `
                <input type="hidden" class="form-control attribute-value-input color-input-hidden" id="${colorInputId}" data-attribute-type="color" value="#000000">
                <button type="button" class="btn btn-sm w-100 color-picker-btn" data-color-input="${colorInputId}" style="background-color: #000000; height: 38px; border: 1px solid #ced4da; border-radius: 0.375rem; cursor: pointer;"></button>
            `;
        case 'number':
            return `<input type="number" class="form-control attribute-value-input" placeholder="${placeholder}" data-attribute-type="number">`;
        case 'date':
            return `<input type="date" class="form-control attribute-value-input" placeholder="${placeholder}" data-attribute-type="date">`;
        case 'boolean':
            return `<select class="form-select attribute-value-input" data-attribute-type="boolean">
                        <option value="">Select option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>`;
        case 'image':
            return `<input type="file" class="form-control attribute-value-input" accept="image/*" data-attribute-type="image">
                    <small class="text-muted">Upload image for this attribute value</small>`;
        case 'select':
            return `<input type="text" class="form-control attribute-value-input" placeholder="${placeholder}" data-attribute-type="select" list="options-${attributeId}">
                    <datalist id="options-${attributeId}">
                        <!-- Existing values will be loaded here -->
                    </datalist>`;
        case 'text':
        default:
            return `<input type="text" class="form-control attribute-value-input" placeholder="${placeholder}" data-attribute-type="text">`;
    }
}

// Cache for attribute values to prevent duplicate API calls
const attributeValuesCache = new Map();
const attributeValuesPromises = new Map(); // Track ongoing requests

// Function disabled - value loading functionality removed
function loadExistingAttributeValues(attributeId, attributeType, attributeDiv, selectedAttributeValuesMap = {}) {
    return; // Disabled
}

// Helper function to populate attribute values from cached or fetched data
function populateAttributeValuesFromData(attributeId, attributeType, attributeDiv, data, selectedAttributeValuesMap = {}) {
    const attributeKey = String(attributeId);
    const $valuesContainer = $(attributeDiv).find('.attribute-values');
    
    if (data && data.length > 0) {
        // Populate datalist for select type attributes
        if (attributeType === 'select' || attributeType === 'text') {
            const $datalist = $(attributeDiv).find('datalist');
            if ($datalist.length) {
                $datalist.empty();
                data.forEach(value => {
                    $datalist.append($('<option>').attr('value', value.value));
                });
            }
        }
        
        // Display existing values as badges with checkboxes
        data.forEach(value => {
            const $valueTag = $('<div>')
                .addClass('form-check form-check-inline mb-2 me-2')
                .css('display', 'inline-block');
            
            let displayValue = value.value;
            if (attributeType === 'color') {
                displayValue = `<span style="background-color: ${value.value}; color: white; padding: 2px 6px; border-radius: 3px;">${value.value}</span>`;
            } else if (attributeType === 'image') {
                displayValue = `<span data-feather="image" class="me-1"></span>${value.value}`;
            }
            
            const checkboxId = `attr_${attributeId}_value_${value.value.replace(/[^a-zA-Z0-9]/g, '_')}`;
           
            $valueTag.html(`
                <input class="form-check-input value-checkbox" type="checkbox" 
                       id="${checkboxId}" 
                       value="${value.value}" 
                       data-attribute-id="${attributeId}"
                       data-value="${value.value}">
                <label class="form-check-label badge bg-secondary" for="${checkboxId}">
                    ${displayValue} 
                </label>
            `);
            
            $valuesContainer.append($valueTag);

            const $checkbox = $valueTag.find('.value-checkbox');
            // Disabled: Don't auto-check value checkboxes during draft restoration - let user manually select
            // Only auto-check if NOT restoring from draft
            if (!isRestoringVariantDraft && selectedAttributeValuesMap[attributeKey] && selectedAttributeValuesMap[attributeKey].includes(value.value)) {
                $checkbox.prop('checked', true);
                $checkbox.trigger('change');
            }
        });
        
        // Add click handler for removing master data values (if needed)
        $valuesContainer.on('click', function(e) {
            if ($(e.target).hasClass('btn-close') && $(e.target).data('fromMaster') === true) {
                $(e.target).closest('.form-check').remove();
            }
        });
    } else {
        // No existing values found
        const $noValuesDiv = $('<div>')
            .addClass('text-muted small mb-2')
            .html('<span data-feather="info" class="me-1"></span>No existing values found. Add new values below.');
        $valuesContainer.append($noValuesDiv);
        refreshFeatherIcons();
    }
}

// Helper functions for checkbox-based attribute selection
function getSelectedAttributeIds() {
    const $container = $('#availableAttributesContainer');
    if (!$container.length) return [];
    return $container.find('.attribute-checkbox:checked').map(function() {
        return String($(this).val());
    }).get();
}

function setSelectedAttributeIds(attributeIds) {
    const $container = $('#availableAttributesContainer');
    if (!$container.length) return;
    
    // Uncheck all first
    $container.find('.attribute-checkbox').prop('checked', false);
    
    // Check selected ones
    if (Array.isArray(attributeIds) && attributeIds.length > 0) {
        attributeIds.forEach(id => {
            $container.find(`.attribute-checkbox[value="${id}"]`).prop('checked', true);
        });
    }
}

function getAttributeInfo(attributeId) {
    const $container = $('#availableAttributesContainer');
    if (!$container.length) return null;
    
    const $checkbox = $container.find(`.attribute-checkbox[value="${attributeId}"]`);
    if (!$checkbox.length) return null;
    
    const $item = $checkbox.closest('.attribute-checkbox-item');
    const $label = $item.find('label');
    const labelText = $label.length ? $label.text().trim() : '';
    
    return {
        id: attributeId,
        type: $checkbox.attr('data-attribute-type') || '',
        description: $checkbox.attr('data-attribute-description') || '',
        name: labelText.replace(/\s*\([^)]*\)$/, '').trim()
    };
}

// Global variables for attribute config updates (declared before DOMContentLoaded)
let isUpdatingAttributeConfig = false;
let updateAttributeConfigTimeout = null;
let isInitialLoad = true;
let hasUserInteracted = false; // Track if user has interacted with attribute checkboxes

$(document).ready(function() {
    const $availableAttributesContainer = $('#availableAttributesContainer');
    
    // Handle checkbox changes for attribute selection
    if ($availableAttributesContainer.length) {
        // Function to handle checkbox change
        function handleAttributeCheckboxChange($checkbox) { 
            // Mark that user has interacted
            hasUserInteracted = true;
            
            const attributeId = String($checkbox.val());
            const isChecked = $checkbox.is(':checked');
            
            // Get all selected attribute IDs
            const newSelectedIds = $availableAttributesContainer.find('.attribute-checkbox:checked').map(function() {
                return String($(this).val());
            }).get();
            
            // Allow users to uncheck attributes freely - no restrictions
            // Removed: Check if any deselected attribute is used in variants
            // Users can now uncheck any attribute regardless of variant usage
            
            selectedAttributeIds = newSelectedIds;
            
            // Show/hide the "Load Values" button based on selected attributes
            const $loadValuesBtn = $('#loadAttributeValuesBtn');
            if (newSelectedIds.length > 0) {
                $loadValuesBtn.css('display', 'inline-block');
            } else {
                $loadValuesBtn.css('display', 'none');
            }
            
            // Clear cache for selected attributes to force fresh load from database
            // This ensures we always get the latest values from the server
            newSelectedIds.forEach(attrId => {
                const attrKey = String(attrId);
                // Clear cache for this attribute to get latest values
                if (attributeValuesCache.has(attrKey)) {
                    attributeValuesCache.delete(attrKey);
                }
                // Also clear any pending promises
                if (attributeValuesPromises.has(attrKey)) {
                    attributeValuesPromises.delete(attrKey);
                }
            });
            
            // Call updateAttributeValuesConfig which will show form (but not load values)
            // Debounce the call to prevent rapid successive calls
            if (updateAttributeConfigTimeout) {
                clearTimeout(updateAttributeConfigTimeout);
            }
            updateAttributeConfigTimeout = setTimeout(function() {
                updateAttributeValuesConfig();
            }, 200);
            
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
        }
        
        // Attach event handlers to all checkboxes using event delegation
        $availableAttributesContainer.on('change', '.attribute-checkbox', function(e) {
            handleAttributeCheckboxChange($(this));
        });
        
        // Handle "Load Values" button click
        $('#loadAttributeValuesBtn').on('click', function() {
            const $btn = $(this);
            const originalText = $btn.html();
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
            
            // Get all selected attribute IDs
            const selectedIds = getSelectedAttributeIds();
            if (selectedIds.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Please select at least one attribute first.');
                } else {
                    alert('Please select at least one attribute first.');
                }
                $btn.prop('disabled', false);
                $btn.html(originalText);
                return;
            }
            
            // Load values for all selected attributes
            let loadedCount = 0;
            
            selectedIds.forEach(attributeId => {
                const attrIdStr = String(attributeId);
                const $container = $('#variantAttributeSelectors').find(`[data-attribute-id="${attrIdStr}"] .attribute-values-checkbox-container`);
                
                if ($container.length && $container[0]) {
                    const attrInfo = getAttributeInfo(attributeId);
                    const attributeType = attrInfo ? attrInfo.type : 'text';
                    
                    loadAttributeValuesForMultiSelect(attributeId, attributeType, $container[0]);
                    loadedCount++;
                }
            });
            
            // Reset button after a short delay
            setTimeout(function() {
                $btn.prop('disabled', false);
                $btn.html(originalText);
                if (loadedCount > 0 && typeof showToast === 'function') {
                    showToast('success', `Loading values for ${loadedCount} attribute(s)...`);
                }
            }, 500);
        });
    }
    
    // Initialize variants table visibility
    const $variantsTableContainer = $('#variantsTableContainer');
    const $variantsTableBody = $('#variantsTableBody');
    const $bulkActions = $('#bulkActions');
    
    // Ensure variants table is visible by default (it was hidden before)
    if ($variantsTableContainer.length) {
        $variantsTableContainer.css('display', 'block');
    }
    
    // Trigger initial check for variants after a short delay
    // Also call updateAttributeValuesConfig to initialize variants
    setTimeout(function() {
        // Ensure table is visible
        if ($variantsTableContainer.length) {
            $variantsTableContainer.css('display', 'block');
        }
        
        // Don't call updateAttributeValuesConfig on initialization - let user manually select attributes
        // This prevents automatic preservation of attribute values
        // if (typeof updateAttributeValuesConfig === 'function') {
        //     updateAttributeValuesConfig();
        // }
        
        // Clear all attribute data at initialization to ensure clean state
        selectedAttributeIds = [];
        attributeValues = {};
        selectedAttributeValues = {};
        
        // Uncheck all attribute checkboxes
        const $availableAttributesContainer = $('#availableAttributesContainer');
        if ($availableAttributesContainer.length) {
            $availableAttributesContainer.find('.attribute-checkbox').prop('checked', false);
        }
        
        // Uncheck all attribute value checkboxes
        $('#variantAttributeSelectors').find('.value-checkbox, .variant-attribute-value-checkbox').prop('checked', false);
        
        // Default variant creation removed per user request
    }, 300);
    const $variantEditModalElement = $('#variantEditModal');
    const variantEditModal = $variantEditModalElement.length ? new bootstrap.Offcanvas($variantEditModalElement[0]) : null;
    const $measurementRowsContainer = $('#variantMeasurementRows');
    const $measurementEmptyState = $('#variantMeasurementsEmptyState');
    const $addMeasurementRowBtn = $('#addMeasurementRowBtn');
    const $measurementDataElement = $('#variantMeasurementData');
    let measurementAttributes = [];
    if ($measurementDataElement.length && $measurementDataElement.data('measurementAttributes')) {
        try {
            const parsed = JSON.parse($measurementDataElement.data('measurementAttributes'));
            measurementAttributes = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            measurementAttributes = [];
        }
    }
    // Units will be loaded dynamically from /units module
    let unitsData = [];
    let unitsLoaded = false;
    let unitsLoading = false;
    let measurementAttributeMap = {};
    let unitsMap = {};
    let defaultUnitByType = {};
    measurementAttributes.forEach(attribute => {
        measurementAttributeMap[String(attribute.id)] = attribute;
    });
    
    // Try to load units from static data first (fallback), then load from API
    let staticUnitsData = [];
    if ($measurementDataElement.length) {
        try {
            // Try jQuery's data method first (it auto-parses JSON)
            let dataValue = $measurementDataElement.data('measurementUnits');
            
            // If data() returns an array, use it directly
            if (Array.isArray(dataValue)) {
                staticUnitsData = dataValue;
            } else if (dataValue) {
                // If it's a string, try to parse it
                if (typeof dataValue === 'string') {
                    // Decode HTML entities first if needed
                    const decoded = $('<div>').html(dataValue).text();
                    staticUnitsData = JSON.parse(decoded);
                } else {
                    // Try to convert to array
                    staticUnitsData = Array.isArray(dataValue) ? dataValue : [];
                }
            } else {
                // Fallback: get raw attribute and parse manually
                const rawData = $measurementDataElement.attr('data-measurement-units');
                if (rawData && rawData.trim()) {
                    // Decode HTML entities
                    const decoded = $('<div>').html(rawData).text();
                    staticUnitsData = JSON.parse(decoded);
                }
            }
            
            // Ensure it's an array
            if (!Array.isArray(staticUnitsData)) {
                staticUnitsData = [];
            }
        } catch (e) {
            console.warn('Error parsing measurement units data:', e);
            staticUnitsData = [];
        }
    }
    if (staticUnitsData.length > 0) {
        unitsData = staticUnitsData.map(unit => ({
            id: unit.id,
            name: unit.name,
            symbol: unit.symbol,
            type: unit.type
        }));
        unitsData.forEach(unit => {
            unitsMap[String(unit.id)] = unit;
            const typeKey = unit.type || 'other';
            if (!defaultUnitByType[typeKey]) {
                defaultUnitByType[typeKey] = unit;
            }
        });
        unitsLoaded = true;
    }
    const $discountTypeSelect = $('#variantDiscountType');
    const $discountValueInput = $('#variantDiscountValue');
    const $discountActiveSelect = $('#variantDiscountActive'); // Changed from toggle to select
    const $discountPrefixSpan = $('#variantDiscountPrefix');
    const $mrpInput = $('#variantPrice');
    const $variantSellPriceInput = $('#variantSalePrice');
    const $variantStatusToggle = $('#variantStatusToggle');
    const $variantManageImagesBtn = $('#variantManageImagesBtn');
    const $variantImagesPreview = $('#variantImagesPreview');
    const $bulkEditModalElement = $('#bulkEditModal');
    const bulkEditModalElement = $bulkEditModalElement.length ? $bulkEditModalElement[0] : null;
    const bulkEditModal = bulkEditModalElement ? new bootstrap.Modal(bulkEditModalElement) : null;
    const $bulkEditForm = $bulkEditModalElement.find('form');
    const bulkEditForm = $bulkEditForm.length ? $bulkEditForm[0] : null;
    const $bulkPriceValue = $('#bulkPriceValue');
    const $bulkSalePriceValue = $('#bulkSalePriceValue');
    const $bulkStatusValue = $('#bulkStatusValue');
    const $bulkDiscountTypeValue = $('#bulkDiscountTypeValue');
    const $bulkDiscountValue = $('#bulkDiscountValue');
    const $bulkDiscountActiveValue = $('#bulkDiscountActiveValue');
    const $bulkDiscountPrefix = $('#bulkDiscountPrefix');
    const $existingVariantsElement = $('#existingVariantsPayload');
    const existingVariantsPayload = $existingVariantsElement.length
        ? JSON.parse($existingVariantsElement.text() || '[]')
        : [];
    const $productFormElement = $('#productForm');
    let isEditMode = false;
    if ($productFormElement.length) {
        isEditMode = $productFormElement.data('isEdit') === true;
    }
    const VARIANT_STORAGE_KEY = 'productVariantDraft';
    let isRestoringVariantDraft = false;

    const DEFAULT_WEIGHT_UNIT = 'kg';
    const DEFAULT_DIMENSION_UNIT = 'cm';
    let measurementRowCounter = 0;

    // Helper function to normalize highlights_details value
    function getHighlightsDetailsValue(highlightsDetails) {
        if (!highlightsDetails) {
            return '[]';
        }
        
        // If it's already a JSON string, return it
        if (typeof highlightsDetails === 'string') {
            try {
                // Validate it's valid JSON
                JSON.parse(highlightsDetails);
                return highlightsDetails;
            } catch (e) {
                return '[]';
            }
        }
        
        // If it's an array, stringify it
        if (Array.isArray(highlightsDetails)) {
            return JSON.stringify(highlightsDetails);
        }
        
        return '[]';
    }

    function escapeHtml(value) {
        if (value === undefined || value === null) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Escape for HTML attribute values (specifically for JSON in value attributes)
    function escapeHtmlAttribute(value) {
        if (value === undefined || value === null) {
            return '';
        }
        // For JSON strings in HTML attributes, we need to escape quotes properly
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Removed filterAvailableAttributes - Select2 handles search/filtering automatically

    function formatMeasurementValue(value) {
        if (value === undefined || value === null || value === '') {
            return '-';
        }
        const numeric = Number(value);
        if (!Number.isFinite(numeric)) {
            return escapeHtml(value);
        }
        if (Number.isInteger(numeric)) {
            return numeric.toString();
        }
        return numeric.toFixed(4).replace(/\.0+$/, '').replace(/0+$/, '').replace(/\.$/, '');
    }

    function getDefaultUnitTypeForAttribute(attributeId) {
        const attribute = measurementAttributeMap[String(attributeId)];
        if (!attribute || !attribute.slug) {
            return 'length';
        }
        const slug = attribute.slug.toLowerCase();
        if (slug.includes('weight') || slug.includes('mass')) {
            return 'weight';
        }
        if (slug.includes('volume')) {
            return 'volume';
        }
        if (slug.includes('diameter') || slug.includes('radius')) {
            return 'length';
        }
        return 'length';
    }

    // Load units from /units module via AJAX
    function loadUnitsFromModule(callback, forceRefresh = false) {
        if (unitsLoaded && !forceRefresh) {
            if (callback) callback();
            return;
        }
        
        if (unitsLoading) {
            // Wait for current load to complete
            const checkInterval = setInterval(() => {
                if (!unitsLoading && unitsLoaded) {
                    clearInterval(checkInterval);
                    if (callback) callback();
                }
            }, 100);
            return;
        }
        
        unitsLoading = true;
        
        $.ajax({
            url: '{{ route("units.index") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    unitsData = response.data.map(unit => ({
                        id: unit.id,
                        name: unit.name,
                        symbol: unit.symbol,
                        type: unit.type
                    }));
                    
                    // Rebuild maps
                    unitsMap = {};
                    defaultUnitByType = {};
                    unitsData.forEach(unit => {
                        unitsMap[String(unit.id)] = unit;
                        const typeKey = unit.type || 'other';
                        if (!defaultUnitByType[typeKey]) {
                            defaultUnitByType[typeKey] = unit;
                        }
                    });
                    
                    unitsLoaded = true;
                    unitsLoading = false;
                    
                    // Update empty state after units are loaded
                    updateMeasurementEmptyState();
                    
                    // Refresh unit dropdowns in existing measurement rows
                    if ($measurementRowsContainer.length) {
                        $measurementRowsContainer.find('.measurement-unit').each(function() {
                            const $select = $(this);
                            const currentValue = $select.val();
                            $select.html(buildUnitOptionsHtml(currentValue));
                            $select.val(currentValue);
                        });
                    }
                    
                    if (callback) callback();
                } else {
                    unitsLoading = false;
                    if (callback) callback();
                }
            },
            error: function(xhr, status, error) {
                unitsLoading = false;
                if (callback) callback();
            }
        });
    }
    
    // Refresh units from API (force reload)
    function refreshUnits(callback) {
        unitsLoaded = false;
        loadUnitsFromModule(callback, true);
    }

    function findDefaultUnitForType(type) {
        if (!type) {
            return defaultUnitByType['length'] || null;
        }
        if (defaultUnitByType[type]) {
            return defaultUnitByType[type];
        }
        const match = unitsData.find(unit => (unit.type || '') === type);
        if (match) {
            defaultUnitByType[type] = match;
            return match;
        }
        return unitsData.length ? unitsData[0] : null;
    }

    function buildUnitOptionsHtml(selectedUnitId) {
        if (!unitsData.length) {
            return '<option value="">No units available</option>';
        }

        const grouped = unitsData.reduce((acc, unit) => {
            const typeKey = unit.type || 'other';
            if (!acc[typeKey]) {
                acc[typeKey] = [];
            }
            acc[typeKey].push(unit);
            return acc;
        }, {});

        return Object.keys(grouped)
            .sort()
            .map(typeKey => {
                const options = grouped[typeKey].map(unit => {
                    const selected = selectedUnitId && String(unit.id) === String(selectedUnitId) ? 'selected' : '';
                    const symbol = unit.symbol ? ` (${escapeHtml(unit.symbol)})` : '';
                    return `<option value="${unit.id}" data-unit-symbol="${escapeHtml(unit.symbol || '')}" data-unit-type="${escapeHtml(unit.type || '')}" ${selected}>${escapeHtml(unit.name)}${symbol}</option>`;
                }).join('');
                const label = typeKey ? typeKey.charAt(0).toUpperCase() + typeKey.slice(1) : 'Other';
                return `<optgroup label="${escapeHtml(label)}">${options}</optgroup>`;
            })
            .join('');
    }

    function updateMeasurementEmptyState() {
        if (!$measurementRowsContainer.length || !$measurementEmptyState.length) {
            return;
        }

        const hasUnits = unitsData.length > 0;
        const rowCount = $measurementRowsContainer.find('.measurement-row').length;
        const $measurementHelpText = $('#measurementHelpText');

        // If no units, show message but enable button (units will load on click)
        if (!hasUnits) {
            $measurementEmptyState.removeClass('d-none');
            const unitsLinkHtml = "{{ route('units.index') }}" && "{{ route('units.index') }}" !== '#'
                ? `<a href="{{ route('units.index') }}" target="_blank">Add units</a>`
                : 'Add units';
            $measurementEmptyState.html(`<div class="d-flex align-items-center"><span data-feather="info" class="text-primary me-2"></span><span>No measurement units available. ${unitsLinkHtml} to continue.</span></div>`);
            refreshFeatherIcons();
            // Enable button even if units aren't loaded - they'll load when clicked
            if ($addMeasurementRowBtn.length) {
                $addMeasurementRowBtn.prop('disabled', false);
            }
            // Show help text
            if ($measurementHelpText.length) {
                $measurementHelpText.removeClass('d-none');
            }
            return;
        }

        // Units exist, enable button
        if ($addMeasurementRowBtn.length) {
            $addMeasurementRowBtn.prop('disabled', false);
        }

        if (rowCount === 0) {
            $measurementEmptyState.removeClass('d-none');
            $measurementEmptyState.html('<div class="d-flex align-items-center"><span data-feather="info" class="text-primary me-2"></span><span>No measurements added yet. Use the "Add Measurement" button to define variant-specific measurements.</span></div>');
            refreshFeatherIcons();
            // Show help text when ready to add measurements
            if ($measurementHelpText.length) {
                $measurementHelpText.removeClass('d-none');
            }
        } else {
            $measurementEmptyState.addClass('d-none');
            // Hide help text when measurements exist
            if ($measurementHelpText.length) {
                $measurementHelpText.addClass('d-none');
            }
        }
    }

    function createMeasurementRow(measurement = {}) {
        if (!$measurementRowsContainer.length) {
            return;
        }

        measurementRowCounter += 1;
        const attributeValue = measurement.attribute_name || '';

        const $row = $('<div>')
            .addClass('measurement-row border rounded p-3 mb-2 bg-body-tertiary')
            .attr('data-index', measurementRowCounter);
        $row.html(`
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Attribute <span class="text-danger">*</span></label>
                    <input type="text" class="form-control measurement-attribute" placeholder="Enter attribute name" value="${escapeHtml(attributeValue)}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <select class="form-select measurement-unit">
                        ${buildUnitOptionsHtml(measurement.unit_id)}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Value <span class="text-danger">*</span></label>
                    <input type="number" class="form-control measurement-value" step="0.0001" min="0" placeholder="0.0000" value="${measurement.value !== undefined && measurement.value !== null ? measurement.value : ''}">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-measurement-row" aria-label="Remove measurement">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </div>
        `);

        $row.find('.remove-measurement-row').on('click', function() {
            $row.remove();
            updateMeasurementEmptyState();
        });

        const $unitSelect = $row.find('.measurement-unit');
        if ($unitSelect.length && measurement.unit_id && unitsMap[String(measurement.unit_id)]) {
            $unitSelect.val(String(measurement.unit_id));
        }

        $measurementRowsContainer.append($row);
        updateMeasurementEmptyState();
        refreshFeatherIcons();
    }

    function renderMeasurementsInModal(measurements = []) {
        if (!$measurementRowsContainer || !$measurementRowsContainer.length) {
            return;
        }

        $measurementRowsContainer.empty();

        // Load units if not already loaded
        if (!unitsLoaded && !unitsLoading) {
            loadUnitsFromModule(function() {
                if (unitsData.length > 0) {
                    if (!measurements.length) {
                        createMeasurementRow();
                    } else {
                        measurements.forEach(measurement => createMeasurementRow(measurement));
                    }
                    updateMeasurementEmptyState();
                } else {
                    updateMeasurementEmptyState();
                }
            });
        } else if (unitsLoaded) {
            if (unitsData.length > 0) {
                if (!measurements.length) {
                    createMeasurementRow();
                } else {
                    measurements.forEach(measurement => createMeasurementRow(measurement));
                }
                updateMeasurementEmptyState();
            } else {
                updateMeasurementEmptyState();
            }
        } else {
            // Units are loading, wait for them
            const checkInterval = setInterval(() => {
                if (!unitsLoading && unitsLoaded) {
                    clearInterval(checkInterval);
                    if (unitsData.length > 0) {
                        if (!measurements.length) {
                            createMeasurementRow();
                        } else {
                            measurements.forEach(measurement => createMeasurementRow(measurement));
                        }
                        updateMeasurementEmptyState();
                    } else {
                        updateMeasurementEmptyState();
                    }
                }
            }, 100);
        }
    }

    function collectMeasurementsFromModal() {
        if (!$measurementRowsContainer || !$measurementRowsContainer.length) {
            return [];
        }

        const $rows = $measurementRowsContainer.find('.measurement-row');
        const measurements = [];

        $rows.each(function() {
            const $row = $(this);
            const $attributeInput = $row.find('.measurement-attribute');
            const $unitSelect = $row.find('.measurement-unit');
            const $valueInput = $row.find('.measurement-value');

            if (!$attributeInput.length || !$valueInput.length) {
                return;
            }

            const attributeName = ($attributeInput.val() || '').trim();
            if (!attributeName) {
                return;
            }

            const rawValue = $valueInput.val();
            if (rawValue === '' || rawValue === null) {
                return;
            }

            const numericValue = parseFloat(rawValue);
            if (!Number.isFinite(numericValue)) {
                return;
            }

            const unitId = $unitSelect.length && $unitSelect.val() ? parseInt($unitSelect.val(), 10) : null;
            const unit = unitId ? unitsMap[String(unitId)] : null;

            measurements.push({
                attribute_id: null,
                attribute_name: attributeName,
                attribute_slug: null,
                value: numericValue,
                unit_id: unitId,
                unit_name: unit ? unit.name : null,
                unit_symbol: unit ? unit.symbol : null,
                unit_type: unit ? unit.type : null,
            });
        });

        return measurements;
    }

    function buildMeasurementDisplayHtml(measurements) {
        if (!measurements || !measurements.length) {
            return '<small class="text-muted">No measurements</small>';
        }

        return measurements.map(measurement => {
            const label = measurement.attribute_name || 'Measurement';
            const unit = measurement.unit_symbol || measurement.unit_name || '';
            const unitSuffix = unit ? ` ${escapeHtml(unit)}` : '';
            return `<small class="text-muted d-block">${escapeHtml(label)}: ${formatMeasurementValue(measurement.value)}${unitSuffix}</small>`;
        }).join('');
    }

    function mapMeasurementsToLegacyValues(measurements) {
        const legacy = { weight: null, length: null, width: null, height: null, diameter: null };
        if (!Array.isArray(measurements)) {
            return legacy;
        }

        measurements.forEach(measurement => {
            const slug = measurement.attribute_slug ? measurement.attribute_slug.toLowerCase() : null;
            if (!slug) {
                return;
            }

            if (Object.prototype.hasOwnProperty.call(legacy, slug) && legacy[slug] === null) {
                legacy[slug] = measurement.value;
            }
        });

        return legacy;
    }

    function syncLegacyHiddenInputs(row, measurements) {
        if (!row) {
            return;
        }

        const legacy = mapMeasurementsToLegacyValues(measurements);

        const $weightInput = $(row).find('[data-variant-weight-input]');
        if ($weightInput.length) {
            $weightInput.val(legacy.weight !== null ? legacy.weight : '');
        }

        const $lengthInput = $(row).find('[data-variant-length-input]');
        if ($lengthInput.length) {
            $lengthInput.val(legacy.length !== null ? legacy.length : '');
        }

        const $widthInput = $(row).find('[data-variant-width-input]');
        if ($widthInput.length) {
            $widthInput.val(legacy.width !== null ? legacy.width : '');
        }

        const $heightInput = $(row).find('[data-variant-height-input]');
        if ($heightInput.length) {
            $heightInput.val(legacy.height !== null ? legacy.height : '');
        }

        const $diameterInput = $(row).find('[data-variant-diameter-input]');
        if ($diameterInput.length) {
            $diameterInput.val(legacy.diameter !== null ? legacy.diameter : '');
        }
    }

    function updateRowMeasurements(row, measurements) {
        if (!row) {
            return;
        }

        const $displayContainer = $(row).find('[data-variant-measurements-display]');
        const $hiddenInput = $(row).find('[data-variant-measurements-input]');

        if ($displayContainer.length) {
            $displayContainer.html(buildMeasurementDisplayHtml(measurements));
        }

        if ($hiddenInput.length) {
            $hiddenInput.val(measurements && measurements.length ? JSON.stringify(measurements) : '[]');
        }

        syncLegacyHiddenInputs(row, measurements);
    }

    function findMeasurementAttributeBySlug(slug) {
        if (!slug) {
            return null;
        }
        const lower = slug.toLowerCase();
        return measurementAttributes.find(attribute => attribute.slug && attribute.slug.toLowerCase() === lower) || null;
    }

    function deriveMeasurementsFromLegacy(row) {
        if (!row) {
            return [];
        }

        const mapping = [
            { slug: 'weight', selector: '[data-variant-weight-input]', defaultType: 'weight', defaultSymbol: DEFAULT_WEIGHT_UNIT },
            { slug: 'length', selector: '[data-variant-length-input]', defaultType: 'length', defaultSymbol: DEFAULT_DIMENSION_UNIT },
            { slug: 'width', selector: '[data-variant-width-input]', defaultType: 'length', defaultSymbol: DEFAULT_DIMENSION_UNIT },
            { slug: 'height', selector: '[data-variant-height-input]', defaultType: 'length', defaultSymbol: DEFAULT_DIMENSION_UNIT },
            { slug: 'diameter', selector: '[data-variant-diameter-input]', defaultType: 'length', defaultSymbol: DEFAULT_DIMENSION_UNIT },
        ];

        const measurements = [];

        mapping.forEach(item => {
            const $input = $(row).find(item.selector);
            if (!$input.length || $input.val() === '' || $input.val() === null) {
                return;
            }
            const numericValue = Number($input.val());
            if (!Number.isFinite(numericValue)) {
                return;
            }
            const attribute = findMeasurementAttributeBySlug(item.slug);
            const defaultUnit = findDefaultUnitForType(item.defaultType);

            measurements.push({
                attribute_id: attribute ? attribute.id : null,
                attribute_name: attribute ? attribute.name : item.slug.charAt(0).toUpperCase() + item.slug.slice(1),
                attribute_slug: attribute ? attribute.slug : item.slug,
                value: numericValue,
                unit_id: defaultUnit ? defaultUnit.id : null,
                unit_name: defaultUnit ? defaultUnit.name : null,
                unit_symbol: defaultUnit ? defaultUnit.symbol : item.defaultSymbol,
                unit_type: defaultUnit ? defaultUnit.type : item.defaultType,
            });
        });

        return measurements;
    }

    function parseMeasurementsFromRow(row) {
        if (!row) {
            return [];
        }

        const $hiddenInput = $(row).find('[data-variant-measurements-input]');
        if ($hiddenInput.length && $hiddenInput[0].value) {
            try {
                const parsed = JSON.parse($hiddenInput[0].value);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            } catch (error) {
            }
        }

        return deriveMeasurementsFromLegacy(row);
    }

    function extractMeasurementsForVariant(variant) {
        if (!variant) {
            return [];
        }

        if (Array.isArray(variant.measurements) && variant.measurements.length) {
            return variant.measurements;
        }

        const fallback = [];
        ['weight', 'length', 'width', 'height', 'diameter'].forEach(slug => {
            if (variant[slug] === undefined || variant[slug] === null || variant[slug] === '') {
                return;
            }

            const attribute = findMeasurementAttributeBySlug(slug);
            if (!attribute) {
                return;
            }

            const numericValue = Number(variant[slug]);
            if (!Number.isFinite(numericValue)) {
                return;
            }

            const unitType = slug === 'weight' ? 'weight' : 'length';
            const defaultUnit = findDefaultUnitForType(unitType);

            fallback.push({
                attribute_id: attribute.id,
                attribute_name: attribute.name,
                attribute_slug: attribute.slug,
                value: numericValue,
                unit_id: defaultUnit ? defaultUnit.id : null,
                unit_name: defaultUnit ? defaultUnit.name : null,
                unit_symbol: defaultUnit ? defaultUnit.symbol : (unitType === 'weight' ? DEFAULT_WEIGHT_UNIT : DEFAULT_DIMENSION_UNIT),
                unit_type: defaultUnit ? defaultUnit.type : unitType,
            });
        });

        return fallback;
    }

    // Function to load numeric attributes dynamically
    function loadNumericAttributes(callback) {
        if (measurementAttributes.length > 0) {
            if (callback) callback();
            return;
        }
        
        $.ajax({
            url: '{{ route("attributes.numeric") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    measurementAttributes = response.data.map(attr => ({
                        id: attr.id,
                        name: attr.name,
                        slug: attr.slug,
                        description: attr.description
                    }));
                    
                    // Rebuild attribute map
                    measurementAttributeMap = {};
                    measurementAttributes.forEach(attribute => {
                        measurementAttributeMap[String(attribute.id)] = attribute;
                    });
                    
                    // Update empty state after attributes are loaded
                    updateMeasurementEmptyState();
                    
                    if (callback) callback();
                } else {
                    showToast('error', 'Failed to load measurement attributes. Please create numeric attributes first.');
                    if (callback) callback();
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Error loading measurement attributes. Please check your connection and try again.');
                if (callback) callback();
            }
        });
    }

    if ($addMeasurementRowBtn.length) {
        $addMeasurementRowBtn.on('click', function() {
            // Load units if not already loaded, then create row
            if (!unitsLoaded && !unitsLoading) {
                loadUnitsFromModule(function() {
                    if (unitsData.length > 0) {
                        createMeasurementRow();
                        updateMeasurementEmptyState();
                    } else {
                        showToast('info', 'No units found. Please add units first.');
                        updateMeasurementEmptyState();
                    }
                });
            } else if (unitsLoaded && unitsData.length > 0) {
                // Units already loaded, create row directly
                createMeasurementRow();
                updateMeasurementEmptyState();
            } else {
                // Units not loaded or empty, try to load them
                loadUnitsFromModule(function() {
                    if (unitsData.length > 0) {
                        createMeasurementRow();
                        updateMeasurementEmptyState();
                    } else {
                        showToast('info', 'No units found. Please add units first.');
                        updateMeasurementEmptyState();
                    }
                });
            }
        });
    }

    // Initialize RichTextEditor for variant description and additional information
    let variantDescriptionEditor = null;
    let variantAdditionalInfoEditor = null;
    
    // Function to initialize richtexteditors
    function initializeVariantRichTextEditors() {
        // Configure RichTextEditor
        if (typeof RTE_DefaultConfig !== 'undefined') {
            RTE_DefaultConfig.url_base = "{{ asset('frontend/js/richtexteditor') }}";
            RTE_DefaultConfig.toolbar = "full"; // Use full toolbar with all options
            RTE_DefaultConfig.editorResizeMode = "both"; // Allow both width and height resize
            RTE_DefaultConfig.showTagList = true; // Show tag list
            RTE_DefaultConfig.showStatistics = true; // Show statistics
            RTE_DefaultConfig.showPlusButton = true; // Show plus button
            RTE_DefaultConfig.showFloatTextToolBar = true; // Show float text toolbar
            RTE_DefaultConfig.showFloatLinkToolBar = true; // Show float link toolbar
            RTE_DefaultConfig.showFloatImageToolBbar = true; // Show float image toolbar
            RTE_DefaultConfig.showFloatTableToolBar = true; // Show float table toolbar
            RTE_DefaultConfig.showFloatParagraph = true; // Show float paragraph
            RTE_DefaultConfig.enableDragDrop = true; // Enable drag and drop
            RTE_DefaultConfig.enableObjectResizing = true; // Enable object resizing
            RTE_DefaultConfig.toggleBorder = true; // Enable toggle border
        }
        
        // Destroy existing editors if they exist - more thorough cleanup
        if (variantDescriptionEditor) {
            try {
                if (typeof variantDescriptionEditor.destroy === 'function') {
                    variantDescriptionEditor.destroy();
                }
            } catch (e) {
                console.warn('Error destroying description editor:', e);
            }
            variantDescriptionEditor = null;
            window.variantDescriptionEditor = null;
        }
        if (variantAdditionalInfoEditor) {
            try {
                if (typeof variantAdditionalInfoEditor.destroy === 'function') {
                    variantAdditionalInfoEditor.destroy();
                }
            } catch (e) {
                console.warn('Error destroying additional info editor:', e);
            }
            variantAdditionalInfoEditor = null;
            window.variantAdditionalInfoEditor = null;
        }
        
        // Clear any existing editor containers/divs that might have been created
        const $descriptionTextarea = $('#variantDescription');
        const $additionalInfoTextarea = $('#variantAdditionalInfo');
        
        // Remove any existing editor containers/DOM elements before re-initializing
        if ($descriptionTextarea.length) {
            // Remove all sibling divs (editor containers created by RichTextEditor)
            $descriptionTextarea.siblings('div').remove();
            // Also check parent's direct children for editor containers
            const descriptionParent = $descriptionTextarea.parent();
            descriptionParent.find('> div').not($descriptionTextarea).remove();
        }
        
        if ($additionalInfoTextarea.length) {
            // Remove all sibling divs (editor containers created by RichTextEditor)
            $additionalInfoTextarea.siblings('div').remove();
            // Also check parent's direct children for editor containers
            const additionalInfoParent = $additionalInfoTextarea.parent();
            additionalInfoParent.find('> div').not($additionalInfoTextarea).remove();
        }
        
        // Initialize Description Editor (always initialize after cleanup to ensure clean state)
        if ($descriptionTextarea.length && typeof RichTextEditor !== 'undefined') {
            try {
                variantDescriptionEditor = new RichTextEditor($descriptionTextarea[0]);
                window.variantDescriptionEditor = variantDescriptionEditor;
                
                // Update textarea on change
                variantDescriptionEditor.attachEvent("textchanged", function() {
                    $descriptionTextarea.val(variantDescriptionEditor.getHTMLCode());
                });
            } catch (e) {
                console.error('Error initializing description editor:', e);
            }
        }
        
        // Initialize Additional Information Editor (always initialize after cleanup to ensure clean state)
        if ($additionalInfoTextarea.length && typeof RichTextEditor !== 'undefined') {
            try {
                variantAdditionalInfoEditor = new RichTextEditor($additionalInfoTextarea[0]);
                window.variantAdditionalInfoEditor = variantAdditionalInfoEditor;
                
                // Update textarea on change
                variantAdditionalInfoEditor.attachEvent("textchanged", function() {
                    $additionalInfoTextarea.val(variantAdditionalInfoEditor.getHTMLCode());
                });
            } catch (e) {
                console.error('Error initializing additional info editor:', e);
            }
        }
    }
    
    // Initialize editors when offcanvas is shown
    if ($variantEditModalElement.length) {
        $variantEditModalElement.on('shown.bs.offcanvas', function() {
            // Wait a bit to ensure offcanvas is fully rendered
            setTimeout(function() {
                initializeVariantRichTextEditors();
            }, 100);
        });
        
        $variantEditModalElement.on('hidden.bs.offcanvas', function() {
            activeVariantRow = null;
            syncModalImagePreview(null, []);
            
            // Destroy editors when offcanvas is hidden - more thorough cleanup
            if (variantDescriptionEditor) {
                try {
                    if (typeof variantDescriptionEditor.destroy === 'function') {
                        variantDescriptionEditor.destroy();
                    }
                } catch (e) {
                    console.warn('Error destroying description editor on close:', e);
                }
                variantDescriptionEditor = null;
                window.variantDescriptionEditor = null;
            }
            
            if (variantAdditionalInfoEditor) {
                try {
                    if (typeof variantAdditionalInfoEditor.destroy === 'function') {
                        variantAdditionalInfoEditor.destroy();
                    }
                } catch (e) {
                    console.warn('Error destroying additional info editor on close:', e);
                }
                variantAdditionalInfoEditor = null;
                window.variantAdditionalInfoEditor = null;
            }
            
            // Clear textarea values and remove any editor containers/DOM elements
            const $descriptionTextarea = $('#variantDescription');
            const $additionalInfoTextarea = $('#variantAdditionalInfo');
            
            if ($descriptionTextarea.length) {
                $descriptionTextarea.val('');
                // Remove any editor-related DOM elements that might have been created
                const descriptionParent = $descriptionTextarea.parent();
                // Remove all divs that are siblings of the textarea (likely editor containers)
                descriptionParent.find('> div').not($descriptionTextarea).each(function() {
                    $(this).remove();
                });
                // Also check for editor containers at the same level
                $descriptionTextarea.siblings('div').each(function() {
                    $(this).remove();
                });
            }
            
            if ($additionalInfoTextarea.length) {
                $additionalInfoTextarea.val('');
                // Remove any editor-related DOM elements that might have been created
                const additionalInfoParent = $additionalInfoTextarea.parent();
                // Remove all divs that are siblings of the textarea (likely editor containers)
                additionalInfoParent.find('> div').not($additionalInfoTextarea).each(function() {
                    $(this).remove();
                });
                // Also check for editor containers at the same level
                $additionalInfoTextarea.siblings('div').each(function() {
                    $(this).remove();
                });
            }
        });
    }

    // Load units automatically on page load if not already loaded from static data
    if (!unitsLoaded && !unitsLoading && measurementAttributes.length > 0) {
        loadUnitsFromModule(function() {
            updateMeasurementEmptyState();
        });
    } else {
        updateMeasurementEmptyState();
    }
    
    handleDiscountStateChange();
    calculateVariantSellPrice();
    updateVariantStatusToggleLabel();

    let selectedAttributeIds = [];
    let attributeValues = {};
    let selectedAttributeValues = {};
    let generatedVariants = [];
    let activeVariantRow = null;
    let bulkEditTargets = [];

    if ($discountTypeSelect.length) {
        $discountTypeSelect.on('change', function() {
            handleDiscountStateChange();
        });
    }

    if ($discountValueInput.length) {
        $discountValueInput.on('input', function() {
            if ($discountActiveSelect.length && $discountActiveSelect.val() === '0' && $discountTypeSelect.length && $discountTypeSelect.val()) {
                $discountActiveSelect.val('1');
            }
            calculateVariantSellPrice();
        });
    }

    if ($discountActiveSelect.length) {
        $discountActiveSelect.on('change', function() {
            calculateVariantSellPrice();
        });
    }

    if ($mrpInput.length) {
        $mrpInput.on('input', calculateVariantSellPrice);
    }

    if ($variantStatusToggle.length) {
        $variantStatusToggle.on('change', function() {
            updateVariantStatusToggleLabel();
        });
    }

    if ($variantManageImagesBtn.length) {
        $variantManageImagesBtn.on('click', function() {
            if (!activeVariantRow) {
                showToast('error', 'Select a variant row before managing images.');
                return;
            }
            const $rowImageInput = $(activeVariantRow).find('.variant-image-input');
            if ($rowImageInput.length) {
                $rowImageInput.trigger('click');
            } else {
                showToast('error', 'Image uploader not available for this variant.');
            }
        });
    }

    // Removed search input handlers - Select2 handles search automatically
    refreshFeatherIcons();

    // Note: Variants section visibility is controlled by the dynamic form's section visibility logic
    
    // Function to create a default variant when no attributes are selected
    // DISABLED: Default variant creation removed per user request
    function createDefaultVariant() {
        // Do nothing - default variant creation is disabled
        return;
    }
    
    // Make function accessible globally (but it does nothing)
    window.createDefaultVariant = createDefaultVariant;
    
    // Initialize for edit mode if product exists and has variants
    // Use setTimeout to ensure DOM is ready
    setTimeout(function() {
        if (Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0) {
            loadExistingVariants(existingVariantsPayload);
        }
    }, 200);
    // Default variant auto-creation removed per user request

    // Attribute selection
    function attributeIsUsedInVariants(attributeId) {
        if (!Array.isArray(generatedVariants) || !generatedVariants.length) {
            return false;
        }
        const key = String(attributeId);
        return generatedVariants.some(variant => {
            const normalizedAttributes = normalizeAttributesPayload(variant.attributes || variant);
            return Object.prototype.hasOwnProperty.call(normalizedAttributes, key);
        });
    }
 

    // Removed moveAttributeToSelected and moveAttributeToAvailable - attributes now stay in place with visual feedback

    function ensureAttributeSelectionFromVariants() {
        if (!Array.isArray(generatedVariants) || !generatedVariants.length) {
            return;
        }

        const requiredAttributeIds = new Set();
        generatedVariants.forEach((variant, index) => {
            const normalized = normalizeAttributesPayload(variant.attributes || variant);
            Object.keys(normalized).forEach(id => {
                requiredAttributeIds.add(String(id));
            });
        });

        if (!requiredAttributeIds.size) {
            return;
        }

        let attributesChanged = false;

        // Disabled: Don't auto-check attributes based on existing variants
        // requiredAttributeIds.forEach(attributeId => {
        //     const attrIdStr = String(attributeId);
        //     if (!selectedAttributeIds.includes(attrIdStr)) {
        //         selectedAttributeIds.push(attrIdStr);
        //         attributesChanged = true;
        //     }

        //     // Select checkbox
        //     const $container = $('#availableAttributesContainer');
        //     if ($container.length) {
        //         const $checkbox = $container.find(`.attribute-checkbox[value="${attrIdStr}"]`);
        //         if ($checkbox.length) {
        //             if (!$checkbox.is(':checked')) {
        //                 $checkbox.prop('checked', true);
        //                 // Manually update selectedAttributeIds first
        //                 if (!selectedAttributeIds.includes(attrIdStr)) {
        //                     selectedAttributeIds.push(attrIdStr);
        //                 }
        //                 // Note: We don't trigger change event on page load to avoid loading values automatically
        //                 // Values will only load when user manually clicks/unchanges the checkbox
        //                 attributesChanged = true;
        //             }
        //         }
        //     }
        // });
 
    }

    function preselectExistingAttributeCheckboxes() {
        // Check SKU type - only allow attribute selection for variant products
        const selectedSkuType = $('input[name="sku_type"]:checked').val() || 'single';
        if (selectedSkuType !== 'variant') {
            // Clear any pre-selected attributes for single products
            setSelectedAttributeIds([]);
            selectedAttributeIds = [];
            return;
        }
        
        // Only preselect if we're in edit mode with existing variants
        const isEditMode = Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0;
        if (!isEditMode) {
            // For new variant products, don't clear - let user select attributes
            // Only clear if switching from variant to single (handled by SKU type change)
            return;
        }

        // Get selected values from checkboxes
        const selectedValues = getSelectedAttributeIds();
        if (!selectedValues.length) {
            return;
        }

        let attributesChanged = false;

        // Disabled: Don't auto-add to selectedAttributeIds - let user manually select
        // selectedValues.forEach(attributeId => {
        //     const id = String(attributeId);
        //     if (!selectedAttributeIds.includes(id)) {
        //         selectedAttributeIds.push(id);
        //         attributesChanged = true;
        //     }
        // });

        if (attributesChanged || selectedAttributeIds.length) {
            updateAttributeValuesConfig();
        }
    }

    function preserveUnsatisfiedVariants() {
        // DISABLED: Don't preserve anything automatically - let user manually select everything
        // All automatic preservation has been disabled per user request
        // This function is kept for potential future use but does nothing
        return;
    }

    // Prevent multiple simultaneous calls to updateAttributeValuesConfig
    // Variables are declared globally above (before DOMContentLoaded)
    
    function updateAttributeValuesConfig() {
        // Don't update attribute config during draft restoration - let user manually select
        if (isRestoringVariantDraft) {
            return;
        }
        
        // Debounce: Clear any pending calls
        if (updateAttributeConfigTimeout) {
            clearTimeout(updateAttributeConfigTimeout);
        }
        
        // Store the initial load state before we potentially change it
        const wasInitialLoad = isInitialLoad;
        
        // If already updating, queue this call (but allow initial load to proceed)
        if (isUpdatingAttributeConfig && !isInitialLoad) {
            updateAttributeConfigTimeout = setTimeout(function() {
                updateAttributeValuesConfig();
            }, 300);
            return;
        }
        isUpdatingAttributeConfig = true;
        
        // Mark that initial load is done after first call (but preserve the state for this call)
        // We'll use wasInitialLoad to check if this is the initial load
        if (isInitialLoad) {
            isInitialLoad = false;
        }
        
        preserveUnsatisfiedVariants();

        const $addVariantForm = $('#addVariantForm');
        const $variantAttributeSelectors = $('#variantAttributeSelectors');
        
        if (!$addVariantForm.length || !$variantAttributeSelectors.length) {
            isUpdatingAttributeConfig = false;
            return;
        }

        const shouldShowForm = selectedAttributeIds.length > 0;
        
        if (shouldShowForm) {
            $addVariantForm.css('display', 'block');
            
            // Destroy existing Select2 instances before clearing
            $variantAttributeSelectors.find('.variant-attribute-selector').each(function() {
                const $select = $(this);
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
            
            // Check which containers already exist and have values loaded
            const existingContainers = new Map();
            const seenAttributeIds = new Set();
            
            $variantAttributeSelectors.find('[data-attribute-id]').each(function() {
                const $div = $(this);
                const attrId = $div.attr('data-attribute-id');
                const $container = $div.find('.attribute-values-checkbox-container');
                
                // Check if this container has values loaded
                // Check for checkboxes directly or inside a row element with content
                const $rowElement = $container.find('.row');
                const rowHasContent = $rowElement.length && $rowElement.children().length > 0;
                const hasValues = $container.length ? (
                    !!$container.find('.variant-attribute-value-checkbox').length || 
                    (rowHasContent && !$container.find('.fa-spinner').length && !$container.find('.text-muted:not(.text-danger)').length) ||
                    !!($container.find('.text-danger').length && !$container.find('.fa-spinner').length)
                ) : false;
                
                // If we've already seen this attribute ID
                if (seenAttributeIds.has(attrId)) {
                    // Keep the one with values, remove the one without
                    const existing = existingContainers.get(attrId);
                    if (existing && existing.hasValues && !hasValues) {
                        // Existing has values, this one doesn't - remove this duplicate
                        $div.remove();
                        return;
                    } else if (existing && !existing.hasValues && hasValues) {
                        // This one has values, existing doesn't - remove the existing one
                        if (existing.$div && existing.$div.parent().length) {
                            existing.$div.remove();
                        }
                        // Update to use this one
                        existingContainers.set(attrId, { $div, $container, hasValues });
                    } else {
                        // Both have values or both don't - remove the duplicate (keep first one)
                        $div.remove();
                        return;
                    }
                } else {
                    seenAttributeIds.add(attrId);
                    existingContainers.set(attrId, { $div, $container, hasValues });
                }
                
                // DEBUG: Attribute/Value - Container detection
                console.log('[ATTR/VAL] Found container for attribute', attrId, 'hasContainer:', $container.length > 0, 'hasValues:', hasValues);
            });
            
            // DEBUG: Attribute/Value - Container mapping
            console.log('[ATTR/VAL] Existing containers:', Array.from(existingContainers.keys()), 'Selected IDs:', selectedAttributeIds);
            
            // Remove containers for unchecked attributes completely from the form
            const selectedIdsSet = new Set(selectedAttributeIds.map(id => String(id)));
            
            // Remove containers for attributes that are no longer selected
            const containersToRemove = [];
            existingContainers.forEach((item, attrId) => {
                if (!selectedIdsSet.has(attrId)) {
                    // Remove the container completely from the DOM
                    if (item.$div && item.$div.length) {
                        item.$div.remove();
                    }
                    
                    // Clear attribute values from data structures
                    const attrKey = String(attrId);
                    if (selectedAttributeValues[attrKey]) {
                        delete selectedAttributeValues[attrKey];
                    }
                    if (attributeValues[attrKey]) {
                        delete attributeValues[attrKey];
                    }
                    
                    // Keep cache so values can be reloaded quickly if attribute is re-selected
                    // But clear pending promises
                    if (attributeValuesPromises.has(attrKey)) {
                        attributeValuesPromises.delete(attrKey);
                    }
                    
                    // Mark for removal from map
                    containersToRemove.push(attrId);
                }
            });
            
            // Remove unchecked attribute containers from the map
            containersToRemove.forEach(attrId => {
                existingContainers.delete(attrId);
            });

            // Create or update multi-select checkboxes for each selected attribute
            selectedAttributeIds.forEach(attributeId => {
                const attrIdStr = String(attributeId);
                
                // First, check if container exists in DOM (more reliable than map)
                const $existingDiv = $variantAttributeSelectors.find(`[data-attribute-id="${attrIdStr}"]`);
                
                if ($existingDiv.length) {
                    // Container div exists in DOM - check if inner container exists
                    let $container = $existingDiv.find('.attribute-values-checkbox-container');
                    
                    if ($container.length) {
                        // Both outer div and inner container exist - preserve it completely
                        // Don't modify anything, just return
                        return;
                    } else {
                        // Outer div exists but inner container is missing - need to recreate inner container
                        // Get attribute info first
                        const attrInfo = getAttributeInfo(attributeId);
                        if (!attrInfo) {
                            return;
                        }
                        const attributeType = attrInfo.type;
                        const attributeName = attrInfo.name;
                        
                        // Check if "Add Value" button exists, if not add it
                        let $addValueBtn = $existingDiv.find('.add-attribute-value-btn');
                        if (!$addValueBtn.length) {
                            $addValueBtn = $('<button>')
                                .attr('type', 'button')
                                .addClass('btn btn-sm add-attribute-value-btn')
                                .attr('data-attribute-id', attributeId)
                                .attr('data-attribute-type', attributeType)
                                .attr('data-attribute-name', attributeName)
                                .attr('title', `Add new value for ${attributeName}`)
                                .css({
                                    'background': '#f5c000',
                                    'color': '#ffffff',
                                    'border': 'none',
                                    'padding': '4px 10px',
                                    'border-radius': '4px',
                                    'display': 'flex',
                                    'align-items': 'center'
                                })
                                .html(`
                                    <i class="bx bx-plus-circle me-1" style="color:#ffffff; margin-right:.25rem;"></i>
                                    Add Value
                                `);
                            $existingDiv.find('.d-flex').append($addValueBtn);
                        }
                        
                        // Recreate the inner container
                        const $innerContainer = $('<div>')
                            .addClass('attribute-values-checkbox-container')
                            .attr('data-attribute-id', attributeId)
                            .attr('data-attribute-type', attributeType)
                            .html(`
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle me-2"></i>Click "Load Values" button to load attribute values
                                </div>
                            `);
                        $existingDiv.append($innerContainer);
                        
                        // Attach click handler for "Add Value" button
                        $addValueBtn.off('click').on('click', function() {
                            openAddAttributeValueModal(attributeId, attributeType, attributeName);
                        });
                        
                        return;
                    }
                }
                
                
                // Get attribute info using helper function
                const attrInfo = getAttributeInfo(attributeId);
                if (!attrInfo) {
                    return;
                }
                
                const attributeType = attrInfo.type;
                const attributeName = attrInfo.name;
                
                // Create new checkbox container for this attribute
                $selectorDiv = $('<div>')
                    .addClass('col-md-12 mb-3')
                    .attr('data-attribute-id', attributeId);
                $selectorDiv.html(`
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">
                        <strong>${escapeHtml(attributeName)}</strong>
                     
                    </label>

                    <button type="button"
                            class="btn btn-sm add-attribute-value-btn"
                            data-attribute-id="${attributeId}"
                            data-attribute-type="${attributeType}"
                            data-attribute-name="${escapeHtml(attributeName)}"
                            title="Add new value for ${escapeHtml(attributeName)}"
                            style="background:#f5c000; color:#ffffff; border:none; padding:4px 10px; border-radius:4px; display:flex; align-items:center;">
                        
                        <i class="bx bx-plus-circle me-1" 
                           style="color:#ffffff; margin-right:.25rem;"></i>
                        
                        Add Value
                    </button>
                  </div>
                  <div class="attribute-values-checkbox-container" 
                       data-attribute-id="${attributeId}" 
                       data-attribute-type="${attributeType}">
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-2"></i>Click "Load Values" button to load attribute values
                    </div>
                  </div>
                `);
                
                // Final check before appending - make sure no duplicate was created
                const $finalCheck = $variantAttributeSelectors.find(`[data-attribute-id="${attrIdStr}"]`);
                if ($finalCheck.length && $finalCheck[0] !== $selectorDiv[0]) {
                    return; // Don't append, container already exists
                }
                
                $variantAttributeSelectors.append($selectorDiv); 
                // Don't auto-load values - wait for user to click "Load Values" button
                // Values will be loaded when the button is clicked
                
                // Attach click handler for "Add Value" button
                $selectorDiv.find('.add-attribute-value-btn').on('click', function() {
                    openAddAttributeValueModal(attributeId, attributeType, attributeName);
                });
            });
        } else {
            // Destroy Select2 instances when hiding the form
            $variantAttributeSelectors.find('.variant-attribute-selector').each(function() {
                const $select = $(this);
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
            $addVariantForm.css('display', 'none');
        }

        // Show variants table if there are existing variants OR if no attributes are selected (default variant)
        const hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
        const noAttributesSelected = selectedAttributeIds.length === 0;
        
        if (hasVariants) {
            if (variantsTableContainer) {
                $variantsTableContainer.css('display', 'block');
            }
            if ($bulkActions.length) {
                $bulkActions.css('display', 'block');
            }
        } else if (noAttributesSelected) {
            // Auto-create default variant when no attributes selected
            // Ensure table is visible first
            if (variantsTableContainer) {
                $variantsTableContainer.css('display', 'block');
            }
            
            // Default variant creation removed per user request
            // Show table if it exists
            if (variantsTableContainer) {
                $variantsTableContainer.css('display', 'block');
            }
        } else {
            // Hide table when attributes are selected but no variants generated yet
            if (variantsTableContainer) {
                $variantsTableContainer.css('display', 'none');
            }
            if (bulkActions) {
                $bulkActions.css('display', 'none');
            }
        }

        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
        
        // Reset the flag to allow future calls
        isUpdatingAttributeConfig = false;
    }
    
  
    // Track which containers are already being populated to prevent duplicates
    const containersBeingPopulated = new Set();
    
    // Load attribute values for multi-select checkboxes
    function loadAttributeValuesForMultiSelect(attributeId, attributeType, containerElement) {
        if (!containerElement || !attributeId) {
            return;
        }
        
        const attributeKey = String(attributeId);
        
        // Check cache first
        if (attributeValuesCache.has(attributeKey)) {
            const cachedData = attributeValuesCache.get(attributeKey);
            populateMultiSelectFromData(attributeId, attributeType, containerElement, cachedData);
            return;
        }
        
        // Check if there's already a pending request for this attribute
        if (attributeValuesPromises.has(attributeKey)) {
            attributeValuesPromises.get(attributeKey).then(data => {
                populateMultiSelectFromData(attributeId, attributeType, containerElement, data);
            }).catch(error => {
                console.error('Error loading attribute values:', error);
                containerElement.innerHTML = `
                    <div class="text-danger text-center py-2">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading values. Please try again.
                    </div>
                `;
            });
            return;
        }
        
        // Show loading state
        containerElement.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin me-2"></i>Loading values...
            </div>
        `;
        
        // Make API request
        const promise = $.ajax({
            url: `{{ url('attributes') }}/${attributeId}/values`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(data) {
            // Cache the data
            attributeValuesCache.set(attributeKey, data);
            attributeValuesPromises.delete(attributeKey);
            return data;
        }).catch(function(xhr) {
            attributeValuesPromises.delete(attributeKey);
            throw xhr;
        });
        
        // Store the promise
        attributeValuesPromises.set(attributeKey, promise);
        
        // Handle the response
        promise.then(function(data) {
            populateMultiSelectFromData(attributeId, attributeType, containerElement, data);
        }).catch(function(xhr) {
            console.error('Error loading attribute values:', xhr);
            containerElement.innerHTML = `
                <div class="text-danger text-center py-2">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading values. Please try again.
                </div>
            `;
        });
    }
    
    // Populate multi-select checkboxes from data
    function populateMultiSelectFromData(attributeId, attributeType, containerElement, data) {
        if (!containerElement) {
            return;
        }
        
        // Clear loading state attribute
        containerElement.removeAttribute('data-loading-start');
        containerElement.innerHTML = '';
        
        if (!data || data.length === 0) {
            containerElement.innerHTML = `
                <div class="text-muted text-center py-2">
                    <i class="fas fa-info-circle me-2"></i>
                    No values available. Click "Add Value" to create one.
                </div>
            `;
            return;
        }
        
        // DEBUG: Attribute/Value - Processing values
        console.log('[ATTR/VAL] Processing', data.length, 'values for attribute', attributeId);
        
        // Create checkbox grid
        const checkboxGrid = document.createElement('div');
        checkboxGrid.className = 'row g-2';
        
        data.forEach(value => {
            const valueItem = value.value || '';
            const colorCode = value.color_code || '';
            
            const colDiv = document.createElement('div');
            colDiv.className = attributeType === 'color' ? 'col-6 col-md-4 col-lg-3' : 'col-12';
            
            const checkboxId = `attr_${attributeId}_value_${valueItem.replace(/[^a-zA-Z0-9]/g, '_')}_${value.id || Date.now()}`; 
            
            let checkboxHtml = '';
            
            if (attributeType === 'color' && colorCode) {
                // Color attribute with color swatch
                checkboxHtml = `
                    <div class="form-check">
                        <input class="form-check-input variant-attribute-value-checkbox" 
                               type="checkbox" 
                               id="${checkboxId}"
                               value="${escapeHtml(valueItem)}"
                               data-attribute-id="${attributeId}"
                               data-attribute-type="${attributeType}"
                               data-color-code="${escapeHtml(colorCode)}">
                        <label class="form-check-label d-flex align-items-center" for="${checkboxId}" style="cursor: pointer;">
                            <span class="color-swatch me-2" 
                                  style="width: 30px; height: 30px; border: 2px solid #dee2e6; border-radius: 4px; background-color: ${escapeHtml(colorCode)}; display: inline-block; flex-shrink: 0;"></span>
                            <span>${escapeHtml(valueItem)}</span>
                        </label>
                    </div>
                `;
            } else {
                // Regular attribute
                checkboxHtml = `
                    <div class="form-check">
                        <input class="form-check-input variant-attribute-value-checkbox" 
                               type="checkbox" 
                               id="${checkboxId}"
                               value="${escapeHtml(valueItem)}"
                               data-attribute-id="${attributeId}"
                               data-attribute-type="${attributeType}">
                        <label class="form-check-label" for="${checkboxId}" style="cursor: pointer;">
                            ${escapeHtml(valueItem)}
                        </label>
                    </div>
                `;
            }
            
            colDiv.innerHTML = checkboxHtml;
            checkboxGrid.appendChild(colDiv);
        });
        
        containerElement.appendChild(checkboxGrid);
        
        // DEBUG: Attribute/Value - Values populated
        const checkboxesCount = $(containerElement).find('.variant-attribute-value-checkbox').length;
        console.log('[ATTR/VAL] Successfully populated', checkboxesCount, 'checkboxes for attribute', attributeId, 'container children:', containerElement.children.length);
    }

    // Function disabled - value loading functionality removed
    function loadAttributeValuesForSelector(attributeId, attributeType, selectElement) {
        return; // Disabled
    }
    
    // Helper function to populate selector from cached or fetched data
    function populateSelectorFromData(attributeId, attributeType, selectElement, data) {
        if (data && data.length > 0) {
            data.forEach(value => {
                const option = document.createElement('option');
                option.value = value.value;
                option.textContent = value.value;
                if (value.color_code) {
                    option.setAttribute('data-color-code', value.color_code);
                }
                selectElement.appendChild(option);
            });
        } else {
            // If no values exist, allow text input
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No values available - enter manually';
            selectElement.appendChild(option);
        }
        
        // Initialize Select2 for this select element
        const $select = $(selectElement);
        if ($select.data('select2')) {
            $select.select2('destroy');
        }
        
        // Find the label - it's a sibling of the select element in the same parent
        const parentDiv = selectElement.parentElement;
        const labelElement = parentDiv ? parentDiv.querySelector('label') : null;
        const labelText = labelElement ? labelElement.textContent.trim() : 'attribute';
                
                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: `-- Select ${labelText} --`,
                    allowClear: true,
                    width: '100%',
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        const $option = $(data.element);
                        const colorCode = $option.data('color-code');
                        if (colorCode) {
                            const $container = $('<span></span>').css('display', 'flex').css('align-items', 'center');
                            const $colorBox = $('<span></span>')
                                .css('display', 'inline-block')
                                .css('width', '20px')
                                .css('height', '20px')
                                .css('background-color', colorCode)
                                .css('border', '1px solid #ccc')
                                .css('border-radius', '3px')
                                .css('margin-right', '8px')
                                .css('flex-shrink', '0');
                            const $text = $('<span></span>').text(data.text);
                            return $container.append($colorBox).append($text);
                        }
                        return data.text;
                    },
                    templateSelection: function(data) {
                        const $option = $(data.element);
                        const colorCode = $option.data('color-code');
                        if (colorCode) {
                            const $container = $('<span></span>').css('display', 'flex').css('align-items', 'center');
                            const $colorBox = $('<span></span>')
                                .css('display', 'inline-block')
                                .css('width', '16px')
                                .css('height', '16px')
                                .css('background-color', colorCode)
                                .css('border', '1px solid #ccc')
                                .css('border-radius', '3px')
                                .css('margin-right', '6px')
                                .css('flex-shrink', '0');
                            const $text = $('<span></span>').text(data.text);
                            return $container.append($colorBox).append($text);
                        }
                        return data.text;
                    }
                });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Open modal for adding new attribute value
    function openAddAttributeValueModal(attributeId, attributeType, attributeName) {
        
        // Try to find modal element - check multiple ways
        let $modalElement = $('#addAttributeValueModal');
        
        if (!$modalElement.length) {
            alert('Modal not found. The page may not be fully loaded. Please refresh and try again.');
            return;
        }
        
        // Check modal content - maybe form is in modal-body
        const $modalBody = $modalElement.find('.modal-body');
        
        // Now find form and other elements - search in modal-body first
        let $form = $modalBody.find('#addAttributeValueForm');
        if (!$form.length) {
            $form = $modalBody.find('form');
        }
        if (!$form.length) {
            $form = $modalElement.find('#addAttributeValueForm');
        }
        if (!$form.length) {
            $form = $modalElement.find('form');
        }
        if (!$form.length) {
            $form = $('#addAttributeValueForm');
        }
        
        // Check each element individually and provide specific error messages
        if (!$form.length) {
            
            // Try to create the form if modal body exists
            if ($modalBody.length) {
                $form = $('<form>')
                    .attr('id', 'addAttributeValueForm')
                    .html(`
                        <input type="hidden" id="modalAttributeId" name="attribute_id">
                        <input type="hidden" id="modalAttributeType" name="attribute_type">
                        <div class="mb-3">
                            <label for="modalAttributeName" class="form-label">Attribute</label>
                            <input type="text" class="form-control" id="modalAttributeName" readonly>
                        </div>
                        <div class="mb-3" id="modalValueInputWrapper">
                            <!-- Dynamic input will be inserted here based on attribute type -->
                        </div>
                        <div class="mb-3 d-none" id="modalColorInputWrapper">
                            <label for="modalColorCode" class="form-label">Color Code</label>
                            <input type="hidden" class="form-control" id="modalColorCode" value="#000000">
                            <button type="button" class="btn btn-sm w-100" id="modalColorCodeBtn" style="background-color: #000000; height: 38px; border: 1px solid #ced4da; border-radius: 0.375rem; cursor: pointer;"></button>
                        </div>
                        <div class="mb-3">
                            <label for="modalSortOrder" class="form-label">Sort Order <span class="text-muted">(optional)</span></label>
                            <input type="number" class="form-control" id="modalSortOrder" name="sort_order" min="0" placeholder="Auto">
                        </div>
                    `);
                $modalBody.empty(); // Clear any existing content
                $modalBody.append($form);
            } else {
                alert('Form element not found and modal body is missing. Please refresh the page and try again.');
                return;
            }
        }
        
        // Get or create modal instance first (before querying wrappers)
        let modal = bootstrap.Modal.getInstance($modalElement[0]);
        if (!modal) {
            modal = new bootstrap.Modal($modalElement[0]);
        }
        
        // Now query for wrappers AFTER form is confirmed to exist
        // Search within form first (most reliable), then modal, then document
        let $valueInputWrapper = $form.find('#modalValueInputWrapper');
        if (!$valueInputWrapper.length) {
            $valueInputWrapper = $modalElement.find('#modalValueInputWrapper');
        }
        if (!$valueInputWrapper.length) {
            $valueInputWrapper = $('#modalValueInputWrapper');
        }
        
        let $colorInputWrapper = $form.find('#modalColorInputWrapper');
        if (!$colorInputWrapper.length) {
            $colorInputWrapper = $modalElement.find('#modalColorInputWrapper');
        }
        if (!$colorInputWrapper.length) {
            $colorInputWrapper = $('#modalColorInputWrapper');
        }
        
        if (!$valueInputWrapper.length) {
            alert('Value input wrapper not found. Please refresh the page and try again.');
            return;
        }
        
        
        // Set attribute info - search within form first, then document
        const $attributeIdInput = $form.find('#modalAttributeId');
        if (!$attributeIdInput.length) {
            $attributeIdInput = $('#modalAttributeId');
        }
        const $attributeTypeInput = $form.find('#modalAttributeType');
        if (!$attributeTypeInput.length) {
            $attributeTypeInput = $('#modalAttributeType');
        }
        const $attributeNameInput = $form.find('#modalAttributeName');
        if (!$attributeNameInput.length) {
            $attributeNameInput = $('#modalAttributeName');
        }
        
        if ($attributeIdInput.length) $attributeIdInput.val(attributeId);
        if ($attributeTypeInput.length) $attributeTypeInput.val(attributeType);
        if ($attributeNameInput.length) $attributeNameInput.val(attributeName);
        
        // Reset form - but preserve the hidden inputs we just set
        if ($form.length) {
            // Get current values before reset
            const currentAttributeId = $attributeIdInput.length ? $attributeIdInput.val() : '';
            const currentAttributeType = $attributeTypeInput.length ? $attributeTypeInput.val() : '';
            const currentAttributeName = $attributeNameInput.length ? $attributeNameInput.val() : '';
            
            $form[0].reset();
            
            // Restore the values after reset
            if ($attributeIdInput.length) $attributeIdInput.val(currentAttributeId || attributeId);
            if ($attributeTypeInput.length) $attributeTypeInput.val(currentAttributeType || attributeType);
            if ($attributeNameInput.length) $attributeNameInput.val(currentAttributeName || attributeName);
        }
        
        const $colorCodeInput = $form.find('#modalColorCode');
        if (!$colorCodeInput.length) {
            $colorCodeInput = $('#modalColorCode');
        }
        const $colorCodeBtn = $('#modalColorCodeBtn');
        if ($colorCodeInput.length) {
            $colorCodeInput.val('#000000');
        }
        if ($colorCodeBtn.length) {
            $colorCodeBtn.css('backgroundColor', '#000000');
            // Reinitialize Pickr if it exists, otherwise wait for modal to be shown
            if (window.variantColorPickers && window.variantColorPickers['modalColorCode']) {
                try {
                    window.variantColorPickers['modalColorCode'].setColor('#000000');
                } catch(e) {
                }
            }
            // Will be initialized when modal is shown (handled below)
        }
        
        // Clear and create input field based on attribute type
        // Make sure we're clearing the wrapper, not duplicating
        if ($valueInputWrapper.length) {
            $valueInputWrapper.empty();
        }
        const $label = $('<label>')
            .addClass('form-label')
            .attr('for', 'modalValueInput')
            .text('Value *');
        
        let $input;
        if (attributeType === 'boolean') {
            $input = $('<select>')
                .addClass('form-select')
                .attr('id', 'modalValueInput')
                .attr('name', 'value')
                .html(`
                    <option value="">-- Select Option --</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                `);
        } else if (attributeType === 'number') {
            $input = $('<input>')
                .attr('type', 'number')
                .addClass('form-control')
                .attr('id', 'modalValueInput')
                .attr('name', 'value')
                .attr('step', '0.01')
                .attr('placeholder', 'Enter numeric value')
                .prop('required', true);
        } else if (attributeType === 'date') {
            $input = $('<input>')
                .attr('type', 'date')
                .addClass('form-control')
                .attr('id', 'modalValueInput')
                .attr('name', 'value')
                .prop('required', true);
        } else if (attributeType === 'color') {
            $input = $('<input>')
                .attr('type', 'text')
                .addClass('form-control')
                .attr('id', 'modalValueInput')
                .attr('name', 'value')
                .attr('placeholder', 'Enter color name (e.g., Red, Blue)')
                .prop('required', true);
            if ($colorInputWrapper.length) {
                $colorInputWrapper.removeClass('d-none');
                // Pickr will be initialized after modal is shown (handled below)
            }
        } else {
            $input = $('<input>')
                .attr('type', 'text')
                .addClass('form-control')
                .attr('id', 'modalValueInput')
                .attr('name', 'value')
                .attr('placeholder', 'Enter value')
                .prop('required', true);
        }
        
        if (attributeType !== 'color' && $colorInputWrapper.length) {
            $colorInputWrapper.addClass('d-none');
        }
        
        // Verify the wrapper is still valid before appending
        if (!$valueInputWrapper.length || !$valueInputWrapper.parent().length) {
            // Re-query if needed
            $valueInputWrapper = $form.find('#modalValueInputWrapper');
            if (!$valueInputWrapper.length) {
                alert('Error: Could not find value input wrapper. Please refresh the page.');
                return;
            }
        }
        
        $valueInputWrapper.append($label);
        $valueInputWrapper.append($input);
        
        // Initialize Pickr for color type after modal is shown
        if (attributeType === 'color' && $colorInputWrapper.length) {
            // Wait for modal to be fully shown before initializing Pickr
            const initColorPicker = function() {
                const $btn = $('#modalColorCodeBtn');
                const $input = $('#modalColorCode');
                if ($btn.length && $input.length && $btn.parent().length) {
                    initializeVariantColorPicker('modalColorCode', $btn[0], $input[0], '#000000');
                    $modalElement.off('shown.bs.modal', initColorPicker);
                }
            };
            $modalElement.one('shown.bs.modal', initColorPicker);
        }
        
        // Ensure DOM updates are processed before showing modal
        // Use requestAnimationFrame to ensure the browser has rendered the new elements
        requestAnimationFrame(() => {
            modal.show();
        });
    }

    // Save new attribute value
    function saveNewAttributeValue() {
        
        const $form = $('#addAttributeValueForm');
        const $attributeIdInput = $('#modalAttributeId');
        const $attributeTypeInput = $('#modalAttributeType');
        const $valueInput = $('#modalValueInput');
        const $colorInput = $('#modalColorCode');
        const $sortOrderInput = $('#modalSortOrder');
        
        if (!$form.length || !$attributeIdInput.length || !$attributeTypeInput.length || !$valueInput.length) {
            alert('Form elements not found. Please refresh the page and try again.');
            return;
        }
        
        const attributeId = $attributeIdInput.val();
        const attributeType = $attributeTypeInput.val();
        
        if (!$valueInput.val() || !$valueInput.val().trim()) {
            alert('Please enter a value');
            return;
        }
        
        const payload = {
            value: $valueInput.val().trim(),
            sort_order: ($sortOrderInput.length && $sortOrderInput.val()) ? parseInt($sortOrderInput.val()) : null,
            color_code: (attributeType === 'color' && $colorInput.length && $colorInput.val()) ? $colorInput.val() : null
        };
        
        // Show loading state
        const $saveBtn = $('#saveAttributeValueBtn');
        if (!$saveBtn.length) {
            alert('Save button not found. Please refresh the page and try again.');
            return;
        }
        
        const originalText = $saveBtn.html();
        $saveBtn.prop('disabled', true);
        $saveBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        
        $.ajax({
            url: `{{ url('attributes') }}/${attributeId}/values`,
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(payload),
            success: function(result) {
                if (result.success) {
                    // Close modal
                    const $modalElement = $('#addAttributeValueModal');
                    const modalInstance = bootstrap.Modal.getInstance($modalElement[0]);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('success', result.message || 'Attribute value created successfully');
                    } else {
                        alert(result.message || 'Attribute value created successfully');
                    }
                    
                    // Clear cache for this attribute to force reload
                    const attributeKey = String(attributeId);
                    attributeValuesCache.delete(attributeKey);
                    
                    // Value loading functionality removed - no longer refreshing containers
                } else {
                    // Show error message
                    const errorMsg = result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Failed to create attribute value');
                    if (typeof showToast === 'function') {
                        showToast('error', errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Failed to create attribute value');
                } else {
                    alert('Failed to create attribute value');
                }
            },
            complete: function() {
                // Restore button state
                $saveBtn.prop('disabled', false);
                $saveBtn.html(originalText);
            }
        });
    }

    // Initialize save button handler - use event delegation to handle dynamically created buttons
    $(document).ready(function() {
        // Use event delegation on document to handle save button clicks (works even if modal is created later)
        $(document).on('click', '#saveAttributeValueBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            saveNewAttributeValue();
        });
    });
    
    // Also attach handler when modal is shown (in case button is created dynamically)
    $(document).on('shown.bs.modal', '#addAttributeValueModal', function(e) {
        const $saveBtn = $(this).find('#saveAttributeValueBtn');
        if ($saveBtn.length) {
            // jQuery handles event delegation automatically, no need to clone
            $saveBtn.off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Save button clicked (modal shown handler)');
                saveNewAttributeValue();
            });
        }
    });

    // Generate variants
    function getVariantKey(variant, attributeIdList = null) {
        if (!variant || typeof variant !== 'object') {
            return null;
        }

        const source = variant.attributes && typeof variant.attributes === 'object'
            ? variant.attributes
            : variant;

        let candidateKeys;
        if (Array.isArray(attributeIdList) && attributeIdList.length > 0) {
            candidateKeys = attributeIdList.map(id => String(id));
        } else {
            candidateKeys = Object.keys(source);
        }

        const pairs = [];
        candidateKeys.forEach(key => {
            if (!Object.prototype.hasOwnProperty.call(source, key)) {
                return;
            }
            const value = source[key];
            if (value === undefined || value === null || String(value).trim() === '') {
                return;
            }
            pairs.push([String(key), value]);
        });

        if (!pairs.length) {
            return null;
        }

        pairs.sort((a, b) => a[0].localeCompare(b[0], undefined, { numeric: true, sensitivity: 'base' }));
        return JSON.stringify(pairs);
    }

    function getVariantFallbackKey(variant, index) {
        if (variant && variant.id) {
            return `id:${variant.id}`;
        }
        if (variant && variant.sku) {
            return `sku:${variant.sku}`;
        }
        return `idx:${index}:${Date.now().toString(36)}:${Math.random().toString(36).slice(2)}`;
    }

    // Generate all combinations (cartesian product) of selected attribute values
    function generateAllCombinations(selectedValuesByAttribute) {
        const attributeIds = Object.keys(selectedValuesByAttribute);
        
        if (attributeIds.length === 0) {
            return [];
        }
        
        // Get all value arrays
        const valueArrays = attributeIds.map(attrId => selectedValuesByAttribute[attrId]);
        
        // Generate cartesian product
        function cartesianProduct(arrays) {
            if (arrays.length === 0) return [[]];
            if (arrays.length === 1) return arrays[0].map(v => [v]);
            
            const [first, ...rest] = arrays;
            const restProduct = cartesianProduct(rest);
            const result = [];
            
            for (const value of first) {
                for (const combination of restProduct) {
                    result.push([value, ...combination]);
                }
            }
            
            return result;
        }
        
        const combinations = cartesianProduct(valueArrays);
        
        // Convert to variant objects
        return combinations.map(combination => {
            const variantAttributes = {};
            attributeIds.forEach((attrId, index) => {
                variantAttributes[attrId] = combination[index];
            });
            return variantAttributes;
        });
    }
    
    // Get selected values for all attributes from checkboxes
    function getSelectedAttributeValues() {
        const selectedValuesByAttribute = {};
        
        // Find all checked value checkboxes
        const $checkedBoxes = $('#variantAttributeSelectors').find('.variant-attribute-value-checkbox:checked');
        
        $checkedBoxes.each(function() {
            const $checkbox = $(this);
            const attributeId = String($checkbox.data('attribute-id'));
            const value = $checkbox.val();
            
            if (attributeId && value) {
                if (!selectedValuesByAttribute[attributeId]) {
                    selectedValuesByAttribute[attributeId] = [];
                }
                selectedValuesByAttribute[attributeId].push(value);
            }
        });
        
        return selectedValuesByAttribute;
    }
    
    // Add variants button handler
    const $addVariantBtn = $('#addVariantBtn');
    if ($addVariantBtn.length) {
        $addVariantBtn.on('click', function() {
            // Get selected attribute values from checkboxes
            const selectedValuesByAttribute = getSelectedAttributeValues();
            
            // Check if any values are selected
            const hasSelectedValues = Object.keys(selectedValuesByAttribute).some(attrId => {
                return selectedValuesByAttribute[attrId] && selectedValuesByAttribute[attrId].length > 0;
            });
            
            if (!hasSelectedValues) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Please select at least one value for each attribute to generate variants.');
                } else {
                    alert('Please select at least one value for each attribute to generate variants.');
                }
                return;
            }
            
            // Check if all selected attributes have at least one value
            const missingValues = selectedAttributeIds.filter(attrId => {
                return !selectedValuesByAttribute[String(attrId)] || selectedValuesByAttribute[String(attrId)].length === 0;
            });
            
            if (missingValues.length > 0) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Please select at least one value for all selected attributes.');
                } else {
                    alert('Please select at least one value for all selected attributes.');
                }
                return;
            }
            
            // Generate all combinations
            const combinations = generateAllCombinations(selectedValuesByAttribute);
            
            if (combinations.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('error', 'No variants can be generated from the selected values.');
                } else {
                    alert('No variants can be generated from the selected values.');
                }
                return;
            }
            
            // Convert combinations to variant format and add to generatedVariants
            combinations.forEach(combination => {
                // Check if this combination already exists
                const exists = generatedVariants.some(variant => {
                    const variantAttrs = variant.attributes || {};
                    return Object.keys(combination).every(attrId => {
                        return variantAttrs[attrId] === combination[attrId];
                    }) && Object.keys(variantAttrs).length === Object.keys(combination).length;
                });
                
                if (!exists) {
                    generatedVariants.push({
                        attributes: combination,
                        sku: '',
                        price: '',
                        sale_price: '',
                        is_active: '1',
                        stock_quantity: 0,
                        stock_status: 'in_stock',
                        manage_stock: false,
                        images: [],
                        measurements: [],
                        highlights_details: [],
                        description: '',
                        additional_information: ''
                    });
                }
            });
            
            // Display the variants
            if (typeof displayVariants === 'function') {
                displayVariants();
            }
            
            // Show success message
            if (typeof showToast === 'function') {
                showToast('success', `Generated ${combinations.length} variant(s) from selected attribute values.`);
            }
            
            // Persist draft
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
        });
    }

    // OLD: Generate variants (kept for backward compatibility but not used)
    const $generateVariantsBtn = $('#generateVariantsBtn');
    if ($generateVariantsBtn.length) {
        $generateVariantsBtn.on('click', function() {
        console.log('Selected attribute IDs:', selectedAttributeIds);
        console.log('Current attributeValues object:', attributeValues);
        console.log('Current selectedAttributeValues object:', selectedAttributeValues);

        const allAttributeValues = {};
        selectedAttributeIds.forEach(attributeId => {
            const key = String(attributeId);
            const explicitSelections = Array.isArray(selectedAttributeValues[key]) ? selectedAttributeValues[key] : [];
            const storedValues = Array.isArray(attributeValues[key]) ? attributeValues[key] : [];
            const combined = Array.from(new Set([...explicitSelections, ...storedValues]
                .filter(value => value !== null && value !== undefined && String(value).trim() !== '')));

            if (combined.length) {
                allAttributeValues[key] = combined;
            }
        });

        if (Object.keys(allAttributeValues).length === 0) {
            showToast('error', 'Add at least one value for each selected attribute before generating variants.');
            return;
        }

        // Preserve existing variants mapped by attribute combination (or fallback)
        const existingVariantMap = new Map();
        generatedVariants.forEach((variant, index) => {
            const existingAttributes = normalizeAttributesPayload(variant.attributes || {});
            const normalizedVariant = {
                ...variant,
                attributes: existingAttributes,
            };
            const key = getVariantKey(normalizedVariant, selectedAttributeIds);
            const mapKey = key || getVariantFallbackKey(normalizedVariant, index);
            existingVariantMap.set(mapKey, normalizedVariant);
        });

        // Generate all combinations
        const newCombinations = generateCombinations(allAttributeValues);
        if (!newCombinations.length) {
            showToast('error', 'No variant combinations could be generated for the selected attribute values.');
            return;
        }

        newCombinations.forEach(combination => {
            const normalizedCombination = normalizeAttributesPayload(combination);
            const key = getVariantKey({ attributes: normalizedCombination }, selectedAttributeIds);
            if (key && existingVariantMap.has(key)) {
                const existingVariant = existingVariantMap.get(key);
                existingVariant.attributes = normalizeAttributesPayload(existingVariant.attributes || normalizedCombination);
                existingVariantMap.set(key, existingVariant);
                return;
            }

            if (!key) {
                const fallbackKey = getVariantFallbackKey(combination, existingVariantMap.size);
                existingVariantMap.set(fallbackKey, { ...combination });
                return;
            }

            existingVariantMap.set(key, { ...combination });
        });

        generatedVariants = Array.from(existingVariantMap.values());
        displayVariants();
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
        });
    }

    function generateCombinations(attributeValues) {
        const attributes = Object.keys(attributeValues);
        const combinations = [];
        
        function generateRecursive(index, currentCombination) {
            if (index === attributes.length) {
                combinations.push({...currentCombination});
                return;
            }
            
            const attributeId = attributes[index];
            const values = attributeValues[attributeId] || [];
            
            values.forEach(value => {
                currentCombination[attributeId] = value;
                generateRecursive(index + 1, currentCombination);
            });
        }
        
        generateRecursive(0, {});
        return combinations;
    }

    function displayVariants() {
        const $variantsTableBody = $('#variantsTableBody');
        const $variantsTableContainer = $('#variantsTableContainer');
        const $bulkActions = $('#bulkActions');
        
        if (!$variantsTableBody.length) return;
        
        $variantsTableBody.empty();
        
        // Ensure variants table is visible when displaying variants
        if ($variantsTableContainer.length) {
            $variantsTableContainer.css('display', 'block');
        }
        if ($bulkActions.length) {
            $bulkActions.css('display', 'block');
        }
        
        // Update variant images section visibility (will be called after variants are added)
        
        if (!Array.isArray(generatedVariants) || generatedVariants.length === 0) {
            // Default variant creation removed per user request
            return;
        }
        
        generatedVariants.forEach((variant, index) => {
            let variantName;
            let variantSku;
            let variantPrice;
            let variantSalePrice;
            let variantActive;
            let variantDiscountType;
            let variantDiscountValue;
            let variantDiscountActive;
            let variantMeasurements = [];
            let variantAttributes = {};
            let variantImages = [];
            
            // Preserve full image structure (id, path, url) instead of normalizing to strings
            const variantImagesSource = variant.images || [];
            const normalizedImages = Array.isArray(variantImagesSource) 
                ? variantImagesSource.map(img => {
                    // If already in correct format (object with id, path, url), keep it
                    if (typeof img === 'object' && (img.id || img.path || img.url)) {
                        return img;
                    }
                    // If string, convert to object format (but without ID - can't delete these)
                    if (typeof img === 'string') {
                        return { path: img, url: img.startsWith('http') ? img : `/storage/${img.replace(/^\/?storage\//, '')}` };
                    }
                    return img;
                })
                : [];

            let normalizedAttributes = {};

            if (variant.name) {
                normalizedAttributes = normalizeAttributesPayload(variant.attributes || {});
                const attributeDisplayOrder = Object.keys(normalizedAttributes)
                    .sort((a, b) => Number(a) - Number(b))
                    .map(key => normalizedAttributes[key]);
                const fallbackName = attributeDisplayOrder.length ? attributeDisplayOrder.join(' - ') : variant.name;
                const containsObjectPlaceholder = typeof variant.name === 'string' && /\[object Object\]/i.test(variant.name);
                variantName = containsObjectPlaceholder ? fallbackName : variant.name;
                if (containsObjectPlaceholder) {
                    variant.name = variantName;
                }
                // Prepend product name to variant name if not already included
                const productName = $('#productName').val() || '';
                if (productName && !variantName.includes(productName)) {
                    variantName = `${productName} - ${variantName}`;
                }
                // Generate SKU if not already set or is empty
                if (variant.sku && variant.sku.trim() !== '') {
                    variantSku = variant.sku;
                } else {
                    // Generate SKU from variant attributes
                    const variantValues = Object.keys(normalizedAttributes)
                        .sort((a, b) => Number(a) - Number(b))
                        .map(key => normalizedAttributes[key]);
                    const skuSuffix = variantValues.map(value =>
                        value.toString().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3)
                    ).join('-');
                    
                    // Get parent SKU or product name for base
                    const parentSku = $('#productSku').val() || '';
                    const productName = $('#productName').val() || '';
                    const baseSku = parentSku ? parentSku.replace('PRD-', '').split('-')[0] : 
                                  (productName ? productName.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10) : 'VAR');
                    variantSku = `${baseSku}-${index + 1}-${skuSuffix}`;
                }
                variantPrice = variant.price || '';
                variantSalePrice = variant.sale_price || '';
                variantActive = variant.is_active ? '1' : '0';
                variantDiscountType = variant.discount_type || '';
                variantDiscountValue = variant.discount_value || '';
                variantDiscountActive = variant.discount_active ? '1' : '';
                variantMeasurements = extractMeasurementsForVariant(variant);
                variantImages = normalizedImages;
                variantAttributes = normalizedAttributes;
            } else {
                // Only use attributes for variant name, not other variant properties
                const normalizedCombination = normalizeAttributesPayload(variant.attributes || {});
                // Filter to only include actual attribute IDs (not other properties)
                const attributeKeys = Object.keys(normalizedCombination).filter(key => {
                    // Only include keys that are in selectedAttributeIds (actual attribute IDs)
                    return selectedAttributeIds.includes(String(key));
                });
                const variantValues = attributeKeys
                    .sort((a, b) => Number(a) - Number(b))
                    .map(key => normalizedCombination[key]);
                const attributeName = variantValues.length > 0 ? variantValues.join(' - ') : 'Variant';
                // Prepend product name to variant name
                const productName = $('#productName').val() || '';
                variantName = productName ? `${productName} - ${attributeName}` : attributeName;
                
                // Generate SKU if not already set
                if (variant.sku && variant.sku.trim() !== '') {
                    variantSku = variant.sku;
                } else {
                    const skuSuffix = variantValues.map(value =>
                        value.toString().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3)
                    ).join('-');
                    
                    // Get parent SKU or product name for base
                    const parentSku = $('#productSku').val() || '';
                    const productName = $('#productName').val() || '';
                    const baseSku = parentSku ? parentSku.replace('PRD-', '').split('-')[0] : 
                                  (productName ? productName.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10) : 'VAR');
                    variantSku = `${baseSku}-${index + 1}-${skuSuffix}`;
                }
                variantPrice = '';
                variantSalePrice = '';
                variantActive = '1';
                variantDiscountType = '';
                variantDiscountValue = '';
                variantDiscountActive = '';
                variantMeasurements = [];
                variantAttributes = normalizedCombination;
                variantImages = normalizedImages;
            }
            variant.attributes = variantAttributes;
            
            const $row = $('<tr>');
            $row.html(`
                <td>
                    <div class="form-check">
                        <input class="form-check-input variant-checkbox" type="checkbox" value="${index}" id="variant_${index}">
                        <label class="form-check-label" for="variant_${index}">
                            ${variantName}
                        </label>
                    </div>
                </td>
                <td><input type="text" class="form-control form-control-sm variant-field variant-field--sku" name="variants[${index}][sku]" value="${variantSku}" placeholder="Auto-generated"></td>
                <td>
                    <div class="input-group input-group-sm variant-field-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control variant-field variant-field--price" name="variants[${index}][price]" step="0.01" value="${variantPrice}" placeholder="0.00" required>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm variant-field-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control variant-field variant-field--price" name="variants[${index}][sale_price]" step="0.01" value="${variantSalePrice}" placeholder="0.00">
                    </div>
                </td>
                <td data-variant-measurements-cell>
                    <div data-variant-measurements-display></div>
                    <input type="hidden" name="variants[${index}][measurements]" data-variant-measurements-input>
                    <input type="hidden" name="variants[${index}][weight]" data-variant-weight-input>
                    <input type="hidden" name="variants[${index}][length]" data-variant-length-input>
                    <input type="hidden" name="variants[${index}][width]" data-variant-width-input>
                    <input type="hidden" name="variants[${index}][height]" data-variant-height-input>
                    <input type="hidden" name="variants[${index}][diameter]" data-variant-diameter-input>
                    <input type="hidden" name="variants[${index}][id]" data-variant-id-input>
                    <input type="hidden" name="variants[${index}][attributes]" data-variant-attributes-input>
                    <input type="hidden" name="variants[${index}][discount_type]" value="${variantDiscountType || ''}" data-variant-discount-type-input>        
                    <input type="hidden" name="variants[${index}][discount_value]" value="${variantDiscountValue || ''}" data-variant-discount-value-input>
                    <input type="hidden" name="variants[${index}][discount_active]" value="${variantDiscountActive || ''}" data-variant-discount-active-input>
                    <input type="hidden" name="variants[${index}][barcode]" value="${variant.barcode || ''}" data-variant-barcode-input>
                    <input type="hidden" name="variants[${index}][name]" value="${variantName}" data-variant-name-input>
                    <input type="hidden" name="variants[${index}][stock_quantity]" value="${variant.stock_quantity || 0}" data-variant-stock-quantity-input>
                    <input type="hidden" name="variants[${index}][stock_status]" value="${variant.stock_status || 'in_stock'}" data-variant-stock-status-input>
                    <input type="hidden" name="variants[${index}][manage_stock]" value="${variant.manage_stock ? '1' : '0'}" data-variant-manage-stock-input>
                    <input type="hidden" name="variants[${index}][low_stock_threshold]" value="${variant.low_stock_threshold || 0}" data-variant-low-stock-threshold-input>
                    <input type="hidden" name="variants[${index}][highlights_details]" value="${escapeHtmlAttribute(getHighlightsDetailsValue(variant.highlights_details))}" data-variant-highlights-details-input>
                    <input type="hidden" name="variants[${index}][description]" value="${escapeHtmlAttribute(variant.description || '')}" data-variant-description-input>
                    <input type="hidden" name="variants[${index}][additional_information]" value="${escapeHtmlAttribute(variant.additional_information || '')}" data-variant-additional-information-input>
                </td>
                <td>
                    <div class="variant-image-uploader">
                        <div class="d-flex gap-2 align-items-start">
                            <div class="flex-grow-1">
                                <input type="file" class="form-control form-control-sm variant-image-input" name="variants[${index}][images][]" accept="image/*" multiple data-variant-index="${index}">
                                <div class="small text-muted mt-1">Supported: multiple images (jpeg, png, webp)</div>
                            </div>
                            <button type="button" class="btn btn-sm ${normalizedImages && normalizedImages.length > 0 ? 'btn-outline-success' : 'btn-outline-info'} view-variant-images-btn" data-variant-index="${index}" title="View Images" style="flex-shrink: 0;">
                                <i class="fas fa-images"></i>
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1" data-variant-image-preview style="display: none !important;"></div>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input variant-status-toggle" type="checkbox" id="variant_status_${index}" ${variantActive === '1' ? 'checked' : ''}>
                            <label class="form-check-label" for="variant_status_${index}">${variantActive === '1' ? 'Active' : 'Inactive'}</label>
                    </div>
                    <input type="hidden" name="variants[${index}][is_active]" value="${variantActive}" data-variant-status-input>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-primary btn-sm edit-variant-btn" data-index="${index}">
                        <i data-feather="edit"></i>
                    </button>
                </td>
            `);
            
            const datasetPayload = {
                id: variant.id || null,
                name: variantName,
                sku: variantSku,
                price: variantPrice,
                sale_price: variantSalePrice,
                is_active: variantActive === '1',
                discount_type: variantDiscountType,
                discount_value: variantDiscountValue,
                discount_active: variantDiscountActive === '1',
                measurements: variantMeasurements,
                attributes: variantAttributes,
                images: normalizedImages,
                stock_quantity: variant.stock_quantity || 0,
                stock_status: variant.stock_status || 'in_stock',
                manage_stock: variant.manage_stock || false,
                low_stock_threshold: variant.low_stock_threshold || 0,
                highlights_details: variant.highlights_details || null,
                description: variant.description || null,
                additional_information: variant.additional_information || null,
                barcode: variant.barcode || ''
            };
            $row.attr('data-variant-id', datasetPayload.id ? String(datasetPayload.id) : '');
            $row.attr('data-variant-index', String(index));
            $row.attr('data-variant-data', JSON.stringify(datasetPayload));
            const $idHidden = $row.find('[data-variant-id-input]');
            if ($idHidden.length) {
                $idHidden.val(datasetPayload.id ? datasetPayload.id : '');
            }
            const $attributesHidden = $row.find('[data-variant-attributes-input]');
            if ($attributesHidden.length) {
                $attributesHidden.val(JSON.stringify(datasetPayload.attributes || {}));
            }
            $row.attr('data-existing-images', JSON.stringify(normalizedImages));
            $variantsTableBody.append($row);
            const rowElement = $row[0];
            const measurementsForRow = datasetPayload.measurements && datasetPayload.measurements.length
                ? datasetPayload.measurements
                : parseMeasurementsFromRow(rowElement);
            updateRowMeasurements(rowElement, measurementsForRow);
            extractVariantRowData(rowElement);
 
            const $imageInput = $row.find('.variant-image-input');
            const $previewEl = $row.find('[data-variant-image-preview]');
            const existingImages = extractExistingImagesFromRow(rowElement);
            const imageInputElement = $imageInput[0];
            updateVariantImagePreview($previewEl[0], imageInputElement && imageInputElement.files && imageInputElement.files.length ? imageInputElement.files : null, existingImages);
            
            // Update button class based on whether images exist
            const $viewBtn = $row.find('.view-variant-images-btn');
            if ($viewBtn.length) {
                const hasFiles = imageInputElement && imageInputElement.files && imageInputElement.files.length > 0;
                const hasExistingImages = Array.isArray(existingImages) && existingImages.length > 0;
                const hasImages = hasFiles || hasExistingImages;
                
                if (hasImages) {
                    $viewBtn.removeClass('btn-outline-info').addClass('btn-outline-success');
                } else {
                    $viewBtn.removeClass('btn-outline-success').addClass('btn-outline-info');
                }
            }
        });
        
        if (generatedVariants.length) {
                $variantsTableContainer.css('display', 'block');
                $bulkActions.css('display', 'block');
        } else {
            // Check if no attributes are selected - hide table if no variants
            const selectedIds = getSelectedAttributeIds();
            if (selectedIds.length === 0) {
                // No attributes selected - hide table (default variant creation removed)
                $variantsTableContainer.css('display', 'none');
            } else {
                // Attributes selected but no variants - hide table until variants are created
                $variantsTableContainer.css('display', 'none');
                $bulkActions.css('display', 'none');
            }
        }
        
        // Update variant images section visibility
        if (window.updateVariantImagesVisibility) {
            window.updateVariantImagesVisibility();
        }
        
        // Populate variant images container
        if (window.populateVariantImagesContainer) {
            setTimeout(function() {
                window.populateVariantImagesContainer();
            }, 200);
        }
        
        refreshFeatherIcons();
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    }

    // Edit variant functionality
    $(variantsTableBody).on('click', '.edit-variant-btn', function(e) {
        if (!$variantEditModalElement.length || !variantEditModal) {
            console.error('Variant edit offcanvas is not available in the DOM.');
            return;
        }

        const $btn = $(this);
        const $row = $btn.closest('tr');
        const index = $btn.data('index');
        
        // Populate modal with current values
        $('#variantSku').val($row.find('[name*="[sku]"]').val());
        $('#variantPrice').val($row.find('[name*="[price]"]').val());
        const $salePriceField = $row.find('[name*="[sale_price]"]');
        if ($salePriceField.length && $variantSellPriceInput.length) $variantSellPriceInput.val($salePriceField.val());
        
        // Populate variant name
        const $variantNameLabel = $row.find('.form-check-label');
        const $variantNameInput = $('#variantName');
        const $variantNameHidden = $row.find('[data-variant-name-input]');
        if ($variantNameInput.length) {
            if ($variantNameHidden.length && $variantNameHidden.val()) {
                $variantNameInput.val($variantNameHidden.val());
            } else if ($variantNameLabel.length) {
                $variantNameInput.val($variantNameLabel.text().trim());
            }
        }
        
        // Populate barcode
        const $barcodeHidden = $row.find('[data-variant-barcode-input]');
        const $barcodeInput = $('#variantBarcode');
        if ($barcodeInput.length && $barcodeHidden.length) {
            $barcodeInput.val($barcodeHidden.val() || '');
        }
        
        const $statusHiddenInput = $row.find('[data-variant-status-input]');
        if ($variantStatusToggle.length) {
            const isActive = $statusHiddenInput.length ? $statusHiddenInput.val() === '1' : true;
            $variantStatusToggle.prop('checked', isActive);
            updateVariantStatusToggleLabel();
        }
        
        // Populate inventory fields
        // Note: Stock Quantity and Stock Status are managed from Inventory module only
        const $lowStockThresholdInput = $('#variantLowStockThreshold');
        
        const $stockQuantityHidden = $row.find('[data-variant-stock-quantity-input]');
        const $stockStatusHidden = $row.find('[data-variant-stock-status-input]');
        const $lowStockThresholdHidden = $row.find('[data-variant-low-stock-threshold-input]');
        const $manageStockHidden = $row.find('[data-variant-manage-stock-input]');
        
        // Stock quantity and status are read-only (managed from Inventory module)
        // Keep existing values from hidden inputs for form submission
        
        if ($lowStockThresholdInput.length && $lowStockThresholdHidden.length) {
            $lowStockThresholdInput.val($lowStockThresholdHidden.val() || '0');
        }
        
        // Populate other fields first
        const $discountTypeHidden = $row.find('[data-variant-discount-type-input]');
        const $discountValueHidden = $row.find('[data-variant-discount-value-input]');
        const $discountActiveHidden = $row.find('[data-variant-discount-active-input]');

        if ($discountTypeHidden.length && $discountTypeSelect.length) {
            $discountTypeSelect.val($discountTypeHidden.val() || '');
        }
        if ($discountValueHidden.length && $discountValueInput.length) {
            $discountValueInput.val($discountValueHidden.val() || '');
            if (!$discountTypeSelect.val()) {
                $discountValueInput.prop('disabled', true);
            } else {
                $discountValueInput.prop('disabled', false);
            }
        }
        if ($discountActiveHidden.length && $discountActiveSelect.length) {
            $discountActiveSelect.val($discountActiveHidden.val() === '1' ? '1' : '0');
        }

        if ($variantSellPriceInput.length) {
            $variantSellPriceInput.data('originalSellPrice', $variantSellPriceInput.val() || '');
        }
 
        activeVariantRow = $row[0];
        syncModalImagePreviewFromRow($row[0]);
        handleDiscountStateChange();
        calculateVariantSellPrice();
        
        // Load highlights & details
        loadHighlightsDetailsFromRow($row[0]);
        
        // Load description and additional information
        const variantDataStr = $row.data('variantData');
        if (variantDataStr) {
            try {
                const variantData = typeof variantDataStr === 'string' ? JSON.parse(variantDataStr) : variantDataStr;
                const $descriptionTextarea = $('#variantDescription');
                const $additionalInfoTextarea = $('#variantAdditionalInfo');
                
                if ($descriptionTextarea.length) {
                    $descriptionTextarea.val(variantData.description || '');
                }
                
                if ($additionalInfoTextarea.length) {
                    $additionalInfoTextarea.val(variantData.additional_information || '');
                }
                
                // Set editor content after editors are initialized (will be called after modal is shown)
                setTimeout(function() {
                    if (variantDataStr) {
                        try {
                            const variantData = typeof variantDataStr === 'string' ? JSON.parse(variantDataStr) : variantDataStr;
                            if (window.variantDescriptionEditor && typeof window.variantDescriptionEditor.setHTMLCode === 'function') {
                                window.variantDescriptionEditor.setHTMLCode(variantData.description || '');
                            }
                            if (window.variantAdditionalInfoEditor && typeof window.variantAdditionalInfoEditor.setHTMLCode === 'function') {
                                window.variantAdditionalInfoEditor.setHTMLCode(variantData.additional_information || '');
                            }
                        } catch (e) {
                            console.error('Error setting editor content:', e);
                        }
                    }
                }, 300);
            } catch (e) {
                console.error('Error loading variant description/additional info:', e);
            }
        }
        
        // Parse measurements from row
        const measurementsForModal = parseMeasurementsFromRow($row[0]);
        
        // Ensure units are loaded before rendering measurements and showing modal
        if (!unitsLoaded && !unitsLoading) {
            loadUnitsFromModule(function() {
                // Render measurements after units are loaded
                renderMeasurementsInModal(measurementsForModal);
                updateMeasurementEmptyState();
                variantEditModal.show();
            // Set editor data after modal is shown and editors are initialized
            setTimeout(function() {
                if (variantDataStr) {
                    try {
                        const variantData = typeof variantDataStr === 'string' ? JSON.parse(variantDataStr) : variantDataStr;
                        if (window.variantDescriptionEditor && typeof window.variantDescriptionEditor.setHTMLCode === 'function') {
                            window.variantDescriptionEditor.setHTMLCode(variantData.description || '');
                        }
                        if (window.variantAdditionalInfoEditor && typeof window.variantAdditionalInfoEditor.setHTMLCode === 'function') {
                            window.variantAdditionalInfoEditor.setHTMLCode(variantData.additional_information || '');
                        }
                    } catch (e) {
                        console.error('Error setting editor data:', e);
                    }
                }
            }, 300);
            });
        } else if (unitsLoaded) {
            // Units already loaded, render measurements and show modal
            renderMeasurementsInModal(measurementsForModal);
            updateMeasurementEmptyState();
            variantEditModal.show();
            // Set editor data after offcanvas is shown (editors are initialized on show.bs.offcanvas)
            setTimeout(function() {
                if (variantDataStr) {
                    try {
                        const variantData = typeof variantDataStr === 'string' ? JSON.parse(variantDataStr) : variantDataStr;
                        if (window.variantDescriptionEditor && typeof window.variantDescriptionEditor.setHTMLCode === 'function') {
                            window.variantDescriptionEditor.setHTMLCode(variantData.description || '');
                        }
                        if (window.variantAdditionalInfoEditor && typeof window.variantAdditionalInfoEditor.setHTMLCode === 'function') {
                            window.variantAdditionalInfoEditor.setHTMLCode(variantData.additional_information || '');
                        }
                    } catch (e) {
                        console.error('Error setting editor data:', e);
                    }
                }
            }, 100);
        } else {
            // Units are loading, wait for them
            const checkInterval = setInterval(() => {
                if (!unitsLoading && unitsLoaded) {
                    clearInterval(checkInterval);
                    renderMeasurementsInModal(measurementsForModal);
                    updateMeasurementEmptyState();
                    variantEditModal.show();
                }
            }, 100);
        }
    });
    
    // Load units when variant edit offcanvas is shown (in case they weren't loaded yet)
    if ($variantEditModalElement.length) {
        $variantEditModalElement.on('shown.bs.offcanvas', function() {
            if (!unitsLoaded && !unitsLoading) {
                loadUnitsFromModule(function() {
                    // Refresh unit dropdowns in measurement rows after units are loaded
                    if ($measurementRowsContainer.length) {
                        const $unitSelects = $measurementRowsContainer.find('.measurement-unit');
                        $unitSelects.each(function() {
                            const $select = $(this);
                            const currentValue = $select.val();
                            $select.html(buildUnitOptionsHtml(currentValue));
                            $select.val(currentValue);
                        });
                    }
                    updateMeasurementEmptyState();
                });
            } else if (unitsLoaded) {
                // Refresh unit dropdowns in case units were updated
                if ($measurementRowsContainer.length) {
                    const $unitSelects = $measurementRowsContainer.find('.measurement-unit');
                    $unitSelects.each(function() {
                        const $select = $(this);
                        const currentValue = $select.val();
                        $select.html(buildUnitOptionsHtml(currentValue));
                        $select.val(currentValue);
                    });
                }
                updateMeasurementEmptyState();
            }
        });
    }

    // Handle View Images button click
    $(variantsTableBody).on('click', '.view-variant-images-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const variantIndex = $btn.data('variant-index');
        const $row = $btn.closest('tr');
        
        // Get images from the row
        const $imageInput = $row.find('.variant-image-input');
        const existingImages = extractExistingImagesFromRow($row[0]);
        const newFiles = $imageInput.length && $imageInput[0].files && $imageInput[0].files.length ? Array.from($imageInput[0].files) : [];
        
        // Collect all images (existing + new)
        const allImages = [];
        
        // Add existing images
        if (Array.isArray(existingImages) && existingImages.length > 0) {
            existingImages.forEach(img => {
                // Handle both old format (string path) and new format (object with id, path, url)
                if (typeof img === 'string') {
                    // Old format: just a path string
                    const normalizedPath = img.startsWith('http')
                        ? img
                        : `/storage/${img.replace(/^\/?storage\//, '')}`;
                    allImages.push({
                        src: normalizedPath,
                        type: 'existing',
                        path: img
                    });
                } else if (img && (img.path || img.url)) {
                    // New format: object with id, path, url
                    allImages.push({
                        id: img.id,
                        src: img.url || (img.path.startsWith('http') ? img.path : `/storage/${img.path.replace(/^\/?storage\//, '')}`),
                        path: img.path,
                        type: 'existing'
                    });
                }
            });
        }
        
        // Add new files with index for removal
        newFiles.forEach((file, fileIndex) => {
            allImages.push({
                src: URL.createObjectURL(file),
                type: 'new',
                name: file.name,
                fileIndex: fileIndex,
                file: file // Store file reference for removal
            });
        });
        
        // Display images in modal
        const $container = $('#variantImagesViewContainer');
        const $emptyMessage = $('#variantImagesViewEmpty');
        
        if (allImages.length === 0) {
            $container.empty();
            $container.css('display', 'none');
            $emptyMessage.css('display', 'block');
        } else {
            $container.empty();
            $container.css('display', 'flex');
            $emptyMessage.css('display', 'none');
            
            allImages.forEach((image, index) => {
                const $col = $('<div>').addClass('col-md-4 col-sm-6');
                
                const $card = $('<div>').addClass('card');
                
                const $img = $('<img>')
                    .attr('src', image.src)
                    .addClass('card-img-top')
                    .css({
                        'height': '200px',
                        'object-fit': 'cover',
                        'cursor': 'pointer'
                    })
                    .attr('alt', image.name || `Variant image ${index + 1}`)
                    .on('click', function() {
                        window.open(image.src, '_blank');
                    });
                
                const $cardBody = $('<div>').addClass('card-body p-2');
                
                let bodyContent = `
                    <small class="text-muted d-block text-truncate" title="${image.name || 'Image'}">
                        ${image.name || `Image ${index + 1}`}
                    </small>
                    <span class="badge bg-${image.type === 'existing' ? 'success' : 'info'} badge-sm mb-2">${image.type === 'existing' ? 'Existing' : 'New'}</span>
                `;
                
                // Add delete button for both existing and new images
                // For existing images: show delete if we have an ID (database image)
                if (image.type === 'existing') {
                    if (image.id) {
                        // Delete button for existing images (from database with ID)
                        bodyContent += `
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-variant-image-btn" 
                                    data-image-id="${image.id}" 
                                    data-variant-index="${variantIndex}"
                                    data-image-type="existing"
                                    title="Delete this image">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        `;
                    }
                    // If no ID (old format string), we can't delete individually - no button
                } else if (image.type === 'new') {
                    // Remove button for newly selected images (from file input)
                    if (image.fileIndex !== undefined && image.fileIndex !== null) {
                        bodyContent += `
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-new-variant-image-btn" 
                                    data-file-index="${image.fileIndex}" 
                                    data-variant-index="${variantIndex}"
                                    data-image-type="new"
                                    title="Remove this image">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </button>
                        `;
                    }
                }
                
                $cardBody.html(bodyContent);
                
                $card.append($img);
                $card.append($cardBody);
                $col.append($card);
                $container.append($col);
            });
        }
        
        // Show modal
        const $modalElement = $('#variantImagesViewModal');
        const modal = new bootstrap.Modal($modalElement[0]);
        modal.show();
        
        // Clean up object URLs when modal is closed
        $modalElement.one('hidden.bs.modal', function() {
            allImages.forEach(image => {
                if (image.type === 'new' && image.src.startsWith('blob:')) {
                    URL.revokeObjectURL(image.src);
                }
            });
        });
    });
    
    // Handle delete variant image button click
    $(document).on('click', '.delete-variant-image-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
            return;
        }
        
        const btn = $(this);
        const imageId = btn.data('image-id');
        const variantIndex = btn.data('variant-index');
        const card = btn.closest('.col-md-4, .col-sm-6');
        
        // Disable button and show loading
        btn.prop('disabled', true);
        btn.html("<i class=\"fas fa-spinner fa-spin me-1\"></i> Deleting...");
        
        $.ajax({
            url: "{{ route('products.deleteVariantImage') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                image_id: imageId
            },
            success: function(response) {
                if (response.success) {
                    // Remove the card from modal
                    card.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update row's existing images data
                        const row = document.querySelector(`tr[data-variant-index="${variantIndex}"]`);
                        if (row) {
                            const existingImages = extractExistingImagesFromRow(row);
                            const updatedImages = existingImages.filter(img => {
                                // Keep old format (string paths) - we can't delete those individually
                                if (typeof img === 'string') {
                                    return true;
                                }
                                // For new format (objects), filter out the deleted image
                                return !img.id || img.id !== imageId;
                            });
                            row.dataset.existingImages = JSON.stringify(updatedImages);
                            
                            // Update preview if visible
                            const previewEl = row.querySelector('[data-variant-image-preview]');
                            if (previewEl) {
                                const imageInput = row.querySelector('.variant-image-input');
                                updateVariantImagePreview(previewEl, imageInput && imageInput.files && imageInput.files.length ? imageInput.files : null, updatedImages);
                            }
                            
                            // Update view button state
                            const viewBtn = row.querySelector('.view-variant-images-btn');
                            if (viewBtn) {
                                const hasFiles = imageInput && imageInput.files && imageInput.files.length > 0;
                                const hasExistingImages = Array.isArray(updatedImages) && updatedImages.length > 0;
                                const hasImages = hasFiles || hasExistingImages;
                                
                                if (hasImages) {
                                    viewBtn.classList.remove('btn-outline-info');
                                    viewBtn.classList.add('btn-outline-success');
                                } else {
                                    viewBtn.classList.remove('btn-outline-success');
                                    viewBtn.classList.add('btn-outline-info');
                                }
                            }
                        }
                        
                        // Check if modal is now empty
                        const container = document.getElementById('variantImagesViewContainer');
                        const remainingCards = container.querySelectorAll('.col-md-4, .col-sm-6');
                        const emptyMessage = document.getElementById('variantImagesViewEmpty');
                        
                        if (remainingCards.length === 0) {
                            container.style.display = 'none';
                            if (emptyMessage) {
                                emptyMessage.style.display = 'block';
                            }
                        }
                    });
                    
                    if (typeof showToast === 'function') {
                        showToast('success', response.message || 'Image deleted successfully');
                    }
                } else {
                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-trash-alt me-1"></i> Delete');
                    if (typeof showToast === 'function') {
                        showToast('error', response.message || 'Failed to delete image');
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-trash-alt me-1"></i> Delete');
                const errorMsg = xhr.responseJSON?.message || 'Failed to delete image';
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                }
            }
        });
    });
    
    // Handle remove newly selected variant image button click
    $(document).on('click', '.remove-new-variant-image-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to remove this image? It will not be uploaded.')) {
            return;
        }
        
        const btn = $(this);
        const fileIndex = parseInt(btn.data('file-index'));
        const variantIndex = btn.data('variant-index');
        const card = btn.closest('.col-md-4, .col-sm-6');
        const row = document.querySelector(`tr[data-variant-index="${variantIndex}"]`);
        
        if (!row) {
            return;
        }
        
        const imageInput = row.querySelector('.variant-image-input');
        if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
            // If no files, just remove the card
            card.fadeOut(300, function() {
                $(this).remove();
            });
            return;
        }
        
        // Create a new FileList without the removed file
        const dt = new DataTransfer();
        const files = Array.from(imageInput.files);
        
        files.forEach((file, index) => {
            if (index !== fileIndex) {
                dt.items.add(file);
            }
        });
        
        // Update the file input
        imageInput.files = dt.files;
        
        // Revoke the object URL to free memory
        const img = card.querySelector('img');
        if (img && img.src.startsWith('blob:')) {
            URL.revokeObjectURL(img.src);
        }
        
        // Remove the card from modal
        card.fadeOut(300, function() {
            $(this).remove();
            
            // Update preview
            const previewEl = row.querySelector('[data-variant-image-preview]');
            if (previewEl) {
                const existingImages = extractExistingImagesFromRow(row);
                updateVariantImagePreview(previewEl, imageInput.files && imageInput.files.length ? imageInput.files : null, existingImages);
            }
            
            // Update view button state
            const viewBtn = row.querySelector('.view-variant-images-btn');
            if (viewBtn) {
                const hasFiles = imageInput.files && imageInput.files.length > 0;
                const existingImages = extractExistingImagesFromRow(row);
                const hasExistingImages = Array.isArray(existingImages) && existingImages.length > 0;
                const hasImages = hasFiles || hasExistingImages;
                
                if (hasImages) {
                    viewBtn.classList.remove('btn-outline-info');
                    viewBtn.classList.add('btn-outline-success');
                } else {
                    viewBtn.classList.remove('btn-outline-success');
                    viewBtn.classList.add('btn-outline-info');
                }
            }
            
            // Trigger change event to update any listeners
            const changeEvent = new Event('change', { bubbles: true });
            imageInput.dispatchEvent(changeEvent);
            
            // Check if modal is still open and refresh it
            const modal = document.getElementById('variantImagesViewModal');
            const isModalOpen = modal && modal.classList.contains('show');
            
            if (isModalOpen) {
                // Modal is still open, refresh the display by re-triggering view images
                const viewBtn = row.querySelector('.view-variant-images-btn');
                if (viewBtn) {
                    // Small delay to ensure file input is updated
                    setTimeout(function() {
                        viewBtn.click();
                    }, 150);
                }
            } else {
                // Modal is closed, just check if empty
                const container = document.getElementById('variantImagesViewContainer');
                const remainingCards = container.querySelectorAll('.col-md-4, .col-sm-6');
                const emptyMessage = document.getElementById('variantImagesViewEmpty');
                
                if (remainingCards.length === 0) {
                    container.style.display = 'none';
                    if (emptyMessage) {
                        emptyMessage.style.display = 'block';
                    }
                }
            }
        });
        
        if (typeof showToast === 'function') {
            showToast('success', 'Image removed from selection');
        }
    });
    
    $(variantsTableBody).on('change', function(e) {
        const target = e.target;
        if (target.classList.contains('variant-status-toggle')) {
            updateVariantRowStatus(target);
        }

        if (target.classList.contains('variant-image-input')) {
            const row = target.closest('tr');
            if (!row) {
                return;
            }
            row.dataset.existingImages = '[]';
            const previewEl = row.querySelector('[data-variant-image-preview]');
            updateVariantImagePreview(previewEl, target.files, []);
            if (activeVariantRow === row) {
                syncModalImagePreview(target.files, []);
            }
            
            // Update button class based on whether images exist
            const viewBtn = row.querySelector('.view-variant-images-btn');
            if (viewBtn) {
                const hasFiles = target.files && target.files.length > 0;
                const existingImages = extractExistingImagesFromRow(row);
                const hasExistingImages = Array.isArray(existingImages) && existingImages.length > 0;
                const hasImages = hasFiles || hasExistingImages;
                
                if (hasImages) {
                    viewBtn.classList.remove('btn-outline-info');
                    viewBtn.classList.add('btn-outline-success');
                } else {
                    viewBtn.classList.remove('btn-outline-success');
                    viewBtn.classList.add('btn-outline-info');
                }
            }
            
            // Update variant images container in images section
            if (window.populateVariantImagesContainer) {
                setTimeout(function() {
                    window.populateVariantImagesContainer();
                }, 200);
            }
        }
    });

    // Extract all variants from the table
    function extractAllVariantsFromTable() {
        const $rows = $(variantsTableBody).find('tr[data-variant-index]');
        const variants = [];
        
        rows.forEach((row, index) => {
            const variantData = extractVariantRowData(row);
            if (variantData) {
                variants.push(variantData);
            }
        });
        
        return variants;
    }

    // Handle variants form submission (for standalone variants page)
    let formSubmissionHandlerAttached = false;
    let isSubmittingVariants = false;
    
    // Make function available globally
    window.formSubmissionHandlerAttached = false;
    window.setupVariantsFormSubmission = function() {
        const variantsForm = document.getElementById('variantsForm');
        if (!variantsForm) {
            console.log('Variants form not found');
            return;
        }
        
        if (formSubmissionHandlerAttached || window.formSubmissionHandlerAttached) {
            console.log('Form submission handler already attached');
            return;
        }
        
        console.log('Setting up variants form submission handler');
        formSubmissionHandlerAttached = true;
        window.formSubmissionHandlerAttached = true;
        
        // Define the submission function first (before it's used)
        window.submitVariantsFormData = function() {
            if (isSubmittingVariants) {
                console.log('Already submitting, preventing duplicate submission');
                return;
            }
            
            console.log('Submitting variants form data - starting');
            isSubmittingVariants = true;
            
            const variantsForm = document.getElementById('variantsForm');
            if (!variantsForm) {
                console.error('Variants form not found');
                isSubmittingVariants = false;
                return;
            }
            
            const formData = new FormData();
            const rows = variantsTableBody ? variantsTableBody.querySelectorAll('tr[data-variant-index]') : [];
            
            console.log('Found variant rows:', rows.length);
            
            if (rows.length === 0) {
                showToast('error', 'No variants to save. Please add at least one variant.');
                return;
            }
            
            // Extract variant data from each row
            rows.forEach((row, index) => {
                // Get all hidden inputs from the row
                const hiddenInputs = row.querySelectorAll('input[type="hidden"]');
                console.log(`Row ${index} has ${hiddenInputs.length} hidden inputs`);
                
                hiddenInputs.forEach(input => {
                    const name = input.name || input.getAttribute('name');
                    const dataAttr = input.getAttribute('data-variant-id-input') || 
                                   input.getAttribute('data-variant-sku-input') ||
                                   input.getAttribute('data-variant-price-input') ||
                                   input.getAttribute('data-variant-attributes-input') ||
                                   input.getAttribute('data-variant-measurements-input') ||
                                   input.getAttribute('data-variant-highlights-details-input') ||
                                   input.getAttribute('data-variant-description-input') ||
                                   input.getAttribute('data-variant-additional-information-input');
                    
                    let fieldName = null;
                    let value = input.value;
                    
                    // Try to get field name from data attribute
                    if (dataAttr) {
                        if (dataAttr.includes('id')) fieldName = 'id';
                        else if (dataAttr.includes('sku')) fieldName = 'sku';
                        else if (dataAttr.includes('price')) fieldName = 'price';
                        else if (dataAttr.includes('attributes')) fieldName = 'attributes';
                        else if (dataAttr.includes('measurements')) fieldName = 'measurements';
                        else if (dataAttr.includes('highlights-details')) fieldName = 'highlights_details';
                        else if (dataAttr.includes('description')) fieldName = 'description';
                        else if (dataAttr.includes('additional-information')) fieldName = 'additional_information';
                    }
                    
                    // If no field name from data attr, try to parse from name attribute
                    if (!fieldName && name && name.startsWith('variants[')) {
                        const fieldMatch = name.match(/variants\[\d+\]\[(.+)\]/);
                        if (fieldMatch) {
                            fieldName = fieldMatch[1];
                        }
                    }
                    
                    if (fieldName && value !== null && value !== undefined) {
                        // Handle JSON fields
                        if (fieldName === 'attributes' || fieldName === 'measurements' || fieldName === 'highlights_details') {
                            if (value && value !== '[]' && value !== '{}' && value.trim() !== '') {
                                formData.append(`variants[${index}][${fieldName}]`, value);
                            }
                        } else if (value !== '' && value !== null) {
                            formData.append(`variants[${index}][${fieldName}]`, value);
                        }
                    }
                });
                
                // Also extract from visible inputs
                const skuInput = row.querySelector('[name*="[sku]"]');
                const priceInput = row.querySelector('[name*="[price]"]');
                const salePriceInput = row.querySelector('[name*="[sale_price]"]');
                
                if (skuInput && skuInput.value) {
                    formData.append(`variants[${index}][sku]`, skuInput.value);
                }
                if (priceInput && priceInput.value) {
                    formData.append(`variants[${index}][price]`, priceInput.value);
                }
                if (salePriceInput && salePriceInput.value) {
                    formData.append(`variants[${index}][sale_price]`, salePriceInput.value);
                }
                
                // Handle image files
                const imageInput = row.querySelector('.variant-image-input');
                if (imageInput && imageInput.files && imageInput.files.length > 0) {
                    Array.from(imageInput.files).forEach((file, imgIndex) => {
                        formData.append(`variants[${index}][images][${imgIndex}]`, file);
                    });
                }
            });
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            } else {
                // Try to get from form
                const formToken = variantsForm.querySelector('input[name="_token"]');
                if (formToken) {
                    formData.append('_token', formToken.value);
                }
            }
            
            // Show loading state
            const $saveBtn = $('#saveVariantsBtn');
            const originalText = $saveBtn.length ? $saveBtn.html() : '';
            if ($saveBtn.length) {
                $saveBtn.prop('disabled', true);
                $saveBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            }
            
            console.log('Submitting form to:', variantsForm.action);
            
            // Submit form
            $.ajax({
                url: variantsForm.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    console.log('Response data:', data);
                    isSubmittingVariants = false;
                    
                    if (data && data.success) {
                        showToast('success', data.message || 'Variants saved successfully');
                        // Redirect after a short delay to show the success message
                        setTimeout(() => {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Error saving variants');
                        if (data.errors) {
                            console.error('Validation errors:', data.errors);
                        }
                        if ($saveBtn.length) {
                            $saveBtn.prop('disabled', false);
                            $saveBtn.html(originalText);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    isSubmittingVariants = false;
                    
                    let errorMessage = 'An error occurred while saving variants: ' + (error || 'Unknown error');
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Use default error message
                    }
                    
                    // Only show error if it's not a navigation error
                    if (error !== 'abort') {
                        showToast('error', errorMessage);
                    }
                    
                    if ($saveBtn.length) {
                        $saveBtn.prop('disabled', false);
                        $saveBtn.html(originalText);
                    }
                }
            });
        };
    }
    
    // Setup form submission when DOM is ready
    function initVariantsForm() {
        console.log('Initializing variants form submission...');
        setupVariantsFormSubmission();
    }
    
    // Use multiple strategies to ensure the handler is attached
    function attachVariantsFormHandler() {
        const saveBtn = document.getElementById('saveVariantsBtn');
        const variantsForm = document.getElementById('variantsForm');
        
        console.log('Checking for save button and form:', {
            saveBtn: !!saveBtn,
            variantsForm: !!variantsForm,
            saveBtnId: saveBtn ? saveBtn.id : 'not found',
            formId: variantsForm ? variantsForm.id : 'not found'
        });
        
        if (variantsForm) {
            setupVariantsFormSubmission();
        }
        
        // Direct button click handler as primary method
        if (saveBtn && !saveBtn.hasAttribute('data-handler-attached')) {
            console.log('Attaching direct click handler to save button');
            saveBtn.setAttribute('data-handler-attached', 'true');
            
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Save button clicked directly!');
                
                if (!variantsForm) {
                    console.error('Variants form not found!');
                    alert('Error: Form not found. Please refresh the page.');
                    return;
                }
                
                // Trigger form submit
                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                variantsForm.dispatchEvent(submitEvent);
            });
        }
    }
    
    // Try multiple times to ensure elements exist
    if (document.readyState === 'loading') {
        $(document).ready(function() {
            attachVariantsFormHandler();
        });
    } else {
        attachVariantsFormHandler();
    }
    
    // Also try after a delay to catch late-loading elements
    setTimeout(attachVariantsFormHandler, 500);
    setTimeout(attachVariantsFormHandler, 1000);

    // Save variant changes (in offcanvas)
    const $saveVariantBtn = $('#saveVariantBtn');
    if ($saveVariantBtn.length) {
        $saveVariantBtn.on('click', function() {
            if (!$variantEditModalElement.length || !variantEditModal) {
                console.error('Variant edit offcanvas is not available in the DOM.');
                return;
            }

            if (!activeVariantRow) {
                alert('Error: Row not found');
                return;
            }

            const row = activeVariantRow;

            const skuInput = row.querySelector('[name*="[sku]"]');
            const priceInput = row.querySelector('[name*="[price]"]');
            const salePriceField = row.querySelector('[name*="[sale_price]"]');
            const statusHiddenInput = row.querySelector('[data-variant-status-input]');
            const statusToggle = row.querySelector('.variant-status-toggle');

            if (skuInput) skuInput.value = document.getElementById('variantSku').value;
            if (priceInput) priceInput.value = document.getElementById('variantPrice').value;
            if (salePriceField && $variantSellPriceInput.length) {
                salePriceField.value = $variantSellPriceInput.val();
            }
            if (statusHiddenInput) {
                statusHiddenInput.value = variantStatusToggle && variantStatusToggle.checked ? '1' : '0';
            }
            if (statusToggle) {
                statusToggle.checked = variantStatusToggle && variantStatusToggle.checked;
                updateVariantRowStatus(statusToggle);
            }

            const discountTypeHidden = row.querySelector('[data-variant-discount-type-input]');
            const discountValueHidden = row.querySelector('[data-variant-discount-value-input]');
            const discountActiveHidden = row.querySelector('[data-variant-discount-active-input]');

            if (discountTypeHidden && $discountTypeSelect.length) {
                discountTypeHidden.value = $discountTypeSelect.val();
            }
            if (discountValueHidden && $discountValueInput.length) {
                discountValueHidden.value = $discountValueInput.val();
            }
            if (discountActiveHidden && $discountActiveSelect.length) {
                discountActiveHidden.value = $discountActiveSelect.val() === '1' ? '1' : '0';
            }

            // Save variant name and barcode
            const variantNameHidden = row.querySelector('[data-variant-name-input]');
            const variantNameInput = document.getElementById('variantName');
            const variantNameLabel = row.querySelector('.form-check-label');
            if (variantNameHidden && variantNameInput) {
                variantNameHidden.value = variantNameInput.value || variantNameLabel?.textContent.trim() || '';
                // Update the label in the table as well
                if (variantNameLabel) {
                    variantNameLabel.textContent = variantNameInput.value || variantNameLabel.textContent.trim();
                }
            }
            
            const barcodeHidden = row.querySelector('[data-variant-barcode-input]');
            const barcodeInput = document.getElementById('variantBarcode');
            if (barcodeHidden && barcodeInput) {
                barcodeHidden.value = barcodeInput.value || '';
            }

            // Save inventory fields
            // Note: Stock Quantity and Stock Status are managed from Inventory module only
            // Keep existing values (don't update from form)
            const stockQuantityHidden = row.querySelector('[data-variant-stock-quantity-input]');
            const stockStatusHidden = row.querySelector('[data-variant-stock-status-input]');
            const lowStockThresholdHidden = row.querySelector('[data-variant-low-stock-threshold-input]');
            const manageStockHidden = row.querySelector('[data-variant-manage-stock-input]');
            
            const lowStockThresholdInput = document.getElementById('variantLowStockThreshold');
            
            // Stock quantity and status remain unchanged (managed from Inventory module)
            // Only update if hidden inputs don't exist (new variant)
            if (stockQuantityHidden && !stockQuantityHidden.value) {
                stockQuantityHidden.value = '0'; // Default for new variants
            }
            if (stockStatusHidden && !stockStatusHidden.value) {
                stockStatusHidden.value = 'in_stock'; // Default for new variants
            }
            
            if (lowStockThresholdHidden && lowStockThresholdInput) {
                lowStockThresholdHidden.value = lowStockThresholdInput.value || '0';
            }

            const measurements = collectMeasurementsFromModal();
            updateRowMeasurements(row, measurements);
            
            // Sync images from modal to row before extracting data
            const modalImageInput = document.getElementById('variantImagesPreview') ? 
                document.querySelector('#variantImagesPreview').closest('.modal-body')?.querySelector('input[type="file"]') : null;
            const rowImageInput = row.querySelector('.variant-image-input');
            
            // If images were selected in modal but not yet in row, sync them
            if (variantImagesPreview && rowImageInput) {
                // Check if modal preview has images that aren't in row yet
                const modalPreviewImgs = variantImagesPreview.querySelectorAll('img');
                const rowPreviewEl = row.querySelector('[data-variant-image-preview]');
                
                // Images should already be in rowImageInput if selected via modal button
                // But ensure preview is synced
                if (rowPreviewEl) {
                    const existingImages = extractExistingImagesFromRow(row);
                    updateVariantImagePreview(rowPreviewEl, rowImageInput.files, existingImages);
                }
            }
            
            // Save description and additional information
            const descriptionTextarea = document.getElementById('variantDescription');
            const additionalInfoTextarea = document.getElementById('variantAdditionalInfo');
            if (window.variantDescriptionEditor && typeof window.variantDescriptionEditor.getHTMLCode === 'function' && descriptionTextarea) {
                descriptionTextarea.value = window.variantDescriptionEditor.getHTMLCode();
            } else if (descriptionTextarea) {
                // Fallback: use textarea value directly if editor not available
                descriptionTextarea.value = descriptionTextarea.value || '';
            }
            if (window.variantAdditionalInfoEditor && typeof window.variantAdditionalInfoEditor.getHTMLCode === 'function' && additionalInfoTextarea) {
                additionalInfoTextarea.value = window.variantAdditionalInfoEditor.getHTMLCode();
            } else if (additionalInfoTextarea) {
                // Fallback: use textarea value directly if editor not available
                additionalInfoTextarea.value = additionalInfoTextarea.value || '';
            }
            
            // Update hidden inputs for description and additional information
            const descriptionHidden = row.querySelector('[data-variant-description-input]');
            const additionalInfoHidden = row.querySelector('[data-variant-additional-information-input]');
            
            if (descriptionHidden && descriptionTextarea) {
                descriptionHidden.value = descriptionTextarea.value || '';
            }
            
            if (additionalInfoHidden && additionalInfoTextarea) {
                additionalInfoHidden.value = additionalInfoTextarea.value || '';
            }
            
            extractVariantRowData(row);
            syncModalImagePreviewFromRow(row);

            variantEditModal.hide();
            activeVariantRow = null;

            showToast('success', 'Variant updated successfully');
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
            
            // Update variant images container in images section
            if (window.populateVariantImagesContainer) {
                setTimeout(function() {
                    window.populateVariantImagesContainer();
                }, 200);
            }
            
            // Save highlights & details
            saveHighlightsDetailsToRow(row);
        });
    }

    // ============================================
    // Variant Highlights & Details Management
    // ============================================
    
    let headingSuggestions = [];
    let headingSuggestionsLoaded = false;
    
    // Load heading suggestions from API
    function loadHeadingSuggestions() {
        if (headingSuggestionsLoaded) return;
        
        $.ajax({
            url: '{{ route("variant-headings.suggestions") }}',
            method: 'GET',
            success: function(data) {
                headingSuggestions = data.suggestions || [];
                headingSuggestionsLoaded = true;
            },
            error: function(xhr, status, error) {
                console.error('Error loading heading suggestions:', error);
            }
        });
    }
    
    // Initialize heading suggestions on offcanvas open
    if ($variantEditModalElement.length) {
        $variantEditModalElement[0].addEventListener('show.bs.offcanvas', function() {
            loadHeadingSuggestions();
        });
    }
    
    // Create autocomplete datalist for heading input
    function createHeadingInput(headingIndex, headingName = '') {
        const inputId = `headingInput_${headingIndex}`;
        const datalistId = `headingSuggestions_${headingIndex}`;
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm heading-input';
        input.id = inputId;
        input.name = `heading_${headingIndex}`;
        input.value = headingName;
        input.required = true;
        input.placeholder = 'Enter heading name...';
        input.setAttribute('list', datalistId);
        input.setAttribute('data-heading-index', headingIndex);
        
        // Create datalist for autocomplete
        const datalist = document.createElement('datalist');
        datalist.id = datalistId;
        headingSuggestions.forEach(suggestion => {
            const option = document.createElement('option');
            option.value = suggestion;
            datalist.appendChild(option);
        });
        
        // Add event listener to save suggestion when new heading is entered
        input.addEventListener('blur', function() {
            const headingValue = this.value.trim();
            if (headingValue && !headingSuggestions.includes(headingValue)) {
                saveHeadingSuggestion(headingValue);
            }
        });
        
        return { input, datalist };
    }
    
    // Save heading suggestion to global list
    async function saveHeadingSuggestion(headingName) {
        try {
            const response = await fetch('{{ route("variant-headings.save-suggestion") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ heading_name: headingName })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && !headingSuggestions.includes(headingName)) {
                    headingSuggestions.push(headingName);
                    // Update all datalists
                    updateAllHeadingDatalists();
                }
            }
        } catch (error) {
            console.error('Error saving heading suggestion:', error);
        }
    }
    
    // Update all heading datalists with current suggestions
    function updateAllHeadingDatalists() {
        document.querySelectorAll('datalist[id^="headingSuggestions_"]').forEach(datalist => {
            datalist.innerHTML = '';
            headingSuggestions.forEach(suggestion => {
                const option = document.createElement('option');
                option.value = suggestion;
                datalist.appendChild(option);
            });
        });
    }
    
    // Create bullet point row
    function createBulletPointRow(headingIndex, pointIndex, pointText = '') {
        const row = document.createElement('div');
        row.className = 'bullet-point-row mb-2 d-flex align-items-center gap-2';
        row.setAttribute('data-heading-index', headingIndex);
        row.setAttribute('data-point-index', pointIndex);
        
        row.innerHTML = `
            <div class="input-group input-group-sm flex-grow-1">
                <input type="text" 
                       class="form-control form-control-sm bullet-point-input" 
                       value="${pointText}"
                       placeholder="Enter bullet point...">
                <button type="button" class="btn btn-outline-primary add-point-inline-btn" data-heading-index="${headingIndex}" title="Add point">
                    <i data-feather="plus" class="me-0" style="width: 14px; height: 14px;"></i>
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger delete-point-btn" title="Delete point">
                <i data-feather="trash-2" class="me-0"></i>
            </button>
        `;
        
        // Initialize feather icon
        if (window.feather) {
            setTimeout(() => feather.replace(), 10);
        }
        
        return row;
    }
    
    // Create heading panel
    function createHeadingPanel(headingIndex, headingName = '', bulletPoints = []) {
        const panel = document.createElement('div');
        panel.className = 'card mb-3 heading-panel';
        panel.setAttribute('data-heading-index', headingIndex);
        
        const { input, datalist } = createHeadingInput(headingIndex, headingName);
        
        // Escape heading name for HTML
        const escapedHeadingName = escapeHtml(headingName);
        
        panel.innerHTML = `
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 me-2">
                    ${input.outerHTML}
                    ${datalist.outerHTML}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger delete-heading-btn" title="Delete heading">
                    <i data-feather="trash-2" class="me-0"></i>
                </button>
            </div>
            <div class="card-body py-2">
                <div class="bullet-points-container" data-heading-index="${headingIndex}">
                    ${bulletPoints.length === 0 ? '<div class="text-muted small mb-2">No bullet points yet. Click the plus icon to add one.</div>' : ''}
                </div>
            </div>
        `;
        
        // Set the heading input value after creating the panel to ensure it's properly set
        const headingInputEl = panel.querySelector('.heading-input');
        if (headingInputEl) {
            headingInputEl.value = headingName;
        }
        
        // Add bullet points if provided
        const container = panel.querySelector('.bullet-points-container');
        bulletPoints.forEach((point, index) => {
            const pointRow = createBulletPointRow(headingIndex, index, point);
            container.insertBefore(pointRow, container.querySelector('.text-muted'));
        });
        
        // Remove "No bullet points" message if points exist
        if (bulletPoints.length > 0) {
            const noPointsMsg = container.querySelector('.text-muted');
            if (noPointsMsg) noPointsMsg.remove();
        }
        
        // Initialize feather icons
        if (window.feather) {
            setTimeout(() => feather.replace(), 10);
        }
        
        // Add event listeners
        const deleteHeadingBtn = panel.querySelector('.delete-heading-btn');
        const headingInput = panel.querySelector('.heading-input');
        
        // Add initial point if no points exist
        if (bulletPoints.length === 0) {
            const container = panel.querySelector('.bullet-points-container');
            const pointRow = createBulletPointRow(headingIndex, 0);
            const noPointsMsg = container.querySelector('.text-muted');
            if (noPointsMsg) noPointsMsg.remove();
            container.appendChild(pointRow);
        }
        
        deleteHeadingBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this heading and all its bullet points?')) {
                panel.remove();
                reindexHeadings();
            }
        });
        
        // Handle delete point button and inline add point button
        panel.addEventListener('click', function(e) {
            // Handle inline add point button
            if (e.target.closest('.add-point-inline-btn')) {
                const btn = e.target.closest('.add-point-inline-btn');
                const headingIdx = parseInt(btn.getAttribute('data-heading-index'));
                const container = panel.querySelector('.bullet-points-container');
                const pointIndex = container.querySelectorAll('.bullet-point-row').length;
                const pointRow = createBulletPointRow(headingIdx, pointIndex);
                
                // Remove "No bullet points" message if exists
                const noPointsMsg = container.querySelector('.text-muted');
                if (noPointsMsg) noPointsMsg.remove();
                
                // Find the row that contains this button and insert after it
                const currentRow = btn.closest('.bullet-point-row');
                if (currentRow && currentRow.nextSibling) {
                    currentRow.parentNode.insertBefore(pointRow, currentRow.nextSibling);
                } else {
                    container.appendChild(pointRow);
                }
                
                // Focus on new input
                const newInput = pointRow.querySelector('.bullet-point-input');
                if (newInput) newInput.focus();
                
                // Reinitialize feather icons
                if (window.feather) {
                    setTimeout(() => feather.replace(), 10);
                }
                return;
            }
            
            if (e.target.closest('.delete-point-btn')) {
                const pointRow = e.target.closest('.bullet-point-row');
                if (pointRow) {
                    const container = panel.querySelector('.bullet-points-container');
                    const remainingRows = container.querySelectorAll('.bullet-point-row');
                    // Keep at least one empty input row
                    if (remainingRows.length <= 1) {
                        // Clear the input instead of removing the row
                        const input = pointRow.querySelector('.bullet-point-input');
                        if (input) input.value = '';
                        return;
                    }
                    pointRow.remove();
                    reindexHeadings();
                }
            }
        });
        
        return panel;
    }
    
    // Reindex headings after deletion
    function reindexHeadings() {
        const panels = document.querySelectorAll('.heading-panel');
        panels.forEach((panel, index) => {
            panel.setAttribute('data-heading-index', index);
            const headingInput = panel.querySelector('.heading-input');
            if (headingInput) {
                headingInput.id = `headingInput_${index}`;
                headingInput.name = `heading_${index}`;
                headingInput.setAttribute('data-heading-index', index);
            }
            const container = panel.querySelector('.bullet-points-container');
            if (container) container.setAttribute('data-heading-index', index);
            const pointRows = container.querySelectorAll('.bullet-point-row');
            pointRows.forEach((row, pointIndex) => {
                row.setAttribute('data-heading-index', index);
                row.setAttribute('data-point-index', pointIndex);
            });
        });
    }
    
    // Load highlights & details into modal
    function loadHighlightsDetailsIntoModal(highlightsDetails) {
        const container = document.getElementById('variantHighlightsDetailsContainer');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!highlightsDetails || !Array.isArray(highlightsDetails) || highlightsDetails.length === 0) {
            return;
        }
        
        highlightsDetails.forEach((item, index) => {
            const panel = createHeadingPanel(index, item.heading_name || '', item.bullet_points || []);
            container.appendChild(panel);
        });
    }
    
    // Extract highlights & details from modal
    function extractHighlightsDetailsFromModal() {
        const panels = document.querySelectorAll('#variantHighlightsDetailsContainer .heading-panel');
        const highlightsDetails = [];
        
        panels.forEach(panel => {
            const headingInput = panel.querySelector('.heading-input');
            const headingName = headingInput ? headingInput.value.trim() : '';
            
            if (!headingName) return; // Skip empty headings
            
            const bulletPoints = [];
            const pointRows = panel.querySelectorAll('.bullet-point-row');
            pointRows.forEach(row => {
                const pointInput = row.querySelector('.bullet-point-input');
                const pointText = pointInput ? pointInput.value.trim() : '';
                if (pointText) {
                    bulletPoints.push(pointText);
                }
            });
            
            highlightsDetails.push({
                heading_name: headingName,
                bullet_points: bulletPoints
            });
        });
        
        return highlightsDetails;
    }
    
    // Save highlights & details to row
    function saveHighlightsDetailsToRow(row) {
        const highlightsDetails = extractHighlightsDetailsFromModal();
        const hiddenInput = row.querySelector('[data-variant-highlights-details-input]');
        
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(highlightsDetails);
        } else {
            // Create hidden input if it doesn't exist
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'variants[' + (row.getAttribute('data-variant-index') || '0') + '][highlights_details]';
            input.setAttribute('data-variant-highlights-details-input', 'true');
            input.value = JSON.stringify(highlightsDetails);
            row.appendChild(input);
        }
    }
    
    // Load highlights & details from row
    function loadHighlightsDetailsFromRow(row) {
        const hiddenInput = row.querySelector('[data-variant-highlights-details-input]');
        console.log('Loading highlights details from row:', {
            hasInput: !!hiddenInput,
            inputValue: hiddenInput ? hiddenInput.value : 'no input',
            inputValueType: hiddenInput ? typeof hiddenInput.value : 'no input',
            inputValueLength: hiddenInput ? hiddenInput.value.length : 0
        });
        
        if (hiddenInput && hiddenInput.value && hiddenInput.value !== '[]' && hiddenInput.value.trim() !== '') {
            try {
                let highlightsDetails = hiddenInput.value;
                
                // Unescape HTML entities if present
                if (typeof highlightsDetails === 'string') {
                    // Decode HTML entities
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = highlightsDetails;
                    highlightsDetails = tempDiv.textContent || tempDiv.innerText || highlightsDetails;
                }
                
                // If it's already a string, try to parse it
                if (typeof highlightsDetails === 'string') {
                    highlightsDetails = JSON.parse(highlightsDetails);
                }
                
                // Ensure it's an array
                if (Array.isArray(highlightsDetails) && highlightsDetails.length > 0) {
                    console.log('Loading highlights details:', highlightsDetails);
                    loadHighlightsDetailsIntoModal(highlightsDetails);
                } else {
                    console.log('Highlights details is empty array or invalid');
                    const container = document.getElementById('variantHighlightsDetailsContainer');
                    if (container) container.innerHTML = '';
                }
            } catch (e) {
                console.error('Error parsing highlights details:', e, 'Value:', hiddenInput.value);
                const container = document.getElementById('variantHighlightsDetailsContainer');
                if (container) container.innerHTML = '';
            }
        } else {
            // Clear modal
            console.log('No highlights details found, clearing modal');
            const container = document.getElementById('variantHighlightsDetailsContainer');
            if (container) container.innerHTML = '';
        }
    }
    
    // Add Heading button handler
    const addHeadingBtn = document.getElementById('addHeadingBtn');
    if (addHeadingBtn) {
        addHeadingBtn.addEventListener('click', function() {
            const container = document.getElementById('variantHighlightsDetailsContainer');
            if (!container) return;
            
            const existingPanels = container.querySelectorAll('.heading-panel');
            const nextIndex = existingPanels.length;
            const panel = createHeadingPanel(nextIndex);
            container.appendChild(panel);
            
            // Focus on heading input
            const headingInput = panel.querySelector('.heading-input');
            if (headingInput) headingInput.focus();
        });
    }
    
    // Load highlights & details when opening edit modal
    if (variantsTableBody) {
        $(variantsTableBody).on('click', function(e) {
            if (e.target.closest('.edit-variant-btn')) {
                const row = e.target.closest('tr');
                setTimeout(() => {
                    loadHighlightsDetailsFromRow(row);
                }, 100);
            }
        });
    }

    // Select All / Deselect All functionality
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        const $checkboxes = $(variantsTableBody).find('.variant-checkbox');
        $checkboxes.prop('checked', true);
        showToast('success', `Selected all ${$checkboxes.length} variants`);
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    });

    document.getElementById('deselectAllBtn').addEventListener('click', function() {
        const $checkboxes = $(variantsTableBody).find('.variant-checkbox');
        $checkboxes.prop('checked', false);
        showToast('success', 'Deselected all variants');
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    });

    // Bulk Actions Functionality
    const bulkEditBtn = document.getElementById('bulkEditBtn');
    if (bulkEditBtn) {
        bulkEditBtn.addEventListener('click', function() {
            const selectedRows = getSelectedVariantRows();
            if (selectedRows.length === 0) {
                alert('Please select variants to edit');
                return;
            }

            bulkEditTargets = selectedRows;
            resetBulkEditModal();
            if (bulkEditModal) {
                bulkEditModal.show();
            }
        });
    }

    document.getElementById('bulkActivateBtn').addEventListener('click', function() {
        const selectedRows = getSelectedVariantRows();
        if (selectedRows.length === 0) {
            alert('Please select variants to activate');
            return;
        }
        
        selectedRows.forEach(row => {
            const statusToggle = row.querySelector('.variant-status-toggle');
            if (statusToggle) {
                statusToggle.checked = true;
                updateVariantRowStatus(statusToggle);
            } else {
                const hiddenInput = row.querySelector('[data-variant-status-input]');
                if (hiddenInput) {
                    hiddenInput.value = '1';
                }
            }
        });
        showToast('success', `${selectedRows.length} variants activated`);
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    });

    document.getElementById('bulkDeactivateBtn').addEventListener('click', function() {
        const selectedRows = getSelectedVariantRows();
        if (selectedRows.length === 0) {
            alert('Please select variants to deactivate');
            return;
        }
        
        selectedRows.forEach(row => {
            const statusToggle = row.querySelector('.variant-status-toggle');
            if (statusToggle) {
                statusToggle.checked = false;
                updateVariantRowStatus(statusToggle);
            } else {
                const hiddenInput = row.querySelector('[data-variant-status-input]');
                if (hiddenInput) {
                    hiddenInput.value = '0';
                }
            }
        });
        showToast('success', `${selectedRows.length} variants deactivated`);
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    });

    // Sync generatedVariants array with actual DOM rows
    function syncGeneratedVariantsFromDOM() {
        const $variantsTableBody = $('#variantsTableBody');
        if (!$variantsTableBody.length) {
            generatedVariants = [];
            return [];
        }
        
        const $rows = $variantsTableBody.find('tr');
        const syncedVariants = [];
        
        $rows.each(function(index) {
            const $row = $(this);
            const variantDataStr = $row.data('variantData');
            if (variantDataStr) {
                try {
                    const variantData = typeof variantDataStr === 'string' ? JSON.parse(variantDataStr) : variantDataStr;
                    // Reconstruct variant object from row data
                    const variant = {
                        id: variantData.id || null,
                        name: variantData.name || '',
                        sku: variantData.sku || '',
                        price: variantData.price || '',
                        sale_price: variantData.sale_price || '',
                        is_active: variantData.is_active !== false,
                        discount_type: variantData.discount_type || '',
                        discount_value: variantData.discount_value || '',
                        discount_active: variantData.discount_active || false,
                        measurements: variantData.measurements || [],
                        attributes: variantData.attributes || {},
                        images: variantData.images || [],
                        stock_quantity: variantData.stock_quantity || 0,
                        stock_status: variantData.stock_status || 'in_stock',
                        manage_stock: variantData.manage_stock || false,
                        low_stock_threshold: variantData.low_stock_threshold || 0,
                        highlights_details: variantData.highlights_details || null,
                        description: variantData.description || null,
                        additional_information: variantData.additional_information || null,
                        barcode: variantData.barcode || ''
                    };
                    syncedVariants.push(variant);
                } catch (e) {
                    console.error('Error parsing variant data from row:', e, variantDataStr);
                }
            } else {
                // If no variantData, try to extract from form fields (fallback)
                const $skuInput = $row.find('[name*="[sku]"]');
                const $priceInput = $row.find('[name*="[price]"]');
                const $attributesInput = $row.find('[data-variant-attributes-input]');
                
                if ($skuInput.length || $priceInput.length || $attributesInput.length) {
                    let attributes = {};
                    if ($attributesInput.length && $attributesInput.val()) {
                        try {
                            attributes = JSON.parse($attributesInput.val());
                        } catch (e) {
                            console.error('Error parsing attributes from input:', e);
                        }
                    }
                    
                    const variant = {
                        id: $row.data('variantId') || null,
                        name: $row.find('[data-variant-name-input]').val() || '',
                        sku: $skuInput.length ? $skuInput.val() : '',
                        price: $priceInput.length ? $priceInput.val() : '',
                        sale_price: $row.find('[name*="[sale_price]"]').val() || '',
                        is_active: $row.find('[data-variant-status-input]').val() === '1',
                        attributes: attributes,
                        images: []
                    };
                    syncedVariants.push(variant);
                }
            }
        });
        
        generatedVariants = syncedVariants;
        console.log('Synced generatedVariants from DOM:', syncedVariants.length, 'variants');
        return syncedVariants;
    }

    $('#bulkDeleteBtn').on('click', function() {
        const selectedRows = getSelectedVariantRows();
        if (selectedRows.length === 0) {
            alert('Please select variants to delete');
            return;
        }
        
        if (confirm(`Are you sure you want to delete ${selectedRows.length} selected variants? This action cannot be undone.`)) {
            selectedRows.forEach($row => {
                $row.remove();
            });
            
            // Sync generatedVariants array with remaining DOM rows
            syncGeneratedVariantsFromDOM();
            
            showToast('success', `${selectedRows.length} variants deleted`);
            
            // Check if any variants remain
            const $remainingRows = $('#variantsTableBody').find('tr');
            if ($remainingRows.length === 0) {
                // Check if no attributes are selected - hide table if no variants
                const selectedIds = getSelectedAttributeIds();
                if (selectedIds.length === 0) {
                    // No attributes selected - hide table (default variant creation removed)
                    $variantsTableContainer.css('display', 'none');
                } else {
                    // Attributes selected but all variants deleted - hide table
                    $variantsTableContainer.css('display', 'none');
                    $bulkActions.css('display', 'none');
                }
            }
            
            // Update variant images section visibility
            if (window.updateVariantImagesVisibility) {
                window.updateVariantImagesVisibility();
            }
            
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
        }
    });

    // Helper function to get selected variant rows
    function getSelectedVariantRows() {
        const $selectedCheckboxes = $('#variantsTableBody').find('.variant-checkbox:checked');
        const selectedRows = [];
        $selectedCheckboxes.each(function() {
            selectedRows.push($(this).closest('tr'));
        });
        return selectedRows;
    }

    // Load existing variants for edit mode
    function loadExistingVariants(payload) {
        if (!Array.isArray(payload) || payload.length === 0) {
            console.log('loadExistingVariants: No payload or empty array');
            return;
        }

        console.log('loadExistingVariants: Loading', payload.length, 'variants');
        
        // Clear all attribute-related data before loading variants to prevent auto-preservation
        selectedAttributeIds = [];
        attributeValues = {};
        selectedAttributeValues = {};
        
        generatedVariants = payload;

        // Display the variants
        displayVariants();
         
        syncGeneratedVariantsFromDOM();

        // Show the variants table and bulk actions
        if (variantsTableContainer) {
            $variantsTableContainer.css('display', 'block');
        }
        if ($bulkActions.length) {
            $bulkActions.css('display', 'block');
        }
        
        // Ensure attribute data remains cleared after loading variants
        // This prevents preserveUnsatisfiedVariants from populating values
        selectedAttributeIds = [];
        attributeValues = {};
        selectedAttributeValues = {};
        
        // Uncheck all attribute checkboxes
        const $availableAttributesContainer = $('#availableAttributesContainer');
        if ($availableAttributesContainer.length) {
            $availableAttributesContainer.find('.attribute-checkbox').prop('checked', false);
        }
        
        // Uncheck all attribute value checkboxes
        $('#variantAttributeSelectors').find('.value-checkbox, .variant-attribute-value-checkbox').prop('checked', false);

        // Disabled: Don't auto-select attributes on page load
        // Wait a bit for DOM to be ready, then auto-select attributes
        // setTimeout(function() {
        //     console.log('loadExistingVariants: Calling ensureAttributeSelectionFromVariants');
        //     ensureAttributeSelectionFromVariants();
        // }, 300);
    }

    // Toast notification function
    function showToast(type, message) {
        // Create toast element if it doesn't exist
        let $toast = $('#variantToast');
        if (!$toast.length) {
            $toast = $('<div>')
                .attr('id', 'variantToast')
                .addClass('toast position-fixed top-0 end-0 m-3')
                .css('zIndex', '1100') // Higher than productFormNav (1000) and sticky-bottom (10)
                .attr('role', 'alert')
                .attr('aria-live', 'assertive')
                .attr('aria-atomic', 'true');
            $('body').append($toast);
        }
        
        const iconName = type === 'success' ? 'check-circle' : 'x-circle';
        const iconToneClass = type === 'success' ? 'text-success' : 'text-danger';
        const title = type === 'success' ? 'Success' : 'Error';
        
        $toast.html(`
            <div class="toast-header">
                <span data-feather="${iconName}" class="me-2 ${iconToneClass}"></span>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `);
        
        const bsToast = new bootstrap.Toast($toast[0]);
        bsToast.show();
        refreshFeatherIcons();
    }

    function calculateVariantSellPrice() {
        if (!$mrpInput.length || !$variantSellPriceInput.length) {
            return;
        }

        const $discountValueField = $discountValueInput;
        const $discountTypeField = $discountTypeSelect;
        const $discountActive = $discountActiveSelect;

        let mrp = parseFloat($mrpInput.val());
        if (isNaN(mrp) || mrp < 0) {
            mrp = 0;
        }

        let sellPrice = $variantSellPriceInput.val() ? parseFloat($variantSellPriceInput.val()) : mrp;

        if ($discountActive.length && $discountActive.val() === '1' && $discountTypeField.length && $discountTypeField.val() && $discountValueField.length) {
            const discountValue = parseFloat($discountValueField.val());
            if (!isNaN(discountValue) && discountValue >= 0) {
                if ($discountTypeField.val() === 'percentage') {
                    sellPrice = Math.max(mrp - (mrp * (discountValue / 100)), 0);
                } else {
                    sellPrice = Math.max(mrp - discountValue, 0);
                }
            }
        } else {
            sellPrice = mrp;
        }

        $variantSellPriceInput.val(sellPrice ? sellPrice.toFixed(2) : '');
    }

    function handleDiscountStateChange() {
        if (!$discountValueInput.length) {
            return;
        }

        updateDiscountPrefix();
        if ($discountTypeSelect.length && $discountTypeSelect.val()) {
            $discountValueInput.prop('disabled', false);
        } else {
            $discountValueInput.val('');
            $discountValueInput.prop('disabled', true);
            if ($discountActiveSelect.length) {
                $discountActiveSelect.val('0');
            }
            if ($variantSellPriceInput.length && $variantSellPriceInput.data('originalSellPrice') !== undefined) {
                $variantSellPriceInput.val($variantSellPriceInput.data('originalSellPrice'));
            }
        }
        calculateVariantSellPrice();
    }

    function updateDiscountPrefix() {
        if ($discountTypeSelect.length && $discountTypeSelect.val() === 'percentage') {
            $discountPrefixSpan.text('%');
        } else {
            $discountPrefixSpan.text('₹');
        }
    }

    function updateVariantStatusToggleLabel() {
        if (!$variantStatusToggle.length) {
            return;
        }
        const $label = $('label[for="variantStatusToggle"]');
        if ($label.length) {
            $label.text($variantStatusToggle.is(':checked') ? 'Active' : 'Inactive');
        }
    }

    function updateVariantRowStatus(toggleElement) {
        if (!toggleElement) {
            return;
        }
        const $row = $(toggleElement).closest('tr');
        if (!$row.length) {
            return;
        }
        const $label = $row.find('label[for="' + toggleElement.id + '"]');
        if ($label.length) {
            $label.text($(toggleElement).is(':checked') ? 'Active' : 'Inactive');
        }
        const hiddenInput = $row[0].querySelector('[data-variant-status-input]');
        if (hiddenInput) {
            hiddenInput.value = toggleElement.checked ? '1' : '0';
        }
    }

    function extractExistingImagesFromRow(row) {
        if (!row || !row.dataset || !row.dataset.existingImages) {
            return [];
        }
        try {
            const parsed = JSON.parse(row.dataset.existingImages);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function updateVariantImagePreview(previewEl, fileList, existingImages) {
        if (!previewEl) {
            return;
        }

        previewEl.innerHTML = '';
        let hasContent = false;

        if (fileList && fileList.length) {
            Array.from(fileList).forEach(file => {
                const wrapper = document.createElement('div');
                wrapper.className = 'variant-image-thumb';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                img.onload = function() {
                    URL.revokeObjectURL(img.src);
                };
                wrapper.appendChild(img);
                previewEl.appendChild(wrapper);
            });
            hasContent = true;
        }

        if (!hasContent && Array.isArray(existingImages) && existingImages.length) {
            existingImages.forEach(img => {
                // Handle both old format (string path) and new format (object with id, path, url)
                let imagePath = null;
                if (typeof img === 'string') {
                    imagePath = img;
                } else if (img && (img.path || img.url)) {
                    imagePath = img.path || img.url;
                }
                
                if (!imagePath) {
                    return;
                }
                
                const normalizedPath = imagePath.startsWith('http')
                    ? imagePath
                    : `/storage/${imagePath.replace(/^\/?storage\//, '')}`;
                const wrapper = document.createElement('div');
                wrapper.className = 'variant-image-thumb';
                const imgEl = document.createElement('img');
                imgEl.src = normalizedPath;
                imgEl.alt = 'Variant image';
                wrapper.appendChild(imgEl);
                previewEl.appendChild(wrapper);
            });
            hasContent = true;
        }

        if (!hasContent) {
            const empty = document.createElement('small');
            empty.className = 'text-muted';
            empty.textContent = 'No images selected.';
            previewEl.appendChild(empty);
        }
    }

    function syncModalImagePreview(files, existingImages) {
        if (!variantImagesPreview) {
            return;
        }
        updateVariantImagePreview(variantImagesPreview, files, existingImages);
    }

    function syncModalImagePreviewFromRow(row) {
        if (!variantImagesPreview) {
            return;
        }
        const imageInput = row ? row.querySelector('.variant-image-input') : null;
        const existingImages = extractExistingImagesFromRow(row);
        if (imageInput && imageInput.files && imageInput.files.length) {
            syncModalImagePreview(imageInput.files, []);
        } else {
            syncModalImagePreview(null, existingImages);
        }
    }

    if (bulkEditForm) {
        const simpleToggles = bulkEditForm.querySelectorAll('.bulk-field-toggle');
        simpleToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = toggle.dataset.target;
                if (!targetId) {
                    return;
                }
                const targetInput = document.getElementById(targetId);
                if (!targetInput) {
                    return;
                }
                if (toggle.checked) {
                    targetInput.removeAttribute('disabled');
                    targetInput.focus();
                } else {
                    targetInput.value = '';
                    targetInput.setAttribute('disabled', 'disabled');
                }
            });
        });

        // Listen to discount type changes to update prefix
        if (bulkDiscountTypeValue) {
            bulkDiscountTypeValue.addEventListener('change', function() {
                updateBulkDiscountPrefix();
            });
        }


        bulkEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyBulkEditChanges();
        });
    }

    function normalizeCurrencyValue(rawValue) {
        if (rawValue === null || rawValue === undefined || rawValue === '') {
            return '';
        }
        const numeric = parseFloat(rawValue);
        if (!Number.isFinite(numeric)) {
            return '';
        }
        return numeric.toFixed(2);
    }

    function normalizeStockValue(rawValue) {
        if (rawValue === null || rawValue === undefined || rawValue === '') {
            return '';
        }
        const numeric = parseInt(rawValue, 10);
        if (!Number.isFinite(numeric) || numeric < 0) {
            return '';
        }
        return numeric.toString();
    }

    function resetBulkEditModal() {
        if (!bulkEditForm) {
            return;
        }
        bulkEditForm.reset();

        // Clear all input values but keep them enabled
        if (bulkPriceValue) {
            bulkPriceValue.value = '';
        }
        if (bulkSalePriceValue) {
            bulkSalePriceValue.value = '';
        }
        if (bulkStatusValue) {
            bulkStatusValue.checked = true;
        }
        if (bulkDiscountTypeValue) {
            bulkDiscountTypeValue.value = '';
        }
        if (bulkDiscountValue) {
            bulkDiscountValue.value = '';
        }
        if (bulkDiscountActiveValue) {
            bulkDiscountActiveValue.checked = false;
        }

        updateBulkDiscountPrefix();
    }

    function updateBulkDiscountPrefix() {
        if (!bulkDiscountPrefix) {
            return;
        }
        if (bulkDiscountTypeValue && bulkDiscountTypeValue.value === 'percentage') {
            bulkDiscountPrefix.textContent = '%';
        } else {
            bulkDiscountPrefix.textContent = '₹';
        }
    }

    function applyBulkEditChanges() {
        if (!bulkEditTargets || bulkEditTargets.length === 0) {
            if (bulkEditModal) {
                bulkEditModal.hide();
            }
            return;
        }

        bulkEditTargets.forEach(function(row) {
            // Convert jQuery object to DOM element if needed
            const rowElement = row.jquery ? row[0] : row;
            const $row = row.jquery ? row : $(row);
            
            if (bulkPriceValue && bulkPriceValue.value) {
                const normalized = normalizeCurrencyValue(bulkPriceValue.value);
                if (normalized !== '') {
                    const priceInput = $row.find('[name*="[price]"]')[0];
                    if (priceInput) {
                        priceInput.value = normalized;
                    }
                }
            }

            if (bulkSalePriceValue && bulkSalePriceValue.value) {
                const normalizedSale = normalizeCurrencyValue(bulkSalePriceValue.value);
                const saleInput = $row.find('[name*="[sale_price]"]')[0];
                if (saleInput) {
                    saleInput.value = normalizedSale;
                }
            }

            if (bulkStatusValue !== null && bulkStatusValue !== undefined) {
                const statusToggle = $row.find('.variant-status-toggle')[0];
                if (statusToggle) {
                    statusToggle.checked = !!bulkStatusValue.checked;
                    updateVariantRowStatus(statusToggle);
                } else {
                    const hiddenStatus = $row.find('[data-variant-status-input]')[0];
                    if (hiddenStatus) {
                        hiddenStatus.value = bulkStatusValue.checked ? '1' : '0';
                    }
                }
            }

            if (bulkDiscountTypeValue && bulkDiscountTypeValue.value) {
                const discountTypeHidden = $row.find('[data-variant-discount-type-input]')[0];
                if (discountTypeHidden) {
                    discountTypeHidden.value = bulkDiscountTypeValue.value;
                }
            }

            if (bulkDiscountValue && bulkDiscountValue.value) {
                const discountValueHidden = $row.find('[data-variant-discount-value-input]')[0];
                if (discountValueHidden) {
                    discountValueHidden.value = bulkDiscountValue.value;
                }
            }

            if (bulkDiscountActiveValue !== null && bulkDiscountActiveValue !== undefined) {
                const discountActiveHidden = $row.find('[data-variant-discount-active-input]')[0];
                if (discountActiveHidden) {
                    discountActiveHidden.value = bulkDiscountActiveValue.checked ? '1' : '';
                }
            }
        });

        showToast('success', `Bulk changes applied to ${bulkEditTargets.length} variants`);
        bulkEditTargets = [];
        if (bulkEditModal) {
            bulkEditModal.hide();
        }
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    }

    if (bulkEditModalElement) {
        bulkEditModalElement.addEventListener('hidden.bs.modal', function() {
            bulkEditTargets = [];
            resetBulkEditModal();
        });
    }

    function deepClone(value) {
        try {
            return JSON.parse(JSON.stringify(value));
        } catch (error) {
            return value;
        }
    }

    function extractVariantRowData(row) {
        if (!row) {
            return null;
        }

        const label = row.querySelector('.form-check-label');
        const skuInput = row.querySelector('[name*="[sku]"]');
        const priceInput = row.querySelector('[name*="[price]"]');
        const salePriceInput = row.querySelector('[name*="[sale_price]"]');
        const statusHidden = row.querySelector('[data-variant-status-input]');
        const discountTypeHidden = row.querySelector('[data-variant-discount-type-input]');
        const discountValueHidden = row.querySelector('[data-variant-discount-value-input]');
        const discountActiveHidden = row.querySelector('[data-variant-discount-active-input]');
        const measurementHidden = row.querySelector('[data-variant-measurements-input]');
        const idHidden = row.querySelector('[data-variant-id-input]');
        const attributesHidden = row.querySelector('[data-variant-attributes-input]');
        const highlightsDetailsHidden = row.querySelector('[data-variant-highlights-details-input]');
        const descriptionHidden = row.querySelector('[data-variant-description-input]');
        const additionalInformationHidden = row.querySelector('[data-variant-additional-information-input]');

        let measurements = [];
        if (measurementHidden && measurementHidden.value) {
            try {
                measurements = JSON.parse(measurementHidden.value);
            } catch (error) {
                measurements = [];
            }
        }

        let attributes = {};
        if (attributesHidden && attributesHidden.value) {
            try {
                attributes = JSON.parse(attributesHidden.value);
            } catch (error) {
                attributes = {};
            }
        } else if (row.dataset.variantData) {
            try {
                const parsed = JSON.parse(row.dataset.variantData);
                if (parsed && typeof parsed === 'object') {
                    attributes = parsed.attributes || {};
                }
            } catch (error) {
                attributes = {};
            }
        }

        let highlightsDetails = [];
        if (highlightsDetailsHidden && highlightsDetailsHidden.value) {
            try {
                highlightsDetails = JSON.parse(highlightsDetailsHidden.value);
            } catch (error) {
                highlightsDetails = [];
            }
        }

        // Get description and additional_information from hidden inputs or variantData
        let description = '';
        let additionalInformation = '';
        
        if (descriptionHidden && descriptionHidden.value) {
            description = descriptionHidden.value;
        } else if (row.dataset.variantData) {
            try {
                const parsed = JSON.parse(row.dataset.variantData);
                if (parsed && typeof parsed === 'object') {
                    description = parsed.description || '';
                }
            } catch (error) {
                description = '';
            }
        }
        
        if (additionalInformationHidden && additionalInformationHidden.value) {
            additionalInformation = additionalInformationHidden.value;
        } else if (row.dataset.variantData) {
            try {
                const parsed = JSON.parse(row.dataset.variantData);
                if (parsed && typeof parsed === 'object') {
                    additionalInformation = parsed.additional_information || '';
                }
            } catch (error) {
                additionalInformation = '';
            }
        }

        let variantId = null;
        if (idHidden && idHidden.value !== '') {
            variantId = idHidden.value;
        } else if (row.dataset.variantId && row.dataset.variantId !== '') {
            variantId = row.dataset.variantId;
        }

        const existingImages = extractExistingImagesFromRow(row);

        const result = {
            id: variantId !== null && variantId !== '' ? variantId : null,
            name: label ? label.textContent.trim() : '',
            sku: skuInput ? skuInput.value : '',
            price: priceInput ? priceInput.value : '',
            sale_price: salePriceInput ? salePriceInput.value : '',
            is_active: statusHidden ? statusHidden.value : '1',
            discount_type: discountTypeHidden ? discountTypeHidden.value : '',
            discount_value: discountValueHidden ? discountValueHidden.value : '',
            discount_active: discountActiveHidden ? discountActiveHidden.value : '',
            measurements,
            attributes,
            highlights_details: highlightsDetails,
            description: description,
            additional_information: additionalInformation,
            images: existingImages,
        };

        row.dataset.variantId = result.id ? String(result.id) : '';
        row.dataset.variantData = JSON.stringify(result);

        if (idHidden) {
            idHidden.value = result.id ? result.id : '';
        }
        if (attributesHidden) {
            attributesHidden.value = JSON.stringify(result.attributes || {});
        }
        if (descriptionHidden) {
            descriptionHidden.value = result.description || '';
        }
        if (additionalInformationHidden) {
            additionalInformationHidden.value = result.additional_information || '';
        }
        row.dataset.existingImages = JSON.stringify(result.images || []);

        return result;
    }

    function collectVariantRowData() {
        const $rows = $('#variantsTableBody').find('tr');
        return $rows.toArray()
            .map(row => extractVariantRowData(row))
            .filter(item => item !== null);
    }

    function persistVariantDraft() {
        if (isEditMode || isRestoringVariantDraft) {
            return;
        }

        try {
            // Only save variants, not attribute selections - user must manually select each time
            const draftPayload = {
                selectedAttributeIds: [], // Always empty - don't preserve attribute selections
                attributeValues: {}, // Always empty - don't preserve attribute values
                selectedAttributeValues: {}, // Always empty - don't preserve selected values
                generatedVariants: collectVariantRowData(),
                // Removed attributeSearchQuery - Select2 handles search internally
            };
            localStorage.setItem(VARIANT_STORAGE_KEY, JSON.stringify(draftPayload));
            console.log('Variant draft saved:', draftPayload);
        } catch (error) {
            console.error('Unable to persist variant draft:', error);
        }
    }

    function restoreVariantDraft() {
        if (isEditMode) {
            return;
        }

        // If we're on a create page and there are no existing variants, clear any old draft
        const isCreatePage = window.location.pathname.includes('/create') || 
                            (!isEditMode && Array.isArray(existingVariantsPayload) && existingVariantsPayload.length === 0);
        
        if (isCreatePage) {
            // Clear old draft when starting a new product creation
            localStorage.removeItem(VARIANT_STORAGE_KEY);
            return;
        }

        const stored = localStorage.getItem(VARIANT_STORAGE_KEY);
        if (!stored) {
            return;
        }

        if (Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0) {
            return;
        }

        let payload;
        try {
            payload = JSON.parse(stored);
        } catch (error) {
            console.warn('Invalid variant draft data, clearing storage.');
            localStorage.removeItem(VARIANT_STORAGE_KEY);
            return;
        }

        console.log('Restoring variant draft:', payload);
 
        isRestoringVariantDraft = true;
 
        // Don't restore attribute selection data - let user manually select
        // Only restore the variants themselves
        selectedAttributeIds = [];
        attributeValues = {};
        selectedAttributeValues = {};
        
        // Immediately uncheck all attribute checkboxes to ensure clean state
        const $availableAttributesContainer = $('#availableAttributesContainer');
        if ($availableAttributesContainer.length) {
            $availableAttributesContainer.find('.attribute-checkbox').prop('checked', false);
        }
        
        const restoredAttributeIds = Array.isArray(payload.selectedAttributeIds) ? payload.selectedAttributeIds : [];
        const restoredVariants = Array.isArray(payload.generatedVariants) ? payload.generatedVariants : [];

        // Disabled: Don't restore selected attributes from draft - let user manually select
        // Restore selected attributes in checkboxes
        // if (restoredAttributeIds.length > 0) {
        //     setSelectedAttributeIds(restoredAttributeIds);
        //     selectedAttributeIds = restoredAttributeIds;
        // }

        // Don't call updateAttributeValuesConfig when restoring - no attributes should be selected
        // if (!restoredAttributeIds.length) {
        //     updateAttributeValuesConfig();
        // }

        if (restoredVariants.length) {
            generatedVariants = restoredVariants.map(variant => {
                const clone = Object.assign({}, variant);
                if (!clone.attributes || typeof clone.attributes !== 'object') {
                    clone.attributes = {};
                }
                return clone;
            });
            displayVariants();
        }

        // Ensure all attribute-related data is cleared after restoring
        // User must manually select attributes
        selectedAttributeIds = [];
        attributeValues = {};
        selectedAttributeValues = {};
        
        // Ensure checkboxes remain unchecked (already unchecked above, but double-check)
        if ($availableAttributesContainer.length) {
            $availableAttributesContainer.find('.attribute-checkbox').prop('checked', false);
        }
        
        // Also uncheck all attribute value checkboxes
        $('#variantAttributeSelectors').find('.value-checkbox, .variant-attribute-value-checkbox').prop('checked', false);

        isRestoringVariantDraft = false;
        persistVariantDraft();
    }

    function clearVariantDraft() {
        localStorage.removeItem(VARIANT_STORAGE_KEY);
    }

    window.clearVariantDraft = clearVariantDraft;

    // Removed filterAvailableAttributes call - Select2 handles search/filtering automatically
    refreshFeatherIcons();

    // Function to update Select2 dropdown with variant attributes
    // This can be called from categories.blade.php after loading attributes
    // This prevents duplicate AJAX calls when category changes
    window.updateVariantAttributesSelect2 = function(variantAttributes) {
        const container = document.getElementById('availableAttributesContainer');
        if (!container) {
            return;
        }
        
        // If no variant attributes provided or empty array, load all attributes as fallback
        if (!variantAttributes || (Array.isArray(variantAttributes) && variantAttributes.length === 0)) {
            console.log('No variant attributes provided, loading all attributes as fallback');
            
            // Try to use loadAllAttributes from categories.blade.php first
            if (typeof loadAllAttributes === 'function') {
                loadAllAttributes();
                return; // loadAllAttributes will call this function again with the loaded attributes
            }
            
            // If loadAllAttributes is not available, load attributes directly
            $.ajax({
                url: "{{ route('products.attributes') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success && response.attributes) {
                        console.log('Fallback: Total attributes received:', response.attributes.length);
                        // Show ALL attributes (not just visible or variant ones) when no category attributes
                        // This allows users to see and use any attribute for variants
                        const variantAttrs = response.attributes.map(function(attr) {
                            return {
                                id: attr.id,
                                name: attr.name || '',
                                slug: attr.slug || '',
                                type: attr.type || 'text',
                                description: attr.description || '',
                                is_required: attr.is_required || false,
                                is_visible: attr.is_visible !== false,
                                sort_order: attr.sort_order || 0,
                                values: (attr.values || []).map(function(value) {
                                    return {
                                        id: value.id || null,
                                        value: value.value || '',
                                        color_code: value.color_code || null,
                                        image_path: value.image_path || null,
                                        sort_order: value.sort_order || 0,
                                    };
                                })
                            };
                        });
                        
                        console.log('Fallback: All attributes loaded:', variantAttrs.length);
                        console.log('Fallback: Attribute names:', variantAttrs.map(a => a.name));
                        
                        // Recursively call this function with the loaded attributes
                        if (variantAttrs.length > 0) {
                            window.updateVariantAttributesSelect2(variantAttrs);
                        } else {
                            // No visible attributes found even after loading all
                            container.innerHTML = '';
                            const noAttributesMsg = document.createElement('div');
                            noAttributesMsg.className = 'text-muted text-center p-3';
                            noAttributesMsg.innerHTML = `
                                <i class="fas fa-info-circle me-2"></i>
                                <small>No variant attributes available. Please create attributes and mark them as "Can be used for variations".</small>
                            `;
                            container.appendChild(noAttributesMsg);
                            selectedAttributeIds = [];
                            updateAttributeValuesConfig();
                        }
                    } else {
                        console.warn('Failed to load all attributes:', response);
                        container.innerHTML = '';
                        const noAttributesMsg = document.createElement('div');
                        noAttributesMsg.className = 'text-muted text-center p-3';
                        noAttributesMsg.innerHTML = `
                            <i class="fas fa-info-circle me-2"></i>
                            <small>No variant attributes available. Please assign attributes to the selected category or create new attributes.</small>
                        `;
                        container.appendChild(noAttributesMsg);
                        selectedAttributeIds = [];
                        updateAttributeValuesConfig();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading all attributes:', error);
                    container.innerHTML = '';
                    const noAttributesMsg = document.createElement('div');
                    noAttributesMsg.className = 'text-muted text-center p-3';
                    noAttributesMsg.innerHTML = `
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Error loading attributes. Please try again or create new attributes.</small>
                    `;
                    container.appendChild(noAttributesMsg);
                    selectedAttributeIds = [];
                    updateAttributeValuesConfig();
                }
            });
            return;
        }
        
        console.log('Updating checkbox list with variant attributes:', variantAttributes);
        
        // Get currently selected values
        const currentSelected = getSelectedAttributeIds();
        
        // Clear existing checkboxes
        container.innerHTML = '';
        
        // Add variant attributes as checkboxes
        if (variantAttributes && variantAttributes.length > 0) {
            console.log('Adding', variantAttributes.length, 'attributes to checkbox list');
            
            variantAttributes.forEach(function(attr) {
                // Don't auto-check attributes based on existing variants
                // const isSelected = currentSelected.includes(String(attr.id));
                const attributeName = attr.name;
                const attributeType = attr.type ? attr.type.charAt(0).toUpperCase() + attr.type.slice(1) : '';
                const description = attr.description || '';
                
                // Create checkbox item
                const itemDiv = document.createElement('div');
                itemDiv.className = 'form-check attribute-checkbox-item';
                itemDiv.setAttribute('data-attribute-id', attr.id); 
                const checkboxId = `attr_${attr.id}`;
                itemDiv.innerHTML = `
                    <input class="form-check-input attribute-checkbox" 
                           type="checkbox" 
                           value="${attr.id}" 
                           id="${checkboxId}"
                           data-attribute-type="${attr.type || ''}"
                           data-attribute-description="${description}">
                    <label class="form-check-label" for="${checkboxId}">
                        <strong>${escapeHtml(attributeName)}</strong>
                        <span class="text-muted ms-2">(${escapeHtml(attributeType)})</span>
                        ${description ? `<small class="text-muted d-block">${escapeHtml(description)}</small>` : ''}
                    </label>
                `;
                
                container.appendChild(itemDiv);
            });
            
            console.log('All checkboxes added to DOM. Total:', container.querySelectorAll('.attribute-checkbox').length);
            
            // Don't restore selected values - let user manually select attributes
            // Attributes will not be auto-checked on page load
            selectedAttributeIds = [];
            updateAttributeValuesConfig();
        } else {
            // This should not happen as we check at the beginning, but just in case
            // No variant attributes from category - load all attributes as fallback
            console.warn('No variant attributes found for category, loading all attributes as fallback');
            
            // Try to load all attributes
            if (typeof loadAllAttributes === 'function') {
                loadAllAttributes();
            } else {
                // If loadAllAttributes is not available, show message
                const noAttributesMsg = document.createElement('div');
                noAttributesMsg.className = 'text-muted text-center p-3';
                noAttributesMsg.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    <small>No variant attributes available. Please assign attributes to the selected category or create new attributes.</small>
                `;
                container.appendChild(noAttributesMsg);
                
                // Clear selection if no attributes
                selectedAttributeIds = [];
                updateAttributeValuesConfig();
            }
        }
    };

    // Category change handler removed - now handled by categories.blade.php
    // The updateVariantAttributesSelect2 function above will be called from categories.blade.php
    // after the AJAX call completes, preventing duplicate requests

    // Ensure variant section is enabled if SKU type is 'variant'
    function ensureVariantSectionEnabled() {
        const selectedSkuType = $('input[name="sku_type"]:checked').val() || 'single';
        if (selectedSkuType === 'variant') {
            // Enable attribute checkboxes
            const $attributeCheckboxes = $('.attribute-checkbox');
            if ($attributeCheckboxes.length) {
                $attributeCheckboxes.prop('disabled', false);
            }
            const $availableAttributesContainer = $('#availableAttributesContainer');
            if ($availableAttributesContainer.length) {
                $availableAttributesContainer.find('.attribute-checkbox-item').css('opacity', '1').css('pointer-events', 'auto');
            }
        }
    }
    
    // Run after a short delay to ensure SKU type is initialized
    setTimeout(function() {
        ensureVariantSectionEnabled();
        // Disabled: Don't preselect attributes on page load
        // preselectExistingAttributeCheckboxes();
    }, 200);

    // Category change is now handled by categories.blade.php
    // No need to trigger change here as it will be handled when categories are loaded

    restoreVariantDraft();

    // Note: Variants section visibility is controlled by the dynamic form's section visibility logic
    
    // Initialize for edit mode if product exists and has variants
    if (Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0) {
        loadExistingVariants(existingVariantsPayload);
    }

    // Attribute selection
    // Removed duplicate event listener - already handled above


    $(document).on('product:clearDrafts', clearVariantDraft);

    function normalizeVariantImages(images) {
        if (!images) {
            return [];
        }

        if (Array.isArray(images)) {
            return images.map(image => normalizeImageEntry(image));
        }

        if (typeof images === 'object') {
            if (Array.isArray(images.data)) {
                return images.data.map(image => normalizeImageEntry(image));
            }

            if (typeof images.toArray === 'function') {
                return images.toArray().map(image => normalizeImageEntry(image));
            }

            return Object.values(images).map(image => normalizeImageEntry(image));
        }

        return [];
    }

    function normalizeImageEntry(image) {
        if (!image) {
            return null;
        }

        if (typeof image === 'string') {
            return image;
        }

        if (typeof image === 'object') {
            if (image.image_path) {
                return image.image_path;
            }
            if (image.path) {
                return image.path;
            }
            if (image.url) {
                return image.url;
            }
        }

        return String(image);
    }

    function extractAttributeScalar(value) {
        if (value === undefined || value === null || value === '') {
            return '';
        }
        if (typeof value === 'object') {
            if (Array.isArray(value) && value.length > 0) {
                return extractAttributeScalar(value[0]);
            }
            if (value.value !== undefined && value.value !== null && value.value !== '') {
                return String(value.value);
            }
            if (value.label !== undefined && value.label !== null && value.label !== '') {
                return String(value.label);
            }
            if (value.name !== undefined && value.name !== null && value.name !== '') {
                return String(value.name);
            }
            if (value.display !== undefined && value.display !== null && value.display !== '') {
                return String(value.display);
            }
            if (value.text !== undefined && value.text !== null && value.text !== '') {
                return String(value.text);
            }
            const objectValue = value.id !== undefined ? value.id : '';
            return objectValue !== '' ? String(objectValue) : '';
        }
        return String(value);
    }

    function normalizeAttributesPayload(source) {
        if (!source) {
            return {};
        }

        let working = source;
        if (typeof working === 'string') {
            try {
                working = JSON.parse(working);
            } catch (error) {
                working = {};
            }
        }

        if (Array.isArray(working)) {
            const result = {};
            working.forEach(entry => {
                if (!entry) {
                    return;
                }
                if (entry.attribute_id !== undefined && entry.attribute_id !== null) {
                    const scalar = extractAttributeScalar(entry.value ?? entry.attribute_value ?? entry.option ?? entry);
                    if (scalar !== '') {
                        result[String(entry.attribute_id)] = scalar;
                    }
                } else if (entry.id !== undefined && entry.value !== undefined) {
                    const scalar = extractAttributeScalar(entry.value);
                    if (scalar !== '') {
                        result[String(entry.id)] = scalar;
                    }
                } else if (entry.key !== undefined && entry.value !== undefined) {
                    const scalar = extractAttributeScalar(entry.value);
                    if (scalar !== '') {
                        result[String(entry.key)] = scalar;
                    }
                }
            });
            return result;
        }

        if (typeof working === 'object') {
            const result = {};
            Object.keys(working).forEach(key => {
                const scalar = extractAttributeScalar(working[key]);
                if (scalar !== '') {
                    result[String(key)] = scalar;
                }
            });
            return result;
        }

        return {};
    }
    
    // Final initialization: Ensure default variant is created and table is visible
    // This runs after all other initialization
    setTimeout(function() {
        const variantsTableContainer = document.getElementById('variantsTableContainer');
        const variantsTableBody = document.getElementById('variantsTableBody');
        
        // Make sure table is visible
        const $variantsTableContainer = $('#variantsTableContainer');
        if ($variantsTableContainer.length) {
            $variantsTableContainer.css('display', 'block');
        }
        
        // Check if we need to create default variant
        const selectedIds = getSelectedAttributeIds();
        const $variantsTableBody = $('#variantsTableBody');
        const hasVariantsInTable = $variantsTableBody.length && $variantsTableBody.find('tr').length > 0;
        
        // Check generatedVariants from the scope
        let hasVariants = false;
        try {
            if (typeof generatedVariants !== 'undefined') {
                hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
            }
        } catch(e) {
            hasVariants = false;
        }
        
        // Default variant creation removed per user request
        // If no attributes selected and no variants, hide the table
        if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
            if (variantsTableContainer) {
                $variantsTableContainer.css('display', 'none');
            }
        }
    }, 1500);
});
</script>

<style> 
.attribute-values .form-check {
    margin-bottom: 0.5rem;
}

.attribute-values .form-check-input {
    margin-top: 0.5rem;
    cursor: pointer;
}

.variants-table-container .table-responsive {
    max-height: 420px;
    overflow-y: auto;
    overflow-x: auto;
}

.attribute-values .form-check-label {
    cursor: pointer; 
    display: inline-block;
    margin-left: 0.25rem;
}

.attribute-values .form-check-label:hover {
    opacity: 0.8;
}

.attribute-values .form-check-input:checked + .form-check-label {
    background-color: #f5c000 !important;
    color: white;
}

.attribute-values .form-check-input:not(:checked) + .form-check-label {
    background-color: #6c757d !important;
    color: white;
}

.attribute-values .form-check-input:checked + .form-check-label.bg-primary {
    background-color: #f5c000 !important;
}

.attribute-values .form-check-input:not(:checked) + .form-check-label.bg-primary {
    background-color: #6c757d !important;
}

.attribute-values .form-check-input:checked + .form-check-label.bg-secondary {
    background-color: #f5c000 !important;
}

.attribute-values .form-check-input:not(:checked) + .form-check-label.bg-secondary {
    background-color: #6c757d !important;
}

/* Select All / Deselect All buttons */
.select-all-values-btn,
.deselect-all-values-btn {
    margin-left: 0.25rem;
}

.form-label .float-end {
    margin-top: -0.25rem;
}

.variants-table-container .variant-field--sku {
    min-width: 160px;
}

.variants-table-container .variant-field-group {
    display: flex;
    flex-wrap: nowrap;
}

.variants-table-container .variant-field-group .input-group-text {
    flex: 0 0 auto;
    width: 38px;
    justify-content: center;
}

.variants-table-container .variant-field-group .form-control {
    min-width: 130px;
    flex: 1 1 auto;
}

.variants-table-container .variant-field--stock {
    min-width: 100px;
}

.variants-table-container .variant-field {
    width: 100%;
}

.variant-image-thumb {
    width: 80px;
    height: 80px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    overflow: hidden;
}

.variant-image-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.5rem;
}

.measurement-rows-container .measurement-row {
    background-color: #f8f9fa;
}

.measurement-rows-container .measurement-row:last-child {
    margin-bottom: 0;
}

.icon-spin {
    display: inline-block;
    animation: icon-spin 1s linear infinite;
}

@keyframes icon-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>

<!-- Include Pickr Color Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>

<script>
// Initialize Pickr color pickers for variants
window.variantColorPickers = {};

function initializeVariantColorPicker(pickerId, buttonElement, hiddenInput, initialColor) {
    // Check if button element exists and is in the DOM
    const $buttonElement = $(buttonElement);
    if (!$buttonElement.length || !$buttonElement.parent().length) {
        console.warn('Button element not in DOM yet, cannot initialize Pickr');
        return null;
    }
    
    // Destroy existing picker if any
    if (window.variantColorPickers[pickerId]) {
        try {
            window.variantColorPickers[pickerId].destroy();
        } catch(e) {
            console.warn('Error destroying existing picker:', e);
        }
    }
    
    // Set button background
    $buttonElement.css({
        'backgroundColor': initialColor || '#000000',
        'width': '100%',
        'height': '38px',
        'border': '1px solid #ced4da',
        'borderRadius': '0.375rem',
        'cursor': 'pointer'
    });
    
    try {
        // Initialize Pickr
        const pickr = Pickr.create({
            el: buttonElement,
            theme: 'classic',
            default: initialColor || '#000000',
            swatches: [
                'rgba(244, 67, 54, 1)', 'rgba(233, 30, 99, 1)', 'rgba(156, 39, 176, 1)',
                'rgba(103, 58, 183, 1)', 'rgba(63, 81, 181, 1)', 'rgba(33, 150, 243, 1)',
                'rgba(3, 169, 244, 1)', 'rgba(0, 188, 212, 1)', 'rgba(0, 150, 136, 1)',
                'rgba(76, 175, 80, 1)', 'rgba(139, 195, 74, 1)', 'rgba(205, 220, 57, 1)',
                'rgba(255, 235, 59, 1)', 'rgba(255, 193, 7, 1)', 'rgba(255, 152, 0, 1)',
                'rgba(255, 87, 34, 1)', 'rgba(121, 85, 72, 1)', 'rgba(158, 158, 158, 1)',
                'rgba(96, 125, 139, 1)', 'rgba(0, 0, 0, 1)', 'rgba(255, 255, 255, 1)'
            ],
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: true,
                    hsla: true,
                    hsva: true,
                    cmyk: true,
                    input: true,
                    clear: true,
                    save: true
                }
            }
        });
        
        pickr.on('change', (color) => {
            const hexColor = color.toHEXA().toString();
            const $hiddenInput = $(hiddenInput);
            if ($hiddenInput.length) {
                $hiddenInput.val(hexColor);
            }
            $buttonElement.css('backgroundColor', hexColor);
        });
        
        window.variantColorPickers[pickerId] = pickr;
        return pickr;
    } catch(error) {
        console.error('Error initializing Pickr:', error);
        return null;
    }
}

// Initialize Pickr for dynamically created color inputs
$(document).ready(function() {
    // Use event delegation for dynamically created color picker buttons
    $(document).on('click', '.color-picker-btn:not([data-pickr-initialized])', function(e) {
        const $btn = $(this);
        if (!$btn.data('pickrInitialized')) {
            const colorInputId = $btn.data('colorInput');
            const $hiddenInput = $('#' + colorInputId);
            if ($hiddenInput.length && $btn.parent().length) {
                $btn.data('pickrInitialized', 'true');
                const initialColor = $hiddenInput.val() || '#000000';
                initializeVariantColorPicker(colorInputId, $btn[0], $hiddenInput[0], initialColor);
            }
        }
    });
    
    // Also initialize any existing color pickers when attribute selectors are created
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const $node = $(node);
                    const $colorPickerBtns = $node.find('.color-picker-btn:not([data-pickr-initialized])');
                    $colorPickerBtns.each(function() {
                        const $btn = $(this);
                        // Only initialize if button is in DOM
                        if ($btn.parent().length) {
                            const colorInputId = $btn.data('colorInput');
                            const $hiddenInput = $('#' + colorInputId);
                            if ($hiddenInput.length) {
                                $btn.data('pickrInitialized', 'true');
                                const initialColor = $hiddenInput.val() || '#000000';
                                initializeVariantColorPicker(colorInputId, $btn[0], $hiddenInput[0], initialColor);
                            }
                        }
                    });
                }
            });
        });
    });
    
    // Observe the document body for dynamically added elements
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Final initialization: Ensure default variant is created and table is visible when no attributes selected
    setTimeout(function() {
        if (typeof getSelectedAttributeIds === 'function') {
            const selectedIds = getSelectedAttributeIds();
            const $variantsTableBody = $('#variantsTableBody');
            const $variantsTableContainer = $('#variantsTableContainer');
            const hasVariantsInTable = $variantsTableBody.length && $variantsTableBody.find('tr').length > 0;
            
            // Check if we have variants in the generatedVariants array (from the main scope)
            let hasVariants = false;
            try {
                hasVariants = typeof generatedVariants !== 'undefined' && Array.isArray(generatedVariants) && generatedVariants.length > 0;
            } catch(e) {
                hasVariants = false;
            }
            
            // Default variant creation removed per user request
            // If no attributes selected and no variants, hide the table
            if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
                if ($variantsTableContainer.length) {
                    $variantsTableContainer.css('display', 'none');
                }
            }
            
            // Ensure variants table is visible if we have variants or no attributes selected
            if ($variantsTableContainer.length) {
                if (hasVariants || hasVariantsInTable || selectedIds.length === 0) {
                    $variantsTableContainer.css('display', 'block');
                }
            }
        }
    }, 1200);
});

// Add CSS for highlights & details section
const highlightsStyles = `
<style>
.highlights-details-container {
    max-height: 500px;
    overflow-y: auto;
}

.heading-panel {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.heading-panel .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.heading-panel .card-body {
    background-color: #ffffff;
}

.bullet-point-row {
    padding: 0.25rem 0;
}

.bullet-point-row:last-child {
    margin-bottom: 0 !important;
}

.heading-input {
    font-weight: 500;
}

.bullet-point-input {
    font-size: 0.875rem;
}

.delete-heading-btn,
.delete-point-btn {
    flex-shrink: 0;
}

.bullet-points-container {
    min-height: 30px;
}

.add-point-inline-btn {
    border-left: 0;
    padding: 0.25rem 0.5rem;
}

.add-point-inline-btn:hover {
    z-index: 1;
}

.input-group .add-point-inline-btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
</style>
`;

// Wait for jQuery to be available before using it
(function() {
    function appendStyles() {
        if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
            $(document).ready(function() {
                $('head').append(highlightsStyles);
            });
        } else {
            // Retry after a short delay if jQuery is not yet loaded
            setTimeout(appendStyles, 50);
        }
    }
    appendStyles();
})();
</script>
