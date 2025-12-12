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
                            <small class="text-muted small mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Select one or more attributes to create product variants. If no attributes are selected, a default variant will be automatically created.
                            </small>
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
                                <p class="text-muted text-center">Please select attributes first to add variants</p>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-primary" id="addVariantBtn">
                                    <i class="fas fa-plus me-1"></i> Add Variant
                                </button>
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

{{-- Variant Edit Modal --}}
<div class="modal fade" id="variantEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Variant</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">MRP</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="variantPrice" name="variant_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Sell Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="variantSalePrice" name="variant_sale_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label form-label-sm mb-2">Discount Settings</label>
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
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Stock Quantity</label>
                            <input type="number" class="form-control form-control-sm" id="variantStockQuantity" name="variant_stock_quantity" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Stock Status</label>
                            <select class="form-select form-select-sm" id="variantStockStatus" name="variant_stock_status">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="on_backorder">On Backorder</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Low Stock Threshold</label>
                            <input type="number" class="form-control form-control-sm" id="variantLowStockThreshold" name="variant_low_stock_threshold" min="0" value="0">
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Alert when stock falls below this</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm d-block">Manage Stock</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="variantManageStock" name="variant_manage_stock" checked>
                                <label class="form-check-label" for="variantManageStock">Enable Stock Tracking</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
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
                            <small id="measurementHelpText" class="text-muted d-block mt-2">Select measurement attributes, choose appropriate units, and provide the values for this variant. Units are loaded from the <a href="{{ route('units.index') }}" target="_blank">Units module</a>.</small>
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
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                'images' => $variant->images->pluck('image_path')->map(function ($path) {
                    return $path;
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
            <form>
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

// Load existing attribute values from Master Data module
function loadExistingAttributeValues(attributeId, attributeType, attributeDiv, selectedAttributeValuesMap = {}) {
    const attributeKey = String(attributeId);
    
    // Check cache first
    if (attributeValuesCache.has(attributeKey)) {
        const cachedData = attributeValuesCache.get(attributeKey);
        populateAttributeValuesFromData(attributeId, attributeType, attributeDiv, cachedData, selectedAttributeValuesMap);
        return;
    }
    
    // Check if there's already an ongoing request for this attribute
    if (attributeValuesPromises.has(attributeKey)) {
        attributeValuesPromises.get(attributeKey).then(data => {
            populateAttributeValuesFromData(attributeId, attributeType, attributeDiv, data, selectedAttributeValuesMap);
        });
        return;
    }
    
    // Show loading indicator
    const valuesContainer = attributeDiv.querySelector('.attribute-values');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'text-muted small mb-2';
    loadingDiv.innerHTML = '<span class="icon-spin me-1" data-feather="loader"></span> Loading existing values...';
    valuesContainer.appendChild(loadingDiv);
    refreshFeatherIcons();
    
    // Create and cache the promise
    const fetchPromise = fetch(`{{ url('attributes') }}/${attributeId}/values`)
        .then(response => response.json())
        .then(data => {
            // Cache the result
            attributeValuesCache.set(attributeKey, data);
            // Remove from promises cache
            attributeValuesPromises.delete(attributeKey);
            return data;
        })
        .catch(error => {
            // Remove from promises cache on error
            attributeValuesPromises.delete(attributeKey);
            throw error;
        });
    
    // Store the promise
    attributeValuesPromises.set(attributeKey, fetchPromise);
    
    fetchPromise.then(data => {
            // Remove loading indicator
            loadingDiv.remove();
            populateAttributeValuesFromData(attributeId, attributeType, attributeDiv, data, selectedAttributeValuesMap);
        })
        .catch(error => {
            // Remove loading indicator on error
            loadingDiv.remove();
            console.error('Error loading existing attribute values:', error);
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-danger small mb-2';
            errorDiv.innerHTML = '<span data-feather="alert-triangle" class="me-1"></span>Error loading existing values.';
            valuesContainer.appendChild(errorDiv);
            refreshFeatherIcons();
        });
    refreshFeatherIcons();
}

// Helper function to populate attribute values from cached or fetched data
function populateAttributeValuesFromData(attributeId, attributeType, attributeDiv, data, selectedAttributeValuesMap = {}) {
    const attributeKey = String(attributeId);
    const valuesContainer = attributeDiv.querySelector('.attribute-values');
    
    if (data && data.length > 0) {
        console.log('Loading existing values for attribute', attributeId, ':', data);
        
        // Populate datalist for select type attributes
        if (attributeType === 'select' || attributeType === 'text') {
            const datalist = attributeDiv.querySelector('datalist');
            if (datalist) {
                datalist.innerHTML = '';
                data.forEach(value => {
                    const option = document.createElement('option');
                    option.value = value.value;
                    datalist.appendChild(option);
                });
            }
        }
        
        // Display existing values as badges with checkboxes
        data.forEach(value => {
            const valueTag = document.createElement('div');
            valueTag.className = 'form-check form-check-inline mb-2 me-2';
            valueTag.style.display = 'inline-block';
            
            let displayValue = value.value;
            if (attributeType === 'color') {
                displayValue = `<span style="background-color: ${value.value}; color: white; padding: 2px 6px; border-radius: 3px;">${value.value}</span>`;
            } else if (attributeType === 'image') {
                displayValue = `<span data-feather="image" class="me-1"></span>${value.value}`;
            }
            
            const checkboxId = `attr_${attributeId}_value_${value.value.replace(/[^a-zA-Z0-9]/g, '_')}`;
            valueTag.innerHTML = `
                <input class="form-check-input value-checkbox" type="checkbox" 
                       id="${checkboxId}" 
                       value="${value.value}" 
                       data-attribute-id="${attributeId}"
                       data-value="${value.value}">
                <label class="form-check-label badge bg-secondary" for="${checkboxId}">
                    ${displayValue} 
                </label>
            `;
            
            valuesContainer.appendChild(valueTag);

            const checkbox = valueTag.querySelector('.value-checkbox');
            if (selectedAttributeValuesMap[attributeKey] && selectedAttributeValuesMap[attributeKey].includes(value.value)) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
        
        // Add click handler for removing master data values (if needed)
        valuesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-close') && e.target.dataset.fromMaster === 'true') {
                e.target.closest('.form-check').remove();
            }
        });
    } else {
        // No existing values found
        const noValuesDiv = document.createElement('div');
        noValuesDiv.className = 'text-muted small mb-2';
        noValuesDiv.innerHTML = '<span data-feather="info" class="me-1"></span>No existing values found. Add new values below.';
        valuesContainer.appendChild(noValuesDiv);
        refreshFeatherIcons();
    }
}

// Helper functions for checkbox-based attribute selection
function getSelectedAttributeIds() {
    const container = document.getElementById('availableAttributesContainer');
    if (!container) return [];
    const checkboxes = container.querySelectorAll('.attribute-checkbox:checked');
    return Array.from(checkboxes).map(cb => String(cb.value));
}

function setSelectedAttributeIds(attributeIds) {
    const container = document.getElementById('availableAttributesContainer');
    if (!container) return;
    
    // Uncheck all first
    container.querySelectorAll('.attribute-checkbox').forEach(cb => {
        cb.checked = false;
    });
    
    // Check selected ones
    if (Array.isArray(attributeIds) && attributeIds.length > 0) {
        attributeIds.forEach(id => {
            const checkbox = container.querySelector(`.attribute-checkbox[value="${id}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
}

function getAttributeInfo(attributeId) {
    const container = document.getElementById('availableAttributesContainer');
    if (!container) return null;
    
    const checkbox = container.querySelector(`.attribute-checkbox[value="${attributeId}"]`);
    if (!checkbox) return null;
    
    const item = checkbox.closest('.attribute-checkbox-item');
    const label = item ? item.querySelector('label') : null;
    const labelText = label ? label.textContent.trim() : '';
    
    return {
        id: attributeId,
        type: checkbox.getAttribute('data-attribute-type') || '',
        description: checkbox.getAttribute('data-attribute-description') || '',
        name: labelText.replace(/\s*\([^)]*\)$/, '').trim()
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const availableAttributesContainer = document.getElementById('availableAttributesContainer');
    
    // Handle checkbox changes for attribute selection
    if (availableAttributesContainer) {
        // Function to handle checkbox change
        function handleAttributeCheckboxChange(checkbox) {
            const attributeId = String(checkbox.value);
            const isChecked = checkbox.checked;
            
            // Get all selected attribute IDs
            const allCheckboxes = availableAttributesContainer.querySelectorAll('.attribute-checkbox');
            const newSelectedIds = Array.from(allCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => String(cb.value));
            
            // Check if any deselected attribute is used in variants
            if (!isChecked) {
                const deselectedIds = selectedAttributeIds.filter(id => !newSelectedIds.includes(id));
                for (const deselectedId of deselectedIds) {
                    if (attributeIsUsedInVariants(deselectedId)) {
                        showToast('error', 'This attribute is used by existing variants. Remove or regenerate those variants before deselecting it.');
                        // Re-check the checkbox
                        checkbox.checked = true;
                        return;
                    }
                }
            }
            
            selectedAttributeIds = newSelectedIds;
            
            console.log('Selected attribute IDs (from checkbox handler):', selectedAttributeIds);
            console.log('About to call updateAttributeValuesConfig...');
            updateAttributeValuesConfig();
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
        }
        
        // Attach event handlers to all checkboxes using event delegation
        availableAttributesContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('attribute-checkbox')) {
                handleAttributeCheckboxChange(e.target);
            }
        });
    }
    
    // Initialize variants table visibility and default variant
    const variantsTableContainer = document.getElementById('variantsTableContainer');
    const variantsTableBody = document.getElementById('variantsTableBody');
    const bulkActions = document.getElementById('bulkActions');
    
    // Ensure variants table is visible by default (it was hidden before)
    if (variantsTableContainer) {
        variantsTableContainer.style.display = 'block';
    }
    
    // Trigger initial check for default variant after a short delay
    // Also call updateAttributeValuesConfig to ensure default variant is created
    setTimeout(function() {
        // Ensure table is visible
        if (variantsTableContainer) {
            variantsTableContainer.style.display = 'block';
        }
        
        // Check if updateAttributeValuesConfig exists and call it to trigger default variant creation
        if (typeof updateAttributeValuesConfig === 'function') {
            updateAttributeValuesConfig();
        }
        
        // Also directly check and create default variant if needed
        const selectedIds = getSelectedAttributeIds();
        let hasVariants = false;
        try {
            hasVariants = typeof generatedVariants !== 'undefined' && Array.isArray(generatedVariants) && generatedVariants.length > 0;
        } catch(e) {}
        
        const hasVariantsInTable = variantsTableBody && variantsTableBody.querySelectorAll('tr').length > 0;
        
        if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
            if (typeof createDefaultVariant === 'function') {
                setTimeout(function() {
                    createDefaultVariant();
                }, 100);
            } else if (typeof window.createDefaultVariant === 'function') {
                setTimeout(function() {
                    window.createDefaultVariant();
                }, 100);
            }
        }
    }, 300);
    const variantEditModalElement = document.getElementById('variantEditModal');
    const variantEditModal = variantEditModalElement ? new bootstrap.Modal(variantEditModalElement) : null;
    const measurementRowsContainer = document.getElementById('variantMeasurementRows');
    const measurementEmptyState = document.getElementById('variantMeasurementsEmptyState');
    const addMeasurementRowBtn = document.getElementById('addMeasurementRowBtn');
    const measurementDataElement = document.getElementById('variantMeasurementData');
    let measurementAttributes = [];
    if (measurementDataElement && measurementDataElement.dataset.measurementAttributes) {
        try {
            const parsed = JSON.parse(measurementDataElement.dataset.measurementAttributes);
            measurementAttributes = Array.isArray(parsed) ? parsed : [];
            console.log('Measurement attributes loaded:', measurementAttributes.length, measurementAttributes);
        } catch (e) {
            console.error('Error parsing measurement attributes:', e);
            measurementAttributes = [];
        }
    } else {
        console.warn('Measurement data element or attributes not found');
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
    const staticUnitsData = measurementDataElement && measurementDataElement.dataset.measurementUnits
        ? JSON.parse(measurementDataElement.dataset.measurementUnits)
        : [];
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
    const discountTypeSelect = document.getElementById('variantDiscountType');
    const discountValueInput = document.getElementById('variantDiscountValue');
    const discountActiveSelect = document.getElementById('variantDiscountActive'); // Changed from toggle to select
    const discountPrefixSpan = document.getElementById('variantDiscountPrefix');
    const mrpInput = document.getElementById('variantPrice');
    const variantSellPriceInput = document.getElementById('variantSalePrice');
    const variantStatusToggle = document.getElementById('variantStatusToggle');
    const variantManageImagesBtn = document.getElementById('variantManageImagesBtn');
    const variantImagesPreview = document.getElementById('variantImagesPreview');
    const bulkEditModalElement = document.getElementById('bulkEditModal');
    const bulkEditModal = bulkEditModalElement ? new bootstrap.Modal(bulkEditModalElement) : null;
    const bulkEditForm = bulkEditModalElement ? bulkEditModalElement.querySelector('form') : null;
    const bulkPriceValue = document.getElementById('bulkPriceValue');
    const bulkSalePriceValue = document.getElementById('bulkSalePriceValue');
    const bulkStatusValue = document.getElementById('bulkStatusValue');
    const bulkDiscountTypeValue = document.getElementById('bulkDiscountTypeValue');
    const bulkDiscountValue = document.getElementById('bulkDiscountValue');
    const bulkDiscountActiveValue = document.getElementById('bulkDiscountActiveValue');
    const bulkDiscountPrefix = document.getElementById('bulkDiscountPrefix');
    const existingVariantsElement = document.getElementById('existingVariantsPayload');
    const existingVariantsPayload = existingVariantsElement
        ? JSON.parse(existingVariantsElement.textContent || '[]')
        : [];
    const productFormElement = document.getElementById('productForm');
    let isEditMode = false;
    if (productFormElement) {
        isEditMode = productFormElement.dataset.isEdit === 'true';
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
                    if (measurementRowsContainer) {
                        const unitSelects = measurementRowsContainer.querySelectorAll('.measurement-unit');
                        unitSelects.forEach(select => {
                            const currentValue = select.value;
                            select.innerHTML = buildUnitOptionsHtml(currentValue);
                            select.value = currentValue;
                        });
                    }
                    
                    if (callback) callback();
                } else {
                    console.error('Failed to load units:', response);
                    unitsLoading = false;
                    if (callback) callback();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading units:', error);
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
        if (!measurementRowsContainer || !measurementEmptyState) {
            return;
        }

        const hasAttributes = measurementAttributes.length > 0;
        const hasUnits = unitsData.length > 0;
        const rowCount = measurementRowsContainer.querySelectorAll('.measurement-row').length;
        const measurementHelpText = document.getElementById('measurementHelpText');

        console.log('updateMeasurementEmptyState:', {
            hasAttributes,
            hasUnits,
            rowCount,
            measurementAttributesCount: measurementAttributes.length,
            unitsDataCount: unitsData.length
        });

        // If no attributes, show message but keep button enabled (will load on click)
        if (!hasAttributes) {
            measurementEmptyState.classList.remove('d-none');
            const linkHtml = "{{ route('attributes.index') }}" && "{{ route('attributes.index') }}" !== '#'
                ? `<a href="{{ route('attributes.index') }}" target="_blank">Create a numeric attribute</a>`
                : 'Create a numeric attribute';
            measurementEmptyState.innerHTML = `<div class="d-flex align-items-center"><span data-feather="info" class="text-primary me-2"></span><span>No measurement attributes configured. ${linkHtml} to capture variant measurements, or click "Add Measurement" to load existing numeric attributes.</span></div>`;
            refreshFeatherIcons();
            if (addMeasurementRowBtn) {
                addMeasurementRowBtn.disabled = false; // Keep button enabled
            }
            // Show help text to guide user
            if (measurementHelpText) {
                measurementHelpText.classList.remove('d-none');
            }
            return;
        }

        // If attributes exist but no units, show message but enable button (units will load on click)
        if (!hasUnits) {
            measurementEmptyState.classList.remove('d-none');
            const unitsLinkHtml = "{{ route('units.index') }}" && "{{ route('units.index') }}" !== '#'
                ? `<a href="{{ route('units.index') }}" target="_blank">Add units</a>`
                : 'Add units';
            measurementEmptyState.innerHTML = `<div class="d-flex align-items-center"><span data-feather="info" class="text-primary me-2"></span><span>No measurement units available. ${unitsLinkHtml} to continue.</span></div>`;
            refreshFeatherIcons();
            // Enable button even if units aren't loaded - they'll load when clicked
            if (addMeasurementRowBtn) {
                addMeasurementRowBtn.disabled = false;
            }
            // Show help text when attributes exist
            if (measurementHelpText) {
                measurementHelpText.classList.remove('d-none');
            }
            return;
        }

        // Both attributes and units exist
        if (addMeasurementRowBtn) {
            addMeasurementRowBtn.disabled = false;
        }

        if (rowCount === 0) {
            measurementEmptyState.classList.remove('d-none');
            measurementEmptyState.innerHTML = '<div class="d-flex align-items-center"><span data-feather="info" class="text-primary me-2"></span><span>No measurements added yet. Use the "Add Measurement" button to define variant-specific measurements.</span></div>';
            refreshFeatherIcons();
            // Show help text when ready to add measurements
            if (measurementHelpText) {
                measurementHelpText.classList.remove('d-none');
            }
        } else {
            measurementEmptyState.classList.add('d-none');
            // Hide help text when measurements exist
            if (measurementHelpText) {
                measurementHelpText.classList.add('d-none');
            }
        }
    }

    function createMeasurementRow(measurement = {}) {
        if (!measurementRowsContainer) {
            return;
        }

        measurementRowCounter += 1;
        const attributeOptions = ['<option value="">Select attribute</option>'];
        measurementAttributes.forEach(attribute => {
            const selected = measurement.attribute_id && String(attribute.id) === String(measurement.attribute_id) ? 'selected' : '';
            const description = attribute.description ? ` (${attribute.description})` : '';
            attributeOptions.push(`<option value="${attribute.id}" ${selected}>${escapeHtml(attribute.name)}${escapeHtml(description)}</option>`);
        });

        const row = document.createElement('div');
        row.className = 'measurement-row border rounded p-3 mb-2 bg-body-tertiary';
        row.dataset.index = measurementRowCounter;
        row.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Attribute <span class="text-danger">*</span></label>
                    <select class="form-select measurement-attribute">
                        ${attributeOptions.join('')}
                    </select>
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
        `;

        const removeBtn = row.querySelector('.remove-measurement-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                row.remove();
                updateMeasurementEmptyState();
            });
        }

        const unitSelect = row.querySelector('.measurement-unit');
        if (unitSelect) {
            if (measurement.unit_id && unitsMap[String(measurement.unit_id)]) {
                unitSelect.value = String(measurement.unit_id);
            } else {
                const defaultUnit = findDefaultUnitForType(getDefaultUnitTypeForAttribute(measurement.attribute_id));
                if (defaultUnit) {
                    unitSelect.value = String(defaultUnit.id);
                }
            }
        }

        measurementRowsContainer.appendChild(row);
        updateMeasurementEmptyState();
        refreshFeatherIcons();
    }

    function renderMeasurementsInModal(measurements = []) {
        if (!measurementRowsContainer) {
            return;
        }

        measurementRowsContainer.innerHTML = '';

        if (!measurementAttributes.length) {
            updateMeasurementEmptyState();
            return;
        }

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
        if (!measurementRowsContainer) {
            return [];
        }

        const rows = measurementRowsContainer.querySelectorAll('.measurement-row');
        const measurements = [];

        rows.forEach(row => {
            const attributeSelect = row.querySelector('.measurement-attribute');
            const unitSelect = row.querySelector('.measurement-unit');
            const valueInput = row.querySelector('.measurement-value');

            if (!attributeSelect || !valueInput) {
                return;
            }

            const attributeId = attributeSelect.value ? parseInt(attributeSelect.value, 10) : null;
            if (!attributeId || !measurementAttributeMap[String(attributeId)]) {
                return;
            }

            const rawValue = valueInput.value;
            if (rawValue === '' || rawValue === null) {
                return;
            }

            const numericValue = parseFloat(rawValue);
            if (!Number.isFinite(numericValue)) {
                return;
            }

            const attribute = measurementAttributeMap[String(attributeId)];
            const unitId = unitSelect && unitSelect.value ? parseInt(unitSelect.value, 10) : null;
            const unit = unitId ? unitsMap[String(unitId)] : null;

            measurements.push({
                attribute_id: attributeId,
                attribute_name: attribute.name,
                attribute_slug: attribute.slug,
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

        const weightInput = row.querySelector('[data-variant-weight-input]');
        if (weightInput) {
            weightInput.value = legacy.weight !== null ? legacy.weight : '';
        }

        const lengthInput = row.querySelector('[data-variant-length-input]');
        if (lengthInput) {
            lengthInput.value = legacy.length !== null ? legacy.length : '';
        }

        const widthInput = row.querySelector('[data-variant-width-input]');
        if (widthInput) {
            widthInput.value = legacy.width !== null ? legacy.width : '';
        }

        const heightInput = row.querySelector('[data-variant-height-input]');
        if (heightInput) {
            heightInput.value = legacy.height !== null ? legacy.height : '';
        }

        const diameterInput = row.querySelector('[data-variant-diameter-input]');
        if (diameterInput) {
            diameterInput.value = legacy.diameter !== null ? legacy.diameter : '';
        }
    }

    function updateRowMeasurements(row, measurements) {
        if (!row) {
            return;
        }

        const displayContainer = row.querySelector('[data-variant-measurements-display]');
        const hiddenInput = row.querySelector('[data-variant-measurements-input]');

        if (displayContainer) {
            displayContainer.innerHTML = buildMeasurementDisplayHtml(measurements);
        }

        if (hiddenInput) {
            hiddenInput.value = measurements && measurements.length ? JSON.stringify(measurements) : '[]';
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
            const input = row.querySelector(item.selector);
            if (!input || input.value === '' || input.value === null) {
                return;
            }
            const numericValue = Number(input.value);
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

        const hiddenInput = row.querySelector('[data-variant-measurements-input]');
        if (hiddenInput && hiddenInput.value) {
            try {
                const parsed = JSON.parse(hiddenInput.value);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            } catch (error) {
                console.error('Failed to parse measurement JSON', error);
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
                    console.error('Failed to load numeric attributes:', response);
                    showToast('error', 'Failed to load measurement attributes. Please create numeric attributes first.');
                    if (callback) callback();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading numeric attributes:', error);
                showToast('error', 'Error loading measurement attributes. Please check your connection and try again.');
                if (callback) callback();
            }
        });
    }

    if (addMeasurementRowBtn) {
        addMeasurementRowBtn.addEventListener('click', function() {
            // Always load attributes first (if not already loaded), then units, then create row
            loadNumericAttributes(function() {
                // Load units if not already loaded
                if (!unitsLoaded && !unitsLoading) {
                    loadUnitsFromModule(function() {
                        if (unitsData.length > 0 && measurementAttributes.length > 0) {
                            createMeasurementRow();
                        } else if (measurementAttributes.length === 0) {
                            showToast('info', 'No numeric attributes found. Please create numeric attributes first.');
                            updateMeasurementEmptyState();
                        } else {
                            showToast('info', 'No units found. Please add units first.');
                            updateMeasurementEmptyState();
                        }
                    });
                } else if (unitsLoaded && unitsData.length > 0) {
                    if (measurementAttributes.length > 0) {
                        createMeasurementRow();
                    } else {
                        showToast('info', 'No numeric attributes found. Please create numeric attributes first.');
                        updateMeasurementEmptyState();
                    }
                } else {
                    // Units not loaded or empty
                    loadUnitsFromModule(function() {
                        if (unitsData.length > 0 && measurementAttributes.length > 0) {
                            createMeasurementRow();
                        } else {
                            updateMeasurementEmptyState();
                        }
                    });
                }
            });
        });
    }

    if (variantEditModalElement) {
        variantEditModalElement.addEventListener('hidden.bs.modal', function() {
            activeVariantRow = null;
            syncModalImagePreview(null, []);
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

    if (discountTypeSelect) {
        discountTypeSelect.addEventListener('change', function() {
            handleDiscountStateChange();
        });
    }

    if (discountValueInput) {
        discountValueInput.addEventListener('input', function() {
            if (discountActiveSelect && discountActiveSelect.value === '0' && discountTypeSelect && discountTypeSelect.value) {
                discountActiveSelect.value = '1';
            }
            calculateVariantSellPrice();
        });
    }

    if (discountActiveSelect) {
        discountActiveSelect.addEventListener('change', function() {
            calculateVariantSellPrice();
        });
    }

    if (mrpInput) {
        mrpInput.addEventListener('input', calculateVariantSellPrice);
    }

    if (variantStatusToggle) {
        variantStatusToggle.addEventListener('change', function() {
            updateVariantStatusToggleLabel();
        });
    }

    if (variantManageImagesBtn) {
        variantManageImagesBtn.addEventListener('click', function() {
            if (!activeVariantRow) {
                showToast('error', 'Select a variant row before managing images.');
                return;
            }
            const rowImageInput = activeVariantRow.querySelector('.variant-image-input');
            if (rowImageInput) {
                rowImageInput.click();
            } else {
                showToast('error', 'Image uploader not available for this variant.');
            }
        });
    }

    // Removed search input handlers - Select2 handles search automatically
    refreshFeatherIcons();

    // Note: Variants section visibility is controlled by the dynamic form's section visibility logic
    
    // Function to create a default variant when no attributes are selected
    function createDefaultVariant() {
        const productNameEl = document.getElementById('productName');
        const productName = productNameEl ? productNameEl.value : 'Product';
        const defaultSku = (productName ? productName.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 15) : 'PROD') + '-DEFAULT-' + Date.now().toString().slice(-6);
        
        const defaultVariant = {
            id: null,
            name: productName || 'Default Variant',
            sku: defaultSku,
            barcode: '',
            price: '0.00',
            sale_price: '',
            cost_price: '',
            low_stock_threshold: '',
            stock_quantity: 0,
            stock_status: 'in_stock',
            manage_stock: true,
            is_active: true,
            discount_type: '',
            discount_value: '',
            discount_active: false,
            attributes: {},
            weight: '',
            length: '',
            width: '',
            height: '',
            diameter: '',
            measurements: [],
            images: []
        };
        
        if (!Array.isArray(generatedVariants)) {
            generatedVariants = [];
        }
        
        // Only add if we don't already have a default variant (one without attributes)
        const hasDefault = generatedVariants.some(v => 
            !v.attributes || Object.keys(v.attributes || {}).length === 0
        );
        
        if (!hasDefault) {
            generatedVariants.push(defaultVariant);
            displayVariants();
        }
    }
    
    // Make function accessible globally
    window.createDefaultVariant = createDefaultVariant;
    
    // Initialize for edit mode if product exists and has variants
    if (Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0) {
        loadExistingVariants(existingVariantsPayload);
    } else {
        // Auto-generate default variant when no attributes are selected and no existing variants
        // Check immediately and also after a short delay to ensure all initialization is complete
        function checkAndCreateDefaultVariant() {
            const selectedIds = getSelectedAttributeIds();
            const hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
            const hasVariantsInTable = variantsTableBody && variantsTableBody.querySelectorAll('tr').length > 0;
            
            if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
                createDefaultVariant();
            }
        }
        
        // Try after delays to ensure all functions are defined
        setTimeout(function() {
            checkAndCreateDefaultVariant();
        }, 100);
        setTimeout(checkAndCreateDefaultVariant, 500);
        setTimeout(checkAndCreateDefaultVariant, 1000);
        
        // Also trigger updateAttributeValuesConfig which will create default variant
        setTimeout(function() {
            if (typeof updateAttributeValuesConfig === 'function') {
                updateAttributeValuesConfig();
            }
        }, 800);
    }
    
    // Also ensure default variant is created on initial page load
    // This runs after all script initialization
    setTimeout(function() {
        const selectedIds = getSelectedAttributeIds();
        const hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
        const hasVariantsInTable = variantsTableBody && variantsTableBody.querySelectorAll('tr').length > 0;
        
        if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable && typeof createDefaultVariant === 'function') {
            createDefaultVariant();
        }
    }, 1500);

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

    // Checkbox handlers are already set up in DOMContentLoaded event listener above
    // No need for additional Select2 handlers

    // Removed moveAttributeToSelected and moveAttributeToAvailable - attributes now stay in place with visual feedback

    function ensureAttributeSelectionFromVariants() {
        if (!Array.isArray(generatedVariants) || !generatedVariants.length) {
            return;
        }

        const requiredAttributeIds = new Set();
        generatedVariants.forEach(variant => {
            const normalized = normalizeAttributesPayload(variant.attributes || variant);
            Object.keys(normalized).forEach(id => {
                requiredAttributeIds.add(String(id));
            });
        });

        if (!requiredAttributeIds.size) {
            return;
        }

        let attributesChanged = false;

        requiredAttributeIds.forEach(attributeId => {
            if (!selectedAttributeIds.includes(attributeId)) {
                selectedAttributeIds.push(attributeId);
                attributesChanged = true;
            }

            // Select checkbox
            const container = document.getElementById('availableAttributesContainer');
            if (container) {
                const checkbox = container.querySelector(`.attribute-checkbox[value="${attributeId}"]`);
                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                    // Trigger change event to update selectedAttributeIds
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    attributesChanged = true;
                }
            }
        });

        if (attributesChanged) {
            updateAttributeValuesConfig();
        } else if (selectedAttributeIds.length) {
            updateAttributeValuesConfig();
        }
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

        selectedValues.forEach(attributeId => {
            const id = String(attributeId);
            if (!selectedAttributeIds.includes(id)) {
                selectedAttributeIds.push(id);
                attributesChanged = true;
            }
        });

        if (attributesChanged || selectedAttributeIds.length) {
            updateAttributeValuesConfig();
        }
    }

    function preserveUnsatisfiedVariants() {
        // Only preserve attributes if we're in edit mode and have existing variants
        // Don't auto-select attributes for new products
        if (!Array.isArray(generatedVariants) || !generatedVariants.length) {
            return;
        }

        // Only preserve if we're loading existing variants (edit mode)
        const isEditMode = Array.isArray(existingVariantsPayload) && existingVariantsPayload.length > 0;
        if (!isEditMode) {
            return;
        }

        const requiredAttributeMap = new Map();

        generatedVariants.forEach(variant => {
            const normalizedAttributes = normalizeAttributesPayload(variant.attributes || variant);
            Object.entries(normalizedAttributes).forEach(([attributeId, value]) => {
                if (attributeId === undefined || attributeId === null) {
                    return;
                }
                const key = String(attributeId);
                if (!requiredAttributeMap.has(key)) {
                    requiredAttributeMap.set(key, new Set());
                }
                if (value !== undefined && value !== null && String(value).trim() !== '') {
                    requiredAttributeMap.get(key).add(String(value));
                }
            });
        });

        requiredAttributeMap.forEach((valueSet, attributeId) => {
            if (!selectedAttributeIds.includes(attributeId)) {
                selectedAttributeIds.push(attributeId);
                // Select checkbox
                const container = document.getElementById('availableAttributesContainer');
                if (container) {
                    const checkbox = container.querySelector(`.attribute-checkbox[value="${attributeId}"]`);
                    if (checkbox && !checkbox.checked) {
                        checkbox.checked = true;
                        // Trigger change event to update selectedAttributeIds
                        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }

            if (!Array.isArray(attributeValues[attributeId])) {
                attributeValues[attributeId] = [];
            }
            if (!Array.isArray(selectedAttributeValues[attributeId])) {
                selectedAttributeValues[attributeId] = [];
            }

            valueSet.forEach(value => {
                if (!attributeValues[attributeId].includes(value)) {
                    attributeValues[attributeId].push(value);
                }
                if (!selectedAttributeValues[attributeId].includes(value)) {
                    selectedAttributeValues[attributeId].push(value);
                }
            });
        });
    }

    function updateAttributeValuesConfig() {
        preserveUnsatisfiedVariants();

        const addVariantForm = document.getElementById('addVariantForm');
        const variantAttributeSelectors = document.getElementById('variantAttributeSelectors');
        
        if (!addVariantForm || !variantAttributeSelectors) {
            return;
        }

        const shouldShowForm = selectedAttributeIds.length > 0;
        
        if (shouldShowForm) {
            addVariantForm.style.display = 'block';
            
            // Destroy existing Select2 instances before clearing
            variantAttributeSelectors.querySelectorAll('.variant-attribute-selector').forEach(select => {
                const $select = $(select);
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
            
            variantAttributeSelectors.innerHTML = '';

            // Create dropdown selectors for each selected attribute
            selectedAttributeIds.forEach(attributeId => {
                // Get attribute info using helper function
                const attrInfo = getAttributeInfo(attributeId);
                if (!attrInfo) return;
                
                const attributeType = attrInfo.type;
                const attributeName = attrInfo.name;
                
                // Create dropdown for this attribute
                const selectorDiv = document.createElement('div');
                selectorDiv.className = 'col-md-12 mb-3';
                selectorDiv.innerHTML = `
                  <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="form-label mb-0">
        ${escapeHtml(attributeName)}
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

                    <select class="form-select variant-attribute-selector" data-attribute-id="${attributeId}" data-attribute-type="${attributeType}">
                        <option value="">-- Select ${escapeHtml(attributeName)} --</option>
                    </select>
                `;
                
                variantAttributeSelectors.appendChild(selectorDiv);
                
                // Load attribute values for this dropdown (will initialize Select2)
                loadAttributeValuesForSelector(attributeId, attributeType, selectorDiv.querySelector('select'));
                
                // Attach click handler for "Add Value" button
                const addValueBtn = selectorDiv.querySelector('.add-attribute-value-btn');
                if (addValueBtn) {
                    addValueBtn.addEventListener('click', function() {
                        openAddAttributeValueModal(attributeId, attributeType, attributeName);
                    });
                }
            });
        } else {
            // Destroy Select2 instances when hiding the form
            variantAttributeSelectors.querySelectorAll('.variant-attribute-selector').forEach(select => {
                const $select = $(select);
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
            addVariantForm.style.display = 'none';
        }

        // Show variants table if there are existing variants OR if no attributes are selected (default variant)
        const hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
        const noAttributesSelected = selectedAttributeIds.length === 0;
        
        if (hasVariants) {
            if (variantsTableContainer) {
                variantsTableContainer.style.display = 'block';
            }
            if (bulkActions) {
                bulkActions.style.display = 'block';
            }
        } else if (noAttributesSelected) {
            // Auto-create default variant when no attributes selected
            // Ensure table is visible first
            if (variantsTableContainer) {
                variantsTableContainer.style.display = 'block';
            }
            
            // Then create default variant
            if (typeof createDefaultVariant === 'function') {
                createDefaultVariant();
            } else if (typeof window.createDefaultVariant === 'function') {
                window.createDefaultVariant();
            } else {
                // Fallback: Show table even if function doesn't exist
                if (variantsTableContainer) {
                    variantsTableContainer.style.display = 'block';
                }
            }
        } else {
            // Hide table when attributes are selected but no variants generated yet
            if (variantsTableContainer) {
                variantsTableContainer.style.display = 'none';
            }
            if (bulkActions) {
                bulkActions.style.display = 'none';
            }
        }

        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    }
    
    // Call updateAttributeValuesConfig on page load to initialize default variant if needed
    // This ensures the default variant is created when no attributes are selected
    setTimeout(function() {
        if (typeof updateAttributeValuesConfig === 'function') {
            updateAttributeValuesConfig();
        }
    }, 100);

    // Load attribute values for dropdown selector and initialize Select2
    function loadAttributeValuesForSelector(attributeId, attributeType, selectElement) {
        const attributeKey = String(attributeId);
        
        // Check cache first
        if (attributeValuesCache.has(attributeKey)) {
            const cachedData = attributeValuesCache.get(attributeKey);
            populateSelectorFromData(attributeId, attributeType, selectElement, cachedData);
            return;
        }
        
        // Check if there's already an ongoing request for this attribute
        if (attributeValuesPromises.has(attributeKey)) {
            attributeValuesPromises.get(attributeKey).then(data => {
                populateSelectorFromData(attributeId, attributeType, selectElement, data);
            });
            return;
        }
        
        // Create and cache the promise
        const fetchPromise = fetch(`{{ url('attributes') }}/${attributeId}/values`)
            .then(response => response.json())
            .then(data => {
                // Cache the result
                attributeValuesCache.set(attributeKey, data);
                // Remove from promises cache
                attributeValuesPromises.delete(attributeKey);
                return data;
            })
            .catch(error => {
                // Remove from promises cache on error
                attributeValuesPromises.delete(attributeKey);
                throw error;
            });
        
        // Store the promise
        attributeValuesPromises.set(attributeKey, fetchPromise);
        
        fetchPromise.then(data => {
            populateSelectorFromData(attributeId, attributeType, selectElement, data);
        }).catch(error => {
            console.error('Error loading attribute values for selector:', error);
        });
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
        console.log('Opening modal for attribute:', { attributeId, attributeType, attributeName });
        
        // Try to find modal element - check multiple ways
        let modalElement = document.getElementById('addAttributeValueModal');
        
        // If not found, try querySelector
        if (!modalElement) {
            modalElement = document.querySelector('#addAttributeValueModal');
        }
        
        // If still not found, try to find it in the body
        if (!modalElement) {
            const allModals = document.querySelectorAll('.modal');
            console.log('Found modals:', allModals.length);
            for (let i = 0; i < allModals.length; i++) {
                if (allModals[i].id === 'addAttributeValueModal') {
                    modalElement = allModals[i];
                    break;
                }
            }
        }
        
        if (!modalElement) {
            console.error('Modal element not found after multiple attempts');
            console.log('Document ready state:', document.readyState);
            console.log('All modals in document:', document.querySelectorAll('.modal').length);
            alert('Modal not found. The page may not be fully loaded. Please refresh and try again.');
            return;
        }
        
        console.log('Modal element found:', modalElement);
        
        // Check modal content - maybe form is in modal-body
        const modalBody = modalElement.querySelector('.modal-body');
        console.log('Modal body found:', !!modalBody);
        if (modalBody) {
            console.log('Modal body innerHTML length:', modalBody.innerHTML ? modalBody.innerHTML.length : 0);
            console.log('Forms in modal body:', modalBody.querySelectorAll('form').length);
        }
        
        // Now find form and other elements - search in modal-body first
        let form = null;
        if (modalBody) {
            form = modalBody.querySelector('#addAttributeValueForm') || modalBody.querySelector('form');
        }
        if (!form) {
            form = modalElement.querySelector('#addAttributeValueForm') || modalElement.querySelector('form');
        }
        if (!form) {
            form = document.getElementById('addAttributeValueForm');
        }
        
        // Check each element individually and provide specific error messages
        if (!form) {
            console.error('Form element (addAttributeValueForm) not found');
            console.log('Modal element:', modalElement);
            console.log('Modal innerHTML length:', modalElement.innerHTML ? modalElement.innerHTML.length : 0);
            console.log('Modal body:', modalBody);
            if (modalBody) {
                console.log('Modal body HTML:', modalBody.innerHTML.substring(0, 1000));
            }
            console.log('Forms in modal:', modalElement.querySelectorAll('form').length);
            console.log('All forms in document:', document.querySelectorAll('form').length);
            
            // Try to create the form if modal body exists
            if (modalBody) {
                console.log('Creating form element in modal body...');
                form = document.createElement('form');
                form.id = 'addAttributeValueForm';
                form.innerHTML = `
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
                `;
                modalBody.innerHTML = ''; // Clear any existing content
                modalBody.appendChild(form);
                console.log('Form created and added to modal body');
            } else {
                alert('Form element not found and modal body is missing. Please refresh the page and try again.');
                return;
            }
        }
        
        // Get or create modal instance first (before querying wrappers)
        let modal = bootstrap.Modal.getInstance(modalElement);
        if (!modal) {
            modal = new bootstrap.Modal(modalElement);
        }
        
        // Now query for wrappers AFTER form is confirmed to exist
        // Search within form first (most reliable), then modal, then document
        let valueInputWrapper = form.querySelector('#modalValueInputWrapper');
        if (!valueInputWrapper) {
            valueInputWrapper = modalElement.querySelector('#modalValueInputWrapper');
        }
        if (!valueInputWrapper) {
            valueInputWrapper = document.getElementById('modalValueInputWrapper');
        }
        
        let colorInputWrapper = form.querySelector('#modalColorInputWrapper');
        if (!colorInputWrapper) {
            colorInputWrapper = modalElement.querySelector('#modalColorInputWrapper');
        }
        if (!colorInputWrapper) {
            colorInputWrapper = document.getElementById('modalColorInputWrapper');
        }
        
        if (!valueInputWrapper) {
            console.error('Value input wrapper (modalValueInputWrapper) not found');
            console.log('Form:', form);
            console.log('Form innerHTML:', form ? form.innerHTML.substring(0, 500) : 'no form');
            alert('Value input wrapper not found. Please refresh the page and try again.');
            return;
        }
        
        if (!colorInputWrapper) {
            console.warn('Color input wrapper (modalColorInputWrapper) not found - this is optional');
        }
        
        console.log('All required elements found, proceeding...');
        
        // Set attribute info - search within form first, then document
        const attributeIdInput = form.querySelector('#modalAttributeId') || document.getElementById('modalAttributeId');
        const attributeTypeInput = form.querySelector('#modalAttributeType') || document.getElementById('modalAttributeType');
        const attributeNameInput = form.querySelector('#modalAttributeName') || document.getElementById('modalAttributeName');
        
        if (attributeIdInput) attributeIdInput.value = attributeId;
        if (attributeTypeInput) attributeTypeInput.value = attributeType;
        if (attributeNameInput) attributeNameInput.value = attributeName;
        
        // Reset form - but preserve the hidden inputs we just set
        if (form) {
            // Get current values before reset
            const currentAttributeId = attributeIdInput ? attributeIdInput.value : '';
            const currentAttributeType = attributeTypeInput ? attributeTypeInput.value : '';
            const currentAttributeName = attributeNameInput ? attributeNameInput.value : '';
            
            form.reset();
            
            // Restore the values after reset
            if (attributeIdInput) attributeIdInput.value = currentAttributeId || attributeId;
            if (attributeTypeInput) attributeTypeInput.value = currentAttributeType || attributeType;
            if (attributeNameInput) attributeNameInput.value = currentAttributeName || attributeName;
        }
        
        const colorCodeInput = form.querySelector('#modalColorCode') || document.getElementById('modalColorCode');
        const colorCodeBtn = document.getElementById('modalColorCodeBtn');
        if (colorCodeInput) {
            colorCodeInput.value = '#000000';
        }
        if (colorCodeBtn) {
            colorCodeBtn.style.backgroundColor = '#000000';
            // Reinitialize Pickr if it exists, otherwise wait for modal to be shown
            if (window.variantColorPickers && window.variantColorPickers['modalColorCode']) {
                try {
                    window.variantColorPickers['modalColorCode'].setColor('#000000');
                } catch(e) {
                    console.warn('Error setting color on existing picker:', e);
                }
            }
            // Will be initialized when modal is shown (handled below)
        }
        
        // Clear and create input field based on attribute type
        // Make sure we're clearing the wrapper, not duplicating
        if (valueInputWrapper) {
            valueInputWrapper.innerHTML = '';
        }
        const label = document.createElement('label');
        label.className = 'form-label';
        label.setAttribute('for', 'modalValueInput');
        label.textContent = 'Value *';
        
        let input;
        if (attributeType === 'boolean') {
            input = document.createElement('select');
            input.className = 'form-select';
            input.id = 'modalValueInput';
            input.name = 'value';
            input.innerHTML = `
                <option value="">-- Select Option --</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            `;
        } else if (attributeType === 'number') {
            input = document.createElement('input');
            input.type = 'number';
            input.className = 'form-control';
            input.id = 'modalValueInput';
            input.name = 'value';
            input.step = '0.01';
            input.placeholder = 'Enter numeric value';
            input.required = true;
        } else if (attributeType === 'date') {
            input = document.createElement('input');
            input.type = 'date';
            input.className = 'form-control';
            input.id = 'modalValueInput';
            input.name = 'value';
            input.required = true;
        } else if (attributeType === 'color') {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = 'modalValueInput';
            input.name = 'value';
            input.placeholder = 'Enter color name (e.g., Red, Blue)';
            input.required = true;
            if (colorInputWrapper) {
                colorInputWrapper.classList.remove('d-none');
                // Pickr will be initialized after modal is shown (handled below)
            }
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = 'modalValueInput';
            input.name = 'value';
            input.placeholder = 'Enter value';
            input.required = true;
        }
        
        if (attributeType !== 'color' && colorInputWrapper) {
            colorInputWrapper.classList.add('d-none');
        }
        
        // Verify the wrapper is still valid before appending
        if (!valueInputWrapper || !valueInputWrapper.parentNode) {
            console.error('Value input wrapper is no longer valid');
            // Re-query if needed
            valueInputWrapper = form.querySelector('#modalValueInputWrapper');
            if (!valueInputWrapper) {
                alert('Error: Could not find value input wrapper. Please refresh the page.');
                return;
            }
        }
        
        valueInputWrapper.appendChild(label);
        valueInputWrapper.appendChild(input);
        
        // Initialize Pickr for color type after modal is shown
        if (attributeType === 'color' && colorInputWrapper) {
            // Wait for modal to be fully shown before initializing Pickr
            const initColorPicker = function() {
                const btn = document.getElementById('modalColorCodeBtn');
                const input = document.getElementById('modalColorCode');
                if (btn && input && btn.parentNode) {
                    initializeVariantColorPicker('modalColorCode', btn, input, '#000000');
                    modalElement.removeEventListener('shown.bs.modal', initColorPicker);
                }
            };
            modalElement.addEventListener('shown.bs.modal', initColorPicker, { once: true });
        }
        
        // Ensure DOM updates are processed before showing modal
        // Use requestAnimationFrame to ensure the browser has rendered the new elements
        requestAnimationFrame(() => {
            modal.show();
        });
    }

    // Save new attribute value
    function saveNewAttributeValue() {
        console.log('saveNewAttributeValue called');
        
        const form = document.getElementById('addAttributeValueForm');
        const attributeIdInput = document.getElementById('modalAttributeId');
        const attributeTypeInput = document.getElementById('modalAttributeType');
        const valueInput = document.getElementById('modalValueInput');
        const colorInput = document.getElementById('modalColorCode');
        const sortOrderInput = document.getElementById('modalSortOrder');
        
        console.log('Form elements:', {
            form: !!form,
            attributeIdInput: !!attributeIdInput,
            attributeTypeInput: !!attributeTypeInput,
            valueInput: !!valueInput,
            colorInput: !!colorInput,
            sortOrderInput: !!sortOrderInput
        });
        
        if (!form || !attributeIdInput || !attributeTypeInput || !valueInput) {
            console.error('Required form elements not found', {
                form: form,
                attributeIdInput: attributeIdInput,
                attributeTypeInput: attributeTypeInput,
                valueInput: valueInput
            });
            alert('Form elements not found. Please refresh the page and try again.');
            return;
        }
        
        const attributeId = attributeIdInput.value;
        const attributeType = attributeTypeInput.value;
        
        if (!valueInput.value.trim()) {
            alert('Please enter a value');
            return;
        }
        
        const payload = {
            value: valueInput.value.trim(),
            sort_order: (sortOrderInput && sortOrderInput.value) ? parseInt(sortOrderInput.value) : null,
            color_code: (attributeType === 'color' && colorInput && colorInput.value) ? colorInput.value : null
        };
        
        // Show loading state
        const saveBtn = document.getElementById('saveAttributeValueBtn');
        if (!saveBtn) {
            console.error('Save button not found');
            alert('Save button not found. Please refresh the page and try again.');
            return;
        }
        
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        console.log('Saving attribute value:', {
            attributeId: attributeId,
            attributeType: attributeType,
            value: valueInput.value.trim(),
            payload: payload
        });
        
        fetch(`{{ url('attributes') }}/${attributeId}/values`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Close modal
                const modalElement = document.getElementById('addAttributeValueModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', result.message || 'Attribute value created successfully');
                } else {
                    alert(result.message || 'Attribute value created successfully');
                }
                
                // Refresh the Select2 dropdown for this attribute
                const selectElement = document.querySelector(`select.variant-attribute-selector[data-attribute-id="${attributeId}"]`);
                if (selectElement) {
                    const $select = $(selectElement);
                    
                    // Add the new value to the dropdown
                    const option = document.createElement('option');
                    option.value = result.value.value;
                    option.textContent = result.value.value;
                    if (result.value.color_code) {
                        option.setAttribute('data-color-code', result.value.color_code);
                    }
                    selectElement.appendChild(option);
                    
                    // Refresh Select2 to recognize the new option
                    if ($select.data('select2')) {
                        $select.trigger('change.select2');
                    } else {
                        // If Select2 is not initialized, reload all values
                        const attributeType = selectElement.getAttribute('data-attribute-type');
                        loadAttributeValuesForSelector(attributeId, attributeType, selectElement);
                    }
                }
            } else {
                // Show error message
                const errorMsg = result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Failed to create attribute value');
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        })
        .catch(error => {
            console.error('Error saving attribute value:', error);
            if (typeof showToast === 'function') {
                showToast('error', 'Failed to create attribute value');
            } else {
                alert('Failed to create attribute value');
            }
        })
        .finally(() => {
            // Restore button state
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }

    // Initialize save button handler - use event delegation to handle dynamically created buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation on document to handle save button clicks (works even if modal is created later)
        document.addEventListener('click', function(e) {
            // Check if the clicked element is the save button or inside it
            const saveBtn = e.target.closest('#saveAttributeValueBtn');
            if (saveBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Save button clicked');
                saveNewAttributeValue();
            }
        });
        
        // Also attach directly if button exists at page load
        const saveBtn = document.getElementById('saveAttributeValueBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Save button clicked (direct handler)');
                saveNewAttributeValue();
            });
        }
    });
    
    // Also attach handler when modal is shown (in case button is created dynamically)
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target && e.target.id === 'addAttributeValueModal') {
            const saveBtn = e.target.querySelector('#saveAttributeValueBtn');
            if (saveBtn) {
                // Remove any existing handlers to avoid duplicates
                const newSaveBtn = saveBtn.cloneNode(true);
                saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
                
                // Attach new handler
                newSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Save button clicked (modal shown handler)');
                    saveNewAttributeValue();
                });
            }
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

    // Add single variant button handler
    const addVariantBtn = document.getElementById('addVariantBtn');
    if (addVariantBtn) {
        addVariantBtn.addEventListener('click', function() {
            const selectors = document.querySelectorAll('.variant-attribute-selector');
            const variantAttributes = {};
            let allSelected = true;
            
            // Collect selected values from all attribute selectors (works with Select2)
            selectors.forEach(select => {
                const attributeId = select.dataset.attributeId;
                const $select = $(select);
                const value = $select.val() ? String($select.val()).trim() : '';
                
                if (!value) {
                    allSelected = false;
                    $select.addClass('is-invalid');
                    // Also add invalid class to Select2 container
                    $select.next('.select2-container').addClass('is-invalid');
                } else {
                    $select.removeClass('is-invalid');
                    $select.next('.select2-container').removeClass('is-invalid');
                    variantAttributes[attributeId] = value;
                }
            });
            
            if (!allSelected) {
                showToast('error', 'Please select a value for all attributes before adding a variant.');
                return;
            }
            
            // Check if this variant combination already exists
            const existingVariant = generatedVariants.find(variant => {
                const normalized = normalizeAttributesPayload(variant.attributes || {});
                const keys = Object.keys(normalized).sort();
                const newKeys = Object.keys(variantAttributes).sort();
                
                if (keys.length !== newKeys.length) return false;
                
                return keys.every(key => {
                    return normalized[key] === variantAttributes[key];
                });
            });
            
            if (existingVariant) {
                showToast('error', 'This variant combination already exists.');
                return;
            }
            
            // Generate SKU for new variant
            const variantValueTexts = [];
            Object.keys(variantAttributes).forEach(attributeId => {
                const value = variantAttributes[attributeId];
                const selector = document.querySelector(`[data-attribute-id="${attributeId}"]`);
                if (selector) {
                    const $select = $(selector);
                    const selectedOption = $select.find(`option[value="${value}"]`);
                    if (selectedOption.length) {
                        const valueText = selectedOption.text().trim();
                        // Extract just the value part (before any parentheses or extra text)
                        const cleanValue = valueText.split('(')[0].trim();
                        variantValueTexts.push(cleanValue);
                    } else {
                        variantValueTexts.push(value.toString());
                    }
                } else {
                    variantValueTexts.push(value.toString());
                }
            });
            
            const skuSuffix = variantValueTexts.map(valueText =>
                valueText.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3)
            ).join('-');
            
            // Get parent SKU or product name for base
            const parentSku = document.getElementById('productSku')?.value || '';
            const productName = document.getElementById('productName')?.value || '';
            const baseSku = parentSku ? parentSku.replace('PRD-', '').split('-')[0] : 
                          (productName ? productName.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10) : 'VAR');
            const variantIndex = generatedVariants.length + 1;
            const autoSku = `${baseSku}-${variantIndex}-${skuSuffix}`;
            
            // Create new variant
            const newVariant = {
                attributes: variantAttributes,
                name: Object.values(variantAttributes).join(' - '),
                sku: autoSku,
                price: '',
                sale_price: '',
                is_active: true,
                measurements: [],
                images: []
            };
            
            // Add to generated variants
            if (!Array.isArray(generatedVariants)) {
                generatedVariants = [];
            }
            generatedVariants.push(newVariant);
            
            // Display variants
            displayVariants();
            
            // Show variants table
            variantsTableContainer.style.display = 'block';
            bulkActions.style.display = 'block';
            
            // Clear selectors (works with Select2)
            selectors.forEach(select => {
                const $select = $(select);
                $select.val(null).trigger('change');
                $select.removeClass('is-invalid');
                $select.next('.select2-container').removeClass('is-invalid');
            });
            
            // Show success message
            showToast('success', 'Variant added successfully.');
            
            if (!isRestoringVariantDraft) {
                persistVariantDraft();
            }
        });
    }

    // OLD: Generate variants (kept for backward compatibility but not used)
    const generateVariantsBtn = document.getElementById('generateVariantsBtn');
    if (generateVariantsBtn) {
        generateVariantsBtn.addEventListener('click', function() {
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
        const variantsTableBody = document.getElementById('variantsTableBody');
        const variantsTableContainer = document.getElementById('variantsTableContainer');
        const bulkActions = document.getElementById('bulkActions');
        
        if (!variantsTableBody) return;
        
        variantsTableBody.innerHTML = '';
        
        // Ensure variants table is visible when displaying variants
        if (variantsTableContainer) {
            variantsTableContainer.style.display = 'block';
        }
        if (bulkActions) {
            bulkActions.style.display = 'block';
        }
        
        // Update variant images section visibility (will be called after variants are added)
        
        if (!Array.isArray(generatedVariants) || generatedVariants.length === 0) {
            // If no variants and no attributes selected, create default variant
            const selectedIds = getSelectedAttributeIds();
            if (selectedIds.length === 0 && typeof createDefaultVariant === 'function') {
                createDefaultVariant();
                return; // Will be called again after variant is created
            }
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
            
            const normalizedImages = normalizeVariantImages(variant.images);

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
                    const parentSku = document.getElementById('productSku')?.value || '';
                    const productName = document.getElementById('productName')?.value || '';
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
                const normalizedCombination = normalizeAttributesPayload(variant);
                const variantValues = Object.keys(normalizedCombination)
                    .sort((a, b) => Number(a) - Number(b))
                    .map(key => normalizedCombination[key]);
                variantName = variantValues.join(' - ');
                
                // Generate SKU if not already set
                if (variant.sku && variant.sku.trim() !== '') {
                    variantSku = variant.sku;
                } else {
                    const skuSuffix = variantValues.map(value =>
                        value.toString().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3)
                    ).join('-');
                    
                    // Get parent SKU or product name for base
                    const parentSku = document.getElementById('productSku')?.value || '';
                    const productName = document.getElementById('productName')?.value || '';
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
            
            const row = document.createElement('tr');
            row.innerHTML = `
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
            `;
            
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
            };
            row.dataset.variantId = datasetPayload.id ? String(datasetPayload.id) : '';
            row.dataset.variantData = JSON.stringify(datasetPayload);
            const idHidden = row.querySelector('[data-variant-id-input]');
            if (idHidden) {
                idHidden.value = datasetPayload.id ? datasetPayload.id : '';
            }
            const attributesHidden = row.querySelector('[data-variant-attributes-input]');
            if (attributesHidden) {
                attributesHidden.value = JSON.stringify(datasetPayload.attributes || {});
            }
            row.dataset.existingImages = JSON.stringify(normalizedImages);
            variantsTableBody.appendChild(row);
            const measurementsForRow = datasetPayload.measurements && datasetPayload.measurements.length
                ? datasetPayload.measurements
                : parseMeasurementsFromRow(row);
            updateRowMeasurements(row, measurementsForRow);
            extractVariantRowData(row);
 
            const imageInput = row.querySelector('.variant-image-input');
            const previewEl = row.querySelector('[data-variant-image-preview]');
            const existingImages = extractExistingImagesFromRow(row);
            updateVariantImagePreview(previewEl, imageInput && imageInput.files && imageInput.files.length ? imageInput.files : null, existingImages);
            
            // Update button class based on whether images exist
            const viewBtn = row.querySelector('.view-variant-images-btn');
            if (viewBtn) {
                const hasFiles = imageInput && imageInput.files && imageInput.files.length > 0;
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
        });
        
        if (generatedVariants.length) {
            variantsTableContainer.style.display = 'block';
            bulkActions.style.display = 'block';
        } else {
            // Check if no attributes are selected - if so, create default variant instead of hiding
            const selectedIds = getSelectedAttributeIds();
            if (selectedIds.length === 0) {
                // No attributes selected - create default variant
                if (typeof createDefaultVariant === 'function') {
                    createDefaultVariant();
                }
            } else {
                // Attributes selected but no variants - hide table until variants are created
                variantsTableContainer.style.display = 'none';
                bulkActions.style.display = 'none';
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
    variantsTableBody.addEventListener('click', function(e) {
        if (!variantEditModalElement || !variantEditModal) {
            console.error('Variant edit modal is not available in the DOM.');
            return;
        }

        if (e.target.closest('.edit-variant-btn')) {
            const row = e.target.closest('tr');
            const index = e.target.closest('.edit-variant-btn').dataset.index;
            
            // Populate modal with current values
            document.getElementById('variantSku').value = row.querySelector('[name*="[sku]"]').value;
            document.getElementById('variantPrice').value = row.querySelector('[name*="[price]"]').value;
            const salePriceField = row.querySelector('[name*="[sale_price]"]');
            if (salePriceField && variantSellPriceInput) variantSellPriceInput.value = salePriceField.value;
            
            // Populate variant name
            const variantNameLabel = row.querySelector('.form-check-label');
            const variantNameInput = document.getElementById('variantName');
            const variantNameHidden = row.querySelector('[data-variant-name-input]');
            if (variantNameInput) {
                if (variantNameHidden && variantNameHidden.value) {
                    variantNameInput.value = variantNameHidden.value;
                } else if (variantNameLabel) {
                    variantNameInput.value = variantNameLabel.textContent.trim();
                }
            }
            
            // Populate barcode
            const barcodeHidden = row.querySelector('[data-variant-barcode-input]');
            const barcodeInput = document.getElementById('variantBarcode');
            if (barcodeInput && barcodeHidden) {
                barcodeInput.value = barcodeHidden.value || '';
            }
            
            const statusHiddenInput = row.querySelector('[data-variant-status-input]');
            if (variantStatusToggle) {
                const isActive = statusHiddenInput ? statusHiddenInput.value === '1' : true;
                variantStatusToggle.checked = isActive;
                updateVariantStatusToggleLabel();
            }
            
            // Populate inventory fields
            const stockQuantityInput = document.getElementById('variantStockQuantity');
            const stockStatusSelect = document.getElementById('variantStockStatus');
            const lowStockThresholdInput = document.getElementById('variantLowStockThreshold');
            const manageStockToggle = document.getElementById('variantManageStock');
            
            const stockQuantityHidden = row.querySelector('[data-variant-stock-quantity-input]');
            const stockStatusHidden = row.querySelector('[data-variant-stock-status-input]');
            const lowStockThresholdHidden = row.querySelector('[data-variant-low-stock-threshold-input]');
            const manageStockHidden = row.querySelector('[data-variant-manage-stock-input]');
            
            if (stockQuantityInput && stockQuantityHidden) {
                stockQuantityInput.value = stockQuantityHidden.value || '0';
            }
            if (stockStatusSelect && stockStatusHidden) {
                stockStatusSelect.value = stockStatusHidden.value || 'in_stock';
            }
            if (lowStockThresholdInput && lowStockThresholdHidden) {
                lowStockThresholdInput.value = lowStockThresholdHidden.value || '0';
            }
            if (manageStockToggle && manageStockHidden) {
                manageStockToggle.checked = manageStockHidden.value === '1';
            }
            
            // Populate other fields first
            const discountTypeHidden = row.querySelector('[data-variant-discount-type-input]');
            const discountValueHidden = row.querySelector('[data-variant-discount-value-input]');
            const discountActiveHidden = row.querySelector('[data-variant-discount-active-input]');

            if (discountTypeHidden && discountTypeSelect) {
                discountTypeSelect.value = discountTypeHidden.value || '';
            }
            if (discountValueHidden && discountValueInput) {
                discountValueInput.value = discountValueHidden.value || '';
                if (!discountTypeSelect.value) {
                    discountValueInput.setAttribute('disabled', 'disabled');
                } else {
                    discountValueInput.removeAttribute('disabled');
                }
            }
            if (discountActiveHidden && discountActiveSelect) {
                discountActiveSelect.value = discountActiveHidden.value === '1' ? '1' : '0';
            }

            if (variantSellPriceInput) {
                variantSellPriceInput.dataset.originalSellPrice = variantSellPriceInput.value || '';
            }
 
            activeVariantRow = row;
            syncModalImagePreviewFromRow(row);
            handleDiscountStateChange();
            calculateVariantSellPrice();
            
            // Load highlights & details
            loadHighlightsDetailsFromRow(row);
            
            // Parse measurements from row
            const measurementsForModal = parseMeasurementsFromRow(row);
            
            // Load attributes and units before rendering measurements and showing modal
            loadNumericAttributes(function() {
                // Ensure units are loaded before rendering measurements and showing modal
                if (!unitsLoaded && !unitsLoading) {
                    loadUnitsFromModule(function() {
                        // Render measurements after attributes and units are loaded
                        renderMeasurementsInModal(measurementsForModal);
                        updateMeasurementEmptyState();
                        variantEditModal.show();
                    });
                } else if (unitsLoaded) {
                    // Units already loaded, render measurements and show modal
                    renderMeasurementsInModal(measurementsForModal);
                    updateMeasurementEmptyState();
                    variantEditModal.show();
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
        }
    });
    
    // Load units when variant edit modal is shown (in case they weren't loaded yet)
    if (variantEditModalElement) {
        variantEditModalElement.addEventListener('shown.bs.modal', function() {
            if (!unitsLoaded && !unitsLoading) {
                loadUnitsFromModule(function() {
                    // Refresh unit dropdowns in measurement rows after units are loaded
                    if (measurementRowsContainer) {
                        const unitSelects = measurementRowsContainer.querySelectorAll('.measurement-unit');
                        unitSelects.forEach(select => {
                            const currentValue = select.value;
                            select.innerHTML = buildUnitOptionsHtml(currentValue);
                            select.value = currentValue;
                        });
                    }
                    updateMeasurementEmptyState();
                });
            } else if (unitsLoaded) {
                // Refresh unit dropdowns in case units were updated
                if (measurementRowsContainer) {
                    const unitSelects = measurementRowsContainer.querySelectorAll('.measurement-unit');
                    unitSelects.forEach(select => {
                        const currentValue = select.value;
                        select.innerHTML = buildUnitOptionsHtml(currentValue);
                        select.value = currentValue;
                    });
                }
                updateMeasurementEmptyState();
            }
        });
    }

    // Handle View Images button click
    variantsTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.view-variant-images-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.view-variant-images-btn');
            const variantIndex = btn.getAttribute('data-variant-index');
            const row = btn.closest('tr');
            
            // Get images from the row
            const imageInput = row.querySelector('.variant-image-input');
            const existingImages = extractExistingImagesFromRow(row);
            const newFiles = imageInput && imageInput.files && imageInput.files.length ? Array.from(imageInput.files) : [];
            
            // Collect all images (existing + new)
            const allImages = [];
            
            // Add existing images
            if (Array.isArray(existingImages) && existingImages.length > 0) {
                existingImages.forEach(path => {
                    if (path) {
                        const normalizedPath = path.startsWith('http')
                            ? path
                            : `/storage/${path.replace(/^\/?storage\//, '')}`;
                        allImages.push({
                            src: normalizedPath,
                            type: 'existing'
                        });
                    }
                });
            }
            
            // Add new files
            newFiles.forEach(file => {
                allImages.push({
                    src: URL.createObjectURL(file),
                    type: 'new',
                    name: file.name
                });
            });
            
            // Display images in modal
            const container = document.getElementById('variantImagesViewContainer');
            const emptyMessage = document.getElementById('variantImagesViewEmpty');
            
            if (allImages.length === 0) {
                container.innerHTML = '';
                container.style.display = 'none';
                emptyMessage.style.display = 'block';
            } else {
                container.innerHTML = '';
                container.style.display = 'flex';
                emptyMessage.style.display = 'none';
                
                allImages.forEach((image, index) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-4 col-sm-6';
                    
                    const card = document.createElement('div');
                    card.className = 'card';
                    
                    const img = document.createElement('img');
                    img.src = image.src;
                    img.className = 'card-img-top';
                    img.style.cssText = 'height: 200px; object-fit: cover; cursor: pointer;';
                    img.alt = image.name || `Variant image ${index + 1}`;
                    
                    // Add click to view full size
                    img.addEventListener('click', function() {
                        window.open(image.src, '_blank');
                    });
                    
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body p-2';
                    cardBody.innerHTML = `
                        <small class="text-muted d-block text-truncate" title="${image.name || 'Image'}">
                            ${image.name || `Image ${index + 1}`}
                        </small>
                        <span class="badge bg-${image.type === 'existing' ? 'success' : 'info'} badge-sm">${image.type === 'existing' ? 'Existing' : 'New'}</span>
                    `;
                    
                    card.appendChild(img);
                    card.appendChild(cardBody);
                    col.appendChild(card);
                    container.appendChild(col);
                });
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('variantImagesViewModal'));
            modal.show();
            
            // Clean up object URLs when modal is closed
            document.getElementById('variantImagesViewModal').addEventListener('hidden.bs.modal', function cleanup() {
                allImages.forEach(image => {
                    if (image.type === 'new' && image.src.startsWith('blob:')) {
                        URL.revokeObjectURL(image.src);
                    }
                });
                document.getElementById('variantImagesViewModal').removeEventListener('hidden.bs.modal', cleanup);
            }, { once: true });
        }
    });
    
    variantsTableBody.addEventListener('change', function(e) {
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

    // Save variant changes
    const saveVariantBtn = document.getElementById('saveVariantBtn');
    if (saveVariantBtn) {
        saveVariantBtn.addEventListener('click', function() {
            if (!variantEditModalElement || !variantEditModal) {
                console.error('Variant edit modal is not available in the DOM.');
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
            if (salePriceField && variantSellPriceInput) {
                salePriceField.value = variantSellPriceInput.value;
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

            if (discountTypeHidden && discountTypeSelect) {
                discountTypeHidden.value = discountTypeSelect.value;
            }
            if (discountValueHidden && discountValueInput) {
                discountValueHidden.value = discountValueInput.value;
            }
            if (discountActiveHidden && discountActiveSelect) {
                discountActiveHidden.value = discountActiveSelect.value === '1' ? '1' : '0';
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
            const stockQuantityHidden = row.querySelector('[data-variant-stock-quantity-input]');
            const stockStatusHidden = row.querySelector('[data-variant-stock-status-input]');
            const lowStockThresholdHidden = row.querySelector('[data-variant-low-stock-threshold-input]');
            const manageStockHidden = row.querySelector('[data-variant-manage-stock-input]');
            
            const stockQuantityInput = document.getElementById('variantStockQuantity');
            const stockStatusSelect = document.getElementById('variantStockStatus');
            const lowStockThresholdInput = document.getElementById('variantLowStockThreshold');
            const manageStockToggle = document.getElementById('variantManageStock');
            
            if (stockQuantityHidden && stockQuantityInput) {
                stockQuantityHidden.value = stockQuantityInput.value || '0';
            }
            if (stockStatusHidden && stockStatusSelect) {
                stockStatusHidden.value = stockStatusSelect.value || 'in_stock';
            }
            if (lowStockThresholdHidden && lowStockThresholdInput) {
                lowStockThresholdHidden.value = lowStockThresholdInput.value || '0';
            }
            if (manageStockHidden && manageStockToggle) {
                manageStockHidden.value = manageStockToggle.checked ? '1' : '0';
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
    async function loadHeadingSuggestions() {
        if (headingSuggestionsLoaded) return;
        
        try {
            const response = await fetch('{{ route("variant-headings.suggestions") }}');
            if (response.ok) {
                const data = await response.json();
                headingSuggestions = data.suggestions || [];
                headingSuggestionsLoaded = true;
            }
        } catch (error) {
            console.error('Error loading heading suggestions:', error);
        }
    }
    
    // Initialize heading suggestions on modal open
    if (variantEditModalElement) {
        variantEditModalElement.addEventListener('show.bs.modal', function() {
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
        row.className = 'bullet-point-row mb-2 d-flex align-items-start';
        row.setAttribute('data-heading-index', headingIndex);
        row.setAttribute('data-point-index', pointIndex);
        
        row.innerHTML = `
            <div class="flex-grow-1 me-2">
                <input type="text" 
                       class="form-control form-control-sm bullet-point-input" 
                       value="${pointText}"
                       placeholder="Enter bullet point...">
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
                    ${bulletPoints.length === 0 ? '<div class="text-muted small mb-2">No bullet points yet. Click "Add Point" to add one.</div>' : ''}
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary add-point-btn" data-heading-index="${headingIndex}">
                    <i data-feather="plus" class="me-1"></i> Add Point
                </button>
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
        const addPointBtn = panel.querySelector('.add-point-btn');
        const deleteHeadingBtn = panel.querySelector('.delete-heading-btn');
        const headingInput = panel.querySelector('.heading-input');
        
        addPointBtn.addEventListener('click', function() {
            const headingIdx = parseInt(this.getAttribute('data-heading-index'));
            const container = panel.querySelector('.bullet-points-container');
            const pointIndex = container.querySelectorAll('.bullet-point-row').length;
            const pointRow = createBulletPointRow(headingIdx, pointIndex);
            
            // Remove "No bullet points" message if exists
            const noPointsMsg = container.querySelector('.text-muted');
            if (noPointsMsg) noPointsMsg.remove();
            
            container.appendChild(pointRow);
            
            // Focus on new input
            const newInput = pointRow.querySelector('.bullet-point-input');
            if (newInput) newInput.focus();
        });
        
        deleteHeadingBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this heading and all its bullet points?')) {
                panel.remove();
                reindexHeadings();
            }
        });
        
        // Handle delete point button
        panel.addEventListener('click', function(e) {
            if (e.target.closest('.delete-point-btn')) {
                const pointRow = e.target.closest('.bullet-point-row');
                if (pointRow) {
                    pointRow.remove();
                    const container = panel.querySelector('.bullet-points-container');
                    if (container.querySelectorAll('.bullet-point-row').length === 0) {
                        const noPointsMsg = document.createElement('div');
                        noPointsMsg.className = 'text-muted small mb-2';
                        noPointsMsg.textContent = 'No bullet points yet. Click "Add Point" to add one.';
                        container.insertBefore(noPointsMsg, container.firstChild);
                    }
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
            const addPointBtn = panel.querySelector('.add-point-btn');
            if (addPointBtn) addPointBtn.setAttribute('data-heading-index', index);
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
        variantsTableBody.addEventListener('click', function(e) {
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
        const checkboxes = variantsTableBody.querySelectorAll('.variant-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        showToast('success', `Selected all ${checkboxes.length} variants`);
        if (!isRestoringVariantDraft) {
            persistVariantDraft();
        }
    });

    document.getElementById('deselectAllBtn').addEventListener('click', function() {
        const checkboxes = variantsTableBody.querySelectorAll('.variant-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
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

    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        const selectedRows = getSelectedVariantRows();
        if (selectedRows.length === 0) {
            alert('Please select variants to delete');
            return;
        }
        
        if (confirm(`Are you sure you want to delete ${selectedRows.length} selected variants? This action cannot be undone.`)) {
            selectedRows.forEach(row => {
                row.remove();
            });
            showToast('success', `${selectedRows.length} variants deleted`);
            
            // Check if any variants remain
            const remainingRows = variantsTableBody.querySelectorAll('tr');
            if (remainingRows.length === 0) {
                // Check if no attributes are selected - if so, create default variant instead of hiding
                const selectedIds = getSelectedAttributeIds();
                if (selectedIds.length === 0) {
                    // No attributes selected - create default variant
                    if (typeof createDefaultVariant === 'function') {
                        createDefaultVariant();
                    }
                } else {
                    // Attributes selected but all variants deleted - hide table
                    variantsTableContainer.style.display = 'none';
                    bulkActions.style.display = 'none';
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
        const selectedCheckboxes = variantsTableBody.querySelectorAll('.variant-checkbox:checked');
        const selectedRows = [];
        selectedCheckboxes.forEach(checkbox => {
            selectedRows.push(checkbox.closest('tr'));
        });
        return selectedRows;
    }

    // Load existing variants for edit mode
    function loadExistingVariants(payload) {
        if (!Array.isArray(payload) || payload.length === 0) {
            return;
        }

        generatedVariants = payload;

        // Display the variants
        displayVariants();

        // Show the variants table and bulk actions
        variantsTableContainer.style.display = 'block';
        bulkActions.style.display = 'block';

        ensureAttributeSelectionFromVariants();
    }

    // Toast notification function
    function showToast(type, message) {
        // Create toast element if it doesn't exist
        let toast = document.getElementById('variantToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'variantToast';
            toast.className = 'toast position-fixed top-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            document.body.appendChild(toast);
        }
        
        const iconName = type === 'success' ? 'check-circle' : 'x-circle';
        const iconToneClass = type === 'success' ? 'text-success' : 'text-danger';
        const title = type === 'success' ? 'Success' : 'Error';
        
        toast.innerHTML = `
            <div class="toast-header">
                <span data-feather="${iconName}" class="me-2 ${iconToneClass}"></span>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        refreshFeatherIcons();
    }

    function calculateVariantSellPrice() {
        if (!mrpInput || !variantSellPriceInput) {
            return;
        }

        const discountValueField = discountValueInput;
        const discountTypeField = discountTypeSelect;
        const discountActive = discountActiveSelect;

        let mrp = parseFloat(mrpInput.value);
        if (isNaN(mrp) || mrp < 0) {
            mrp = 0;
        }

        let sellPrice = variantSellPriceInput.value ? parseFloat(variantSellPriceInput.value) : mrp;

        if (discountActive && discountActive.value === '1' && discountTypeField && discountTypeField.value && discountValueField) {
            const discountValue = parseFloat(discountValueField.value);
            if (!isNaN(discountValue) && discountValue >= 0) {
                if (discountTypeField.value === 'percentage') {
                    sellPrice = Math.max(mrp - (mrp * (discountValue / 100)), 0);
                } else {
                    sellPrice = Math.max(mrp - discountValue, 0);
                }
            }
        } else {
            sellPrice = mrp;
        }

        variantSellPriceInput.value = sellPrice ? sellPrice.toFixed(2) : '';
    }

    function handleDiscountStateChange() {
        if (!discountValueInput) {
            return;
        }

        updateDiscountPrefix();
        if (discountTypeSelect && discountTypeSelect.value) {
            discountValueInput.removeAttribute('disabled');
        } else {
            discountValueInput.value = '';
            discountValueInput.setAttribute('disabled', 'disabled');
            if (discountActiveSelect) {
                discountActiveSelect.value = '0';
            }
            if (variantSellPriceInput && variantSellPriceInput.dataset.originalSellPrice !== undefined) {
                variantSellPriceInput.value = variantSellPriceInput.dataset.originalSellPrice;
            }
        }
        calculateVariantSellPrice();
    }

    function updateDiscountPrefix() {
        if (discountTypeSelect && discountTypeSelect.value === 'percentage') {
            discountPrefixSpan.textContent = '%';
        } else {
            discountPrefixSpan.textContent = '₹';
        }
    }

    function updateVariantStatusToggleLabel() {
        if (!variantStatusToggle) {
            return;
        }
        const label = document.querySelector('label[for="variantStatusToggle"]');
        if (label) {
            label.textContent = variantStatusToggle.checked ? 'Active' : 'Inactive';
        }
    }

    function updateVariantRowStatus(toggleElement) {
        if (!toggleElement) {
            return;
        }
        const row = toggleElement.closest('tr');
        if (!row) {
            return;
        }
        const label = row.querySelector('label[for="' + toggleElement.id + '"]');
        if (label) {
            label.textContent = toggleElement.checked ? 'Active' : 'Inactive';
        }
        const hiddenInput = row.querySelector('[data-variant-status-input]');
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
            existingImages.forEach(path => {
                if (!path) {
                    return;
                }
                const normalizedPath = path.startsWith('http')
                    ? path
                    : `/storage/${path.replace(/^\/?storage\//, '')}`;
                const wrapper = document.createElement('div');
                wrapper.className = 'variant-image-thumb';
                const img = document.createElement('img');
                img.src = normalizedPath;
                img.alt = 'Variant image';
                wrapper.appendChild(img);
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
            if (bulkPriceValue && bulkPriceValue.value) {
                const normalized = normalizeCurrencyValue(bulkPriceValue.value);
                if (normalized !== '') {
                    const priceInput = row.querySelector('[name*="[price]"]');
                    if (priceInput) {
                        priceInput.value = normalized;
                    }
                }
            }

            if (bulkSalePriceValue && bulkSalePriceValue.value) {
                const normalizedSale = normalizeCurrencyValue(bulkSalePriceValue.value);
                const saleInput = row.querySelector('[name*="[sale_price]"]');
                if (saleInput) {
                    saleInput.value = normalizedSale;
                }
            }

            if (bulkStatusValue !== null && bulkStatusValue !== undefined) {
                const statusToggle = row.querySelector('.variant-status-toggle');
                if (statusToggle) {
                    statusToggle.checked = !!bulkStatusValue.checked;
                    updateVariantRowStatus(statusToggle);
                } else {
                    const hiddenStatus = row.querySelector('[data-variant-status-input]');
                    if (hiddenStatus) {
                        hiddenStatus.value = bulkStatusValue.checked ? '1' : '0';
                    }
                }
            }

            if (bulkDiscountTypeValue && bulkDiscountTypeValue.value) {
                const discountTypeHidden = row.querySelector('[data-variant-discount-type-input]');
                if (discountTypeHidden) {
                    discountTypeHidden.value = bulkDiscountTypeValue.value;
                }
            }

            if (bulkDiscountValue && bulkDiscountValue.value) {
                const discountValueHidden = row.querySelector('[data-variant-discount-value-input]');
                if (discountValueHidden) {
                    discountValueHidden.value = bulkDiscountValue.value;
                }
            }

            if (bulkDiscountActiveValue !== null && bulkDiscountActiveValue !== undefined) {
                const discountActiveHidden = row.querySelector('[data-variant-discount-active-input]');
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
        row.dataset.existingImages = JSON.stringify(result.images || []);

        return result;
    }

    function collectVariantRowData() {
        const rows = variantsTableBody.querySelectorAll('tr');
        return Array.from(rows)
            .map(row => extractVariantRowData(row))
            .filter(item => item !== null);
    }

    function persistVariantDraft() {
        if (isEditMode || isRestoringVariantDraft) {
            return;
        }

        try {
            const draftPayload = {
                selectedAttributeIds: deepClone(selectedAttributeIds),
                attributeValues: deepClone(attributeValues),
                selectedAttributeValues: deepClone(selectedAttributeValues),
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
 
        selectedAttributeIds = [];
        attributeValues = payload.attributeValues || {};
        selectedAttributeValues = payload.selectedAttributeValues || {};
        const restoredAttributeIds = Array.isArray(payload.selectedAttributeIds) ? payload.selectedAttributeIds : [];
        const restoredVariants = Array.isArray(payload.generatedVariants) ? payload.generatedVariants : [];

        // Restore selected attributes in checkboxes
        if (restoredAttributeIds.length > 0) {
            setSelectedAttributeIds(restoredAttributeIds);
            selectedAttributeIds = restoredAttributeIds;
        }

        if (!restoredAttributeIds.length) {
            updateAttributeValuesConfig();
        }

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
                url: '{{ route("products.attributes") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.attributes) {
                        // Filter only variant attributes (is_variation = true)
                        const variantAttrs = response.attributes.filter(function(attr) {
                            return attr.is_variation === true || attr.is_variation === 1 || attr.is_variation === '1';
                        }).map(function(attr) {
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
                        
                        // Recursively call this function with the loaded attributes
                        if (variantAttrs.length > 0) {
                            window.updateVariantAttributesSelect2(variantAttrs);
                        } else {
                            // No variant attributes found even after loading all
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
                const isSelected = currentSelected.includes(String(attr.id));
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
                           ${isSelected ? 'checked' : ''}
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
            
            // Restore selected values
            const validSelected = currentSelected.filter(id => 
                variantAttributes.some(attr => String(attr.id) === String(id))
            );
            
            if (validSelected.length > 0) {
                setSelectedAttributeIds(validSelected);
                selectedAttributeIds = validSelected;
                updateAttributeValuesConfig();
            } else {
                // No valid selected attributes, clear selection
                selectedAttributeIds = [];
                updateAttributeValuesConfig();
            }
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
        preselectExistingAttributeCheckboxes();
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


    document.addEventListener('product:clearDrafts', clearVariantDraft);

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
        if (variantsTableContainer) {
            variantsTableContainer.style.display = 'block';
        }
        
        // Check if we need to create default variant
        const selectedIds = getSelectedAttributeIds();
        const hasVariantsInTable = variantsTableBody && variantsTableBody.querySelectorAll('tr').length > 0;
        
        // Check generatedVariants from the scope
        let hasVariants = false;
        try {
            if (typeof generatedVariants !== 'undefined') {
                hasVariants = Array.isArray(generatedVariants) && generatedVariants.length > 0;
            }
        } catch(e) {
            hasVariants = false;
        }
        
        // If no attributes selected and no variants, create default variant
        if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
            if (typeof createDefaultVariant === 'function') {
                createDefaultVariant();
            } else if (typeof window.createDefaultVariant === 'function') {
                window.createDefaultVariant();
            } else {
                // Fallback: manually create and show a default variant row
                if (variantsTableBody) {
                    const productName = document.getElementById('productName') ? document.getElementById('productName').value : 'Product';
                    const defaultSku = (productName ? productName.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 15) : 'PROD') + '-DEFAULT-' + Date.now().toString().slice(-6);
                    
                    const defaultRow = `
                        <tr data-variant-index="0">
                            <td>
                                <input type="text" class="form-control form-control-sm" name="variants[0][name]" value="${productName || 'Default Variant'}" />
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="variants[0][sku]" value="${defaultSku}" required />
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control form-control-sm" name="variants[0][price]" value="0.00" step="0.01" required />
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control form-control-sm" name="variants[0][sale_price]" value="" step="0.01" />
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">-</small>
                            </td>
                            <td>
                                <input type="file" class="form-control form-control-sm" name="variants[0][images][]" accept="image/*" multiple />
                            </td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editVariantRow(this)">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteVariantRow(this)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    variantsTableBody.innerHTML = defaultRow;
                }
            }
        }
    }, 1500);
});
</script>

<style>
/* Attribute Values Checkbox Styling */
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
    if (!buttonElement || !buttonElement.parentNode) {
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
    buttonElement.style.backgroundColor = initialColor || '#000000';
    buttonElement.style.width = '100%';
    buttonElement.style.height = '38px';
    buttonElement.style.border = '1px solid #ced4da';
    buttonElement.style.borderRadius = '0.375rem';
    buttonElement.style.cursor = 'pointer';
    
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
            if (hiddenInput) {
                hiddenInput.value = hexColor;
            }
            buttonElement.style.backgroundColor = hexColor;
        });
        
        window.variantColorPickers[pickerId] = pickr;
        return pickr;
    } catch(error) {
        console.error('Error initializing Pickr:', error);
        return null;
    }
}

// Initialize Pickr for dynamically created color inputs
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically created color picker buttons
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('color-picker-btn') && !e.target.dataset.pickrInitialized) {
            const colorInputId = e.target.dataset.colorInput;
            const hiddenInput = document.getElementById(colorInputId);
            if (hiddenInput && e.target.parentNode && !e.target.dataset.pickrInitialized) {
                e.target.dataset.pickrInitialized = 'true';
                const initialColor = hiddenInput.value || '#000000';
                initializeVariantColorPicker(colorInputId, e.target, hiddenInput, initialColor);
            }
        }
    });
    
    // Also initialize any existing color pickers when attribute selectors are created
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const colorPickerBtns = node.querySelectorAll ? node.querySelectorAll('.color-picker-btn:not([data-pickr-initialized])') : [];
                    colorPickerBtns.forEach(function(btn) {
                        // Only initialize if button is in DOM
                        if (btn.parentNode) {
                            const colorInputId = btn.dataset.colorInput;
                            const hiddenInput = document.getElementById(colorInputId);
                            if (hiddenInput) {
                                btn.dataset.pickrInitialized = 'true';
                                const initialColor = hiddenInput.value || '#000000';
                                initializeVariantColorPicker(colorInputId, btn, hiddenInput, initialColor);
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
        const selectedIds = getSelectedAttributeIds();
        const variantsTableBody = document.getElementById('variantsTableBody');
        const variantsTableContainer = document.getElementById('variantsTableContainer');
        const hasVariantsInTable = variantsTableBody && variantsTableBody.querySelectorAll('tr').length > 0;
        
        // Check if we have variants in the generatedVariants array (from the main scope)
        let hasVariants = false;
        try {
            hasVariants = typeof generatedVariants !== 'undefined' && Array.isArray(generatedVariants) && generatedVariants.length > 0;
        } catch(e) {
            hasVariants = false;
        }
        
        if (selectedIds.length === 0 && !hasVariants && !hasVariantsInTable) {
            // Create default variant
            if (typeof createDefaultVariant === 'function') {
                createDefaultVariant();
            } else if (typeof window.createDefaultVariant === 'function') {
                window.createDefaultVariant();
            }
        }
        
        // Ensure variants table is visible if we have variants or no attributes selected
        if (variantsTableContainer) {
            if (hasVariants || hasVariantsInTable || selectedIds.length === 0) {
                variantsTableContainer.style.display = 'block';
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
    min-height: 40px;
}

.add-point-btn {
    margin-top: 0.5rem;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', highlightsStyles);
</script>
