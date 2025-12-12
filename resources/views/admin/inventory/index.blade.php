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
                            <th>Manage Stock</th>
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
                            <label for="editWarehouse" class="form-label">Warehouse</label>
                            <select class="form-select" id="editWarehouse" name="warehouse_id">
                                <option value="">Select Warehouse (Optional)</option>
                            </select>
                            <small class="text-muted">Leave empty to update total stock</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6" id="editWarehouseLocationField" style="display: none;">
                            <label for="editWarehouseLocation" class="form-label">Location</label>
                            <select class="form-select" id="editWarehouseLocation" name="warehouse_location_id">
                                <option value="">No Specific Location</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6" id="editStockQuantityField">
                            <label for="editStockQuantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editStockQuantity" name="stock_quantity" min="0" required>
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

                        <div class="col-md-6" id="editStockStatusField">
                            <label for="editStockStatus" class="form-label">Stock Status</label>
                            <select class="form-select" id="editStockStatus" name="stock_status">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="on_backorder">On Backorder</option>
                            </select>
                            <small class="text-muted">Auto-updated based on quantity</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12" id="stockBreakdownSection" style="display: none;">
                            <hr>
                            <h6>Stock Breakdown by Warehouse</h6>
                            <div id="stockBreakdownContent"></div>
                        </div>

                        <div class="col-md-6" id="editLowStockField">
                            <label for="editLowStockThreshold" class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" id="editLowStockThreshold" name="low_stock_threshold" min="0" value="5">
                            <small class="text-muted">Alert when stock falls below this number</small>
                            <div class="invalid-feedback"></div>
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
                                <th>Manage Stock</th>
                                <th>Stock Quantity</th>
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
                                <label for="stockQuantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stockQuantity" min="0" value="0" required>
                                <small class="text-muted">Enter the quantity to add to selected products</small>
                            </div>
                            <div class="col-md-6">
                                <label for="stockStatus" class="form-label">Stock Status</label>
                                <select class="form-select" id="stockStatus">
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="on_backorder">On Backorder</option>
                                </select>
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
                                <li>Only fill in the columns you want to update</li>
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
                            <button type="button" class="btn btn-outline-secondary" id="downloadSampleBtn" onclick="downloadSampleFile(event)">
                                <i class="bx bx-download me-1"></i> Download
                            </button>
                        </div>
                        <small class="text-muted">Choose the type of sample file you want to download</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select File to Import <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".csv,.xls,.xlsx" required>
                        <small class="text-muted">Maximum file size: 10MB</small>
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
                
                warehouseSelect.empty().append('<option value="">All Warehouses</option>');
                editWarehouseSelect.empty().append('<option value="">Select Warehouse (Optional)</option>');
                
                response.data.forEach(function(warehouse) {
                    warehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                    editWarehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
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
                targets: 9, // Actions column
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
            { data: 'manage_stock' },
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
                        html += `<div class="small">
                            <span class="badge bg-primary">${stock.warehouse_name}</span>
                            <span class="text-muted">(${stock.quantity})</span>
                        </div>`;
                    });
                    html += `</div>`;
                    return html;
                }
            },
            {
                data: 'stock_quantity',
                render: function(data, type, row) {
                    const isLow = row.is_low_stock;
                    return `<span class="${isLow ? 'low-stock' : ''}">${data}</span>`;
                }
            },
            {
                data: 'stock_status',
                render: function(data, type, row) {
                    const statusClass = `stock-${row.stock_status_value.replace(/_/g, '-')}`;
                    return `<span class="stock-status ${statusClass}">${data}</span>`;
                }
            },
            { data: 'low_stock_threshold' },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="editInventory(${data}, true, '${row.name.replace(/'/g, "\\'")}', '${row.sku.replace(/'/g, "\\'")}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
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
        loadWarehouseLocations($(this).val());
        if ($(this).val()) {
            loadStockBreakdown();
        } else {
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
                html += '<thead><tr><th>Warehouse</th><th>Location</th><th>Quantity</th><th>Reserved</th><th>Available</th></tr></thead><tbody>';
                
                response.data.forEach(function(stock) {
                    html += `<tr>
                        <td>${stock.warehouse_name} (${stock.warehouse_code})</td>
                        <td>${stock.location_code}</td>
                        <td>${stock.quantity}</td>
                        <td>${stock.reserved_quantity}</td>
                        <td><strong>${stock.available_quantity}</strong></td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                html += `<p class="mb-0"><strong>Total Stock:</strong> ${response.total_stock} | <strong>Available:</strong> ${response.available_stock}</p>`;
                
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
    
    // Low stock threshold is available for both variants and products
    // Only hide product-specific fields (stock location, backorder) for variants
    if (!isVariant) {
        $('#editLowStockField, #editStockLocationField, #editBackorderField').show();
    } else {
        $('#editLowStockField').show(); // Show low stock threshold for variants
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
    $('#editStockQuantity').val(variantData.stock_quantity);
    $('#editStockStatus').val(variantData.stock_status_value);
    $('#editLowStockThreshold').val(variantData.low_stock_threshold || 0);
    
    // Reset warehouse fields
    $('#editWarehouse').val('');
    $('#editWarehouseLocation').empty().append('<option value="">No Specific Location</option>');
    $('#editWarehouseLocationField').hide();
    $('#editUpdateType').val('set');
    $('#editUpdateTypeField').hide();
    
    // Load stock breakdown
    loadStockBreakdown();
    
    // Show all variant-relevant fields including low stock threshold
    $('#editStockQuantityField, #editStockStatusField, #editLowStockField, #editWarehouseField').show();
    $('#editStockLocationField, #editBackorderField').hide(); // Hide fields not applicable to variants
    toggleStockFields();
    editModal.show();
}

function updateInventory() {
    const id = $('#inventoryProductId').val();
    
    if (!id) {
        showToast('Product ID is missing. Please try again.', 'error');
        return;
    }
    
    const isVariant = $('#isVariant').val() === '1';
    
    const formData = {
        is_variant: isVariant,
        manage_stock: $('#editManageStock').val() === '1' ? 1 : 0,
        stock_quantity: $('#editStockQuantity').val(),
        stock_status: $('#editStockStatus').val(),
    };
    
    // Add warehouse fields if warehouse is selected
    if ($('#editWarehouse').val()) {
        formData.warehouse_id = $('#editWarehouse').val();
        formData.warehouse_location_id = $('#editWarehouseLocation').val() || null;
        formData.update_type = $('#editUpdateType').val() || 'set';
    }
    
    // Add low_stock_threshold for variants
    if (isVariant) {
        formData.low_stock_threshold = $('#editLowStockThreshold').val() || 0;
    } else {
        // Add product-specific fields only if not a variant
        formData.allow_backorder = $('#editAllowBackorder').is(':checked') ? 1 : 0;
        formData.low_stock_threshold = $('#editLowStockThreshold').val();
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
    
    // Load variants
    loadVariantsForSelection();
    loadBrandsAndCategories();
    
    addInventoryModal.show();
}

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
    
    let url = '{{ route("products.data") }}?';
    const params = new URLSearchParams();
    // DataTable required parameters
    params.append('draw', '1');
    params.append('start', '0');
    params.append('length', '1000'); // Get more products
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
            console.log('Products data received:', data); // Debug log
            const productsData = data.data || [];
            
            // Filter by brand and category
            let filteredProducts = productsData;
            if (brandId) {
                filteredProducts = filteredProducts.filter(p => {
                    return p.brands && p.brands.some(b => b.id == brandId);
                });
            }
            if (categoryId) {
                filteredProducts = filteredProducts.filter(p => {
                    return p.category && p.category.id == categoryId;
                });
            }
            
            // Flatten variants: convert products to variants array
            variantsData = [];
            filteredProducts.forEach(product => {
                const hasVariants = product.has_variants || (product.variants && product.variants.length > 0);
                
                if (hasVariants && product.variants && product.variants.length > 0) {
                    // Product has variants - add each variant
                    product.variants.forEach(variant => {
                        // Use variant image if available, otherwise fallback to product image
                        const variantImageUrl = variant.image_url || product.thumbnail_url || '/assets/images/placeholder.jpg';
                        
                        variantsData.push({
                            id: variant.id,
                            name: variant.name || 'Variant',
                            sku: variant.sku,
                            image_url: variantImageUrl,
                            product_id: product.id,
                            product_name: product.name,
                            type: 'Variant',
                            manage_stock: variant.manage_stock ? 'Yes' : 'No',
                            stock_quantity: variant.stock_quantity || 0,
                            stock_status: variant.stock_status ? variant.stock_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'In Stock',
                            stock_status_value: variant.stock_status || 'in_stock',
                            low_stock_threshold: variant.low_stock_threshold || 0
                        });
                    });
                } else {
                    // Simple product - create a pseudo-variant entry
                    variantsData.push({
                        id: `product_${product.id}`, // Use prefix to distinguish from variant IDs
                        name: product.name,
                        sku: product.sku || '-',
                        image_url: product.thumbnail_url || '/assets/images/placeholder.jpg',
                        product_id: product.id,
                        product_name: product.name,
                        type: 'Product',
                        manage_stock: product.manage_stock ? 'Yes' : 'No',
                        stock_quantity: product.stock_quantity || 0,
                        stock_status: 'In Stock',
                        stock_status_value: 'in_stock',
                        low_stock_threshold: 0
                    });
                }
            });
            
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
                <td>${variant.manage_stock}</td>
                <td>${variant.stock_quantity}</td>
                <td>
                    <span class="stock-status stock-${variant.stock_status_value.replace(/_/g, '-')}">
                        ${variant.stock_status}
                    </span>
                </td>
                <td>${variant.low_stock_threshold}</td>
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
    const stockStatus = $('#stockStatus').val();
    
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
    let confirmMessage = `Add ${stockQuantity} units to ${variantIds.length} selected variant(s)?`;
    
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
            stock_status: stockStatus,
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
    $('#importProgress').hide();
    $('#importErrors').hide();
    $('#importErrorsList').empty();
    $('#importBtn').prop('disabled', false);
    
    importInventoryModal.show();
}

function downloadSampleFile(event) {
    event.preventDefault();
    const type = $('#sampleFileType').val();
    const url = '{{ route("inventory.sample") }}?type=' + type;
    window.location.href = url;
}

// Handle import form submission
$('#importInventoryForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = $('#importFile')[0];
    
    if (!fileInput.files || !fileInput.files[0]) {
        showToast('Please select a file to import', 'error');
        return;
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

