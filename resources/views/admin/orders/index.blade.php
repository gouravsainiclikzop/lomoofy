@extends('layouts.admin')

@section('title', 'Orders')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Orders</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" id="addOrderBtn">
                    <i class='bx bx-plus'></i> Add Order
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-12">
                        <input type="text" placeholder="Search orders..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            
            <!-- Status Filter Tabs -->
            <div class="px-4 pt-3">
                <ul class="nav nav-tabs order-status-tabs" id="orderStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-status="" type="button" role="tab">
                            All
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="pending" type="button" role="tab">
                            Pending
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="processing" type="button" role="tab">
                            Processing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="shipped" type="button" role="tab">
                            Shipped
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="delivered" type="button" role="tab">
                            Delivered
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="cancelled" type="button" role="tab">
                            Cancelled
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="refunded" type="button" role="tab">
                            Refunded
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="table-responsive p-4">
                <table class="table table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th width="60">S.no</th>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Source</th>
                            <th>Payment Status</th>
                            <th>Order-Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <tr>
                            <td colspan="10" class="text-center py-5">
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

<!-- View Invoice Modal -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInvoiceModalLabel">View Invoice</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" id="printInvoiceBtn">
                        <i class='bx bx-printer'></i> Print
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="invoiceContent">
                <!-- Invoice content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Order Modal (Reuse existing modal from old page) -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="orderForm">
                <div class="modal-body">
                    <input type="hidden" id="orderId" name="id">
                    
                    <!-- Customer Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" id="customerId" name="customer_id" required>
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order Status</label>
                            <select class="form-select" id="orderStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                    </div>

                    <!-- Customer Info Card -->
                    <div id="customerInfoCard" class="mb-4" style="display: none;">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="card-title mb-0">
                                        <i class='bx bx-user me-2'></i>Customer Information
                                    </h6>
                                    <button type="button" class="btn btn-link p-0 border-0" id="toggleCustomerInfo" title="Collapse">
                                        <i class='bx bx-chevron-up text-warning' style="font-size: 1.5rem;"></i>
                                    </button>
                                </div>
                                <div id="customerInfoContent">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted small">Loading customer details...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order Items</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                        <div id="orderItemsContainer">
                            <!-- Items will be added here dynamically -->
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary mb-4">
                        <h6 class="mb-3">Order Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" class="form-control numeric-input" id="subtotal" name="subtotal" value="0" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tax Amount</label>
                                    <input type="number" class="form-control numeric-input" id="taxAmount" name="tax_amount" value="0" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Shipping Amount</label>
                                    <input type="number" class="form-control numeric-input" id="shippingAmount" name="shipping_amount" value="0" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount Amount</label>
                                    <input type="number" class="form-control numeric-input" id="discountAmount" name="discount_amount" value="0" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Total Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control numeric-input" id="totalAmount" name="total_amount" value="0" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <input type="text" class="form-control" id="paymentMethod" name="payment_method" placeholder="e.g., Cash, Card, Online">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Status</label>
                                    <select class="form-select" id="paymentStatus" name="payment_status">
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="failed">Failed</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="orderNotes" name="notes" rows="3" placeholder="Additional notes about this order"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveOrderBtn">
                        <span id="saveBtnText">Save Order</span>
                        <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
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
                Are you sure you want to delete this order? This action cannot be undone.
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
            <i class='bx bx-check-circle text-success me-2'></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('styles')
