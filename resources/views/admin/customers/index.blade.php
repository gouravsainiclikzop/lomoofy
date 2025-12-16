@extends('layouts.admin')

@section('title', 'Customers')

@push('styles')
<style>
.field-group {
    margin-bottom: 1.5rem;
}
.field-group-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    /* border-bottom: 1px solid #e9ecef; */
    color: #495057;
}
.conditional-field {
    display: none;
}
.field-help-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customers</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Customers</h1>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Add Customer
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filterSearch" class="form-label">Search</label>
                        <input type="text" class="form-control" id="filterSearch" placeholder="Name, Email, Phone...">
                    </div>
                    <div class="col-md-2">
                        <label for="filterDateRange" class="form-label">Date Range</label>
                        <select class="form-select" id="filterDateRange">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="last_90_days">Last 90 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterArea" class="form-label">Area</label>
                        <select class="form-select" id="filterArea">
                            <option value="">All Areas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterOrderCount" class="form-label">Order Count</label>
                        <select class="form-select" id="filterOrderCount">
                            <option value="">All</option>
                            <option value="highest">Highest First</option>
                            <option value="lowest">Lowest First</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterOrderAmount" class="form-label">Order Amount</label>
                        <select class="form-select" id="filterOrderAmount">
                            <option value="">All</option>
                            <option value="highest">Highest First</option>
                            <option value="lowest">Lowest First</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-secondary w-100" id="clearFilters">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                <table class="table table-hover" id="customersTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>No of Orders</th>
                            <th>Amount of Orders</th>
                            <th>Pending</th>
                            <th>In Cart Items</th>
                            <th>Status</th>
                            <th>Created</th>
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

<!-- Create/Edit Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="customerId" name="id">
                    <div id="customerFormFields">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading form fields...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading form fields...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveCustomerBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
        <div class="toast-header">
            <i class='bx bx-check-circle text-success me-2'></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>
<!-- Addresses Popup Modal -->
<div class="modal fade" id="addressesModal" tabindex="-1" aria-labelledby="addressesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressesModalLabel">Customer Addresses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addressesList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Popup Modal -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordersModalLabel">Customer Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="ordersList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Orders Popup Modal -->
<div class="modal fade" id="pendingOrdersModal" tabindex="-1" aria-labelledby="pendingOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pendingOrdersModalLabel">Pending Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pendingOrdersList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Items Popup Modal -->
<div class="modal fade" id="cartItemsModal" tabindex="-1" aria-labelledby="cartItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartItemsModalLabel">Cart Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cartItemsList"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Show addresses popup
function showAddressesPopup(customerId) {
    $.ajax({
        url: `/customers/${customerId}/addresses`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>Type</th><th>Address</th><th>City</th><th>State</th><th>Pincode</th><th>Default</th></tr></thead><tbody>';
                
                if (response.data.length === 0) {
                    html += '<tr><td colspan="6" class="text-center text-muted">No addresses found</td></tr>';
                } else {
                    response.data.forEach(function(addr) {
                        html += `<tr>
                            <td>${addr.address_type || '-'}</td>
                            <td>${(() => { const addr1 = addr.address_line1 || ''; const addr2 = addr.address_line2 || ''; const combined = addr1 + (addr2 ? ', ' + addr2 : ''); return combined || '-'; })()}</td>
                            <td>${addr.city || '-'}</td>
                            <td>${addr.state || '-'}</td>
                            <td>${addr.pincode || '-'}</td>
                            <td>${addr.is_default ? '<span class="badge bg-success">Yes</span>' : '-'}</td>
                        </tr>`;
                    });
                }
                html += '</tbody></table></div>';
                $('#addressesList').html(html);
                $('#addressesModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading addresses');
        }
    });
}

// Show orders popup
function showOrdersPopup(customerId) {
    $.ajax({
        url: `/customers/${customerId}/orders`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>Order Number</th><th>Date</th><th>Status</th><th>Total Amount</th><th>Payment Status</th></tr></thead><tbody>';
                
                if (response.data.length === 0) {
                    html += '<tr><td colspan="5" class="text-center text-muted">No orders found</td></tr>';
                } else {
                    response.data.forEach(function(order) {
                        html += `<tr>
                            <td>${order.order_number || '-'}</td>
                            <td>${order.created_at || '-'}</td>
                            <td><span class="badge bg-info">${order.status || '-'}</span></td>
                            <td>₹${order.total_amount || '0.00'}</td>
                            <td><span class="badge bg-${order.payment_status === 'paid' ? 'success' : 'warning'}">${order.payment_status || '-'}</span></td>
                        </tr>`;
                    });
                }
                html += '</tbody></table></div>';
                $('#ordersList').html(html);
                $('#ordersModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading orders');
        }
    });
}

