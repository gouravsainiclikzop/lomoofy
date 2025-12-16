@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <!-- Left Column - Profile Info -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="sa-symbol sa-symbol--shape--rounded" style="--sa-symbol-size: 8rem; margin: 0 auto;">
                                <img id="profileImagePreview" src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : asset('assets/images/customers/customer-4-64x64.jpg') }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;"/>
                            </div>
                        </div>
                        <h5 class="mb-1" id="profileName">{{ Auth::user()->name }}</h5>
                        <p class="text-muted mb-3" id="profileEmail">{{ Auth::user()->email }}</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changeImageModal">
                            <i class='fas fa-image-add'></i> Change Photo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Profile Settings -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Profile Information</h5>
                        
                        <!-- Name Section -->
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label text-muted mb-1">Full Name</label>
                                    <div class="fw-medium" id="displayName">{{ Auth::user()->name }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#changeNameModal">
                                    <i class='fas fa-edit'></i> Edit
                                </button>
                            </div>
                        </div>

                        <!-- Email Section -->
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label text-muted mb-1">Email Address</label>
                                    <div class="fw-medium" id="displayEmail">{{ Auth::user()->email }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#changeEmailModal">
                                    <i class='fas fa-edit'></i> Edit
                                </button>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label text-muted mb-1">Password</label>
                                    <div class="fw-medium">••••••••</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class='fas fa-lock-alt'></i> Change
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="card mt-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Account Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Member Since</label>
                                <div class="fw-medium">{{ Auth::user()->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Last Updated</label>
                                <div class="fw-medium">{{ Auth::user()->updated_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Settings Card -->
                <div class="card mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Company Information</h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#companySettingsModal">
                                <i class='fas fa-edit'></i> Edit
                            </button>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Company Name</label>
                                <div class="fw-medium" id="displayCompanyName">{{ $companySettings->company_name ?? 'Lomoof' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Company Logo Text</label>
                                <div class="fw-medium" id="displayCompanyLogoText">{{ $companySettings->company_logo_text ?? 'Lomoofy' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Company Logo</label>
                                <div id="displayCompanyLogo">
                                    @if($companySettings->company_logo ?? null)
                                        <img src="{{ asset('storage/' . $companySettings->company_logo) }}" alt="Company Logo" style="max-height: 60px; max-width: 200px;" class="img-thumbnail">
                                    @else
                                        <span class="text-muted">No logo uploaded</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Phone</label>
                                <div class="fw-medium" id="displayCompanyPhone">{{ $companySettings->phone ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Email</label>
                                <div class="fw-medium" id="displayCompanyEmail">{{ $companySettings->email ?? '-' }}</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted mb-1">Address</label>
                                <div class="fw-medium" id="displayCompanyAddress">{{ $companySettings->address ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Image Modal -->
<div class="modal fade" id="changeImageModal" tabindex="-1" aria-labelledby="changeImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeImageModalLabel">
                    <i class='fas fa-image-add'></i> Change Profile Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeImageForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profileImage" class="form-label">Choose New Photo</label>
                        <input type="file" class="form-control" id="profileImage" name="image" accept="image/*" required>
                        <div class="form-text">Recommended size: 400x400px (Max: 2MB)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Preview:</label>
                        <div class="text-center">
                            <img id="imagePreview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; display: none; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Photo</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Name Modal -->
<div class="modal fade" id="changeNameModal" tabindex="-1" aria-labelledby="changeNameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeNameModalLabel">
                    <i class='fas fa-user'></i> Change Name
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeNameForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Changes</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Email Modal -->
<div class="modal fade" id="changeEmailModal" tabindex="-1" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeEmailModalLabel">
                    <i class='fas fa-envelope'></i> Change Email Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeEmailForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="current_password_email" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password_email" name="current_password" required>
                        <div class="form-text">Please confirm your password to change email</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Changes</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class='fas fa-lock-alt'></i> Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Minimum 8 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Change Password</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Company Settings Modal -->
<div class="modal fade" id="companySettingsModal" tabindex="-1" aria-labelledby="companySettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companySettingsModalLabel">
                    <i class='fas fa-building'></i> Edit Company Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="companySettingsForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $companySettings->company_name ?? 'Lomoof' }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="company_logo_text" class="form-label">Company Logo Text</label>
                        <input type="text" class="form-control" id="company_logo_text" name="company_logo_text" value="{{ $companySettings->company_logo_text ?? 'Lomoofy' }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="company_logo" class="form-label">Company Logo</label>
                        <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF, SVG (Max: 2MB)</small>
                        @if($companySettings->company_logo ?? null)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $companySettings->company_logo) }}" alt="Current Logo" style="max-height: 80px; max-width: 200px;" class="img-thumbnail">
                                <div class="mt-1">
                                    <small class="text-muted">Current logo</small>
                                </div>
                            </div>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="company_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="company_phone" name="phone" value="{{ $companySettings->phone ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="company_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="company_email" name="email" value="{{ $companySettings->email ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="company_address" class="form-label">Address</label>
                        <textarea class="form-control" id="company_address" name="address" rows="3">{{ $companySettings->address ?? '' }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Save Changes</span>
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
    .card {
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .bx {
        vertical-align: middle;
        font-size: 1.1rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    
    // Image preview on file selection
    $('#profileImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Change Image Form
    $('#changeImageForm').on('submit', function(e) {
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
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("profile.updateImage") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Update profile image
                    $('#profileImagePreview').attr('src', response.image_url);
                    $('.sa-toolbar-user__avatar img').attr('src', response.image_url);
                    
                    // Close modal
                    $('#changeImageModal').modal('hide');
                    
                    // Show success message
                    showToast('success', 'Profile photo updated successfully!');
                    
                    // Reset form
                    form[0].reset();
                    $('#imagePreview').hide();
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

    // Change Name Form
    $('#changeNameForm').on('submit', function(e) {
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
            url: '{{ route("profile.updateName") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Update name in UI
                    $('#profileName').text(response.name);
                    $('#displayName').text(response.name);
                    $('.sa-toolbar-user__title').text(response.name);
                    
                    // Close modal
                    $('#changeNameModal').modal('hide');
                    
                    // Show success message
                    showToast('success', 'Name updated successfully!');
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

    // Change Email Form
    $('#changeEmailForm').on('submit', function(e) {
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
            url: '{{ route("profile.updateEmail") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Update email in UI
                    $('#profileEmail').text(response.email);
                    $('#displayEmail').text(response.email);
                    $('.sa-toolbar-user__subtitle').text(response.email);
                    
                    // Close modal
                    $('#changeEmailModal').modal('hide');
                    
                    // Show success message
                    showToast('success', 'Email updated successfully!');
                    
                    // Reset password field
                    form.find('#current_password_email').val('');
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

    // Change Password Form
    $('#changePasswordForm').on('submit', function(e) {
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
            url: '{{ route("profile.updatePassword") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#changePasswordModal').modal('hide');
                    
                    // Show success message
                    showToast('success', 'Password changed successfully!');
                    
                    // Reset form
                    form[0].reset();
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

    // Company Settings Form
    $('#companySettingsForm').on('submit', function(e) {
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
        
        // Use FormData to handle file upload
        const formData = new FormData(form[0]);
        
        $.ajax({
            url: '{{ route("profile.updateCompanySettings") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update display values
                    $('#displayCompanyName').text(response.settings.company_name);
                    $('#displayCompanyLogoText').text(response.settings.company_logo_text);
                    $('#displayCompanyPhone').text(response.settings.phone || '-');
                    $('#displayCompanyEmail').text(response.settings.email || '-');
                    $('#displayCompanyAddress').text(response.settings.address || '-');
                    
                    // Update logo display
                    if (response.settings.logo_url) {
                        $('#displayCompanyLogo').html(`<img src="${response.settings.logo_url}" alt="Company Logo" style="max-height: 60px; max-width: 200px;" class="img-thumbnail">`);
                    } else {
                        $('#displayCompanyLogo').html('<span class="text-muted">No logo uploaded</span>');
                    }
                    
                    // Reset form to clear file input
                    form[0].reset();
                    
                    // Close modal
                    $('#companySettingsModal').modal('hide');
                    
                    // Show success message
                    showToast('success', 'Company settings updated successfully!');
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
        
        // Remove toast after it's hidden
        $('#' + toastId).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush

