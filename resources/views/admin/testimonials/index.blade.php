@extends('layouts.admin')

@section('title', 'Testimonials Management')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .sortable-ghost {
        opacity: 0.4;
        background: #f8f9fa;
    }
    .sortable-drag {
        opacity: 0.8;
    }
    .drag-handle {
        cursor: grab;
        color: #6c757d;
        font-size: 1.2rem;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .table tbody tr {
        cursor: move;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .image-preview {
        max-width: 80px;
        max-height: 80px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    /* Select2 styling for modal */
    .modal .select2-container {
        width: 100% !important;
        z-index: 1055;
    }
    .modal .select2-dropdown {
        z-index: 1056 !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Testimonials Management</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" id="addTestimonialBtn">
                    <i class="fas fa-plus"></i> Add Testimonial
                </button>
            </div>
        </div>

        <!-- Testimonials Table -->
        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-12">
                        <input type="text" placeholder="Search testimonials..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="testimonialsTable">
                    <thead>
                        <tr>
                            <th width="50"><i class="bx bx-menu drag-handle"></i></th>
                            <th width="100">Image</th>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="testimonialsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
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

<!-- Add/Edit Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialModalLabel">Add Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="testimonialForm" enctype="multipart/form-data" method="POST">
                    <input type="hidden" id="testimonialId" name="id">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="testimonialName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="testimonialName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="testimonialTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="testimonialTitle" name="title" placeholder="e.g., CEO, Manager, etc.">
                            <div class="invalid-feedback" id="titleError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="testimonialDescription" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="testimonialDescription" name="description" rows="4" required placeholder="Enter testimonial description"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="testimonialImage" class="form-label">Image</label>
                            <input type="file" class="form-control" id="testimonialImage" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 200x200px, Max size: 2MB</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="image-preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div id="currentImage" class="mt-2" style="display: none;">
                                <img id="currentImg" src="" alt="Current" class="image-preview">
                                <p class="text-muted small mt-1">Current image</p>
                            </div>
                            <div class="invalid-feedback" id="imageError"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTestimonialBtn">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let sortableInstance;
let isEditMode = false;

$(document).ready(function() {
    // Load testimonials on page load
    loadTestimonials();

    // Search functionality
    $('#tableSearch').on('keyup', debounce(function() {
        loadTestimonials();
    }, 500));

    // Add Testimonial Button
    $('#addTestimonialBtn').on('click', function() {
        isEditMode = false;
        $('#testimonialModalLabel').text('Add Testimonial');
        $('#testimonialForm')[0].reset();
        $('#testimonialId').val('');
        $('#imagePreview').hide();
        $('#currentImage').hide();
        $('#testimonialModal').modal('show');
    });

    // Save Testimonial
    $('#saveTestimonialBtn').on('click', function() {
        const form = $('#testimonialForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        
        const url = isEditMode 
            ? '{{ route("testimonials.update", ":id") }}'.replace(':id', $('#testimonialId').val())
            : '{{ route("testimonials.store") }}';

        // Add _method for PUT if editing (Laravel method spoofing)
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#testimonialModal').modal('hide');
                    showAlert('success', response.message);
                    loadTestimonials();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            }
        });
    });

    // Edit Testimonial
    $(document).on('click', '.edit-testimonial', function() {
        const id = $(this).data('id');
        isEditMode = true;
        $('#testimonialModalLabel').text('Edit Testimonial');
        
        $.ajax({
            url: '{{ route("testimonials.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#testimonialId').val(data.id);
                    $('#testimonialName').val(data.name);
                    $('#testimonialTitle').val(data.title);
                    $('#testimonialDescription').val(data.description);
                    
                    if (data.image) {
                        $('#currentImg').attr('src', data.image);
                        $('#currentImage').show();
                    } else {
                        $('#currentImage').hide();
                    }
                    
                    $('#imagePreview').hide();
                    $('#testimonialModal').modal('show');
                }
            }
        });
    });

    // Delete Testimonial
    $(document).on('click', '.delete-testimonial', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete testimonial from "${name}"?`)) {
            $.ajax({
                url: '{{ route("testimonials.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadTestimonials();
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        }
    });

    // Image Preview
    $('#testimonialImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
                $('#currentImage').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Image
    $('#removeImageBtn').on('click', function() {
        $('#testimonialImage').val('');
        $('#imagePreview').hide();
    });

    // Initialize Sortable
    function initializeSortable() {
        const tbody = document.getElementById('testimonialsTableBody');
        if (tbody && typeof Sortable !== 'undefined') {
            sortableInstance = Sortable.create(tbody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    updateSortOrder();
                }
            });
        }
    }

    // Update Sort Order
    function updateSortOrder() {
        const items = [];
        $('#testimonialsTableBody tr').each(function(index) {
            const id = $(this).data('id');
            if (id) {
                items.push({
                    id: id,
                    sort_order: index + 1
                });
            }
        });

        $.ajax({
            url: '{{ route("testimonials.update-sort-order") }}',
            method: 'POST',
            data: {
                items: items
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadTestimonials();
                }
            }
        });
    }

    // Load Testimonials
    function loadTestimonials() {
        const search = $('#tableSearch').val();

        // Show loading state
        $('#testimonialsTableBody').html('<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.ajax({
            url: '{{ route("testimonials.data") }}',
            method: 'GET',
            data: {
                search: search
            },
            success: function(response) {
                if (response.success) {
                    renderTable(response.data);
                    initializeSortable();
                } else {
                    $('#testimonialsTableBody').html('<tr><td colspan="8" class="text-center text-danger">' + (response.message || 'Failed to load testimonials') + '</td></tr>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error loading testimonials';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'Unauthorized. Please login.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Access forbidden.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint not found.';
                }
                $('#testimonialsTableBody').html('<tr><td colspan="8" class="text-center text-danger">' + errorMessage + '</td></tr>');
            }
        });
    }

    // Render Table
    function renderTable(data) {
        if (!data || data.length === 0) {
            $('#testimonialsTableBody').html('<tr><td colspan="8" class="text-center py-5">No testimonials found</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item) {
            const imageHtml = item.image 
                ? `<img src="${item.image}" alt="${item.name || 'Testimonial'}" class="image-preview">`
                : '<span class="text-muted">No image</span>';

            const name = item.name || 'Untitled';
            const title = item.title || 'N/A';
            const description = item.description ? (item.description.length > 50 ? item.description.substring(0, 50) + '...' : item.description) : 'N/A';
            const sortOrder = item.sort_order || 0;
            const createdAt = item.created_at || 'N/A';

            html += `
                <tr data-id="${item.id}">
                    <td><i class="bx bx-menu drag-handle"></i></td>
                    <td>${imageHtml}</td>
                    <td>${name}</td>
                    <td>${title}</td>
                    <td>${description}</td>
                    <td>${sortOrder}</td>
                    <td>${createdAt}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-testimonial" data-id="${item.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-testimonial" data-id="${item.id}" data-name="${name}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#testimonialsTableBody').html(html);
    }

    // Display Errors
    function displayErrors(errors) {
        $('.invalid-feedback').text('').hide();
        $('.form-control').removeClass('is-invalid');
        
        $.each(errors, function(key, value) {
            $(`#${key}Error`).text(value[0]).show();
            $(`#testimonial${key.charAt(0).toUpperCase() + key.slice(1)}`).addClass('is-invalid');
        });
    }

    // Show Alert
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#modalAlertContainer').html(alertHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 3000);
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
});
</script>
@endpush

