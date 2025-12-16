@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Inventory Management</h1>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info" onclick="exportInventory()">
                            <i class="bx bx-export me-1"></i> Export Inventory
                        </button>
                        <button type="button" class="btn btn-success" onclick="openImportInventoryModal()">
                            <i class="bx bx-import me-1"></i> Import Inventory
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openAddInventoryModal()">
                            <i class="bx bx-plus me-1"></i> Add Inventory
                        </button>
                    </div>
                </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filterWarehouse" class="form-label">Warehouse</label>
                        <select class="form-select" id="filterWarehouse">
                            <option value="">All Warehouses</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStockStatus" class="form-label">Stock Status</label>
                        <select class="form-select" id="filterStockStatus">
                            <option value="">All Status</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="on_backorder">On Backorder</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="filterLowStock">
                            <label class="form-check-label" for="filterLowStock">
                                Show Low Stock Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                <table class="table table-hover" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Stock Quantity</th>
                            <th>Warehouses</th>
                            <th>Stock Status</th>
                            <th>Low Stock Threshold</th>
                            <th class="w-min" data-orderable="false">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Inventory Modal -->
<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInventoryModalLabel">
                    <i class="fas fa-boxes me-2"></i>Edit Inventory
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="inventoryForm">
                <div class="modal-body">
                    <input type="hidden" id="inventoryProductId" name="product_id">
                    <input type="hidden" id="isVariant" name="is_variant" value="0">
                    
                    <div id="variantInfo" class="alert alert-info" style="display: none;">
                        <strong>Variant:</strong> <span id="variantName"></span><br>
                        <strong>SKU:</strong> <span id="variantSku"></span>
                    </div>
                    
                    <div class="row g-3">
                        <input type="hidden" id="editManageStock" name="manage_stock" value="1">

                        <div class="col-md-6" id="editWarehouseField">
                            <label for="editWarehouse" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="editWarehouse" name="warehouse_id" required>
                                <option value="">Select Warehouse</option>
                            </select>
                            <small class="text-muted">Required to update stock quantity</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6" id="editWarehouseLocationField" style="display: none;">
                            <label for="editWarehouseLocation" class="form-label">Location</label>
                            <select class="form-select" id="editWarehouseLocation" name="warehouse_location_id">
                                <option value="">No Specific Location</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6" id="editStockQuantityField" style="display: none;">
                            <label for="editStockQuantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editStockQuantity" name="stock_quantity" min="0" required disabled>
                            <small class="text-muted">Select a warehouse first to update stock quantity</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6" id="editUpdateTypeField" style="display: none;">
                            <label for="editUpdateType" class="form-label">Update Type</label>
                            <select class="form-select" id="editUpdateType" name="update_type">
                                <option value="set">Set Quantity</option>
                                <option value="increment">Add to Existing</option>
                                <option value="decrement">Subtract from Existing</option>
                            </select>
                            <small class="text-muted">How to update the stock</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12" id="stockBreakdownSection" style="display: none;">
                            <hr>
                            <h6>Stock Breakdown by Warehouse</h6>
                            <div id="stockBreakdownContent"></div>
                        </div>

                        <div class="col-md-6" id="editStockLocationField">
                            <label for="editStockLocation" class="form-label">Stock Location</label>
                            <input type="text" class="form-control" id="editStockLocation" name="stock_location" placeholder="e.g., Warehouse A, Store Front">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12" id="editBackorderField">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editAllowBackorder" name="allow_backorder">
                                <label class="form-check-label" for="editAllowBackorder">
                                    <strong>Allow Backorders</strong>
                                    <small class="text-muted d-block">Allow customers to purchase when out of stock</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">
                    <i class="bx bx-plus me-2"></i>Add Inventory
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search and Filter Section -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="productSearch" class="form-label">Search Products</label>
                                <input type="text" class="form-control" id="productSearch" placeholder="Search by name or SKU...">
                            </div>
                            <div class="col-md-3">
                                <label for="filterProductBrand" class="form-label">Brand</label>
                                <select class="form-select" id="filterProductBrand">
                                    <option value="">All Brands</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterProductCategory" class="form-label">Category</label>
                                <select class="form-select" id="filterProductCategory">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Variants Summary -->
                <div id="selectedProductsSummary" class="alert alert-info mb-3" style="display: none;">
                    <strong><span id="selectedCount">0</span> variant(s) selected</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="clearSelection()">Clear Selection</button>
                </div>

                <!-- Variants Table -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover" id="productSelectionTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllVariants">
                                </th>
                                <th class="min-w-20x">Variant</th>
                                <th>SKU</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Stock Quantity</th>
                                <th>Warehouses</th>
                                <th>Stock Status</th>
                                <th>Low Stock Threshold</th>
                            </tr>
                        </thead>
                        <tbody id="productSelectionTableBody">
                            <!-- Variants will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Stock Input Section -->
                <div class="card mt-3" id="stockInputSection" style="display: none;">
                    <div class="card-body">
                        <h6 class="card-title">Add Stock</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="addStockWarehouse" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select" id="addStockWarehouse" required>
                                    <option value="">Select Warehouse</option>
                                </select>
                                <small class="text-muted">Select warehouse to add stock to</small>
                            </div>
                            <div class="col-md-6">
                                <label for="addStockLocation" class="form-label">Location (Optional)</label>
                                <select class="form-select" id="addStockLocation">
                                    <option value="">No Specific Location</option>
                                </select>
                                <small class="text-muted">Optional warehouse location</small>
                            </div>
                            <div class="col-md-6">
                                <label for="stockQuantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stockQuantity" min="0" value="0" required>
                                <small class="text-muted">Enter the quantity to add to selected products</small>
                            </div>
                            <div class="col-md-6">
                                <label for="addStockUpdateType" class="form-label">Update Type</label>
                                <select class="form-select" id="addStockUpdateType">
                                    <option value="increment">Add to Existing</option>
                                    <option value="set">Set Quantity</option>
                                </select>
                                <small class="text-muted">How to update the stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addStockBtn" onclick="addStockToSelected()" disabled>
                    <i class="bx bx-plus me-1"></i> Add Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Inventory Modal -->
