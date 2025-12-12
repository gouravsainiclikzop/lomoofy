@extends('layouts.admin')

@section('title', 'Permissions Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Permissions Management</h1>
                </div>

                <!-- Filter by Module/Group -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="filterModule" class="form-label">Filter by Module</label>
                                <select class="form-select" id="filterModule">
                                    <option value="">All Modules</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filterGroup" class="form-label">Filter by Page/Group</label>
                                <select class="form-select" id="filterGroup">
                                    <option value="">All Groups</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary w-100" id="clearFilters">
                                    <i class='bx bx-x'></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="permissionsTable">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Resource</th>
                                        <th>Action</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Page/Group</th>
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
                <input type="hidden" id="permissionId" name="id">
                <div class="modal-body" id="permissionDetails">
                    <!-- Permission details will be loaded here -->
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
<style>
    .bx {
        vertical-align: middle;
        font-size: 1.1rem;
    }
    .permission-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let dataTable;

    // Load modules and groups for filters
    function loadFilters() {
        $.ajax({
            url: '{{ route("permissions.data") }}',
            type: 'GET',
            data: { length: 1000 }, // Get more records for filters
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    const modules = [...new Set(response.data.map(p => p.module).filter(Boolean))].sort();
                    const groups = [...new Set(response.data.map(p => p.group).filter(Boolean))].sort();
                    
                    modules.forEach(module => {
                        $('#filterModule').append(`<option value="${module}">${module}</option>`);
                    });
                    
                    groups.forEach(group => {
                        $('#filterGroup').append(`<option value="${group}">${group}</option>`);
                    });
                }
            },
            error: function() {
                // Silently fail - filters are optional
            }
        });
    }

    // Initialize DataTable
    dataTable = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("permissions.data") }}',
            data: function(d) {
                d.module = $('#filterModule').val();
                d.group = $('#filterGroup').val();
            }
        },
        columns: [
            { 
                data: 'module', 
                name: 'module',
                render: function(data) {
                    return data ? `<span class="badge bg-primary permission-badge">${data}</span>` : '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'resource', 
                name: 'resource',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'action', 
                name: 'action',
                render: function(data) {
                    if (!data) return '<span class="text-muted">-</span>';
                    const actionColors = {
                        'view': 'info',
                        'create': 'success',
                        'update': 'warning',
                        'delete': 'danger',
                        'export': 'secondary',
                        'import': 'primary'
                    };
                    const color = actionColors[data] || 'secondary';
                    return `<span class="badge bg-${color} permission-badge">${data}</span>`;
                }
            },
            { data: 'name', name: 'name' },
            { 
                data: 'slug', 
                name: 'slug',
                render: function(data) {
                    return `<code class="text-muted">${data}</code>`;
                }
            },
            { 
                data: 'group', 
                name: 'group',
                render: function(data) {
                    return data ? `<span class="badge bg-secondary permission-badge">${data}</span>` : '<span class="text-muted">-</span>';
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
                    `;
                }
            }
        ],
        order: [[0, 'asc'], [1, 'asc'], [2, 'asc']]
    });

    // Load filters on page load
    loadFilters();

    // Apply filters
    $('#filterModule, #filterGroup').on('change', function() {
        dataTable.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterModule, #filterGroup').val('').trigger('change');
    });

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
                    const hasIsActive = p.is_active !== undefined && p.is_active !== null;
                    
                    // Format module, resource, action, group - show parsed values if available
                    const module = p.module || parseModuleFromSlug(p.slug) || '-';
                    const resource = p.resource || parseResourceFromSlug(p.slug) || '-';
                    const action = p.action || parseActionFromSlug(p.slug) || '-';
                    const group = p.group || parseGroupFromSlug(p.slug, module) || '-';
                    
                    const details = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Name:</strong></label>
                                <div>${p.name || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Slug:</strong></label>
                                <div><code>${p.slug || '-'}</code></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Module:</strong></label>
                                <div>${module !== '-' ? `<span class="badge bg-primary">${module}</span>` : '<span class="text-muted">-</span>'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Resource:</strong></label>
                                <div>${resource !== '-' ? resource : '<span class="text-muted">-</span>'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Action:</strong></label>
                                <div>${action !== '-' ? `<span class="badge bg-info">${action}</span>` : '<span class="text-muted">-</span>'}</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Page/Group:</strong></label>
                                <div>${group !== '-' ? `<span class="badge bg-secondary">${group}</span>` : '<span class="text-muted">-</span>'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Assigned to Roles:</strong></label>
                                <div><span class="badge bg-success">${p.roles_count || 0} role(s)</span></div>
                            </div>
                        </div>
                        ${hasIsActive ? `
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="permissionIsActive" name="is_active" ${p.is_active ? 'checked' : ''}>
                                <label class="form-check-label" for="permissionIsActive">
                                    <strong>Active</strong>
                                </label>
                            </div>
                        </div>
                        ` : ''}
                        <div class="mb-3">
                            <label for="permissionDescription" class="form-label"><strong>Description</strong></label>
                            <textarea class="form-control" id="permissionDescription" name="description" rows="4" placeholder="Enter permission description...">${p.description || ''}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    `;
                    $('#permissionId').val(p.id);
                    $('#permissionDetails').html(details);
                    $('#editPermissionModal').modal('show');
                }
            }
        });
    });

    // Helper functions to parse permission slug
    function parseModuleFromSlug(slug) {
        if (!slug) return null;
        
        const moduleMap = {
            'dashboard': 'dashboard',
            'users': 'users',
            'user': 'users',
            'roles': 'roles',
            'role': 'roles',
            'permissions': 'permissions',
            'permission': 'permissions',
            'products': 'products',
            'product': 'products',
            'categories': 'categories',
            'category': 'categories',
            'orders': 'orders',
            'order': 'orders',
            'customers': 'customers',
            'customer': 'customers',
            'leads': 'leads',
            'lead': 'leads',
            'inventory': 'inventory',
            'reports': 'reports',
            'report': 'reports',
            'settings': 'settings',
            'setting': 'settings',
        };

        const parts = slug.split('-');
        for (let part of parts) {
            if (moduleMap[part]) {
                return moduleMap[part];
            }
        }
        return parts.length > 1 ? parts[1] : parts[0];
    }

    function parseResourceFromSlug(slug) {
        if (!slug) return null;
        const parts = slug.split('-');
        return parts.length >= 2 ? parts[1] : null;
    }

    function parseActionFromSlug(slug) {
        if (!slug) return null;
        
        const actionMap = {
            'view': 'view',
            'create': 'create',
            'edit': 'update',
            'update': 'update',
            'delete': 'delete',
            'export': 'export',
            'import': 'import',
            'assign': 'assign',
            'approve': 'approve',
            'reject': 'reject',
        };

        const parts = slug.split('-');
        if (parts.length > 0) {
            const action = parts[0].toLowerCase();
            return actionMap[action] || action;
        }
        return null;
    }

    function parseGroupFromSlug(slug, module) {
        const groupMap = {
            'dashboard': 'Dashboard',
            'users': 'User Management',
            'roles': 'Roles & Permissions',
            'permissions': 'Roles & Permissions',
            'products': 'Products',
            'categories': 'Categories',
            'orders': 'Orders',
            'customers': 'Customers',
            'leads': 'Leads',
            'inventory': 'Inventory',
            'reports': 'Reports',
            'settings': 'Settings',
        };

        if (module && groupMap[module]) {
            return groupMap[module];
        }

        const moduleFromSlug = parseModuleFromSlug(slug);
        if (moduleFromSlug && groupMap[moduleFromSlug]) {
            return groupMap[moduleFromSlug];
        }

        return null;
    }

    // Submit Edit Form
    $('#permissionEditForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        
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
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editPermissionModal').modal('hide');
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

    // Toast notification function
    function showToast(type, message) {
        const toastContainer = $('.sa-app__toasts');
        if (toastContainer.length === 0) {
            $('body').append('<div class="sa-app__toasts position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
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
        
        $('.sa-app__toasts').append(toast);
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