// Show pending orders popup
function showPendingOrdersPopup(customerId) {
    $.ajax({
        url: `/customers/${customerId}/orders?status=pending`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>Order Number</th><th>Date</th><th>Total Amount</th><th>Payment Status</th></tr></thead><tbody>';
                
                if (response.data.length === 0) {
                    html += '<tr><td colspan="4" class="text-center text-muted">No pending orders found</td></tr>';
                } else {
                    response.data.forEach(function(order) {
                        html += `<tr>
                            <td>${order.order_number || '-'}</td>
                            <td>${order.created_at || '-'}</td>
                            <td>₹${order.total_amount || '0.00'}</td>
                            <td><span class="badge bg-${order.payment_status === 'paid' ? 'success' : 'warning'}">${order.payment_status || '-'}</span></td>
                        </tr>`;
                    });
                }
                html += '</tbody></table></div>';
                $('#pendingOrdersList').html(html);
                $('#pendingOrdersModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading pending orders');
        }
    });
}

// Show cart items popup
function showCartItemsPopup(customerId) {
    $.ajax({
        url: `/customers/${customerId}/cart-items`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>Product</th><th>Variant</th><th>SKU</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
                
                if (response.data.length === 0) {
                    html += '<tr><td colspan="6" class="text-center text-muted">No cart items found</td></tr>';
                } else {
                    response.data.forEach(function(item) {
                        html += `<tr>
                            <td>${item.product_name || '-'}</td>
                            <td>${item.variant_name || '-'}</td>
                            <td>${item.sku || '-'}</td>
                            <td>${item.quantity || 0}</td>
                            <td>₹${item.unit_price || '0.00'}</td>
                            <td>₹${item.total_price || '0.00'}</td>
                        </tr>`;
                    });
                }
                html += '</tbody></table></div>';
                $('#cartItemsList').html(html);
                $('#cartItemsModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading cart items');
        }
    });
}
</script>

@endsection

@push('styles')
<style>
    /* Toast z-index fix */
    .toast-container {
        z-index: 9999 !important;
    }
    .toast {
        z-index: 10000 !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Override Bootstrap's enforceFocus to prevent interference with Select2 dropdowns in modals
// This fixes the issue where clicking on Select2 options closes the modal
// Works for both Bootstrap 4 and Bootstrap 5
if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    // Bootstrap 5
    const Modal = bootstrap.Modal;
    const originalEnforceFocus = Modal.prototype._enforceFocus;
    Modal.prototype._enforceFocus = function() {};
} else if (typeof $.fn.modal !== 'undefined' && $.fn.modal.Constructor) {
    // Bootstrap 4
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
}

let customersTable;
let formFields = [];
let fieldGroups = {};
let isEditMode = false;
let editingCustomerData = null;

$(document).ready(function() {
    initializeDataTable();
    loadAreas();
    
    // Filter change handlers
    $('#filterDateRange, #filterArea, #filterOrderCount, #filterOrderAmount').on('change', function() {
        customersTable.draw();
    });
    
    // Search input handler with debounce
    $('#filterSearch').on('keyup', debounce(function() {
        customersTable.search(this.value).draw();
    }, 300));
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterSearch').val('');
        $('#filterDateRange').val('');
        $('#filterArea').val('');
        $('#filterOrderCount').val('');
        $('#filterOrderAmount').val('');
        customersTable.search('').draw();
    });
    
    // Modal events
    $('#customerModal').on('show.bs.modal', function() {
        loadFormFields();
    });
    
    // Note: Select2 initialization removed - location fields are now text inputs
    
    $('#customerModal').on('hidden.bs.modal', function() {
        $('#customerForm')[0].reset();
        $('#customerId').val('');
        isEditMode = false;
        editingCustomerData = null;
        $('#customerModalLabel').text('Add New Customer');
    });
    
    // Add Customer Button
    $('#addCustomerBtn').on('click', function() {
        isEditMode = false;
        editingCustomerData = null;
        $('#customerModalLabel').text('Add New Customer');
    });
    
    // Form Submission
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        saveCustomer();
    });
});