<div class="modal fade" id="importInventoryModal" tabindex="-1" aria-labelledby="importInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importInventoryModalLabel">
                    <i class="bx bx-import me-2"></i>Import Inventory
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importInventoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Instructions:</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="collapse" data-bs-target="#importInstructionsCollapse" aria-expanded="true" aria-controls="importInstructionsCollapse">
                                <i class="fas fa-chevron-down" id="importInstructionsIcon"></i>
                            </button>
                        </div>
                        <div class="collapse show" id="importInstructionsCollapse">
                            <ul class="mb-0 mt-2">
                                <li>Download the sample file to see the correct format</li>
                                <li>Use SKU to identify variants (required)</li>
                                <li><strong>Stock Quantity will be ADDED to existing quantity</strong> (not replaced)</li>
                                <li><strong>Warehouse is REQUIRED:</strong> Include <strong>Warehouse Code</strong> in the file OR select a default warehouse below</li>
                                <li>Include <strong>Location Code</strong> column (optional) for specific warehouse locations</li>
                                <li>Stock quantity updates require warehouse selection - total quantity cannot be updated directly</li>
                                <li>Supported formats: CSV, XLS, XLSX</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sampleFileType" class="form-label">Download Sample File</label>
                        <div class="input-group">
                            <select class="form-select" id="sampleFileType">
                                <option value="default">Default (Sample Data)</option>
                                <option value="all">All Variants (First 1000)</option>
                                <option value="low_stock">Low Stock / Near Empty</option>
                            </select>
                            <select class="form-select" id="sampleWarehouse" style="max-width: 200px;">
                                <option value="">All Warehouses</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" id="downloadSampleBtn" onclick="downloadSampleFile(event)">
                                <i class="bx bx-download me-1"></i> Download
                            </button>
                        </div>
                        <small class="text-muted">Choose the type of sample file you want to download</small>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info btn-sm" id="showWarehouseCodesBtn" onclick="showWarehouseCodesModal()">
                            <i class="bx bx-info-circle me-1"></i> View Warehouse & Location Codes
                        </button>
                        <small class="text-muted d-block mt-1">Click to see all available warehouse codes and location codes for reference</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importWarehouse" class="form-label">Default Warehouse</label>
                        <select class="form-select" id="importWarehouse" name="warehouse_id">
                            <option value="">Select Default Warehouse (or use Warehouse Code in file)</option>
                        </select>
                        <small class="text-muted"><strong>Required:</strong> Either provide Warehouse Code in the file OR select a default warehouse here. Stock quantity updates require warehouse selection.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select File to Import <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".csv,.xls,.xlsx" required>
                        <small class="text-muted">Maximum file size: 10MB. File should include columns: SKU, Stock Quantity to Add, Warehouse Code (required if no default warehouse selected), Location Code (optional)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="updateExistingOnly" name="update_existing_only" value="1">
                            <label class="form-check-label" for="updateExistingOnly">
                                Update existing inventory only (skip new variants)
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning" id="importProgress" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span id="importProgressText">Processing import...</span>
                        </div>
                    </div>
                    
                    <div id="importErrors" class="alert alert-danger" style="display: none;">
                        <strong>Errors:</strong>
                        <ul id="importErrorsList" class="mb-0"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="importBtn">
                        <i class="bx bx-import me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Warehouse & Location Codes Modal -->
<div class="modal fade" id="warehouseCodesModal" tabindex="-1" aria-labelledby="warehouseCodesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warehouseCodesModalLabel">
                    <i class="bx bx-info-circle me-2"></i>Warehouse & Location Codes Reference
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Reference Guide:</strong> Use these codes in the <strong>Warehouse Code</strong> and <strong>Location Code</strong> columns when importing inventory.
                </div>
                
                <div id="warehouseCodesLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading warehouse and location codes...</p>
                </div>
                
                <div id="warehouseCodesContent" style="display: none;">
                    <div id="warehouseCodesTableContainer"></div>
                </div>
                
                <div id="warehouseCodesEmpty" class="text-center py-4" style="display: none;">
                    <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No warehouses found. Please add warehouses first.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<style>
.product-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    flex-shrink: 0;
    border-radius: 4px;
}

.stock-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.stock-in-stock {
    background: #00a629;
    color: #fff3cd;
}

.stock-out-of-stock {
    background: #f8d7da;
    color: #db3737;
}

.stock-on-backorder {
    background: #fff3cd;
    color: #3d464d;
}

.stock-partially-out-of-stock {
    background: #ffeaa7;
    color: #db3737;
}

.low-stock {
    color: #db3737;
    font-weight: 600;
}

/* Variant rows are now shown directly in the table, no special styling needed */

.form-control--search {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control--search:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.w-min {
    width: 1%;
    white-space: nowrap;
}

/* Variant column now sizes to content - removed fixed min-width */
</style>
<!-- Inventory History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history me-2"></i>Inventory History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Variant:</strong> <span id="historyVariantName"></span> | 
                    <strong>SKU:</strong> <span id="historyVariantSku"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="historyTable">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Warehouse</th>
                                <th>Location</th>
                                <th>Previous Qty</th>
                                <th>New Qty</th>
                                <th>Change</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>User</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- History will be loaded here -->
                        </tbody>
                    </table>
                </div>
                <div id="historyEmpty" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-history fa-3x mb-3"></i>
                    <p>No history found for this variant.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="clearHistoryBtn" onclick="clearInventoryHistory()" style="display: none;">
                    <i class="fas fa-trash me-1"></i> Clear History
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let inventoryTable;
let editModal;

document.addEventListener('DOMContentLoaded', function() {
    loadWarehouses();
    initializeDataTable();
    setupEventListeners();
    editModal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
});

// Load warehouses for filter and edit modal
function loadWarehouses() {
    $.ajax({
        url: '{{ route("inventory.warehouses") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let warehouseSelect = $('#filterWarehouse');
                let editWarehouseSelect = $('#editWarehouse');
                let importWarehouseSelect = $('#importWarehouse');
                let sampleWarehouseSelect = $('#sampleWarehouse');
                
                warehouseSelect.empty().append('<option value="">All Warehouses</option>');
                editWarehouseSelect.empty().append('<option value="">Select Warehouse (Optional)</option>');
                importWarehouseSelect.empty().append('<option value="">No Default Warehouse (Use Warehouse Code from file)</option>');
                sampleWarehouseSelect.empty().append('<option value="">All Warehouses</option>');
                
                response.data.forEach(function(warehouse) {
                    warehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                    editWarehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                    importWarehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                    sampleWarehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                });
            }
        }
    });
}

