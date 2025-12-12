@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav class="mb-2" aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-sa-simple">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Coupons</li>
                            </ol>
                        </nav>
                        <h1 class="h3 m-0">Coupon Management</h1>
                        <p class="text-muted mb-0">Manage discount coupons for your store</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="openCreateModal()">
                            <i class='bx bx-plus'></i> Add Coupon
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-3 col-md-4">
                                <label for="filterStatus" class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <label for="filterDiscountType" class="form-label">Discount Type</label>
                                <select class="form-select" id="filterDiscountType">
                                    <option value="">All Types</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class='bx bx-x'></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coupons Table -->
                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="couponsTable">
                                <thead>
                                    <tr>
                                        <th>Coupon Code</th>
                                        <th>Discount Type</th>
                                        <th>Discount Value</th>
                                        <th>Min Order Amount</th>
                                        <th>Usage Limit</th>
                                        <th>Used Count</th>
                                        <th>Valid From</th>
                                        <th>Valid To</th>
                                        <th>Status</th>
                                        <th class="text-end" style="width: 120px;">Actions</th>
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

<!-- Create/Edit Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponModalLabel">Add Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="couponForm">
                <div class="modal-body">
                    <input type="hidden" id="couponId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="code" name="code" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCouponCode()" title="Generate Code">
                                    <i class='bx bx-refresh'></i>
                                </button>
                            </div>
                            <div class="form-text" id="codeValidation"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="discount_type" name="discount_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="discountPrefix">%</span>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0.01" required>
                            </div>
                            <div class="form-text" id="discountValueHelp"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="min_order_amount" class="form-label">Minimum Order Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" step="0.01" min="0">
                            </div>
                            <div class="form-text">Leave empty for no minimum</div>
                        </div>
                        <div class="col-md-6">
                            <label for="max_uses" class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" id="max_uses" name="max_uses" min="1">
                            <div class="form-text">Leave empty for unlimited uses</div>
                        </div>
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Valid From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Valid To <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }
    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #00a629;
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let couponsTable;
    let isEditMode = false;

    // Initialize DataTable
    function initTable() {
        // Destroy existing table if it exists
        if ($.fn.DataTable.isDataTable('#couponsTable')) {
            $('#couponsTable').DataTable().destroy();
        }
        
        couponsTable = $('#couponsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("coupons.data") }}',
                type: 'GET',
                data: function(d) {
                    d.status = $('#filterStatus').val();
                    d.discount_type = $('#filterDiscountType').val();
                },
                dataSrc: function(json) {
                    console.log('DataTables response:', json);
                    if (json.error) {
                        console.error('Server error:', json.error);
                        showToast('Error', json.error, 'danger');
                        return [];
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    showToast('Error', 'Failed to load coupons data. Please refresh the page.', 'danger');
                }
            },
            columns: [
                { data: 'code', name: 'code' },
                { data: 'discount_type', name: 'discount_type' },
                { data: 'discount_value', name: 'discount_value' },
                { data: 'min_order_amount', name: 'min_order_amount' },
                { data: 'max_uses', name: 'max_uses' },
                { data: 'uses', name: 'uses' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                { 
                    data: 'status', 
                    name: 'status',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <label class="switch">
                                <input type="checkbox" ${data ? 'checked' : ''} onchange="toggleStatus(${row.id}, this.checked)">
                                <span class="slider"></span>
                            </label>
                        `;
                    }
                },
                { 
                    data: 'id', 
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(${data})" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCoupon(${data})" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: 'No coupons found',
                zeroRecords: 'No matching coupons found'
            }
        });
    }

    // Initialize table on page load
    initTable();

    // Filter change handlers
    $('#filterStatus, #filterDiscountType').on('change', function() {
        couponsTable.ajax.reload();
    });

    // Discount type change handler
    $('#discount_type').on('change', function() {
        const type = $(this).val();
        const prefix = $('#discountPrefix');
        const help = $('#discountValueHelp');
        
        if (type === 'percentage') {
            prefix.text('%');
            help.text('Enter percentage (0-100)');
            $('#discount_value').attr('max', '100');
        } else {
            prefix.text('₹');
            help.text('Enter fixed amount in rupees');
            $('#discount_value').removeAttr('max');
        }
    });

    // Code validation on input
    let codeValidationTimeout;
    $('#code').on('input', function() {
        clearTimeout(codeValidationTimeout);
        const code = $(this).val();
        const couponId = $('#couponId').val();
        
        if (code.length >= 3) {
            codeValidationTimeout = setTimeout(function() {
                validateCouponCode(code, couponId);
            }, 500);
        } else {
            $('#codeValidation').html('').removeClass('text-danger text-success');
        }
    });

    // Form submission
    $('#couponForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            code: $('#code').val(),
            discount_type: $('#discount_type').val(),
            discount_value: $('#discount_value').val(),
            min_order_amount: $('#min_order_amount').val() || null,
            max_uses: $('#max_uses').val() || null,
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val() === '1' ? true : false,
        };

        const couponId = $('#couponId').val();
        const url = couponId ? `{{ url('coupons') }}/${couponId}` : '{{ route("coupons.store") }}';
        const method = couponId ? 'POST' : 'POST';

        const submitBtn = $('#submitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    $('#couponModal').modal('hide');
                    resetForm();
                    couponsTable.ajax.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Please fix the following errors:\n';
                    Object.keys(errors).forEach(key => {
                        errorMessage += `- ${errors[key][0]}\n`;
                    });
                    showToast('Validation Error', errorMessage, 'danger');
                } else {
                    showToast('Error', xhr.responseJSON?.message || 'An error occurred', 'danger');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Modal close handler
    $('#couponModal').on('hidden.bs.modal', function() {
        resetForm();
    });
});

// Open create modal
function openCreateModal() {
    isEditMode = false;
    $('#couponModalLabel').text('Add Coupon');
    $('#submitBtn').text('Save Coupon');
    resetForm();
    $('#discount_type').trigger('change');
}

// Open edit modal
function openEditModal(id) {
    isEditMode = true;
    $('#couponModalLabel').text('Edit Coupon');
    $('#submitBtn').text('Update Coupon');
    
    $.ajax({
        url: `{{ url('coupons') }}/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const coupon = response.coupon;
                $('#couponId').val(coupon.id);
                $('#code').val(coupon.code);
                $('#discount_type').val(coupon.discount_type);
                $('#discount_value').val(coupon.discount_value);
                $('#min_order_amount').val(coupon.min_order_amount || '');
                $('#max_uses').val(coupon.max_uses || '');
                $('#start_date').val(coupon.start_date);
                $('#end_date').val(coupon.end_date);
                $('#status').val(coupon.status ? '1' : '0');
                $('#discount_type').trigger('change');
                $('#couponModal').modal('show');
            }
        },
        error: function() {
            showToast('Error', 'Failed to load coupon data', 'danger');
        }
    });
}

