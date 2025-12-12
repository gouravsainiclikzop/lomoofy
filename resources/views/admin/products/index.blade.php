@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Products</h1>
            </div>
            <div class="col-auto d-flex flex-wrap gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteBtn" onclick="bulkDeleteProducts()">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importProductsModal">
                    <i class="fas fa-file-import"></i> Import Products
                </button>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4">
                        <label for="filterBrand" class="form-label">Brand</label>
                        <select class="form-select" id="filterBrand">
                            <option value="">All Brands</option>
                            <!-- Brands will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="hidden">Hidden</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label for="filterCategory" class="form-label">Category</label>
                        <select class="form-select" id="filterCategory">
                            <option value="">All Categories</option>
                            @php
                                $allCategories = \App\Models\Category::active()->with('parent')->get();
                                $categoriesById = $allCategories->keyBy('id');
                                function buildCategoryPath($category, $categoriesById) {
                                    $path = [];
                                    $current = $category;
                                    $maxDepth = 20;
                                    $depth = 0;
                                    while ($current && $depth < $maxDepth) {
                                        array_unshift($path, $current->name);
                                        if ($current->parent_id) {
                                            $current = $categoriesById->get($current->parent_id);
                                        } else {
                                            $current = null;
                                        }
                                        $depth++;
                                    }
                                    return implode(' > ', $path);
                                }
                            @endphp
                            @foreach($allCategories as $category)
                                <option value="{{ $category->id }}">{{ buildCategoryPath($category, $categoriesById) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th class="w-min" data-orderable="false">
                                <input type="checkbox" class="form-check-input m-0 fs-exact-16 d-block" aria-label="..." id="selectAll"/>
                            </th>
                            <th class="min-w-20x">Product</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Variants</th>
                            <th class="text-nowrap">Price Range</th>
                            <th>Units</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Created</th>
                            <th class="w-min" data-orderable="false"></th>
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

<!-- SEO Management Modal -->
<div class="modal fade" id="seoModal" tabindex="-1" aria-labelledby="seoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seoModalLabel">
                    <i class="fas fa-search me-2"></i>Manage SEO
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="seoForm">
                <div class="modal-body">
                    <input type="hidden" id="seoProductId" name="product_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="seoMetaTitle" class="form-label">SEO Title</label>
                            <input type="text" class="form-control" id="seoMetaTitle" name="meta_title" 
                                   placeholder="Enter SEO title (recommended: 50-60 characters)" maxlength="60"> 
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="seoMetaDescription" class="form-label">SEO Description</label>
                            <textarea class="form-control" id="seoMetaDescription" name="meta_description" 
                                      rows="2" placeholder="Enter SEO description (recommended: 150-160 characters)" maxlength="160"></textarea>
                            
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="seoMetadata" class="form-label">Raw Metadata / JSON-LD <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control font-monospace" id="seoMetadata" name="metadata" 
                                      rows="4" placeholder="Paste complete meta tags, OpenGraph data, JSON-LD structured data or other SEO snippets here..."></textarea>
                            
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save SEO Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Products Modal -->
<div class="modal fade" id="importProductsModal" tabindex="-1" aria-labelledby="importProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importProductsModalLabel">Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">
                    Start by downloading the templates below. Populate them with your product data, then upload the completed file. 
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title d-flex align-items-center gap-2">
                                    <i class="fas fa-table text-primary"></i> Main Templates
                                </h6>
                                <p class="small text-muted mb-3">Use these files to prepare your core product details.</p>
                                <div class="d-grid gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ asset('import-templates/products-template.xlsx') }}" download>
                                        <i class="fas fa-file-excel"></i> Download Excel Template
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm" href="{{ asset('import-templates/products-template.csv') }}" download>
                                        <i class="fas fa-file-csv"></i> Download CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title d-flex align-items-center gap-2">
                                    <i class="fas fa-layer-group text-primary"></i> Related Data
                                </h6>
                                <p class="small text-muted mb-3">Variant and image details live in separate optional sheets.</p>
                                <div class="d-grid gap-2">
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ asset('import-templates/product-variants-template.csv') }}" download>
                                        <i class="fas fa-random"></i> Variant CSV Template
                                    </a>
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ asset('import-templates/product-images-template.csv') }}" download>
                                        <i class="fas fa-image"></i> Image CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <form id="productImportForm" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="productImportFile" class="form-label">Upload Completed Template</label>
                        <input class="form-control" type="file" id="productImportFile" name="product_import_file" accept=".xlsx,.csv" required>
                        <div class="form-text">
                            Upload the filled `products-template.xlsx` workbook (includes Products, Variants, Images sheets) or the CSV equivalent.
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            Ensure that referenced brands, categories, attributes, and images already exist in the system. Use slugs or IDs that match your current database to avoid validation errors during import.
                        </div>
                    </div>
                </form>

                <div class="alert alert-secondary d-none mt-4" id="productImportSummary"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="productImportForm" id="startImportButton">
                    <span class="import-button-label"><i class="fas fa-play"></i> Start Import</span>
                    <span class="import-button-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Importing...
                    </span>
                </button>
            </div>
            
            <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-download text-success"></i> Export Existing Data
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Export your current data to CSV/Excel for editing and re-importing.</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <a href="{{ route('exports.brands', ['format' => 'csv']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-csv"></i> Export Brands (CSV)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.brands', ['format' => 'xlsx']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-excel"></i> Export Brands (Excel)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.categories', ['format' => 'csv']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-csv"></i> Export Categories (CSV)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.categories', ['format' => 'xlsx']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-excel"></i> Export Categories (Excel)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.products', ['format' => 'csv']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-csv"></i> Export Products (CSV)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.products', ['format' => 'xlsx']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-excel"></i> Export Products (Excel)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.variants', ['format' => 'csv']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-csv"></i> Export Variants (CSV)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('exports.variants', ['format' => 'xlsx']) }}" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-file-excel"></i> Export Variants (Excel)
                                </a>
                            </div>
                        </div>
                    </div>
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
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Product deleted successfully!
        </div>
    </div>
