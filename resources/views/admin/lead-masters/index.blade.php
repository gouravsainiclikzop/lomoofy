@extends('layouts.admin')

@section('title', 'Lead Masters Management')

@push('styles')
<style>
    .master-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: white;
    }
    .master-item:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .master-item.editing {
        border-color: #f5c000;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .badge-color-preview {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    /* Ensure active tab is visible */
    .nav-tabs .nav-link {
        color: #6c757d;
        border: 1px solid transparent;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        isolation: isolate;
    }
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active:hover {
        border-color: #dee2e6 #dee2e6 #fff;
    }
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
        display: block;
    }
    .tab-pane.show {
        display: block !important;
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
                        <li class="breadcrumb-item">Master Data</li>
                        <li class="breadcrumb-item active">Lead Masters</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Lead Masters Management</h1>
                <p class="text-muted mb-0">Manage lead statuses, sources, and priorities. These are the standard master data for the Lead Management module.</p>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="masterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="status-tab" data-bs-toggle="tab" data-bs-target="#status" type="button" role="tab">
                    <i class='bx bx-check-circle'></i> Statuses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="source-tab" data-bs-toggle="tab" data-bs-target="#source" type="button" role="tab">
                    <i class='bx bx-link'></i> Sources
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="priority-tab" data-bs-toggle="tab" data-bs-target="#priority" type="button" role="tab">
                    <i class='bx bx-star'></i> Priorities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tag-tab" data-bs-toggle="tab" data-bs-target="#tag" type="button" role="tab">
                    <i class='bx bx-purchase-tag'></i> Tags
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="masterTabsContent">
            <!-- Status Tab -->
            <div class="tab-pane fade show active" id="status" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Lead Statuses</h5>
                            <small class="text-muted">Manage the status options for leads</small>
                        </div>
                        <button class="btn btn-primary btn-sm" id="addStatusBtn">
                            <i class='bx bx-plus'></i> Add Status
                        </button>
                    </div>
                    <div class="card-body" id="statusList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Source Tab -->
            <div class="tab-pane fade" id="source" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Lead Sources</h5>
                            <small class="text-muted">Manage where leads come from</small>
                        </div>
                        <button class="btn btn-primary btn-sm" id="addSourceBtn">
                            <i class='bx bx-plus'></i> Add Source
                        </button>
                    </div>
                    <div class="card-body" id="sourceList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Priority Tab -->
            <div class="tab-pane fade" id="priority" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Lead Priorities</h5>
                            <small class="text-muted">Manage priority levels</small>
                        </div>
                        <button class="btn btn-primary btn-sm" id="addPriorityBtn">
                            <i class='bx bx-plus'></i> Add Priority
                        </button>
                    </div>
                    <div class="card-body" id="priorityList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tag Tab -->
            <div class="tab-pane fade" id="tag" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Lead Tags</h5>
                            <small class="text-muted">Manage tags for categorizing leads</small>
                        </div>
                        <button class="btn btn-primary btn-sm" id="addTagBtn">
                            <i class='bx bx-plus'></i> Add Tag
                        </button>
                    </div>
                    <div class="card-body" id="tagList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStatusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" checked id="addStatusActive">
                                <label class="form-check-label" for="addStatusActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Source Modal -->
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSourceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" checked id="addSourceActive">
                                <label class="form-check-label" for="addSourceActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Source</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Priority Modal -->
<div class="modal fade" id="addPriorityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Priority</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPriorityForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" checked id="addPriorityActive">
                                <label class="form-check-label" for="addPriorityActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Priority</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTagForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" checked id="addTagActive">
                        <label class="form-check-label" for="addTagActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                <p class="text-danger mb-0" id="deleteWarning"></p>
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
            <i class='bx bx-check-circle text-success me-2'></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let masterData = {};

    // Ensure first tab is active on page load
    // Bootstrap tabs should work automatically, but we ensure the active state is visible
    // Show the first tab pane if not already shown
    const firstTabPane = $('#status');
    if (firstTabPane.length && !firstTabPane.hasClass('show')) {
        firstTabPane.addClass('show active');
    }
    
    // Ensure the first tab button has active class
    const firstTabButton = $('#status-tab');
    if (firstTabButton.length && !firstTabButton.hasClass('active')) {
        firstTabButton.addClass('active');
    }

    // Load master data
    loadMasterData();

    function loadMasterData() {
        $.ajax({
            url: '{{ route("lead-masters.data") }}',
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    masterData = response.data;
                    renderStatuses();
                    renderSources();
                    renderPriorities();
                    renderTags();
                }
            },
            error: function(xhr) {
                console.error('Error loading master data:', xhr);
            }
        });
    }

    function renderStatuses() {
        const container = $('#statusList');
        if(masterData.statuses.length === 0) {
            container.html('<p class="text-muted">No statuses found</p>');
            return;
        }

        let html = '';
        masterData.statuses.forEach(function(status) {
            html += `
                <div class="master-item" data-id="${status.id}">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <strong>${status.name}</strong>
                        </div>
                        <div class="col-md-2">
                            <span class="badge ${status.is_active ? 'bg-success' : 'bg-secondary'}">${status.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Order: ${status.sort_order}</small>
                        </div>
                        <div class="col-md-1 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-master" data-type="status" data-id="${status.id}" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-outline-danger delete-master" data-type="status" data-id="${status.id}" data-name="${status.name}" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="edit-form mt-3" style="display: none;">
                        <form class="master-edit-form" data-type="status" data-id="${status.id}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="${status.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="${status.sort_order}" min="0">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" ${status.is_active ? 'checked' : ''} id="status_active_${status.id}">
                                        <label class="form-check-label" for="status_active_${status.id}">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    function renderSources() {
        const container = $('#sourceList');
        if(masterData.sources.length === 0) {
            container.html('<p class="text-muted">No sources found</p>');
            return;
        }

        let html = '';
        masterData.sources.forEach(function(source) {
            html += `
                <div class="master-item" data-id="${source.id}">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <strong>${source.name}</strong>
                        </div>
                        <div class="col-md-2">
                            <span class="badge ${source.is_active ? 'bg-success' : 'bg-secondary'}">${source.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-master" data-type="source" data-id="${source.id}" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-outline-danger delete-master" data-type="source" data-id="${source.id}" data-name="${source.name}" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="edit-form mt-3" style="display: none;">
                        <form class="master-edit-form" data-type="source" data-id="${source.id}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="${source.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="${source.sort_order}" min="0">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" ${source.is_active ? 'checked' : ''} id="source_active_${source.id}">
                                        <label class="form-check-label" for="source_active_${source.id}">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    function renderPriorities() {
        const container = $('#priorityList');
        if(masterData.priorities.length === 0) {
            container.html('<p class="text-muted">No priorities found</p>');
            return;
        }

        let html = '';
        masterData.priorities.forEach(function(priority) {
            html += `
                <div class="master-item" data-id="${priority.id}">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <strong>${priority.name}</strong>
                        </div>
                        <div class="col-md-2">
                            <span class="badge ${priority.is_active ? 'bg-success' : 'bg-secondary'}">${priority.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Order: ${priority.sort_order}</small>
                        </div>
                        <div class="col-md-1 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-master" data-type="priority" data-id="${priority.id}" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-outline-danger delete-master" data-type="priority" data-id="${priority.id}" data-name="${priority.name}" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="edit-form mt-3" style="display: none;">
                        <form class="master-edit-form" data-type="priority" data-id="${priority.id}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="${priority.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="${priority.sort_order}" min="0">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" ${priority.is_active ? 'checked' : ''} id="priority_active_${priority.id}">
                                        <label class="form-check-label" for="priority_active_${priority.id}">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    function renderTags() {
        const container = $('#tagList');
        if(!masterData.tags || masterData.tags.length === 0) {
            container.html('<p class="text-muted">No tags found</p>');
            return;
        }

        let html = '';
        masterData.tags.forEach(function(tag) {
            html += `
                <div class="master-item" data-id="${tag.id}">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <strong>${tag.name}</strong>
                        </div>
                        <div class="col-md-2">
                            <span class="badge ${tag.is_active ? 'bg-success' : 'bg-secondary'}">${tag.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="col-md-3">
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-master" data-type="tag" data-id="${tag.id}" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-outline-danger delete-master" data-type="tag" data-id="${tag.id}" data-name="${tag.name}" title="Delete">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="edit-form mt-3" style="display: none;">
                        <form class="master-edit-form" data-type="tag" data-id="${tag.id}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="${tag.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" ${tag.is_active ? 'checked' : ''} id="tag_active_${tag.id}">
                                        <label class="form-check-label" for="tag_active_${tag.id}">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-12 text-end">
                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    // Edit button click
    $(document).on('click', '.edit-master', function() {
        const item = $(this).closest('.master-item');
        const form = item.find('.edit-form');
        item.addClass('editing');
        form.slideDown();
    });

    // Cancel edit
    $(document).on('click', '.cancel-edit', function() {
        const item = $(this).closest('.master-item');
        const form = item.find('.edit-form');
        item.removeClass('editing');
        form.slideUp();
    });

    // Submit edit form
    $(document).on('submit', '.master-edit-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const type = form.data('type');
        const id = form.data('id');
        const formData = new FormData(this);
        formData.append('is_active', form.find('[name="is_active"]').is(':checked') ? 1 : 0);

        let url = '';
        if(type === 'status') {
            url = '{{ route("lead-masters.update-status", ":id") }}'.replace(':id', id);
        } else if(type === 'source') {
            url = '{{ route("lead-masters.update-source", ":id") }}'.replace(':id', id);
        } else if(type === 'priority') {
            url = '{{ route("lead-masters.update-priority", ":id") }}'.replace(':id', id);
        } else if(type === 'tag') {
            url = '{{ route("lead-masters.update-tag", ":id") }}'.replace(':id', id);
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
                if(response.success) {
                    showToast('success', response.message || 'Updated successfully');
                    loadMasterData();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', 'Validation error: ' + JSON.stringify(xhr.responseJSON.errors));
                } else {
                    showToast('error', 'Failed to update');
                }
            }
        });
    });

    function showToast(type, message) {
        try {
            // Get or create toast container
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                // Create toast container if it doesn't exist
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // Create a unique toast element for each message
            const toastId = 'toast-' + Date.now();
            const toastEl = document.createElement('div');
            toastEl.id = toastId;
            toastEl.className = 'toast';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');

            // Set toast content
            const iconClass = type === 'success' ? 'bx-check-circle text-success' : 'bx-x-circle text-danger';
            const title = type === 'success' ? 'Success' : 'Error';
            
            toastEl.innerHTML = `
                <div class="toast-header">
                    <i class='bx ${iconClass} me-2'></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;

            // Append to container
            toastContainer.appendChild(toastEl);

            // Create and show toast
            const bsToast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000
            });

            // Remove toast element after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function() {
                toastEl.remove();
            });

            // Show toast
            bsToast.show();
        } catch (error) {
            console.error('Error showing toast:', error);
            // If toast still fails, create a simple notification in the container
            const toastContainer = document.querySelector('.toast-container') || (function() {
                const container = document.createElement('div');
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(container);
                return container;
            })();
            
            const errorToast = document.createElement('div');
            errorToast.className = 'toast show';
            errorToast.innerHTML = `
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;
            toastContainer.appendChild(errorToast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (errorToast.parentNode) {
                    errorToast.remove();
                }
            }, 5000);
        }
    }

    // Add button clicks
    $('#addStatusBtn').on('click', function() {
        $('#addStatusForm')[0].reset();
        $('#addStatusModal').modal('show');
    });

    $('#addSourceBtn').on('click', function() {
        $('#addSourceForm')[0].reset();
        $('#addSourceModal').modal('show');
    });

    $('#addPriorityBtn').on('click', function() {
        $('#addPriorityForm')[0].reset();
        $('#addPriorityModal').modal('show');
    });

    $('#addTagBtn').on('click', function() {
        $('#addTagForm')[0].reset();
        $('#addTagModal').modal('show');
    });

    // Add form submissions
    $('#addStatusForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('is_active', $('#addStatusActive').is(':checked') ? 1 : 0);

        $.ajax({
            url: '{{ route("lead-masters.store-status") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#addStatusModal').modal('hide');
                    loadMasterData();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', 'Validation error: ' + JSON.stringify(xhr.responseJSON.errors));
                } else {
                    showToast('error', 'Failed to create status');
                }
            }
        });
    });

    $('#addSourceForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('is_active', $('#addSourceActive').is(':checked') ? 1 : 0);

        $.ajax({
            url: '{{ route("lead-masters.store-source") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#addSourceModal').modal('hide');
                    loadMasterData();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', 'Validation error: ' + JSON.stringify(xhr.responseJSON.errors));
                } else {
                    showToast('error', 'Failed to create source');
                }
            }
        });
    });

    $('#addPriorityForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('is_active', $('#addPriorityActive').is(':checked') ? 1 : 0);

        $.ajax({
            url: '{{ route("lead-masters.store-priority") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#addPriorityModal').modal('hide');
                    loadMasterData();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', 'Validation error: ' + JSON.stringify(xhr.responseJSON.errors));
                } else {
                    showToast('error', 'Failed to create priority');
                }
            }
        });
    });

    $('#addTagForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('is_active', $('#addTagActive').is(':checked') ? 1 : 0);

        $.ajax({
            url: '{{ route("lead-masters.store-tag") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    $('#addTagModal').modal('hide');
                    loadMasterData();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    showToast('error', 'Validation error: ' + JSON.stringify(xhr.responseJSON.errors));
                } else {
                    showToast('error', 'Failed to create tag');
                }
            }
        });
    });

    // Delete button click
    let deleteType = null;
    let deleteId = null;

    $(document).on('click', '.delete-master', function() {
        deleteType = $(this).data('type');
        deleteId = $(this).data('id');
        const name = $(this).data('name');
        
        $('#deleteWarning').text(`You are about to delete "${name}".`);
        $('#confirmDelete').prop('disabled', false).html('Delete');
        $('#deleteModal').modal('show');
    });

    // Reset delete button when modal is hidden
    $('#deleteModal').on('hidden.bs.modal', function() {
        $('#confirmDelete').prop('disabled', false).html('Delete');
        deleteType = null;
        deleteId = null;
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        if(!deleteType || !deleteId) return;

        let url = '';
        if(deleteType === 'status') {
            url = '{{ route("lead-masters.delete-status", ":id") }}'.replace(':id', deleteId);
        } else if(deleteType === 'source') {
            url = '{{ route("lead-masters.delete-source", ":id") }}'.replace(':id', deleteId);
        } else if(deleteType === 'priority') {
            url = '{{ route("lead-masters.delete-priority", ":id") }}'.replace(':id', deleteId);
        } else if(deleteType === 'tag') {
            url = '{{ route("lead-masters.delete-tag", ":id") }}'.replace(':id', deleteId);
        }

        // Disable button to prevent double clicks
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    
                    // Close modal using Bootstrap 5 API
                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    if(deleteModal) {
                        deleteModal.hide();
                    } else {
                        $('#deleteModal').modal('hide');
                    }
                    
                    // Reload data after a short delay to ensure modal is closed
                    setTimeout(function() {
                        loadMasterData();
                    }, 300);
                } else {
                    showToast('error', response.message || 'Failed to delete');
                    btn.prop('disabled', false).html('Delete');
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errorMsg = xhr.responseJSON.message || (xhr.responseJSON.errors ? JSON.stringify(xhr.responseJSON.errors) : 'Cannot delete this item');
                    showToast('error', errorMsg);
                } else {
                    showToast('error', xhr.responseJSON?.message || 'Failed to delete');
                }
                btn.prop('disabled', false).html('Delete');
            },
            complete: function() {
                deleteType = null;
                deleteId = null;
            }
        });
    });
});
</script>
@endpush

@endsection

