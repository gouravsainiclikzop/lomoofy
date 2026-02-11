@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Roles Management</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('permissions.index') }}" class="btn btn-info d-none">
                            <i class='fas fa-shield-alt'></i> Manage Permissions
                        </a>
                        @if(auth()->user()->hasPermission('role_permission.create'))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" id="addRoleBtn">
                                <i class='fas fa-plus'></i> Add Role
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="rolesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Users</th>
                                        <th>Manage Permissions</th>
                                        <th>Created</th>
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

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">
                    <i class='fas fa-shield'></i> <span id="modalTitle">Add Role</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm">
                @csrf
                <input type="hidden" id="roleId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="roleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roleName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="roleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="roleDescription" name="description" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Role</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsModalLabel">
                    <i class='bx bx-shield'></i> Manage Permissions - <span id="permissionsRoleName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permissionsRoleId">
                <div class="border rounded p-3" style="height: 100%;">
                    <div id="permissionsList">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Loading permissions...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePermissionsBtn">
                    <span class="btn-text">Save Permissions</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
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
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class='fas fa-trash'></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role?</p>
                <p class="text-muted mb-0"><strong>Note:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <span class="btn-text">Delete</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    .bx {
        vertical-align: middle;
        font-size: 1.1rem;
    }
    .permission-checkbox {
        margin-bottom: 0.5rem;
    }
    #permissionsList {
        max-height: 500px;
        overflow-y: auto;
    }
    .permission-item {
        border-left: 3px solid #0d6efd;
    }
    .permission-item .card-body {
        padding: 1rem;
    }
    .permission-main-checkbox {
        cursor: pointer;
        width: 1.2em;
        height: 1.2em;
        margin-right: 0.5rem;
    }
    .action-checkbox {
        cursor: pointer;
        width: 1.1em;
        height: 1.1em;
    }
    .permissions-container {
        padding: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
const rolePermissions = {
    view: @json(auth()->user()->hasPermission('role_permission.view')),
    create: @json(auth()->user()->hasPermission('role_permission.create')),
    update: @json(auth()->user()->hasPermission('role_permission.update')),
    delete: @json(auth()->user()->hasPermission('role_permission.delete')),
    assign: @json(auth()->user()->hasPermission('role_permission.assign'))
};

$(document).ready(function() {
    let dataTable;
    let deleteId = null;
    let allPermissions = [];

    // Initialize DataTable
    dataTable = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("roles.data") }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { 
                data: 'description', 
                name: 'description',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'users_count', 
                name: 'users_count',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return '<span class="badge bg-info">' + data + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (rolePermissions.assign) {
                        return `
                            <button class="btn btn-sm btn-info permissions-btn" data-id="${row.id}" data-name="${row.name}" title="Manage Permissions">
                              Assign Permissions
                            </button>
                        `;
                    }
                    return '<span class="text-muted">-</span>';
                }
            },
            
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="d-flex gap-1">';
                    if (rolePermissions.update) {
                        actions += `<button class="btn btn-sm btn-secondary edit-btn" data-id="${row.id}" title="Edit">
                                <i class='fas fa-edit'></i>
                            </button>`;
                    }
                    if (rolePermissions.delete) {
                        actions += `<button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete">
                                <i class='fas fa-trash'></i>
                            </button>`;
                    }
                    actions += '</div>';
                    return actions || '<span class="text-muted">-</span>';
                }
            }
        ],
        order: [[0, 'asc']]
    });

    // Load permissions for permissions modal
    function loadPermissionsForModal(roleId, permissionsData = []) {
        // Convert permissions_data array to a map for easy lookup
        const permissionsMap = {};
        permissionsData.forEach(function(perm) {
            permissionsMap[perm.id] = perm.actions || [];
        });
        
        // Store for use in renderPermissionsGrouped
        window.selectedPermissionsData = permissionsMap;
        $.ajax({
            url: '{{ route("roles.permissions") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    allPermissions = response.permissions || [];
                    console.log('Permissions loaded:', allPermissions.length);
                    console.log('Grouped data:', response.grouped);
                    console.log('Structure:', response.structure);
                    renderPermissionsGrouped([], response.grouped || [], response.structure || null);
                } else {
                    console.error('Failed to load permissions:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading permissions:', error);
            }
        });
    }

    // Render permissions with actions as checkboxes
    function renderPermissionsGrouped(selectedIds = [], grouped = [], structureData = null) {
        // Get selected permissions data from window if available
        const selectedPermissionsData = window.selectedPermissionsData || {};
        // Collect all permissions
        let allPermsData = [];
        
        if (allPermissions && allPermissions.length > 0) {
            allPermissions.forEach(function(permission) {
                allPermsData.push(permission);
            });
        }
        
        if (grouped && grouped.length > 0) {
            grouped.forEach(function(group) {
                if (group.permissions && group.permissions.length > 0) {
                    group.permissions.forEach(function(permission) {
                        // Check if already added
                        const alreadyAdded = allPermsData.some(function(item) {
                            return item.id === permission.id;
                        });
                        
                        if (!alreadyAdded) {
                            allPermsData.push(permission);
                        }
                    });
                }
            });
        }

        if (allPermsData.length === 0) {
            $('#permissionsList').html('<div class="alert alert-info">No permissions found.</div>');
            return;
        }

        // Standard action order
        const actionOrder = ['view', 'create', 'update', 'edit', 'delete', 'export', 'import', 'approve', 'publish', 'assign'];
        
        // Build HTML with permissions and their actions as checkboxes
        let html = '<div class="permissions-container">';
        
        allPermsData.forEach(function(permission) {
            // Parse actions
            let actions = [];
            if (permission.actions && Array.isArray(permission.actions)) {
                actions = permission.actions;
            } else if (permission.action) {
                try {
                    actions = JSON.parse(permission.action);
                    if (!Array.isArray(actions)) {
                        actions = [permission.action];
                    }
                } catch(e) {
                    actions = permission.action.includes(',') 
                        ? permission.action.split(',').map(a => a.trim()) 
                        : [permission.action];
                }
            }
            
            if (actions.length === 0) {
                return; // Skip permissions without actions
            }
            
            // Sort actions
            const sortedActions = actions.sort(function(a, b) {
                const aIndex = actionOrder.indexOf(a.toLowerCase());
                const bIndex = actionOrder.indexOf(b.toLowerCase());
                if (aIndex === -1 && bIndex === -1) return a.localeCompare(b);
                if (aIndex === -1) return 1;
                if (bIndex === -1) return -1;
                return aIndex - bIndex;
            });
            
            // Check if this permission is selected and get selected actions
            const isPermissionSelected = selectedIds.includes(permission.id);
            const selectedActions = selectedPermissionsData[permission.id] || [];
            const isMainChecked = isPermissionSelected || selectedActions.length > 0;
            
            html += '<div class="card mb-3 permission-item">';
            html += '<div class="card-body">';
            html += '<div class="row align-items-center">';
            
            // Permission Name with checkbox
            html += '<div class="col-md-3">';
            html += `<div class="form-check">`;
            html += `<input class="form-check-input permission-main-checkbox" 
                           type="checkbox" 
                           name="permissions[]" 
                           value="${permission.id}" 
                           id="perm_${permission.id}" 
                           ${isMainChecked ? 'checked' : ''}
                           data-permission-id="${permission.id}">`;
            html += `<label class="form-check-label fw-bold" for="perm_${permission.id}">`;
            html += `${permission.name || 'Unnamed Permission'}`;
            html += `</label>`;
            html += `</div>`;
            html += `</div>`;
            
            // Actions checkboxes
            html += '<div class="col-md-9">';
            html += '<div class="d-flex flex-wrap gap-3">';
            
            sortedActions.forEach(function(action) {
                const actionId = `perm_${permission.id}_action_${action}`;
                const isActionSelected = selectedActions.includes(action) || (isPermissionSelected && selectedActions.length === 0);
                html += `<div class="form-check">`;
                html += `<input class="form-check-input action-checkbox" 
                               type="checkbox" 
                               id="${actionId}"
                               data-permission-id="${permission.id}"
                               data-action="${action}"
                               ${isActionSelected ? 'checked' : ''}>`;
                html += `<label class="form-check-label" for="${actionId}">`;
                html += `${action.charAt(0).toUpperCase() + action.slice(1)}`;
                html += `</label>`;
                html += `</div>`;
            });
            
            html += '</div>';  
            html += '</div>'; 
            html += '</div>'; 
            html += '</div>';  
            html += '</div>';  
        });
        
        html += '</div>'; 
        
        $('#permissionsList').html(html);
         
        $(document).off('change', '.permission-main-checkbox').on('change', '.permission-main-checkbox', function() {
            const permissionId = $(this).data('permission-id');
            const isChecked = $(this).is(':checked');
             
            $(`.action-checkbox[data-permission-id="${permissionId}"]`).prop('checked', isChecked);
        });
         
        $(document).off('change', '.action-checkbox').on('change', '.action-checkbox', function() {
            const permissionId = $(this).data('permission-id');
            const mainCheckbox = $(`.permission-main-checkbox[data-permission-id="${permissionId}"]`);
             
            const anyActionChecked = $(`.action-checkbox[data-permission-id="${permissionId}"]:checked`).length > 0;
            mainCheckbox.prop('checked', anyActionChecked);
        });
    }

 
    $('#addRoleBtn').on('click', function() {
        if (!rolePermissions.create) {
            showToast('error', 'You do not have permission to create roles.');
            return;
        }
        $('#roleForm')[0].reset();
        $('#roleId').val('');
        $('#modalTitle').text('Add Role');
        $('#roleForm').find('.is-invalid').removeClass('is-invalid');
        $('#roleForm').find('.invalid-feedback').text('');
    });
 
    $(document).on('click', '.edit-btn', function() {
        if (!rolePermissions.update) {
            showToast('error', 'You do not have permission to update roles.');
            return;
        }
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ route("roles.edit") }}',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    $('#roleId').val(response.role.id);
                    $('#roleName').val(response.role.name);
                    $('#roleDescription').val(response.role.description);
                    $('#modalTitle').text('Edit Role');
                    
                    $('#roleModal').modal('show');
                }
            }
        });
    });
 
    $(document).on('click', '.permissions-btn', function() {
        if (!rolePermissions.assign) {
            showToast('error', 'You do not have permission to assign permissions.');
            return;
        }
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');
        
        $('#permissionsRoleId').val(roleId);
        $('#permissionsRoleName').text(roleName);
         
        $.ajax({
            url: '{{ route("roles.edit") }}',
            type: 'GET',
            data: { id: roleId },
            success: function(response) {
                if (response.success) {
                    loadPermissionsForModal(roleId, response.permissions_data || []);
                    $('#permissionsModal').modal('show');
                }
            }
        });
    });

    // Save Permissions
    $('#savePermissionsBtn').on('click', function() {
        if (!rolePermissions.assign) {
            showToast('error', 'You do not have permission to assign permissions.');
            return;
        }
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        const roleId = $('#permissionsRoleId').val();
        const selectedPermissions = [];
         
        $('#permissionsList .permission-item').each(function() {
            const mainCheckbox = $(this).find('.permission-main-checkbox'); 
            const permissionId = mainCheckbox.attr('data-permission-id') || mainCheckbox.data('permission-id');
             
            if (!permissionId || isNaN(permissionId)) {
                console.warn('Permission ID not found or invalid for permission item:', permissionId);
                return;
            }
             
            const anyActionChecked = $(this).find('.action-checkbox:checked').length > 0;
            
            if (mainCheckbox.is(':checked') || anyActionChecked) {
                const selectedActions = [];
                $(this).find('.action-checkbox:checked').each(function() {
                    const action = $(this).attr('data-action') || $(this).data('action');  
                    if (action) {
                        selectedActions.push(action);
                    }
                });
                
                const permId = parseInt(permissionId, 10);
                if (permId && !isNaN(permId)) {
                    selectedPermissions.push({
                        id: permId,
                        actions: selectedActions
                    });
                }
            }
        });
        
        console.log('Sending permissions:', selectedPermissions);
        
        btn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("roles.update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: roleId,
                permissions: selectedPermissions
            },
            success: function(response) {
                if (response.success) {
                    $('#permissionsModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    showToast('error', xhr.responseJSON.message || 'Validation error');
                } else {
                    showToast('error', 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Submit Role Form
    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        const isEdit = $('#roleId').val() !== '';
        const url = isEdit ? '{{ route("roles.update") }}' : '{{ route("roles.store") }}';
        
        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        // Remove permissions from form data as they're managed separately
        const formData = form.serializeArray().filter(item => item.name !== 'permissions[]');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                if (response.success) {
                    $('#roleModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message);
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
                    showToast('error', 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Delete Role
    $(document).on('click', '.delete-btn', function() {
        if (!rolePermissions.delete) {
            showToast('error', 'You do not have permission to delete roles.');
            return;
        }
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (!rolePermissions.delete) {
            showToast('error', 'You do not have permission to delete roles.');
            return;
        }
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("roles.delete") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: deleteId
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON.message) {
                    showToast('error', xhr.responseJSON.message);
                } else {
                    showToast('error', 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });

    // Toast notification function
    function showToast(type, message) {
        const toastContainer = $('.sa-app__toasts');
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        
        const toast = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body d-flex align-items-center">
                    <i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2' style='font-size: 1.5rem;'></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.append(toast);
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000
        });
        toastElement.show();
        
        $('#' + toastId).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush

