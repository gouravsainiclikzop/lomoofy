@extends('layouts.admin')

@section('title', 'Cart Management')

@push('styles')
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .cart-item-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

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
                                <li class="breadcrumb-item active" aria-current="page">Carts</li>
                            </ol>
                        </nav>
                        <h1 class="h3 m-0">Cart Management</h1>
                        <p class="text-muted mb-0">View and manage customer shopping carts</p>
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
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
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

                <!-- Carts Table -->
                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="cartsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Session ID</th>
                                        <th>Items</th>
                                        <th>Subtotal</th>
                                        <th>Discount</th>
                                        <th>Tax</th>
                                        <th>Shipping</th>
                                        <th>Total</th>
                                        <th>Coupon</th>
                                        <th>Status</th>
                                        <th>Expires At</th>
                                        <th>Created</th>
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

<!-- Cart Details Modal -->
<div class="modal fade" id="cartDetailsModal" tabindex="-1" aria-labelledby="cartDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartDetailsModalLabel">Cart Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cartDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    let cartsTable;

    $(document).ready(function() {
        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        initializeDataTable();

        // Filter change
        $('#filterStatus').on('change', function() {
            cartsTable.ajax.reload();
        });

        // View cart details
        $(document).on('click', '.view-cart-btn', function() {
            const cartId = $(this).data('id');
            viewCartDetails(cartId);
        });

        // Delete cart
        $(document).on('click', '.delete-cart-btn', function() {
            const cartId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will release all reserved stock and cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteCart(cartId);
                }
            });
        });
    });

    function initializeDataTable() {
        cartsTable = $('#cartsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("carts.data") }}',
                type: 'GET',
                data: function(d) {
                    d.status = $('#filterStatus').val();
                },
                error: function(xhr, error, thrown) {
                    console.error("DataTables AJAX Error:", xhr.responseText);
                    showToast('Error', 'Failed to load carts data. Please try again later.', 'danger');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { 
                    data: 'customer_name', 
                    name: 'customer_name',
                    render: function(data, type, row) {
                        return `${data}<br><small class="text-muted">${row.customer_email}</small>`;
                    }
                },
                { 
                    data: 'session_id', 
                    name: 'session_id',
                    render: function(data) {
                        return data !== 'N/A' ? `<code class="small">${data}</code>` : '<span class="text-muted">N/A</span>';
                    }
                },
                { 
                    data: 'total_items', 
                    name: 'total_items',
                    render: function(data) {
                        return `<span class="badge bg-info">${data} items</span>`;
                    }
                },
                { 
                    data: 'subtotal', 
                    name: 'subtotal',
                    render: function(data) {
                        return '₹' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                },
                { 
                    data: 'discount_amount', 
                    name: 'discount_amount',
                    render: function(data) {
                        return data > 0 ? '<span class="text-success">-₹' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span>' : '₹0.00';
                    }
                },
                { 
                    data: 'tax_amount', 
                    name: 'tax_amount',
                    render: function(data) {
                        return '₹' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                },
                { 
                    data: 'shipping_amount', 
                    name: 'shipping_amount',
                    render: function(data) {
                        return '₹' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                },
                { 
                    data: 'total_amount', 
                    name: 'total_amount',
                    render: function(data) {
                        return '<strong class="text-primary">₹' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong>';
                    }
                },
                { 
                    data: 'coupon_code', 
                    name: 'coupon_code',
                    render: function(data) {
                        return data !== 'N/A' ? `<span class="badge bg-success">${data}</span>` : '<span class="text-muted">None</span>';
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        const statusColors = {
                            'active': 'bg-success',
                            'expired': 'bg-secondary'
                        };
                        const color = statusColors[data] || 'bg-secondary';
                        return `<span class="badge ${color} status-badge">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                { 
                    data: 'expires_at', 
                    name: 'expires_at',
                    render: function(data) {
                        return data !== 'N/A' ? moment(data).format('MMM D, YYYY h:mm A') : '<span class="text-muted">N/A</span>';
                    }
                },
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    render: function(data) {
                        return moment(data).format('MMM D, YYYY h:mm A');
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
                                <button class="btn btn-sm btn-outline-info view-cart-btn" data-id="${data}" title="View Details">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-cart-btn" data-id="${data}" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[12, 'desc']], // Order by Created column by default
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: 'No carts found.',
                zeroRecords: 'No matching carts found.'
            }
        });
    }

    function viewCartDetails(cartId) {
        $('#cartDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#cartDetailsModal').modal('show');

        $.ajax({
            url: `{{ url('carts') }}/${cartId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const cart = response.data;
                    let html = `
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Cart Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <strong>Cart ID:</strong> ${cart.id}
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <strong>Status:</strong> <span class="badge ${cart.status === 'active' ? 'bg-success' : 'bg-secondary'}">${cart.status.charAt(0).toUpperCase() + cart.status.slice(1)}</span>
                                            </div>
                                            ${cart.customer ? `
                                                <div class="col-md-6 mb-3">
                                                    <strong>Customer:</strong> ${cart.customer.name}<br>
                                                    <small class="text-muted">${cart.customer.email}</small><br>
                                                    <small class="text-muted">${cart.customer.phone || 'N/A'}</small>
                                                </div>
                                            ` : ''}
                                            <div class="col-md-6 mb-3">
                                                <strong>Session ID:</strong> <code>${cart.session_id || 'N/A'}</code>
                                            </div>
                                            ${cart.coupon ? `
                                                <div class="col-md-6 mb-3">
                                                    <strong>Coupon:</strong> <span class="badge bg-success">${cart.coupon.code}</span><br>
                                                    <small class="text-muted">${cart.coupon.discount_type === 'percentage' ? cart.coupon.discount_value + '%' : '₹' + cart.coupon.discount_value}</small>
                                                </div>
                                            ` : ''}
                                            <div class="col-md-6 mb-3">
                                                <strong>Expires At:</strong> ${cart.expires_at ? moment(cart.expires_at).format('MMM D, YYYY h:mm A') : 'N/A'}
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <strong>Created At:</strong> ${moment(cart.created_at).format('MMM D, YYYY h:mm A')}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Cart Items (${cart.items.length})</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Image</th>
                                                        <th>Product</th>
                                                        <th>Variant</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total</th>
                                                        <th>Reserved Stock</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${cart.items.map(item => `
                                                        <tr>
                                                            <td><img src="${item.image_url}" alt="${item.product_name}" class="cart-item-image"></td>
                                                            <td>
                                                                <strong>${item.product_name}</strong><br>
                                                                <small class="text-muted">SKU: ${item.product_sku}</small>
                                                            </td>
                                                            <td>${item.variant_name ? `${item.variant_name}<br><small class="text-muted">SKU: ${item.variant_sku}</small>` : '<span class="text-muted">N/A</span>'}</td>
                                                            <td><span class="badge bg-info">${item.quantity}</span></td>
                                                            <td>₹${parseFloat(item.unit_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                            <td><strong>₹${parseFloat(item.total_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                                                            <td><span class="badge bg-warning">${item.reserved_stock}</span></td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span> <span>₹${parseFloat(cart.summary.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                            </li>
                                            <li class="d-flex justify-content-between mb-2">
                                                <span>Discount:</span> <span class="text-success">-₹${parseFloat(cart.summary.discount_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                            </li>
                                            <li class="d-flex justify-content-between mb-2">
                                                <span>Tax:</span> <span>₹${parseFloat(cart.summary.tax_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                            </li>
                                            <li class="d-flex justify-content-between mb-2">
                                                <span>Shipping:</span> <span>₹${parseFloat(cart.summary.shipping_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                            </li>
                                            <li class="d-flex justify-content-between fw-bold pt-2 border-top">
                                                <span>Total:</span> <span class="text-primary">₹${parseFloat(cart.summary.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                            </li>
                                            <li class="d-flex justify-content-between mt-2">
                                                <span>Total Items:</span> <span class="badge bg-info">${cart.summary.total_items}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#cartDetailsContent').html(html);
                } else {
                    $('#cartDetailsContent').html('<div class="alert alert-danger">Failed to load cart details.</div>');
                }
            },
            error: function(xhr) {
                console.error('Error loading cart details:', xhr);
                $('#cartDetailsContent').html('<div class="alert alert-danger">Failed to load cart details.</div>');
                showToast('Error', 'Failed to load cart details.', 'danger');
            }
        });
    }

    function deleteCart(cartId) {
        $.ajax({
            url: `{{ url('carts') }}/${cartId}`,
            type: 'POST',
            data: {
                _method: 'DELETE'
            },
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    cartsTable.ajax.reload();
                } else {
                    showToast('Error', response.message || 'Failed to delete cart.', 'danger');
                }
            },
            error: function(xhr) {
                console.error('Error deleting cart:', xhr);
                showToast('Error', xhr.responseJSON?.message || 'Failed to delete cart.', 'danger');
            }
        });
    }

    function clearFilters() {
        $('#filterStatus').val('');
        cartsTable.ajax.reload();
    }

    // Global toast function
    let currentToast = null;
    function showToast(title, message, type = 'info') {
        try {
            const toastElement = document.getElementById('toast');
            if (!toastElement) {
                console.warn('Toast element not found. Falling back to alert.', { title, message, type });
                alert(`${title}: ${message}`);
                return;
            }

            if (currentToast) {
                currentToast.dispose();
            }

            const toastTitle = toastElement.querySelector('#toastTitle');
            const toastMessage = toastElement.querySelector('#toastMessage');
            const toast = $(toastElement);

            if (toastTitle) toastTitle.textContent = title;
            if (toastMessage) toastMessage.textContent = message;

            toast.removeClass('bg-primary bg-success bg-danger bg-warning text-white');
            if (type === 'success') {
                toast.addClass('bg-success text-white');
            } else if (type === 'danger') {
                toast.addClass('bg-danger text-white');
            } else if (type === 'warning') {
                toast.addClass('bg-warning text-white');
            } else {
                toast.addClass('bg-primary text-white');
            }
            
            currentToast = new bootstrap.Toast(toastElement);
            currentToast.show();
        } catch (e) {
            console.error('Error showing toast:', e);
            alert(`${title}: ${message}`);
        }
    }
</script>
@endpush