// Load locations for selected warehouse
function loadWarehouseLocations(warehouseId) {
    if (!warehouseId) {
        $('#editWarehouseLocation').empty().append('<option value="">No Specific Location</option>');
        $('#editWarehouseLocationField').hide();
        return;
    }
    
    $.ajax({
        url: '{{ route("inventory.warehouse-locations", ":id") }}'.replace(':id', warehouseId),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let locationSelect = $('#editWarehouseLocation');
                locationSelect.empty().append('<option value="">No Specific Location</option>');
                
                response.data.forEach(function(location) {
                    locationSelect.append(`<option value="${location.id}">${location.full_location}</option>`);
                });
                
                $('#editWarehouseLocationField').show();
                $('#editUpdateTypeField').show();
            }
        }
    });
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#inventoryTable')) {
        $('#inventoryTable').DataTable().destroy();
    }
    
    inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        columnDefs: [
            {
                targets: 0, // Variant column
                width: 'auto'
            },
            {
                targets: 1, // SKU column
                width: 'auto'
            },
            {
                targets: 8, // Actions column
                width: '100px',
                orderable: false
            }
        ],
        ajax: {
            url: '{{ route("inventory.data") }}',
            data: function(d) {
                d.stock_status = $('#filterStockStatus').val();
                d.low_stock = $('#filterLowStock').is(':checked') ? '1' : '';
                d.warehouse_id = $('#filterWarehouse').val();
            }
        },
        columns: [
            {
                data: 'name',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center" style="min-width: fit-content;">
                            <img src="${row.image_url || '/assets/images/placeholder.jpg'}" 
                                 alt="${data}" 
                                 class="product-image me-2"
                                 style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;"
                                 onerror="this.src='/assets/images/placeholder.jpg'">
                            <div style="min-width: 0;">
                                <div class="fw-bold text-truncate" style="max-width: 200px;" title="${data}">${data}</div>
                                <small class="text-muted">Variant</small>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'sku' },
            {
                data: 'product_name',
                render: function(data, type, row) {
                    if (row.product_id) {
                        return `
                            <div>
                                <a href="/products/${row.product_id}" target="_blank" class="text-decoration-none fw-bold">
                                    ${data}
                                </a>
                            </div>
                        `;
                    } else {
                        return `
                            <div>
                                <div class="fw-bold text-muted">${data}</div>
                                <small class="text-danger">Product not found</small>
                            </div>
                        `;
                    }
                }
            },
            {
                data: 'type',
                render: function(data) {
                    return '<span class="badge bg-info">Variant</span>';
                }
            },
            {
                data: 'stock_quantity',
                render: function(data, type, row) {
                    let html = `<div class="fw-bold">${data || 0}</div>`;
                    if (row.available_stock !== undefined && row.available_stock !== data) {
                        html += `<small class="text-muted">Available: ${row.available_stock}</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'warehouse_count',
                render: function(data, type, row) {
                    if (!row.warehouse_breakdown || row.warehouse_breakdown.length === 0) {
                        return '<span class="text-muted">-</span>';
                    }
                    
                    let html = `<div class="d-flex flex-column gap-1">`;
                    row.warehouse_breakdown.forEach(function(stock) {
                        // Show warehouse name with total quantity (summed across all locations)
                        let quantityDisplay = stock.quantity || 0;
                        let availableDisplay = stock.available_quantity !== undefined ? stock.available_quantity : quantityDisplay;
                        
                        html += `<div class="small">
                            <span class="badge bg-primary">${stock.warehouse_name}</span>
                            <span class="text-muted">(${quantityDisplay})</span>
                            ${availableDisplay !== quantityDisplay ? `<span class="text-success">[Avail: ${availableDisplay}]</span>` : ''}
                        </div>`;
                    });
                    html += `</div>`;
                    return html;
                }
            },
            {
                data: 'stock_status',
                render: function(data, type, row) {
                    const statusClass = `stock-${row.stock_status_value.replace(/_/g, '-')}`;
                    const isLow = row.is_low_stock;
                    let statusHtml = `<span class="stock-status ${statusClass}">${data}</span>`;
                    // Show low stock badge when stock is at or below threshold (and > 0)
                    if (isLow) {
                        statusHtml += ` <span class="badge bg-warning text-dark">Low Stock</span>`;
                    }
                    return statusHtml;
                }
            },
            { data: 'low_stock_threshold' },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary" onclick="editInventory(${data}, true, '${row.name.replace(/'/g, "\\'")}', '${row.sku.replace(/'/g, "\\'")}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewHistory(${data}, '${row.name.replace(/'/g, "\\'")}', '${row.sku.replace(/'/g, "\\'")}')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });
}

function setupEventListeners() {
    // Search
    $('#table-search').on('keyup', function() {
        inventoryTable.search(this.value).draw();
    });

    // Filters
    $('#filterStockStatus, #filterLowStock, #filterWarehouse').on('change', function() {
        inventoryTable.draw();
    });

    // Warehouse selection in edit modal
    $('#editWarehouse').on('change', function() {
        const warehouseId = $(this).val();
        loadWarehouseLocations(warehouseId);
        if (warehouseId) {
            $('#editStockQuantityField').show();
            $('#editStockQuantity').prop('required', true).prop('disabled', false);
            $('#editUpdateTypeField').show();
            loadStockBreakdown();
        } else {
            $('#editStockQuantityField').hide();
            $('#editStockQuantity').prop('required', false).prop('disabled', true).val('');
            $('#editUpdateTypeField').hide();
            $('#stockBreakdownSection').hide();
        }
    });

    // Form submission
    $('#inventoryForm').on('submit', function(e) {
        e.preventDefault();
        updateInventory();
    });

    // Stock fields are always enabled, no toggle needed
}

function clearFilters() {
    $('#filterStockStatus').val('');
    $('#filterLowStock').prop('checked', false);
    $('#filterWarehouse').val('');
    inventoryTable.draw();
}

// Load stock breakdown for current variant
function loadStockBreakdown() {
    let variantId = $('#inventoryProductId').val();
    if (!variantId) return;
    
    $.ajax({
        url: '{{ route("inventory.stock-breakdown", ":id") }}'.replace(':id', variantId),
        type: 'GET',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '<table class="table table-sm table-bordered">';
                html += '<thead><tr><th>Warehouse</th><th>Location</th><th>Quantity</th></tr></thead><tbody>';
                
                response.data.forEach(function(stock) {
                    html += `<tr>
                        <td>${stock.warehouse_name} (${stock.warehouse_code})</td>
                        <td>${stock.location_code}</td>
                        <td><strong>${stock.quantity}</strong></td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                html += `<p class="mb-0"><strong>Total Stock:</strong> ${response.total_stock}</p>`;
                
                $('#stockBreakdownContent').html(html);
                $('#stockBreakdownSection').show();
            } else {
                $('#stockBreakdownSection').hide();
            }
        }
    });
}

