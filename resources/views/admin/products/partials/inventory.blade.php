{{-- Inventory Section --}}
<div class="card mb-4" id="inventorySection" style="display: none;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-boxes me-2"></i>Inventory
            </h5> 

            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#inventoryInfoModal">
                <svg class="svg-inline--fa fa-info-circle fa-w-16" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg><!-- <i class="fas fa-info-circle"></i> Font Awesome fontawesome.com -->
            </button>

        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Stock Management --}}
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="manageStock" name="manage_stock" 
                           {{ old('manage_stock', $product->manage_stock ?? true) ? '' : '' }}>
                    <label class="form-check-label" for="manageStock">
                        <strong>Manage Stock</strong>
                        <small class="text-muted d-block">Enable stock tracking for this product</small>
                    </label>
                </div>
            </div>

            {{-- Stock Quantity --}}
            <div class="col-md-6" id="stockQuantityField">
                <label for="stockQuantity" class="form-label">Stock Quantity</label>
                <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" 
                       min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                <div class="invalid-feedback"></div>
            </div>

            {{-- Stock Status --}}
            <div class="col-md-6" id="stockStatusField">
                <label for="stockStatus" class="form-label">Stock Status</label>
                <select class="form-select" id="stockStatus" name="stock_status">
                    <option value="in_stock" {{ old('stock_status', $product->stock_status ?? 'in_stock') == 'in_stock' ? 'selected' : '' }}>
                        In Stock
                    </option>
                    <option value="out_of_stock" {{ old('stock_status', $product->stock_status ?? '') == 'out_of_stock' ? 'selected' : '' }}>
                        Out of Stock
                    </option>
                    <option value="on_backorder" {{ old('stock_status', $product->stock_status ?? '') == 'on_backorder' ? 'selected' : '' }}>
                        On Backorder
                    </option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Backorder Settings --}}
            <div class="col-12" id="backorderSettings">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="allowBackorder" name="allow_backorder" 
                           {{ old('allow_backorder', $product->allow_backorder ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allowBackorder">
                        <strong>Allow Backorders</strong>
                        <small class="text-muted d-block">Allow customers to purchase when out of stock</small>
                    </label>
                </div>
            </div>

            {{-- Low Stock Threshold --}}
            <div class="col-md-6" id="lowStockField">
                <label for="lowStockThreshold" class="form-label">Low Stock Threshold</label>
                <input type="number" class="form-control" id="lowStockThreshold" name="low_stock_threshold" 
                       min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}">
                <small class="text-muted">Alert when stock falls below this number</small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Stock Location --}}
            <div class="col-md-6" id="stockLocationField">
                <label for="stockLocation" class="form-label">Stock Location</label>
                <input type="text" class="form-control" id="stockLocation" name="stock_location" 
                       value="{{ old('stock_location', $product->stock_location ?? '') }}" 
                       placeholder="e.g., Warehouse A, Store Front">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Information Modal -->
<div class="modal fade" id="inventoryInfoModal" tabindex="-1" aria-labelledby="inventoryInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inventoryInfoModalLabel">
                    <i class="fas fa-boxes me-2"></i>Inventory Management Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-toggle-on text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Stock Management:</strong> Enable automatic stock tracking and inventory alerts <small class="text-muted">(Recommended for physical products)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cubes text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Stock Quantity:</strong> Current available quantity in your inventory <small class="text-muted">(Set to 0 for out of stock items)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Stock Status:</strong> Current availability status for customers <small class="text-muted">(In Stock, Out of Stock, or On Backorder)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Backorders:</strong> Allow customers to purchase when out of stock <small class="text-muted">(Useful for pre-orders and restocking)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Low Stock Alert:</strong> Get notified when inventory runs low <small class="text-muted">(Set threshold for automatic alerts)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map-marker-alt text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Stock Location:</strong> Physical location where items are stored <small class="text-muted">(Helpful for fulfillment and organization)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Enable stock management</strong> for physical products <small class="text-muted">(Essential for inventory control)</small></div>
                        <div><strong>Set realistic low stock thresholds</strong> <small class="text-muted">(Prevent stockouts)</small></div>
                        <div><strong>Use backorders</strong> for popular items <small class="text-muted">(Maintain sales during restocking)</small></div>
                        <div><strong>Keep stock locations updated</strong> <small class="text-muted">(Improve fulfillment efficiency)</small></div>
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
    const manageStockCheckbox = document.getElementById('manageStock');
    const stockQuantityField = document.getElementById('stockQuantityField');
    const stockStatusField = document.getElementById('stockStatusField');
    const backorderSettings = document.getElementById('backorderSettings');
    const lowStockField = document.getElementById('lowStockField');
    const stockLocationField = document.getElementById('stockLocationField');

    function toggleStockFields() {
        const isEnabled = manageStockCheckbox.checked;
        
        stockQuantityField.style.display = isEnabled ? 'block' : 'none';
        stockStatusField.style.display = isEnabled ? 'block' : 'none';
        backorderSettings.style.display = isEnabled ? 'block' : 'none';
        lowStockField.style.display = isEnabled ? 'block' : 'none';
        stockLocationField.style.display = isEnabled ? 'block' : 'none';
        
        // Make stock quantity required when stock management is enabled
        document.getElementById('stockQuantity').required = isEnabled;
    }

    manageStockCheckbox.addEventListener('change', toggleStockFields);
    toggleStockFields(); // Initial call
});
</script>
