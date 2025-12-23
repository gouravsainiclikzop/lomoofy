@extends('layouts.admin')

@section('title', 'Featured Category Style Management')

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
                        <li class="breadcrumb-item active" aria-current="page">Featured Category Style</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Featured Category Style Management</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" id="addCollectionBtn">
                    <i class="fas fa-plus"></i> Add Featured Category Style
                </button>
            </div>
        </div>

        <!-- Collections Table -->
        <div class="card">
            <div class="p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <input type="text" placeholder="Search featured category styles..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="collectionsTable">
                    <thead>
                        <tr>
                            <th width="50"><i class="bx bx-menu drag-handle"></i></th>
                            <th width="100">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="collectionsTableBody">
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

<!-- Add/Edit Collection Modal -->
<div class="modal fade" id="collectionModal" tabindex="-1" aria-labelledby="collectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectionModalLabel">Add Featured Category Style</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="collectionForm" enctype="multipart/form-data" method="POST">
                    <input type="hidden" id="collectionId" name="id">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="collectionCategory" class="form-label">Category</label>
                            <select class="form-select" id="collectionCategory" name="category_id" style="width: 100%;">
                                <option value="">Select Category (Optional)</option>
                            </select>
                            <div class="invalid-feedback" id="categoryError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="collectionTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="collectionTitle" name="title" required>
                            <div class="invalid-feedback" id="titleError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="collectionImage" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="collectionImage" name="featured_image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 800x600px, Max size: 2MB</small>
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
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="collectionIsActive" name="is_active" checked>
                                <label class="form-check-label" for="collectionIsActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCollectionBtn">
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
let categorySelect2Initialized = false;
let pendingCategoryData = null; // Store category data for edit mode

// Function to initialize Select2 for category
function initializeCategorySelect2() {
    const select = $('#collectionCategory');
    
    // Destroy existing Select2 if initialized
    if (select.hasClass('select2-hidden-accessible')) {
        select.select2('destroy');
    }
    
    // Initialize Select2
    select.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select Category (Optional)',
        allowClear: true,
        dropdownParent: $('#collectionModal'),
        ajax: {
            url: '{{ route("categories.data") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    status: 1
                };
            },
            processResults: function (data) {
                if (data.success && data.data) {
                    return {
                        results: data.data.map(function(category) {
                            return {
                                id: category.id,
                                text: category.full_path_name || category.name
                            };
                        })
                    };
                }
                return { results: [] };
            },
            cache: true
        }
    });
    
    categorySelect2Initialized = true;
}