function toggleStockFields() {
    // Fields are always enabled, just hide/show product-specific fields for variants
    const isVariant = $('#isVariant').val() === '1';
    
    // Only hide product-specific fields (stock location, backorder) for variants
    if (!isVariant) {
        $('#editStockLocationField, #editBackorderField').show();
    } else {
        $('#editStockLocationField, #editBackorderField').hide(); // Hide product-only fields
    }
}

// Removed toggleVariants function - variants are now shown directly in the table

function editInventory(id, isVariant = true, variantName = '', variantSku = '') {
    // All rows are variants now
    $('#isVariant').val('1');
    
    // Get variant data from the table
    const allData = inventoryTable.rows({search: 'applied'}).data().toArray();
    const variantData = allData.find(v => v.id == id);
    
    if (!variantData) {
        showToast('Variant not found in the current table view. Please refresh the page.', 'error');
        return;
    }
    
    $('#inventoryProductId').val(id);
    $('#variantInfo').show();
    $('#variantName').text(variantName || variantData.name);
    $('#variantSku').text(variantSku || variantData.sku);
    $('#editManageStock').val(variantData.manage_stock === 'Yes' ? '1' : '0');
    
    // Reset warehouse fields
    $('#editWarehouse').val('');
    $('#editWarehouseLocation').empty().append('<option value="">No Specific Location</option>');
    $('#editWarehouseLocationField').hide();
    $('#editUpdateType').val('set');
    $('#editUpdateTypeField').hide();
    $('#editStockQuantity').val('').prop('required', false).prop('disabled', true);
    
    // Load stock breakdown
    loadStockBreakdown();
    
    // Show all variant-relevant fields
    $('#editWarehouseField').show();
    $('#editStockQuantityField').hide(); // Hide until warehouse is selected
    $('#editStockLocationField, #editBackorderField').hide(); // Hide fields not applicable to variants
    toggleStockFields();
    editModal.show();
}

let currentHistoryVariantId = null;

function viewHistory(variantId, variantName, variantSku) {
    // Store current variant ID for clear function
    currentHistoryVariantId = variantId;
    
    // Set variant info in modal
    $('#historyVariantName').text(variantName || 'N/A');
    $('#historyVariantSku').text(variantSku || 'N/A');
    
    // Clear previous history
    $('#historyTableBody').empty();
    $('#historyEmpty').hide();
    $('#clearHistoryBtn').hide();
    
    // Show loading
    $('#historyTableBody').html('<tr><td colspan="10" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
    
    // Show modal
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    historyModal.show();
    
    // Load history
    $.ajax({
        url: `/inventory/${variantId}/history`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data.history.length > 0) {
                let historyHtml = '';
                response.data.history.forEach(function(record) {
                    const changeClass = record.quantity_change > 0 ? 'text-success' : record.quantity_change < 0 ? 'text-danger' : 'text-muted';
                    const changeSign = record.quantity_change > 0 ? '+' : '';
                    
                    // Special styling for history cleared records
                    const isCleared = record.reference_type === 'history_cleared';
                    const rowClass = isCleared ? 'table-warning' : '';
                    const refBadgeClass = isCleared ? 'bg-warning text-dark' : 'bg-secondary';
                    
                    historyHtml += `
                        <tr class="${rowClass}">
                            <td>${record.created_at}</td>
                            <td>${record.warehouse_name}</td>
                            <td>${record.location_name}</td>
                            <td>${record.previous_quantity}</td>
                            <td><strong>${record.new_quantity}</strong></td>
                            <td class="${changeClass}"><strong>${changeSign}${record.quantity_change}</strong></td>
                            <td><span class="badge bg-info">${record.change_type}</span></td>
                            <td><span class="badge ${refBadgeClass}">${record.reference_type || 'N/A'}</span></td>
                            <td>${record.user_name}</td>
                            <td>${record.notes || '-'}</td>
                        </tr>
                    `;
                });
                $('#historyTableBody').html(historyHtml);
                // Show clear button if there are records (excluding the clearance record itself)
                const nonClearedRecords = response.data.history.filter(r => r.reference_type !== 'history_cleared');
                if (nonClearedRecords.length > 0) {
                    $('#clearHistoryBtn').show();
                }
            } else {
                $('#historyTableBody').empty();
                $('#historyEmpty').show();
                $('#clearHistoryBtn').hide();
            }
        },
        error: function(xhr) {
            $('#historyTableBody').html('<tr><td colspan="10" class="text-center text-danger">Error loading history</td></tr>');
            showToast('Error loading inventory history', 'error');
            $('#clearHistoryBtn').hide();
        }
    });
}