function initializeDataTable() {
    customersTable = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("customers.data") }}',
            data: function(d) {
                d.date_range = $('#filterDateRange').val();
                d.area = $('#filterArea').val();
                d.order_count_sort = $('#filterOrderCount').val();
                d.order_amount_sort = $('#filterOrderAmount').val();
            }
        },
        columns: [
            {
                data: 'profile_image',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `<img src="${data}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Customer" onerror="this.src='/assets/images/placeholder.jpg'">`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div class="small">
                            <div><strong>${row.full_name || '-'}</strong></div>
                            <div class="text-muted"><i class="fas fa-mobile-alt"></i> ${row.phone || '-'}</div>
                            <div class="text-muted"><i class="fas fa-envelope"></i> ${row.email || '-'}</div>
                            ${row.alternate_phone && row.alternate_phone !== '-' ? `<div class="text-muted"><i class="fas fa-phone"></i> ${row.alternate_phone}</div>` : ''}
                        </div>
                    `;
                }
            },
            {
                data: 'addresses',
                render: function(data, type, row) {
                    if (!data || data.length === 0) {
                        return '<span class="text-muted">-</span>';
                    }
                    const defaultAddr = data.find(a => a.is_default) || data[0];
                    if (!defaultAddr) {
                        return '<span class="text-muted">-</span>';
                    }
                    // Build complete address
                    const addressParts = [];
                    if (defaultAddr.address_line1) addressParts.push(defaultAddr.address_line1);
                    if (defaultAddr.address_line2) addressParts.push(defaultAddr.address_line2);
                    if (defaultAddr.city) addressParts.push(defaultAddr.city);
                    if (defaultAddr.state) addressParts.push(defaultAddr.state);
                    // Add country if it exists and is not empty
                    if (defaultAddr.country && String(defaultAddr.country).trim() !== '') {
                        addressParts.push(defaultAddr.country);
                    }
                    if (defaultAddr.pincode) addressParts.push(defaultAddr.pincode);
                    const addressText = addressParts.length > 0 ? addressParts.join(', ') : '-';
                    const plusIcon = data.length > 1 ? `<i class="fas fa-plus-circle text-primary ms-2" style="cursor: pointer;" onclick="showAddressesPopup(${row.id})" title="View all addresses"></i>` : '';
                    return `<div class="small">${addressText}</div>${plusIcon}`;
                }
            },
            {
                data: 'orders_count',
                render: function(data, type, row) {
                    if (data === 0) {
                        return '<span class="text-muted">0</span>';
                    }
                    return `<span class="badge bg-primary" style="cursor: pointer;" onclick="showOrdersPopup(${row.id})" title="Click to view orders">${data}</span>`;
                }
            },
            {
                data: 'orders_total',
                render: function(data) {
                    return data ? `₹${data}` : '<span class="text-muted">₹0.00</span>';
                }
            },
            {
                data: 'pending_orders_count',
                render: function(data, type, row) {
                    if (data === 0) {
                        return '<span class="text-muted">0</span>';
                    }
                    return `<span class="badge bg-warning text-dark" style="cursor: pointer;" onclick="showPendingOrdersPopup(${row.id})" title="Click to view pending orders">${data}</span>`;
                }
            },
            {
                data: 'cart_items_count',
                render: function(data, type, row) {
                    if (data === 0) {
                        return '<span class="text-muted">0</span>';
                    }
                    return `<span class="badge bg-info" style="cursor: pointer;" onclick="showCartItemsPopup(${row.id})" title="Click to view cart items">${data}</span>`;
                }
            },
            {
                data: 'is_active',
                render: function(data) {
                    return data ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>';
                }
            },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-secondary edit-customer" data-id="${row.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[8, 'desc']]
    });
}

function loadFormFields() {
    $.ajax({
        url: '{{ route("customers.fields") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                formFields = response.data;
                renderFormFields();
                
                // If editing, populate form
                if (isEditMode && editingCustomerData) {
                    populateForm(editingCustomerData);
                }
            } else {
                $('#customerFormFields').html('<div class="alert alert-danger">Failed to load form fields</div>');
            }
        },
        error: function() {
            $('#customerFormFields').html('<div class="alert alert-danger">Error loading form fields</div>');
        }
    });
}

function renderFormFields() {
    const container = $('#customerFormFields');
    container.empty();
    
    // Group fields by field_group
    fieldGroups = {};
    formFields.forEach(field => {
        const group = field.field_group || 'other';
        if (!fieldGroups[group]) {
            fieldGroups[group] = [];
        }
        fieldGroups[group].push(field);
    });
    
    // Render each group
    const groupOrder = ['basic_info', 'credentials', 'address', 'business', 'preferences', 'qol', 'internal', 'other'];
    
    groupOrder.forEach(groupKey => {
        if (fieldGroups[groupKey] && fieldGroups[groupKey].length > 0) {
            const groupTitle = getGroupTitle(groupKey);
            const groupHtml = `
                <div class="field-group" data-group="${groupKey}">
                    <h6 class="field-group-title">${groupTitle}</h6>
                    <div class="row g-3">
                        ${renderGroupFields(fieldGroups[groupKey])}
                    </div>
                </div>
            `;
            container.append(groupHtml);
        }
    });
    
    // Render any remaining groups
    Object.keys(fieldGroups).forEach(groupKey => {
        if (!groupOrder.includes(groupKey)) {
            const groupTitle = getGroupTitle(groupKey);
            const groupHtml = `
                <div class="field-group" data-group="${groupKey}">
                    <h6 class="field-group-title">${groupTitle}</h6>
                    <div class="row g-3">
                        ${renderGroupFields(fieldGroups[groupKey])}
                    </div>
                </div>
            `;
            container.append(groupHtml);
        }
    });
    
    // Initialize conditional fields
    initializeConditionalFields();
    
    // Note: Select2 initialization for location fields is disabled
    // Country, state, city are now simple text inputs
}

function getGroupTitle(groupKey) {
    const titles = {
        'basic_info': 'Customer Basics',
        'credentials': 'Account Credentials',
        'address': 'Address Details',
        'business': 'Business Information',
        'preferences': 'Preferences',
        'qol': 'Quality-of-Life Fields',
        'internal': 'Internal Use',
        'other': 'Other Information'
    };
    return titles[groupKey] || groupKey.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function renderGroupFields(fields) {
    let html = '';
    fields.forEach(field => {
        html += renderField(field);
    });
    return html;
}

function renderField(field) {
    const fieldId = `field_${field.field_key}`;
    const required = field.is_required ? '<span class="text-danger">*</span>' : '';
    const helpText = field.help_text ? `<div class="field-help-text">${field.help_text}</div>` : '';
    const conditionalClass = field.conditional_rules ? 'conditional-field' : '';
    const conditionalAttrs = field.conditional_rules ? `data-conditional="${JSON.stringify(field.conditional_rules)}"` : '';

    // Location fields (country, state, city) - render as text inputs for now
    const isLocationField = ['country', 'state', 'city'].includes(field.field_key);
    
    let fieldHtml = '';
    const colClass = getColClass(field.input_type);

    if (isLocationField) {
        // Render as simple text input
        return `
            <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                <input type="text" 
                       class="form-control" 
                       id="${fieldId}" 
                       name="${field.field_key}"
                       placeholder="${field.placeholder || 'Enter ' + field.label}">
                ${helpText}
                <div class="invalid-feedback"></div>
            </div>
        `;
    }
    
    switch(field.input_type) {
        case 'text':
        case 'email':
        case 'tel':
        case 'number':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="${field.input_type}" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'password':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="password" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'textarea':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <textarea class="form-control" 
                              id="${fieldId}" 
                              name="${field.field_key}" 
                              rows="3"
                              placeholder="${field.placeholder || ''}"></textarea>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'select':
            const options = field.options || [];
            let optionsHtml = '<option value="">Select ' + field.label + '</option>';
            options.forEach(option => {
                const value = typeof option === 'object' ? option.value : option;
                const label = typeof option === 'object' ? option.label : option;
                optionsHtml += `<option value="${value}">${label}</option>`;
            });

            const selectClass = 'form-select';
            
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <select class="${selectClass}" 
                            id="${fieldId}" 
                            name="${field.field_key}"
                            >
                        ${optionsHtml}
                    </select>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'radio':
            const radioOptions = field.options || [];
            let radioHtml = '';
            radioOptions.forEach((option, index) => {
                const value = typeof option === 'object' ? option.value : option;
                const label = typeof option === 'object' ? option.label : option;
                const radioId = `${fieldId}_${index}`;
                radioHtml += `
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="radio" 
                               id="${radioId}" 
                               name="${field.field_key}" 
                               value="${value}">
                        <label class="form-check-label" for="${radioId}">${label}</label>
                    </div>
                `;
            });
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label class="form-label">${field.label} ${required}</label>
                    ${radioHtml}
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'checkbox':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="${fieldId}" 
                               name="${field.field_key}"
                               value="1">
                        <label class="form-check-label" for="${fieldId}">${field.label}</label>
                    </div>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'date':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="date" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'file':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="file" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        default:
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="text" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
    }
    
    return fieldHtml;
}

function getColClass(inputType) {
    if (inputType === 'textarea') {
        return 'col-12';
    }
    return 'col-md-6';
}

function initializeConditionalFields() {
    $('.conditional-field').each(function() {
        const $field = $(this);
        const rules = $field.data('conditional');
        if (rules) {
            checkConditionalField($field, rules);
            
            if (rules.depends_on) {
                $(document).on('change', `[name="${rules.depends_on}"]`, function() {
                    checkConditionalField($field, rules);
                });
            }
        }
    });
}

function checkConditionalField($field, rules) {
    if (rules.depends_on) {
        const dependentValue = $(`[name="${rules.depends_on}"]`).val();
        const showWhen = rules.show_when;
        
        if (showWhen) {
            if (Array.isArray(showWhen)) {
                if (showWhen.includes(dependentValue)) {
                    $field.show();
                } else {
                    $field.hide();
                }
            } else if (dependentValue === showWhen) {
                $field.show();
            } else {
                $field.hide();
            }
        }
    }
}

// Edit Customer
$(document).on('click', '.edit-customer', function() {
    const id = $(this).data('id');
    $.ajax({
        url: `/customers/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                isEditMode = true;
                editingCustomerData = response.data;
                $('#customerModalLabel').text('Edit Customer');
                $('#customerId').val(response.data.id);
                $('#customerModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading customer data');
        }
    });
});

