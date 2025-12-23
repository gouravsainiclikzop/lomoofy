@extends('layouts.admin')

@section('title', 'Home Sliders Management')

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
    /* Toggle Switch */
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
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
                        <li class="breadcrumb-item active" aria-current="page">Home Sliders</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Home Sliders Management</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" id="addSliderBtn">
                    <i class="fas fa-plus"></i> Add Home Slider
                </button>
            </div>
        </div>

        <!-- Sliders Table -->
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
                        <input type="text" placeholder="Search home sliders..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="slidersTable">
                    <thead>
                        <tr>
                            <th width="50"><i class="bx bx-menu drag-handle"></i></th>
                            <th width="100">Image</th>
                            <th>Title</th>
                            <th>Tagline</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slidersTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
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

<!-- Add/Edit Slider Modal -->
<div class="modal fade" id="sliderModal" tabindex="-1" aria-labelledby="sliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sliderModalLabel">Add Home Slider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="sliderForm" enctype="multipart/form-data" method="POST">
                    <input type="hidden" id="sliderId" name="id">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="sliderCategory" class="form-label">Category</label>
                            <select class="form-select" id="sliderCategory" name="category_id" style="width: 100%;">
                                <option value="">Select Category (Optional)</option>
                            </select>
                            <div class="invalid-feedback" id="categoryError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="sliderTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="sliderTitle" name="title" required placeholder="Enter title">
                                <button type="button" class="btn btn-outline-secondary" id="insertBrBtn" title="Insert &lt;br&gt; tag">
                                    <i class="fas fa-code"></i> &lt;br&gt;
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="removeBrBtn" title="Remove all &lt;br&gt; tags">
                                    <i class="fas fa-eraser"></i> Reset
                                </button>
                            </div>
                            <small class="form-text text-muted">Click &lt;br&gt; button to insert line break, or Reset to remove all &lt;br&gt; tags</small>
                            <div class="invalid-feedback" id="titleError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="sliderTagline" class="form-label">Tagline</label>
                            <input type="text" class="form-control" id="sliderTagline" name="tagline" placeholder="Enter tagline">
                            <div class="invalid-feedback" id="taglineError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="sliderImage" class="form-label">Image</label>
                            <input type="file" class="form-control" id="sliderImage" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 1920x1080px, Max size: 2MB</small>
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
                <button type="button" class="btn btn-primary" id="saveSliderBtn">
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
let pendingCategoryData = null;