function clearInventoryHistory() {
    if (!currentHistoryVariantId) {
        showToast('Variant ID is missing', 'error');
        return;
    }
    
    if (!confirm('Are you sure you want to clear all inventory history for this variant? This action cannot be undone. A record of this clearance will be kept as the last activity.')) {
        return;
    }
    
    const btn = $('#clearHistoryBtn');
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Clearing...');
    
    $.ajax({
        url: `/inventory/${currentHistoryVariantId}/history/clear`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                // Reload history to show the clearance record
                const variantName = $('#historyVariantName').text();
                const variantSku = $('#historyVariantSku').text();
                viewHistory(currentHistoryVariantId, variantName, variantSku);
            } else {
                showToast(response.message || 'Error clearing history', 'error');
                btn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error clearing inventory history';
            showToast(errorMsg, 'error');
            btn.prop('disabled', false).html(originalHtml);
        }
    });
}

function updateInventory() {
    const id = $('#inventoryProductId').val();
    
    if (!id) {
        showToast('Product ID is missing. Please try again.', 'error');
        return;
    }
    
    const isVariant = $('#isVariant').val() === '1';
    const stockQuantity = $('#editStockQuantity').val();
    const warehouseId = $('#editWarehouse').val();
    
    // Validate warehouse selection if stock quantity is being updated
    if (stockQuantity !== '' && stockQuantity !== null && !warehouseId) {
        showToast('Please select a warehouse to update stock quantity', 'error');
        $('#editWarehouse').addClass('is-invalid');
        return;
    }
    
    const formData = {
        is_variant: isVariant,
        manage_stock: $('#editManageStock').val() === '1' ? 1 : 0,
    };
    
    // Only add stock_quantity if warehouse is selected
    if (warehouseId && stockQuantity !== '' && stockQuantity !== null) {
        formData.stock_quantity = stockQuantity;
        formData.warehouse_id = warehouseId;
        formData.warehouse_location_id = $('#editWarehouseLocation').val() || null;
        formData.update_type = $('#editUpdateType').val() || 'set';
    }
    
    // Add product-specific fields only if not a variant
    if (!isVariant) {
        formData.allow_backorder = $('#editAllowBackorder').is(':checked') ? 1 : 0;
        formData.stock_location = $('#editStockLocation').val();
    }

    $.ajax({
        url: '{{ route("inventory.update", ":id") }}'.replace(':id', id),
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Inventory updated successfully!');
                editModal.hide();
                inventoryTable.draw(false);
            } else {
                showToast('Error: ' + response.message, 'error');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            let errorMsg = 'Error updating inventory';
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showToast(errorMsg, 'error');
            
            // Show validation errors
            Object.keys(errors).forEach(function(key) {
                const fieldName = key.replace(/_([a-z])/g, (g) => g[0] + g[1].toUpperCase());
                const field = $(`#edit${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}`);
                if (field.length) {
                    field.addClass('is-invalid');
                    field.siblings('.invalid-feedback').text(errors[key][0]);
                }
            });
        }
    });
}

function showToast(message, type = 'success') {
    // Get or create toast container
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create unique toast element
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'error' 
        ? 'fas fa-exclamation-circle text-danger me-2' 
        : 'fas fa-check-circle text-success me-2';
    const title = type === 'error' ? 'Error' : 'Success';
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="${iconClass}"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    if (toastElement) {
        try {
            // Dispose of any existing toast instance
            const existingToast = bootstrap.Toast.getInstance(toastElement);
            if (existingToast) {
                existingToast.dispose();
            }
            
            const bsToast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
            
            bsToast.show();
        } catch (error) {
            console.error('Error showing toast:', error);
            // Fallback to alert if toast fails
            alert(message);
        }
    }
}

// Add Inventory Modal Functions
let addInventoryModal;
let selectedVariants = new Set(); // For variants only
let variantsData = []; // Flattened variants data

function openAddInventoryModal() {
    if (!addInventoryModal) {
        addInventoryModal = new bootstrap.Modal(document.getElementById('addInventoryModal'));
    }
    
    // Reset state
    selectedVariants.clear();
    variantsData = [];
    $('#productSelectionTableBody').empty();
    $('#selectedProductsSummary').hide();
    $('#stockInputSection').hide();
    $('#addStockBtn').prop('disabled', true);
    $('#productSearch').val('');
    $('#filterProductBrand').val('');
    $('#filterProductCategory').val('');
    $('#addStockWarehouse').val('');
    $('#addStockLocation').empty().append('<option value="">No Specific Location</option>');
    $('#stockQuantity').val(0);
    $('#addStockUpdateType').val('increment');
    
    // Load warehouses
    loadWarehousesForAddInventory();
    
    // Load variants
    loadVariantsForSelection();
    loadBrandsAndCategories();
    
    addInventoryModal.show();
}

function loadWarehousesForAddInventory() {
    $.ajax({
        url: '{{ route("inventory.warehouses") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let warehouseSelect = $('#addStockWarehouse');
                warehouseSelect.empty().append('<option value="">Select Warehouse</option>');
                
                response.data.forEach(function(warehouse) {
                    warehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                });
            }
        }
    });
}

