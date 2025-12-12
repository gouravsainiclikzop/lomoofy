@extends('layouts.admin')

@section('title', 'Shipping Zones')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shipping.zones.index') }}">Shipping</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Zones</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Shipping Zones</h1>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteZonesBtn">
                    <i class="fas fa-trash me-2"></i>Delete Selected
                </button>
                <button class="btn btn-primary" id="addZoneBtn">
                    <i class="fas fa-plus"></i> Add Zone
                </button>
            </div>
        </div>

        <!-- Zones Table -->
        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="pincode">Pincode</option>
                            <option value="state">State</option>
                            <option value="city">City</option>
                            <option value="country">Country</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" placeholder="Search zones..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="zonesTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAllZones" title="Select All">
                            </th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Coverage</th>
                            <th>Country</th>
                            <th>Rates</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="zonesTableBody">
                        <tr>
                            <td colspan="11" class="text-center py-5">
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

<!-- Add/Edit Zone Modal -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zoneModalLabel">Add Shipping Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="zoneForm">
                    <input type="hidden" id="zoneId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="zoneName" class="form-label">Zone Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="zoneName" name="name" placeholder="e.g., Delhi Metro Zone" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="zoneCode" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="zoneCode" name="code" placeholder="e.g., DLH-METRO" required>
                            <small class="form-text text-muted">Unique zone code (uppercase, no spaces)</small>
                            <div class="invalid-feedback" id="codeError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="zoneType" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="zoneType" name="type" required>
                                <option value="pincode">Pincode</option>
                                <option value="state">State</option>
                                <option value="city">City</option>
                                <option value="country">Country</option>
                            </select>
                            <div class="invalid-feedback" id="typeError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="zoneCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="zoneCountry" name="country" value="India" placeholder="e.g., India">
                            <div class="invalid-feedback" id="countryError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="zoneDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="zoneDescription" name="description" rows="2" placeholder="e.g., Delhi and surrounding metro areas"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        
                        <!-- Dynamic fields based on type -->
                        <div class="col-12" id="pincodesField" style="display: none;">
                            <label for="zonePincodes" class="form-label">Pincodes</label>
                            <textarea class="form-control" id="zonePincodes" name="pincodes_text" rows="3" placeholder="110001, 110002, 110003, 110004, 110005&#10;Or enter one per line:&#10;110001&#10;110002&#10;110003"></textarea>
                            <small class="text-muted">Enter pincodes separated by comma or one per line (e.g., 110001, 110002, 110003)</small>
                        </div>
                        
                        <div class="col-12" id="statesField" style="display: none;">
                            <label for="zoneStates" class="form-label">States</label>
                            <textarea class="form-control" id="zoneStates" name="states_text" rows="3" placeholder="Karnataka, Maharashtra, Tamil Nadu&#10;Or enter one per line:&#10;Karnataka&#10;Maharashtra"></textarea>
                            <small class="text-muted">Enter state names separated by comma or one per line (e.g., Karnataka, Maharashtra)</small>
                        </div>
                        
                        <div class="col-12" id="citiesField" style="display: none;">
                            <label for="zoneCities" class="form-label">Cities</label>
                            <textarea class="form-control" id="zoneCities" name="cities_text" rows="3" placeholder="Bangalore, Bengaluru, Mysore&#10;Or enter one per line:&#10;Bangalore&#10;Mysore"></textarea>
                            <small class="text-muted">Enter city names separated by comma or one per line (e.g., Bangalore, Mysore)</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="zoneStatus" class="form-label">Status</label>
                            <select class="form-select" id="zoneStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="statusError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="zoneSortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="zoneSortOrder" name="sort_order" value="0" min="0" placeholder="0">
                            <small class="text-muted">Lower numbers appear first (0 = highest priority)</small>
                            <div class="invalid-feedback" id="sortOrderError"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveZoneBtn">
                    <span id="saveBtnText">Save Zone</span>
                    <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this zone? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
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
    let deleteZoneId = null;
    let currentPage = 1;
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    loadZones();
    
    // Type filter change
    $('#zoneType').change(function() {
        let type = $(this).val();
        $('#pincodesField, #statesField, #citiesField').hide();
        if (type === 'pincode') $('#pincodesField').show();
        else if (type === 'state') $('#statesField').show();
        else if (type === 'city') $('#citiesField').show();
    });
    
    // Filters
    $('#typeFilter, #statusFilter').change(function() {
        currentPage = 1;
        loadZones();
    });
    
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadZones();
        }, 500);
    });
    
    // Add Zone
    $('#addZoneBtn').on('click', function() {
        $('#zoneModalLabel').text('Add Shipping Zone');
        $('#zoneForm')[0].reset();
        $('#zoneId').val('');
        $('#zoneStatus').val('active');
        $('#zoneSortOrder').val('0');
        $('#zoneCountry').val('India');
        $('#zoneType').val('pincode').trigger('change');
        clearModalAlerts();
        $('#zoneModal').modal('show');
    });
    
    // Edit Zone
    $(document).on('click', '.edit-zone', function() {
        let id = $(this).data('id');
        $.ajax({
            url: '{{ route("shipping.zones.edit", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let zone = response.data;
                    $('#zoneId').val(zone.id);
                    $('#zoneName').val(zone.name);
                    $('#zoneCode').val(zone.code);
                    $('#zoneType').val(zone.type).trigger('change');
                    $('#zoneDescription').val(zone.description || '');
                    $('#zoneCountry').val(zone.country || 'India');
                    $('#zoneStatus').val(zone.status);
                    $('#zoneSortOrder').val(zone.sort_order || 0);
                    
                    // Populate type-specific fields
                    if (zone.type === 'pincode' && zone.pincodes) {
                        $('#zonePincodes').val(zone.pincodes.join(', '));
                    } else if (zone.type === 'state' && zone.states) {
                        $('#zoneStates').val(zone.states.join(', '));
                    } else if (zone.type === 'city' && zone.cities) {
                        $('#zoneCities').val(zone.cities.join(', '));
                    }
                    
                    $('#zoneModalLabel').text('Edit Shipping Zone');
                    clearModalAlerts();
                    $('#zoneModal').modal('show');
                }
            }
        });
    });
    
    // Save Zone
    $('#saveZoneBtn').on('click', function() {
        let formData = {
            name: $('#zoneName').val(),
            code: $('#zoneCode').val(),
            type: $('#zoneType').val(),
            description: $('#zoneDescription').val(),
            country: $('#zoneCountry').val(),
            status: $('#zoneStatus').val(),
            sort_order: $('#zoneSortOrder').val() || 0
        };
        
        // Process type-specific fields
        let type = formData.type;
        if (type === 'pincode') {
            let pincodesText = $('#zonePincodes').val();
            if (pincodesText) {
                formData.pincodes = pincodesText.split(/[,\n]/).map(p => p.trim()).filter(p => p);
            }
        } else if (type === 'state') {
            let statesText = $('#zoneStates').val();
            if (statesText) {
                formData.states = statesText.split(/[,\n]/).map(s => s.trim()).filter(s => s);
            }
        } else if (type === 'city') {
            let citiesText = $('#zoneCities').val();
            if (citiesText) {
                formData.cities = citiesText.split(/[,\n]/).map(c => c.trim()).filter(c => c);
            }
        }
        
        let id = $('#zoneId').val();
        let url = id ? '{{ route("shipping.zones.update", ":id") }}'.replace(':id', id) : '{{ route("shipping.zones.store") }}';
        let method = id ? 'POST' : 'POST';
        
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveZoneBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#zoneModal').modal('hide');
                    loadZones();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('').hide();
                    Object.keys(errors).forEach(function(field) {
                        let fieldId = 'zone' + field.charAt(0).toUpperCase() + field.slice(1);
                        $('#' + fieldId).addClass('is-invalid');
                        $('#' + fieldId + 'Error').text(errors[field][0]).show();
                    });
                } else {
                    showModalAlert(xhr.responseJSON?.message || 'An error occurred.', 'danger');
                }
            },
            complete: function() {
                $('#saveBtnText').removeClass('d-none');
                $('#saveBtnSpinner').addClass('d-none');
                $('#saveZoneBtn').prop('disabled', false);
            }
        });
    });
    
    // Delete Zone
    $(document).on('click', '.delete-zone', function() {
        deleteZoneId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').on('click', function() {
        if (deleteZoneId) {
            $.ajax({
                url: '{{ route("shipping.zones.destroy", ":id") }}'.replace(':id', deleteZoneId),
                type: 'DELETE',
                success: function(response) {
                    if(response.success) {
                        $('#deleteModal').modal('hide');
                        loadZones();
                        showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Error deleting zone');
                }
            });
        }
    });
    
    // Load Zones
    function loadZones() {
        let params = {
            draw: 1,
            start: (currentPage - 1) * 20,
            length: 20
        };
        
        let search = $('#tableSearch').val();
        if (search) params.search = { value: search };
        
        let type = $('#typeFilter').val();
        if (type) params.type = type;
        
        let status = $('#statusFilter').val();
        if (status) params.status = status;
        
        $.ajax({
            url: '{{ route("shipping.zones.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                let tbody = $('#zonesTableBody');
                tbody.empty();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(function(zone) {
                        let statusBadge = zone.status === 'active' 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let typeBadge = '<span class="badge bg-info">' + zone.type.charAt(0).toUpperCase() + zone.type.slice(1) + '</span>';
                        
                        let row = `
                            <tr data-id="${zone.id}">
                                <td><input type="checkbox" class="form-check-input zone-checkbox" value="${zone.id}"></td>
                                <td><strong>${zone.name}</strong></td>
                                <td><code>${zone.code}</code></td>
                                <td>${typeBadge}</td>
                                <td>${zone.coverage || '-'}</td>
                                <td>${zone.country || '-'}</td>
                                <td><span class="badge bg-info">${zone.rates_count || 0}</span></td>
                                <td>${statusBadge}</td>
                                <td>${zone.sort_order || 0}</td>
                                <td>${new Date(zone.created_at).toLocaleDateString()}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-zone" data-id="${zone.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-zone" data-id="${zone.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="11" class="text-center py-4">No zones found</td></tr>');
                }
            },
            error: function() {
                $('#zonesTableBody').html('<tr><td colspan="11" class="text-center py-4 text-danger">Error loading zones</td></tr>');
            }
        });
    }
    
    function clearModalAlerts() {
        $('#modalAlertContainer').empty();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }
    
    function showModalAlert(message, type) {
        $('#modalAlertContainer').html(`<div class="alert alert-${type}">${message}</div>`);
    }
    
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