</div>

<style>
/* Theme-specific styles */
.sa-symbol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sa-symbol--shape--rounded {
    border-radius: 0.375rem;
}

.sa-symbol--size--lg {
    width: 2.5rem;
    height: 2.5rem;
}

.sa-meta {
    font-size: 0.75rem;
    color: #6c757d;
}

.sa-meta__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 1rem;
}

.sa-meta__item {
    display: inline;
}

.st-copy {
    cursor: pointer;
    text-decoration: underline;
}

.st-copy:hover {
    color: #f5c000;
}

.sa-price {
    display: flex;
    align-items: baseline;
    font-weight: 600;
    white-space: nowrap;
}

.sa-price__symbol {
    font-size: 0.875rem;
    margin-right: 0.125rem;
}

.sa-price__integer {
    font-size: 1rem;
}

.sa-price__decimal {
    font-size: 0.875rem;
    opacity: 0.7;
}

/* Hide toggle input but keep it functional - remove from layout */
.form-check.form-switch {
    display: inline-block;
    margin: 0;
    padding: 0;
}

.form-check.form-switch .form-check-input {
    position: absolute !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
    visibility: hidden !important;
}

.form-check.form-switch .form-check-label {
    cursor: pointer;
    margin-left: 0 !important;
    margin-bottom: 0;
    user-select: none;
    display: inline-block;
}

.btn-sa-muted {
    background-color: transparent;
    border: none;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
}

.btn-sa-muted:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.sa-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 0;
}

.form-control--search {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control--search:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Badge styles */
.badge-sa-success {
    background-color: #d1edff;
    color: #0c63e4;
}

.badge-sa-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-sa-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-sa-primary {
    background-color: #cfe2ff;
    color: #084298;
}

.badge-sa-secondary {
    background-color: #e2e3e5;
    color: #41464b;
}

/* Table styles */
.w-min {
    width: 1%;
    white-space: nowrap;
}

.min-w-20x {
    min-width: 20rem;
}

/* Price Range column - prevent wrapping */
#productsTable th:nth-child(7),
#productsTable td:nth-child(7) {
    white-space: nowrap;
    min-width: 120px;
}

#productsTable td:nth-child(7) .sa-price {
    white-space: nowrap;
}

/* Product image styles */
.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.featured-badge {
    background: #ffc107;
    color: #000;
}

.type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.stock-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.stock-in-stock {
    background: #d1edff;
    color: #0c63e4;
}

.stock-out-of-stock {
    background: #f8d7da;
    color: #721c24;
}

.stock-on-backorder {
    background: #fff3cd;
    color: #856404;
}
</style>

<script>
let productsTable;
let deleteProductId = null;

