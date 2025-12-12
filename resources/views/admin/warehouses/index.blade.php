@extends('layouts.admin')

@section('title', 'Warehouses')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Warehouses</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Warehouses</h1>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteWarehousesBtn">
                    <i class="fas fa-trash me-2"></i>Delete Selected
                </button>
                <button class="btn btn-primary" id="addWarehouseBtn">
                    <i class="fas fa-plus"></i> Add Warehouse
                </button>
            </div>
        </div>

        <!-- Warehouses Table -->
        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <input type="text" placeholder="Search warehouses..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="warehousesTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAllWarehouses" title="Select All">
                            </th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Country</th>
                            <th>Locations</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="warehousesTableBody">
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- AJAX Pagination -->
            <div class="d-flex justify-content-center p-4" id="paginationContainer">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Warehouse Modal -->
<div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warehouseModalLabel">Add Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="warehouseForm">
                    <input type="hidden" id="warehouseId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="warehouseName" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="warehouseName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="warehouseCode" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="warehouseCode" name="code" required>
                            <small class="form-text text-muted">Unique warehouse code</small>
                            <div class="invalid-feedback" id="codeError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="warehouseAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="warehouseAddress" name="address" rows="2"></textarea>
                            <div class="invalid-feedback" id="addressError"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="warehouseCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="warehouseCity" name="city">
                            <div class="invalid-feedback" id="cityError"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="warehouseState" class="form-label">State</label>
                            <input type="text" class="form-control" id="warehouseState" name="state">
                            <div class="invalid-feedback" id="stateError"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="warehouseCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="warehouseCountry" name="country">
                            <div class="invalid-feedback" id="countryError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="warehouseStatus" class="form-label">Status</label>
                            <select class="form-select" id="warehouseStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="statusError"></div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="warehouseIsDefault" name="is_default" value="1">
                                <label class="form-check-label" for="warehouseIsDefault">
                                    <strong>Set as Primary/Default Warehouse</strong>
                                    <small class="text-muted d-block">This warehouse will be used as the default when no warehouse is specified. Only one warehouse can be set as primary.</small>
                                </label>
                            </div>
                            <div class="invalid-feedback" id="isDefaultError"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWarehouseBtn">
                    <span id="saveBtnText">Save Warehouse</span>
                    <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this warehouse? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Locations Management Modal -->
