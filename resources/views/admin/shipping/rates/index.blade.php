@extends('layouts.admin')

@section('title', 'Shipping Rates')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shipping.rates.index') }}">Shipping</a></li>
                        <li class="breadcrumb-item active">Rates</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Shipping Rates</h1>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteRatesBtn">
                    <i class="fas fa-trash me-2"></i>Delete Selected
                </button>
                <button class="btn btn-primary" id="addRateBtn">
                    <i class="fas fa-plus"></i> Add Rate
                </button>
            </div>
        </div>

        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <select class="form-select" id="zoneFilter">
                            <option value="">All Zones</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="methodFilter">
                            <option value="">All Methods</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="rateTypeFilter">
                            <option value="">All Types</option>
                            <option value="flat_rate">Flat Rate</option>
                            <option value="weight_based">Weight Based</option>
                            <option value="price_based">Price Based</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" placeholder="Search..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="ratesTable">
                    <thead>
                        <tr>
                            <th width="50"><input type="checkbox" class="form-check-input" id="selectAllRates"></th>
                            <th>Zone</th>
                            <th>Method</th>
                            <th>Rate Type</th>
                            <th>Rate</th>
                            <th>Per Kg / %</th>
                            <th>Free Shipping</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ratesTableBody">
                        <tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Rate Modal -->
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rateModalLabel">Add Shipping Rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                <form id="rateForm">
                    <input type="hidden" id="rateId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rateZone" class="form-label">Zone <span class="text-danger">*</span></label>
                            <select class="form-select" id="rateZone" name="shipping_zone_id" required>
                                <option value="">Select Zone</option>
                            </select>
                            <div class="invalid-feedback" id="shipping_zone_idError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="rateMethod" class="form-label">Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="rateMethod" name="shipping_method_id" required>
                                <option value="">Select Method</option>
                            </select>
                            <div class="invalid-feedback" id="shipping_method_idError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="rateType" class="form-label">Rate Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="rateType" name="rate_type" required>
                                <option value="flat_rate">Flat Rate - Fixed amount regardless of weight/price</option>
                                <option value="weight_based">Weight Based - Base rate + cost per kg</option>
                                <option value="price_based">Price Based - Base rate + percentage of order total</option>
                                <option value="distance_based">Distance Based - Based on delivery distance</option>
                            </select>
                            <small class="text-muted">Select how shipping cost will be calculated</small>
                            <div class="invalid-feedback" id="rate_typeError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="rateBase" class="form-label">Base Rate (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateBase" name="rate" placeholder="e.g., 50.00" required>
                            <small class="text-muted">Base shipping cost (always charged)</small>
                            <div class="invalid-feedback" id="rateError"></div>
                        </div>
                        <div class="col-md-6" id="ratePerKgField" style="display: none;">
                            <label for="ratePerKg" class="form-label">Rate Per Kg (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="ratePerKg" name="rate_per_kg" placeholder="e.g., 15.00">
                            <small class="text-muted">Additional cost per kilogram (for weight-based rates)</small>
                            <div class="invalid-feedback" id="rate_per_kgError"></div>
                        </div>
                        <div class="col-md-6" id="ratePercentageField" style="display: none;">
                            <label for="ratePercentage" class="form-label">Rate Percentage (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="ratePercentage" name="rate_percentage" placeholder="e.g., 5.00">
                            <small class="text-muted">Percentage of order total (for price-based rates, 0-100%)</small>
                            <div class="invalid-feedback" id="rate_percentageError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="rateMinValue" class="form-label">Min Value</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateMinValue" name="min_value" placeholder="e.g., 0.5 (for weight) or 100 (for price)">
                            <small class="text-muted">Minimum weight (kg) for weight-based OR minimum price (₹) for price-based</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateMaxValue" class="form-label">Max Value</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateMaxValue" name="max_value" placeholder="e.g., 10.0 (for weight) or 1000 (for price)">
                            <small class="text-muted">Maximum weight (kg) for weight-based OR maximum price (₹) for price-based</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateFragile" class="form-label">Fragile Surcharge (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateFragile" name="fragile_surcharge" value="0" placeholder="e.g., 20.00">
                            <small class="text-muted">Extra charge for fragile items</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateOversized" class="form-label">Oversized Surcharge (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateOversized" name="oversized_surcharge" value="0" placeholder="e.g., 50.00">
                            <small class="text-muted">Extra charge for oversized items</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateHazardous" class="form-label">Hazardous Surcharge (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateHazardous" name="hazardous_surcharge" value="0" placeholder="e.g., 100.00">
                            <small class="text-muted">Extra charge for hazardous items</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateExpress" class="form-label">Express Surcharge (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateExpress" name="express_surcharge" value="0" placeholder="e.g., 30.00">
                            <small class="text-muted">Extra charge for express shipping class</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateFreeThreshold" class="form-label">Free Shipping Threshold (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="rateFreeThreshold" name="free_shipping_threshold" placeholder="e.g., 1000.00">
                            <small class="text-muted">Free shipping if order total exceeds this amount (leave empty for no free shipping)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rateStatus" class="form-label">Status</label>
                            <select class="form-select" id="rateStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRateBtn">
                    <span id="saveBtnText">Save Rate</span>
                    <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete this rate?</div>
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
    let deleteRateId = null;
    let currentPage = 1;
    
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    
    // Load zones and methods for dropdowns
    loadZones();
    loadMethods();
    loadRates();
    
    // Rate type change handler
    $('#rateType').change(function() {
        let type = $(this).val();
        $('#ratePerKgField, #ratePercentageField').hide();
        if (type === 'weight_based') $('#ratePerKgField').show();
        else if (type === 'price_based') $('#ratePercentageField').show();
    });
    
    // Filters
    $('#zoneFilter, #methodFilter, #rateTypeFilter, #statusFilter').change(function() {
        currentPage = 1;
        loadRates();
    });
    
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { currentPage = 1; loadRates(); }, 500);
    });
    
    function loadZones() {
        $.ajax({
            url: '{{ route("shipping.zones.data") }}',
            type: 'GET',
            success: function(response) {
                let select = $('#rateZone, #zoneFilter');
                select.empty().append('<option value="">Select Zone</option>');
                if (response.data) {
                    response.data.forEach(function(zone) {
                        select.append(`<option value="${zone.id}">${zone.name} (${zone.code})</option>`);
                    });
                }
            }
        });
    }
    
    function loadMethods() {
        $.ajax({
            url: '{{ route("shipping.methods.data") }}',
            type: 'GET',
            success: function(response) {
                let select = $('#rateMethod, #methodFilter');
                select.empty().append('<option value="">Select Method</option>');
                if (response.data) {
                    response.data.forEach(function(method) {
                        select.append(`<option value="${method.id}">${method.name} (${method.code})</option>`);
                    });
                }
            }
        });
    }
    
    $('#addRateBtn').on('click', function() {
        $('#rateModalLabel').text('Add Shipping Rate');
        $('#rateForm')[0].reset();
        $('#rateId').val('');
        $('#rateStatus').val('active');
        $('#rateType').val('flat_rate').trigger('change');
        clearModalAlerts();
        $('#rateModal').modal('show');
    });
    
    $(document).on('click', '.edit-rate', function() {
        let id = $(this).data('id');
        $.ajax({
            url: '{{ route("shipping.rates.edit", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let r = response.data;
                    $('#rateId').val(r.id);
                    $('#rateZone').val(r.shipping_zone_id);
                    $('#rateMethod').val(r.shipping_method_id);
                    $('#rateType').val(r.rate_type).trigger('change');
                    $('#rateBase').val(r.rate);
                    $('#ratePerKg').val(r.rate_per_kg || '');
                    $('#ratePercentage').val(r.rate_percentage || '');
                    $('#rateMinValue').val(r.min_value || '');
                    $('#rateMaxValue').val(r.max_value || '');
                    $('#rateFragile').val(r.fragile_surcharge || 0);
                    $('#rateOversized').val(r.oversized_surcharge || 0);
                    $('#rateHazardous').val(r.hazardous_surcharge || 0);
                    $('#rateExpress').val(r.express_surcharge || 0);
                    $('#rateFreeThreshold').val(r.free_shipping_threshold || '');
                    $('#rateStatus').val(r.status);
                    $('#rateModalLabel').text('Edit Shipping Rate');
                    clearModalAlerts();
                    $('#rateModal').modal('show');
                }
            }
        });
    });
    
    $('#saveRateBtn').on('click', function() {
        let formData = {
            shipping_zone_id: $('#rateZone').val(),
            shipping_method_id: $('#rateMethod').val(),
            rate_type: $('#rateType').val(),
            rate: $('#rateBase').val(),
            rate_per_kg: $('#ratePerKg').val() || null,
            rate_percentage: $('#ratePercentage').val() || null,
            min_value: $('#rateMinValue').val() || null,
            max_value: $('#rateMaxValue').val() || null,
            fragile_surcharge: $('#rateFragile').val() || 0,
            oversized_surcharge: $('#rateOversized').val() || 0,
            hazardous_surcharge: $('#rateHazardous').val() || 0,
            express_surcharge: $('#rateExpress').val() || 0,
            free_shipping_threshold: $('#rateFreeThreshold').val() || null,
            status: $('#rateStatus').val()
        };
        
        let id = $('#rateId').val();
        let url = id ? '{{ route("shipping.rates.update", ":id") }}'.replace(':id', id) : '{{ route("shipping.rates.store") }}';
        
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveRateBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#rateModal').modal('hide');
                    loadRates();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('').hide();
                    Object.keys(errors).forEach(function(field) {
                        let fieldId = 'rate' + field.charAt(0).toUpperCase() + field.slice(1).replace(/_([a-z])/g, (g) => g[1].toUpperCase());
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
                $('#saveRateBtn').prop('disabled', false);
            }
        });
    });
    
    $(document).on('click', '.delete-rate', function() {
        deleteRateId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').on('click', function() {
        if (deleteRateId) {
            $.ajax({
                url: '{{ route("shipping.rates.destroy", ":id") }}'.replace(':id', deleteRateId),
                type: 'DELETE',
                success: function(response) {
                    if(response.success) {
                        $('#deleteModal').modal('hide');
                        loadRates();
                        showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Error deleting rate');
                }
            });
        }
    });
    
    function loadRates() {
        let params = { draw: 1, start: (currentPage - 1) * 20, length: 20 };
        let search = $('#tableSearch').val();
        if (search) params.search = { value: search };
        let zone = $('#zoneFilter').val();
        if (zone) params.zone_id = zone;
        let method = $('#methodFilter').val();
        if (method) params.method_id = method;
        let rateType = $('#rateTypeFilter').val();
        if (rateType) params.rate_type = rateType;
        let status = $('#statusFilter').val();
        if (status) params.status = status;
        
        $.ajax({
            url: '{{ route("shipping.rates.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                let tbody = $('#ratesTableBody');
                tbody.empty();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(function(rate) {
                        let statusBadge = rate.status === 'active' 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let typeBadge = '<span class="badge bg-info">' + rate.rate_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</span>';
                        
                        let row = `
                            <tr data-id="${rate.id}">
                                <td><input type="checkbox" class="form-check-input rate-checkbox" value="${rate.id}"></td>
                                <td>${rate.zone_name}</td>
                                <td>${rate.method_name}</td>
                                <td>${typeBadge}</td>
                                <td>₹${rate.rate}</td>
                                <td>${rate.rate_per_kg !== '-' ? '₹' + rate.rate_per_kg + '/kg' : (rate.rate_percentage !== '-' ? rate.rate_percentage : '-')}</td>
                                <td>${rate.free_shipping_threshold !== '-' ? rate.free_shipping_threshold : '-'}</td>
                                <td>${statusBadge}</td>
                                <td>${new Date(rate.created_at).toLocaleDateString()}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-rate" data-id="${rate.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-rate" data-id="${rate.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="10" class="text-center py-4">No rates found</td></tr>');
                }
            },
            error: function() {
                $('#ratesTableBody').html('<tr><td colspan="10" class="text-center py-4 text-danger">Error loading rates</td></tr>');
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

