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
                            All <span class="badge bg-secondary ms-1" id="count-all">{{ $orderCounts['all'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="pending" type="button" role="tab">
                            Pending <span class="badge bg-warning ms-1" id="count-pending">{{ $orderCounts['pending'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="processing" type="button" role="tab">
                            Processing <span class="badge bg-info ms-1" id="count-processing">{{ $orderCounts['processing'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="shipped" type="button" role="tab">
                            Shipped <span class="badge bg-primary ms-1" id="count-shipped">{{ $orderCounts['shipped'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="delivered" type="button" role="tab">
                            Delivered <span class="badge bg-success ms-1" id="count-delivered">{{ $orderCounts['delivered'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="cancelled" type="button" role="tab">
                            Cancelled <span class="badge bg-danger ms-1" id="count-cancelled">{{ $orderCounts['cancelled'] ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="refunded" type="button" role="tab">
                            Refunded <span class="badge bg-secondary ms-1" id="count-refunded">{{ $orderCounts['refunded'] ?? 0 }}</span>
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

                    <!-- Shipping Address Section -->
                    <div class="mb-4" id="shippingSection">
                        <div class="card border-info">
                            <div class="card-header bg-info bg-opacity-10">
                                <h6 class="mb-0"><i class='bx bx-package me-2'></i>Shipping Address & Method</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="shippingPincode" name="shipping_pincode" placeholder="Enter pincode" maxlength="6">
                                        <small class="text-muted">Required for shipping calculation</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" class="form-control" id="shippingState" name="shipping_state" placeholder="Enter state">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" id="shippingCity" name="shipping_city" placeholder="Enter city">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Shipping Method</label>
                                        <select class="form-select" id="shippingMethod" name="shipping_method_id">
                                            <option value="">Select Shipping Method</option>
                                        </select>
                                        <small class="text-muted">Select method to calculate shipping cost</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Shipping Zone</label>
                                        <input type="text" class="form-control" id="shippingZone" readonly placeholder="Will be detected from address">
                                        <input type="hidden" id="shippingZoneId" name="shipping_zone_id">
                                    </div>
                                </div>
                                <div class="mt-3" id="shippingMethodsContainer" style="display: none;">
                                    <div class="alert alert-info mb-0">
                                        <small><i class='bx bx-info-circle me-1'></i>Select a shipping method above to calculate shipping cost</small>
                                    </div>
                                </div>
                            </div>
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
                                    <div class="input-group">
                                        <input type="number" class="form-control numeric-input" id="shippingAmount" name="shipping_amount" value="0" step="0.01" min="0" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="calculateShippingBtn" title="Calculate Shipping">
                                            <i class='bx bx-calculator'></i> Calculate
                                        </button>
                                    </div>
                                    <small class="text-muted">Enter address and select method to calculate</small>
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
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #dee2e6;
    align-items: start;
}
.invoice-company {
    display: flex;
    flex-direction: column;
}
.invoice-company-logo-img {
    margin-bottom: 1rem;
}
.invoice-company-logo-img img {
    max-height: 70px;
    max-width: 200px;
    object-fit: contain;
}
.invoice-company-logo {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.75rem;
}
.invoice-company-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.75rem;
}
.invoice-company-details {
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.8;
}
.invoice-company-details div {
    margin-bottom: 0.25rem;
}
.invoice-shipping {
    display: flex;
    flex-direction: column;
}
 
.invoice-shipping-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}
.invoice-shipping-details {
    font-size: 0.95rem;
    color: #212529;
    line-height: 1.8;
}
 
.invoice-shipping-label {
    font-weight: 600;
    color: #495057;
    display: inline-block;
    min-width: 140px;
}
.invoice-shipping-value {
    color: #212529;
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
    
    // Load order counts on page load
    loadOrderCounts();
    
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
                    
                    // Update order counts
                    loadOrderCounts();
                    
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
                        
                        let isEditable = order.source !== 'frontend' && order.source !== 'online'; 
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
                                    ${order.source === 'frontend' || order.source === 'online' 
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
    
    // Load Order Counts
    function loadOrderCounts() {
        $.ajax({
            url: '{{ route("orders.counts") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.counts) {
                    $('#count-all').text(response.counts.all || 0);
                    $('#count-pending').text(response.counts.pending || 0);
                    $('#count-processing').text(response.counts.processing || 0);
                    $('#count-shipped').text(response.counts.shipped || 0);
                    $('#count-delivered').text(response.counts.delivered || 0);
                    $('#count-cancelled').text(response.counts.cancelled || 0);
                    $('#count-refunded').text(response.counts.refunded || 0);
                }
            },
            error: function(xhr) {
                console.error('Error loading order counts:', xhr);
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
                    let companySettings = order.company_settings || {};
                    
                    // Build address components separately
                    let addressParts = [];
                    if (shippingAddress.address_line1) addressParts.push(shippingAddress.address_line1);
                    if (shippingAddress.address_line2) addressParts.push(shippingAddress.address_line2);
                    if (shippingAddress.landmark) addressParts.push(shippingAddress.landmark);
                    let address = addressParts.length > 0 ? addressParts.join(', ') : '-';
                    
                    // City with state
                    let cityStateParts = [];
                    if (shippingAddress.city) cityStateParts.push(shippingAddress.city);
                    if (shippingAddress.state) cityStateParts.push(shippingAddress.state);
                    let cityState = cityStateParts.length > 0 ? cityStateParts.join(', ') : '-';
                    
                    // Pin Code with Country
                    let pinCodeCountryParts = [];
                    if (shippingAddress.pincode) pinCodeCountryParts.push(shippingAddress.pincode);
                    if (shippingAddress.country) pinCodeCountryParts.push(shippingAddress.country);
                    let pinCodeCountry = pinCodeCountryParts.length > 0 ? pinCodeCountryParts.join(', ') : '-';
                    
                    // Format status badge color
                    const statusColors = {
                        'pending': '#ffc107',
                        'processing': '#0dcaf0',
                        'shipped': '#198754',
                        'delivered': '#198754',
                        'cancelled': '#dc3545',
                        'refunded': '#6c757d'
                    };
                    const statusColor = statusColors[order.status.toLowerCase()] || '#6c757d';
                    const statusText = order.status.charAt(0).toUpperCase() + order.status.slice(1);
                    
                    let invoiceHtml = `
                        <div class="invoice-container">
                            <div class="invoice-header">
                                <!-- Left Column: Company Details -->
                                <div class="invoice-company">
                                    ${companySettings.company_logo ? 
                                        `<div class="invoice-company-logo-img">
                                            <img src="${companySettings.company_logo}" alt="Company Logo">
                                        </div>` : 
                                        `<div class="invoice-company-logo">${companySettings.company_logo_text || 'Lomoofy'}</div>`
                                    }
                                    <div class="invoice-company-name">${companySettings.company_name || 'Lomoof'}</div>
                                    <div class="invoice-company-details">
                                        ${companySettings.phone ? `<div>${companySettings.phone}</div>` : ''}
                                        ${companySettings.email ? `<div>${companySettings.email}</div>` : ''}
                                        ${companySettings.address ? `<div>${companySettings.address}</div>` : ''}
                                    </div>
                                </div>
                                
                                <!-- Right Column: Invoice Title and Shipping Details -->
                                <div class="invoice-shipping"> 
                                    <div class="invoice-shipping-details">
                                        <div class="invoice-shipping-section-title">Shipping Details</div>
                                        <div>
                                            <span class="invoice-shipping-label">Order Id :</span>
                                            <span class="invoice-shipping-value">${order.order_number}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Name :</span>
                                            <span class="invoice-shipping-value">${order.customer.full_name}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Phone :</span>
                                            <span class="invoice-shipping-value">${order.customer.phone || '-'}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Email :</span>
                                            <span class="invoice-shipping-value">${order.customer.email}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Address :</span>
                                            <span class="invoice-shipping-value">${address}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">City with state:</span>
                                            <span class="invoice-shipping-value">${cityState}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Pin Code :</span>
                                            <span class="invoice-shipping-value">${pinCodeCountry}</span>
                                        </div>
                                        <div>
                                            <span class="invoice-shipping-label">Payment Method :</span>
                                            <span class="invoice-shipping-value">${order.payment_method || 'Offline'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <table class="invoice-items-table">
                                <thead>
                                    <tr>
                                        <th>Item name</th>
                                        <th>Variant</th>
                                        <th>Unit Price</th>
                                        <th>GST</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    // Calculate total GST and track exclusive GST amount
                    let totalGstAmount = 0;
                    let exclusiveGstAmount = 0; // GST amount that needs to be added to grand total
                    let gstBreakdown = {};
                    
                    order.items.forEach(function(item) {
                        let itemGstAmount = 0;
                        // Handle GST type: true/1 = Inclusive, false/0 = Exclusive, default to Inclusive
                        let gstType = item.gst_type !== undefined && item.gst_type !== null 
                            ? (item.gst_type === true || item.gst_type === 1 || item.gst_type === '1' || item.gst_type === 'true') 
                            : true;
                        let gstPercentage = item.gst_percentage !== null && item.gst_percentage !== undefined 
                            ? parseFloat(item.gst_percentage) 
                            : 0;
                        let itemTotal = parseFloat(item.total_price) || 0;
                        
                        // Format GST display
                        let gstDisplay = '-';
                        if (gstPercentage > 0) {
                            let gstTypeText = gstType ? 'Inclusive' : 'Exclusive';
                            gstDisplay = `${gstPercentage}% (${gstTypeText})`;
                        }
                        
                        // Calculate GST amount if percentage is set
                        if (gstPercentage > 0 && itemTotal > 0) {
                            if (gstType) {
                                // Inclusive of GST: GST = (total * percentage) / (100 + percentage)
                                // Example: If total is ₹118 with 18% inclusive GST, GST = (118 * 18) / 118 = 18
                                itemGstAmount = (itemTotal * gstPercentage) / (100 + gstPercentage);
                            } else {
                                // Exclusive of GST: GST = (total * percentage) / 100
                                // Example: If total is ₹100 with 18% exclusive GST, GST = (100 * 18) / 100 = 18
                                itemGstAmount = (itemTotal * gstPercentage) / 100;
                                // For exclusive GST, we need to add this to the grand total
                                exclusiveGstAmount += itemGstAmount;
                            }
                            
                            totalGstAmount += itemGstAmount;
                            
                            // Group by GST percentage and type for breakdown
                            let gstTypeText = gstType ? 'Inclusive' : 'Exclusive';
                            let gstKey = `${gstPercentage}% (${gstTypeText})`;
                            if (!gstBreakdown[gstKey]) {
                                gstBreakdown[gstKey] = 0;
                            }
                            gstBreakdown[gstKey] += itemGstAmount;
                        }
                        
                        invoiceHtml += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.variant_name || '-'}</td>
                                <td>₹ ${parseFloat(item.unit_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${gstDisplay}</td>
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
                                    </tr>`;
                    
                    // Show GST breakdown if there is any GST
                    if (totalGstAmount > 0) {
                        // Show individual GST breakdowns if multiple rates
                        let gstKeys = Object.keys(gstBreakdown);
                        if (gstKeys.length > 1) {
                            gstKeys.forEach(function(key) {
                                invoiceHtml += `
                                    <tr>
                                        <td>GST ${key}</td>
                                        <td>₹ ${parseFloat(gstBreakdown[key]).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                            });
                        }
                        invoiceHtml += `
                                    <tr>
                                        <td>Total GST</td>
                                        <td>₹ ${parseFloat(totalGstAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                    }
                    
                    invoiceHtml += `
                                    <tr>
                                        <td>Delivery Charges</td>
                                        <td>₹ ${parseFloat(order.shipping_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                    
                    // Calculate correct Grand Total
                    // For Inclusive GST: Subtotal already includes GST, so: Subtotal - Discount + Shipping
                    // For Exclusive GST: Subtotal does NOT include GST, so: Subtotal - Discount + Exclusive GST + Shipping
                    let subtotal = parseFloat(order.subtotal) || 0;
                    let discount = parseFloat(order.discount_amount || 0);
                    let shipping = parseFloat(order.shipping_amount || 0);
                    let calculatedGrandTotal = subtotal - discount + exclusiveGstAmount + shipping;
                    
                    invoiceHtml += `
                                    <tr class="grand-total">
                                        <td>Grand Total</td>
                                        <td>₹ ${calculatedGrandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
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
        
        let productSelectHtml = '<option value="">Select Product</option>';
        if (productsLoaded && products && products.length > 0) {
            products.forEach(function(product) {
                productSelectHtml += `<option value="${product.id}" data-type="${product.type}">${product.name} (${product.sku})</option>`;
            });
        }
        
        let itemHtml = `
            <div class="order-item-row" data-item-id="${itemId}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" data-item-id="${itemId}" required>
                            ${productSelectHtml}
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
        
        // Get references to selects
        const productSelect = $(`.product-select[data-item-id="${itemId}"]`);
        const variantSelect = $(`.variant-select[data-item-id="${itemId}"]`);
        const warehouseSelect = $(`.warehouse-select[data-item-id="${itemId}"]`);
        const unitPriceInput = $(`.item-unit-price[data-item-id="${itemId}"]`);
        
        // Populate warehouse dropdown
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
        productSelect.on('change', function() {
            const productId = $(this).val();
            
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
        
        // If editing and itemData exists, set the product and variant
        if (itemData && itemData.product_id) {
            // Set the product value
            productSelect.val(itemData.product_id);
            
            // Populate variants directly
            const productId = itemData.product_id;
            const product = products.find(p => parseInt(p.id) === parseInt(productId));
            
            if (product) {
                variantSelect.empty().append('<option value="">Select Variant</option>');
                
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(function(variant) {
                        const selected = itemData.product_variant_id && parseInt(itemData.product_variant_id) === parseInt(variant.id) ? 'selected' : '';
                        variantSelect.append(`<option value="${variant.id}" data-price="${variant.price || 0}" ${selected}>${variant.name} (${variant.sku})</option>`);
                    });
                    
                    // If variant is selected, update price and trigger change for stock check
                    if (itemData.product_variant_id) {
                        const selectedVariant = product.variants.find(v => parseInt(v.id) === parseInt(itemData.product_variant_id));
                        if (selectedVariant) {
                            unitPriceInput.val(selectedVariant.price || 0);
                            // Trigger change to check stock availability
                            variantSelect.trigger('change');
                        }
                    }
                } else {
                    // No variants, use product price
                    unitPriceInput.val(product.price || 0);
                    // Check stock availability for product without variant
                    checkItemStockAvailability(itemId);
                }
            } else {
                // Product not found in products array, trigger change to let handler deal with it
                productSelect.trigger('change');
                // Try to set variant after a delay
                if (itemData.product_variant_id) {
                    setTimeout(function() {
                        variantSelect.val(itemData.product_variant_id);
                        variantSelect.trigger('change');
                    }, 100);
                }
            }
        }
        
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
    
    // Shipping calculation
    function calculateShipping() {
        const pincode = $('#shippingPincode').val();
        const state = $('#shippingState').val();
        const city = $('#shippingCity').val();
        const shippingMethodId = $('#shippingMethod').val();
        
        if (!pincode && !state && !city) {
            showToast('error', 'Please enter shipping address (pincode, state, or city )');
            return;
        }
        
        // Collect order items for weight calculation
        const items = [];
        $('.order-item-row').each(function() {
            const productId = $(this).find('.product-select').val();
            const variantId = $(this).find('.variant-select').val();
            const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
            
            if (productId && quantity > 0) {
                items.push({
                    product_id: productId,
                    variant_id: variantId || null,
                    quantity: quantity
                });
            }
        });
        
        if (items.length === 0) {
            showToast('error', 'Please add items to the order first');
            return;
        }
        
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        
        // Show loading
        $('#calculateShippingBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '{{ route("orders.calculate-shipping") }}',
            type: 'POST',
            data: {
                pincode: pincode,
                state: state,
                city: city,
                shipping_method_id: shippingMethodId,
                items: items,
                order_total: subtotal,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.zone) {
                        $('#shippingZone').val(response.zone.name);
                        $('#shippingZoneId').val(response.zone.id);
                    }
                    
                    if (response.methods && response.methods.length > 0) {
                        // Populate shipping methods dropdown
                        let methodsHtml = '<option value="">Select Shipping Method</option>';
                        response.methods.forEach(function(method) {
                            methodsHtml += `<option value="${method.id}" data-cost="${method.cost}" data-rate-id="${method.rate_id}">${method.name} - ₹${method.cost.toFixed(2)} (${method.estimated_days})</option>`;
                        });
                        $('#shippingMethod').html(methodsHtml);
                        $('#shippingMethodsContainer').show();
                        
                        // Auto-select first method if none selected
                        if (!shippingMethodId && response.methods.length > 0) {
                            $('#shippingMethod').val(response.methods[0].id).trigger('change');
                        } else if (shippingMethodId) {
                            // Keep selected method if it exists
                            $('#shippingMethod').val(shippingMethodId).trigger('change');
                        }
                    } else {
                        $('#shippingMethod').html('<option value="">No shipping methods available for this address</option>');
                        $('#shippingAmount').val(0);
                        showToast('error', 'No shipping methods available for this address');
                    }
                } else {
                    showToast('error', response.message || 'Error calculating shipping');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error calculating shipping';
                showToast('error', errorMsg);
            },
            complete: function() {
                $('#calculateShippingBtn').prop('disabled', false).html('<i class=\'bx bx-calculator\'></i> Calculate');
            }
        });
    }
    
    // Shipping method selection handler
    $('#shippingMethod').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const cost = parseFloat(selectedOption.data('cost')) || 0;
        const rateId = selectedOption.data('rate-id') || null;
        $('#shippingAmount').val(cost.toFixed(2));
        $('#shippingRateId').val(rateId || '');
        calculateTotal();
        console.log(selectedOption);
        console.log(cost);
        console.log(rateId);
    });
    
    // Calculate shipping button
    $('#calculateShippingBtn').on('click', function() {
        calculateShipping();
    });
    
    // Auto-calculate when pincode changes
    $('#shippingPincode').on('blur', function() {
        if ($(this).val().length >= 6) {
            calculateShipping();
        }
    });
    
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
                    let addressesHtml = '';
                    if (customer.addresses && customer.addresses.length > 0) {
                        customer.addresses.forEach(function(address) {
                            const isDefault = address.is_default ? ' (Default)' : '';
                            addressesHtml += `
                                <option value="${address.id}" 
                                        data-pincode="${address.pincode || ''}" 
                                        data-state="${address.state || ''}" 
                                        data-city="${address.city || ''}"
                                        ${address.is_default ? 'selected' : ''}>
                                    ${address.address_type} - ${address.pincode || 'No pincode'}${isDefault}
                                </option>
                            `;
                        });
                    }
                    
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
                            ${customer.addresses && customer.addresses.length > 0 ? `
                            <div class="col-md-12">
                                <label class="form-label">Select Shipping Address</label>
                                <select class="form-select" id="customerAddressSelect">
                                    <option value="">Use address below or enter manually</option>
                                    ${addressesHtml}
                                </select>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    $('#customerInfoContent').html(html);
                    
                    // Auto-fill shipping address when customer address is selected
                    if (customer.addresses && customer.addresses.length > 0) {
                        const defaultAddress = customer.addresses.find(a => a.is_default) || customer.addresses[0];
                        if (defaultAddress) {
                            $('#shippingPincode').val(defaultAddress.pincode || '');
                            $('#shippingState').val(defaultAddress.state || '');
                            $('#shippingCity').val(defaultAddress.city || '');
                        }
                        
                        $('#customerAddressSelect').on('change', function() {
                            const addressId = $(this).val();
                            if (addressId) {
                                const selectedAddress = customer.addresses.find(a => a.id == addressId);
                                if (selectedAddress) {
                                    $('#shippingPincode').val(selectedAddress.pincode || '');
                                    $('#shippingState').val(selectedAddress.state || '');
                                    $('#shippingCity').val(selectedAddress.city || '');
                                    // Trigger shipping calculation
                                    if (selectedAddress.pincode) {
                                        calculateShipping();
                                    }
                                }
                            }
                        });
                    }
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
            shipping_zone_id: $('#shippingZoneId').val() || null,
            shipping_method_id: $('#shippingMethod').val() || null,
            shipping_rate_id: $('#shippingRateId').val() || null,
            discount_amount: $('#discountAmount').val(),
            total_amount: $('#totalAmount').val(),
            notes: $('#orderNotes').val(),
            shipping_address: {
                pincode: $('#shippingPincode').val(),
                state: $('#shippingState').val(),
                city: $('#shippingCity').val()
            },
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
                    loadOrderCounts(); // Update counts after creating/updating order
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
                    loadOrderCounts(); // Update counts after deleting order
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
        $('#shippingPincode, #shippingState, #shippingCity').val('');
        $('#shippingZone').val('');
        $('#shippingZoneId').val('');
        $('#shippingMethod').html('<option value="">Select Shipping Method</option>');
        $('#shippingMethodsContainer').hide();
        $('#customerInfoCard').slideUp(300);
        $('#customerInfoContent').html('');
    });
});
</script>
@endpush

@endsection
