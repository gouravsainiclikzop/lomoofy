@extends('layouts.admin')

@section('title', 'Units')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
            <div class="row g-4 align-items-center">
                <div class="col">
                    <nav class="mb-2" aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-sa-simple">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Units</li>
                        </ol>
                    </nav>
                    <h1 class="h3 m-0">Units Management</h1>
                    <p class="text-muted mb-0">Manage measurement units for products. Units define how products are measured and sold (weight, volume, length, etc.)</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteUnitsBtn" onclick="bulkDeleteUnits()">
                        <i class="fas fa-trash me-2"></i>Delete Selected
                    </button>
                    <button class="btn btn-primary" id="addUnitBtn">
                        <i class="fas fa-plus me-2"></i>New Unit
                    </button>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <!-- <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-primary me-2"></i>What are Units?
                </h5>
                <p class="card-text mb-3">
                    Units define how products are measured and sold in your store. They help customers understand product quantities and enable proper inventory management.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Common Unit Types:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Weight/Mass:</strong> kg, g, lb, oz (for food, jewelry, etc.)</li>
                            <li><strong>Volume:</strong> L, ml, gal, fl oz (for liquids, beverages)</li>
                            <li><strong>Length:</strong> m, cm, in, ft (for fabric, cables, etc.)</li>
                            <li><strong>Area:</strong> m², ft² (for flooring, paint coverage)</li>
                            <li><strong>Time:</strong> h, min, day, mo, yr (for services, subscriptions)</li>
                            <li><strong>Other:</strong> pc, set, pack, box (for countable items)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">How Units Work:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Base Unit:</strong> The standard unit for calculations</li>
                            <li><strong>Conversion Factor:</strong> How to convert to base unit</li>
                            <li><strong>Example:</strong> 1 kg = 1000 g (factor: 1000)</li>
                            <li><strong>Product Display:</strong> Shows quantity with unit symbol</li>
                            <li><strong>Inventory:</strong> Tracks stock in base units</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="card">
            <div class="card-body p-4">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="length">Length / Distance</option>
                            <option value="weight">Weight / Mass</option>
                            <option value="volume">Volume</option>
                            <option value="time">Time</option>
                            <option value="temperature">Temperature</option>
                            <option value="area">Area</option>
                            <option value="angle">Angle</option>
                            <option value="energy">Energy / Power</option>
                            <option value="pressure">Pressure</option>
                            <option value="electric">Electric</option>
                            <option value="frequency">Frequency</option>
                            <option value="other">Other / Common</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <input type="text" placeholder="Start typing to search for units" class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
                <div class="table-responsive">
                <table class="table table-hover" id="unitsTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllUnits" class="form-check-input">
                            </th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="w-min"></th>
                        </tr>
                    </thead>
                    <tbody id="unitsTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Unit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Alert Container (for system errors only, not validation) -->
                <div id="modalAlertContainer"></div>
                
                <form id="unitForm">
                    <input type="hidden" id="unitId" name="id">
                    <input type="hidden" name="_method" id="httpMethod" value="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unitName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unitSymbol" name="symbol" required>
                            <div class="invalid-feedback" id="symbolError"></div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="unitType" name="type" required>
                                <option value="">Select Type</option>
                                <option value="length">Length / Distance</option>
                                <option value="weight">Weight / Mass</option>
                                <option value="volume">Volume</option>
                                <option value="time">Time</option>
                                <option value="temperature">Temperature</option>
                                <option value="area">Area</option>
                                <option value="angle">Angle</option>
                                <option value="energy">Energy / Power</option>
                                <option value="pressure">Pressure</option>
                                <option value="electric">Electric</option>
                                <option value="frequency">Frequency</option>
                                <option value="other">Other / Common</option>
                            </select>
                            <div class="invalid-feedback" id="typeError"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="unitIsActive" name="is_active" checked>
                            <label class="form-check-label" for="unitIsActive">
                                Active
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUnitBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                    <span id="saveBtnText">Save Unit</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this unit?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="deleteSpinner"></span>
                    <span id="deleteBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentUnitId = null;
    let deleteUnitId = null;
    
    // Load units on page load
    loadUnits();
    
    // Add Unit Button Click
    $('#addUnitBtn').click(function() {
        openCreateModal();
    });
    
    // Save Unit Button Click
    $('#saveUnitBtn').click(function(e) {
        e.preventDefault();
        saveUnit();
    });
    
    // Prevent form submission on Enter key
    $('#unitForm').on('submit', function(e) {
        e.preventDefault();
        saveUnit();
    });
    
    // Confirm Delete Button Click
    $('#confirmDeleteBtn').click(function() {
        deleteUnit();
    });
    
    // Type Filter Change
    $('#typeFilter').change(function() {
        loadUnits();
    });
    
    // Table Search
    $('#tableSearch').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        $('#unitsTableBody tr').each(function() {
            let rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Edit Unit Click
    $(document).on('click', '.edit-unit', function(e) {
        e.preventDefault();
        let unitId = $(this).data('id');
        editUnit(unitId);
    });
    
    // Delete Unit Click
    $(document).on('click', '.delete-unit', function(e) {
        e.preventDefault();
        deleteUnitId = $(this).data('id');
        showModalSafely('#deleteUnitModal');
    });
    
    // Toggle Status Click
    $(document).on('change', '.status-toggle', function() {
        let unitId = $(this).data('id');
        let isActive = $(this).is(':checked');
        toggleStatus(unitId, isActive);
    });
    
    // Select All Units
    $('#selectAllUnits').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.unit-checkbox').prop('checked', isChecked);
        updateBulkDeleteUnitsButton();
    });
    
    // Individual checkbox change
    $(document).on('change', '.unit-checkbox', function() {
        updateBulkDeleteUnitsButton();
    });
    
    function updateBulkDeleteUnitsButton() {
        const checkedCount = $('.unit-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteUnitsBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteUnitsBtn').addClass('d-none');
        }
    }
    
    function bulkDeleteUnits() {
        const checkedBoxes = $('.unit-checkbox:checked');
        if (checkedBoxes.length === 0) {
            showToast('Error', 'Please select at least one unit to delete', 'error');
            return;
        }
        
        const ids = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        
        if (!confirm(`Are you sure you want to delete ${ids.length} unit(s)? Units used by products cannot be deleted.`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("units.bulk-delete") }}',
            type: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    loadUnits();
                    $('#selectAllUnits').prop('checked', false);
                    updateBulkDeleteUnitsButton();
                } else {
                    let errorMsg = response.message || 'Failed to delete units';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '\n' + response.errors.join('\n');
                    }
                    showToast('Error', errorMsg, 'error');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to delete units';
                showToast('Error', message, 'error');
            }
        });
    }
    
    // Load Units Function
    function loadUnits() {
        let typeFilter = $('#typeFilter').val();
        let url = '{{ route("units.index") }}';
        if (typeFilter) {
            url += '?type=' + typeFilter;
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let tbody = $('#unitsTableBody');
                    tbody.empty();
                    
                    if(response.data.length > 0) {
                        response.data.forEach(function(unit) {
                            let statusBadge = unit.is_active 
                                ? '<span class="badge badge-sa-success">Active</span>' 
                                : '<span class="badge badge-sa-secondary">Inactive</span>';
                            
                            let row = `
                                <tr data-id="${unit.id}">
                                    <td>
                                        <input type="checkbox" class="form-check-input unit-checkbox" value="${unit.id}">
                                    </td>
                                    <td>
                                        <a href="#" class="text-reset fw-medium">${unit.name || '-'}</a>
                                    </td>
                                    <td>
                                        <span class="badge badge-sa-info">${unit.symbol}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sa-primary">${unit.type ? unit.type.charAt(0).toUpperCase() + unit.type.slice(1) : '-'}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" type="checkbox" data-id="${unit.id}" ${unit.is_active ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sa-muted btn-sm" type="button" data-bs-toggle="dropdown">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="3" height="13" fill="currentColor">
                                                    <path d="M1.5,8C0.7,8,0,7.3,0,6.5S0.7,5,1.5,5S3,5.7,3,6.5S2.3,8,1.5,8z M1.5,3C0.7,3,0,2.3,0,1.5S0.7,0,1.5,0 S3,0.7,3,1.5S2.3,3,1.5,3z M1.5,10C2.3,10,3,10.7,3,11.5S2.3,13,1.5,13S0,12.3,0,11.5S0.7,10,1.5,10z"></path>
                                                </svg>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item edit-unit" href="#" data-id="${unit.id}">Edit</a></li>
                                                <li><hr class="dropdown-divider"/></li>
                                                <li><a class="dropdown-item text-danger delete-unit" href="#" data-id="${unit.id}">Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center py-4">No units found</td></tr>');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading units:', xhr);
                $('#unitsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Error loading units</td></tr>');
            }
        });
    }
    
    // Safe modal close function (Bootstrap 5 compatible)
    function closeModalSafely(modalSelector) {
        try {
            const modalElement = document.querySelector(modalSelector);
            if (modalElement) {
                // Try Bootstrap 5 API first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        // If no instance exists, create one and hide
                        const newModal = new bootstrap.Modal(modalElement);
                        newModal.hide();
                    }
                } else if (typeof $.fn.modal !== 'undefined') {
                    // Fallback to jQuery/Bootstrap 4
                    $(modalSelector).modal('hide');
                } else {
                    // Last resort: hide via CSS
                    $(modalElement).removeClass('show').css('display', 'none');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
            }
        } catch (error) {
            console.error('Error closing modal:', error);
            // Fallback: hide via CSS
            $(modalSelector).removeClass('show').css('display', 'none');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    }
    
    // Safe modal show function (Bootstrap 5 compatible)
    function showModalSafely(modalSelector) {
        try {
            const modalElement = document.querySelector(modalSelector);
            if (modalElement) {
                // Try Bootstrap 5 API first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    if (!modalElement.classList.contains('show')) {
                        modal.show();
                    }
                } else if (typeof $.fn.modal !== 'undefined') {
                    // Fallback to jQuery/Bootstrap 4
                    if (!$(modalSelector).hasClass('show')) {
                        $(modalSelector).modal('show');
                    }
                } else {
                    // Last resort: show via CSS
                    $(modalElement).addClass('show').css('display', 'block');
                    $('body').addClass('modal-open');
                    $('.modal-backdrop').remove();
                    $('body').append('<div class="modal-backdrop fade show"></div>');
                }
            }
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback: show via CSS
            $(modalSelector).addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
            $('.modal-backdrop').remove();
            $('body').append('<div class="modal-backdrop fade show"></div>');
        }
    }
    
    // Open Create Modal
    function openCreateModal() {
        currentUnitId = null;
        $('#modalTitle').text('Add Unit');
        $('#unitForm')[0].reset();
        $('#unitIsActive').prop('checked', true);
        $('#httpMethod').val('POST');
        clearValidationErrors();
        showModalSafely('#unitModal');
    }
    
    // Edit Unit
    function editUnit(unitId) {
        currentUnitId = unitId;
        
        $.ajax({
            url: '{{ route("units.show", ":id") }}'.replace(':id', unitId),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let unit = response.unit;
                    $('#modalTitle').text('Edit Unit');
                    $('#unitId').val(unit.id);
                    $('#unitName').val(unit.name);
                    $('#unitSymbol').val(unit.symbol);
                    $('#unitType').val(unit.type);
                    $('#unitIsActive').prop('checked', unit.is_active);
                    $('#httpMethod').val('PUT');
                    
                    clearValidationErrors();
                    showModalSafely('#unitModal');
                }
            },
            error: function(xhr) {
                showToast('Error', 'Failed to load unit data', 'error');
            }
        });
    }
    
    // Save Unit
    function saveUnit() {
        // Clear previous validation errors
        clearValidationErrors();
        
        // Get form values explicitly to ensure all fields are included
        let formData = {
            name: $('#unitName').val(),
            symbol: $('#unitSymbol').val(),
            type: $('#unitType').val(),
            is_active: $('#unitIsActive').is(':checked') ? '1' : '0',
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Add _method for PUT requests
        if (currentUnitId) {
            formData._method = 'PUT';
        }
        
        // Validate required fields before submission
        if (!formData.name || !formData.symbol || !formData.type) {
            let missingFields = [];
            if (!formData.name) {
                $('#unitName').addClass('is-invalid');
                $('#nameError').text('The name field is required.');
                missingFields.push('Name');
            }
            if (!formData.symbol) {
                $('#unitSymbol').addClass('is-invalid');
                $('#symbolError').text('The symbol field is required.');
                missingFields.push('Symbol');
            }
            if (!formData.type) {
                $('#unitType').addClass('is-invalid');
                $('#typeError').text('The type field is required.');
                missingFields.push('Type');
            }
            showToast('Validation Error', 'Please fill in all required fields: ' + missingFields.join(', '), 'error');
            return;
        }
        
        let url = currentUnitId 
            ? '{{ route("units.update", ":id") }}'.replace(':id', currentUnitId)
            : '{{ route("units.store") }}';
        
        // Show loading state
        $('#saveSpinner').removeClass('d-none');
        $('#saveBtnText').text('Saving...');
        $('#saveUnitBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('Success', response.message, 'success');
                    // Close modal using Bootstrap 5 API
                    closeModalSafely('#unitModal');
                    loadUnits();
                } else {
                    showToast('Error', response.message || 'Failed to save unit', 'error');
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    displayValidationErrors(errors);
                    let errorMessage = xhr.responseJSON.message || 'Validation failed';
                    if (errors) {
                        let errorList = Object.values(errors).flat().join(', ');
                        errorMessage += ': ' + errorList;
                    }
                    showToast('Validation Error', errorMessage, 'error');
                } else {
                    showToast('Error', 'Failed to save unit', 'error');
                }
            },
            complete: function() {
                // Hide loading state
                $('#saveSpinner').addClass('d-none');
                $('#saveBtnText').text('Save Unit');
                $('#saveUnitBtn').prop('disabled', false);
            }
        });
    }
    
    // Delete Unit
    function deleteUnit() {
        if(!deleteUnitId) return;
        
        // Show loading state
        $('#deleteSpinner').removeClass('d-none');
        $('#deleteBtnText').text('Deleting...');
        $('#confirmDeleteBtn').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("units.destroy", ":id") }}'.replace(':id', deleteUnitId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('Success', response.message, 'success');
                    // Close modal using Bootstrap 5 API
                    closeModalSafely('#deleteUnitModal');
                    loadUnits();
                } else {
                    showToast('Error', response.message || 'Failed to delete unit', 'error');
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('Error', xhr.responseJSON.message || 'Cannot delete unit', 'error');
                } else {
                    showToast('Error', 'Failed to delete unit', 'error');
                }
            },
            complete: function() {
                // Hide loading state
                $('#deleteSpinner').addClass('d-none');
                $('#deleteBtnText').text('Delete');
                $('#confirmDeleteBtn').prop('disabled', false);
                deleteUnitId = null;
            }
        });
    }
    
    // Toggle Status
    function toggleStatus(unitId, isActive) {
        $.ajax({
            url: '{{ route("units.toggle-status", ":id") }}'.replace(':id', unitId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('Success', response.message, 'success');
                } else {
                    showToast('Error', response.message || 'Failed to update status', 'error');
                    // Revert toggle
                    $('.status-toggle[data-id="' + unitId + '"]').prop('checked', !isActive);
                }
            },
            error: function(xhr) {
                showToast('Error', 'Failed to update status', 'error');
                // Revert toggle
                $('.status-toggle[data-id="' + unitId + '"]').prop('checked', !isActive);
            }
        });
    }
    
    // Display Validation Errors
    function displayValidationErrors(errors) {
        clearValidationErrors();
        
        // Map field names to their corresponding input IDs
        const fieldMap = {
            'name': 'unitName',
            'symbol': 'unitSymbol',
            'type': 'unitType',
            'is_active': 'unitIsActive'
        };
        
        Object.keys(errors).forEach(function(field) {
            let inputId = fieldMap[field] || field;
            let errorElement = $('#' + inputId + 'Error');
            let inputElement = $('#' + inputId);
            
            if(errorElement.length) {
                errorElement.text(errors[field][0]);
                inputElement.addClass('is-invalid');
            } else if(inputElement.length) {
                // If error element doesn't exist, add class to input
                inputElement.addClass('is-invalid');
            }
        });
    }
    
    // Clear Validation Errors
    function clearValidationErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
    
    // Show Toast
    function showToast(title, message, type = 'info') {
        $('#toastTitle').text(title);
        $('#toastMessage').text(message);
        
        let toast = new bootstrap.Toast(document.getElementById('toast'));
        toast.show();
    }
});
</script>
@endpush