@extends('layouts.admin')

@section('title', 'About Us Management')

@push('styles')
<!-- RichTextEditor -->
<link rel="stylesheet" href="{{ asset('frontend/js/richtexteditor/rte_theme_default.css') }}" />
<style>
    .image-preview {
        max-width: 400px;
        max-height: 300px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-top: 10px;
    }
    .rte-container {
        border: 1px solid #dee2e6;
        border-radius: 4px;
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
                        <li class="breadcrumb-item active" aria-current="page">About Us</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">About Us Management</h1>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <div id="alertContainer"></div>
                
                <form id="aboutUsForm" enctype="multipart/form-data" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 1200x800px, Max size: 2MB</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="image-preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div id="currentImage" class="mt-2" style="display: none;">
                                <img id="currentImg" src="" alt="Current" class="image-preview">
                                <p class="text-muted small mt-1">Current image</p>
                            </div>
                            <div class="invalid-feedback" id="imageError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <div id="descriptionEditor"></div>
                            <textarea id="description" name="description" style="display: none;">{{ old('description', $aboutUs->description ?? '') }}</textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="updateAboutUsBtn">
                                <i class="fas fa-save"></i> Update About Us
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
<!-- RichTextEditor -->
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/lang/rte-lang-en.js') }}"></script>

<script>
let editor;

$(document).ready(function() {
    // Initialize Rich Text Editor with minimal configuration
    try {
        editor = new RichTextEditor("#descriptionEditor");
        
        // Set initial value if exists
        const initialContent = $('#description').val();
        if (initialContent && editor && typeof editor.setHTMLCode === 'function') {
            editor.setHTMLCode(initialContent);
        }
    } catch(e) {
        console.error("RTE Error:", e);
        // Fallback to textarea if RTE fails
        $('#descriptionEditor').hide();
        $('#description').show().css('height', '400px');
    }
    
    // Show current image if exists
    @if(isset($aboutUs) && $aboutUs->image)
        $('#currentImg').attr('src', '{{ asset("storage/" . $aboutUs->image) }}');
        $('#currentImage').show();
    @endif

    // Image Preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
                $('#currentImage').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Image
    $('#removeImageBtn').on('click', function() {
        $('#image').val('');
        $('#imagePreview').hide();
        if ($('#currentImg').attr('src')) {
            $('#currentImage').show();
        }
    });

    // Update About Us
    $('#updateAboutUsBtn').on('click', function() {
        // Get content from rich text editor (if initialized)
        if (editor && typeof editor.getHTMLCode === 'function') {
            const editorContent = editor.getHTMLCode();
            $('#description').val(editorContent);
        }
        
        const form = $('#aboutUsForm')[0];
        const formData = new FormData(form);
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '{{ route("about-us.update") }}',
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
                    if (response.data.image) {
                        $('#currentImg').attr('src', response.data.image);
                        $('#currentImage').show();
                        $('#imagePreview').hide();
                        $('#image').val('');
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
        $('.form-control').removeClass('is-invalid');
        
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
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 'fast');
        
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