// Function to initialize Select2 for category
function initializeCategorySelect2() {
    const select = $('#sliderCategory');
    
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
        dropdownParent: $('#sliderModal'),
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
    // Load sliders on page load
    loadSliders();

    // Search functionality
    $('#tableSearch').on('keyup', debounce(function() {
        loadSliders();
    }, 500));

    // Status filter
    $('#statusFilter').on('change', function() {
        loadSliders();
    });

    // Initialize Select2 when modal is opened
    $('#sliderModal').on('shown.bs.modal', function() {
        initializeCategorySelect2();
        
        // Set pending category data if in edit mode
        if (pendingCategoryData && pendingCategoryData.id) {
            setTimeout(function() {
                const categorySelect = $('#sliderCategory');
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
    $('#sliderModal').on('hidden.bs.modal', function() {
        const select = $('#sliderCategory');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
            categorySelect2Initialized = false;
        }
        // Clear pending category data
        pendingCategoryData = null;
    });
    
    // Add Slider Button
    $('#addSliderBtn').on('click', function() {
        isEditMode = false;
        pendingCategoryData = null;
        $('#sliderModalLabel').text('Add Home Slider');
        $('#sliderForm')[0].reset();
        $('#sliderId').val('');
        $('#imagePreview').hide();
        $('#currentImage').hide();
        
        // Clear category select
        $('#sliderCategory').empty();
        
        $('#sliderModal').modal('show');
        
        // Clear category selection after modal is shown
        setTimeout(function() {
            $('#sliderCategory').val(null).trigger('change');
        }, 300);
    });

    // Save Slider
    $('#saveSliderBtn').on('click', function() {
        const form = $('#sliderForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        
        const url = isEditMode 
            ? '{{ route("home-sliders.update", ":id") }}'.replace(':id', $('#sliderId').val())
            : '{{ route("home-sliders.store") }}';

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
                    $('#sliderModal').modal('hide');
                    showAlert('success', response.message);
                    loadSliders();
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

    // Edit Slider
    $(document).on('click', '.edit-slider', function() {
        const id = $(this).data('id');
        isEditMode = true;
        $('#sliderModalLabel').text('Edit Home Slider');
        
        $.ajax({
            url: '{{ route("home-sliders.show", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#sliderId').val(data.id);
                    $('#sliderTitle').val(data.title);
                    $('#sliderTagline').val(data.tagline);
                    
                    // Store category data for later use (after Select2 initializes)
                    pendingCategoryData = {
                        id: data.category_id,
                        name: data.category_full_path_name || data.category_name || null
                    };
                    
                    if (data.image) {
                        $('#currentImg').attr('src', data.image);
                        $('#currentImage').show();
                    } else {
                        $('#currentImage').hide();
                    }
                    
                    $('#imagePreview').hide();
                    
                    // Clear category select before opening modal
                    const categorySelect = $('#sliderCategory');
                    categorySelect.empty();
                    
                    // Open modal - category will be set in shown.bs.modal handler
                    $('#sliderModal').modal('show');
                }
            }
        });
    });

    // Delete Slider
    $(document).on('click', '.delete-slider', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        
        if (confirm(`Are you sure you want to delete "${title}"?`)) {
            $.ajax({
                url: '{{ route("home-sliders.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadSliders();
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        }
    });

    // Toggle Status
    $(document).on('change', '.status-toggle', function() {
        const id = $(this).data('id');
        const isActive = $(this).is(':checked');
        const $toggle = $(this);
        
        // Disable toggle while updating
        $toggle.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("home-sliders.update-status", ":id") }}'.replace(':id', id),
            method: 'POST',
            data: {
                is_active: isActive ? 1 : 0  // Convert boolean to 1/0 for better compatibility
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    // Update the label text
                    const $label = $toggle.next('label');
                    $label.text(isActive ? 'Active' : 'Inactive');
                }
            },
            error: function(xhr) {
                // Revert toggle on error
                $toggle.prop('checked', !isActive);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors && errors.is_active) {
                        showAlert('danger', errors.is_active[0]);
                    } else {
                        showAlert('danger', xhr.responseJSON?.message || 'Validation failed');
                    }
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            },
            complete: function() {
                $toggle.prop('disabled', false);
            }
        });
    });

    // Image Preview
    $('#sliderImage').on('change', function(e) {
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
        $('#sliderImage').val('');
        $('#imagePreview').hide();
    });

    // Insert <br> tag in title
    $('#insertBrBtn').on('click', function() {
        const $titleInput = $('#sliderTitle');
        const currentValue = $titleInput.val();
        const cursorPos = $titleInput[0].selectionStart || currentValue.length;
        const textBefore = currentValue.substring(0, cursorPos);
        const textAfter = currentValue.substring(cursorPos);
        const newValue = textBefore + '<br>' + textAfter;
        $titleInput.val(newValue);
        
        // Set cursor position after the inserted <br>
        setTimeout(function() {
            $titleInput[0].setSelectionRange(cursorPos + 4, cursorPos + 4);
            $titleInput.focus();
        }, 10);
    });

    // Remove all <br> tags from title
    $('#removeBrBtn').on('click', function() {
        const $titleInput = $('#sliderTitle');
        const currentValue = $titleInput.val();
        // Remove all <br> and <br/> tags (case insensitive)
        const newValue = currentValue.replace(/<br\s*\/?>/gi, '');
        $titleInput.val(newValue);
        $titleInput.focus();
    });

    // Initialize Sortable
    function initializeSortable() {
        const tbody = document.getElementById('slidersTableBody');
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
        $('#slidersTableBody tr').each(function(index) {
            const id = $(this).data('id');
            if (id) {
                items.push({
                    id: id,
                    sort_order: index + 1
                });
            }
        });

        $.ajax({
            url: '{{ route("home-sliders.update-sort-order") }}',
            method: 'POST',
            data: {
                items: items
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadSliders();
                }
            }
        });
    }

    // Load Sliders
    function loadSliders() {
        const search = $('#tableSearch').val();
        const status = $('#statusFilter').val();

        // Show loading state
        $('#slidersTableBody').html('<tr><td colspan="9" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.ajax({
            url: '{{ route("home-sliders.data") }}',
            method: 'GET',
            data: {
                search: search,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    renderTable(response.data);
                    initializeSortable();
                } else {
                    $('#slidersTableBody').html('<tr><td colspan="9" class="text-center text-danger">' + (response.message || 'Failed to load sliders') + '</td></tr>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error loading sliders';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'Unauthorized. Please login.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Access forbidden.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint not found.';
                }
                $('#slidersTableBody').html('<tr><td colspan="9" class="text-center text-danger">' + errorMessage + '</td></tr>');
            }
        });
    }

    // Render Table
    function renderTable(data) {
        if (!data || data.length === 0) {
            $('#slidersTableBody').html('<tr><td colspan="9" class="text-center py-5">No home sliders found</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item) {
            const imageHtml = item.image 
                ? `<img src="${item.image}" alt="${item.title || 'Home Slider'}" class="image-preview">`
                : '<span class="text-muted">No image</span>';

            const title = item.title || 'Untitled';
            const tagline = item.tagline || 'N/A';
            const categoryName = item.category_name || 'N/A';
            const sortOrder = item.sort_order || 0;
            const createdAt = item.created_at || 'N/A';
            const isActive = item.is_active;

            html += `
                <tr data-id="${item.id}">
                    <td><i class="bx bx-menu drag-handle"></i></td>
                    <td>${imageHtml}</td>
                    <td>${title}</td>
                    <td>${tagline}</td>
                    <td>${categoryName}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" type="checkbox" role="switch" id="statusToggle${item.id}" data-id="${item.id}" ${isActive ? 'checked' : ''}>
                            <label class="form-check-label" for="statusToggle${item.id}">
                                ${isActive ? 'Active' : 'Inactive'}
                            </label>
                        </div>
                    </td>
                    <td>${sortOrder}</td>
                    <td>${createdAt}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-slider" data-id="${item.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-slider" data-id="${item.id}" data-title="${title}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#slidersTableBody').html(html);
    }

    // Display Errors
    function displayErrors(errors) {
        $('.invalid-feedback').text('').hide();
        $('.form-control, .form-select').removeClass('is-invalid');
        
        $.each(errors, function(key, value) {
            const field = key.replace('_', '');
            $(`#${field}Error`).text(value[0]).show();
            $(`#slider${field.charAt(0).toUpperCase() + field.slice(1)}`).addClass('is-invalid');
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