// Load locations when warehouse is selected in Add Inventory modal
$('#addStockWarehouse').on('change', function() {
    const warehouseId = $(this).val();
    if (warehouseId) {
        $.ajax({
            url: '{{ route("inventory.warehouse-locations", ":id") }}'.replace(':id', warehouseId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let locationSelect = $('#addStockLocation');
                    locationSelect.empty().append('<option value="">No Specific Location</option>');
                    
                    response.data.forEach(function(location) {
                        locationSelect.append(`<option value="${location.id}">${location.full_location}</option>`);
                    });
                }
            }
        });
    } else {
        $('#addStockLocation').empty().append('<option value="">No Specific Location</option>');
    }
});

function loadBrandsAndCategories() {
    // Load brands
    fetch('{{ route("brands.data") }}')
        .then(response => response.json())
        .then(data => {
            const brandSelect = $('#filterProductBrand');
            brandSelect.empty().append('<option value="">All Brands</option>');
            if (data.data) {
                data.data.forEach(brand => {
                    brandSelect.append(`<option value="${brand.id}">${brand.name}</option>`);
                });
            }
        });
    
    // Load categories
    fetch('{{ route("categories.data") }}')
        .then(response => response.json())
        .then(data => {
            const categorySelect = $('#filterProductCategory');
            categorySelect.empty().append('<option value="">All Categories</option>');
            if (data.data) {
                data.data.forEach(category => {
                    const path = category.parent_name 
                        ? `${category.parent_name} > ${category.name}` 
                        : category.name;
                    categorySelect.append(`<option value="${category.id}">${path}</option>`);
                });
            }
        });
}

function loadVariantsForSelection() {
    const search = $('#productSearch').val();
    const brandId = $('#filterProductBrand').val();
    const categoryId = $('#filterProductCategory').val();
    
    // Use inventory.data route which includes warehouse breakdown
    let url = '{{ route("inventory.data") }}?';
    const params = new URLSearchParams();
    // DataTable required parameters
    params.append('draw', '1');
    params.append('start', '0');
    params.append('length', '1000'); // Get more variants
    if (search) {
        params.append('search[value]', search);
    }
    
    fetch(url + params.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Inventory data received:', data); // Debug log
            const inventoryData = data.data || [];
            
            // Filter by brand and category (need to check product data)
            // Since inventory.data returns variants directly, we need to filter differently
            // For now, we'll use all variants and filter client-side if needed
            // Note: inventory.data doesn't include brand/category filters, so we'll show all
            let filteredVariants = inventoryData;
            
            // Convert inventory data format to variantsData format
            variantsData = filteredVariants.map(variant => {
                return {
                    id: variant.id,
                    name: variant.name || 'Variant',
                    sku: variant.sku,
                    image_url: variant.image_url || '/assets/images/placeholder.jpg',
                    product_id: variant.product_id,
                    product_name: variant.product_name,
                    type: variant.type || 'Variant',
                    manage_stock: variant.manage_stock || 'No',
                    stock_quantity: variant.stock_quantity || 0,
                    stock_status: variant.stock_status || 'In Stock',
                    stock_status_value: variant.stock_status_value || 'in_stock',
                    low_stock_threshold: variant.low_stock_threshold || 0,
                    warehouse_breakdown: variant.warehouse_breakdown || []
                };
            });
            
            // Apply brand and category filters if needed (would require additional API call)
            // For now, we'll show all variants from inventory
            
            console.log('Variants loaded:', variantsData.length); // Debug log
            
            renderVariantsSelectionTable();
        })
        .catch(error => {
            console.error('Error loading variants:', error);
            showToast('Error loading variants: ' + error.message, 'error');
        });
}

function renderVariantsSelectionTable() {
    const tbody = $('#productSelectionTableBody');
    tbody.empty();
    
    if (variantsData.length === 0) {
        const message = 'No variants found.';
        tbody.append(`<tr><td colspan="9" class="text-center text-muted p-4">${message}</td></tr>`);
        return;
    }
    
    variantsData.forEach(variant => {
        const isVariantSelected = selectedVariants.has(variant.id);
        const variantRow = `
            <tr data-variant-id="${variant.id}" data-product-id="${variant.product_id}">
                <td>
                    <input type="checkbox" class="form-check-input variant-checkbox" 
                           data-variant-id="${variant.id}" 
                           ${isVariantSelected ? 'checked' : ''}
                           onchange="toggleVariantSelection('${variant.id}', this.checked)">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${variant.image_url || '/assets/images/placeholder.jpg'}" 
                             alt="${variant.name}" 
                             class="product-image me-3"
                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"
                             onerror="this.src='/assets/images/placeholder.jpg'">
                        <div>
                            <div class="fw-bold d-flex align-items-center gap-2">
                                <span>${variant.name}</span>
                                <small class="text-muted">(${variant.sku})</small>
                            </div>
                            <small class="text-muted">Variant</small>
                        </div>
                    </div>
                </td>
                <td>${variant.sku}</td>
                <td>
                    <a href="/products/${variant.product_id}" target="_blank" class="text-decoration-none fw-bold">
                        ${variant.product_name}
                    </a>
                </td>
                <td>
                    <span class="badge ${variant.type === 'Variant' ? 'bg-info' : 'bg-success'}">
                        ${variant.type}
                    </span>
                </td>
                <td>${variant.stock_quantity || 0}</td>
                <td>
                    ${variant.warehouse_breakdown && variant.warehouse_breakdown.length > 0 
                        ? `<div class="d-flex flex-column gap-1">${variant.warehouse_breakdown.map(stock => 
                            `<div class="small">
                                <span class="badge bg-primary">${stock.warehouse_name || 'N/A'}</span>
                                <span class="text-muted">(${stock.quantity || 0})</span>
                            </div>`
                          ).join('')}</div>`
                        : '<span class="text-muted">-</span>'
                    }
                </td>
                <td>
                    <span class="stock-status stock-${variant.stock_status_value.replace(/_/g, '-')}">
                        ${variant.stock_status}
                    </span>
                </td>
                <td>${variant.low_stock_threshold || 0}</td>
            </tr>
        `;
        tbody.append(variantRow);
    });
}

