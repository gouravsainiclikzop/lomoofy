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
    .category-actions-popover .manage-children-category,
    .category-actions-popover .add-child-category {
        font-weight: 500;
    }
    .category-actions-popover .manage-children-category:hover,
    .category-actions-popover .add-child-category:hover {
        background-color: #e7f3ff;
    }
    
    .child-category-row {
        background-color: #f8f9fa;
        transition: all 0.2s;
    }
    
    .child-category-row:hover {
        background-color: #e9ecef;
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
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 5px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    
    .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #f5c000;
        margin: 0;
        flex-shrink: 0;
    }
    
    .stat-card-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
        text-align: left;
        flex: 1;
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
    
    .category-table-image {
        transition: opacity 0.3s ease;
    }
    
    .category-table-image[loading="lazy"] {
        background-color: #f0f0f0;
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
                        <select class="form-select  " id="statusFilter">
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
            
        </div>
    </div>
</div>

<!-- Subcategories Modal -->
<div class="modal fade" id="subcategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subcategories of <span id="subcategoriesModalCategoryName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subcategoriesList" class="list-group" style="max-height: 500px; overflow-y: auto;">
                    <!-- Subcategories will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Children Categories Modal -->
<div class="modal fade" id="manageChildrenCategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Children Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="manageChildrenCategoriesAlertContainer"></div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Parent Category:</label>
                    <span id="manageChildrenCategoriesParentName" class="badge bg-primary fs-6"></span>
                </div>
                
                <!-- Children Categories Section (Existing + New) -->
                <div class="mb-3">
                    <h6 class="mb-3">
                        <i class="fas fa-list me-2"></i>Children Categories
                        <small class="text-muted ms-2">(Edit existing or add new)</small>
                    </h6>
                    <div id="multipleChildCategoriesRowsContainer">
                        <!-- Existing and new rows will be added here dynamically -->
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="addMoreChildCategoryRow">
                            <i class="fas fa-plus"></i> Add New Category
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveMultipleChildCategoriesBtn">
                    <span id="saveMultipleBtnText">Save All Changes</span>
                    <span id="saveMultipleBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
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

    let currentView = 'table'; // Only table view
    let allCategoriesData = []; // Store all categories for stats
    
    // Function to get next sort order for root categories (no parent)
    function getNextRootSortOrder() {
        if (!allCategoriesData || allCategoriesData.length === 0) {
            return 1;
        }
        
        // Get all root categories (no parent_id)
        const rootCategories = allCategoriesData.filter(cat => !cat.parent_id);
        
        if (rootCategories.length === 0) {
            return 1;
        }
        
        // Find maximum sort_order
        const maxSortOrder = Math.max(...rootCategories.map(cat => cat.sort_order || 0));
        return maxSortOrder + 1;
    }
    
    // Function to get next sort order for child categories (with parent)
    function getNextChildSortOrder(parentId) {
        if (!allCategoriesData || allCategoriesData.length === 0) {
            return 1;
        }
        
        // Get all children of the parent
        const childCategories = allCategoriesData.filter(cat => cat.parent_id == parentId);
        
        if (childCategories.length === 0) {
            return 1;
        }
        
        // Find maximum sort_order
        const maxSortOrder = Math.max(...childCategories.map(cat => cat.sort_order || 0));
        return maxSortOrder + 1;
    }
    
    // Function to get next sort order from existing children in modal
    function getNextSortOrderFromModalRows(parentId) {
        const existingRows = $(`.child-category-row[data-is-existing="1"]`);
        let maxSortOrder = 0;
        
        existingRows.each(function() {
            const sortOrder = parseInt($(this).find('.child-category-sort-order').val()) || 0;
            if (sortOrder > maxSortOrder) {
                maxSortOrder = sortOrder;
            }
        });
        
        // Also check allCategoriesData for this parent
        const childCategories = allCategoriesData.filter(cat => cat.parent_id == parentId);
        childCategories.forEach(cat => {
            const sortOrder = cat.sort_order || 0;
            if (sortOrder > maxSortOrder) {
                maxSortOrder = sortOrder;
            }
        });
        
        return maxSortOrder + 1;
    }

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
    
    // Function to recursively get all child categories
    function getAllChildren(category) {
        let allChildren = [];
        if (category.children && category.children.length > 0) {
            category.children.forEach(function(child) {
                allChildren.push(child);
                allChildren = allChildren.concat(getAllChildren(child));
            });
        }
        return allChildren;
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

    // Load Categories (no pagination - all categories loaded at once)
    function loadCategories() {
        
        let search = $('#tableSearch').val();
        let status = $('#statusFilter').val();
        let url = '{{ route("categories.data") }}';
        let params = new URLSearchParams();
        
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
                    allCategoriesData = response.data;
                    renderCategories(response.data);
                    updateStats(response.data);
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
                    updateStats(response.data);
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
            ? `<img src="${imageUrl}" alt="${category.name}" class="category-tree-image" loading="lazy" decoding="async">`
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
            collapseIcon = `<button type="button" class="btn btn-sm btn-link p-0 me-2 category-toggle-btn" data-category-id="${category.id}" style="text-decoration: none; min-width: 20px;">
                <i class="fas fa-chevron-right"></i>
            </button>`;
        } else {
            collapseIcon = '<span class="me-2" style="display: inline-block; width: 20px;"></span>';
        }
        
        // Count only immediate (first level) children, not all nested children
        let totalChildrenCount = hasChildren && category.children ? category.children.length : 0;
        let categoryCountBadge = '';
        if (totalChildrenCount > 0) {
            categoryCountBadge = `<span class="badge bg-info ms-2 category-count-badge" style="cursor: pointer;" data-category-id="${category.id}" data-category-name="${escapeHtml(category.name)}" title="Click to view all ${totalChildrenCount} subcategories">${totalChildrenCount}</span>`;
        }
        
        let nameCell = `
            <div class="category-name-cell" style="padding-left: ${indentPx}px;">
                ${collapseIcon}
                <a href="#" class="text-reset fw-medium">${category.name || '-'}</a>
                ${categoryCountBadge}
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
            ? `<img src="${imageUrl}" alt="${category.name}" class="img-thumbnail category-table-image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" loading="lazy" decoding="async">`
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
        
        // Child rows are hidden by default (collapsed)
        let rowClass = level > 0 ? 'category-child-row d-none' : '';
        let parentIdValue = category.parent_id ? String(category.parent_id) : '';
        
        // Check if category can have children (max depth is 4, so level 0, 1, 2, or 3 can have children)
        let canHaveChildren = level < 3; // Level 0, 1, 2, or 3 can have children (max level is 4)
        
        // Build popover content - conditionally show "Add Child" option
        let addChildOption = '';
        if (canHaveChildren) {
            addChildOption = `<a href="#" class="manage-children-category d-block py-2 px-3 text-decoration-none text-primary" data-id="${category.id}" data-name="${category.name}"><i class="fas fa-sitemap me-2"></i>Manage Children</a><hr class="my-1">`;
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
                    $(popoverTip).find('.manage-children-category').attr('data-id', categoryId).attr('data-name', categoryName);
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
    function updateStats(categories) {
        // Flatten nested structure if needed
        let flatCategories = categories;
        if (categories.length > 0 && categories[0].children !== undefined) {
            flatCategories = flattenCategories(categories);
        }
        
        let total = flatCategories.length;
        let active = flatCategories.filter(c => c.is_active).length;
        let root = flatCategories.filter(c => !c.parent_id).length;
        let totalProducts = flatCategories.reduce((sum, c) => sum + (c.products_count || 0), 0);
        
        $('#totalCategories').text(total);
        $('#activeCategories').text(active);
        $('#rootCategories').text(root);
        $('#totalProducts').text(totalProducts);
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
        
        // Set next sort order automatically
        const nextSortOrder = getNextRootSortOrder();
        $('#sortOrder').val(nextSortOrder);
        
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
                
                // Update sort order when parent changes (for child categories)
                $('#parentCategory').on('change', function() {
                    const parentId = $(this).val();
                    if (parentId) {
                        const nextSortOrder = getNextChildSortOrder(parentId);
                        $('#sortOrder').val(nextSortOrder);
                    } else {
                        const nextSortOrder = getNextRootSortOrder();
                        $('#sortOrder').val(nextSortOrder);
                    }
                });
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
    
    // Handle category count badge click - show all subcategories in modal
    $(document).on('click', '.category-count-badge', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let badge = $(this);
        let categoryId = badge.data('category-id');
        let categoryName = badge.data('category-name');
        
        // Find the category in allCategoriesData
        function findCategoryInTree(cats, id) {
            for (let i = 0; i < cats.length; i++) {
                if (cats[i].id == id) {
                    return cats[i];
                }
                if (cats[i].children && cats[i].children.length > 0) {
                    let found = findCategoryInTree(cats[i].children, id);
                    if (found) return found;
                }
            }
            return null;
        }
        
        let category = findCategoryInTree(allCategoriesData, categoryId);
        
        // If category not found in allCategoriesData, try to get children from table rows
        let directChildren = [];
        if (category && category.children && category.children.length > 0) {
            directChildren = category.children;
        } else {
            // Fallback: Get children from table rows
            let categoryIdStr = String(categoryId);
            let childRows = $('#categoriesTableBody').find(`tr.category-child-row[data-parent-id="${categoryIdStr}"]`);
            
            childRows.each(function() {
                let row = $(this);
                let childId = row.data('id');
                let childName = row.find('.category-name-cell a').text();
                let isActive = row.find('.status-toggle').is(':checked');
                let productCount = 0;
                
                // Try to get product count from the row
                let productCountText = row.find('.products-count-badge').text();
                if (productCountText) {
                    let match = productCountText.match(/\d+/);
                    if (match) {
                        productCount = parseInt(match[0]);
                    }
                }
                
                // Check if this child has children by looking at data attribute
                let hasChildren = row.data('has-children') == '1';
                let childrenCount = 0;
                
                // Count immediate children of this child
                if (hasChildren) {
                    let grandchildRows = $('#categoriesTableBody').find(`tr.category-child-row[data-parent-id="${String(childId)}"]`);
                    childrenCount = grandchildRows.length;
                }
                
                directChildren.push({
                    id: childId,
                    name: childName,
                    is_active: isActive,
                    products_count: productCount,
                    children: hasChildren ? [] : null // We don't need nested children for display
                });
            });
        }
        
        // Set modal title
        $('#subcategoriesModalCategoryName').text(categoryName);
        
        // Build list of subcategories
        let listHtml = '';
        if (directChildren.length === 0) {
            listHtml = '<div class="alert alert-info">No subcategories found.</div>';
        } else {
            directChildren.forEach(function(child) {
                let statusBadge = child.is_active 
                    ? '<span class="badge bg-success ms-2">Active</span>' 
                    : '<span class="badge bg-secondary ms-2">Inactive</span>';
                
                let childrenCount = (child.children && child.children.length > 0) ? child.children.length : 0;
                
                listHtml += `<div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${escapeHtml(child.name)}</strong>
                            ${statusBadge} 
                            ${childrenCount > 0 ? `<span class="badge bg-secondary ms-2">${childrenCount} subcategories</span>` : ''}
                        </div>
                    </div>
                </div>`;
            });
        }
        
        $('#subcategoriesList').html(listHtml);
        $('#subcategoriesModal').modal('show');
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
    
    // Clean up Select2 when multiple child categories modal is closed
    $('#addMultipleChildCategoriesModal').on('hidden.bs.modal', function() {
        $('.child-category-attributes').each(function() {
            try {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            } catch (e) {
                // Ignore errors if Select2 is not initialized
                console.log('Error destroying Select2, ignoring:', e);
            }
        });
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

    // Handle "Manage Children" click from popover
    $(document).on('click', '.popover .manage-children-category, .popover .add-child-category', function(e) {
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
        openManageChildrenModal(parentId, parentName);
    });

    // Multiple child categories row counter
    let childCategoryRowCounter = 0;
    
    // Store existing children data
    let existingChildrenData = [];
    
    // Function to create a new row for adding child category
    function createChildCategoryRow(index = null, parentId = null, parentName = null, isExisting = false, existingData = null) {
        const rowIndex = index !== null ? index : childCategoryRowCounter++;
        const rowId = `childCategoryRow_${rowIndex}`;
        const isEditMode = isExisting && existingData;
        
        // Get parent info from modal if not provided
        if (!parentId) {
            parentId = $('#manageChildrenCategoriesParentId').val() || '';
        }
        if (!parentName) {
            parentName = $('#manageChildrenCategoriesParentName').text() || '';
        }
        
        const categoryName = isEditMode ? escapeHtml(existingData.name) : '';
        // For new categories, get next sort order; for existing, use current sort order
        let sortOrder;
        if (isEditMode) {
            sortOrder = existingData.sort_order || 0;
        } else {
            // Get next sort order for this parent
            sortOrder = getNextSortOrderFromModalRows(parentId);
        }
        const categoryId = isEditMode ? existingData.id : '';
        const imagePreview = isEditMode && existingData.image ? `<img src="/storage/${existingData.image}" class="img-thumbnail mt-2" style="max-width: 70px; max-height: 70px;" loading="lazy" decoding="async">` : '';
        
        const rowHtml = `
            <div class="child-category-row mb-3 p-3 border rounded ${isEditMode ? 'existing-child-row' : 'new-child-row'}" data-row-index="${rowIndex}" data-category-id="${categoryId}" data-is-existing="${isExisting ? '1' : '0'}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm child-category-name" placeholder="Category name" value="${categoryName}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Parent Category</label>
                        <input type="text" class="form-control form-control-sm" value="${escapeHtml(parentName)}" readonly>
                        <input type="hidden" class="child-category-parent-id" value="${parentId}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Category Image</label>
                        <input type="file" class="form-control form-control-sm child-category-image" accept="image/*">
                        ${imagePreview}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Sort Order</label>
                        <div class="d-flex gap-2 align-items-end">
                            <input type="number" class="form-control form-control-sm child-category-sort-order" value="${sortOrder}" style="flex: 1;">
                            <div class="d-flex gap-1">
                                ${isEditMode ? `<button type="button" class="btn btn-sm btn-outline-danger delete-existing-child-row" data-category-id="${categoryId}" data-row-index="${rowIndex}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>` : ''}
                                <button type="button" class="btn btn-sm btn-outline-danger remove-child-category-row" data-row-index="${rowIndex}" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return rowHtml;
    }
    
    // Function to load existing children
    function loadExistingChildren(parentId) {
        $.ajax({
            url: '{{ route("categories.children") }}',
            type: 'GET',
            data: { parent_id: parentId },
            success: function(response) {
                if (response.success && response.data) {
                    existingChildrenData = response.data;
                    
                    // Clear existing rows first
                    $('#multipleChildCategoriesRowsContainer').empty();
                    childCategoryRowCounter = 0;
                    
                    // Add existing children as rows by default
                    if (response.data.length > 0) {
                        const parentName = $('#manageChildrenCategoriesParentName').text();
                        response.data.forEach(function(child) {
                            const rowIndex = childCategoryRowCounter++;
                            const row = createChildCategoryRow(rowIndex, parentId, parentName, true, child);
                            $('#multipleChildCategoriesRowsContainer').append(row);
                        });
                        updateRowNumbers();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading children:', xhr);
                showToast('error', 'Error loading children categories');
            }
        });
    }
    
    // Function to open modal for managing children categories
    function openManageChildrenModal(parentId, parentName) {
        // Store parent info
        $('#manageChildrenCategoriesParentName').text(parentName);
        if (!$('#manageChildrenCategoriesParentId').length) {
            $('<input>').attr({
                type: 'hidden',
                id: 'manageChildrenCategoriesParentId',
                value: parentId
            }).appendTo('#manageChildrenCategoriesModal .modal-body');
        } else {
            $('#manageChildrenCategoriesParentId').val(parentId);
        }
        
        // Clear and reset
        $('#multipleChildCategoriesRowsContainer').empty();
        childCategoryRowCounter = 0;
        $('#manageChildrenCategoriesAlertContainer').empty();
        
        // Load existing children
        loadExistingChildren(parentId);
        
        // Show modal
        $('#manageChildrenCategoriesModal').modal('show');
    }
    
    // Legacy function name for compatibility
    function openAddMultipleChildCategoriesModal(parentId, parentName) {
        openManageChildrenModal(parentId, parentName);
    }
    
    // Function to open modal for adding a child category (now opens manage children modal)
    function openAddChildModal(parentId, parentName) {
        openManageChildrenModal(parentId, parentName);
    }
    
    // Function to load attributes and initialize Select2 for all rows
    function loadAttributesForMultipleChildCategories(callback = null) {
        $.ajax({
            url: '{{ route("categories.attributes") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const attributes = response.data;
                    
                    // Update all attribute selects
                    $('.child-category-attributes-container').each(function() {
                        const container = $(this);
                        const rowIndex = container.data('row-index');
                        const selectId = `childCategoryAttributes_${rowIndex}`;
                        
                        // Check if Select2 is already initialized and destroy it safely
                        const existingSelect = container.find('.child-category-attributes');
                        if (existingSelect.length) {
                            try {
                                if (existingSelect.hasClass('select2-hidden-accessible')) {
                                    existingSelect.select2('destroy');
                                }
                            } catch (e) {
                                // Select2 might not be initialized, ignore error
                                console.log('Select2 not initialized for row', rowIndex);
                            }
                        }
                        
                        let optionsHtml = '';
                        attributes.forEach(function(attr) {
                            optionsHtml += `<option value="${attr.id}">${escapeHtml(attr.name)}</option>`;
                        });
                        
                        const selectHtml = `<select class="form-select form-select-sm child-category-attributes" id="${selectId}" multiple data-placeholder="Select attributes">${optionsHtml}</select>`;
                        container.html(selectHtml);
                        
                        // Initialize Select2 for the new select
                        const select = $(`#${selectId}`);
                        if (select.length) {
                            select.select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: 'Select attributes',
                                dropdownParent: $('#manageChildrenCategoriesModal'),
                                closeOnSelect: false
                            });
                        }
                    });
                    
                    if (callback) callback();
                }
            },
            error: function() {
                console.error('Error loading attributes');
                if (callback) callback();
            }
        });
    }
    
    // Function to initialize Select2 for a single row's attributes
    function initializeAttributesSelect2ForRow(rowIndex) {
        const container = $(`.child-category-attributes-container[data-row-index="${rowIndex}"]`);
        
        if (!container.length) {
            console.error('Container not found for row', rowIndex);
            return;
        }
        
        // Check if already initialized
        const existingSelect = container.find('.child-category-attributes');
        if (existingSelect.length && existingSelect.hasClass('select2-hidden-accessible')) {
            // Already initialized, skip
            return;
        }
        
        $.ajax({
            url: '{{ route("categories.attributes") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const attributes = response.data;
                    const selectId = `childCategoryAttributes_${rowIndex}`;
                    
                    // Safely destroy existing Select2 if it exists
                    if (existingSelect.length) {
                        try {
                            if (existingSelect.hasClass('select2-hidden-accessible')) {
                                existingSelect.select2('destroy');
                            }
                        } catch (e) {
                            // Select2 might not be initialized, ignore error
                            console.log('Select2 not initialized, continuing...');
                        }
                    }
                    
                    let optionsHtml = '';
                    attributes.forEach(function(attr) {
                        optionsHtml += `<option value="${attr.id}">${escapeHtml(attr.name)}</option>`;
                    });
                    
                    const selectHtml = `<select class="form-select form-select-sm child-category-attributes" id="${selectId}" multiple data-placeholder="Select attributes">${optionsHtml}</select>`;
                    container.html(selectHtml);
                    
                    // Initialize Select2
                    const newSelect = $(`#${selectId}`);
                    if (newSelect.length) {
                        newSelect.select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Select attributes',
                            dropdownParent: $('#manageChildrenCategoriesModal'),
                            closeOnSelect: false
                        });
                    }
                }
            },
            error: function() {
                console.error('Error loading attributes for row');
                // Show error message in container
                container.html('<div class="text-danger small">Error loading attributes</div>');
            }
        });
    }
    
    // Handle Add More button
    $(document).on('click', '#addMoreChildCategoryRow', function() {
        const parentId = $('#manageChildrenCategoriesParentId').val();
        const parentName = $('#manageChildrenCategoriesParentName').text();
        
        // Store the counter value before increment (which happens in createChildCategoryRow)
        const newRowIndex = childCategoryRowCounter;
        const newRow = createChildCategoryRow(null, parentId, parentName);
        
        $('#multipleChildCategoriesRowsContainer').append(newRow);
        
        // Update all row numbers after adding
        updateRowNumbers();
        
        // Set next sort order for the new row automatically
        const nextSortOrder = getNextSortOrderFromModalRows(parentId);
        const newRowElement = $(`.child-category-row[data-row-index="${newRowIndex}"]`);
        newRowElement.find('.child-category-sort-order').val(nextSortOrder);
    });
    
    // Handle Delete Existing Child button (from edit row)
    $(document).on('click', '.delete-existing-child-row', function() {
        const childId = $(this).data('category-id');
        const rowIndex = $(this).data('row-index');
        const row = $(`.child-category-row[data-row-index="${rowIndex}"]`);
        const childName = row.find('.child-category-name').val();
        
        if (!confirm(`Are you sure you want to delete "${childName}"? This action cannot be undone.`)) {
            return;
        }
        
        // Delete via API
        $.ajax({
            url: '{{ route("categories.delete") }}',
            type: 'POST',
            data: {
                id: childId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    row.remove();
                    updateRowNumbers();
                    // Reload children list
                    const parentId = $('#manageChildrenCategoriesParentId').val();
                    loadExistingChildren(parentId);
                    // Also reload main categories table
                    loadCategories();
                    showToast('success', 'Category deleted successfully');
                } else {
                    showToast('error', response.message || 'Failed to delete category');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to delete category';
                showToast('error', message);
            }
        });
    });
    
    // Function to update row numbers in all rows
    function updateRowNumbers() {
        let newRowIndex = 1;
        $('.child-category-row').each(function(index) {
            const row = $(this);
            const isExisting = row.data('is-existing') == '1';
            // if (isExisting) {
            //     row.find('h6').text('Existing Category');
            // } else {
            //     row.find('h6').text(`New Category ${newRowIndex}`);
            //     newRowIndex++;
            // }
        });
    }
    
    // Handle Remove row button
    $(document).on('click', '.remove-child-category-row', function() {
        const rowIndex = $(this).data('row-index');
        const row = $(`.child-category-row[data-row-index="${rowIndex}"]`);
        
        // Safely destroy Select2 if it exists
        const select = row.find('.child-category-attributes');
        if (select.length) {
            try {
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
            } catch (e) {
                // Ignore errors if Select2 is not initialized
                console.log('Error destroying Select2 on remove, ignoring:', e);
            }
        }
        
        row.remove();
        
        // Update row numbers
        updateRowNumbers();
    });
    
    // Function to open modal for adding a child category (now opens multiple child categories modal)
    function openAddChildModal(parentId, parentName) {
        openAddMultipleChildCategoriesModal(parentId, parentName);
    }

    // Save Multiple Child Categories
    $('#saveMultipleChildCategoriesBtn').on('click', function() {
        const rows = $('.child-category-row');
        const parentId = $('#manageChildrenCategoriesParentId').val();
        const categoriesToSave = [];
        const categoriesToUpdate = [];
        
        // Collect data from all rows
        let hasErrors = false;
        rows.each(function(index) {
            const row = $(this);
            const name = row.find('.child-category-name').val().trim();
            const sortOrder = row.find('.child-category-sort-order').val() || 0;
            const imageFile = row.find('.child-category-image')[0].files[0];
            const categoryId = row.data('category-id');
            const isExisting = row.data('is-existing') == '1';
            
            // Validate required fields
            if (!name) {
                row.find('.child-category-name').addClass('is-invalid');
                hasErrors = true;
            } else {
                row.find('.child-category-name').removeClass('is-invalid');
            }
            
            if (name) {
                const categoryData = {
                    name: name,
                    parent_id: parentId ? parseInt(parentId) : null,
                    sort_order: parseInt(sortOrder) || 0,
                    description: '', // Empty description
                    is_active: true, // Always active by default (boolean)
                    product_attribute_ids: [], // No attributes
                    imageFile: imageFile
                };
                
                // Check if this is an existing category (has ID and is marked as existing)
                if (isExisting && categoryId && categoryId !== '') {
                    categoryData.id = parseInt(categoryId);
                    categoriesToUpdate.push(categoryData);
                } else {
                    // New category - no ID or not marked as existing
                    categoriesToSave.push(categoryData);
                }
            }
        });
        
        if (hasErrors) {
            $('#manageChildrenCategoriesAlertContainer').html('<div class="alert alert-danger">Please fill in all required fields (Category Name).</div>');
            return;
        }
        
        if (categoriesToSave.length === 0 && categoriesToUpdate.length === 0) {
            $('#manageChildrenCategoriesAlertContainer').html('<div class="alert alert-warning">No changes to save.</div>');
            return;
        }
        
        // Show loading
        $('#saveMultipleBtnText').addClass('d-none');
        $('#saveMultipleBtnSpinner').removeClass('d-none');
        $('#saveMultipleChildCategoriesBtn').prop('disabled', true);
        
        // Function to convert file to base64
        function fileToBase64(file, callback) {
            if (!file) {
                callback(null);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                callback(e.target.result);
            };
            reader.onerror = function() {
                callback(null);
            };
            reader.readAsDataURL(file);
        }
        
        // Process all categories and convert images to base64
        // Combine all categories (new and existing) into a single array for bulk-sync
        let processedCount = 0;
        const totalToProcess = categoriesToSave.length + categoriesToUpdate.length;
        const processedCategories = []; // Single array for all categories
        let allProcessed = false;
        
        function processCategory(categoryData, isUpdate) {
            // Ensure is_active is a proper boolean
            const isActive = categoryData.is_active === true || categoryData.is_active === 1 || categoryData.is_active === '1' || categoryData.is_active === 'true';
            
            if (categoryData.imageFile) {
                fileToBase64(categoryData.imageFile, function(base64) {
                    const processedData = {
                        name: categoryData.name,
                        parent_id: categoryData.parent_id ? parseInt(categoryData.parent_id) : null,
                        sort_order: parseInt(categoryData.sort_order) || 0,
                        description: categoryData.description || '',
                        is_active: isActive, // Ensure boolean
                        product_attribute_ids: categoryData.product_attribute_ids || [],
                        image_base64: base64
                    };
                    
                    // Include ID only for updates (existing categories)
                    if (isUpdate && categoryData.id) {
                        processedData.id = parseInt(categoryData.id);
                    }
                    
                    processedCategories.push(processedData);
                    processedCount++;
                    checkIfAllProcessed();
                });
            } else {
                const processedData = {
                    name: categoryData.name,
                    parent_id: categoryData.parent_id ? parseInt(categoryData.parent_id) : null,
                    sort_order: parseInt(categoryData.sort_order) || 0,
                    description: categoryData.description || '',
                    is_active: isActive, // Ensure boolean
                    product_attribute_ids: categoryData.product_attribute_ids || []
                };
                
                // Include ID only for updates (existing categories)
                if (isUpdate && categoryData.id) {
                    processedData.id = parseInt(categoryData.id);
                }
                
                processedCategories.push(processedData);
                processedCount++;
                checkIfAllProcessed();
            }
        }
        
        function checkIfAllProcessed() {
            if (processedCount >= totalToProcess && !allProcessed) {
                allProcessed = true;
                // Use setTimeout to ensure this only runs once
                setTimeout(function() {
                    if (allProcessed) {
                        sendBulkRequests();
                    }
                }, 10);
            }
        }
        
        // Process all categories
        if (totalToProcess === 0) {
            $('#saveMultipleBtnText').removeClass('d-none');
            $('#saveMultipleBtnSpinner').addClass('d-none');
            $('#saveMultipleChildCategoriesBtn').prop('disabled', false);
            return;
        }
        
        // Process all categories (async image processing)
        categoriesToSave.forEach(function(cat) {
            processCategory(cat, false);
        });
        
        categoriesToUpdate.forEach(function(cat) {
            processCategory(cat, true);
        });
        
        // Send bulk sync request (single request for both create and update)
        let requestsSent = false;
        function sendBulkRequests() {
            // Prevent duplicate calls
            if (requestsSent) {
                return;
            }
            requestsSent = true;
            
            // Send single bulk-sync request (handles both create and update)
            if (processedCategories.length > 0) {
                $.ajax({
                    url: '{{ route("categories.bulk-sync") }}',
                    type: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({
                        categories: processedCategories
                    }),
                    success: function(response) {
                        handleBulkSyncResponse(response);
                    },
                    error: function(xhr) {
                        handleBulkSyncError(xhr);
                    }
                });
            }
        }
        
        function handleBulkSyncResponse(response) {
            $('#saveMultipleBtnText').removeClass('d-none');
            $('#saveMultipleBtnSpinner').addClass('d-none');
            $('#saveMultipleChildCategoriesBtn').prop('disabled', false);
            
            const createdCount = response.results?.created?.success?.length || 0;
            const updatedCount = response.results?.updated?.success?.length || 0;
            const createdFailed = response.results?.created?.failed?.length || 0;
            const updatedFailed = response.results?.updated?.failed?.length || 0;
            const totalFailed = createdFailed + updatedFailed;
            
            if (totalFailed === 0) {
                // Reload existing children (this will also add them as rows)
                const parentId = $('#manageChildrenCategoriesParentId').val();
                loadExistingChildren(parentId);
                
                // Reload main categories table
                loadCategories();
                
                // Show success message
                if (response.message) {
                    showToast('success', response.message);
                } else {
                    let message = '';
                    if (createdCount > 0 && updatedCount > 0) {
                        message = `Successfully created ${createdCount} and updated ${updatedCount} category(ies)`;
                    } else if (createdCount > 0) {
                        message = `Successfully created ${createdCount} category(ies)`;
                    } else if (updatedCount > 0) {
                        message = `Successfully updated ${updatedCount} category(ies)`;
                    }
                    showToast('success', message);
                }
            } else {
                let message = response.message || `Processed ${createdCount + updatedCount} category(ies), ${totalFailed} failed`;
                showToast('warning', message);
                
                // Still reload to show what was saved
                if (createdCount > 0 || updatedCount > 0) {
                    const parentId = $('#manageChildrenCategoriesParentId').val();
                    loadExistingChildren(parentId);
                    loadCategories();
                }
            }
        }
        
        function handleBulkSyncError(xhr) {
            $('#saveMultipleBtnText').removeClass('d-none');
            $('#saveMultipleBtnSpinner').addClass('d-none');
            $('#saveMultipleChildCategoriesBtn').prop('disabled', false);
            
            let message = 'Failed to save categories';
            if (xhr && xhr.responseJSON) {
                message = xhr.responseJSON.message || message;
                if (xhr.responseJSON.errors) {
                    const errorMessages = Object.values(xhr.responseJSON.errors).flat();
                    if (errorMessages.length > 0) {
                        message += ': ' + errorMessages[0];
                    }
                }
            }
            
            showToast('error', message);
        }
    });

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