<style>
.order-item-row {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 0;
}
.order-item-row:last-child {
    border-bottom: none;
}
.order-summary {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
}
.status-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}
.status-select {
    min-width: 120px;
}
.order-status-tabs {
    border-bottom: 2px solid #dee2e6;
}
.order-status-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    background: transparent;
    cursor: pointer;
}
.order-status-tabs .nav-link:hover {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background: rgba(13, 110, 253, 0.05);
}
.order-status-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background: transparent;
    font-weight: 600;
}
.order-status-tabs .nav-link:focus {
    outline: none;
    box-shadow: none;
}
.invoice-container {
    padding: 2rem;
    background: white;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #dee2e6;
}
.invoice-company {
    flex: 1;
}
.invoice-company-logo {
    font-size: 2rem;
    font-weight: bold;
    color: #20c997;
    margin-bottom: 0.5rem;
}
.invoice-shipping {
    flex: 1;
    text-align: right;
}
.invoice-items-table {
    width: 100%;
    margin: 2rem 0;
}
.invoice-items-table th,
.invoice-items-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}
.invoice-items-table th {
    background: #f8f9fa;
    font-weight: 600;
}
.invoice-totals {
    margin-top: 2rem;
    text-align: right;
}
.invoice-totals table {
    width: 100%;
    max-width: 300px;
    margin-left: auto;
}
.invoice-totals td {
    padding: 0.5rem;
}
.invoice-totals td:first-child {
    text-align: left;
    font-weight: 600;
}
.invoice-totals .grand-total {
    font-size: 1.2rem;
    font-weight: bold;
    border-top: 2px solid #dee2e6;
}
@media print {
    .modal-header,
    .modal-footer,
    .btn {
        display: none !important;
    }
    .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
    }
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let deleteOrderId = null;
    let currentPage = 1;
    let isEditMode = false;
    let customers = [];
    let products = [];
    let productsLoading = false;
    let productsLoaded = false;
    
    let warehouses = [];
    let warehousesLoading = false;
    let warehousesLoaded = false;
    let itemCounter = 0;
    
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Load orders on page load
    loadOrders();
    
    // Status Tab Click Handler
    $(document).on('click', '.order-status-tabs .nav-link', function(e) {
        e.preventDefault();
        // Remove active class from all tabs
        $('.order-status-tabs .nav-link').removeClass('active');
        // Add active class to clicked tab
        $(this).addClass('active');
        // Reset to first page
        currentPage = 1;
        // Load orders with new filter
        loadOrders();
    });
    
    // Search functionality with debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadOrders();
        }, 500);
    });
    
    // Load customers
    loadCustomers();
    
    // Load products
    loadProducts();
    
    // Load warehouses
    loadWarehouses();
    
    // Open Modal for Add
    $('#addOrderBtn').on('click', function() {
        $('#orderModalLabel').text('Create New Order');
        $('#orderForm')[0].reset();
        $('#orderId').val('');
        $('#orderItemsContainer').empty();
        itemCounter = 0;
        $('#customerInfoCard').hide();
        $('#orderModal').modal('show');
        addOrderItem(); // Add one empty item
    });
    
    // View Invoice
    $(document).on('click', '.view-order', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        viewInvoice(id);
    });
    
    // Edit Order
    $(document).on('click', '.edit-order', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        editOrder(id);
    });
    
    // Delete Order
    $(document).on('click', '.delete-order', function(e) {
        e.preventDefault();
        deleteOrderId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    // Confirm Delete
    $('#confirmDelete').click(function() {
        deleteOrder();
    });
    
    // Inline Status Change
    $(document).on('change', '.status-select', function() {
        let orderId = $(this).data('id');
        let newStatus = $(this).val();
        let $select = $(this);
        let oldStatus = $select.data('old-status');
        let currentTabStatus = $('.order-status-tabs .nav-link.active').data('status') || '';
        
        // Update status via AJAX
        $.ajax({
            url: '{{ route("orders.update-status", ":id") }}'.replace(':id', orderId),
            type: 'POST',
            data: {
                status: newStatus
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Order status updated successfully');
                    $select.data('old-status', newStatus);
                    
                    // If a status tab is active and the order status changed to a different status, reload the table
                    // This will hide the order if it no longer matches the current filter
                    if (currentTabStatus && newStatus !== currentTabStatus) {
                        loadOrders();
                    }
                }
            },
            error: function(xhr) {
                $select.val(oldStatus); // Revert on error
                if(xhr.status === 422) {
                    showToast('error', xhr.responseJSON.errors.status[0] || 'Error updating status');
                } else {
                    showToast('error', 'Error updating order status');
                }
            }
        });
    });
    
    // Print Invoice
    $('#printInvoiceBtn').on('click', function() {
        window.print();
    });
    
    // Load Orders Function
    function loadOrders() {
        let search = $('#tableSearch').val();
        // Get status from active tab
        let $activeTab = $('.order-status-tabs .nav-link.active');
        let status = $activeTab.length ? ($activeTab.data('status') || '') : '';
        
        // Show loading state
        let tbody = $('#ordersTableBody');
        tbody.html('<tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
        
        let params = {
            draw: 1,
            start: 0, // Always start from 0 for client-side filtering
            length: 1000 // Get more records to filter client-side
        };
        
        if (search) {
            params.search = { value: search };
        }
        
        $.ajax({
            url: '{{ route("orders.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                tbody.empty();
                
                let filteredCount = 0;
                
                if(response.data && response.data.length > 0) {
                    let allData = response.data;
                    
                    // Filter by status if a tab is selected (not "All")
                    let filteredData = allData;
                    if (status) {
                        filteredData = allData.filter(function(order) {
                            return order.status === status;
                        });
                    }
                    
                    filteredCount = filteredData.length;
                    
                    // Apply pagination to filtered data
                    let startIndex = (currentPage - 1) * 20;
                    let endIndex = startIndex + 20;
                    let paginatedData = filteredData.slice(startIndex, endIndex);
                    let serialNumber = startIndex + 1;
                    
                    if (paginatedData.length > 0) {
                        paginatedData.forEach(function(order) {
                            let statusColors = {
                                'pending': 'bg-warning',
                                'processing': 'bg-info',
                                'shipped': 'bg-primary',
                                'delivered': 'bg-success',
                                'cancelled': 'bg-danger',
                                'refunded': 'bg-secondary'
                            };
                            
                            let paymentStatusColors = {
                                'pending': 'bg-warning',
                                'paid': 'bg-success',
                                'failed': 'bg-danger',
                                'refunded': 'bg-secondary'
                            };
                            
                            let statusColor = statusColors[order.status] || 'bg-secondary';
                            let paymentStatusColor = paymentStatusColors[order.payment_status] || 'bg-secondary';
                        
                        let isEditable = order.source !== 'online';
                        let actionButtons = '';
                        
                        if (isEditable) {
                            actionButtons = `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info view-order" data-id="${order.id}" title="View">
                                        <i class='bx bx-show'></i>
                                    </button>
                                    <button class="btn btn-outline-primary edit-order" data-id="${order.id}" title="Edit">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-order" data-id="${order.id}" title="Delete">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            actionButtons = `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info view-order" data-id="${order.id}" title="View">
                                        <i class='bx bx-show'></i>
                                    </button>
                                </div>
                            `;
                        }
                        
                        let row = `
                            <tr data-id="${order.id}">
                                <td>${serialNumber++}</td>
                                <td><strong>${order.order_number}</strong></td>
                                <td>${order.created_at || order.date}</td>
                                <td>
                                    <div><strong>${order.customer_name}</strong></div>
                                    <div class="text-muted small">${order.customer_email}</div>
                                    <div class="text-muted small">${order.customer_phone}</div>
                                </td>
                                <td><span class="badge bg-info">${order.items_count}</span></td>
                                <td><strong>₹${parseFloat(order.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                                <td>
                                    ${order.source === 'online' 
                                        ? '<span class="badge bg-primary">Online</span>' 
                                        : '<span class="badge bg-secondary">Offline</span>'}
                                </td>
                                <td><span class="badge ${paymentStatusColor} status-badge">${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</span></td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-id="${order.id}" data-old-status="${order.status}">
                                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                        <option value="refunded" ${order.status === 'refunded' ? 'selected' : ''}>Refunded</option>
                                    </select>
                                </td>
                                <td>${actionButtons}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                    } else {
                        tbody.html('<tr><td colspan="10" class="text-center py-4">No orders found for this status</td></tr>');
                    }
                } else {
                    tbody.html('<tr><td colspan="10" class="text-center py-4">No orders found</td></tr>');
                }
                
                // Update pagination with filtered data count
                updatePagination({
                    recordsFiltered: filteredCount,
                    recordsTotal: response.recordsTotal
                });
            },
            error: function(xhr) {
                console.error('Error loading orders:', xhr);
                $('#ordersTableBody').html('<tr><td colspan="10" class="text-center py-4 text-danger">Error loading orders</td></tr>');
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
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        currentPage = page;
        loadOrders();
    });
    
    // View Invoice
    function viewInvoice(id) {
        $.ajax({
            url: '{{ route("orders.show", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let order = response.data;
                    let shippingAddress = order.shipping_address || {};
                    
                    let invoiceHtml = `
                        <div class="invoice-container">
                            <div class="invoice-header">
                                <div class="invoice-company">
                                    <div class="invoice-company-logo">Lomoofy</div>
                                    <div><strong>Lomoof</strong></div>
                                    <div>+91 9876315314</div>
                                    <div>info@lomoof.com</div>
                                    <div>123, Main Street, Anytown, USA</div>
                                    <div class="mt-2">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-primary">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                                    </div>
                                </div>
                                <div class="invoice-shipping">
                                    <h5>Shipping Details</h5>
                                    <div><strong>Order Id:</strong> ${order.order_number}</div>
                                    <div><strong>Name:</strong> ${order.customer.full_name}</div>
                                    <div><strong>Phone:</strong> ${order.customer.phone || '-'}</div>
                                    <div><strong>Email:</strong> ${order.customer.email}</div>
                                    <div><strong>Address:</strong> ${shippingAddress.full_address || '-'}</div>
                                    <div><strong>State:</strong> ${shippingAddress.state || '-'}${shippingAddress.city ? ', ' + shippingAddress.city : ''}</div>
                                    <div><strong>Pin Code:</strong> ${shippingAddress.pincode || '-'}, India</div>
                                    <div><strong>Payment Method:</strong> ${order.payment_method || 'Offline'}</div>
                                </div>
                            </div>
                            
                            <table class="invoice-items-table">
                                <thead>
                                    <tr>
                                        <th>Item name</th>
                                        <th>Variant</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    order.items.forEach(function(item) {
                        invoiceHtml += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.variant_name || '-'}</td>
                                <td>₹ ${parseFloat(item.unit_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${item.quantity}</td>
                                <td>₹ ${parseFloat(item.total_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    });
                    
                    invoiceHtml += `
                                </tbody>
                            </table>
                            
                            <div class="invoice-totals">
                                <table>
                                    <tr>
                                        <td>SubTotal</td>
                                        <td>₹ ${parseFloat(order.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Discount</td>
                                        <td>₹ ${parseFloat(order.discount_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Charges</td>
                                        <td>₹ ${parseFloat(order.shipping_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Grand Total</td>
                                        <td>₹ ${parseFloat(order.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <p class="text-muted">Thank you for choosing us.</p>
                            </div>
                        </div>
                    `;
                    
                    $('#invoiceContent').html(invoiceHtml);
                    $('#viewInvoiceModal').modal('show');
                }
            },
            error: function(xhr) {
                showToast('error', 'Error loading invoice');
            }
        });
    }
    
    // Load Customers
    function loadCustomers() {
        $.ajax({
            url: '{{ route("orders.customers") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    customers = response.data;
                    const select = $('#customerId');
                    select.empty().append('<option value="">Select Customer</option>');
                    customers.forEach(function(customer) {
                        select.append(`<option value="${customer.id}">${customer.full_name} (${customer.email})</option>`);
                    });
                }
            }
        });
    }
    
    // Load Warehouses
    function loadWarehouses() {
        if (warehousesLoading) {
            return;
        }
        
        warehousesLoading = true;
        $.ajax({
            url: '{{ route("orders.warehouses") }}',
            type: 'GET',
            success: function(response) {
                warehousesLoading = false;
                if (response.success) {
                    warehouses = response.data || [];
                    warehousesLoaded = true;
                    
                    // Populate warehouse dropdowns in existing items
                    $('.warehouse-select').each(function() {
                        const $select = $(this);
                        $select.empty().append('<option value="">Auto (Default)</option>');
                        warehouses.forEach(function(warehouse) {
                            $select.append(`<option value="${warehouse.id}">${warehouse.name} (${warehouse.code})</option>`);
                        });
                    });
                } else {
                    warehouses = [];
                    warehousesLoaded = false;
                }
            },
            error: function(xhr) {
                warehousesLoading = false;
                warehousesLoaded = false;
                warehouses = [];
            }
        });
    }
    
    // Load Products
    function loadProducts() {
        if (productsLoading) {
            return;
        }
        
        productsLoading = true;
        $.ajax({
            url: '{{ route("orders.products") }}',
            type: 'GET',
            success: function(response) {
                productsLoading = false;
                if (response.success) {
                    products = response.data || [];
                    productsLoaded = true;
                } else {
                    products = [];
                    productsLoaded = false;
                }
            },
            error: function(xhr) {
                productsLoading = false;
                productsLoaded = false;
                products = [];
            }
        });
    }
    
    // Add Order Item (simplified version - you can expand this)
    function addOrderItem(itemData = null) {
        itemCounter++;
        const itemId = `item_${itemCounter}`;
        
        let productSelect = '<option value="">Select Product</option>';
        if (productsLoaded && products && products.length > 0) {
            products.forEach(function(product) {
                productSelect += `<option value="${product.id}" data-type="${product.type}">${product.name} (${product.sku})</option>`;
            });
        }
        
        let itemHtml = `
            <div class="order-item-row" data-item-id="${itemId}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" data-item-id="${itemId}" required>
                            ${productSelect}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Variant</label>
                        <select class="form-select variant-select" data-item-id="${itemId}">
                            <option value="">Select Variant</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Warehouse</label>
                        <select class="form-select warehouse-select" data-item-id="${itemId}">
                            <option value="">Auto (Default)</option>
                        </select>
                        <small class="text-muted">Uses product's default warehouse</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control item-quantity" data-item-id="${itemId}" value="${itemData ? itemData.quantity : 1}" min="1" required>
                        <small class="text-muted stock-info" data-item-id="${itemId}" style="display: none;"></small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit Price</label>
                        <input type="number" class="form-control item-unit-price" data-item-id="${itemId}" value="${itemData ? itemData.unit_price : 0}" step="0.01" min="0" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm w-100 remove-item" data-item-id="${itemId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#orderItemsContainer').append(itemHtml);
        
        // Populate warehouse dropdown
        const warehouseSelect = $(`.warehouse-select[data-item-id="${itemId}"]`);
        warehouseSelect.empty().append('<option value="">Auto (Default)</option>');
        if (warehousesLoaded && warehouses.length > 0) {
            warehouses.forEach(function(warehouse) {
                const selected = itemData && itemData.warehouse_id == warehouse.id ? 'selected' : '';
                warehouseSelect.append(`<option value="${warehouse.id}" ${selected}>${warehouse.name} (${warehouse.code})</option>`);
            });
        }
        
        // Warehouse change handler - check stock availability
        warehouseSelect.on('change', function() {
            checkItemStockAvailability(itemId);
        });
        
        // Product change handler
        $(`.product-select[data-item-id="${itemId}"]`).on('change', function() {
            const productId = $(this).val();
            const variantSelect = $(`.variant-select[data-item-id="${itemId}"]`);
            const unitPriceInput = $(`.item-unit-price[data-item-id="${itemId}"]`);
            
            variantSelect.empty().append('<option value="">Select Variant</option>');
            unitPriceInput.val(0);
            
            if (productId) {
                const product = products.find(p => parseInt(p.id) === parseInt(productId));
                if (product) {
                    if (product.variants && product.variants.length > 0) {
                        product.variants.forEach(function(variant) {
                            variantSelect.append(`<option value="${variant.id}" data-price="${variant.price || 0}">${variant.name} (${variant.sku})</option>`);
                        });
                    } else {
                        unitPriceInput.val(product.price || 0);
                    }
                }
            }
        });
        
        // Variant change handler
        $(`.variant-select[data-item-id="${itemId}"]`).on('change', function() {
            const variantId = $(this).val();
            const unitPriceInput = $(`.item-unit-price[data-item-id="${itemId}"]`);
            
            if (variantId) {
                const selectedOption = $(this).find('option:selected');
                unitPriceInput.val(selectedOption.data('price') || 0);
            } else {
                const productId = $(`.product-select[data-item-id="${itemId}"]`).val();
                const product = products.find(p => parseInt(p.id) === parseInt(productId));
                unitPriceInput.val(product ? (product.price || 0) : 0);
            }
            checkItemStockAvailability(itemId);
            calculateSubtotal();
        });
        
        // Quantity change handler
        $(`.item-quantity[data-item-id="${itemId}"]`).on('input', function() {
            checkItemStockAvailability(itemId);
            calculateSubtotal();
        });
        
        // Check stock availability function
        function checkItemStockAvailability(itemId) {
            const productId = $(`.product-select[data-item-id="${itemId}"]`).val();
            const variantId = $(`.variant-select[data-item-id="${itemId}"]`).val();
            const warehouseId = $(`.warehouse-select[data-item-id="${itemId}"]`).val();
            const quantity = parseInt($(`.item-quantity[data-item-id="${itemId}"]`).val()) || 0;
            const stockInfo = $(`.stock-info[data-item-id="${itemId}"]`);
            
            if (!productId || quantity <= 0) {
                stockInfo.hide();
                return;
            }
            
            $.ajax({
                url: '{{ route("orders.stock-availability") }}',
                type: 'GET',
                data: {
                    product_id: productId,
                    product_variant_id: variantId || null,
                    warehouse_id: warehouseId || null,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        const stock = response.data;
                        if (stock.available) {
                            stockInfo.removeClass('text-danger').addClass('text-success')
                                .text(`Stock: ${stock.quantity} available`)
                                .show();
                        } else {
                            stockInfo.removeClass('text-success').addClass('text-danger')
                                .text(`Insufficient stock! Available: ${stock.quantity}`)
                                .show();
                        }
                    }
                },
                error: function() {
                    stockInfo.hide();
                }
            });
        }
        
        // Remove item handler
        $(`.remove-item[data-item-id="${itemId}"]`).on('click', function() {
            $(`.order-item-row[data-item-id="${itemId}"]`).remove();
            calculateSubtotal();
        });
        
        // Add item button
        $('#addItemBtn').on('click', function() {
            addOrderItem();
        });
    }
    
    // Calculate Subtotal
    function calculateSubtotal() {
        let subtotal = 0;
        $('.order-item-row').each(function() {
            const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.item-unit-price').val()) || 0;
            subtotal += quantity * unitPrice;
        });
        $('#subtotal').val(subtotal.toFixed(2));
        calculateTotal();
    }
    
    // Calculate Total
    function calculateTotal() {
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const taxAmount = parseFloat($('#taxAmount').val()) || 0;
        const shippingAmount = parseFloat($('#shippingAmount').val()) || 0;
        const discountAmount = parseFloat($('#discountAmount').val()) || 0;
        
        const total = subtotal + taxAmount + shippingAmount - discountAmount;
        $('#totalAmount').val(total.toFixed(2));
    }
    
    // Calculate total when amounts change
    $('#subtotal, #taxAmount, #shippingAmount, #discountAmount').on('input', calculateTotal);
    
    // Customer selection handler
    $('#customerId').on('change', function() {
        const customerId = $(this).val();
        if (customerId) {
            loadCustomerDetails(customerId);
        } else {
            $('#customerInfoCard').slideUp(300);
        }
    });
    
    // Load Customer Details
    function loadCustomerDetails(customerId) {
        $('#customerInfoCard').slideDown(300);
        $('#customerInfoContent').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted small">Loading customer details...</p>
            </div>
        `);
        
        $.ajax({
            url: `/orders/customers/${customerId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let customer = response.data;
                    let html = `
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${customer.full_name}</h6>
                                        <div class="small text-muted">
                                            <div class="mb-1"><i class='bx bx-envelope me-1'></i>${customer.email}</div>
                                            <div class="mb-1"><i class='bx bx-phone me-1'></i>${customer.phone || '-'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#customerInfoContent').html(html);
                }
            },
            error: function(xhr) {
                $('#customerInfoContent').html(`
                    <div class="alert alert-danger mb-0">
                        <i class='bx bx-error-circle me-2'></i>Error loading customer details
                    </div>
                `);
            }
        });
    }
    
    // Form submission
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        saveOrder();
    });
    
    // Save Order
    function saveOrder() {
        const formData = {
            customer_id: $('#customerId').val(),
            status: $('#orderStatus').val(),
            payment_method: $('#paymentMethod').val(),
            payment_status: $('#paymentStatus').val(),
            subtotal: $('#subtotal').val(),
            tax_amount: $('#taxAmount').val(),
            shipping_amount: $('#shippingAmount').val(),
            discount_amount: $('#discountAmount').val(),
            total_amount: $('#totalAmount').val(),
            notes: $('#orderNotes').val(),
            items: []
        };
        
        // Collect items
        $('.order-item-row').each(function() {
            const productId = $(this).find('.product-select').val();
            const variantId = $(this).find('.variant-select').val();
            const warehouseId = $(this).find('.warehouse-select').val();
            const quantity = $(this).find('.item-quantity').val();
            const unitPrice = $(this).find('.item-unit-price').val();
            
            if (productId && quantity) {
                formData.items.push({
                    product_id: productId,
                    product_variant_id: variantId || null,
                    warehouse_id: warehouseId || null,
                    quantity: parseInt(quantity),
                    unit_price: parseFloat(unitPrice)
                });
            }
        });
        
        if (formData.items.length === 0) {
            showToast('error', 'Please add at least one item to the order');
            return;
        }
        
        const url = isEditMode ? `/orders/${$('#orderId').val()}` : '{{ route("orders.store") }}';
        const method = isEditMode ? 'PUT' : 'POST';
        
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveOrderBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#orderModal').modal('hide');
                    loadOrders();
                    showToast('success', isEditMode ? 'Order updated successfully!' : 'Order created successfully!');
                } else {
                    showToast('error', response.message || 'Error saving order');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = 'Validation errors: ';
                    Object.keys(errors).forEach(key => {
                        errorMsg += `${errors[key][0]}; `;
                    });
                    showToast('error', errorMsg.trim());
                } else if (xhr.status === 403) {
                    showToast('error', xhr.responseJSON.message || 'Cannot edit online orders');
                } else {
                    showToast('error', 'Error saving order');
                }
            },
            complete: function() {
                $('#saveBtnText').removeClass('d-none');
                $('#saveBtnSpinner').addClass('d-none');
                $('#saveOrderBtn').prop('disabled', false);
            }
        });
    }
    
    // Edit Order
    function editOrder(id) {
        $.ajax({
            url: `/orders/${id}/edit`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    isEditMode = true;
                    const order = response.data;
                    
                    $('#orderId').val(order.id);
                    $('#orderModalLabel').text('Edit Order');
                    $('#customerId').val(order.customer_id);
                    
                    if (order.customer_id) {
                        loadCustomerDetails(order.customer_id);
                    }
                    
                    $('#orderStatus').val(order.status);
                    $('#paymentMethod').val(order.payment_method);
                    $('#paymentStatus').val(order.payment_status);
                    $('#subtotal').val(order.subtotal);
                    $('#taxAmount').val(order.tax_amount);
                    $('#shippingAmount').val(order.shipping_amount);
                    $('#discountAmount').val(order.discount_amount);
                    $('#totalAmount').val(order.total_amount);
                    $('#orderNotes').val(order.notes);
                    
                    // Clear and add items
                    $('#orderItemsContainer').empty();
                    itemCounter = 0;
                    order.items.forEach(function(item) {
                        addOrderItem(item);
                    });
                    
                    $('#orderModal').modal('show');
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    showToast('error', 'Cannot edit online orders');
                } else {
                    showToast('error', 'Error loading order data');
                }
            }
        });
    }
    
    // Delete Order
    function deleteOrder() {
        if(!deleteOrderId) return;
        
        $.ajax({
            url: '{{ route("orders.destroy", ":id") }}'.replace(':id', deleteOrderId),
            type: 'POST',
            data: {
                '_method': 'DELETE'
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#deleteModal').modal('hide');
                    loadOrders();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', xhr.responseJSON.message || 'Cannot delete order');
                } else {
                    showToast('error', 'Failed to delete order');
                }
            },
            complete: function() {
                deleteOrderId = null;
            }
        });
    }
    
    // Show Toast
    function showToast(type, message) {
        const toast = $('#toast');
        if (!toast.length || !toast[0]) {
            console.error('Toast element not found');
            alert(message);
            return;
        }
        const toastBody = toast.find('.toast-body');
        const toastHeader = toast.find('.toast-header');
        if (toastBody.length) {
            toastBody.text(message);
        }
        if(type === 'success') {
            if (toastHeader.length) {
                toastHeader.html('<i class=\'bx bx-check-circle text-success me-2\'></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
            }
        } else {
            if (toastHeader.length) {
                toastHeader.html('<i class=\'bx bx-x-circle text-danger me-2\'></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
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
            alert(message);
        }
    }
    
    // Reset form when modal is closed
    $('#orderModal').on('hidden.bs.modal', function() {
        isEditMode = false;
        $('#orderForm')[0].reset();
        $('#orderId').val('');
        $('#orderModalLabel').text('Create New Order');
        $('#orderItemsContainer').empty();
        itemCounter = 0;
        $('#subtotal, #taxAmount, #shippingAmount, #discountAmount, #totalAmount').val(0);
        $('#customerInfoCard').slideUp(300);
        $('#customerInfoContent').html('');
    });
});
</script>
@endpush

@endsection