function populateForm(customer) {
    // Populate basic fields
    Object.keys(customer).forEach(key => {
        const field = $(`#field_${key}`);
        if (field.length) {
            if (field.attr('type') === 'checkbox') {
                field.prop('checked', customer[key] == 1 || customer[key] === true);
            } else if (field.attr('type') === 'radio') {
                $(`input[name="${key}"][value="${customer[key]}"]`).prop('checked', true);
            } else {
                field.val(customer[key]);
            }
        }
    });
    
    // Populate address if exists
    if (customer.addresses && customer.addresses.length > 0) {
        const address = customer.addresses[0];
        $('#field_address_type').val(address.address_type);
        $('#field_address_line1').val(address.address_line1);
        $('#field_address_line2').val(address.address_line2 || '');
        $('#field_landmark').val(address.landmark);
        
        // Handle country, state, city as simple text inputs
        if (address.country && $('#field_country').length) {
            $('#field_country').val(address.country);
        }
        
        if (address.state && $('#field_state').length) {
            $('#field_state').val(address.state);
        }
        
        if (address.city && $('#field_city').length) {
            $('#field_city').val(address.city);
        }
        
        $('#field_pincode').val(address.pincode);
        $('#field_delivery_instructions').val(address.delivery_instructions);
        $('#field_make_default_address').prop('checked', address.is_default);
    }
    
    // Populate custom data
    if (customer.custom_data) {
        Object.keys(customer.custom_data).forEach(key => {
            const field = $(`#field_${key}`);
            if (field.length) {
                field.val(customer.custom_data[key]);
            }
        });
    }
    
    // Trigger conditional field checks
    $('[name]').trigger('change');
}

