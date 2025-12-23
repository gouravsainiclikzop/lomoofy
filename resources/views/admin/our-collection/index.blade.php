@extends('layouts.admin')

@section('title', 'Our Collection Management')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .image-preview {
        max-width: 300px;
        max-height: 200px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-top: 10px;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
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
                        <li class="breadcrumb-item active" aria-current="page">Our Collection</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Our Collection Management</h1>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <div id="alertContainer"></div>
                
                <form id="ourCollectionForm" enctype="multipart/form-data" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="backgroundImage" class="form-label">Background Image</label>
                            <input type="file" class="form-control" id="backgroundImage" name="background_image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 1920x1080px, Max size: 2MB</small>
                            <div id="backgroundImagePreview" class="mt-2" style="display: none;">
                                <img id="previewBackgroundImg" src="" alt="Preview" class="image-preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeBackgroundImageBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div id="currentBackgroundImage" class="mt-2" style="display: none;">
                                <img id="currentBackgroundImg" src="" alt="Current" class="image-preview">
                                <p class="text-muted small mt-1">Current background image</p>
                            </div>
                            <div class="invalid-feedback" id="backgroundImageError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="heading" class="form-label">Heading</label>
                            <input type="text" class="form-control" id="heading" name="heading" value="{{ old('heading', $ourCollection->heading ?? '') }}" placeholder="Enter heading">
                            <div class="invalid-feedback" id="headingError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Enter description">{{ old('description', $ourCollection->description ?? '') }}</textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="categorySelect" class="form-label">Category</label>
                            <select class="form-select" id="categorySelect" name="category_id" style="width: 100%;">
                                <option value="">Select Category (Optional)</option>
                            </select>
                            <div class="invalid-feedback" id="categoryError"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="updateOurCollectionBtn">
                                <i class="fas fa-save"></i> Update Our Collection
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let categorySelect2Initialized = false;
let pendingCategoryData = null;

// Function to initialize Select2 for category
function initializeCategorySelect2() {
    const select = $('#categorySelect');
    
    // Destroy existing Select2 if initialized
    if (select.hasClass('select2-hidden-accessible')) {
        select.select2('destroy');
    }
    
    // Initialize Select2
    select.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select Category (Optional)',
        allowClear: true,
        ajax: {
            url: '{{ route("categories.data") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    status: 1
                };
            },
            processResults: function (data) {
                if (data.success && data.data) {
                    return {
                        results: data.data.map(function(category) {
                            return {
                                id: category.id,
                                text: category.full_path_name || category.name
                            };
                        })
                    };
                }
                return { results: [] };
            },
            cache: true
        }
    });
    
    categorySelect2Initialized = true;
}

$(document).ready(function() {
    // Initialize Select2 on page load
    initializeCategorySelect2();
    
    // Set category if exists
    @if(isset($ourCollection) && $ourCollection->category_id)
        pendingCategoryData = {
            id: {{ $ourCollection->category_id }},
            name: '{{ $ourCollection->category ? addslashes($ourCollection->category->getFullPathName()) : "" }}'
        };
        
        setTimeout(function() {
            const categorySelect = $('#categorySelect');
            if (categorySelect.hasClass('select2-hidden-accessible') && pendingCategoryData && pendingCategoryData.id) {
                // Create option if it doesn't exist
                if (categorySelect.find('option[value="' + pendingCategoryData.id + '"]').length === 0) {
                    const option = new Option(pendingCategoryData.name, pendingCategoryData.id, true, true);
                    categorySelect.append(option).trigger('change');
                }
                // Set the value using Select2 API
                categorySelect.val(pendingCategoryData.id).trigger('change');
            }
        }, 300);
    @endif
    
    // Show current background image if exists
    @if(isset($ourCollection) && $ourCollection->background_image)
        $('#currentBackgroundImg').attr('src', '{{ asset("storage/" . $ourCollection->background_image) }}');
        $('#currentBackgroundImage').show();
    @endif

    // Background Image Preview
    $('#backgroundImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewBackgroundImg').attr('src', e.target.result);
                $('#backgroundImagePreview').show();
                $('#currentBackgroundImage').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Background Image
    $('#removeBackgroundImageBtn').on('click', function() {
        $('#backgroundImage').val('');
        $('#backgroundImagePreview').hide();
    });

    // Update Our Collection
    $('#updateOurCollectionBtn').on('click', function() {
        const form = $('#ourCollectionForm')[0];
        const formData = new FormData(form);
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '{{ route("our-collection.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    
                    // Update current image if new image was uploaded
                    if (response.data.background_image) {
                        $('#currentBackgroundImg').attr('src', response.data.background_image);
                        $('#currentBackgroundImage').show();
                        $('#backgroundImagePreview').hide();
                        $('#backgroundImage').val('');
                    }
                    
                    // Update category if changed
                    if (response.data.category_id && pendingCategoryData) {
                        pendingCategoryData.id = response.data.category_id;
                        pendingCategoryData.name = response.data.category_full_path_name || response.data.category_name;
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                    showAlert('danger', 'Validation failed. Please check the form.');
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Display Errors
    function displayErrors(errors) {
        $('.invalid-feedback').text('').hide();
        $('.form-control, .form-select').removeClass('is-invalid');
        
        $.each(errors, function(key, value) {
            const field = key.replace('_', '');
            $(`#${field}Error`).text(value[0]).show();
            $(`#${field.charAt(0).toUpperCase() + field.slice(1)}`).addClass('is-invalid');
        });
    }

    // Show Alert
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush

