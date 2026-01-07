@extends('layouts.admin')

@section('title', 'Reviews Management')

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
                                <li class="breadcrumb-item active" aria-current="page">Reviews</li>
                            </ol>
                        </nav>
                        <h1 class="h3 m-0">Review Management</h1>
                        <p class="text-muted mb-0">Manage product variant reviews</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Total Reviews</h6>
                                        <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                                    </div>
                                    <div class="text-primary">
                                        <i class='bx bx-star fs-1'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Active Reviews</h6>
                                        <h3 class="mb-0 text-success">{{ $stats['active'] ?? 0 }}</h3>
                                    </div>
                                    <div class="text-success">
                                        <i class='bx bx-check-circle fs-1'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Pending Reviews</h6>
                                        <h3 class="mb-0 text-warning">{{ $stats['inactive'] ?? 0 }}</h3>
                                    </div>
                                    <div class="text-warning">
                                        <i class='bx bx-time fs-1'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <label for="filterRating" class="form-label">Rating</label>
                                <select class="form-select" id="filterRating">
                                    <option value="">All Ratings</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class='bx bx-x'></i> Clear Filters
                                </button>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success flex-fill" onclick="bulkUpdateStatus('active')">
                                        <i class='bx bx-check'></i> Activate Selected
                                    </button>
                                    <button type="button" class="btn btn-warning flex-fill" onclick="bulkUpdateStatus('inactive')">
                                        <i class='bx bx-x'></i> Deactivate Selected
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="bulkDelete()">
                                        <i class='bx bx-trash'></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Table -->
                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="reviewsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="selectAllReviews" class="form-check-input">
                                        </th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end" style="width: 150px;">Actions</th>
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
    .star-rating {
        color: #ffc107;
    }
    .star-rating .far {
        color: #dee2e6;
    }
    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .comment-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
let reviewsTable;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#reviewsTable')) {
        $('#reviewsTable').DataTable().destroy();
    }
    
    reviewsTable = $('#reviewsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("reviews.data") }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.rating = $('#filterRating').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable Error:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading reviews data.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input review-checkbox" value="${data}">`;
                }
            },
            {
                data: 'customer_name',
                render: function(data, type, row) {
                    return `<div>
                        <strong>${data}</strong><br>
                        <small class="text-muted">${row.customer_email}</small>
                    </div>`;
                }
            },
            {
                data: 'product_name',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            {
                data: 'rating',
                render: function(data) {
                    let stars = '';
                    for (let i = 1; i <= 5; i++) {
                        if (i <= data) {
                            stars += '<i class="fas fa-star"></i>';
                        } else {
                            stars += '<i class="far fa-star"></i>';
                        }
                    }
                    return `<div class="star-rating">${stars}</div>`;
                }
            },
            {
                data: 'comment',
                render: function(data) {
                    if (!data) return '<span class="text-muted">No comment</span>';
                    const preview = data.length > 100 ? data.substring(0, 100) + '...' : data;
                    return `<div class="comment-preview" title="${data.replace(/"/g, '&quot;')}">${preview}</div>`;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badgeClass = data === 'active' ? 'bg-success' : 'bg-warning';
                    const text = data === 'active' ? 'Active' : 'Inactive';
                    return `<span class="badge ${badgeClass} badge-status">${text}</span>`;
                }
            },
            {
                data: 'created_at_formatted',
                render: function(data) {
                    return data || '';
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    const statusBtn = row.status === 'active' 
                        ? `<button class="btn btn-sm btn-warning" onclick="updateStatus(${data}, 'inactive')" title="Deactivate">
                            <i class='bx bx-x'></i>
                        </button>`
                        : `<button class="btn btn-sm btn-success" onclick="updateStatus(${data}, 'active')" title="Activate">
                            <i class='bx bx-check'></i>
                        </button>`;
                    
                    return `
                        <div class="d-flex gap-1 justify-content-end">
                            ${statusBtn}
                            <button class="btn btn-sm btn-danger" onclick="deleteReview(${data})" title="Delete">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[6, 'desc']], // Order by created_at (column index 6)
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });
}

function setupEventListeners() {
    // Select all checkbox
    $('#selectAllReviews').on('change', function() {
        $('.review-checkbox').prop('checked', this.checked);
    });
    
    // Filter change
    $('#filterStatus, #filterRating').on('change', function() {
        reviewsTable.ajax.reload();
    });
}

function clearFilters() {
    $('#filterStatus').val('');
    $('#filterRating').val('');
    reviewsTable.ajax.reload();
}

function updateStatus(id, status) {
    if (!confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'deactivate'} this review?`)) {
        return;
    }
    
    $.ajax({
        url: `/reviews/${id}/update-status`,
        method: 'POST',
        data: {
            status: status,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message || 'Review status updated successfully', 'success');
                reviewsTable.ajax.reload();
            } else {
                showToast('Error', response.message || 'Failed to update review status', 'error');
            }
        },
        error: function(xhr) {
            showToast('Error', 'Failed to update review status', 'error');
        }
    });
}

function deleteReview(id) {
    if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: `/reviews/${id}`,
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message || 'Review deleted successfully', 'success');
                reviewsTable.ajax.reload();
            } else {
                showToast('Error', response.message || 'Failed to delete review', 'error');
            }
        },
        error: function(xhr) {
            showToast('Error', 'Failed to delete review', 'error');
        }
    });
}

function bulkUpdateStatus(status) {
    const selectedIds = getSelectedReviewIds();
    if (selectedIds.length === 0) {
        alert('Please select at least one review');
        return;
    }
    
    if (!confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'deactivate'} ${selectedIds.length} review(s)?`)) {
        return;
    }
    
    $.ajax({
        url: '/reviews/bulk-update-status',
        method: 'POST',
        data: {
            ids: selectedIds,
            status: status,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message || 'Reviews updated successfully', 'success');
                reviewsTable.ajax.reload();
                $('#selectAllReviews').prop('checked', false);
            } else {
                showToast('Error', response.message || 'Failed to update reviews', 'error');
            }
        },
        error: function(xhr) {
            showToast('Error', 'Failed to update reviews', 'error');
        }
    });
}

function bulkDelete() {
    const selectedIds = getSelectedReviewIds();
    if (selectedIds.length === 0) {
        alert('Please select at least one review');
        return;
    }
    
    if (!confirm(`Are you sure you want to delete ${selectedIds.length} review(s)? This action cannot be undone.`)) {
        return;
    }
    
    $.ajax({
        url: '/reviews/bulk-delete',
        method: 'POST',
        data: {
            ids: selectedIds,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message || 'Reviews deleted successfully', 'success');
                reviewsTable.ajax.reload();
                $('#selectAllReviews').prop('checked', false);
            } else {
                showToast('Error', response.message || 'Failed to delete reviews', 'error');
            }
        },
        error: function(xhr) {
            showToast('Error', 'Failed to delete reviews', 'error');
        }
    });
}

function getSelectedReviewIds() {
    const ids = [];
    $('.review-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function showToast(title, message, type) {
    const toast = $('#toast');
    $('#toastTitle').text(title);
    $('#toastMessage').text(message);
    
    toast.removeClass('bg-success bg-danger bg-warning');
    if (type === 'success') {
        toast.addClass('bg-success text-white');
    } else if (type === 'error') {
        toast.addClass('bg-danger text-white');
    } else {
        toast.addClass('bg-warning text-dark');
    }
    
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
}
</script>
@endpush

