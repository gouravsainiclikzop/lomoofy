@extends('layouts.admin')

@section('title', 'User Roles Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">User Roles Management</h1>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="userRolesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
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

<!-- Assign Roles Modal -->
<div class="modal fade" id="assignRolesModal" tabindex="-1" aria-labelledby="assignRolesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignRolesModalLabel">
                    <i class='fas fa-user-check'></i> Assign Roles to <span id="userName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignRolesForm">
                @csrf
                <input type="hidden" id="userId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Roles</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div id="rolesList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="mt-2">Loading roles...</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Select one or more roles for this user</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Roles</span>
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
    .role-checkbox {
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let dataTable;
    let allRoles = [];

    // Initialize DataTable
    dataTable = $('#userRolesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("user-roles.data") }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { 
                data: 'roles', 
                name: 'roles',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (data) {
                        const roles = data.split(', ');
                        return roles.map(role => '<span class="badge bg-primary me-1">' + role + '</span>').join('');
                    }
                    return '<span class="text-muted">No roles</span>';
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
                        <button class="btn btn-sm btn-primary assign-roles-btn" 
                                data-id="${row.id}" 
                                data-name="${row.name}"
                                data-roles='${JSON.stringify(row.role_ids)}' 
                                title="Assign Roles">
                            <i class='fas fa-user-check'></i> Assign Roles
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']]
    });

    // Load roles
    function loadRoles(selectedIds = []) {
        $.ajax({
            url: '{{ route("user-roles.roles") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    allRoles = response.roles;
                    renderRoles(selectedIds);
                }
            }
        });
    }

    // Render roles checkboxes
    function renderRoles(selectedIds = []) {
        let html = '';
        allRoles.forEach(function(role) {
            const checked = selectedIds.includes(role.id) ? 'checked' : '';
            html += `
                <div class="form-check role-checkbox">
                    <input class="form-check-input" type="checkbox" name="roles[]" value="${role.id}" id="role_${role.id}" ${checked}>
                    <label class="form-check-label" for="role_${role.id}">
                        <strong>${role.name}</strong>
                        ${role.description ? '<br><small class="text-muted">' + role.description + '</small>' : ''}
                    </label>
                </div>
            `;
        });
        $('#rolesList').html(html);
    }

    // Assign Roles to User
    $(document).on('click', '.assign-roles-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const roleIds = $(this).data('roles') || [];
        
        $('#userId').val(id);
        $('#userName').text(name);
        
        loadRoles(roleIds);
        
        $('#assignRolesModal').modal('show');
    });

    // Submit Assign Roles Form
    $('#assignRolesForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("user-roles.update") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#assignRolesModal').modal('hide');
                    dataTable.ajax.reload();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    showToast('error', Object.values(errors)[0][0]);
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