// Wait for both DOM and jQuery to be ready
function waitForJQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback();
    } else {
        // Retry after a short delay
        setTimeout(function() {
            waitForJQuery(callback);
        }, 50);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    waitForJQuery(function() {
        initializeDataTable();
        setupEventListeners();
        
        // Initialize popovers for product actions
        initializeProductPopovers();
        
        // Handle popover action clicks
        setupPopoverActions();
    });
});

function initializeProductPopovers() {
    // Initialize popovers after table is drawn
    if (productsTable) {
        productsTable.on('draw', function() {
            // Destroy existing popovers
            document.querySelectorAll('.product-action-btn').forEach(function(element) {
                const existingPopover = bootstrap.Popover.getInstance(element);
                if (existingPopover) {
                    existingPopover.dispose();
                }
            });
            
            // Initialize new popovers with dynamic content
            document.querySelectorAll('.product-action-btn').forEach(function(element) {
                const productId = element.getAttribute('data-product-id');
                const popoverContent = '<div class="list-group list-group-flush" style="min-width: 150px;">' +
                    '<a class="list-group-item list-group-item-action" href="/products/' + productId + '">' +
                    '<i class="fas fa-eye me-2"></i>View Product</a>' +
                    '<a class="list-group-item list-group-item-action" href="/products/' + productId + '/edit">' +
                    '<i class="fas fa-edit me-2"></i>Edit</a>' +
                    '<a class="list-group-item list-group-item-action" href="#" data-action="seo" data-product-id="' + productId + '">' +
                    '<i class="fas fa-search me-2"></i>Manage SEO</a>' + 
                    '<a class="list-group-item list-group-item-action text-danger" href="#" data-action="delete" data-product-id="' + productId + '">' +
                    '<i class="fas fa-trash me-2"></i>Delete</a>' +
                    '</div>';
                
                new bootstrap.Popover(element, {
                    container: 'body',
                    placement: 'left',
                    trigger: 'click',
                    html: true,
                    content: popoverContent,
                    sanitize: false
                });
            });
        });
    }
    
    // Close popover when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.product-action-btn') && !e.target.closest('.popover')) {
            document.querySelectorAll('.product-action-btn').forEach(function(element) {
                const popover = bootstrap.Popover.getInstance(element);
                if (popover) {
                    popover.hide();
                }
            });
        }
    });
}

function setupPopoverActions() {
    // Use event delegation for popover content clicks
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-action]');
        if (target && target.closest('.popover')) {
            const action = target.getAttribute('data-action');
            const productId = target.getAttribute('data-product-id');
            
            // Hide the popover
            const btn = document.getElementById(`product-context-menu-${productId}`);
            if (btn) {
                const popover = bootstrap.Popover.getInstance(btn);
                if (popover) {
                    popover.hide();
                }
            }
            
            // Handle actions
            if (action === 'seo') {
                e.preventDefault();
                openSeoModal(productId);
            } else if (action === 'delete') {
                e.preventDefault();
                confirmDelete(productId);
            }
        }
    });
}

