@extends('layouts.admin')

@section('title', 'Service Highlights Management')

@push('styles')
<style>
    .highlight-section {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
    }
    .highlight-section h5 {
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
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
                        <li class="breadcrumb-item active" aria-current="page">Service Highlights</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Service Highlights Management</h1>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <div id="alertContainer"></div>
                
                <form id="serviceHighlightsForm" method="POST">
                    @csrf
                    
                    <!-- Highlight 1 -->
                    <div class="highlight-section">
                        <h5>Highlight 1</h5>
                        <div class="row g-3">
                            <div class="col-3">
                                <label for="highlight1Title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="highlight1Title" name="highlight1_title" value="{{ old('highlight1_title', $serviceHighlight->highlight1_title ?? '') }}" placeholder="Enter title">
                                <div class="invalid-feedback" id="highlight1TitleError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight1Text" class="form-label">Text</label>
                                <input type="text" class="form-control" id="highlight1Text" name="highlight1_text" value="{{ old('highlight1_text', $serviceHighlight->highlight1_text ?? '') }}" placeholder="Enter text">
                                <div class="invalid-feedback" id="highlight1TextError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight1Icon" class="form-label">Icon</label>
                                <select class="form-select" id="highlight1Icon" name="highlight1_icon">
                                    <option value="">Select Icon</option>
                                    <option value="fas fa-shopping-basket" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-shopping-basket' ? 'selected' : '' }}>Shopping Basket</option>
                                    <option value="far fa-credit-card" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'far fa-credit-card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="fas fa-shield-alt" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-shield-alt' ? 'selected' : '' }}>Shield</option>
                                    <option value="fas fa-headphones-alt" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-headphones-alt' ? 'selected' : '' }}>Headphones</option>
                                    <option value="fas fa-truck" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="fas fa-undo" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-undo' ? 'selected' : '' }}>Return</option>
                                    <option value="fas fa-gift" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="fas fa-tag" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-tag' ? 'selected' : '' }}>Tag</option>
                                    <option value="fas fa-star" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-star' ? 'selected' : '' }}>Star</option>
                                    <option value="fas fa-heart" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-heart' ? 'selected' : '' }}>Heart</option>
                                    <option value="fas fa-check-circle" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-check-circle' ? 'selected' : '' }}>Check Circle</option>
                                    <option value="fas fa-lock" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-lock' ? 'selected' : '' }}>Lock</option>
                                    <option value="fas fa-shipping-fast" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-shipping-fast' ? 'selected' : '' }}>Fast Shipping</option>
                                    <option value="fas fa-medal" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-medal' ? 'selected' : '' }}>Medal</option>
                                    <option value="fas fa-thumbs-up" {{ old('highlight1_icon', $serviceHighlight->highlight1_icon ?? '') == 'fas fa-thumbs-up' ? 'selected' : '' }}>Thumbs Up</option>
                                </select>
                                <div class="invalid-feedback" id="highlight1IconError"></div>
                            </div>
                            <div class="col-3">
                            <label for="highlight1Active" class="form-label">Status</label>
                                <div class="form-check form-switch"> 
                                    <input class="form-check-input" type="checkbox" role="switch" id="highlight1Active" name="highlight1_active" {{ old('highlight1_active', $serviceHighlight->highlight1_active ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="highlight1Active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight 2 -->
                    <div class="highlight-section">
                        <h5>Highlight 2</h5>
                        <div class="row g-3">
                            <div class="col-3">
                                <label for="highlight2Title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="highlight2Title" name="highlight2_title" value="{{ old('highlight2_title', $serviceHighlight->highlight2_title ?? '') }}" placeholder="Enter title">
                                <div class="invalid-feedback" id="highlight2TitleError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight2Text" class="form-label">Text</label>
                                <input type="text" class="form-control" id="highlight2Text" name="highlight2_text" value="{{ old('highlight2_text', $serviceHighlight->highlight2_text ?? '') }}" placeholder="Enter text">
                                <div class="invalid-feedback" id="highlight2TextError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight2Icon" class="form-label">Icon</label>
                                <select class="form-select" id="highlight2Icon" name="highlight2_icon">
                                    <option value="">Select Icon</option>
                                    <option value="fas fa-shopping-basket" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-shopping-basket' ? 'selected' : '' }}>Shopping Basket</option>
                                    <option value="far fa-credit-card" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'far fa-credit-card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="fas fa-shield-alt" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-shield-alt' ? 'selected' : '' }}>Shield</option>
                                    <option value="fas fa-headphones-alt" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-headphones-alt' ? 'selected' : '' }}>Headphones</option>
                                    <option value="fas fa-truck" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="fas fa-undo" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-undo' ? 'selected' : '' }}>Return</option>
                                    <option value="fas fa-gift" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="fas fa-tag" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-tag' ? 'selected' : '' }}>Tag</option>
                                    <option value="fas fa-star" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-star' ? 'selected' : '' }}>Star</option>
                                    <option value="fas fa-heart" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-heart' ? 'selected' : '' }}>Heart</option>
                                    <option value="fas fa-check-circle" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-check-circle' ? 'selected' : '' }}>Check Circle</option>
                                    <option value="fas fa-lock" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-lock' ? 'selected' : '' }}>Lock</option>
                                    <option value="fas fa-shipping-fast" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-shipping-fast' ? 'selected' : '' }}>Fast Shipping</option>
                                    <option value="fas fa-medal" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-medal' ? 'selected' : '' }}>Medal</option>
                                    <option value="fas fa-thumbs-up" {{ old('highlight2_icon', $serviceHighlight->highlight2_icon ?? '') == 'fas fa-thumbs-up' ? 'selected' : '' }}>Thumbs Up</option>
                                </select>
                                <div class="invalid-feedback" id="highlight2IconError"></div>
                            </div>
                            <div class="col-3">
                            <label for="highlight2Active" class="form-label">Status</label>
                                <div class="form-check form-switch"> 
                                    <input class="form-check-input" type="checkbox" role="switch" id="highlight2Active" name="highlight2_active" {{ old('highlight2_active', $serviceHighlight->highlight2_active ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="highlight2Active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight 3 -->
                    <div class="highlight-section">
                        <h5>Highlight 3</h5>
                        <div class="row g-3">
                            <div class="col-3">
                                <label for="highlight3Title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="highlight3Title" name="highlight3_title" value="{{ old('highlight3_title', $serviceHighlight->highlight3_title ?? '') }}" placeholder="Enter title">
                                <div class="invalid-feedback" id="highlight3TitleError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight3Text" class="form-label">Text</label>
                                <input type="text" class="form-control" id="highlight3Text" name="highlight3_text" value="{{ old('highlight3_text', $serviceHighlight->highlight3_text ?? '') }}" placeholder="Enter text">
                                <div class="invalid-feedback" id="highlight3TextError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight3Icon" class="form-label">Icon</label>
                                <select class="form-select" id="highlight3Icon" name="highlight3_icon">
                                    <option value="">Select Icon</option>
                                    <option value="fas fa-shopping-basket" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-shopping-basket' ? 'selected' : '' }}>Shopping Basket</option>
                                    <option value="far fa-credit-card" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'far fa-credit-card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="fas fa-shield-alt" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-shield-alt' ? 'selected' : '' }}>Shield</option>
                                    <option value="fas fa-headphones-alt" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-headphones-alt' ? 'selected' : '' }}>Headphones</option>
                                    <option value="fas fa-truck" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="fas fa-undo" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-undo' ? 'selected' : '' }}>Return</option>
                                    <option value="fas fa-gift" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="fas fa-tag" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-tag' ? 'selected' : '' }}>Tag</option>
                                    <option value="fas fa-star" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-star' ? 'selected' : '' }}>Star</option>
                                    <option value="fas fa-heart" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-heart' ? 'selected' : '' }}>Heart</option>
                                    <option value="fas fa-check-circle" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-check-circle' ? 'selected' : '' }}>Check Circle</option>
                                    <option value="fas fa-lock" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-lock' ? 'selected' : '' }}>Lock</option>
                                    <option value="fas fa-shipping-fast" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-shipping-fast' ? 'selected' : '' }}>Fast Shipping</option>
                                    <option value="fas fa-medal" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-medal' ? 'selected' : '' }}>Medal</option>
                                    <option value="fas fa-thumbs-up" {{ old('highlight3_icon', $serviceHighlight->highlight3_icon ?? '') == 'fas fa-thumbs-up' ? 'selected' : '' }}>Thumbs Up</option>
                                </select>
                                <div class="invalid-feedback" id="highlight3IconError"></div>
                            </div>
                            <div class="col-3">
                            <label for="highlight3Active" class="form-label">Status</label> 
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="highlight3Active" name="highlight3_active" {{ old('highlight3_active', $serviceHighlight->highlight3_active ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="highlight3Active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight 4 -->
                    <div class="highlight-section">
                        <h5>Highlight 4</h5>
                        <div class="row g-3">
                            <div class="col-3">
                                <label for="highlight4Title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="highlight4Title" name="highlight4_title" value="{{ old('highlight4_title', $serviceHighlight->highlight4_title ?? '') }}" placeholder="Enter title">
                                <div class="invalid-feedback" id="highlight4TitleError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight4Text" class="form-label">Text</label>
                                <input type="text" class="form-control" id="highlight4Text" name="highlight4_text" value="{{ old('highlight4_text', $serviceHighlight->highlight4_text ?? '') }}" placeholder="Enter text">
                                <div class="invalid-feedback" id="highlight4TextError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight4Icon" class="form-label">Icon</label>
                                <select class="form-select" id="highlight4Icon" name="highlight4_icon">
                                    <option value="">Select Icon</option>
                                    <option value="fas fa-shopping-basket" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-shopping-basket' ? 'selected' : '' }}>Shopping Basket</option>
                                    <option value="far fa-credit-card" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'far fa-credit-card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="fas fa-shield-alt" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-shield-alt' ? 'selected' : '' }}>Shield</option>
                                    <option value="fas fa-headphones-alt" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-headphones-alt' ? 'selected' : '' }}>Headphones</option>
                                    <option value="fas fa-truck" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="fas fa-undo" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-undo' ? 'selected' : '' }}>Return</option>
                                    <option value="fas fa-gift" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="fas fa-tag" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-tag' ? 'selected' : '' }}>Tag</option>
                                    <option value="fas fa-star" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-star' ? 'selected' : '' }}>Star</option>
                                    <option value="fas fa-heart" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-heart' ? 'selected' : '' }}>Heart</option>
                                    <option value="fas fa-check-circle" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-check-circle' ? 'selected' : '' }}>Check Circle</option>
                                    <option value="fas fa-lock" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-lock' ? 'selected' : '' }}>Lock</option>
                                    <option value="fas fa-shipping-fast" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-shipping-fast' ? 'selected' : '' }}>Fast Shipping</option>
                                    <option value="fas fa-medal" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-medal' ? 'selected' : '' }}>Medal</option>
                                    <option value="fas fa-thumbs-up" {{ old('highlight4_icon', $serviceHighlight->highlight4_icon ?? '') == 'fas fa-thumbs-up' ? 'selected' : '' }}>Thumbs Up</option>
                                </select>
                                <div class="invalid-feedback" id="highlight4IconError"></div>
                            </div>
                            <div class="col-3">
                                <label for="highlight4Active" class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="highlight4Active" name="highlight4_active" {{ old('highlight4_active', $serviceHighlight->highlight4_active ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="highlight4Active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="updateServiceHighlightsBtn">
                                <i class="fas fa-save"></i> Update Service Highlights
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
$(document).ready(function() {
    // Update Service Highlights
    $('#updateServiceHighlightsBtn').on('click', function() {
        const form = $('#serviceHighlightsForm')[0];
        const formData = new FormData(form);
        
        // Handle checkbox values properly
        formData.set('highlight1_active', $('#highlight1Active').is(':checked') ? '1' : '0');
        formData.set('highlight2_active', $('#highlight2Active').is(':checked') ? '1' : '0');
        formData.set('highlight3_active', $('#highlight3Active').is(':checked') ? '1' : '0');
        formData.set('highlight4_active', $('#highlight4Active').is(':checked') ? '1' : '0');
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '{{ route("service-highlights.update") }}',
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
            $(`#${key}Error`).text(value[0]).show();
            $(`#${key}`).addClass('is-invalid');
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