function saveCustomer() {
    const formData = new FormData($('#customerForm')[0]);
    const url = isEditMode ? `/customers/${$('#customerId').val()}` : '{{ route("customers.store") }}';
    const method = 'POST';
    
    $('#saveCustomerBtn').prop('disabled', true);
    $('#saveCustomerBtn .spinner-border').removeClass('d-none');
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                customersTable.ajax.reload();
                $('#customerModal').modal('hide');
                showToast('success', isEditMode ? 'Customer updated successfully!' : 'Customer created successfully!');
            } else {
                showErrors(response.errors || {});
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors || {};
                showErrors(errors);
                
                // Show toastr with error summary
                const errorMessages = [];
                Object.keys(errors).forEach(key => {
                    if (errors[key] && errors[key].length > 0) {
                        errorMessages.push(errors[key][0]);
                    }
                });
                
                if (errorMessages.length > 0) {
                    const errorSummary = errorMessages.slice(0, 3).join(', ');
                    const moreErrors = errorMessages.length > 3 ? ` and ${errorMessages.length - 3} more` : '';
                    showToast('error', `Validation errors: ${errorSummary}${moreErrors}`);
                } else {
                    showToast('error', 'Please fix the validation errors');
                }
            } else {
                showToast('error', 'Error saving customer');
            }
        },
        complete: function() {
            $('#saveCustomerBtn').prop('disabled', false);
            $('#saveCustomerBtn .spinner-border').addClass('d-none');
        }
    });
}