function initializeDataTable() {
    // Wait for jQuery before initializing DataTable
    waitForJQuery(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#productsTable')) {
            $('#productsTable').DataTable().destroy();
        }
        
        productsTable = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: '{{ route("products.data") }}',
            type: 'GET',
            data: function(d) {
                d.brand_id = $('#filterBrand').val();
                d.status = $('#filterStatus').val();
                d.category_id = $('#filterCategory').val();
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input m-0 fs-exact-16 d-block product-checkbox" aria-label="..." data-product-id="${row.id}"/>`;
                }
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center">
                            <a href="/products/${row.id}/edit" class="me-4">
                                <div class="sa-symbol sa-symbol--shape--rounded sa-symbol--size--lg">
                                    <img src="${row.thumbnail_url}" width="40" height="40" alt=""/>
                                </div>
                            </a>
                            <div>
                                <a href="/products/${row.id}/edit" class="text-reset">${data}</a>
                                <div class="sa-meta mt-0">
                                    <ul class="sa-meta__list">
                                        <li class="sa-meta__item">${row.variant_count > 0 ? 'Variant SKUs' : 'SKU'}: <span title="Click to copy SKU" class="st-copy">${row.sku || '—'}</span></li>
                                    </ul>
                                </div>
                                ${renderProductTags(row.tags)}
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'brands',
                name: 'brands',
                render: function(data, type, row) {
                    if (data && data.length > 0) {
                        let brandsHtml = '';
                        data.forEach((brand, index) => {
                            const badgeClass = index === 0 ? 'bg-primary' : 'bg-secondary';
                            brandsHtml += `<span class="badge ${badgeClass} me-1">${brand.name}</span>`;
                        });
                        return brandsHtml;
                    }
                    return '<span class="text-muted">No brands</span>';
                }
            },
            {
                data: 'categories',
                name: 'categories',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (data && data.length > 0) {
                        let categoriesHtml = '';
                        data.forEach((category, index) => {
                            const badgeClass = category.is_primary ? 'bg-success' : 'bg-secondary';
                            categoriesHtml += `<span class="badge ${badgeClass} me-1">${category.path || category.name}</span>`;
                        });
                        return categoriesHtml;
                    }
                    // Fallback to single category if categories array is empty
                    if (row.category && row.category.path) {
                        return `<span class="badge bg-success">${row.category.path}</span>`;
                    }
                    return '<span class="text-muted">No category</span>';
                }
            },
            {
                data: 'sku',
                name: 'sku',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data || '';
                    }
                    if (data && data !== '—') {
                        // If multiple SKUs, show count or truncate
                        const skus = data.split(', ');
                        if (skus.length > 1) {
                            return `<span title="${data}">${skus.length} SKUs</span>`;
                        }
                        return `<span>${data}</span>`;
                    }
                    return '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'variant_count',
                name: 'variant_count',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const count = parseInt(data ?? 0, 10);
                    if (type !== 'display') {
                        return isNaN(count) ? 0 : count;
                    }
                    if (isNaN(count) || count === 0) {
                        return '<span class="badge bg-secondary">No variants</span>';
                    }
                    return `<span class="badge bg-primary">${count} ${count === 1 ? 'Variant' : 'Variants'}</span>`;
                }
            },
            {
                data: 'variant_price_min',
                name: 'variant_price_min',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `
                            <div class="sa-price">
                                <span class="sa-price__integer">${row.variant_price_range || '—'}</span>
                            </div>
                        `;
                    }
                    return data ?? 0;
                }
            },
            {
                data: 'variant_units_display',
                name: 'variant_units_display',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data || '';
                    }
                    if (data && data !== '—') {
                        return `<span class="badge bg-info text-dark">${data}</span>`;
                    }
                    return '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'variant_stock_total',
                name: 'variant_stock_total',
                render: function(data, type, row) {
                    const total = parseInt(data ?? 0, 10);
                    if (type !== 'display') {
                        return isNaN(total) ? 0 : total;
                    }
                    const statusCode = row.variant_stock_status || 'unknown';
                    const statusLabel = row.variant_stock_label || '—';
                    const stockClass = getStockClass(statusCode);
                    return `<div class="badge ${stockClass}">${isNaN(total) ? 0 : total} ${statusLabel}</div>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const isPublished = data.toLowerCase() === 'published';
                    const statusClass = getStatusClass(data);
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" type="checkbox" 
                                   id="status-${row.id}" ${isPublished ? 'checked' : ''} 
                                   data-product-id="${row.id}" data-current-status="${data.toLowerCase()}">
                            <label class="form-check-label" for="status-${row.id}" style="cursor: pointer;">
                                <span class="badge status-badge ${statusClass}">${data}</span>
                            </label>
                        </div>
                    `;
                }
            },
            {
                data: 'featured',
                name: 'featured',
                render: function(data, type, row) {
                    const isFeatured = data === 'Yes';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input featured-toggle" type="checkbox" 
                                   id="featured-${row.id}" ${isFeatured ? 'checked' : ''} 
                                   data-product-id="${row.id}" data-current-featured="${isFeatured}">
                            <label class="form-check-label" for="featured-${row.id}" style="cursor: pointer;">
                                <span class="badge featured-badge ${isFeatured ? 'bg-warning' : 'bg-secondary'}">
                                    ${isFeatured ? 'Featured' : 'Not Featured'}
                                </span>
                            </label>
                        </div>
                    `;
                }
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="position-relative">
                            <button class="btn btn-sa-muted btn-sm product-action-btn" type="button" 
                                    id="product-context-menu-${data}" 
                                    data-bs-toggle="popover" 
                                    data-bs-trigger="click"
                                    data-bs-placement="left"
                                    data-bs-html="true"
                                    data-bs-container="body"
                                    data-product-id="${data}"
                                    aria-label="More">
                                <svg xmlns="http://www.w3.org/2000/svg" width="3" height="13" fill="currentColor">
                                    <path d="M1.5,8C0.7,8,0,7.3,0,6.5S0.7,5,1.5,5S3,5.7,3,6.5S2.3,8,1.5,8z M1.5,3C0.7,3,0,2.3,0,1.5S0.7,0,1.5,0 S3,0.7,3,1.5S2.3,3,1.5,3z M1.5,10C2.3,10,3,10.7,3,11.5S2.3,13,1.5,13S0,12.3,0,11.5S0.7,10,1.5,10z"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[12, 'desc']], // Sort by created_at desc
        pageLength: 25,
        responsive: true,
        stateSave: false, // Disable state saving for better performance
        stateDuration: 0,
        pagingType: 'simple_numbers', // Simpler pagination for better performance
        language: {
            processing: "Loading products...",
            emptyTable: "No products found",
            zeroRecords: "No products match your search criteria"
        },
        // Performance optimizations
        scrollCollapse: false,
        autoWidth: false
        });
    });
}

function loadBrands() {
    fetch('{{ route("brands.data") }}')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const brandSelect = document.getElementById('filterBrand');
            if (brandSelect) {
                brandSelect.innerHTML = '<option value="">All Brands</option>';
                
                if (Array.isArray(data)) {
                    data.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.id;
                        option.textContent = brand.name;
                        brandSelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading brands:', error);
            // Don't show error to user, just log it
        });
}

function setupEventListeners() {
    // Load brands for filter (defer to avoid blocking initial load)
    setTimeout(function() {
        loadBrands();
    }, 100);
    
    // Filter change events
    $('#filterBrand, #filterStatus, #filterCategory').on('change', function() {
        if (productsTable) {
            productsTable.draw();
        }
    });
    
    // Delete confirmation
    $('#confirmDelete').on('click', function() {
        if (deleteProductId) {
            deleteProduct(deleteProductId);
        }
    });
    
    // Select all checkbox functionality
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.product-checkbox').prop('checked', isChecked);
        updateBulkActions();
    });
    
    // Individual checkbox change
    $(document).on('change', '.product-checkbox', function() {
        updateBulkActions();
        updateSelectAllState();
    });
    
    // Status toggle functionality
    $(document).on('change', '.status-toggle', function() {
        const productId = $(this).data('product-id');
        const currentStatus = $(this).data('current-status');
        const newStatus = $(this).is(':checked') ? 'published' : 'hidden';
        
        toggleProductStatus(productId, currentStatus, newStatus, $(this));
    });
    
    // Featured toggle functionality
    $(document).on('change', '.featured-toggle', function() {
        const productId = $(this).data('product-id');
        const currentFeatured = $(this).data('current-featured');
        const newFeatured = $(this).is(':checked');
        
        toggleProductFeatured(productId, currentFeatured, newFeatured, $(this));
    });
    
    // Copy to clipboard functionality
    $(document).on('click', '.st-copy', function() {
        const text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            showToast('success', 'Copied to clipboard!');
        });
    });
    
    // Custom search functionality
    $('#table-search').on('keyup', function() {
        if (productsTable) {
            productsTable.search(this.value).draw();
        }
    });

    // Import form functionality starts

    const importForm = document.getElementById('productImportForm');
    if (importForm) {
        importForm.addEventListener('submit', handleProductImportSubmit);
    }

    const importModal = document.getElementById('importProductsModal');
    if (importModal) {
        importModal.addEventListener('hidden.bs.modal', resetImportModal);
    }
}

function setImportButtonLoading(isLoading) {
    const button = document.getElementById('startImportButton');
    if (!button) {
        return;
    }

    const label = button.querySelector('.import-button-label');
    const spinner = button.querySelector('.import-button-spinner');

    button.disabled = isLoading;
    if (label) {
        label.classList.toggle('d-none', isLoading);
    }
    if (spinner) {
        spinner.classList.toggle('d-none', !isLoading);
    }
}

function handleProductImportSubmit(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const fileInput = document.getElementById('productImportFile');

    if (!fileInput || fileInput.files.length === 0) {
        showToast('error', 'Please choose a CSV or XLSX file to import.');
        return;
    }

    setImportButtonLoading(true);
    clearImportSummary();

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData,
    })
        .then(async (response) => {
            const rawText = await response.text();
            const payload = tryParseJson(rawText);
            return {
                ok: response.ok,
                status: response.status,
                payload,
                rawText,
            };
        })
        .then((result) => {
            const { ok, payload, rawText, status } = result;

            if (payload?.summary) {
                renderImportSummary(payload.summary);
            } else if (rawText) {
                renderRawSummary(rawText);
            } else {
                clearImportSummary();
            }

            if (ok && payload?.success) {
                showToast('success', payload.message || 'Products imported successfully.');
                form.reset();
            } else {
                let errorMessage = payload?.message || 'Import completed with issues.';
                
                // Handle validation errors (422)
                if (status === 422 && payload?.errors) {
                    const errorList = Object.values(payload.errors).flat().join(', ');
                    errorMessage = `Validation failed: ${errorList}`;
                    renderValidationErrors(payload.errors);
                } else if (status >= 300 && status < 400) {
                    errorMessage = 'Import received a redirect response.';
                }
                
                showToast('error', errorMessage);
            }

            if (productsTable) {
                productsTable.draw();
            }
        })
        .catch(error => {
            console.error('Product import error:', error);
            showToast('error', 'An unexpected error occurred while importing products.');
        })
        .finally(() => {
            setImportButtonLoading(false);
        });
}

function clearImportSummary() {
    const container = document.getElementById('productImportSummary');
    if (!container) {
        return;
    }

    container.classList.add('d-none');
    container.classList.remove('alert-danger');
    container.classList.add('alert-secondary');
    container.innerHTML = '';
}

function renderImportSummary(summary) {
    const container = document.getElementById('productImportSummary');
    if (!container) {
        return;
    }

    const created = summary?.created ?? 0;
    const updated = summary?.updated ?? 0;
    const skipped = summary?.skipped ?? 0;
    const errors = Array.isArray(summary?.errors) ? summary.errors : [];
    const warnings = Array.isArray(summary?.warnings) ? summary.warnings : [];

    let html = `<strong>Summary</strong><br>Created: ${created}, Updated: ${updated}, Skipped: ${skipped}.`;

    if (warnings.length > 0) {
        const warningItems = warnings.map(message => `<li>${message}</li>`).join('');
        html += `<hr><strong>Warnings</strong><ul class="mb-0">${warningItems}</ul>`;
    }

    if (errors.length > 0) {
        const errorItems = errors.map(error => {
            const row = error.row ? `Row ${error.row}: ` : '';
            return `<li>${row}${error.message}</li>`;
        }).join('');
        html += `<hr><strong class="text-danger">Errors</strong><ul class="mb-0 text-danger">${errorItems}</ul>`;
    }

    container.innerHTML = html;
    container.classList.remove('d-none');
    container.classList.toggle('alert-danger', errors.length > 0);
    container.classList.toggle('alert-secondary', errors.length === 0);
}

function renderValidationErrors(errors) {
    const container = document.getElementById('productImportSummary');
    if (!container) {
        return;
    }

    let html = '<strong>Validation Errors</strong><ul class="mb-0">';
    Object.entries(errors).forEach(([field, messages]) => {
        const fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        messages.forEach(message => {
            html += `<li><strong>${fieldLabel}:</strong> ${message}</li>`;
        });
    });
    html += '</ul>';

    container.innerHTML = html;
    container.classList.remove('d-none');
    container.classList.add('alert-danger');
    container.classList.remove('alert-secondary');
}

function resetImportModal() {
    const form = document.getElementById('productImportForm');
    if (form) {
        form.reset();
    }
    setImportButtonLoading(false);
    clearImportSummary();
}

// Import form functionality ends

function tryParseJson(rawText) {
    if (!rawText || typeof rawText !== 'string') {
        return null;
    }

    try {
        return JSON.parse(rawText);
    } catch (error) {
        return null;
    }
}

function renderRawSummary(rawText) {
    const container = document.getElementById('productImportSummary');
    if (!container) {
        return;
    }

    if (!rawText || typeof rawText !== 'string') {
        clearImportSummary();
        return;
    }

    container.innerHTML = `<strong>Response</strong><pre class="mb-0 text-wrap">${escapeHtml(rawText)}</pre>`;
    container.classList.remove('d-none');
    container.classList.add('alert-danger');
    container.classList.remove('alert-secondary');
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.innerText = value;
    return div.innerHTML;
}

function renderProductTags(tags) {
    if (!Array.isArray(tags) || tags.length === 0) {
        return '';
    }

    const badges = tags.map(tag => `<span class="badge bg-light text-dark me-1 mb-1">${tag}</span>`).join('');
    return `<div class="mt-2 d-flex flex-wrap align-items-center">${badges}</div>`;
}

function getStatusClass(status) {
    switch(status.toLowerCase()) {
        case 'published':
            return 'bg-success';
        case 'hidden':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getStockClass(stockStatus) {
    if (!stockStatus) {
        return 'badge-sa-secondary';
    }

    const normalized = stockStatus.toString().toLowerCase().replace(/\s+/g, '_');

    switch(normalized) {
        case 'in_stock':
            return 'badge-sa-success';
        case 'out_of_stock':
            return 'badge-sa-danger';
        case 'on_backorder':
            return 'badge-sa-warning';
        case 'preorder':
            return 'badge-sa-primary';
        default:
            return 'badge-sa-secondary';
    }
}

function updateSelectAllState() {
    waitForJQuery(function() {
        const totalCheckboxes = $('.product-checkbox').length;
        const checkedCheckboxes = $('.product-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    });
}

function updateBulkActions() {
    waitForJQuery(function() {
        const selectedCount = $('.product-checkbox:checked').length;
        // Show/hide bulk delete button
        if (selectedCount > 0) {
            $('#bulkDeleteBtn').removeClass('d-none');
        } else {
            $('#bulkDeleteBtn').addClass('d-none');
        }
    });
}

function updateBulkDeleteButton() {
    updateBulkActions();
    updateSelectAllState();
}

function bulkDeleteProducts() {
    waitForJQuery(function() {
        const checkedBoxes = $('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            showToast('error', 'Please select at least one product to delete');
            return;
        }
        
        const ids = checkedBoxes.map(function() {
            return $(this).data('product-id');
        }).get();
        
        if (!confirm(`Are you sure you want to delete ${ids.length} product(s)? This action cannot be undone.`)) {
            return;
        }
        
        $.ajax({
        url: '{{ route("products.bulk-delete") }}',
        type: 'POST',
        data: {
            ids: ids,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                if (productsTable) {
                    productsTable.draw();
                }
                $('#selectAll').prop('checked', false);
                updateBulkActions();
            } else {
                showToast('error', response.message || 'Failed to delete products');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to delete products';
            showToast('error', message);
        }
        });
    });
}


function openSeoModal(productId) {
    waitForJQuery(function() {
        // Reset form
        $('#seoForm')[0].reset();
        $('#seoProductId').val(productId);
        
        // Load current SEO data
        $.ajax({
        url: `/products/${productId}/seo`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#seoMetaTitle').val(response.data.meta_title || '');
                $('#seoMetaDescription').val(response.data.meta_description || '');
                
                // Combine metadata and json_ld if both exist
                let metadataContent = '';
                if (response.data.metadata) {
                    metadataContent = response.data.metadata;
                }
                if (response.data.json_ld) {
                    const jsonLdStr = typeof response.data.json_ld === 'string' 
                        ? response.data.json_ld 
                        : JSON.stringify(response.data.json_ld, null, 2);
                    if (metadataContent) {
                        metadataContent += '\n\n' + jsonLdStr;
                    } else {
                        metadataContent = jsonLdStr;
                    }
                }
                $('#seoMetadata').val(metadataContent);
            }
        },
        error: function(xhr) {
            console.error('Error loading SEO data:', xhr);
            showToast('error', 'Failed to load SEO data');
        }
        });
        
        $('#seoModal').modal('show');
    });
}

// Handle SEO form submission
$('#seoForm').on('submit', function(e) {
    e.preventDefault();
    
    const productId = $('#seoProductId').val();
    const metadataContent = $('#seoMetadata').val();
    
    // Try to parse JSON-LD from metadata content
    let jsonLd = null;
    let metadata = metadataContent;
    
    if (metadataContent) {
        // Try to extract JSON-LD script tag or standalone JSON
        const jsonLdMatch = metadataContent.match(/<script[^>]*type=["']application\/ld\+json["'][^>]*>(.*?)<\/script>/is);
        if (jsonLdMatch) {
            try {
                jsonLd = JSON.parse(jsonLdMatch[1]);
                // Remove JSON-LD from metadata
                metadata = metadataContent.replace(/<script[^>]*type=["']application\/ld\+json["'][^>]*>.*?<\/script>/is, '').trim();
            } catch (e) {
                // If JSON-LD parsing fails, keep it in metadata
            }
        } else {
            // Try to parse as standalone JSON
            try {
                jsonLd = JSON.parse(metadataContent);
                metadata = null; // If it's valid JSON, store as json_ld only
            } catch (e) {
                // Not JSON, keep as metadata
            }
        }
    }
    
    const formData = {
        meta_title: $('#seoMetaTitle').val(),
        meta_description: $('#seoMetaDescription').val(),
        metadata: metadata,
        json_ld: jsonLd ? JSON.stringify(jsonLd) : null,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
        url: `/products/${productId}/seo`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                showToast('success', 'SEO settings saved successfully');
                $('#seoModal').modal('hide');
            } else {
                showToast('error', response.message || 'Failed to save SEO settings');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            let errorMessage = 'Failed to save SEO settings';
            
            if (Object.keys(errors).length > 0) {
                errorMessage = Object.values(errors).flat().join(', ');
            } else if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showToast('error', errorMessage);
        }
    });
});

function confirmDelete(productId) {
    deleteProductId = productId;
    $('#deleteModal').modal('show');
}

function deleteProduct(productId) {
    fetch(`/products/${productId}/delete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            $('#deleteModal').modal('hide');
            showToast('success', data.message);
            if (productsTable) {
                productsTable.draw();
            }
        } else {
            showToast('error', data.message || 'Error deleting product');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while deleting the product');
    });
}