$(document).ready(function() {
    // Load collections on page load
    loadCollections();

    // Search functionality
    $('#tableSearch').on('keyup', debounce(function() {
        loadCollections();
    }, 500));

    // Status filter
    $('#statusFilter').on('change', function() {
        loadCollections();
    });

    // Initialize Select2 when modal is opened
    $('#collectionModal').on('shown.bs.modal', function() {
        initializeCategorySelect2();
        
        // Set pending category data if in edit mode
        if (pendingCategoryData && pendingCategoryData.id) {
            setTimeout(function() {
                const categorySelect = $('#collectionCategory');
                if (categorySelect.hasClass('select2-hidden-accessible')) {
                    // Create option if it doesn't exist
                    if (categorySelect.find('option[value="' + pendingCategoryData.id + '"]').length === 0) {
                        const option = new Option(pendingCategoryData.name, pendingCategoryData.id, true, true);
                        categorySelect.append(option).trigger('change');
                    }
                    // Set the value using Select2 API
                    categorySelect.val(pendingCategoryData.id).trigger('change');
                }
            }, 100);
        }
    });
    
    // Clean up Select2 when modal is hidden
    $('#collectionModal').on('hidden.bs.modal', function() {
        const select = $('#collectionCategory');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
            categorySelect2Initialized = false;
        }
        // Clear pending category data
        pendingCategoryData = null;
    });
    
    // Add Collection Button
    $('#addCollectionBtn').on('click', function() {
        isEditMode = false;
        pendingCategoryData = null; // Clear any pending category data
        $('#collectionModalLabel').text('Add Featured Category Style');
        $('#collectionForm')[0].reset();
        $('#collectionId').val('');
        $('#imagePreview').hide();
        $('#currentImage').hide();
        $('#collectionIsActive').prop('checked', true);
        
        // Clear category select
        $('#collectionCategory').empty();
        
        $('#collectionModal').modal('show');
        
        // Clear category selection after modal is shown
        setTimeout(function() {
            $('#collectionCategory').val(null).trigger('change');
        }, 300);
    });

    // Save Collection
    $('#saveCollectionBtn').on('click', function() {
        const form = $('#collectionForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        
        // Handle checkbox value properly (convert to 1 or 0)
        if ($('#collectionIsActive').is(':checked')) {
            formData.set('is_active', '1');
        } else {
            formData.set('is_active', '0');
        }
        
        const url = isEditMode 
            ? '{{ route("featured-category-style.update", ":id") }}'.replace(':id', $('#collectionId').val())
            : '{{ route("featured-category-style.store") }}';
        const method = 'POST';

        // Add _method for PUT if editing (Laravel method spoofing)
        if (isEditMode) {
            formData.append('_method', 'PUT');
            console.log('Edit mode: Adding _method=PUT to form data');
        }

        console.log('Sending request:', { url: url, method: 'POST', isEditMode: isEditMode });

        $.ajax({
            url: url,
            type: 'POST', // Always use POST, Laravel will handle method spoofing via _method parameter
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#collectionModal').modal('hide');
                    showAlert('success', response.message);
                    loadCollections();
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

    // Edit Collection
    $(document).on('click', '.edit-collection', function() {
        const id = $(this).data('id');
        isEditMode = true;
        $('#collectionModalLabel').text('Edit Featured Category Style');
        
        $.ajax({
            url: '{{ route("featured-category-style.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#collectionId').val(data.id);
                    $('#collectionTitle').val(data.title);
                    $('#collectionIsActive').prop('checked', data.is_active);
                    
                    // Store category data for later use (after Select2 initializes)
                    pendingCategoryData = {
                        id: data.category_id,
                        name: data.category_full_path_name || data.category_name || null
                    };
                    
                    if (data.featured_image) {
                        $('#currentImg').attr('src', data.featured_image);
                        $('#currentImage').show();
                    } else {
                        $('#currentImage').hide();
                    }
                    
                    $('#imagePreview').hide();
                    
                    // Clear category select before opening modal
                    const categorySelect = $('#collectionCategory');
                    categorySelect.empty();
                    
                    // Open modal - category will be set in shown.bs.modal handler
                    $('#collectionModal').modal('show');
                }
            }
        });
    });

    // Delete Collection
    $(document).on('click', '.delete-collection', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        
        if (confirm(`Are you sure you want to delete "${title}"?`)) {
            $.ajax({
                url: '{{ route("featured-category-style.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadCollections();
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        }
    });

    // Image Preview
    $('#collectionImage').on('change', function(e) {
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
        $('#collectionImage').val('');
        $('#imagePreview').hide();
    });

    // Initialize Sortable
    function initializeSortable() {
        const tbody = document.getElementById('collectionsTableBody');
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
        $('#collectionsTableBody tr').each(function(index) {
            const id = $(this).data('id');
            if (id) {
                items.push({
                    id: id,
                    sort_order: index + 1
                });
            }
        });

        $.ajax({
            url: '{{ route("featured-category-style.update-sort-order") }}',
            method: 'POST',
            data: {
                items: items
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadCollections();
                }
            }
        });
    }

    // Load Collections
    function loadCollections() {
        const search = $('#tableSearch').val();
        const status = $('#statusFilter').val();

        // Show loading state
        $('#collectionsTableBody').html('<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.ajax({
            url: '{{ route("featured-category-style.data") }}',
            method: 'GET',
            data: {
                search: search,
                status: status
            },
            success: function(response) {
                console.log('Collections response:', response);
                if (response.success) {
                    renderTable(response.data);
                    initializeSortable();
                } else {
                    $('#collectionsTableBody').html('<tr><td colspan="8" class="text-center text-danger">' + (response.message || 'Failed to load collections') + '</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Error loading collections:', xhr);
                let errorMessage = 'Error loading collections';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'Unauthorized. Please login.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Access forbidden.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint not found.';
                }
                $('#collectionsTableBody').html('<tr><td colspan="8" class="text-center text-danger">' + errorMessage + '</td></tr>');
            }
        });
    }

    // Render Table
    function renderTable(data) {
        console.log('Rendering table with data:', data);
        
        if (!data || data.length === 0) {
                    $('#collectionsTableBody').html('<tr><td colspan="8" class="text-center py-5">No featured category styles found</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item) {
            console.log('Processing item:', item);
            
            const statusBadge = item.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-secondary">Inactive</span>';
            
            const imageHtml = item.featured_image 
                ? `<img src="${item.featured_image}" alt="${item.title || 'Featured Category Style'}" class="image-preview">`
                : '<span class="text-muted">No image</span>';

            const title = item.title || 'Untitled';
            const categoryName = item.category_name || 'N/A';
            const sortOrder = item.sort_order || 0;
            const createdAt = item.created_at || 'N/A';

            html += `
                <tr data-id="${item.id}">
                    <td><i class="bx bx-menu drag-handle"></i></td>
                    <td>${imageHtml}</td>
                    <td>${title}</td>
                    <td>${categoryName}</td>
                    <td>${statusBadge}</td>
                    <td>${sortOrder}</td>
                    <td>${createdAt}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-collection" data-id="${item.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-collection" data-id="${item.id}" data-title="${title}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#collectionsTableBody').html(html);
        console.log('Table rendered with', data.length, 'items');
    }

    // Display Errors
    function displayErrors(errors) {
        $('.invalid-feedback').text('').hide();
        $('.form-control, .form-select').removeClass('is-invalid');
        
        $.each(errors, function(key, value) {
            const field = key.replace('_', '');
            $(`#${field}Error`).text(value[0]).show();
            $(`#collection${field.charAt(0).toUpperCase() + field.slice(1)}`).addClass('is-invalid');
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

