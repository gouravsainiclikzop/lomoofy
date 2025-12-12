@extends('layouts.admin')

@section('title', 'Shipping Methods')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shipping.methods.index') }}">Shipping</a></li>
                        <li class="breadcrumb-item active">Methods</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Shipping Methods</h1>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteMethodsBtn">
                    <i class="fas fa-trash me-2"></i>Delete Selected
                </button>
                <button class="btn btn-primary" id="addMethodBtn">
                    <i class="fas fa-plus"></i> Add Method
                </button>
            </div>
        </div>

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
                        <input type="text" placeholder="Search methods..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="methodsTable">
                    <thead>
                        <tr>
                            <th width="50"><input type="checkbox" class="form-check-input" id="selectAllMethods"></th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Delivery Time</th>
                            <th>Rates</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="methodsTableBody">
                        <tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Method Modal -->
<div class="modal fade" id="methodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="methodModalLabel">Add Shipping Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                <form id="methodForm">
                    <input type="hidden" id="methodId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="methodName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="methodName" name="name" placeholder="e.g., Standard Shipping" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="methodCode" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="methodCode" name="code" placeholder="e.g., STANDARD" required>
                            <small class="text-muted">Unique code (uppercase, no spaces)</small>
                            <div class="invalid-feedback" id="codeError"></div>
                        </div>
                        <div class="col-12">
                            <label for="methodDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="methodDescription" name="description" rows="2" placeholder="e.g., Regular shipping with standard delivery time"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="methodDaysMin" class="form-label">Min Delivery Days</label>
                            <input type="number" class="form-control" id="methodDaysMin" name="estimated_days_min" min="0" placeholder="e.g., 3">
                            <small class="text-muted">Minimum delivery time in days</small>
                            <div class="invalid-feedback" id="estimated_days_minError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="methodDaysMax" class="form-label">Max Delivery Days</label>
                            <input type="number" class="form-control" id="methodDaysMax" name="estimated_days_max" min="0" placeholder="e.g., 5">
                            <small class="text-muted">Maximum delivery time in days (should be >= Min Days)</small>
                            <div class="invalid-feedback" id="estimated_days_maxError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="methodStatus" class="form-label">Status</label>
                            <select class="form-select" id="methodStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="methodSortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="methodSortOrder" name="sort_order" value="0" min="0" placeholder="0">
                            <small class="text-muted">Lower numbers appear first (0 = highest priority)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveMethodBtn">
                    <span id="saveBtnText">Save Method</span>
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
            <div class="modal-body">Are you sure you want to delete this method?</div>
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
    let deleteMethodId = null;
    let currentPage = 1;
    
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    
    loadMethods();
    
    $('#statusFilter').change(function() {
        currentPage = 1;
        loadMethods();
    });
    
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { currentPage = 1; loadMethods(); }, 500);
    });
    
    $('#addMethodBtn').on('click', function() {
        $('#methodModalLabel').text('Add Shipping Method');
        $('#methodForm')[0].reset();
        $('#methodId').val('');
        $('#methodStatus').val('active');
        $('#methodSortOrder').val('0');
        clearModalAlerts();
        $('#methodModal').modal('show');
    });
    
    $(document).on('click', '.edit-method', function() {
        let id = $(this).data('id');
        $.ajax({
            url: '{{ route("shipping.methods.edit", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let m = response.data;
                    $('#methodId').val(m.id);
                    $('#methodName').val(m.name);
                    $('#methodCode').val(m.code);
                    $('#methodDescription').val(m.description || '');
                    $('#methodDaysMin').val(m.estimated_days_min || '');
                    $('#methodDaysMax').val(m.estimated_days_max || '');
                    $('#methodStatus').val(m.status);
                    $('#methodSortOrder').val(m.sort_order || 0);
                    $('#methodModalLabel').text('Edit Shipping Method');
                    clearModalAlerts();
                    $('#methodModal').modal('show');
                }
            }
        });
    });
    
    $('#saveMethodBtn').on('click', function() {
        let formData = {
            name: $('#methodName').val(),
            code: $('#methodCode').val(),
            description: $('#methodDescription').val(),
            estimated_days_min: $('#methodDaysMin').val() || null,
            estimated_days_max: $('#methodDaysMax').val() || null,
            status: $('#methodStatus').val(),
            sort_order: $('#methodSortOrder').val() || 0
        };
        
        let id = $('#methodId').val();
        let url = id ? '{{ route("shipping.methods.update", ":id") }}'.replace(':id', id) : '{{ route("shipping.methods.store") }}';
        
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveMethodBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#methodModal').modal('hide');
                    loadMethods();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('').hide();
                    Object.keys(errors).forEach(function(field) {
                        let fieldId = 'method' + field.charAt(0).toUpperCase() + field.slice(1).replace(/_([a-z])/g, (g) => g[1].toUpperCase());
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
                $('#saveMethodBtn').prop('disabled', false);
            }
        });
    });
    
    $(document).on('click', '.delete-method', function() {
        deleteMethodId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').on('click', function() {
        if (deleteMethodId) {
            $.ajax({
                url: '{{ route("shipping.methods.destroy", ":id") }}'.replace(':id', deleteMethodId),
                type: 'DELETE',
                success: function(response) {
                    if(response.success) {
                        $('#deleteModal').modal('hide');
                        loadMethods();
                        showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Error deleting method');
                }
            });
        }
    });
    
    function loadMethods() {
        let params = { draw: 1, start: (currentPage - 1) * 20, length: 20 };
        let search = $('#tableSearch').val();
        if (search) params.search = { value: search };
        let status = $('#statusFilter').val();
        if (status) params.status = status;
        
        $.ajax({
            url: '{{ route("shipping.methods.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                let tbody = $('#methodsTableBody');
                tbody.empty();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(function(method) {
                        let statusBadge = method.status === 'active' 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let row = `
                            <tr data-id="${method.id}">
                                <td><input type="checkbox" class="form-check-input method-checkbox" value="${method.id}"></td>
                                <td><strong>${method.name}</strong></td>
                                <td><code>${method.code}</code></td>
                                <td>${method.description || '-'}</td>
                                <td>${method.estimated_delivery || '-'}</td>
                                <td><span class="badge bg-info">${method.rates_count || 0}</span></td>
                                <td>${statusBadge}</td>
                                <td>${method.sort_order || 0}</td>
                                <td>${new Date(method.created_at).toLocaleDateString()}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-method" data-id="${method.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-method" data-id="${method.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="10" class="text-center py-4">No methods found</td></tr>');
                }
            },
            error: function() {
                $('#methodsTableBody').html('<tr><td colspan="10" class="text-center py-4 text-danger">Error loading methods</td></tr>');
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