function toggleProductStatus(productId, currentStatus, newStatus, toggleElement) {
    // Disable toggle while processing
    toggleElement.prop('disabled', true);
    
    fetch(`/products/${productId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the badge text and class
            const badge = toggleElement.siblings('label').find('.status-badge');
            badge.removeClass('bg-success bg-secondary bg-warning bg-danger');
            badge.addClass(getStatusClass(newStatus));
            badge.text(newStatus);
            
            // Update data attributes
            toggleElement.data('current-status', newStatus);
            
            showToast('success', data.message);
        } else {
            // Revert toggle state
            toggleElement.prop('checked', currentStatus.toLowerCase() === 'published');
            showToast('error', data.message || 'Error updating product status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert toggle state
        toggleElement.prop('checked', currentStatus.toLowerCase() === 'published');
        showToast('error', 'An error occurred while updating product status');
    })
    .finally(() => {
        // Re-enable toggle
        toggleElement.prop('disabled', false);
    });
}

function toggleProductFeatured(productId, currentFeatured, newFeatured, toggleElement) {
    // Disable toggle while processing
    toggleElement.prop('disabled', true);
    
    fetch(`/products/${productId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            featured: newFeatured
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the badge text and class
            const badge = toggleElement.siblings('label').find('.featured-badge');
            badge.removeClass('bg-warning bg-secondary');
            badge.addClass(newFeatured ? 'bg-warning' : 'bg-secondary');
            badge.text(newFeatured ? 'Featured' : 'Not Featured');
            
            // Update data attributes
            toggleElement.data('current-featured', newFeatured);
            
            showToast('success', data.message);
        } else {
            // Revert toggle state
            toggleElement.prop('checked', currentFeatured);
            showToast('error', data.message || 'Error updating featured status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert toggle state
        toggleElement.prop('checked', currentFeatured);
        showToast('error', 'An error occurred while updating featured status');
    })
    .finally(() => {
        // Re-enable toggle
        toggleElement.prop('disabled', false);
    });
}

function clearFilters() {
    $('#filterBrand, #filterStatus, #filterCategory').val('');
    $('#table-search').val('');
    if (productsTable) {
        productsTable.search('').draw();
    }
}

function showToast(type, message) {
    const toast = document.getElementById('toast');
    if (!toast) {
        console.log(`${type.toUpperCase()}: ${message}`);
        return;
    }
    
    const toastBody = toast.querySelector('.toast-body');
    const toastHeader = toast.querySelector('.toast-header');
    
    if (toastBody) {
        toastBody.textContent = message;
    }
    
    if (toastHeader) {
        if (type === 'success') {
            toastHeader.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>';
        } else {
            toastHeader.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>';
        }
    }
    
    try {
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    } catch (error) {
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}
</script>
@push('scripts')
<script>
// jQuery-dependent code is already initialized in the main script section
// No need to re-initialize here to avoid duplicate DataTable initialization
// The DataTable is initialized once in the DOMContentLoaded event listener (line 576)
</script>
@endpush
@endsection