function toggleVariantSelection(variantId, isSelected) {
    if (isSelected) {
        selectedVariants.add(variantId);
    } else {
        selectedVariants.delete(variantId);
    }
    updateSelectionSummary();
}

function toggleVariantSelection(variantId, isSelected) {
    if (isSelected) {
        selectedVariants.add(variantId);
    } else {
        selectedVariants.delete(variantId);
    }
    
    updateSelectionSummary();
}

function updateSelectionSummary() {
    const variantCount = selectedVariants.size;
    
    $('#selectedCount').text(variantCount);
    const summaryHtml = `<span id="selectedCount">${variantCount}</span> variant(s) selected`;
    $('#selectedProductsSummary').find('strong').html(summaryHtml);
    
    if (variantCount > 0) {
        $('#selectedProductsSummary').show();
        $('#stockInputSection').show();
        $('#addStockBtn').prop('disabled', false);
    } else {
        $('#selectedProductsSummary').hide();
        $('#stockInputSection').hide();
        $('#addStockBtn').prop('disabled', true);
    }
}

function clearSelection() {
    selectedVariants.clear();
    $('.variant-checkbox').prop('checked', false);
    $('#selectAllVariants').prop('checked', false);
    updateSelectionSummary();
}

$('#selectAllVariants').on('change', function() {
    const isChecked = $(this).is(':checked');
    
    // Select/deselect all variants
    $('.variant-checkbox').prop('checked', isChecked);
    
    // Update selected sets
    if (isChecked) {
        $('.variant-checkbox').each(function() {
            const variantId = $(this).data('variant-id');
            selectedVariants.add(variantId);
        });
    } else {
        selectedVariants.clear();
    }
    
    updateSelectionSummary();
});

// Search and filter handlers
$('#productSearch').on('keyup', debounce(function() {
    loadVariantsForSelection();
}, 300));