function showErrors(errors) {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    Object.keys(errors).forEach(key => {
        const field = $(`#field_${key}, [name="${key}"]`).first();
        field.addClass('is-invalid');
        field.siblings('.invalid-feedback').text(errors[key][0]);
    });
}

// Delete customer
$(document).on('click', '.delete-customer', function() {
    const id = $(this).data('id');
    if (confirm('Are you sure you want to delete this customer?')) {
        $.ajax({
            url: `/customers/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    customersTable.ajax.reload();
                    showToast('success', 'Customer deleted successfully');
                }
            },
            error: function() {
                showToast('error', 'Error deleting customer');
            }
        });
    }
});

// Toast notification function
function showToast(type, message) {
    // Get or create toast container
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create unique toast element
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'error' 
        ? 'fas fa-exclamation-circle text-danger me-2' 
        : 'fas fa-check-circle text-success me-2';
    const title = type === 'error' ? 'Error' : 'Success';
    
    const toastHtml = `
        <div id="${toastId}" class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
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
        } catch (e) {
            console.error('Error showing toast:', e);
        }
    }
}

// Load areas for filter dropdown
function loadAreas() {
    $.ajax({
        url: '{{ route("customers.areas") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const areaSelect = $('#filterArea');
                areaSelect.empty().append('<option value="">All Areas</option>');
                
                // Add states
                if (response.data.states && response.data.states.length > 0) {
                    areaSelect.append('<optgroup label="States">');
                    response.data.states.forEach(function(state) {
                        areaSelect.append(`<option value="${state.value}">${state.label}</option>`);
                    });
                    areaSelect.append('</optgroup>');
                }
                
                // Add cities
                if (response.data.cities && response.data.cities.length > 0) {
                    areaSelect.append('<optgroup label="Cities">');
                    response.data.cities.forEach(function(city) {
                        areaSelect.append(`<option value="${city.value}">${city.label}</option>`);
                    });
                    areaSelect.append('</optgroup>');
                }
            }
        },
        error: function() {
            console.error('Error loading areas');
        }
    });
}

// Debounce function
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

