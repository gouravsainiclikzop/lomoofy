@extends('layouts.admin')

@section('title', 'Categories')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Select2 styling */
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
    
    /* Select2 with checkboxes and radio buttons */
    .select2-results__option {
        padding-left: 35px !important;
        position: relative;
        line-height: 1.5;
    }
    .select2-results__option--highlighted {
        background-color: #f8f9fa !important;
        color: #212529 !important;
    }
    .select2-results__option[aria-selected="true"] {
        background-color: #e7f3ff !important;
    }
    
    /* Checkbox styling for multi-select */
    .select2-results__option .select2-checkbox {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #ced4da;
        border-radius: 3px;
        background-color: #fff;
        display: inline-block;
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    .select2-results__option:hover .select2-checkbox {
        border-color: #f5c000;
    }
    .select2-results__option[aria-selected="true"] .select2-checkbox {
        background-color: #f5c000;
        border-color: #f5c000;
    }
    .select2-results__option[aria-selected="true"] .select2-checkbox::after {
        content: '✓';
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        line-height: 1;
    }
    
    /* Radio button styling for single select */
    .select2-results__option .select2-radio {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #ced4da;
        border-radius: 50%;
        background-color: #fff;
        display: inline-block;
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    .select2-results__option:hover .select2-radio {
        border-color: #f5c000;
    }
    .select2-results__option[aria-selected="true"] .select2-radio {
        border-color: #f5c000;
    }
    .select2-results__option[aria-selected="true"] .select2-radio::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #f5c000;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }
    .parent-display {
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.2s;
        min-height: 30px;
        display: inline-block;
        width: 100%;
    }
    .parent-display:hover {
        background-color: #f0f0f0;
    }
    .parent-display * {
        pointer-events: none;
    }
    .parent-edit select {
        border: 2px solidrgb(211, 165, 0);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .status-toggle {
        cursor: pointer;
    }
    .status-cell .form-check {
        margin: 0;
        padding: 0;
        display: inline-block;
    }
    .status-cell .form-check-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        margin: 0;
        padding: 0;
        pointer-events: none;
    }
    .status-cell .form-check-label {
        margin: 0;
        padding: 0;
        cursor: pointer;
        display: inline-block;
    }
    .img-thumbnail {
        border: 1px solid #dee2e6;
        padding: 2px;
    }
    table td img {
        display: block;
        margin: 0 auto;
    }
    
    /* Modern UI Improvements */
    .category-header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    /* Improved Table View */
    .table-view {
        display: block;
    }
    
    .table-view.hidden {
        display: none;
    }
    
    .category-name-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Collapsible category rows */
    .category-child-row {
        background-color: #f8f9fa;
    }
    .category-child-row td {
        padding-left: 40px !important;
    }
    .category-toggle-btn {
        color: #6c757d;
        transition: transform 0.2s ease;
        border: none;
        background: none;
        padding: 0;
        margin-right: 8px;
    }
    .category-toggle-btn:hover {
        color: #495057;
    }
    .category-toggle-btn.expanded i {
        transform: rotate(90deg);
    }
    .category-toggle-btn i {
        transition: transform 0.2s ease;
        font-size: 12px;
    }
    
    .category-name-cell .hierarchy-indent {
        width: 20px;
        display: inline-flex;
        align-items: center;
        color: #adb5bd;
    }
    
    .products-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        background: #e7f3ff;
        color: #f5c000;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    /* Category Actions Popover Styling */
    .category-actions-popover {
        min-width: 140px;
        padding: 0;
    }
    .category-actions-popover a {
        transition: background-color 0.2s ease;
    }
    .category-actions-popover a:hover {
        background-color: #f8f9fa;
    }
    .category-actions-popover .add-child-category {
        font-weight: 500;
    }
    .category-actions-popover .add-child-category:hover {
        background-color: #e7f3ff;
    }
    .popover {
        border: 1px solid rgba(0, 0, 0, 0.2);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .popover .popover-body {
        padding: 0;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
    }
    
    .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #f5c000;
        margin: 8px 0;
    }
    
    .stat-card-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Hierarchy/Tree View Styles */
    .categories-hierarchy {
        padding: 20px;
    }
    
    .category-tree {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .category-tree-item {
        margin: 8px 0;
        padding: 0;
    }
    
    .category-tree-node {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        transition: all 0.2s;
        margin-left: 0;
    }
    
    .category-tree-node:hover {
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .category-tree-node.has-children {
        cursor: pointer;
    }
    
    .category-tree-toggle {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        border-radius: 4px;
        background: #f0f0f0;
        color: #666;
        font-size: 12px;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .category-tree-toggle:hover {
        background: #e0e0e0;
    }
    
    .category-tree-toggle.expanded {
        background: #f5c000;
        color: white;
    }
    
    .category-tree-spacer {
        width: 24px;
        display: inline-block;
        flex-shrink: 0;
    }
    
    .category-tree-content {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .category-tree-image {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        flex-shrink: 0;
    }
    
    .category-tree-image-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        background: linear-gradient(135deg, #f5c000 0%, #f5c000 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .category-tree-info {
        flex: 1;
        min-width: 0;
    }
    
    .category-tree-name {
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .category-tree-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
        font-size: 12px;
        color: #6c757d;
    }
    
    .category-tree-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .category-tree-children {
        list-style: none;
        padding: 0;
        margin: 0;
        margin-left: 32px;
        display: none;
    }
    
    .category-tree-children.expanded {
        display: block;
    }
    
    .category-tree-level-indicator {
        width: 3px;
        height: 100%;
        background: #e0e0e0;
        margin-right: 8px;
        flex-shrink: 0;
    }
    
    .category-tree-level-indicator.level-0 {
        background: #f5c000;
    }
    
    .category-tree-level-indicator.level-1 {
        background: #6c757d;
    }
    
    .category-tree-level-indicator.level-2 {
        background: #adb5bd;
    }
    
    @media (max-width: 768px) {
        .category-header-actions {
            flex-direction: column;
            width: 100%;
        }
        
        .category-tree-node {
            flex-wrap: wrap;
        }
        
        .category-tree-actions {
            width: 100%;
            margin-top: 8px;
            justify-content: flex-end;
        }
        
        .category-tree-children {
            margin-left: 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="mx-sm-2 px-2 px-sm-3 px-xxl-4 pb-6">
    <div class="container">
        <div class="py-5">
            <div class="row g-4 align-items-center mb-4">
                <div class="col">
                    <nav class="mb-2" aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-sa-simple">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </nav>
                    <h1 class="h3 m-0">
                        <i class="fas fa-folder-tree me-2 text-primary"></i>Categories Management
                    </h1>
                    <p class="text-muted mb-0 mt-2">Organize your products with unlimited category hierarchy</p>
                </div>
                <div class="col-auto">
                    <div class="category-header-actions">
                        <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteCategoriesBtn">
                            <i class="fas fa-trash me-2"></i>Delete Selected
                        </button>
                        <button class="btn btn-primary" id="addCategoryBtn">
                            <i class="fas fa-plus me-2"></i>New Category
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards" id="statsCards">
                <div class="stat-card">
                    <div class="stat-card-label">Total Categories</div>
                    <div class="stat-card-value" id="totalCategories">-</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Active Categories</div>
                    <div class="stat-card-value text-success" id="activeCategories">-</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Root Categories</div>
                    <div class="stat-card-value text-info" id="rootCategories">-</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Total Products</div>
                    <div class="stat-card-value text-warning" id="totalProducts">-</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="filter-section">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Status Filter</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label small text-muted mb-1">Search Categories</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" placeholder="Search by name, description, or slug..." class="form-control form-control-sm" id="tableSearch"/>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table View -->
            <div class="table-view" id="tableView">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="categoriesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAllCategories" class="form-check-input">
                                </th>
                                <th style="width: 80px;">Image</th>
                                <th>Category Name</th>
                                <th>Parent</th>
                                <th style="width: 150px;">Attributes</th>
                                <th style="width: 100px;">Products</th>
                                <th style="width: 120px;">Status</th>
                                <th class="w-min" style="width: 60px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Hierarchy/Tree View -->
            <div class="categories-hierarchy" id="categoriesHierarchy" style="display: none; padding: 20px;">
                <!-- Tree structure will be loaded here -->
            </div>
            
            <!-- AJAX Pagination -->
            <div class="d-flex justify-content-center p-4 border-top" id="paginationContainer">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Alert Container (for system errors only, not validation) -->
                <div id="modalAlertContainer"></div>
                
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sortOrder" name="sort_order" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select class="form-select select2-single" id="parentCategory" name="parent_id" data-placeholder="Select Parent Category">
                            <option value="">-- None (Main Category) --</option>
                        </select>
                        <div class="invalid-feedback" id="parentCategoryError"></div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Search and select a parent category.  
                        </small>
                        <div class="alert alert-warning mt-2 mb-0" id="maxDepthWarning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Maximum Depth Reached:</strong> The selected parent category is already at level 4. You cannot create child categories under it.
                        </div>
                    </div>
                    
                    <!-- Product Attributes Assignment -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-tags me-1 text-primary"></i>Assigned Product Attributes
                        </label>
                        <select class="form-select select2-multiple" id="productAttributes" name="product_attribute_ids[]" multiple data-placeholder="Select attributes for this category">
                            <!-- Options will be loaded dynamically -->
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Select product attributes that apply to this category. Child categories will inherit these attributes.
                        </small>
                        <div id="inheritedAttributesContainer" class="mt-2" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <strong><i class="fas fa-arrow-down me-1"></i>Inherited Attributes (from parent categories):</strong>
                                <div id="inheritedAttributesList" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="categoryImage" name="image" accept="image/*">
                        <div class="form-text">Upload an image for this category (JPG, PNG, GIF - Max 2MB)</div>
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn">
                                <i class="fas fa-times"></i> Remove Image
                            </button>
                        </div>
                        <div id="currentImage" class="mt-2" style="display: none;">
                            <img id="currentImg" src="" alt="Current" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                            <p class="text-muted small mt-1">Current image</p>
                        </div>
                        <div class="invalid-feedback" id="imageError"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" id="isActive" name="is_active" checked>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">
                    <span id="saveBtnText">Save Category</span>
                    <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let currentPage = 1;
    let currentView = 'table'; // Only table view
    let allCategoriesData = []; // Store all categories for stats

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Helper function to show alert in modal (for system errors only)
    function showModalAlert(message, type = 'danger') {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#modalAlertContainer').html(alertHtml);
        
        // Scroll to alert
        $('.modal-body').animate({ scrollTop: 0 }, 300);
    }

    // Helper function to show success alert
    function showSuccessAlert(message) {
        let alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;" role="alert">
                <strong>Success!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Helper function to show toast
    function showToast(type, message) {
        // Use showSuccessAlert for now, or implement toast if needed
        if (type === 'success') {
            showSuccessAlert(message);
        } else {
            let alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;" role="alert">
                    <strong>Error!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('body').append(alertHtml);
            
            setTimeout(function() {
                $('.alert-danger').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    }

    // Helper function to clear modal alerts
    function clearModalAlerts() {
        $('#modalAlertContainer').empty();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        // Clear Select2 invalid styling
        $('.select2-container .select2-selection.is-invalid').removeClass('is-invalid');
    }

    // Load Categories with Pagination
    function loadCategories() {
        
        let search = $('#tableSearch').val();
        let status = $('#statusFilter').val();
        let url = '{{ route("categories.data") }}';
        let params = new URLSearchParams();
        
        if (search) params.append('search', search);
        if (status !== '') params.append('status', status);
        if (currentPage > 1) params.append('page', currentPage);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    allCategoriesData = response.data;
                    renderCategories(response.data);
                    updateStats(response.data, response.pagination);
                    
                    // Update pagination
                    updatePagination(response.pagination);
                }
            },
            error: function(xhr) {
                console.error('Error loading categories:', xhr);
                $('#categoriesTableBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Error loading categories</td></tr>');
            }
        });
    }

    // Load Categories in Hierarchy View
    function loadCategoriesHierarchy() {
        let search = $('#tableSearch').val();
        let status = $('#statusFilter').val();
        let url = '{{ route("categories.data") }}';
        let params = new URLSearchParams();
        
        params.append('hierarchy', '1');
        if (search) params.append('search', search);
        if (status !== '') params.append('status', status);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    renderHierarchyView(response.data);
                    updateStats(response.data, response.pagination);
                }
            },
            error: function(xhr) {
                console.error('Error loading categories hierarchy:', xhr);
                $('#categoriesHierarchy').html('<div class="text-center py-4 text-danger">Error loading categories hierarchy</div>');
            }
        });
    }

    // Render Hierarchy View
    function renderHierarchyView(categories) {
        let hierarchyContainer = $('#categoriesHierarchy');
        hierarchyContainer.empty();
        
        // Hide and clear pagination for hierarchy view (all categories shown in tree)
        $('#paginationContainer').hide().html('');
        
        if (categories.length === 0) {
            hierarchyContainer.html('<div class="text-center py-5"><i class="fas fa-folder-open fa-3x text-muted mb-3"></i><p class="text-muted">No categories found</p></div>');
            return;
        }
        
        let tree = $('<ul class="category-tree"></ul>');
        categories.forEach(function(category) {
            tree.append(renderHierarchyNode(category, 0));
        });
        
        hierarchyContainer.append(tree);
    }

    // Render single hierarchy node (recursive)
    function renderHierarchyNode(category, level) {
        let hasChildren = category.children && category.children.length > 0;
        let nodeId = 'category-node-' + category.id;
        let childrenId = 'category-children-' + category.id;
        
        let imageUrl = category.image ? '{{ asset("storage") }}/' + category.image : '';
        let imageHtml = category.image 
            ? `<img src="${imageUrl}" alt="${category.name}" class="category-tree-image">`
            : `<div class="category-tree-image-placeholder"><i class="fas fa-folder"></i></div>`;
        
        let statusBadge = category.is_active 
            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>'
            : '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
        
        let toggleButton = hasChildren 
            ? `<span class="category-tree-toggle" data-target="${childrenId}"><i class="fas fa-chevron-right"></i></span>`
            : '<span class="category-tree-spacer"></span>';
        
        let levelIndicator = `<span class="category-tree-level-indicator level-${Math.min(level, 2)}"></span>`;
        
        let node = $(`
            <li class="category-tree-item">
                <div class="category-tree-node ${hasChildren ? 'has-children' : ''}" style="margin-left: ${level * 24}px;">
                    ${levelIndicator}
                    ${toggleButton}
                    <div class="category-tree-content">
                        ${imageHtml}
                        <div class="category-tree-info">
                            <div class="category-tree-name">
                                <span>${category.name || '-'}</span>
                                ${category.parent_id ? '<span class="badge bg-secondary"><i class="fas fa-layer-group"></i></span>' : '<span class="badge bg-primary"><i class="fas fa-folder"></i></span>'}
                            </div>
                            <div class="category-tree-meta">
                                <span><i class="fas fa-box text-primary"></i> <strong>${category.products_count || 0}</strong> Products</span>
                                <span>${statusBadge}</span>
                                ${category.parent_name ? `<span><i class="fas fa-level-up-alt"></i> ${category.parent_name}</span>` : ''}
                            </div>
                        </div>
                        <div class="category-tree-actions">
                            <button class="btn btn-sm btn-outline-primary edit-category" data-id="${category.id}">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-category" data-id="${category.id}">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
                ${hasChildren ? `<ul class="category-tree-children" id="${childrenId}"></ul>` : ''}
            </li>
        `);
        
        // Add children recursively
        if (hasChildren && category.children) {
            let childrenList = node.find('ul.category-tree-children');
            category.children.forEach(function(child) {
                childrenList.append(renderHierarchyNode(child, level + 1));
            });
        }
        
        return node;
    }

    // Handle hierarchy toggle clicks
    $(document).on('click', '.category-tree-toggle', function() {
        let targetId = $(this).data('target');
        let children = $('#' + targetId);
        let icon = $(this).find('i');
        
        if (children.hasClass('expanded')) {
            children.removeClass('expanded').slideUp(200);
            $(this).removeClass('expanded');
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            children.addClass('expanded').slideDown(200);
            $(this).addClass('expanded');
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }
    });

    // Render categories (table view only)
    function renderCategories(categories) {
        renderTableView(categories);
    }

    // Render Table View
    function renderTableView(categories) {
        let tbody = $('#categoriesTableBody');
        
        // Destroy all existing popovers before clearing
        $('.category-actions-btn').each(function() {
            let popoverInstance = bootstrap.Popover.getInstance(this);
            if (popoverInstance) {
                popoverInstance.dispose();
            }
        });
        
        tbody.empty();
        
        if(categories.length > 0) {
            // Build complete hierarchy tree recursively
            let categoriesMap = {}; // Map all categories by ID
            let rootCategories = [];
            
            // First pass: create a map of all categories by ID
            categories.forEach(function(category) {
                categoriesMap[category.id] = {
                    ...category,
                    children: []
                };
            });
            
            // Second pass: build hierarchy tree (all levels)
            categories.forEach(function(category) {
                let categoryNode = categoriesMap[category.id];
                if (!category.parent_id) {
                    // Root category
                    rootCategories.push(categoryNode);
                } else {
                    // Child category - add to parent's children array
                    if (categoriesMap[category.parent_id]) {
                        categoriesMap[category.parent_id].children.push(categoryNode);
                    }
                }
            });
            
            // Third pass: recursively mark categories with children
            function markChildren(category) {
                if (category.children && category.children.length > 0) {
                    category.has_children = true;
                    category.children.forEach(function(child) {
                        markChildren(child);
                    });
                } else {
                    category.has_children = false;
                }
            }
            
            rootCategories.forEach(function(category) {
                markChildren(category);
            });
            
            // Render root categories recursively
            if (rootCategories.length > 0) {
                rootCategories.forEach(function(category) {
                    renderTableRowRecursive(category, tbody, 0);
                });
            } else {
                tbody.html('<tr><td colspan="8" class="text-center py-4">No categories found</td></tr>');
            }
        } else {
            tbody.html('<tr><td colspan="8" class="text-center py-4">No categories found</td></tr>');
        }
    }

    // Render single table row recursively (supports unlimited nesting)
    function renderTableRowRecursive(category, tbody, level = 0) {
        let parentName = category.parent_name || '<span class="text-muted">—</span>';
        let hasChildren = category.has_children || (category.children && category.children.length > 0);
        let isRoot = level === 0;
        
        // Calculate indentation based on level
        let indentPx = level * 20; // 20px per level
        
        // Collapse/expand icon - show for ANY category with children (not just root)
        let collapseIcon = '';
        if (hasChildren) {
            collapseIcon = `<button type="button" class="btn btn-sm btn-link p-0 me-2 category-toggle-btn expanded" data-category-id="${category.id}" style="text-decoration: none; min-width: 20px;">
                <i class="fas fa-chevron-right"></i>
            </button>`;
        } else {
            collapseIcon = '<span class="me-2" style="display: inline-block; width: 20px;"></span>';
        }
        
        let nameCell = `
            <div class="category-name-cell" style="padding-left: ${indentPx}px;">
                ${collapseIcon}
                <a href="#" class="text-reset fw-medium">${category.name || '-'}</a>
            </div>
        `;
        
        let statusToggle = `
            <div class="form-check form-switch">
                <input class="form-check-input status-toggle" type="checkbox" 
                       id="status-${category.id}" 
                       data-id="${category.id}" 
                       ${category.is_active ? 'checked' : ''}>
                <label class="form-check-label status-badge-label" for="status-${category.id}" data-id="${category.id}">
                    <span class="badge ${category.is_active ? 'badge-sa-success' : 'badge-sa-secondary'}">
                        ${category.is_active ? 'Active' : 'Inactive'}
                    </span>
                </label>
            </div>
        `;
        
        let imageUrl = category.image ? '{{ asset("storage") }}/' + category.image : '';
        let imageCell = category.image 
            ? `<img src="${imageUrl}" alt="${category.name}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">`
            : '<div class="text-center"><i class="fas fa-image text-muted"></i></div>';
        
        let productsCount = `
            <span class="products-count-badge">
                <i class="fas fa-box"></i> ${category.products_count || 0}
            </span>
        `;
        
        // Render product attributes
        let attributesCell = '<span class="text-muted">—</span>';
        if (category.product_attributes && category.product_attributes.length > 0) {
            let attributeBadges = category.product_attributes.map(function(attr) {
                let badgeClass = attr.is_variation ? 'bg-info' : 'bg-secondary';
                return `<span class="badge ${badgeClass} me-1 mb-1" title="${attr.name}">${escapeHtml(attr.name)}</span>`;
            }).join('');
            attributesCell = `<div class="d-flex flex-wrap">${attributeBadges}</div>`;
        }
        
        // Child rows are shown by default (expanded)
        let rowClass = level > 0 ? 'category-child-row' : '';
        let parentIdValue = category.parent_id ? String(category.parent_id) : '';
        
        // Check if category can have children (max depth is 4, so level 0, 1, 2, or 3 can have children)
        let canHaveChildren = level < 3; // Level 0, 1, 2, or 3 can have children (max level is 4)
        
        // Build popover content - conditionally show "Add Child" option
        let addChildOption = '';
        if (canHaveChildren) {
            addChildOption = `<a href="#" class="add-child-category d-block py-2 px-3 text-decoration-none text-primary" data-id="${category.id}" data-name="${category.name}"><i class="fas fa-plus-circle me-2"></i>Add Child</a><hr class="my-1">`;
        }
        
        let popoverContent = `<div class="category-actions-popover">${addChildOption}<a href="#" class="edit-category d-block py-2 px-3 text-decoration-none" data-id="${category.id}"><i class="fas fa-edit me-2"></i>Edit</a><hr class="my-1"><a href="#" class="delete-category d-block py-2 px-3 text-decoration-none text-danger" data-id="${category.id}"><i class="fas fa-trash me-2"></i>Delete</a></div>`;
        
        let row = `
            <tr data-id="${category.id}" data-parent-id="${parentIdValue}" class="${rowClass}" data-has-children="${hasChildren ? '1' : '0'}" data-level="${level}">
                <td>
                    <input type="checkbox" class="form-check-input category-checkbox" value="${category.id}">
                </td>
                <td>${imageCell}</td>
                <td>${nameCell}</td>
                <td class="parent-cell" data-parent-id="${category.parent_id || ''}">
                    ${parentName}
                </td>
                <td>${attributesCell}</td>
                <td>${productsCount}</td>
                <td class="status-cell">${statusToggle}</td>
                <td>
                    <button class="btn btn-sa-muted btn-sm category-actions-btn" type="button" 
                            data-bs-toggle="popover" 
                            data-bs-placement="left"
                            data-bs-container="body"
                            data-bs-html="true"
                            data-bs-content='${popoverContent.replace(/'/g, "&#39;")}'
                            data-category-id="${category.id}"
                            data-category-name="${category.name || ''}"
                            data-can-have-children="${canHaveChildren ? '1' : '0'}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="3" height="13" fill="currentColor">
                            <path d="M1.5,8C0.7,8,0,7.3,0,6.5S0.7,5,1.5,5S3,5.7,3,6.5S2.3,8,1.5,8z M1.5,3C0.7,3,0,2.3,0,1.5S0.7,0,1.5,0 S3,0.7,3,1.5S2.3,3,1.5,3z M1.5,10C2.3,10,3,10.7,3,11.5S2.3,13,1.5,13S0,12.3,0,11.5S0.7,10,1.5,10z"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
        
        // Initialize popover for actions button
        let rowElement = tbody.find(`tr[data-id="${category.id}"]`);
        let actionsBtn = rowElement.find('.category-actions-btn');
        if (actionsBtn.length) {
            let popoverInstance = new bootstrap.Popover(actionsBtn[0], {
                container: 'body',
                placement: 'left',
                html: true,
                trigger: 'click'
            });
            
            // When popover is shown, attach the category ID and name to the links inside it
            actionsBtn.on('shown.bs.popover', function() {
                let categoryId = $(this).data('category-id');
                let categoryName = $(this).data('category-name') || $(this).attr('data-category-name') || '';
                let popoverTip = popoverInstance.tip;
                if (popoverTip && categoryId) {
                    $(popoverTip).find('.edit-category').attr('data-id', categoryId);
                    $(popoverTip).find('.delete-category').attr('data-id', categoryId);
                    $(popoverTip).find('.add-child-category').attr('data-id', categoryId).attr('data-name', categoryName);
                }
            });
        }
        
        // Recursively render child categories (all levels)
        if (hasChildren && category.children && category.children.length > 0) {
            category.children.forEach(function(child) {
                renderTableRowRecursive(child, tbody, level + 1);
            });
        }
    }

    // Keep old function for backward compatibility
    function renderTableRow(category, tbody, isParent = false) {
        renderTableRowRecursive(category, tbody, isParent ? 0 : 1);
    }


    // Flatten hierarchical categories for stats calculation
    function flattenCategories(categories) {
        let flat = [];
        categories.forEach(function(category) {
            flat.push(category);
            if (category.children && category.children.length > 0) {
                flat = flat.concat(flattenCategories(category.children));
            }
        });
        return flat;
    }

    // Update Stats
    function updateStats(categories, pagination) {
        // Flatten nested structure if needed
        let flatCategories = categories;
        if (categories.length > 0 && categories[0].children !== undefined) {
            flatCategories = flattenCategories(categories);
        }
        
        let total = pagination?.total || flatCategories.length;
        let active = flatCategories.filter(c => c.is_active).length;
        let root = flatCategories.filter(c => !c.parent_id).length;
        let totalProducts = flatCategories.reduce((sum, c) => sum + (c.products_count || 0), 0);
        
        $('#totalCategories').text(total);
        $('#activeCategories').text(active);
        $('#rootCategories').text(root);
        $('#totalProducts').text(totalProducts);
    }


    // Update Pagination
    function updatePagination(pagination) {
        
        // Show pagination container for table view
        $('#paginationContainer').show();
        
        let paginationHtml = '';
        
        if (pagination && pagination.last_page > 1) {
            paginationHtml = '<nav><ul class="pagination">';
            
            // Previous button
            if (pagination.current_page > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
            }
            
            // Page numbers
            let startPage = Math.max(1, pagination.current_page - 2);
            let endPage = Math.min(pagination.last_page, pagination.current_page + 2);
            
            if (startPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                let activeClass = i == pagination.current_page ? 'active' : '';
                paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            
            if (endPage < pagination.last_page) {
                if (endPage < pagination.last_page - 1) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
            }
            
            // Next button
            if (pagination.has_more_pages) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${parseInt(pagination.current_page) + 1}">Next</a></li>`;
            }
            
            paginationHtml += '</ul></nav>';
        }
        
        $('#paginationContainer').html(paginationHtml);
    }

    // Cache for parent categories to avoid duplicate requests
    const parentCategoriesCache = {};
    const parentCategoriesCacheTimeout = 5 * 60 * 1000; // 5 minutes
    
    // Clear parent categories cache (call after create/update/delete)
    function clearParentCategoriesCache() {
        Object.keys(parentCategoriesCache).forEach(key => {
            delete parentCategoriesCache[key];
        });
        console.log('Parent categories cache cleared');
    }
    
    // Load Parent Categories for Dropdown with caching
    function loadParentCategories(excludeId = null, currentParentId = null, callback = null) {
        // Create cache key
        const cacheKey = `exclude_${excludeId || 'none'}_parent_${currentParentId || 'none'}`;
        
        // Check cache first
        if (parentCategoriesCache[cacheKey]) {
            const cached = parentCategoriesCache[cacheKey];
            const now = Date.now();
            
            // Use cached data if it's still valid (not expired)
            if (now - cached.timestamp < parentCategoriesCacheTimeout) { 
                
                let select = $('#parentCategory');
                select.find('option:not(:first)').remove();
                
                cached.data.forEach(function(parent) {
                    select.append(`<option value="${parent.id}">${parent.name}</option>`);
                });
                
                if (select.hasClass('select2-hidden-accessible')) {
                    select.trigger('change');
                }
                
                if (callback && typeof callback === 'function') {
                    callback();
                }
                return;
            } else {
                // Cache expired, remove it
                delete parentCategoriesCache[cacheKey];
            }
        }
        
        let url = '{{ route("categories.parents") }}';
        let params = new URLSearchParams();
        
        if (excludeId) {
            params.append('exclude_id', excludeId);
        }
        if (currentParentId) {
            params.append('current_parent_id', currentParentId);
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        } 
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) { 
                console.log('Parent categories response:', response);
                
                if(response.success && response.data) {
                    // Cache the response
                    parentCategoriesCache[cacheKey] = {
                        data: response.data,
                        timestamp: Date.now()
                    };
                    
                    let select = $('#parentCategory');
                    select.find('option:not(:first)').remove();
                    
                    if (response.data.length === 0) {
                        console.warn('No parent categories available');
                        select.append('<option value="">No categories available</option>');
                    } else {
                        response.data.forEach(function(parent) { 
                            // Ensure ID is treated as string for consistent matching
                            let optionText = parent.name;
                            // Disable option if category cannot have children (at max depth)
                            let disabled = parent.can_have_children === false ? 'disabled' : '';
                            select.append(`<option value="${String(parent.id)}" ${disabled}>${optionText}</option>`);
                        });
                    }
                    
                    // Trigger change to update Select2 if it's already initialized
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.trigger('change');
                    }
                    
                    // Call callback if provided (for setting selection after loading)
                    if (callback && typeof callback === 'function') { 
                        callback();
                    }
                } else {
                    console.error('Parent categories response not successful:', response);
                    let select = $('#parentCategory');
                    select.find('option:not(:first)').remove();
                    select.append('<option value="">Error loading categories</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading parent categories:', {
                    status: status,
                    error: error,
                    response: xhr.responseText,
                    statusCode: xhr.status
                });
                // Show error message to user
                let select = $('#parentCategory');
                select.append('<option value="">Error loading categories. Please refresh the page.</option>');
            }
        });
    }

    // Image preview handler
    $('#categoryImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.match('image.*')) {
                $('#imageError').text('Please select a valid image file.');
                $('#categoryImage').addClass('is-invalid');
                $(this).val('');
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                $('#imageError').text('Image size must be less than 2MB.');
                $('#categoryImage').addClass('is-invalid');
                $(this).val('');
                return;
            }
            
            $('#imageError').text('');
            $('#categoryImage').removeClass('is-invalid');
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
                $('#currentImage').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remove image button
    $('#removeImageBtn').on('click', function() {
        $('#categoryImage').val('');
        $('#imagePreview').hide();
        $('#previewImg').attr('src', '');
    });
    
    // Initialize Select2 for modal parent category dropdown
    function initializeParentCategorySelect2() {
        let select = $('#parentCategory');
        
        // Destroy existing Select2 instance if it exists
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        
        // Initialize Select2 with search
        select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select Parent Category',
            allowClear: true,
            dropdownParent: $('#categoryModal'),
            language: {
                noResults: function() {
                    return "No categories found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
        
        // Check for max depth warning when selection changes
        select.on('change', function() {
            let selectedValue = $(this).val();
            let selectedOption = $(this).find('option[value="' + selectedValue + '"]');
            let warningDiv = $('#maxDepthWarning');
            
            if (selectedValue && selectedOption.length > 0) {
                // Check if the selected category is disabled (at max depth)
                if (selectedOption.prop('disabled')) {
                    warningDiv.show();
                } else {
                    warningDiv.hide();
                }
            } else {
                warningDiv.hide();
            }
        });
    }

    // Load all available ProductAttributes
    function loadAvailableAttributes(selectedIds = [], callback = null) {
        $.ajax({
            url: '{{ route("categories.attributes") }}',
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    let select = $('#productAttributes');
                    
                    // Destroy existing Select2 if it exists
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }
                    
                    // Clear and populate options
                    select.empty();
                    
                    response.data.forEach(function(attr) {
                        let isSelected = selectedIds.includes(attr.id);
                        let option = $('<option></option>')
                            .attr('value', attr.id)
                            .attr('selected', isSelected)
                            .text(attr.name + (attr.is_variation ? ' (Variant)' : ' (Static)'));
                        select.append(option);
                    });
                    
                    // Initialize Select2
                    initializeAttributesSelect2();
                    
                    // Set selected values after initialization
                    if (selectedIds.length > 0) {
                        select.val(selectedIds).trigger('change');
                    }
                    
                    // Execute callback if provided
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading attributes:', xhr);
            }
        });
    }

    // Initialize Select2 for attributes multi-select
    function initializeAttributesSelect2() {
        let select = $('#productAttributes');
        
        // Destroy existing Select2 instance if it exists
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        
        // Initialize Select2 with search
        select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select product attributes',
            allowClear: true,
            dropdownParent: $('#categoryModal'),
            language: {
                noResults: function() {
                    return "No attributes found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
    }

    // Display inherited attributes
    function displayInheritedAttributes(inheritedAttributes) {
        let container = $('#inheritedAttributesContainer');
        let list = $('#inheritedAttributesList');
        
        if (inheritedAttributes && inheritedAttributes.length > 0) {
            list.empty();
            inheritedAttributes.forEach(function(attr) {
                let badge = $('<span class="badge bg-info me-2 mb-2"></span>')
                    .html('<i class="fas fa-lock me-1"></i>' + attr.name + (attr.is_variation ? ' (Variant)' : ' (Static)'));
                list.append(badge);
            });
            container.show();
        } else {
            container.hide();
        }
    }

    // Open Modal for Add
    $('#addCategoryBtn').on('click', function() {
        $('#modalTitle').text('Add Category');
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#isActive').prop('checked', true);
        $('#imagePreview').hide();
        $('#currentImage').hide();
        clearModalAlerts();
        
        // Hide inherited attributes for new category
        $('#inheritedAttributesContainer').hide();
        
        // Load parent categories and attributes
        loadParentCategories(null, null, function() {
            // Initialize Select2 after categories are loaded
            setTimeout(function() {
                // Destroy existing Select2 instance if it exists
                if ($('#parentCategory').hasClass('select2-hidden-accessible')) {
                    $('#parentCategory').select2('destroy');
                }
                initializeParentCategorySelect2();
            }, 100);
        });
        
        // Load available attributes (no pre-selection for new category)
        loadAvailableAttributes([], function() {
            $('#categoryModal').modal('show');
        });
    });
    
    // Open Modal for Edit
    // Handle category collapse/expand toggle
    $(document).on('click', '.category-toggle-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let btn = $(this);
        let categoryId = btn.data('category-id');
        let icon = btn.find('i');
        let parentRow = btn.closest('tr');
        let categoryIdStr = String(categoryId);
        
        // Find all direct child rows for this category
        let directChildRows = $('#categoriesTableBody').find(`tr.category-child-row[data-parent-id="${categoryIdStr}"]`);
        
        // Check if currently expanded or collapsed
        let isExpanded = directChildRows.length > 0 && !directChildRows.first().hasClass('d-none');
        
        if (directChildRows.length > 0) {
            if (isExpanded) {
                // Collapse: recursively hide all descendant rows
                function hideDescendants(parentId) {
                    let childRows = $('#categoriesTableBody').find(`tr.category-child-row[data-parent-id="${String(parentId)}"]`);
                    childRows.addClass('d-none');
                    // Also hide toggle icon state
                    childRows.each(function() {
                        let childId = $(this).data('id');
                        let childBtn = $(`.category-toggle-btn[data-category-id="${childId}"]`);
                        if (childBtn.length) {
                            childBtn.removeClass('expanded');
                        }
                        // Recursively hide this child's descendants
                        if ($(this).data('has-children') == '1') {
                            hideDescendants(childId);
                        }
                    });
                }
                hideDescendants(categoryId);
                btn.removeClass('expanded');
            } else {
                // Expand: show direct child rows only (children will be shown when their parent is expanded)
                directChildRows.removeClass('d-none');
                btn.addClass('expanded');
            }
        }
    });

    $(document).on('click', '.edit-category', function(e) {
        e.preventDefault();
        // Try multiple methods to get the ID
        let id = $(this).data('id') || $(this).attr('data-id');
        
        // If id is not found from data-id, try to get it from the closest row or button
        if (!id) {
            // Try to get from closest row
            let row = $(this).closest('tr');
            if (row.length) {
                id = row.data('id') || row.attr('data-id');
            }
        }
        
        // If still not found, try to get from the popover trigger button
        if (!id) {
            // Find the button that triggered this popover
            let clickedElement = $(this);
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance && popoverInstance.tip) {
                    // Check if the clicked element is inside this popover
                    if ($(popoverInstance.tip).find(clickedElement).length || 
                        $(popoverInstance.tip)[0] === clickedElement.closest('.popover')[0]) {
                        id = $(this).data('category-id') || $(this).attr('data-category-id');
                        return false; // break the loop
                    }
                }
            });
        }
        
        if (!id) {
            showSuccessAlert('Error: Category ID not found', 'danger');
            console.error('Category ID not found for edit action. Element:', this, 'All data:', $(this).data());
            return;
        }
        
        console.log('Editing category with ID:', id);
        
        $('#modalTitle').text('Edit Category');
        clearModalAlerts();
        
        $.ajax({
            url: '{{ route("categories.edit") }}',
            type: 'GET',
            data: { id: id },
            success: function(response) { 
                
                if(response.success) {
                    let category = response.data; 
                    
                    $('#categoryId').val(category.id);
                    $('#categoryName').val(category.name);
                    $('#categoryDescription').val(category.description);
                    // Don't set parent category value here - wait until options are loaded
                    $('#sortOrder').val(category.sort_order || 0);
                    $('#isActive').prop('checked', category.is_active);
                    
                    // Handle image display
                    $('#categoryImage').val('');
                    $('#imagePreview').hide();
                    if (category.image) {
                        $('#currentImg').attr('src', '{{ asset("storage") }}/' + category.image);
                        $('#currentImage').show();
                    } else {
                        $('#currentImage').hide();
                    }
                     
                    // Store parent_id for later use
                    let parentCategoryId = category.parent_id || null;
                    console.log('Editing category - Parent ID:', parentCategoryId);
                    
                    // Load parent categories
                    // Exclude current category, include current parent (if exists), and use callback to set parent category selection after dropdown is populated
                    loadParentCategories(category.id, category.parent_id, function() {
                        // Initialize Select2 after categories are loaded
                        setTimeout(function() {
                            // Destroy existing Select2 instance if it exists
                            if ($('#parentCategory').hasClass('select2-hidden-accessible')) {
                                $('#parentCategory').select2('destroy');
                            }
                            initializeParentCategorySelect2();
                            
                            // Set the value after Select2 is initialized and options are loaded
                            // Use a small delay to ensure Select2 is fully ready and options are populated
                            setTimeout(function() {
                                console.log('Setting parent category value to:', parentCategoryId);
                                if (parentCategoryId) {
                                    // Convert to string for consistent matching
                                    let parentIdStr = String(parentCategoryId);
                                    
                                    // Check if the option exists before setting
                                    let optionExists = $('#parentCategory option[value="' + parentIdStr + '"]').length > 0;
                                    console.log('Parent option exists:', optionExists, 'Looking for value:', parentIdStr);
                                    console.log('Available options:', $('#parentCategory option').map(function() { return $(this).val(); }).get());
                                    
                                    if (optionExists) {
                                        $('#parentCategory').val(parentIdStr).trigger('change');
                                        console.log('Parent category value set successfully');
                                    } else {
                                        console.warn('Parent category option not found in dropdown. Parent ID:', parentIdStr);
                                        // Try to find by converting to number
                                        let parentIdNum = parseInt(parentCategoryId);
                                        if ($('#parentCategory option[value="' + parentIdNum + '"]').length > 0) {
                                            $('#parentCategory').val(parentIdNum).trigger('change');
                                            console.log('Parent category value set using numeric value');
                                        }
                                    }
                                } else {
                                    $('#parentCategory').val('').trigger('change');
                                }
                            }, 200);
                        }, 100);
                    });
                    
                    // Load available attributes with pre-selected values
                    let selectedAttributeIds = category.product_attribute_ids || category.assigned_product_attributes || [];
                    loadAvailableAttributes(selectedAttributeIds, function() {
                        // Display inherited attributes
                        if (category.inherited_product_attributes && category.inherited_product_attributes.length > 0) {
                            displayInheritedAttributes(category.inherited_product_attributes);
                        } else {
                            $('#inheritedAttributesContainer').hide();
                        }
                        
                        $('#categoryModal').modal('show');
                    });
                } else {
                    console.error('Edit response not successful:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('=== EDIT AJAX ERROR ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                console.error('Request ID:', id);
                
                let message = 'Error loading category data';
                if (xhr.status === 404) {
                    message = 'Category not found. It may have been deleted.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else {
                    message += ': ' + error;
                }
                
                showModalAlert(message, 'danger');
            }
        });
    });

    // Clean up Select2 when modal is closed
    $('#categoryModal').on('hidden.bs.modal', function() {
        let parentSelect = $('#parentCategory');
        if (parentSelect.hasClass('select2-hidden-accessible')) {
            parentSelect.select2('destroy');
        }
        
        let attrSelect = $('#productAttributes');
        if (attrSelect.hasClass('select2-hidden-accessible')) {
            attrSelect.select2('destroy');
        }
    });

    // Save Category (Create or Update)
    $('#saveCategoryBtn').on('click', function() {
        let id = $('#categoryId').val();
        let url = id ? '{{ route("categories.update") }}' : '{{ route("categories.store") }}';
        
        // Create FormData for file upload
        let formData = new FormData();
        formData.append('id', id);
        formData.append('name', $('#categoryName').val());
        formData.append('description', $('#categoryDescription').val());
        formData.append('parent_id', $('#parentCategory').val() || '');
        formData.append('sort_order', $('#sortOrder').val());
        formData.append('is_active', $('#isActive').is(':checked') ? 1 : 0);
        
        // Handle product attributes
        let selectedAttributes = $('#productAttributes').val() || [];
        formData.append('product_attribute_ids', JSON.stringify(selectedAttributes));
        
        // Append image file if selected
        let imageFile = $('#categoryImage')[0].files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        // Show loading
        $('#saveBtnText').addClass('d-none');
        $('#saveBtnSpinner').removeClass('d-none');
        $('#saveCategoryBtn').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#categoryModal').modal('hide');
                    // Clear parent categories cache to fetch latest data
                    clearParentCategoriesCache();
                    // Refresh data
                    currentPage = 1; // Reset to first page
                    loadCategories();
                    showSuccessAlert(response.message);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors - show in invalid-feedback and as toast
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    
                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');
                    
                    // Mark fields as invalid and collect error messages
                    if(errors.name) {
                        $('#categoryName').addClass('is-invalid');
                        $('#nameError').text(errors.name[0]);
                        errorMessages.push(errors.name[0]);
                    }
                    if(errors.parent_id) {
                        // Mark Select2 field as invalid
                        let parentSelect = $('#parentCategory');
                        parentSelect.addClass('is-invalid');
                        $('#parentCategoryError').text(errors.parent_id[0]);
                        errorMessages.push(errors.parent_id[0]);
                        // Show modal alert for parent_id errors
                        showModalAlert(errors.parent_id[0], 'danger');
                        // Update Select2 container styling
                        if (parentSelect.hasClass('select2-hidden-accessible')) {
                            parentSelect.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                        }
                    }
                    if(errors.is_active) {
                        errorMessages.push(errors.is_active[0]);
                        showModalAlert(errors.is_active[0], 'danger');
                    }
                    
                    // Show toast notification for validation errors
                    if(errorMessages.length > 0) {
                        let errorMessage = errorMessages.join('<br>');
                        showToast('error', errorMessage);
                    }
                } else {
                    let errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    showModalAlert(errorMessage, 'danger');
                    showToast('error', errorMessage);
                }
            },
            complete: function() {
                $('#saveBtnText').removeClass('d-none');
                $('#saveBtnSpinner').addClass('d-none');
                $('#saveCategoryBtn').prop('disabled', false);
            }
        });
    });

    // Delete Category
    $(document).on('click', '.delete-category', function(e) {
        e.preventDefault();
        // Try multiple methods to get the ID
        let id = $(this).data('id') || $(this).attr('data-id');
        
        // If id is not found from data-id, try to get it from the closest row or button
        if (!id) {
            // Try to get from closest row
            let row = $(this).closest('tr');
            if (row.length) {
                id = row.data('id') || row.attr('data-id');
            }
        }
        
        // If still not found, try to get from the popover trigger button
        if (!id) {
            // Find the button that triggered this popover
            let clickedElement = $(this);
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance && popoverInstance.tip) {
                    // Check if the clicked element is inside this popover
                    if ($(popoverInstance.tip).find(clickedElement).length || 
                        $(popoverInstance.tip)[0] === clickedElement.closest('.popover')[0]) {
                        id = $(this).data('category-id') || $(this).attr('data-category-id');
                        return false; // break the loop
                    }
                }
            });
        }
        
        if (!id) {
            showSuccessAlert('Error: Category ID not found', 'danger');
            console.error('Category ID not found for delete action. Element:', this, 'All data:', $(this).data());
            return;
        }
        
        console.log('Deleting category with ID:', id);
        
        if(confirm('Are you sure you want to delete this category?')) {
            $.ajax({
                url: '{{ route("categories.delete") }}',
                type: 'POST',
                data: { 
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        // Clear parent categories cache to fetch latest data
                        clearParentCategoriesCache();
                        // Remove from current view
                        if (currentView === 'table') {
                            $(`tr[data-id="${id}"]`).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else if (currentView === 'hierarchy') {
                            $(`.category-tree-item:has([data-id="${id}"])`).fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                        
                        // Refresh data to update stats and ensure consistency
                        if (currentView === 'hierarchy') {
                            loadCategoriesHierarchy();
                        } else {
                            currentPage = 1; // Reset to first page
                            loadCategories();
                        }
                        showSuccessAlert(response.message);
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to delete category';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        message = 'Category not found. It may have already been deleted.';
                    } else if (xhr.status === 422) {
                        message = xhr.responseJSON?.message || 'Cannot delete category with subcategories';
                    }
                    
                    let alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;" role="alert">
                            <strong>Error!</strong> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('body').append(alertHtml);
                    
                    setTimeout(function() {
                        $('.alert-danger').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 4000);
                }
            });
        }
    });

    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        
        let page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadCategories();
        }
    });
    
    // Search functionality with debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
                        loadCategories();
        }, 500);
    });

    // Status filter change event
    $('#statusFilter').on('change', function() {
        currentPage = 1;
                        loadCategories();
    });

    // Select All Checkboxes
    $('#selectAllCategories').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.category-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox change
    $(document).on('change', '.category-checkbox', function() {
        let totalCheckboxes = $('.category-checkbox').length;
        let checkedCheckboxes = $('.category-checkbox:checked').length;
        $('#selectAllCategories').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateBulkDeleteButton();
    });
    
    function updateBulkDeleteButton() {
        const checkedCount = $('.category-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteCategoriesBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteCategoriesBtn').addClass('d-none');
        }
    }
    
    function bulkDeleteCategories() {
        const checkedBoxes = $('.category-checkbox:checked');
        if (checkedBoxes.length === 0) {
            showSuccessAlert('Please select at least one category to delete', 'warning');
            return;
        }
        
        const ids = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        
        if (!confirm(`Are you sure you want to delete ${ids.length} category(ies)? Categories with subcategories cannot be deleted.`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("categories.bulk-delete") }}',
            type: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Clear parent categories cache to fetch latest data
                    clearParentCategoriesCache();
                    showSuccessAlert(response.message);
                    // Refresh the current view
                    if (currentView === 'hierarchy') {
                        loadCategoriesHierarchy();
                    } else {
                        currentPage = 1;
                        loadCategories();
                    }
                    $('#selectAllCategories').prop('checked', false);
                    updateBulkDeleteButton();
                } else {
                    let errorMsg = response.message || 'Failed to delete categories';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '<br>' + response.errors.join('<br>');
                    }
                    showSuccessAlert(errorMsg, 'danger');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to delete categories';
                showSuccessAlert(message, 'danger');
            }
        });
    }

    // Bulk delete button click handler
    $(document).on('click', '#bulkDeleteCategoriesBtn', function(e) {
        e.preventDefault();
        bulkDeleteCategories();
    });

    // Handle "Add Child" click from popover
    $(document).on('click', '.popover .add-child-category', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Get parent category ID and name
        let parentId = $(this).data('id') || $(this).attr('data-id');
        let parentName = $(this).data('name') || '';
        
        // If not found, try to get from the popover trigger button
        if (!parentId) {
            let clickedElement = $(this);
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance && popoverInstance.tip) {
                    if ($(popoverInstance.tip).find(clickedElement).length || 
                        $(popoverInstance.tip)[0] === clickedElement.closest('.popover')[0]) {
                        parentId = $(this).data('category-id') || $(this).attr('data-category-id');
                        parentName = $(this).data('category-name') || '';
                        return false;
                    }
                }
            });
        }
        
        if (!parentId) {
            showSuccessAlert('Error: Parent category ID not found', 'danger');
            console.error('Parent category ID not found for add child action');
            return;
        }
        
        // Close the popover
        setTimeout(function() {
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance && popoverInstance.tip && $(popoverInstance.tip).is(':visible')) {
                    popoverInstance.hide();
                }
            });
        }, 100);
        
        // Open modal with parent pre-selected
        openAddChildModal(parentId, parentName);
    });

    // Function to open modal for adding a child category
    function openAddChildModal(parentId, parentName) {
        // Reset form
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#modalTitle').text('Add Child Category');
        clearModalAlerts();
        
        // Hide image previews
        $('#imagePreview').hide();
        $('#currentImage').hide();
        $('#categoryImage').val('');
        
        // Hide inherited attributes for new category
        $('#inheritedAttributesContainer').hide();
        
        // Load parent categories and set the selected parent
        loadParentCategories(null, null, function() {
            // Initialize Select2 after categories are loaded
            setTimeout(function() {
                // Destroy existing Select2 instance if it exists
                if ($('#parentCategory').hasClass('select2-hidden-accessible')) {
                    $('#parentCategory').select2('destroy');
                }
                initializeParentCategorySelect2();
                
                // Set the parent category value
                $('#parentCategory').val(parentId).trigger('change');
            }, 100);
        });
        
        // Load available attributes (no pre-selection for new category)
        loadAvailableAttributes([], function() {
            $('#categoryModal').modal('show');
        });
    }

    // Close popovers when action is clicked (the existing edit/delete handlers will handle the actions)
    $(document).on('click', '.popover .edit-category, .popover .delete-category', function(e) {
        // Close the popover after a short delay to allow the click event to propagate
        setTimeout(function() {
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance && popoverInstance.tip && $(popoverInstance.tip).is(':visible')) {
                    popoverInstance.hide();
                }
            });
        }, 100);
    });

    // Close popovers when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.category-actions-btn, .popover').length) {
            $('.category-actions-btn').each(function() {
                let popoverInstance = bootstrap.Popover.getInstance(this);
                if (popoverInstance) {
                    popoverInstance.hide();
                }
            });
        }
    });

    // Status Toggle - Badge click handler
    $(document).on('click', '.status-badge-label', function(e) {
        e.preventDefault();
        let label = $(this);
        let categoryId = label.data('id');
        let toggle = label.siblings('.status-toggle');
        let isActive = toggle.is(':checked') ? 0 : 1; // Toggle the value
        
        // Update checkbox state
        toggle.prop('checked', isActive === 1);
        
        // Disable badge while updating
        label.css('pointer-events', 'none');
        
        $.ajax({
            url: '{{ route("categories.updateStatus") }}',
            type: 'POST',
            data: {
                id: categoryId,
                is_active: isActive
            },
            success: function(response) {
                if(response.success) {
                    // Update badge in current view
                    if (currentView === 'table') {
                        let badge = label.find('.badge');
                        if(response.data.is_active) {
                            badge.removeClass('badge-sa-secondary').addClass('badge-sa-success').text('Active');
                        } else {
                            badge.removeClass('badge-sa-success').addClass('badge-sa-secondary').text('Inactive');
                        }
                    } else if (currentView === 'hierarchy') {
                        // Update badge in hierarchy view
                        let node = $(`.category-tree-node:has([data-id="${categoryId}"])`);
                        let badge = node.find('.badge');
                        if(response.data.is_active) {
                            badge.removeClass('bg-secondary').addClass('bg-success').html('<i class="fas fa-check-circle me-1"></i>Active');
                        } else {
                            badge.removeClass('bg-success').addClass('bg-secondary').html('<i class="fas fa-times-circle me-1"></i>Inactive');
                        }
                    }
                    
                    // Refresh data to update stats
                    loadCategories();
                    showSuccessAlert('Status updated successfully');
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Failed to update status';
                alert('Error: ' + message);
                // Revert toggle state
                toggle.prop('checked', !toggle.is(':checked'));
            },
            complete: function() {
                // Re-enable badge
                label.css('pointer-events', 'auto');
            }
        });
    });

    // Initial Load
    loadCategories();
});
</script>
@endpush
@endsection

