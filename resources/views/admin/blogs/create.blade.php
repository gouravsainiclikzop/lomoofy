@extends('layouts.admin')

@section('title', 'Create Blog')

@push('styles')
<!-- RichTextEditor -->
<link rel="stylesheet" href="{{ asset('frontend/js/richtexteditor/rte_theme_default.css') }}" />
<style>
    .image-preview {
        max-width: 300px;
        max-height: 200px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-top: 10px;
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
                        <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Blog</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Create New Blog</h1>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <div id="alertContainer"></div>
                
                <form id="blogForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Title -->
                        <div class="col-12">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback" id="titleError"></div>
                        </div>

                        <!-- Slug -->
                        <div class="col-12">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug">
                            <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                            <div class="invalid-feedback" id="slugError"></div>
                        </div>

                        <!-- Thumbnail Image -->
                        <div class="col-md-6">
                            <label for="thumbnailImage" class="form-label">Thumbnail Image</label>
                            <input type="file" class="form-control" id="thumbnailImage" name="thumbnail_image" accept="image/*">
                            <small class="form-text text-muted">Recommended: 400x300px, Max: 2MB</small>
                            <div id="thumbnailPreview" style="display: none;">
                                <img id="previewThumbnail" src="" alt="Preview" class="image-preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeThumbnailBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div class="invalid-feedback" id="thumbnailImageError"></div>
                        </div>

                        <!-- Featured Image -->
                        <div class="col-md-6">
                            <label for="featuredImage" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="featuredImage" name="featured_image" accept="image/*">
                            <small class="form-text text-muted">Recommended: 1200x800px, Max: 2MB</small>
                            <div id="featuredPreview" style="display: none;">
                                <img id="previewFeatured" src="" alt="Preview" class="image-preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeFeaturedBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div class="invalid-feedback" id="featuredImageError"></div>
                        </div>

                        <!-- Added By -->
                        <div class="col-md-6">
                            <label for="addedBy" class="form-label">Added By</label>
                            <input type="text" class="form-control" id="addedBy" name="added_by" placeholder="e.g., Admin">
                            <div class="invalid-feedback" id="addedByError"></div>
                        </div>

                        <!-- Published Date -->
                        <div class="col-md-6">
                            <label for="publishedDate" class="form-label">Published Date</label>
                            <input type="date" class="form-control" id="publishedDate" name="published_date" value="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback" id="publishedDateError"></div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <div id="descriptionEditor"></div>
                            <textarea id="description" name="description" style="display: none;"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Create Blog
                            </button>
                            <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
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
    // Initialize Rich Text Editor with simple config
    var config = {};
    config.toolbar = "basic";
    config.height = "400px";
    
    try {
        editor = new RichTextEditor("#descriptionEditor", config);
    } catch(e) {
        console.error("RTE Error:", e);
        // Fallback to textarea if RTE fails
        $('#descriptionEditor').hide();
        $('#description').show().css('height', '400px');
    }

    // Auto-generate slug from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const slug = title.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        $('#slug').val(slug);
    });

    // Thumbnail Preview
    $('#thumbnailImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewThumbnail').attr('src', e.target.result);
                $('#thumbnailPreview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#removeThumbnailBtn').on('click', function() {
        $('#thumbnailImage').val('');
        $('#thumbnailPreview').hide();
    });

    // Featured Image Preview
    $('#featuredImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewFeatured').attr('src', e.target.result);
                $('#featuredPreview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#removeFeaturedBtn').on('click', function() {
        $('#featuredImage').val('');
        $('#featuredPreview').hide();
    });

    // Form Submit
    $('#blogForm').on('submit', function(e) {
        e.preventDefault();

        // Get content from editor (if initialized)
        if (editor && typeof editor.getHTMLCode === 'function') {
            const editorContent = editor.getHTMLCode();
            $('#description').val(editorContent);
        }

        const formData = new FormData(this);
        
        const $btn = $('#submitBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

        $.ajax({
            url: '{{ route("blogs.store") }}',
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
                    setTimeout(function() {
                        window.location.href = '{{ route("blogs.index") }}';
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayErrors(xhr.responseJSON.errors);
                    showAlert('danger', 'Validation failed. Please check the form.');
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    function displayErrors(errors) {
        $('.invalid-feedback').text('').hide();
        $('.form-control').removeClass('is-invalid');
        
        $.each(errors, function(key, value) {
            const errorId = '#' + key.replace(/_/g, '') + 'Error';
            $(errorId).text(value[0]).show();
            $('[name="' + key + '"]').addClass('is-invalid');
        });
    }

    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    }
});
</script>
@endpush