$('#filterProductBrand, #filterProductCategory').on('change', function() {
    loadVariantsForSelection();
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function addStockToSelected() {
    const stockQuantity = parseInt($('#stockQuantity').val());
    const warehouseId = $('#addStockWarehouse').val();
    const locationId = $('#addStockLocation').val();
    const updateType = $('#addStockUpdateType').val();
    
    if (!warehouseId) {
        showToast('Please select a warehouse', 'error');
        return;
    }
    
    if (!stockQuantity || stockQuantity < 0) {
        showToast('Please enter a valid stock quantity', 'error');
        return;
    }
    
    const variantIds = Array.from(selectedVariants);
    
    if (variantIds.length === 0) {
        showToast('Please select at least one variant', 'error');
        return;
    }
    
    // Separate product IDs (prefixed with "product_") from variant IDs
    const productIds = variantIds.filter(id => String(id).startsWith('product_')).map(id => String(id).replace('product_', ''));
    const actualVariantIds = variantIds.filter(id => !String(id).startsWith('product_'));
    
    // Confirm action
    const updateTypeText = updateType === 'set' ? 'Set' : 'Add';
    let confirmMessage = `${updateTypeText} ${stockQuantity} units to ${variantIds.length} selected variant(s) in warehouse?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Disable button during processing
    $('#addStockBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');
    
    $.ajax({
        url: '{{ route("inventory.bulk-add") }}',
        method: 'POST',
        data: {
            product_ids: productIds,
            variant_ids: actualVariantIds,
            stock_quantity: stockQuantity,
            warehouse_id: warehouseId,
            warehouse_location_id: locationId || null,
            update_type: updateType,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let message = response.message || 'Stock added successfully!';
                
                // Show errors if any products were skipped
                if (response.errors && response.errors.length > 0) {
                    message += '\n\n' + response.errors.join('\n');
                }
                
                if (response.updated > 0) {
                    showToast(message, 'success');
                    addInventoryModal.hide();
                    inventoryTable.draw(false);
                } else {
                    // No products were updated, show warning
                    showToast(message, 'error');
                }
                $('#addStockBtn').prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Stock');
            } else {
                showToast(response.message || 'Error adding stock', 'error');
                $('#addStockBtn').prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Stock');
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error adding stock';
            showToast(errorMsg, 'error');
            $('#addStockBtn').prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Stock');
        }
    });
}

// Import Inventory Modal Functions
let importInventoryModal;

function openImportInventoryModal() {
    if (!importInventoryModal) {
        importInventoryModal = new bootstrap.Modal(document.getElementById('importInventoryModal'));
    }
    
    // Reset form
    $('#importInventoryForm')[0].reset();
    $('#sampleFileType').val('default');
    $('#sampleWarehouse').val('');
    $('#importWarehouse').val('');
    $('#importProgress').hide();
    $('#importErrors').hide();
    $('#importErrorsList').empty();
    $('#importBtn').prop('disabled', false);
    
    importInventoryModal.show();
}

function downloadSampleFile(event) {
    event.preventDefault();
    const type = $('#sampleFileType').val();
    const warehouseId = $('#sampleWarehouse').val();
    let url = '{{ route("inventory.sample") }}?type=' + type;
    if (warehouseId) {
        url += '&warehouse_id=' + warehouseId;
    }
    window.location.href = url;
}

function showWarehouseCodesModal() {
    const modal = new bootstrap.Modal(document.getElementById('warehouseCodesModal'));
    const loadingEl = document.getElementById('warehouseCodesLoading');
    const contentEl = document.getElementById('warehouseCodesContent');
    const emptyEl = document.getElementById('warehouseCodesEmpty');
    const tableContainer = document.getElementById('warehouseCodesTableContainer');
    
    // Reset display
    loadingEl.style.display = 'block';
    contentEl.style.display = 'none';
    emptyEl.style.display = 'none';
    
    // Show modal
    modal.show();
    
    // Fetch data
    $.ajax({
        url: '{{ route("inventory.warehouse-codes-reference") }}',
        type: 'GET',
        success: function(response) {
            loadingEl.style.display = 'none';
            
            if (response.success && response.data && response.data.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover table-sm">';
                html += '<thead class="table-light"><tr><th>Warehouse Name</th><th>Warehouse Code</th><th>Location Code</th><th>Rack</th><th>Shelf</th><th>Bin</th></tr></thead>';
                html += '<tbody>';
                
                response.data.forEach(function(warehouse) {
                    if (warehouse.locations && warehouse.locations.length > 0) {
                        warehouse.locations.forEach(function(location, index) {
                            html += '<tr>';
                            if (index === 0) {
                                html += `<td rowspan="${warehouse.locations.length}" class="align-middle"><strong>${warehouse.name}</strong></td>`;
                                html += `<td rowspan="${warehouse.locations.length}" class="align-middle"><code>${warehouse.code}</code></td>`;
                            }
                            html += `<td><code>${location.code || 'N/A'}</code></td>`;
                            html += `<td>${location.rack || '-'}</td>`;
                            html += `<td>${location.shelf || '-'}</td>`;
                            html += `<td>${location.bin || '-'}</td>`;
                            html += '</tr>';
                        });
                    } else {
                        // Warehouse with no locations
                        html += '<tr>';
                        html += `<td><strong>${warehouse.name}</strong></td>`;
                        html += `<td><code>${warehouse.code}</code></td>`;
                        html += '<td colspan="4" class="text-muted">No locations configured</td>';
                        html += '</tr>';
                    }
                });
                
                html += '</tbody></table></div>';
                tableContainer.innerHTML = html;
                contentEl.style.display = 'block';
            } else {
                emptyEl.style.display = 'block';
            }
        },
        error: function(xhr) {
            loadingEl.style.display = 'none';
            emptyEl.style.display = 'block';
            emptyEl.innerHTML = '<div class="alert alert-danger"><i class="bx bx-error me-2"></i>Error loading warehouse codes. Please try again.</div>';
        }
    });
}

function exportInventory() {
    const warehouseId = $('#filterWarehouse').val();
    const stockStatus = $('#filterStockStatus').val();
    const lowStock = $('#filterLowStock').is(':checked') ? '1' : '';
    
    let url = '{{ route("inventory.export") }}?';
    const params = new URLSearchParams();
    
    if (warehouseId) {
        params.append('warehouse_id', warehouseId);
    }
    if (stockStatus) {
        params.append('stock_status', stockStatus);
    }
    if (lowStock) {
        params.append('low_stock', lowStock);
    }
    
    window.location.href = url + params.toString();
}

// Handle import form submission
$('#importInventoryForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = $('#importFile')[0];
    const warehouseId = $('#importWarehouse').val();
    
    if (!fileInput.files || !fileInput.files[0]) {
        showToast('Please select a file to import', 'error');
        return;
    }
    
    // Add warehouse_id to form data if selected
    if (warehouseId) {
        formData.append('warehouse_id', warehouseId);
    }
    
    // Show progress
    $('#importProgress').show();
    $('#importErrors').hide();
    $('#importBtn').prop('disabled', true);
    
    $.ajax({
        url: '{{ route("inventory.import") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#importProgress').hide();
            $('#importBtn').prop('disabled', false);
            
            if (response.success) {
                showToast(response.message, 'success');
                importInventoryModal.hide();
                
                // Refresh inventory table
                if (inventoryTable) {
                    inventoryTable.draw(false);
                }
                
                // Show errors if any
                if (response.errors && response.errors.length > 0) {
                    let errorsHtml = '';
                    response.errors.forEach(function(error) {
                        errorsHtml += '<li>' + error + '</li>';
                    });
                    $('#importErrorsList').html(errorsHtml);
                    $('#importErrors').show();
                }
            } else {
                showToast(response.message || 'Import failed', 'error');
                if (response.errors && response.errors.length > 0) {
                    let errorsHtml = '';
                    response.errors.forEach(function(error) {
                        errorsHtml += '<li>' + error + '</li>';
                    });
                    $('#importErrorsList').html(errorsHtml);
                    $('#importErrors').show();
                }
            }
        },
        error: function(xhr) {
            $('#importProgress').hide();
            $('#importBtn').prop('disabled', false);
            
            let errorMessage = 'Error importing file';
            let hasErrors = false;
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (xhr.responseJSON.errors) {
                    hasErrors = true;
                    let errorsHtml = '';
                    if (Array.isArray(xhr.responseJSON.errors)) {
                        // Array of error strings
                        xhr.responseJSON.errors.forEach(function(error) {
                            errorsHtml += '<li>' + error + '</li>';
                        });
                    } else {
                        // Object with field errors
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            const fieldErrors = Array.isArray(xhr.responseJSON.errors[key]) 
                                ? xhr.responseJSON.errors[key] 
                                : [xhr.responseJSON.errors[key]];
                            fieldErrors.forEach(function(error) {
                                errorsHtml += '<li>' + error + '</li>';
                            });
                        });
                    }
                    $('#importErrorsList').html(errorsHtml);
                    $('#importErrors').show();
                }
            } else if (xhr.status === 404) {
                errorMessage = 'Import route not found. Please check the route configuration.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            }
            
            showToast(errorMessage, 'error');
            
            if (!hasErrors && xhr.responseJSON && xhr.responseJSON.message) {
                // Show error message in modal if not already shown
                $('#importErrorsList').html('<li>' + errorMessage + '</li>');
                $('#importErrors').show();
            }
        }
    });
});

// Toggle collapse icon for import instructions
$('#importInstructionsCollapse').on('show.bs.collapse', function() {
    $('#importInstructionsIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
});

$('#importInstructionsCollapse').on('hide.bs.collapse', function() {
    $('#importInstructionsIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
});
</script>
@endpush