// Initialize Select2 for location fields (country, state, city)
// DISABLED: Location fields are now simple text inputs
function initializeLocationSelects() {
    // Function disabled - location fields are now text inputs
    return;
    console.log('[DEBUG] initializeLocationSelects called');
    // Use a small delay to ensure DOM is ready
    setTimeout(function() {
        console.log('[DEBUG] Inside setTimeout, checking for country field');
        // Initialize country select
        const $countrySelect = $('#field_country');
        console.log('[DEBUG] Country select found:', $countrySelect.length, $countrySelect[0]);
        
        if ($countrySelect.length) {
            console.log('[DEBUG] Country select element:', $countrySelect[0]);
            console.log('[DEBUG] Is select element?', $countrySelect.is('select'));
            console.log('[DEBUG] Current classes:', $countrySelect.attr('class'));
            console.log('[DEBUG] Has select2?', $countrySelect.hasClass('select2-hidden-accessible'));
            console.log('[DEBUG] Readonly?', $countrySelect.prop('readonly'));
            console.log('[DEBUG] Disabled?', $countrySelect.prop('disabled'));
            
            // Destroy existing Select2 instance if it exists
            if ($countrySelect.hasClass('select2-hidden-accessible')) {
                console.log('[DEBUG] Destroying existing Select2 instance');
                try {
                    $countrySelect.select2('destroy');
                    console.log('[DEBUG] Select2 destroyed successfully');
                } catch(e) {
                    console.error('[DEBUG] Error destroying Select2:', e);
                }
            }
            
            // Ensure it's a select element (not input)
            if ($countrySelect.is('select')) {
                // Remove readonly/disabled attributes if present
                $countrySelect.prop('readonly', false).prop('disabled', false);
                console.log('[DEBUG] Removed readonly/disabled, initializing Select2...');
                
                try {
                    $countrySelect.select2({
                        placeholder: 'Select Country',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#customerModal'),
                        ajax: {
                            url: '{{ route("customers.countries") }}',
                            dataType: 'json',
                            type: 'GET',
                            delay: 250,
                            data: function(params) {
                                console.log('[DEBUG] Select2 AJAX request with term:', params.term);
                                return {
                                    search: params.term || ''
                                };
                            },
                            processResults: function(data) {
                                console.log('[DEBUG] Select2 processResults:', data);
                                if (data && data.success && Array.isArray(data.data)) {
                                    return {
                                        results: data.data.map(function(country) {
                                            return {
                                                id: country.id,
                                                text: country.text || country.name
                                            };
                                        })
                                    };
                                }
                                return { results: [] };
                            },
                            cache: true
                        }
                    });
                    console.log('[DEBUG] Select2 initialized successfully on country field');
                    console.log('[DEBUG] Country select after init:', $('#field_country').hasClass('select2-hidden-accessible'));
                    
                    // Fix readonly issue - remove aria-readonly from Select2 rendered element
                    // This is a known Select2 issue where it sets aria-readonly="true" by default
                    const fixReadonly = function() {
                        const $rendered = $('#select2-field_country-container');
                        if ($rendered.length) {
                            $rendered.attr('aria-readonly', 'false');
                            $rendered.removeAttr('readonly');
                            console.log('[DEBUG] Removed readonly from Select2 rendered element');
                            
                            // Also remove from the selection element
                            const $selection = $countrySelect.next('.select2-container').find('.select2-selection__rendered');
                            if ($selection.length) {
                                $selection.attr('aria-readonly', 'false');
                                $selection.removeAttr('readonly');
                            }
                        } else {
                            // Retry if element not ready yet
                            setTimeout(fixReadonly, 50);
                        }
                    };
                    
                    // Try immediately and also after a short delay
                    fixReadonly();
                    setTimeout(fixReadonly, 100);
                    setTimeout(fixReadonly, 300);
                    
                    // Also ensure the select element itself is not readonly
                    $countrySelect.prop('readonly', false);
                    $countrySelect.removeAttr('readonly');
                    
                    // Re-run fix after Select2 opens
                    $countrySelect.on('select2:open', function() {
                        setTimeout(fixReadonly, 10);
                    });
                    
                    // Add click event listener for debugging
                    $countrySelect.on('select2:open', function() {
                        console.log('[DEBUG] Select2 dropdown opened for country');
                    });
                    
                    $countrySelect.on('click', function() {
                        console.log('[DEBUG] Country field clicked directly');
                    });
                    
                    // Check if Select2 container is clickable
                    const $select2Container = $countrySelect.next('.select2-container');
                    if ($select2Container.length) {
                        console.log('[DEBUG] Select2 container found:', $select2Container[0]);
                        $select2Container.on('click', function() {
                            console.log('[DEBUG] Select2 container clicked');
                        });
                    } else {
                        console.warn('[DEBUG] Select2 container not found!');
                    }
                } catch(e) {
                    console.error('[DEBUG] Error initializing Select2 on country:', e);
                    console.error('[DEBUG] Error stack:', e.stack);
                }
            } else {
                console.warn('[DEBUG] Country field is not a select element:', $countrySelect[0], 'Type:', $countrySelect[0]?.tagName);
            }
        } else {
            console.warn('[DEBUG] Country field (#field_country) not found in DOM');
            console.log('[DEBUG] Available fields in form:', $('#customerFormFields').find('select, input').map(function() { return $(this).attr('id'); }).get());
        }
        
        // Initialize state select (depends on country)
        const $stateSelect = $('#field_state');
        if ($stateSelect.length) {
            // Destroy existing Select2 instance if it exists
            if ($stateSelect.hasClass('select2-hidden-accessible')) {
                $stateSelect.select2('destroy');
            }
            
            if ($stateSelect.is('select')) {
                // Remove readonly/disabled attributes if present
                $stateSelect.prop('readonly', false).prop('disabled', false);
                
                $stateSelect.select2({
                    placeholder: 'Select State',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#customerModal'),
                    ajax: {
                        url: '{{ route("customers.states") }}',
                        dataType: 'json',
                        type: 'GET',
                        delay: 250,
                        data: function(params) {
                            return {
                                country_id: $countrySelect.val() || '',
                                search: params.term || ''
                            };
                        },
                        processResults: function(data) {
                            if (data && data.success && Array.isArray(data.data)) {
                                return {
                                    results: data.data.map(function(state) {
                                        return {
                                            id: state.id,
                                            text: state.text || state.name
                                        };
                                    })
                                };
                            }
                            return { results: [] };
                        },
                        cache: true
                    }
                });
                
                // Fix readonly for state field
                const fixStateReadonly = function() {
                    const $rendered = $('#select2-field_state-container');
                    if ($rendered.length) {
                        $rendered.attr('aria-readonly', 'false').removeAttr('readonly');
                        const $selection = $stateSelect.next('.select2-container').find('.select2-selection__rendered');
                        if ($selection.length) {
                            $selection.attr('aria-readonly', 'false').removeAttr('readonly');
                        }
                    } else {
                        setTimeout(fixStateReadonly, 50);
                    }
                };
                setTimeout(fixStateReadonly, 100);
                $stateSelect.on('select2:open', function() {
                    setTimeout(fixStateReadonly, 10);
                });
                
                // Reload states when country changes
                $countrySelect.off('change.location').on('change.location', function() {
                    $stateSelect.val(null).trigger('change');
                    $('#field_city').val(null).trigger('change');
                });
            } else {
                console.warn('State field is not a select element');
            }
        } else {
            console.warn('State field (#field_state) not found in DOM');
        }
        
        // Initialize city select (depends on state)
        const $citySelect = $('#field_city');
        if ($citySelect.length) {
            // Destroy existing Select2 instance if it exists
            if ($citySelect.hasClass('select2-hidden-accessible')) {
                $citySelect.select2('destroy');
            }
            
            if ($citySelect.is('select')) {
                // Remove readonly/disabled attributes if present
                $citySelect.prop('readonly', false).prop('disabled', false);
                
                $citySelect.select2({
                    placeholder: 'Select City',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#customerModal'),
                    ajax: {
                        url: '{{ route("customers.cities") }}',
                        dataType: 'json',
                        type: 'GET',
                        delay: 250,
                        data: function(params) {
                            return {
                                state_id: $stateSelect.val() || '',
                                search: params.term || ''
                            };
                        },
                        processResults: function(data) {
                            if (data && data.success && Array.isArray(data.data)) {
                                return {
                                    results: data.data.map(function(city) {
                                        return {
                                            id: city.id,
                                            text: city.text || city.name
                                        };
                                    })
                                };
                            }
                            return { results: [] };
                        },
                        cache: true
                    }
                });
                
                // Fix readonly for city field
                const fixCityReadonly = function() {
                    const $rendered = $('#select2-field_city-container');
                    if ($rendered.length) {
                        $rendered.attr('aria-readonly', 'false').removeAttr('readonly');
                        const $selection = $citySelect.next('.select2-container').find('.select2-selection__rendered');
                        if ($selection.length) {
                            $selection.attr('aria-readonly', 'false').removeAttr('readonly');
                        }
                    } else {
                        setTimeout(fixCityReadonly, 50);
                    }
                };
                setTimeout(fixCityReadonly, 100);
                $citySelect.on('select2:open', function() {
                    setTimeout(fixCityReadonly, 10);
                });
                
                // Reload cities when state changes
                $stateSelect.off('change.location').on('change.location', function() {
                    $citySelect.val(null).trigger('change');
                });
            } else {
                console.warn('City field is not a select element');
            }
        } else {
            console.warn('City field (#field_city) not found in DOM');
        }
    }, 200); // Increased delay to ensure DOM is fully ready
}
</script>
@endpush
