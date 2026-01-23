@extends('layouts.admin')

@section('title', 'Legal Pages Management')

@push('styles')
<!-- RichTextEditor -->
<link rel="stylesheet" href="{{ asset('frontend/js/richtexteditor/rte_theme_default.css') }}" />
<style>
    .rte-container {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    .legal-section {
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        background: #f8f9fa;
    }
    .legal-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #dee2e6;
    }
    .legal-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .form-switch .form-check-label {
        cursor: pointer;
        margin-left: 0.5rem;
        font-weight: 500;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
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
                        <li class="breadcrumb-item active" aria-current="page">Legal Pages</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Legal Pages Management</h1>
                <p class="text-muted">Manage all legal and policy pages for your website</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-4">
                <div id="alertContainer"></div>
                
                <form id="legalPagesForm" method="POST">
                    @csrf
                    
                    <!-- Terms & Conditions -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-file-contract me-2"></i>Terms & Conditions
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="termsConditionsStatus" 
                                       name="terms_conditions_status" value="1"
                                       {{ old('terms_conditions_status', $legalPages->terms_conditions_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="termsConditionsStatus">
                                    <span class="status-badge status-active" id="termsConditionsStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="termsConditionsEditor"></div>
                        <textarea id="termsConditions" name="terms_conditions" style="display: none;">{{ old('terms_conditions', $legalPages->terms_conditions ?? '') }}</textarea>
                        <div class="invalid-feedback" id="termsConditionsError"></div>
                    </div>

                    <!-- Shipping -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-shipping-fast me-2"></i>Shipping
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="shippingStatus" 
                                       name="shipping_status" value="1"
                                       {{ old('shipping_status', $legalPages->shipping_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="shippingStatus">
                                    <span class="status-badge status-active" id="shippingStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="shippingEditor"></div>
                        <textarea id="shipping" name="shipping" style="display: none;">{{ old('shipping', $legalPages->shipping ?? '') }}</textarea>
                        <div class="invalid-feedback" id="shippingError"></div>
                    </div>

                    <!-- Cancellation & Refund -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-undo me-2"></i>Cancellation & Refund
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="cancellationRefundStatus" 
                                       name="cancellation_refund_status" value="1"
                                       {{ old('cancellation_refund_status', $legalPages->cancellation_refund_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cancellationRefundStatus">
                                    <span class="status-badge status-active" id="cancellationRefundStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="cancellationRefundEditor"></div>
                        <textarea id="cancellationRefund" name="cancellation_refund" style="display: none;">{{ old('cancellation_refund', $legalPages->cancellation_refund ?? '') }}</textarea>
                        <div class="invalid-feedback" id="cancellationRefundError"></div>
                    </div>

                    <!-- Return & Refund Policy -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-exchange-alt me-2"></i>Return & Refund Policy
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="returnRefundPolicyStatus" 
                                       name="return_refund_policy_status" value="1"
                                       {{ old('return_refund_policy_status', $legalPages->return_refund_policy_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="returnRefundPolicyStatus">
                                    <span class="status-badge status-active" id="returnRefundPolicyStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="returnRefundPolicyEditor"></div>
                        <textarea id="returnRefundPolicy" name="return_refund_policy" style="display: none;">{{ old('return_refund_policy', $legalPages->return_refund_policy ?? '') }}</textarea>
                        <div class="invalid-feedback" id="returnRefundPolicyError"></div>
                    </div>

                    <!-- Privacy Policy -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="privacyPolicyStatus" 
                                       name="privacy_policy_status" value="1"
                                       {{ old('privacy_policy_status', $legalPages->privacy_policy_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="privacyPolicyStatus">
                                    <span class="status-badge status-active" id="privacyPolicyStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="privacyPolicyEditor"></div>
                        <textarea id="privacyPolicy" name="privacy_policy" style="display: none;">{{ old('privacy_policy', $legalPages->privacy_policy ?? '') }}</textarea>
                        <div class="invalid-feedback" id="privacyPolicyError"></div>
                    </div>

                    <!-- Disclaimer -->
                    <div class="legal-section">
                        <div class="legal-section-header">
                            <h5 class="legal-section-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Disclaimer
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="disclaimerStatus" 
                                       name="disclaimer_status" value="1"
                                       {{ old('disclaimer_status', $legalPages->disclaimer_status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="disclaimerStatus">
                                    <span class="status-badge status-active" id="disclaimerStatusLabel">Active</span>
                                </label>
                            </div>
                        </div>
                        <div id="disclaimerEditor"></div>
                        <textarea id="disclaimer" name="disclaimer" style="display: none;">{{ old('disclaimer', $legalPages->disclaimer ?? '') }}</textarea>
                        <div class="invalid-feedback" id="disclaimerError"></div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-lg" id="updateLegalPagesBtn">
                                <i class="fas fa-save"></i> Update Legal Pages
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
let editors = {};

$(document).ready(function() {
    // Initialize all Rich Text Editors
    const editorFields = [
        { name: 'termsConditions', label: 'Terms & Conditions' },
        { name: 'shipping', label: 'Shipping' },
        { name: 'cancellationRefund', label: 'Cancellation & Refund' },
        { name: 'returnRefundPolicy', label: 'Return & Refund Policy' },
        { name: 'privacyPolicy', label: 'Privacy Policy' },
        { name: 'disclaimer', label: 'Disclaimer' }
    ];
    
    editorFields.forEach(function(field) {
        const editorId = '#' + field.name + 'Editor';
        const textareaId = '#' + field.name;
        
        try {
            // Initialize with minimal configuration
            editors[field.name] = new RichTextEditor(editorId);
            
            // Set initial value if exists
            const initialContent = $(textareaId).val();
            if (initialContent && editors[field.name] && typeof editors[field.name].setHTMLCode === 'function') {
                editors[field.name].setHTMLCode(initialContent);
            }
        } catch(e) {
            console.error("RTE Error for " + field.name + ":", e);
            // Fallback to textarea if RTE fails
            $(editorId).hide();
            $(textareaId).show().css('height', '300px');
        }
    });
    
    // Toggle status badge labels
    function updateStatusLabel(checkboxId, labelId) {
        const checkbox = $('#' + checkboxId);
        const label = $('#' + labelId);
        
        if (checkbox.is(':checked')) {
            label.removeClass('status-inactive').addClass('status-active').text('Active');
        } else {
            label.removeClass('status-active').addClass('status-inactive').text('Inactive');
        }
    }
    
    // Initialize all status labels
    const statusFields = [
        { checkbox: 'termsConditionsStatus', label: 'termsConditionsStatusLabel' },
        { checkbox: 'shippingStatus', label: 'shippingStatusLabel' },
        { checkbox: 'cancellationRefundStatus', label: 'cancellationRefundStatusLabel' },
        { checkbox: 'returnRefundPolicyStatus', label: 'returnRefundPolicyStatusLabel' },
        { checkbox: 'privacyPolicyStatus', label: 'privacyPolicyStatusLabel' },
        { checkbox: 'disclaimerStatus', label: 'disclaimerStatusLabel' }
    ];
    
    statusFields.forEach(function(field) {
        updateStatusLabel(field.checkbox, field.label);
        
        $('#' + field.checkbox).on('change', function() {
            updateStatusLabel(field.checkbox, field.label);
        });
    });

    // Update Legal Pages
    $('#updateLegalPagesBtn').on('click', function() {
        // Get content from all rich text editors
        editorFields.forEach(function(field) {
            // Safely get content from editor
            if (editors[field.name] && typeof editors[field.name].getHTMLCode === 'function') {
                const editorContent = editors[field.name].getHTMLCode();
                $('#' + field.name).val(editorContent);
            }
        });
        
        const form = $('#legalPagesForm')[0];
        const formData = new FormData(form);
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '{{ route("legal-pages.update") }}',
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
        
        $.each(errors, function(key, value) {
            const errorId = '#' + key.replace(/_/g, '') + 'Error';
            $(errorId).text(value[0]).show();
        });
    }

    // Show Alert
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
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