// Delete coupon
function deleteCoupon(id) {
    if (confirm('Are you sure you want to delete this coupon?')) {
        $.ajax({
            url: `{{ url('coupons') }}/${id}`,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    $('#couponsTable').DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                showToast('Error', xhr.responseJSON?.message || 'Failed to delete coupon', 'danger');
            }
        });
    }
}

// Toggle status
function toggleStatus(id, status) {
    $.ajax({
        url: `{{ url('coupons') }}/${id}/toggle-status`,
        type: 'POST',
        data: { status: status },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message, 'success');
                $('#couponsTable').DataTable().ajax.reload();
            }
        },
        error: function(xhr) {
            showToast('Error', 'Failed to update status', 'danger');
            $('#couponsTable').DataTable().ajax.reload();
        }
    });
}

// Generate coupon code
function generateCouponCode() {
    $.ajax({
        url: '{{ route("coupons.generateCode") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#code').val(response.code);
                $('#codeValidation').html('<span class="text-success">Code generated</span>');
            }
        },
        error: function() {
            showToast('Error', 'Failed to generate code', 'danger');
        }
    });
}

// Validate coupon code
function validateCouponCode(code, couponId) {
    $.ajax({
        url: '{{ route("coupons.validateCode") }}',
        type: 'POST',
        data: {
            code: code,
            id: couponId || null
        },
        success: function(response) {
            const validationDiv = $('#codeValidation');
            if (response.valid) {
                validationDiv.html('<span class="text-success"><i class="bx bx-check-circle"></i> ' + response.message + '</span>');
            } else {
                validationDiv.html('<span class="text-danger"><i class="bx bx-x-circle"></i> ' + response.message + '</span>');
            }
        }
    });
}

// Clear filters
function clearFilters() {
    $('#filterStatus').val('');
    $('#filterDiscountType').val('');
    $('#couponsTable').DataTable().ajax.reload();
}

// Reset form
function resetForm() {
    $('#couponForm')[0].reset();
    $('#couponId').val('');
    $('#codeValidation').html('').removeClass('text-danger text-success');
    $('#discount_type').val('percentage');
    $('#status').val('1');
}

// Show toast notification
let toastInstance = null;

function showToast(title, message, type = 'info') {
    const toast = $('#toast');
    const toastTitle = $('#toastTitle');
    const toastMessage = $('#toastMessage');
    
    // Check if toast elements exist
    if (toast.length === 0 || toastTitle.length === 0 || toastMessage.length === 0) {
        console.error('Toast elements not found');
        // Fallback to alert
        alert(title + ': ' + message);
        return;
    }
    
    // Hide and dispose existing toast instance if any
    if (toastInstance) {
        try {
            toastInstance.hide();
            toastInstance.dispose();
        } catch (e) {
            // Ignore errors when disposing
        }
        toastInstance = null;
    }
    
    toastTitle.text(title);
    toastMessage.text(message);
    
    toast.removeClass('bg-primary bg-success bg-danger bg-warning text-white');
    if (type === 'success') {
        toast.addClass('bg-success text-white');
    } else if (type === 'danger') {
        toast.addClass('bg-danger text-white');
    } else {
        toast.addClass('bg-primary text-white');
    }
    
    // Check if Bootstrap Toast is available and element exists
    if (typeof bootstrap !== 'undefined' && toast[0]) {
        try {
            toastInstance = new bootstrap.Toast(toast[0], {
                autohide: true,
                delay: 3000
            });
            toastInstance.show();
        } catch (e) {
            console.error('Error showing toast:', e);
            // Fallback to alert
            alert(title + ': ' + message);
        }
    } else {
        // Fallback to alert
        alert(title + ': ' + message);
    }
}
</script>
@endpush