<div class="modal fade" id="locationsModal" tabindex="-1" aria-labelledby="locationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationsModalLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>Manage Locations - <span id="locationsWarehouseName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="locationsAlertContainer"></div>
                
                <!-- Add Location Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <button type="button" class="btn btn-primary" id="addLocationBtn">
                            <i class="fas fa-plus me-2"></i>Add Location
                        </button>
                    </div>
                    <div>
                        <input type="text" placeholder="Search locations..." class="form-control form-control-sm" id="locationsSearch" style="width: 250px;">
                    </div>
                </div>
                
                <!-- Locations Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="locationsTable">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAllLocations" title="Select All">
                                </th>
                                <th>Rack</th>
                                <th>Shelf</th>
                                <th>Bin</th>
                                <th>Location Code</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="locationsTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Bulk Delete Button -->
                <div class="mt-3 d-none" id="bulkDeleteLocationsBtnContainer">
                    <button type="button" class="btn btn-outline-danger" id="bulkDeleteLocationsBtn">
                        <i class="fas fa-trash me-2"></i>Delete Selected
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Location Modal -->
<div class="modal fade" id="locationFormModal" tabindex="-1" aria-labelledby="locationFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationFormModalLabel">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="locationFormAlertContainer"></div>
                
                <form id="locationForm">
                    <input type="hidden" id="locationId" name="id">
                    <input type="hidden" id="locationWarehouseId" name="warehouse_id">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="locationRack" class="form-label">Rack</label>
                            <input type="text" class="form-control" id="locationRack" name="rack" placeholder="e.g., A, 1, Rack-A">
                            <small class="text-muted">Optional</small>
                            <div class="invalid-feedback" id="rackError"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="locationShelf" class="form-label">Shelf</label>
                            <input type="text" class="form-control" id="locationShelf" name="shelf" placeholder="e.g., 1, 2, Shelf-1">
                            <small class="text-muted">Optional</small>
                            <div class="invalid-feedback" id="shelfError"></div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="locationBin" class="form-label">Bin</label>
                            <input type="text" class="form-control" id="locationBin" name="bin" placeholder="e.g., 1, 2, Bin-A">
                            <small class="text-muted">Optional</small>
                            <div class="invalid-feedback" id="binError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="locationStatus" class="form-label">Status</label>
                            <select class="form-select" id="locationStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="statusError"></div>
                        </div>
                        
                        <div class="col-12">
                            <div class="alert alert-info">
                                <small>
                                    <strong>Note:</strong> Location code will be automatically generated as: Rack-Shelf-Bin
                                    <br>At least one field (Rack, Shelf, or Bin) should be filled.
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLocationBtn">
                    <span id="saveLocationBtnText">Save Location</span>
                    <span id="saveLocationBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let deleteWarehouseId = null;
    let currentPage = 1;
    
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Load warehouses on page load
    loadWarehouses();
    
    // Status Filter Change
    $('#statusFilter').change(function() {
        currentPage = 1;
        loadWarehouses();
    });
    
    // Search functionality with debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadWarehouses();
        }, 500);
    });
    
    // Open Modal for Add
    $('#addWarehouseBtn').on('click', function() {
        $('#warehouseModalLabel').text('Add Warehouse');
        $('#warehouseForm')[0].reset();
        $('#warehouseId').val('');
        $('#warehouseStatus').val('active');
        $('#warehouseIsDefault').prop('checked', false);
        clearModalAlerts();
        $('#warehouseModal').modal('show');
    });
    
    // Open Modal for Edit
    $(document).on('click', '.edit-warehouse', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        
        $('#warehouseModalLabel').text('Edit Warehouse');
        clearModalAlerts();
        
        $.ajax({
            url: '{{ route("warehouses.edit", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let warehouse = response.data;
                    
                    $('#warehouseId').val(warehouse.id);
                    $('#warehouseName').val(warehouse.name);
                    $('#warehouseCode').val(warehouse.code);
                    $('#warehouseAddress').val(warehouse.address);
                    $('#warehouseCity').val(warehouse.city);
                    $('#warehouseState').val(warehouse.state);
                    $('#warehouseCountry').val(warehouse.country);
                    $('#warehouseStatus').val(warehouse.status);
                    $('#warehouseIsDefault').prop('checked', warehouse.is_default || false);
                    
                    $('#warehouseModal').modal('show');
                }
            },
            error: function(xhr) {
                showToast('error', 'Error loading warehouse data');
            }
        });
    });
    
    // Save Warehouse (Create or Update)
    $('#saveWarehouseBtn').on('click', function() {
        let id = $('#warehouseId').val();
        let url = id ? '{{ route("warehouses.update", ":id") }}'.replace(':id', id) : '{{ route("warehouses.store") }}';
        
        let formData = {
            id: id,
            name: $('#warehouseName').val(),
            code: $('#warehouseCode').val(),
            address: $('#warehouseAddress').val(),
            city: $('#warehouseCity').val(),
            state: $('#warehouseState').val(),
            country: $('#warehouseCountry').val(),
            status: $('#warehouseStatus').val(),
            is_default: $('#warehouseIsDefault').is(':checked') ? 1 : 0
        };
        
        // Show loading
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveWarehouseBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#warehouseModal').modal('hide');
                    loadWarehouses();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    
                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('').hide();
                    
                    // Mark fields as invalid
                    Object.keys(errors).forEach(function(field) {
                        let fieldId = 'warehouse' + field.charAt(0).toUpperCase() + field.slice(1);
                        $('#' + fieldId).addClass('is-invalid');
                        $('#' + fieldId + 'Error').text(errors[field][0]).show();
                    });
                } else {
                    showModalAlert(xhr.responseJSON?.message || 'An error occurred. Please try again.', 'danger');
                }
            },
            complete: function() {
                $('#saveBtnText').removeClass('d-none');
                $('#saveBtnSpinner').addClass('d-none');
                $('#saveWarehouseBtn').prop('disabled', false);
            }
        });
    });
    
    // Helper function to show alert in modal
    function showModalAlert(message, type = 'danger') {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#modalAlertContainer').html(alertHtml);
    }
    
    // Helper function to clear modal alerts
    function clearModalAlerts() {
        $('#modalAlertContainer').empty();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        currentPage = page;
        loadWarehouses();
    });
    
    // Delete Warehouse Click
    $(document).on('click', '.delete-warehouse', function(e) {
        e.preventDefault();
        deleteWarehouseId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    // Confirm Delete Button Click
    $('#confirmDelete').click(function() {
        deleteWarehouse();
    });
    
    // Load Warehouses Function
    function loadWarehouses() {
        let search = $('#tableSearch').val();
        let status = $('#statusFilter').val();
        
        let params = {
            draw: 1,
            start: (currentPage - 1) * 20,
            length: 20
        };
        
        if (search) {
            params.search = { value: search };
        }
        if (status !== '') {
            params.status = status;
        }
        
        $.ajax({
            url: '{{ route("warehouses.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                let tbody = $('#warehousesTableBody');
                tbody.empty();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(function(warehouse) {
                        let statusBadge = warehouse.status === 'active' 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let addressHtml = warehouse.address 
                            ? warehouse.address.substring(0, 50) + (warehouse.address.length > 50 ? '...' : '')
                            : '<span class="text-muted">-</span>';
                        
                        let row = `
                            <tr data-id="${warehouse.id}">
                                <td>
                                    <input type="checkbox" class="form-check-input warehouse-checkbox" value="${warehouse.id}">
                                </td>
                                <td>
                                    <strong>${warehouse.name}</strong>
                                </td>
                                <td><code>${warehouse.code}</code></td>
                                <td>${addressHtml}</td>
                                <td>${warehouse.city || '<span class="text-muted">-</span>'}</td>
                                <td>${warehouse.state || '<span class="text-muted">-</span>'}</td>
                                <td>${warehouse.country || '<span class="text-muted">-</span>'}</td>
                                <td><span class="badge bg-info">${warehouse.locations_count || 0}</span></td>
                                <td>${statusBadge}</td>
                                <td>${warehouse.is_default ? '<span class="badge bg-primary"><i class="fas fa-star me-1"></i>Primary</span>' : '<span class="text-muted">-</span>'}</td>
                                <td>${new Date(warehouse.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info manage-locations" data-id="${warehouse.id}" data-name="${warehouse.name}" title="Manage Locations">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                        <button class="btn btn-outline-primary edit-warehouse" data-id="${warehouse.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-warehouse" data-id="${warehouse.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="12" class="text-center py-4">No warehouses found</td></tr>');
                }
                
                // Update pagination
                updatePagination(response);
                
                // Reset bulk delete button
                $('#selectAllWarehouses').prop('checked', false);
                updateBulkDeleteButton();
            },
            error: function(xhr) {
                console.error('Error loading warehouses:', xhr);
                $('#warehousesTableBody').html('<tr><td colspan="12" class="text-center py-4 text-danger">Error loading warehouses</td></tr>');
            }
        });
    }
    
    // Update Pagination
    function updatePagination(response) {
        let paginationHtml = '';
        let totalPages = Math.ceil(response.recordsFiltered / 20);
        
        if (totalPages > 1) {
            paginationHtml = '<nav><ul class="pagination">';
            
            // Previous button
            if (currentPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
            }
            
            // Page numbers
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                let activeClass = i == currentPage ? 'active' : '';
                paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }
            
            // Next button
            if (currentPage < totalPages) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${parseInt(currentPage) + 1}">Next</a></li>`;
            }
            
            paginationHtml += '</ul></nav>';
        }
        
        $('#paginationContainer').html(paginationHtml);
    }
    
    // Delete Warehouse
    function deleteWarehouse() {
        if(!deleteWarehouseId) return;
        
        $.ajax({
            url: '{{ route("warehouses.destroy", ":id") }}'.replace(':id', deleteWarehouseId),
            type: 'POST',
            data: {
                '_method': 'DELETE'
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#deleteModal').modal('hide');
                    loadWarehouses();
                    $('#selectAllWarehouses').prop('checked', false);
                    updateBulkDeleteButton();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', xhr.responseJSON.message || 'Cannot delete warehouse');
                } else {
                    showToast('error', 'Failed to delete warehouse');
                }
            },
            complete: function() {
                deleteWarehouseId = null;
            }
        });
    }
    
    // Select All Checkboxes
    $('#selectAllWarehouses').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.warehouse-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox change
    $(document).on('change', '.warehouse-checkbox', function() {
        let totalCheckboxes = $('.warehouse-checkbox').length;
        let checkedCheckboxes = $('.warehouse-checkbox:checked').length;
        $('#selectAllWarehouses').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateBulkDeleteButton();
    });
    
    function updateBulkDeleteButton() {
        const checkedCount = $('.warehouse-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteWarehousesBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteWarehousesBtn').addClass('d-none');
        }
    }
    
    function bulkDeleteWarehouses() {
        const checkedBoxes = $('.warehouse-checkbox:checked');
        if (checkedBoxes.length === 0) {
            showToast('error', 'Please select at least one warehouse to delete');
            return;
        }
        
        const ids = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        
        if (!confirm(`Are you sure you want to delete ${ids.length} warehouse(s)? This action cannot be undone.`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("warehouses.bulk-delete") }}',
            type: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    loadWarehouses();
                    $('#selectAllWarehouses').prop('checked', false);
                    updateBulkDeleteButton();
                } else {
                    let errorMsg = response.message || 'Failed to delete warehouses';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '<br>' + response.errors.join('<br>');
                    }
                    showToast('error', errorMsg);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to delete warehouses';
                showToast('error', message);
            }
        });
    }

    // Bulk delete button click handler
    $(document).on('click', '#bulkDeleteWarehousesBtn', function(e) {
        e.preventDefault();
        bulkDeleteWarehouses();
    });

    // Show Toast
    // ==================== LOCATIONS MANAGEMENT ====================
    let currentWarehouseId = null;
    let currentWarehouseName = null;
    let locationsPage = 1;
    let deleteLocationId = null;
    
    // Open Locations Management Modal
    $(document).on('click', '.manage-locations', function() {
        currentWarehouseId = $(this).data('id');
        currentWarehouseName = $(this).data('name');
        $('#locationsWarehouseName').text(currentWarehouseName);
        $('#locationsModal').modal('show');
        locationsPage = 1;
        loadLocations();
    });
    
    // Load Locations
    function loadLocations() {
        if (!currentWarehouseId) return;
        
        $.ajax({
            url: '{{ route("warehouses.locations.data") }}',
            type: 'GET',
            data: {
                warehouse_id: currentWarehouseId,
                draw: 1,
                start: (locationsPage - 1) * 20,
                length: 20,
                search: {
                    value: $('#locationsSearch').val() || ''
                },
                status: ''
            },
            success: function(response) {
                const tbody = $('#locationsTableBody');
                tbody.empty();
                
                // Handle both DataTables format and simple array format
                let locations = [];
                if (response.data && Array.isArray(response.data)) {
                    locations = response.data;
                } else if (Array.isArray(response)) {
                    locations = response;
                }
                
                if (locations.length > 0) {
                    locations.forEach(function(location) {
                        // Handle status - can be boolean or string
                        const statusValue = location.status === true || location.status === 'active' || location.status === 1 ? 'active' : 'inactive';
                        const statusBadge = statusValue === 'active' 
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        // Format created_at - handle both date string and timestamp
                        let createdDate = 'N/A';
                        if (location.created_at) {
                            try {
                                createdDate = new Date(location.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            } catch(e) {
                                createdDate = location.created_at;
                            }
                        }
                        
                        const row = `
                            <tr data-id="${location.id}">
                                <td>
                                    <input type="checkbox" class="form-check-input location-checkbox" value="${location.id}">
                                </td>
                                <td>${location.rack || '<span class="text-muted">-</span>'}</td>
                                <td>${location.shelf || '<span class="text-muted">-</span>'}</td>
                                <td>${location.bin || '<span class="text-muted">-</span>'}</td>
                                <td><code>${location.location_code || 'N/A'}</code></td>
                                <td>${statusBadge}</td>
                                <td>${createdDate}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-location" data-id="${location.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-location" data-id="${location.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="8" class="text-center py-4">No locations found. Click "Add Location" to create one.</td></tr>');
                }
                
                updateLocationsBulkDeleteButton();
            },
            error: function(xhr) {
                console.error('Error loading locations:', xhr);
                console.error('Response:', xhr.responseJSON);
                let errorMsg = 'Error loading locations';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#locationsTableBody').html(`<tr><td colspan="8" class="text-center py-4 text-danger">${errorMsg}</td></tr>`);
            }
        });
    }
    
    // Search Locations
    let locationsSearchTimeout;
    $('#locationsSearch').on('keyup', function() {
        clearTimeout(locationsSearchTimeout);
        locationsSearchTimeout = setTimeout(function() {
            locationsPage = 1;
            loadLocations();
        }, 500);
    });
    
    // Add Location
    $('#addLocationBtn').on('click', function() {
        $('#locationFormModalLabel').text('Add Location');
        $('#locationForm')[0].reset();
        $('#locationId').val('');
        $('#locationWarehouseId').val(currentWarehouseId);
        $('#locationStatus').val('active');
        $('#locationFormAlertContainer').empty();
        $('#locationFormModal').modal('show');
    });
    
    // Edit Location
    $(document).on('click', '.edit-location', function() {
        const locationId = $(this).data('id');
        
        $.ajax({
            url: '{{ route("warehouses.locations.edit", ":id") }}'.replace(':id', locationId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const location = response.data;
                    $('#locationFormModalLabel').text('Edit Location');
                    $('#locationId').val(location.id);
                    $('#locationWarehouseId').val(location.warehouse_id);
                    $('#locationRack').val(location.rack || '');
                    $('#locationShelf').val(location.shelf || '');
                    $('#locationBin').val(location.bin || '');
                    $('#locationStatus').val(location.status || 'active');
                    $('#locationFormAlertContainer').empty();
                    $('#locationFormModal').modal('show');
                }
            },
            error: function(xhr) {
                showToast('error', 'Error loading location data');
            }
        });
    });
    
    // Save Location
    $('#saveLocationBtn').on('click', function() {
        const formData = {
            id: $('#locationId').val(),
            warehouse_id: $('#locationWarehouseId').val(),
            rack: $('#locationRack').val(),
            shelf: $('#locationShelf').val(),
            bin: $('#locationBin').val(),
            status: $('#locationStatus').val()
        };
        
        // Validate at least one field is filled
        if (!formData.rack && !formData.shelf && !formData.bin) {
            $('#locationFormAlertContainer').html(
                '<div class="alert alert-danger">Please fill at least one field (Rack, Shelf, or Bin)</div>'
            );
            return;
        }
        
        const url = formData.id 
            ? '{{ route("warehouses.locations.update", ":id") }}'.replace(':id', formData.id)
            : '{{ route("warehouses.locations.store") }}';
        const method = formData.id ? 'POST' : 'POST';
        
        if (formData.id) {
            formData._method = 'PUT';
        }
        
        $('#saveLocationBtnText').addClass('d-none');
        $('#saveLocationBtnSpinner').removeClass('d-none');
        $('#saveLocationBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message || 'Location saved successfully');
                    $('#locationFormModal').modal('hide');
                    loadLocations();
                    loadWarehouses(); // Refresh warehouse list to update location count
                } else {
                    $('#locationFormAlertContainer').html(
                        '<div class="alert alert-danger">' + (response.message || 'Error saving location') + '</div>'
                    );
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error saving location';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    let errorList = '<ul class="mb-0">';
                    Object.keys(errors).forEach(function(key) {
                        errors[key].forEach(function(msg) {
                            errorList += '<li>' + msg + '</li>';
                        });
                    });
                    errorList += '</ul>';
                    $('#locationFormAlertContainer').html('<div class="alert alert-danger">' + errorList + '</div>');
                } else {
                    $('#locationFormAlertContainer').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                }
            },
            complete: function() {
                $('#saveLocationBtnText').removeClass('d-none');
                $('#saveLocationBtnSpinner').addClass('d-none');
                $('#saveLocationBtn').prop('disabled', false);
            }
        });
    });
    
    // Delete Location
    $(document).on('click', '.delete-location', function() {
        deleteLocationId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this location? This action cannot be undone.')) {
            $.ajax({
                url: '{{ route("warehouses.locations.destroy", ":id") }}'.replace(':id', deleteLocationId),
                type: 'POST',
                data: {
                    '_method': 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message || 'Location deleted successfully');
                        loadLocations();
                        loadWarehouses(); // Refresh warehouse list to update location count
                    } else {
                        showToast('error', response.message || 'Error deleting location');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showToast('error', xhr.responseJSON.message || 'Cannot delete location');
                    } else {
                        showToast('error', 'Failed to delete location');
                    }
                },
                complete: function() {
                    deleteLocationId = null;
                }
            });
        }
    });
    
    // Select All Locations
    $(document).on('change', '#selectAllLocations', function() {
        $('.location-checkbox').prop('checked', $(this).is(':checked'));
        updateLocationsBulkDeleteButton();
    });
    
    // Location Checkbox Change
    $(document).on('change', '.location-checkbox', function() {
        updateLocationsBulkDeleteButton();
    });
    
    // Update Bulk Delete Button
    function updateLocationsBulkDeleteButton() {
        const checked = $('.location-checkbox:checked').length;
        if (checked > 0) {
            $('#bulkDeleteLocationsBtnContainer').removeClass('d-none');
        } else {
            $('#bulkDeleteLocationsBtnContainer').addClass('d-none');
        }
    }
    
    // Bulk Delete Locations
    $('#bulkDeleteLocationsBtn').on('click', function() {
        const selectedIds = $('.location-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            showToast('error', 'Please select at least one location to delete');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selectedIds.length} location(s)? This action cannot be undone.`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("warehouses.locations.bulk-delete") }}',
            type: 'POST',
            data: {
                ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message || 'Locations deleted successfully');
                    $('#selectAllLocations').prop('checked', false);
                    loadLocations();
                    loadWarehouses(); // Refresh warehouse list to update location count
                } else {
                    showToast('error', response.message || 'Error deleting locations');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error deleting locations';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showToast('error', errorMsg);
            }
        });
    });
    
    // Refresh locations when modal is shown
    $('#locationsModal').on('shown.bs.modal', function() {
        loadLocations();
    });
    
    function showToast(type, message) {
        const toast = $('#toast');
        if (!toast.length || !toast[0]) {
            console.error('Toast element not found');
            alert(message); // Fallback to alert
            return;
        }
        const toastBody = toast.find('.toast-body');
        const toastHeader = toast.find('.toast-header');
        if (toastBody.length) {
            toastBody.text(message);
        }
        if(type === 'success') {
            if (toastHeader.length) {
                toastHeader.html('<i class="fas fa-check-circle text-success me-2"></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
            }
        } else {
            if (toastHeader.length) {
                toastHeader.html('<i class="fas fa-times-circle text-danger me-2"></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
            }
        }
        try {
            const bsToast = new bootstrap.Toast(toast[0], {
                autohide: true,
                delay: 5000
            });
            bsToast.show();
        } catch (e) {
            console.error('Error showing toast:', e);
            alert(message); // Fallback to alert
        }
    }
});
</script>
@endpush

@endsection

