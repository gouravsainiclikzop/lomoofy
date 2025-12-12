@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Roles Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" id="addRoleBtn">
                        <i class='fas fa-plus'></i> Add Role
                    </button>
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
    #permissionsList table {
        font-size: 0.9rem;
    }
    #permissionsList table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    #permissionsList table td {
        vertical-align: middle;
    }
    #permissionsList .form-check-input {
        cursor: pointer;
        width: 1.2em;
        height: 1.2em;
    }
</style>
@endpush

@push('scripts')
<script>
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
                    return `
                        <button class="btn btn-sm btn-info permissions-btn" data-id="${row.id}" data-name="${row.name}" title="Manage Permissions">
                          Permissions
                        </button>
                    `;
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
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-secondary edit-btn" data-id="${row.id}" title="Edit">
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete">
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']]
    });

    // Load permissions for permissions modal
    function loadPermissionsForModal(roleId, selectedIds = []) {
        $.ajax({
            url: '{{ route("roles.permissions") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    allPermissions = response.permissions || [];
                    console.log('Permissions loaded:', allPermissions.length);
                    console.log('Grouped data:', response.grouped);
                    renderPermissionsGrouped(selectedIds, response.grouped || []);
                } else {
                    console.error('Failed to load permissions:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading permissions:', error);
            }
        });
    }

    // Render permissions grouped by module with related actions in same row - Table Layout
    function renderPermissionsGrouped(selectedIds = [], grouped = []) {
        // Collect all permissions and group them properly
        let allPermsData = [];
        
        console.log('Rendering permissions. Grouped length:', grouped.length, 'All permissions:', allPermissions.length);
        
        if (grouped && grouped.length > 0) {
            grouped.forEach(function(group) {
                if (group.permissions && group.permissions.length > 0) {
                    group.permissions.forEach(function(permission) {
                        allPermsData.push({
                            module: group.module || permission.module || 'Other',
                            resource: permission.resource || group.resource || group.module || 'Other',
                            permission: permission
                        });
                    });
                }
            });
        }
        
        // Also process allPermissions directly if grouped is empty or incomplete
        if (allPermissions && allPermissions.length > 0) {
            allPermissions.forEach(function(permission) {
                // Only add if not already added from grouped data
                const alreadyAdded = allPermsData.some(function(item) {
                    return item.permission.id === permission.id;
                });
                
                if (!alreadyAdded) {
                    allPermsData.push({
                        module: permission.module || 'Other',
                        resource: permission.resource || permission.module || 'Other',
                        permission: permission
                    });
                }
            });
        }
        
        console.log('Total permissions to render:', allPermsData.length);
        
        // Group by module and resource
        const tableData = {};
        allPermsData.forEach(function(item) {
            const key = item.module + '|' + item.resource;
            if (!tableData[key]) {
                tableData[key] = {
                    module: item.module,
                    resource: item.resource,
                    permissions: {}
                };
            }
            const action = item.permission.action || 'other';
            tableData[key].permissions[action] = item.permission;
        });
        
        // Get all unique actions to create columns
        const allActions = new Set();
        Object.values(tableData).forEach(function(row) {
            Object.keys(row.permissions).forEach(function(action) {
                allActions.add(action);
            });
        });
        
        // Standard action order
        const actionOrder = ['view', 'create', 'update', 'edit', 'delete', 'export', 'import', 'approve', 'publish'];
        const sortedActions = Array.from(allActions).sort(function(a, b) {
            const aIndex = actionOrder.indexOf(a);
            const bIndex = actionOrder.indexOf(b);
            if (aIndex === -1 && bIndex === -1) return a.localeCompare(b);
            if (aIndex === -1) return 1;
            if (bIndex === -1) return -1;
            return aIndex - bIndex;
        });
        
        // Build table HTML
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">Module</th>
                            <th style="width: 20%;">Resource</th>
        `;
        
        sortedActions.forEach(function(action) {
            const actionLabel = action.charAt(0).toUpperCase() + action.slice(1);
            html += `<th class="text-center" style="width: ${80 / sortedActions.length}%;">${actionLabel}</th>`;
        });
        
        html += `
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Sort table data by module then resource
        const sortedRows = Object.values(tableData).sort(function(a, b) {
            if (a.module !== b.module) {
                return a.module.localeCompare(b.module);
            }
            return a.resource.localeCompare(b.resource);
        });
        
        sortedRows.forEach(function(row) {
            html += `<tr>`;
            html += `<td><strong class="text-primary">${row.module}</strong></td>`;
            html += `<td>${row.resource}</td>`;
            
            sortedActions.forEach(function(action) {
                const permission = row.permissions[action];
                if (permission) {
                    const checked = selectedIds.includes(permission.id) ? 'checked' : '';
                    html += `
                        <td class="text-center">
                            <input class="form-check-input permission-checkbox" 
                                   type="checkbox" 
                                   name="permissions[]" 
                                   value="${permission.id}" 
                                   id="perm_${permission.id}" 
                                   ${checked}
                                   title="${permission.name || (action + ' ' + row.resource)}">
                        </td>
                    `;
                } else {
                    html += `<td class="text-center text-muted">-</td>`;
                }
            });
            
            html += `</tr>`;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        $('#permissionsList').html(html);
    }

    // Add Role Button
    $('#addRoleBtn').on('click', function() {
        $('#roleForm')[0].reset();
        $('#roleId').val('');
        $('#modalTitle').text('Add Role');
        $('#roleForm').find('.is-invalid').removeClass('is-invalid');
        $('#roleForm').find('.invalid-feedback').text('');
    });

    // Edit Role
    $(document).on('click', '.edit-btn', function() {
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

    // Open Permissions Modal
    $(document).on('click', '.permissions-btn', function() {
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');
        
        $('#permissionsRoleId').val(roleId);
        $('#permissionsRoleName').text(roleName);
        
        // Load role permissions
        $.ajax({
            url: '{{ route("roles.edit") }}',
            type: 'GET',
            data: { id: roleId },
            success: function(response) {
                if (response.success) {
                    loadPermissionsForModal(roleId, response.permission_ids || []);
                    $('#permissionsModal').modal('show');
                }
            }
        });
    });

    // Save Permissions
    $('#savePermissionsBtn').on('click', function() {
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        const roleId = $('#permissionsRoleId').val();
        const selectedPermissions = [];
        
        $('#permissionsList .permission-checkbox:checked').each(function() {
            selectedPermissions.push($(this).val());
        });
        
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
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
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

