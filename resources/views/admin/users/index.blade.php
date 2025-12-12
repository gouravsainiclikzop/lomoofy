@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 m-0">Users Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn">
                        <i class='fas fa-plus'></i> Add User
                    </button>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Image</th>
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

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">
                    <i class='fas fa-user-plus'></i> <span id="modalTitle">Add User</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="userId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="userName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userPassword" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                            <input type="password" class="form-control" id="userPassword" name="password">
                            <div class="form-text" id="passwordHelp">Minimum 8 characters</div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userPasswordConfirmation" class="form-label">Confirm Password <span class="text-danger" id="confirmRequired">*</span></label>
                            <input type="password" class="form-control" id="userPasswordConfirmation" name="password_confirmation">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="userImage" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="userImage" name="image" accept="image/*">
                            <div class="form-text">Recommended size: 400x400px (Max: 2MB)</div>
                            <div class="invalid-feedback"></div>
                            <div class="mt-2" id="imagePreviewContainer" style="display: none;">
                                <img id="imagePreview" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Roles</label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <div id="rolesList">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="mt-2">Loading roles...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save User</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
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
                <p>Are you sure you want to delete this user?</p>
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
    .role-checkbox {
        margin-bottom: 0.5rem;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let dataTable;
    let deleteId = null;
    let allRoles = [];
    let isEditMode = false;

    // Initialize DataTable
    dataTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("users.data") }}',
        columns: [
            { 
                data: 'image', 
                name: 'image',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (data) {
                        return '<img src="/storage/' + data + '" class="user-avatar" alt="User">';
                    }
                    return '<img src="{{ asset("assets/images/customers/customer-4-64x64.jpg") }}" class="user-avatar" alt="User">';
                }
            },
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
                        <button class="btn btn-sm btn-secondary edit-btn" data-id="${row.id}" title="Edit">
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete">
                            <i class='fas fa-trash'></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[1, 'asc']]
    });

    // Load roles
    function loadRoles(selectedIds = []) {
        $.ajax({
            url: '{{ route("users.roles") }}',
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

    // Image preview
    $('#userImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreviewContainer').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagePreviewContainer').hide();
        }
    });

    // Add User Button
    $('#addUserBtn').on('click', function() {
        isEditMode = false;
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#modalTitle').text('Add User');
        $('#userForm').find('.is-invalid').removeClass('is-invalid');
        $('#userForm').find('.invalid-feedback').text('');
        $('#imagePreviewContainer').hide();
        
        // Make password required for new users
        $('#userPassword').prop('required', true);
        $('#userPasswordConfirmation').prop('required', true);
        $('#passwordRequired').show();
        $('#confirmRequired').show();
        $('#passwordHelp').text('Minimum 8 characters');
        
        loadRoles();
    });

    // Edit User
    $(document).on('click', '.edit-btn', function() {
        isEditMode = true;
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ route("users.edit") }}',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    $('#userId').val(response.user.id);
                    $('#userName').val(response.user.name);
                    $('#userEmail').val(response.user.email);
                    $('#modalTitle').text('Edit User');
                    
                    // Show existing image if available
                    if (response.user.image) {
                        $('#imagePreview').attr('src', '/storage/' + response.user.image);
                        $('#imagePreviewContainer').show();
                    } else {
                        $('#imagePreviewContainer').hide();
                    }
                    
                    // Make password optional for editing
                    $('#userPassword').prop('required', false);
                    $('#userPasswordConfirmation').prop('required', false);
                    $('#passwordRequired').hide();
                    $('#confirmRequired').hide();
                    $('#passwordHelp').text('Leave blank to keep current password');
                    
                    loadRoles(response.role_ids);
                    
                    $('#userModal').modal('show');
                }
            }
        });
    });

    // Submit User Form
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        const url = isEditMode ? '{{ route("users.update") }}' : '{{ route("users.store") }}';
        
        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#userModal').modal('hide');
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

    // Delete User
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
            url: '{{ route("users.delete") }}',
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

