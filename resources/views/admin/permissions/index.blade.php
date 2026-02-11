@extends('layouts.admin')

@section('title', 'Permissions Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Permissions Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPermissionModal" id="addPermissionBtn">
                        <i class='bx bx-plus'></i> Add Permission
                    </button>
                </div>


                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="permissionsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><i class='bx bx-menu'></i></th>
                                        <th>Sort No</th>
                                        <th>Permission Name</th>
                                        <th>Actions</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPermissionModalLabel">
                    <i class='bx bx-plus'></i> Add Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPermissionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="permissionName" class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="permissionName" name="name" required placeholder="e.g., Product Management, Order Management">
                        <small class="form-text text-muted">Enter a descriptive name for this permission</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Actions <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="view" id="permActionView" checked>
                                    <label class="form-check-label" for="permActionView">View</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="create" id="permActionCreate" checked>
                                    <label class="form-check-label" for="permActionCreate">Create</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="update" id="permActionUpdate" checked>
                                    <label class="form-check-label" for="permActionUpdate">Update</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="delete" id="permActionDelete" checked>
                                    <label class="form-check-label" for="permActionDelete">Delete</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="export" id="permActionExport">
                                    <label class="form-check-label" for="permActionExport">Export</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="import" id="permActionImport">
                                    <label class="form-check-label" for="permActionImport">Import</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="approve" id="permActionApprove">
                                    <label class="form-check-label" for="permActionApprove">Approve</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="assign" id="permActionAssign">
                                    <label class="form-check-label" for="permActionAssign">Assign</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="permissionSortNo" class="form-label">Sort Number</label>
                        <input type="number" class="form-control" id="permissionSortNo" name="sort_no" min="0" value="0" placeholder="e.g., 1, 2, 3...">
                        <small class="form-text text-muted">Lower numbers appear first. Used for ordering permissions.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Create Permission</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermissionModalLabel">
                    <i class='bx bx-edit'></i> Edit Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="permissionEditForm">
                @csrf
                <input type="hidden" id="editPermissionId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPermissionName" class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPermissionName" name="name" required placeholder="e.g., Product Management, Order Management">
                        <small class="form-text text-muted">Enter a descriptive name for this permission</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Actions <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="view" id="editPermActionView">
                                    <label class="form-check-label" for="editPermActionView">View</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="create" id="editPermActionCreate">
                                    <label class="form-check-label" for="editPermActionCreate">Create</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="update" id="editPermActionUpdate">
                                    <label class="form-check-label" for="editPermActionUpdate">Update</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="delete" id="editPermActionDelete">
                                    <label class="form-check-label" for="editPermActionDelete">Delete</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="export" id="editPermActionExport">
                                    <label class="form-check-label" for="editPermActionExport">Export</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="import" id="editPermActionImport">
                                    <label class="form-check-label" for="editPermActionImport">Import</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="approve" id="editPermActionApprove">
                                    <label class="form-check-label" for="editPermActionApprove">Approve</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit_actions[]" value="assign" id="editPermActionAssign">
                                    <label class="form-check-label" for="editPermActionAssign">Assign</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editPermissionSortNo" class="form-label">Sort Number</label>
                        <input type="number" class="form-control" id="editPermissionSortNo" name="sort_no" min="0" value="0" placeholder="e.g., 1, 2, 3...">
                        <small class="form-text text-muted">Lower numbers appear first. Used for ordering permissions.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Update Permission</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css" rel="stylesheet">
<style>
    .bx {
        vertical-align: middle;
        font-size: 1.1rem;
    }
    .permission-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .sortable-handle {
        cursor: move;
        cursor: grab;
        color: #6c757d;
        font-size: 1.2rem;
        padding: 0.5rem;
    }
    .sortable-handle:active {
        cursor: grabbing;
    }
    .sortable-ghost {
        opacity: 0.4;
        background-color: #f8f9fa;
    }
    .sortable-drag {
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    #permissionsTable tbody tr {
        cursor: move;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let dataTable;
    let sortableInstance = null;

    // Initialize DataTable
    dataTable = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("permissions.data") }}'
        },
        columns: [
            { 
                data: null,
                orderable: false,
                searchable: false,
                width: '40px',
                render: function(data, type, row) {
                    return `<div class="sortable-handle"><i class='bx bx-menu'></i></div>`;
                }
            },
            {
                data: 'sort_no',
                name: 'sort_no',
                orderable: true,
                searchable: false,
                width: '80px',
                render: function(data, type, row) {
                    const sortNo = data || 0;
                    return `<span class="badge bg-primary">${sortNo}</span>`;
                }
            },
            { 
                data: 'name', 
                name: 'name',
                render: function(data) {
                    return data ? `<strong>${data}</strong>` : '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'actions', 
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (!data) return '<span class="text-muted">-</span>';
                    
                    // Parse actions if it's a JSON string
                    let actions = [];
                    try {
                        actions = typeof data === 'string' ? JSON.parse(data) : data;
                    } catch(e) {
                        // If not JSON, treat as comma-separated or single value
                        actions = data.includes(',') ? data.split(',').map(a => a.trim()) : [data];
                    }
                    
                    if (!Array.isArray(actions)) {
                        actions = [actions];
                    }
                    
                    const actionColors = {
                        'view': 'info',
                        'create': 'success',
                        'update': 'warning',
                        'delete': 'danger',
                        'export': 'secondary',
                        'import': 'primary',
                        'approve': 'success',
                        'assign': 'info'
                    };
                    
                    return actions.map(action => {
                        const color = actionColors[action.toLowerCase()] || 'secondary';
                        return `<span class="badge bg-${color} permission-badge me-1">${action}</span>`;
                    }).join('');
                }
            },
            { 
                data: 'roles_count', 
                name: 'roles_count',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `<span class="badge bg-success">${data || 0}</span>`;
                }
            },
            {
                data: 'is_active',
                name: 'is_active',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (data === undefined || data === null) {
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                    return data 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" title="Edit">
                            <i class='bx bx-edit'></i>
                        </button>
                        <input type="hidden" class="permission-id" value="${row.id}" data-permission-id="${row.id}">
                    `;
                }
            }
        ],
        order: [[1, 'asc']], 
        drawCallback: function(settings) {
            // Initialize SortableJS after DataTable draws
            if (sortableInstance) {
                sortableInstance.destroy();
            }
            
            const tbody = $('#permissionsTable tbody')[0];
            if (tbody) {
                sortableInstance = new Sortable(tbody, {
                    handle: '.sortable-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        // Update sort order
                        updateSortOrder();
                    }
                });
            }
        }
    });
    
    // Function to update sort order
    function updateSortOrder() {
        const rows = $('#permissionsTable tbody tr');
        const sortData = [];
        
        rows.each(function(index) {
            const row = $(this); 
                        let permissionId = row.find('.edit-btn').data('id');
                        if (!permissionId) { 
                            permissionId = row.data('id');
                        }
                        if (!permissionId) { 
                            permissionId = row.find('[data-permission-id]').data('permission-id');
                        }
            if (permissionId) {
                sortData.push({
                    id: permissionId,
                    sort_no: index + 1
                });
            }
        });
        
        if (sortData.length === 0) {
            return;
        }
        
        // Show loading indicator
        const loadingToast = showToast('info', 'Updating sort order...', true);
        
        $.ajax({
            url: '{{ route("permissions.updateSort") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                permissions: sortData
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message || 'Sort order updated successfully'); 
                    dataTable.ajax.reload(null, false); // false = don't reset paging
                } else {
                    showToast('error', response.message || 'Failed to update sort order');
                    // Reload to revert changes
                    dataTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'An error occurred while updating sort order');
                // Reload to revert changes
                dataTable.ajax.reload(null, false);
            }
        });
    }

    // Edit Permission
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ route("permissions.edit") }}',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    const p = response.permission;
                    
                    // Parse actions
                    let actions = [];
                    if (p.actions && Array.isArray(p.actions)) {
                        actions = p.actions;
                    } else if (p.action) {
                        try {
                            actions = JSON.parse(p.action);
                            if (!Array.isArray(actions)) {
                                actions = [p.action];
                            }
                        } catch(e) {
                            actions = p.action.includes(',') ? p.action.split(',').map(a => a.trim()) : [p.action];
                        }
                    }
                    
                    // Populate form
                    $('#editPermissionId').val(p.id);
                    $('#editPermissionName').val(p.name || '');
                    $('#editPermissionSortNo').val(p.sort_no || 0);
                    
                    // Clear all action checkboxes
                    $('input[name="edit_actions[]"]').prop('checked', false);
                    
                    // Check the actions that this permission has
                    actions.forEach(function(action) {
                        const actionLower = action.toLowerCase();
                        const actionId = 'editPermAction' + actionLower.charAt(0).toUpperCase() + actionLower.slice(1);
                        const checkbox = $('#' + actionId);
                        if (checkbox.length > 0) {
                            checkbox.prop('checked', true);
                        }
                    });
                    
                    // Reset form validation
                    $('#permissionEditForm').find('.is-invalid').removeClass('is-invalid');
                    $('#permissionEditForm').find('.invalid-feedback').text('');
                    
                    $('#editPermissionModal').modal('show');
                }
            }
        });
    });


    // Submit Edit Form
    $('#permissionEditForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        const id = $('#editPermissionId').val();
        const name = $('#editPermissionName').val().trim();
        const actions = $('input[name="edit_actions[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        const sortNo = $('#editPermissionSortNo').val() || 0;
        
        if (!name) {
            alert('Please enter a permission name');
            return;
        }
        
        if (actions.length === 0) {
            alert('Please select at least one action');
            return;
        }
        
        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("permissions.update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                name: name,
                actions: actions,
                sort_no: sortNo
            },
            success: function(response) {
                if (response.success) {
                    $('#editPermissionModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message || 'Permission updated successfully');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = form.find('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    showToast('error', xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Reset edit form when modal is closed
    $('#editPermissionModal').on('hidden.bs.modal', function() {
        $('#permissionEditForm')[0].reset();
        $('#permissionEditForm').find('.is-invalid').removeClass('is-invalid');
        $('#permissionEditForm').find('.invalid-feedback').text('');
        $('input[name="edit_actions[]"]').prop('checked', false);
    });

    // Toast notification function
    function showToast(type, message, persistent = false) {
        const toastContainer = $('.sa-app__toasts');
        if (toastContainer.length === 0) {
            $('body').append('<div class="sa-app__toasts position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        const toastId = 'toast-' + Date.now();
        
        let bgClass = 'bg-info';
        let icon = 'bx-info-circle';
        
        if (type === 'success') {
            bgClass = 'bg-success';
            icon = 'bx-check-circle';
        } else if (type === 'error') {
            bgClass = 'bg-danger';
            icon = 'bx-error-circle';
        } else if (type === 'warning') {
            bgClass = 'bg-warning';
            icon = 'bx-error-circle';
        } else if (type === 'info') {
            bgClass = 'bg-info';
            icon = 'bx-info-circle';
        }
        
        const toast = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body d-flex align-items-center">
                    <i class='bx ${icon} me-2' style='font-size: 1.5rem;'></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        $('.sa-app__toasts').append(toast);
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: !persistent,
            delay: persistent ? 0 : 3000
        });
        toastElement.show();
        
        $('#' + toastId).on('hidden.bs.toast', function() {
            $(this).remove();
        });
        
        return toastId;
    }

    // Add Permission
    $('#addPermissionBtn').on('click', function() {
        $('#addPermissionForm')[0].reset();
        $('input[name="actions[]"]').prop('checked', false);
        $('#permActionView, #permActionCreate, #permActionUpdate, #permActionDelete').prop('checked', true);
    });

    // Submit Add Permission Form
    $('#addPermissionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        const name = $('#permissionName').val().trim();
        const actions = $('input[name="actions[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (!name) {
            alert('Please enter a permission name');
            return;
        }
        
        if (actions.length === 0) {
            alert('Please select at least one action');
            return;
        }
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        // Generate slug from name
        const slug = name.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
        
        $.ajax({
            url: '{{ route("permissions.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                slug: slug,
                actions: actions
            },
            success: function(response) {
                if (response.success) {
                    $('#addPermissionModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message || 'Permission created successfully');
                } else {
                    showToast('error', response.message || 'Failed to create permission');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to create permission';
                showToast('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });
});
</script>
@endpush
