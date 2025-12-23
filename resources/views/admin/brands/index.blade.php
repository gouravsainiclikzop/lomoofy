@extends('layouts.admin')

@section('title', 'Brands')

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Brands</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Brands</h1>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteBrandsBtn">
                    <i class="fas fa-trash me-2"></i>Delete Selected
                </button>
                <button class="btn btn-primary" id="addBrandBtn">
                    <i class="fas fa-plus"></i> Add Brand
                </button>
            </div>
        </div>

        <!-- Brands Table -->
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
                        <input type="text" placeholder="Search brands..." class="form-control form-control--search" id="tableSearch"/>
                    </div>
                </div>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="brandsTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAllBrands" title="Select All">
                            </th>
                            <th width="80">Logo</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Website</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="brandsTableBody">
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

            <!-- AJAX Pagination -->
            <div class="d-flex justify-content-center p-4" id="paginationContainer">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandModalLabel">Add Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlertContainer"></div>
                
                <form id="brandForm">
                    <input type="hidden" id="brandId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="brandName" class="form-label">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brandName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brandSlug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="brandSlug" name="slug">
                            <small class="form-text text-muted">Leave empty to auto-generate from name</small>
                            <div class="invalid-feedback" id="slugError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="brandDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="brandDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brandLogo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="brandLogo" name="logo" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 200x200px, Max size: 2MB</small>
                            <div id="logoPreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #dee2e6;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeLogoBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div id="currentLogo" class="mt-2" style="display: none;">
                                <img id="currentImg" src="" alt="Current" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #dee2e6;">
                                <p class="text-muted small mt-1">Current logo</p>
                            </div>
                            <div class="invalid-feedback" id="logoError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brandWebsite" class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="brandWebsite" name="website" placeholder="https://example.com">
                            <div class="invalid-feedback" id="websiteError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brandSortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="brandSortOrder" name="sort_order" value="0" min="0">
                            <div class="invalid-feedback" id="sortOrderError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="brandIsActive" name="is_active" checked>
                                <label class="form-check-label" for="brandIsActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBrandBtn">
                    <span id="saveBtnText">Save Brand</span>
                    <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
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
                Are you sure you want to delete this brand? This action cannot be undone.
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
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('styles')
<style>
.brand-logo {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

.brand-logo-placeholder {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let deleteBrandId = null;
    let currentPage = 1;
    
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Load brands on page load
    loadBrands();
    
    // Status Filter Change
    $('#statusFilter').change(function() {
        currentPage = 1;
        loadBrands();
    });
    
    // Search functionality with debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadBrands();
        }, 500);
    });
    
    // Auto-generate slug from name
    let slugManuallyEdited = false;
    
    function generateSlugFromName(name) {
        return name.toLowerCase()                   // convert to lowercase
                    .replace(/[^a-z0-9\s-]/g, '')    // remove invalid chars
                    .replace(/\s+/g, '-')           // replace spaces with hyphens
                    .replace(/-+/g, '-')            // collapse multiple hyphens
                    .replace(/^-+|-+$/g, '');       // remove leading/trailing hyphens
    }
    
    $('#brandName').on('input', function() {
        const name = $(this).val();
        const slugField = $('#brandSlug');
        
        // Only auto-generate if slug hasn't been manually edited
        if (!slugManuallyEdited) {
            const slug = generateSlugFromName(name);
            slugField.val(slug);
        }
    });
    
    // Track if slug is manually edited
    $('#brandSlug').on('input', function() {
        slugManuallyEdited = true;
    });
    
    // Reset flag when modal is opened for new brand
    $('#brandModal').on('show.bs.modal', function() {
        // Check if this is a new brand (no ID) or if slug matches auto-generated
        const brandId = $('#brandId').val();
        const brandName = $('#brandName').val();
        const brandSlug = $('#brandSlug').val();
        
        if (!brandId) {
            // New brand - reset flag
            slugManuallyEdited = false;
        } else if (brandName && brandSlug) {
            // Existing brand - check if slug matches auto-generated
            const autoSlug = generateSlugFromName(brandName);
            slugManuallyEdited = (brandSlug !== autoSlug);
        }
    });
    
    $('#brandModal').on('hidden.bs.modal', function() {
        slugManuallyEdited = false;
        $('#brandSlug').val('');
    });


    
    // Logo preview handler
    $('#brandLogo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.match('image.*')) {
                $('#logoError').text('Please select a valid image file.').show();
                $('#brandLogo').addClass('is-invalid');
                $(this).val('');
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                $('#logoError').text('Image size must be less than 2MB.').show();
                $('#brandLogo').addClass('is-invalid');
                $(this).val('');
                return;
            }
            
            $('#logoError').text('').hide();
            $('#brandLogo').removeClass('is-invalid');
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#logoPreview').show();
                $('#currentLogo').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remove logo button
    $('#removeLogoBtn').on('click', function() {
        $('#brandLogo').val('');
        $('#logoPreview').hide();
        $('#previewImg').attr('src', '');
    });
    
    // Open Modal for Add
    $('#addBrandBtn').on('click', function() {
        $('#brandModalLabel').text('Add Brand');
        $('#brandForm')[0].reset();
        $('#brandId').val('');
        $('#brandIsActive').prop('checked', true);
        $('#logoPreview').hide();
        $('#currentLogo').hide();
        clearModalAlerts();
        $('#brandModal').modal('show');
    });
    
    // Open Modal for Edit
    $(document).on('click', '.edit-brand', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        
        $('#brandModalLabel').text('Edit Brand');
        clearModalAlerts();
        
        $.ajax({
            url: '{{ route("brands.edit", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let brand = response.data;
                    
                    $('#brandId').val(brand.id);
                    $('#brandName').val(brand.name);
                    $('#brandSlug').val(brand.slug);
                    $('#brandDescription').val(brand.description);
                    $('#brandWebsite').val(brand.website);
                    $('#brandSortOrder').val(brand.sort_order);
                    $('#brandIsActive').prop('checked', brand.is_active);
                    
                    // Handle logo display
                    $('#brandLogo').val('');
                    $('#logoPreview').hide();
                    if (brand.logo) {
                        $('#currentImg').attr('src', '/storage/' + brand.logo);
                        $('#currentLogo').show();
                    } else {
                        $('#currentLogo').hide();
                    }
                    
                    $('#brandModal').modal('show');
                }
            },
            error: function(xhr) {
                showToast('error', 'Error loading brand data');
            }
        });
    });
    
    // Save Brand (Create or Update)
    $('#saveBrandBtn').on('click', function() {
        let id = $('#brandId').val();
        let url = id ? '{{ route("brands.update", ":id") }}'.replace(':id', id) : '{{ route("brands.store") }}';
        
        // Create FormData for file upload
        let formData = new FormData();
        formData.append('id', id);
        formData.append('name', $('#brandName').val());
        formData.append('slug', $('#brandSlug').val());
        formData.append('description', $('#brandDescription').val());
        formData.append('website', $('#brandWebsite').val());
        formData.append('sort_order', $('#brandSortOrder').val());
        formData.append('is_active', $('#brandIsActive').is(':checked') ? 1 : 0);
        
        // Append logo file if selected
        let logoFile = $('#brandLogo')[0].files[0];
        if (logoFile) {
            formData.append('logo', logoFile);
        }
        
        // No need for _method since route accepts POST
        
        // Show loading
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveBrandBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#brandModal').modal('hide');
                    loadBrands();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    
                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('').hide();
                    
                    // Mark fields as invalid
                    if(errors.name) {
                        $('#brandName').addClass('is-invalid');
                        $('#nameError').text(errors.name[0]).show();
                    }
                    if(errors.slug) {
                        $('#brandSlug').addClass('is-invalid');
                        $('#slugError').text(errors.slug[0]).show();
                    }
                    if(errors.description) {
                        $('#brandDescription').addClass('is-invalid');
                        $('#descriptionError').text(errors.description[0]).show();
                    }
                    if(errors.logo) {
                        $('#brandLogo').addClass('is-invalid');
                        $('#logoError').text(errors.logo[0]).show();
                    }
                    if(errors.website) {
                        $('#brandWebsite').addClass('is-invalid');
                        $('#websiteError').text(errors.website[0]).show();
                    }
                    if(errors.sort_order) {
                        $('#brandSortOrder').addClass('is-invalid');
                        $('#sortOrderError').text(errors.sort_order[0]).show();
                    }
                } else {
                    showModalAlert(xhr.responseJSON?.message || 'An error occurred. Please try again.', 'danger');
                }
            },
            complete: function() {
                $('#saveBtnText').removeClass('d-none');
                $('#saveBtnSpinner').addClass('d-none');
                $('#saveBrandBtn').prop('disabled', false);
            }
        });
    });
    
    // Helper function to show alert in modal
    function showModalAlert(message, type = 'danger') {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#modalAlertContainer').html(alertHtml);
    }
    
    // Helper function to clear modal alerts
    function clearModalAlerts() {
        $('#modalAlertContainer').empty();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        currentPage = page;
        loadBrands();
    });
    
    // Delete Brand Click
    $(document).on('click', '.delete-brand', function(e) {
        e.preventDefault();
        deleteBrandId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    // Confirm Delete Button Click
    $('#confirmDelete').click(function() {
        deleteBrand();
    });
    
    // Load Brands Function
    function loadBrands() {
        let search = $('#tableSearch').val();
        let status = $('#statusFilter').val();
        
        let params = {
            draw: 1,
            start: (currentPage - 1) * 20,
            length: 20
        };
        
        if (search) {
            params.search = { value: search };
        }
        if (status !== '') {
            params.status = status;
        }
        
        $.ajax({
            url: '{{ route("brands.data") }}',
            type: 'GET',
            data: params,
            success: function(response) {
                let tbody = $('#brandsTableBody');
                tbody.empty();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(function(brand) {
                        let logoHtml = '';
                        if(brand.logo) {
                            logoHtml = `<img src="/storage/${brand.logo}" alt="${brand.name}" class="brand-logo">`;
                        } else {
                            logoHtml = '<div class="brand-logo-placeholder"><i class="fas fa-image"></i></div>';
                        }
                        
                        let statusBadge = brand.is_active 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let websiteHtml = brand.website 
                            ? `<a href="${brand.website}" target="_blank" class="text-decoration-none"><i class="fas fa-external-link-alt"></i> Visit</a>`
                            : '<span class="text-muted">-</span>';
                        
                        let descriptionHtml = brand.description 
                            ? `<br><small class="text-muted">${brand.description.substring(0, 50)}${brand.description.length > 50 ? '...' : ''}</small>`
                            : '';
                        
                        let row = `
                            <tr data-id="${brand.id}">
                                <td>
                                    <input type="checkbox" class="form-check-input brand-checkbox" value="${brand.id}">
                                </td>
                                <td>${logoHtml}</td>
                                <td>
                                    <strong>${brand.name}</strong>
                                    ${descriptionHtml}
                                </td>
                                <td><code>${brand.slug}</code></td>
                                <td>${websiteHtml}</td>
                                <td><span class="badge bg-info">${brand.categories_count || 0}</span></td>
                                <td>${statusBadge}</td>
                                <td>${brand.sort_order}</td>
                                <td>${new Date(brand.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-brand" data-id="${brand.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-brand" data-id="${brand.id}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="10" class="text-center py-4">No brands found</td></tr>');
                }
                
                // Update pagination
                updatePagination(response);
                
                // Reset bulk delete button
                $('#selectAllBrands').prop('checked', false);
                updateBulkDeleteButton();
            },
            error: function(xhr) {
                console.error('Error loading brands:', xhr);
                $('#brandsTableBody').html('<tr><td colspan="10" class="text-center py-4 text-danger">Error loading brands</td></tr>');
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
    
    // Delete Brand
    function deleteBrand() {
        if(!deleteBrandId) return;
        
        $.ajax({
            url: '{{ route("brands.destroy", ":id") }}'.replace(':id', deleteBrandId),
            type: 'POST',
            data: {
                '_method': 'DELETE'
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#deleteModal').modal('hide');
                    loadBrands();
                    $('#selectAllBrands').prop('checked', false);
                    updateBulkDeleteButton();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', xhr.responseJSON.message || 'Cannot delete brand');
                } else {
                    showToast('error', 'Failed to delete brand');
                }
            },
            complete: function() {
                deleteBrandId = null;
            }
        });
    }
    
    // Select All Checkboxes
    $('#selectAllBrands').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.brand-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox change
    $(document).on('change', '.brand-checkbox', function() {
        let totalCheckboxes = $('.brand-checkbox').length;
        let checkedCheckboxes = $('.brand-checkbox:checked').length;
        $('#selectAllBrands').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateBulkDeleteButton();
    });
    
    function updateBulkDeleteButton() {
        const checkedCount = $('.brand-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBrandsBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteBrandsBtn').addClass('d-none');
        }
    }
    
    function bulkDeleteBrands() {
        const checkedBoxes = $('.brand-checkbox:checked');
        if (checkedBoxes.length === 0) {
            showToast('error', 'Please select at least one brand to delete');
            return;
        }
        
        const ids = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        
        if (!confirm(`Are you sure you want to delete ${ids.length} brand(s)? This action cannot be undone.`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("brands.bulk-delete") }}',
            type: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    loadBrands();
                    $('#selectAllBrands').prop('checked', false);
                    updateBulkDeleteButton();
                } else {
                    let errorMsg = response.message || 'Failed to delete brands';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '<br>' + response.errors.join('<br>');
                    }
                    showToast('error', errorMsg);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to delete brands';
                showToast('error', message);
            }
        });
    }

    // Bulk delete button click handler
    $(document).on('click', '#bulkDeleteBrandsBtn', function(e) {
        e.preventDefault();
        bulkDeleteBrands();
    });

    // Show Toast
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
