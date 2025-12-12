{{-- Dynamic Product Form Container --}}
<div class="container-fluid compact-form">
    <div class="py-3">
        @php
            $isEditMode = isset($product) && $product->exists;
            $isQuick = isset($isQuickCreate) && $isQuickCreate;
            $formTitle = $isEditMode
                ? 'Edit Product'
                : ($isQuick ? 'Quick Create Product' : 'Create Product');
        @endphp
        <div class="row g-3 align-items-center mb-3">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">{{ $formTitle }}</li>
                    </ol>
                </nav>
                <h1 class="h4 m-0">{{ $formTitle }}</h1>
            </div>
             
            <div class="col-auto">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Scroll down to see all sections
                </small>
            </div> 
        </div>

        <form id="productForm" enctype="multipart/form-data" 
              action="{{ isset($product) && $product->exists ? route('products.update', $product) : route('products.store') }}"
              method="POST"
              data-is-edit="{{ isset($product) && $product->exists ? 'true' : 'false' }}"
              data-store-url="{{ route('products.store') }}"
              data-update-url="{{ isset($product) && $product->exists ? route('products.update', $product) : '' }}"
              data-existing-product-type="{{ isset($product) && $product->exists ? $product->type : '' }}">
            @csrf
            <input type="hidden" id="productType" name="type" value="{{ old('type', isset($product) && $product->type ? $product->type : 'variable') }}">
            <input type="hidden" id="productStatus" name="status" value="{{ isset($product) ? $product->status : 'hidden' }}">

            <!-- Full Width Layout -->
            <div class="row g-4">
                <!-- Main Content (col-12) -->
                <div class="col-12">

            {{-- Sticky Navigation Bar --}}
            <nav class="sticky-nav-bar" id="productFormNav">
                <div class="container-fluid">
                    <div class="nav-scroll-container">
                        <a href="#section-basic-info" class="nav-link active" data-section="basic-info">
                            <i class='bx bx-info-circle me-1'></i>
                            <span>Basic Information</span>
                        </a>
                        <a href="#section-categories" class="nav-link" data-section="categories">
                            <i class='bx bx-purchase-tag me-1'></i>
                            <span>Categories & Tags</span>
                        </a>
                        <a href="#section-variants" class="nav-link" id="nav-variants" data-section="variants">
                            <i class='bx bx-layer me-1'></i>
                            <span>Product Variants</span>
                        </a>
                        <a href="#section-images" class="nav-link" data-section="images">
                            <i class='bx bx-image me-1'></i>
                            <span>Product Images</span>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Single-Page Scrollable Sections --}}
            <div id="productFormSections" class="product-form-sections">
                {{-- Section 1: Basic Information --}}
                <section id="section-basic-info" class="form-section" data-section="basic-info">
                    <div class="section-content">
                        @include('admin.products.partials.basic-info')
                        @include('admin.products.partials.shipping') 
                    </div>
                </section>

                {{-- Section 2: Categories & Tags --}}
                <section id="section-categories" class="form-section" data-section="categories">
                    <div class="section-content">
                        @include('admin.products.partials.categories')
                    </div>
                </section>

                {{-- Section 3: Product Variants -- Always Visible --}}
                <section id="section-variants" class="form-section" data-section="variants">
                    <div class="section-content">
                        <div id="variantsContent">
                            @include('admin.products.partials.variants')
                        </div>
                    </div>
                </section>

                {{-- Section 4: Product Images --}}
                <section id="section-images" class="form-section" data-section="images">
                    <div class="section-content">
                        @include('admin.products.partials.images')
                        <div id="variantImagesNote" class="alert alert-info mt-3" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Variant Product:</strong> You can assign specific images to individual variants in the Variants section above.
                        </div>
                    </div>
                </section>
            </div>
                 
            <div class="card mt-4 sticky-bottom">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2"> 
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="saveAsHidden(this)">
                            <i class='fas fa-eye-slash'></i> Save as Hidden
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="publishProduct(this)">
                            <i class='fas fa-check'></i> Publish
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Changes are saved when you click Publish or Save as Hidden
                    </small>
                </div>
            </div> 

                
            </div>
                </div>
            </div>
        </div>
        </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<style>
.product-type-option {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
}

.product-type-option:hover {
    border-color: #f5c000;
    background-color: #f8f9fa;
}

.product-type-option .form-check-input:checked + .form-check-label {
    color: #f5c000;
}

.product-type-option .form-check-input:checked ~ * {
    border-color: #f5c000;
    background-color: #e7f3ff;
}

.product-type-icon {
    color: #6c757d;
    transition: color 0.3s ease;
}

.product-type-option:hover .product-type-icon,
.product-type-option .form-check-input:checked + .form-check-label .product-type-icon {
    color: #f5c000;
}

/* Compact version styles */
.product-type-compact {
    padding: 0.5rem;
    min-height: auto;
}

.product-type-compact .form-check-label {
    margin-bottom: 0;
}

.product-type-compact .d-flex {
    align-items: center;
}

.product-type-compact i {
    font-size: 1.1rem;
    color: #6c757d;
    transition: color 0.3s ease;
    min-width: 18px;
}

.product-type-compact:hover i,
.product-type-compact .form-check-input:checked + .form-check-label i {
    color: #f5c000;
}

.product-type-compact strong {
    font-size: 0.85rem;
    margin-bottom: 0;
    line-height: 1.2;
}

.product-type-compact small {
    font-size: 0.7rem;
    line-height: 1.1;
}

/* Compact form styles */
.compact-form .card-body {
    padding: 1rem;
}

.compact-form .form-label {
    font-size: 0.9rem;
    margin-bottom: 0.4rem;
    font-weight: 500;
}

.compact-form .form-control,
.compact-form .form-select {
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    min-height: auto;
}

.compact-form .form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.compact-form .row {
    margin-bottom: 0.75rem;
}

.compact-form .row:last-child {
    margin-bottom: 0;
}

.compact-form .col-12 {
    margin-bottom: 0.5rem;
}

.compact-form .col-12:last-child {
    margin-bottom: 0;
}

.compact-form .mb-3 {
    margin-bottom: 0.75rem !important;
}

.compact-form .mb-4 {
    margin-bottom: 1rem !important;
}

.compact-form .card {
    margin-bottom: 1rem;
}

.compact-form .card-header {
    padding: 0.75rem 1rem;
}

.compact-form .card-title {
    font-size: 1rem;
    margin-bottom: 0;
}

.compact-form .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.compact-form .small {
    font-size: 0.75rem;
}

.compact-form .text-muted {
    font-size: 0.75rem;
}

.section-transition {
    transition: all 0.3s ease;
}

.section-hidden {
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    margin: 0;
    padding: 0;
}

.section-visible {
    opacity: 1;
    max-height: none;
}

/* custom style */
.product-type-compact .form-check-label { 
	width: 100% !important;
}

/* Sticky Navigation Bar */
.sticky-nav-bar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    margin: 0 -15px 1rem -15px;
    padding: 0 15px;
}

.nav-scroll-container {
    display: flex;
    gap: 0.25rem;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    padding: 0.5rem 0;
}

.nav-scroll-container::-webkit-scrollbar {
    height: 3px;
}

.nav-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.nav-scroll-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.sticky-nav-bar .nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    color: #6c757d;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.875rem;
    white-space: nowrap;
    flex-shrink: 0;
    position: relative;
    border-radius: 4px 4px 0 0;
}

.sticky-nav-bar .nav-link:hover {
    color: #f5c000;
    border-bottom-color: #dee2e6;
    background-color: rgba(255, 211, 51, 0.05);
}

.sticky-nav-bar .nav-link.active {
    color: #f5c000;
    border-bottom-color: #f5c000;
    font-weight: 600;
    background-color: rgba(255, 211, 51, 0.08);
}

.sticky-nav-bar .nav-link i,
.sticky-nav-bar .nav-link .bx {
    font-size: 0.95rem;
}

/* Single-Page Sections */
.product-form-sections {
    position: relative;
}

.form-section {
    margin-bottom: 1rem;
    padding-bottom: 0rem !important; 
    /* border-bottom: 1px solid #e9ecef; */
    scroll-margin-top: 70px; /* Offset for sticky nav */
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.section-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 0;
}

.section-content {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Sticky Bottom Buttons */
.sticky-bottom {
    position: sticky;
    bottom: 0;
    background-color: #fff;
    z-index: 10;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 2rem !important;
}

/* Mobile Responsive Adjustments */
@media (max-width: 767.98px) {
    .sticky-nav-bar {
        margin-left: -15px;
        margin-right: -15px;
        padding: 0 15px;
    }
    
    .nav-scroll-container {
        padding: 0.375rem 0;
    }
    
    .sticky-nav-bar .nav-link {
        padding: 0.375rem 0.625rem;
        font-size: 0.8rem;
    }
    
    .sticky-nav-bar .nav-link span {
        display: none;
    }
    
    .sticky-nav-bar .nav-link i,
    .sticky-nav-bar .nav-link .bx {
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
    
    .section-description {
        font-size: 0.875rem;
    }
    
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
    }
    
    .sticky-bottom {
        margin-left: -1rem;
        margin-right: -1rem;
        border-radius: 0;
    }
    
    .sticky-bottom .card-body {
        padding: 0.75rem 1rem;
    }
    
    .sticky-bottom .btn {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
}

/* Section spacing */
.form-section .card {
    margin-bottom: 1rem;
}

.form-section .card:last-child {
    margin-bottom: 0;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Section visibility transitions */
.form-section {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.form-section[style*="display: none"] {
    display: none !important;
}
</style>

@push('scripts')
<script>
const FORM_STORAGE_KEY = 'productFormDraft';
const VARIANT_STORAGE_KEY = 'productVariantDraft';
let productForm = null;
let productTypeHidden = null;
let isEditMode = false;

// Product Configuration Management
$(document).ready(function() {
    productForm = $('#productForm')[0];
    productTypeHidden = $('#productType')[0];
    isEditMode = productForm ? $(productForm).data('is-edit') === 'true' : false;

    if (!isEditMode && productForm) {
        restoreFormDraft();
    }

    if (productForm && productTypeHidden) {
        const existingProductType = $(productForm).data('existing-product-type');
        if (existingProductType) {
            $(productTypeHidden).val(existingProductType);
        }
    }
    
    const $variantsContent = $('#variantsContent');
    if ($variantsContent.length) {
        $variantsContent.show();
    }
    
    // Single-Page Navigation
    initializeSinglePageNavigation();
    
    // Variants section is always visible - no SKU type handling needed
    
    // Auto-generate SKU from name
    $('#productName').on('input', function() {
        const name = $(this).val();
        const sku = name.toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 20);
        $('#productSku').val(sku.toUpperCase());
    });
    
    // Listen for changes in product specifications to auto-update variants
    const specFields = ['productColor', 'productSize', 'productMaterial'];
    specFields.forEach(fieldId => {
        const $field = $('#' + fieldId);
        if ($field.length) {
            $field.on('input', function() {
                // If variants content is visible, update the attribute values
                const $variantsContent = $('#variantsContent');
                if ($variantsContent.length && $variantsContent.is(':visible')) {
                    const attributeType = fieldId.replace('product', '').toLowerCase();
                    const value = $(this).val().trim();
                    
                    if (value) {
                        // Check if the attribute is already selected
                        const $attributeItem = $('[data-attribute-type="' + attributeType + '"]');
                        if ($attributeItem.length) {
                            const $checkbox = $attributeItem.find('input[type="checkbox"]');
                            if ($checkbox.length && !$checkbox.is(':checked')) {
                                $checkbox.prop('checked', true);
                                $checkbox.trigger('change');
                            }
                            
                            // Update the attribute values
                            setTimeout(() => {
                                populateAttributeValues(attributeType, value);
                            }, 100);
                        }
                    }
                }
            });
        }
    });
    
    // Initialize form validation
    updateFormValidation();
    
    // Variants section is always visible - ensure it's shown
    $('#section-variants').show();
    $('#nav-variants').css('display', 'flex');
    
    // Update variant images section visibility based on whether variants exist
    function updateVariantImagesVisibility() {
        const $variantImagesNote = $('#variantImagesNote');
        const $variantImagesSection = $('#variantImagesSection');
        const $variantsTableBody = $('#variantsTableBody');
        
        if ($variantsTableBody.length) {
            const hasVariants = $variantsTableBody.find('tr').length > 0;
            
            if ($variantImagesNote.length) {
                $variantImagesNote.toggle(hasVariants);
            }
            if ($variantImagesSection.length) {
                $variantImagesSection.toggle(hasVariants);
                // Populate variant images container when section is shown
                if (hasVariants && window.populateVariantImagesContainer) {
                    setTimeout(function() {
                        window.populateVariantImagesContainer();
                    }, 100);
                }
            }
        } else {
            if ($variantImagesNote.length) {
                $variantImagesNote.hide();
            }
            if ($variantImagesSection.length) {
                $variantImagesSection.hide();
            }
        }
    }
    
    // Make updateVariantImagesVisibility available globally
    window.updateVariantImagesVisibility = updateVariantImagesVisibility;
    
    // Auto-select variant attributes when applicable
});

// Auto-select variant attributes based on product specifications
function autoSelectVariantAttributes() {
    // Get values from product specifications
    const colorValue = $('#productColor').val()?.trim();
    const sizeValue = $('#productSize').val()?.trim();
    const materialValue = $('#productMaterial').val()?.trim();
    
    // Map of specification fields to attribute types
    const specToAttributeMap = {
        'color': colorValue,
        'size': sizeValue,
        'material': materialValue
    };
    
    // Auto-select attributes that have values in specifications
    Object.entries(specToAttributeMap).forEach(([attributeType, value]) => {
        if (value) {
            // Find the attribute checkbox by type
            const $attributeItem = $('[data-attribute-type="' + attributeType + '"]');
            if ($attributeItem.length) {
                const $checkbox = $attributeItem.find('input[type="checkbox"]');
                if ($checkbox.length && !$checkbox.is(':checked')) {
                    // Check the attribute
                    $checkbox.prop('checked', true);
                    
                    // Trigger the change event to move it to selected attributes
                    $checkbox.trigger('change');
                    
                    // If there are predefined values, auto-populate them
                    setTimeout(() => {
                        populateAttributeValues(attributeType, value);
                    }, 100);
                }
            }
        }
    });
}

// Populate attribute values based on specification
function populateAttributeValues(attributeType, specificationValue) {
    // Find the attribute values container for this attribute type
    const $valuesContainer = $('[data-attribute-type="' + attributeType + '"] .attribute-values-container');
    if ($valuesContainer.length) {
        // Split the specification value by common separators
        const values = specificationValue.split(/[,;|]/).map(v => v.trim()).filter(v => v);
        
        if (values.length > 0) {
            // Clear existing values
            $valuesContainer.empty();
            
            // Add each value as a chip
            values.forEach((value, index) => {
                const $chip = $('<div>', {
                    class: 'badge bg-primary me-2 mb-2'
                }).html(`
                    ${value}
                    <input type="hidden" name="attribute_values[${attributeType}][]" value="${value}">
                    <button type="button" class="btn-close btn-close-white ms-1" onclick="removeAttributeValue(this)"></button>
                `);
                $valuesContainer.append($chip);
            });
        }
    }
}

// Helper function to remove attribute value chips
function removeAttributeValue(button) {
    $(button).closest('.badge').remove();
}


// Update form validation based on current toggle states and SKU type
function updateFormValidation() {
    // Remove required attributes from hidden fields
    $('#productForm input, #productForm select, #productForm textarea').each(function() {
        const $input = $(this);
        if ($input.closest('[style*="display: none"]').length || $input.closest('.section-hidden').length) {
            $input.removeAttr('required');
        }
    });

    // SKU and price are now handled at variant level, not product level
    // No product-level SKU/price validation needed

    // Add required attributes based on current product type
    const currentType = $('#productType').val();
    
    switch(currentType) {
        case 'digital':
            // Download files are required for digital products
            const $downloadFiles = $('#downloadFiles');
            if ($downloadFiles.length) {
                $downloadFiles.attr('required', 'required');
            }
            break;
        case 'subscription':
            // Subscription period is required for subscription products
            const $subscriptionPeriod = $('#subscriptionPeriod');
            if ($subscriptionPeriod.length) {
                $subscriptionPeriod.attr('required', 'required');
            }
            break;
    }
}

// Form submission functions
function saveAsHidden(button) {
    $('#productStatus').val('hidden');
    submitForm(button);
}

function publishProduct(button) {
    $('#productStatus').val('published');
    submitForm(button);
}

function submitForm(button) {
    const $form = $('#productForm');
    const formData = new FormData($form[0]);
    
    // Clean up duplicate fields - handle array fields properly
    const cleanedFormData = new FormData();
    const fieldValues = new Map();
    
    // First pass: collect all values for each field
    for (let [key, value] of formData.entries()) {
        if (!fieldValues.has(key)) {
            fieldValues.set(key, []);
        }
        fieldValues.get(key).push(value);
    }
    
    // Second pass: add values to cleaned FormData
    for (let [key, values] of fieldValues.entries()) {
        if (key.includes('[]')) {
            // For array fields, add all non-empty values
            const nonEmptyValues = values.filter(value => value && value.toString().trim() !== '');
            nonEmptyValues.forEach(value => {
                cleanedFormData.append(key, value);
            });
        } else {
            // For regular fields, keep only the first non-empty value
            const firstNonEmpty = values.find(value => value && value.toString().trim() !== '');
            if (firstNonEmpty) {
                cleanedFormData.set(key, firstNonEmpty);
            }
        }
    }
    
    // Ensure type has a fallback before validation and respect UI state
    let currentTypeValue = cleanedFormData.get('type');
    const $typeInput = $('#productType');
    const $pricingFieldsContainer = $('#pricingFieldsContainer');
    const pricingVisible = $pricingFieldsContainer.length ? $pricingFieldsContainer.is(':visible') : true;

    const hasVariantPayload = Array.from(cleanedFormData.keys()).some(key => key.startsWith('variants['));

    if (!currentTypeValue || (typeof currentTypeValue === 'string' && currentTypeValue.trim() === '')) {
        currentTypeValue = $typeInput.length ? ($typeInput.val() || 'simple') : 'simple';
    }

    if (hasVariantPayload && (!$pricingFieldsContainer.length || !pricingVisible)) {
        currentTypeValue = 'variable';
    }

    cleanedFormData.set('type', currentTypeValue);

    // SKU and price are now handled at variant level, not product level
    const requiredFields = ['name', 'status', 'brand_ids[]'];

    if (cleanedFormData.has('primary_category')) {
        requiredFields.push('primary_category');
    }
    
    const missingFields = [];
    
    requiredFields.forEach(field => {
        if (field.includes('[]')) {
            // For array fields, check if any values exist
            const hasValues = Array.from(cleanedFormData.entries()).some(([key, value]) => 
                key === field && value && value.toString().trim() !== ''
            );
            if (!hasValues) {
                missingFields.push(field);
            }
        } else {
            // For regular fields, check single value
            const value = cleanedFormData.get(field);
            if (!value || (typeof value === 'string' && value.trim() === '')) {
                missingFields.push(field);
            }
        }
    });
    
    if (missingFields.length > 0) {
        handleMissingFields(missingFields);
        return;
    }
    
    // Show loading state
    const $submitBtn = $(button);
    const originalText = $submitBtn.html();
    $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    $submitBtn.prop('disabled', true);
    
    // Determine the correct URL based on whether we're editing or creating
    const $productFormElement = $('#productForm');
    const isEditAttr = $productFormElement.length ? $productFormElement.data('is-edit') : false;
    // Handle both string 'true' and boolean true
    const isEdit = isEditAttr === 'true' || isEditAttr === true;
    const storeUrl = $productFormElement.length ? $productFormElement.data('store-url') || '' : '';
    const updateUrl = $productFormElement.length ? $productFormElement.data('update-url') || '' : '';
    const formAction = $productFormElement.length ? $productFormElement.attr('action') || '' : '';
    
    // Use update URL if in edit mode, otherwise use store URL
    // Check form action as fallback since it's set correctly in the blade template
    let url;
    if (isEdit) {
        // In edit mode, prefer updateUrl, then formAction, then storeUrl as last resort
        url = updateUrl || formAction || storeUrl;
    } else {
        // In create mode, use storeUrl or formAction
        url = storeUrl || formAction;
    }
    
    const method = 'POST'; // Always use POST method
    
    // Debug logging (can be removed in production)
    console.log('Form submission:', {
        isEdit: isEdit,
        isEditAttr: isEditAttr,
        storeUrl: storeUrl,
        updateUrl: updateUrl,
        formAction: formAction,
        finalUrl: url
    });
    
    $.ajax({
        url: url,
        method: method,
        data: cleanedFormData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                showToast('success', data.message);
                clearFormDraft();
                localStorage.removeItem(VARIANT_STORAGE_KEY);
                setTimeout(() => {
                    window.location.href = '{{ route("products.index") }}';
                }, 1500);
            } else {
                showToast('error', data.message || 'Error creating product');
                if (data.errors) {
                    displayValidationErrors(data.errors);
                }
            }
        },
        error: function(xhr, status, error) {
            showToast('error', 'An error occurred while creating the product');
        },
        complete: function() {
            $submitBtn.html(originalText);
            $submitBtn.prop('disabled', false);
        }
    });
}

function normalizeFieldName(field) {
    if (!field || typeof field !== 'string') {
        return field;
    }

    if (field.includes('.')) {
        const segments = field.split('.');
        const [first, ...rest] = segments;
        return first + rest.map(segment => `[${segment}]`).join('');
    }

    return field;
}


function handleMissingFields(missingFields) {
    // Clear previous errors
    clearFieldErrors();
    
    // Field name mapping for user-friendly messages
    const fieldNames = {
        'name': 'Product Name',
        'sku': 'SKU',
        'price': 'Regular Price',
        'brand_ids[]': 'Brand Selection',
        'type': 'Product Type',
        'status': 'Product Status'
    };
    
    // Field to tab mapping
    const fieldToSectionMap = {
        'name': 'section-basic-info',
        'sku': 'section-basic-info',
        'price': 'section-basic-info',
        'brand_ids[]': 'section-categories',
        'category_id': 'section-categories',
        'type': 'section-basic-info',
        'status': 'section-categories'
    };
    
    // Create user-friendly error messages
    const userFriendlyFields = missingFields.map(field => fieldNames[field] || field);
    const errorMessage = `Please fill in the following required fields:\n\n• ${userFriendlyFields.join('\n• ')}`;
    
    // Show user-friendly alert
    showUserFriendlyAlert('Missing Required Information', errorMessage, 'warning');
    
    // Highlight missing fields and determine which section to scroll to
    let targetSection = null;
    missingFields.forEach(field => {
        const $input = $('[name="' + field + '"]');
        if ($input.length) {
            highlightField($input[0]);
            
            // Determine section from field or input location
            if (!targetSection) {
                const $formSection = $input.closest('.form-section');
                if ($formSection.length) {
                    targetSection = $formSection.attr('id');
                } else if (fieldToSectionMap[field]) {
                    targetSection = fieldToSectionMap[field];
                }
            }
        }
    });
    
    // Scroll to section with first missing field
    if (targetSection) {
        scrollToSection(targetSection);
    }
    
    // Focus on first missing field
    const $firstMissingField = $('.is-invalid').first();
    if ($firstMissingField.length) {
        setTimeout(() => {
            $firstMissingField.focus();
            $('html, body').animate({
                scrollTop: $firstMissingField.offset().top - 100
            }, 300);
        }, 300);
    }
}

function clearFieldErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function highlightField(input) {
    const $input = $(input);
    $input.addClass('is-invalid');
    
    // Add shake animation for emphasis
    $input.css('animation', 'shake 0.5s ease-in-out');
    setTimeout(() => {
        $input.css('animation', '');
    }, 500);
}

function showUserFriendlyAlert(title, message, type = 'error') {
    // Remove existing alert if any
    $('#userFriendlyAlert').remove();
    
    // Map type to Bootstrap alert class
    const alertClass = type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 
                      type === 'success' ? 'alert-success' : 
                      type === 'info' ? 'alert-info' : 'alert-primary';
    
    // Map type to icon
    const iconClass = type === 'error' ? 'fa-exclamation-triangle' : 
                     type === 'warning' ? 'fa-exclamation-circle' : 
                     type === 'success' ? 'fa-check-circle' : 
                     type === 'info' ? 'fa-info-circle' : 'fa-info-circle';
    
    // Create simple Bootstrap alert
    const $alertDiv = $('<div>', {
        class: 'alert ' + alertClass + ' alert-dismissible fade show',
        id: 'userFriendlyAlert',
        role: 'alert'
    }).html(`
        <div class="d-flex align-items-center">
            <i class="fas ${iconClass} me-2"></i>
            <div class="flex-grow-1">
                <strong>${title}</strong>
                <div style="white-space: pre-wrap; margin-top: 0.5rem;">${message}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `);
    
    // Add to the top of the form
    const $form = $('#productForm');
    if ($form.length) {
        $form.prepend($alertDiv);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            $alertDiv.remove();
        }, 8000);
        
        // Scroll to top to show the alert
        $('html, body').animate({
            scrollTop: $alertDiv.offset().top - 20
        }, 300);
    }
}

function showToast(type, message) {
    const $toast = $('#toast');
    if (!$toast.length) {
        showUserFriendlyAlert(type === 'success' ? 'Success' : 'Error', message, type === 'success' ? 'success' : 'danger');
        return;
    }
    const $toastBody = $toast.find('.toast-body');
    const $toastHeader = $toast.find('.toast-header');
    
    $toastBody.text(message);
    
    if (type === 'success') {
        $toastHeader.html('<i class="fas fa-check-circle text-success me-2"></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>');
    } else {
        $toastHeader.html('<i class="fas fa-times-circle text-danger me-2"></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>');
    }
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast($toast[0]);
        bsToast.show();
    }
}

// Initialize Quill editor if not already initialized
$(document).ready(function() {
    // Wait a bit for the DOM to be fully ready
    setTimeout(function() {
        const $quillElements = $('.sa-quill-control:not(.sa-quill-control--ready)');
        if ($quillElements.length > 0 && typeof window.stroyka !== 'undefined' && window.stroyka.quill) {
            $quillElements.each(function() {
                window.stroyka.quill.init(this);
            });
        }
    }, 100);
    
    // Add event listeners to clear errors when user starts typing
    $('input, select, textarea').on('input', function() {
        const $input = $(this);
        if ($input.hasClass('is-invalid')) {
            $input.removeClass('is-invalid');
            const $feedback = $input.parent().find('.invalid-feedback');
            if ($feedback.length) {
                $feedback.text('');
            }
        }
    });


    // Chips Input Implementation
    class ChipsInput {
        constructor(containerId, selectId, optionsId, searchId) {
            this.$container = $('#' + containerId);
            this.$select = $('#' + selectId);
            this.$optionsContainer = $('#' + optionsId);
            this.$searchInput = $('#' + searchId);
            this.$chipsList = $('#' + containerId.replace('Chips', 'ChipsList'));
            this.$dropdown = $('#' + containerId.replace('Chips', 'Dropdown'));
            
            this.init();
        }
        
        init() {
            this.populateOptions();
            this.bindEvents();
            this.updateChips();
        }
        
        populateOptions() {
            if (!this.$select.length || !this.$optionsContainer.length) return;
            
            this.$optionsContainer.empty();
            const options = this.$select[0].options;
            
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if (option.value) {
                    const $optionElement = $('<div>', {
                        class: 'chips-option',
                        'data-value': option.value
                    }).text(option.textContent);
                    
                    if (option.selected) {
                        $optionElement.addClass('selected');
                    }
                    
                    $optionElement.on('click', () => {
                        this.toggleOption($optionElement[0], option);
                    });
                    
                    this.$optionsContainer.append($optionElement);
                }
            }
        }
        
        bindEvents() {
            // Search functionality
            if (this.$searchInput.length) {
                this.$searchInput.on('input', (e) => {
                    this.filterOptions($(e.target).val());
                });
            }
            
            // Click outside to close dropdown
            $(document).on('click', (e) => {
                if (!this.$container[0].contains(e.target)) {
                    this.hideDropdown();
                }
            });
            
            // Click on container to show dropdown
            if (this.$container.length) {
                this.$container.on('click', (e) => {
                    if (e.target === this.$container[0] || e.target === this.$chipsList[0]) {
                        this.showDropdown();
                    }
                });
            }
        }
        
        filterOptions(searchTerm) {
            const $options = this.$optionsContainer.find('.chips-option');
            const term = searchTerm.toLowerCase();
            
            $options.each(function() {
                const $option = $(this);
                const text = $option.text().toLowerCase();
                $option.toggle(text.includes(term));
            });
        }
        
        toggleOption(optionElement, selectOption) {
            const $optionElement = $(optionElement);
            const isSelected = $optionElement.hasClass('selected');
            
            if (isSelected) {
                $optionElement.removeClass('selected');
                selectOption.selected = false;
            } else {
                $optionElement.addClass('selected');
                selectOption.selected = true;
            }
            
            this.updateChips();
        }
        
        updateChips() {
            if (!this.$chipsList.length || !this.$select.length) return;
            
            this.$chipsList.empty();
            const selectedOptions = Array.from(this.$select[0].selectedOptions);
            
            selectedOptions.forEach((option, index) => {
                const $chip = $('<span>', {
                    class: 'chip'
                }).html(`
                    <span class="chip-text">${option.textContent}</span>
                    <button type="button" class="chip-remove" onclick="chipsInputs['${this.$select[0].id}'].removeChip('${option.value}')">
                        <i class="fas fa-times"></i>
                    </button>
                `);
                this.$chipsList.append($chip);
            });
        }
        
        removeChip(value) {
            const option = this.$select[0].querySelector('option[value="' + value + '"]');
            const $optionElement = this.$optionsContainer.find('[data-value="' + value + '"]');
            
            if (option) {
                option.selected = false;
            }
            if ($optionElement.length) {
                $optionElement.removeClass('selected');
            }
            
            this.updateChips();
        }
        
        showDropdown() {
            if (this.$dropdown.length) {
                this.$dropdown.show();
            }
        }
        
        hideDropdown() {
            if (this.$dropdown.length) {
                this.$dropdown.hide();
            }
        }
    }
    
    // Initialize chips inputs
    window.chipsInputs = {};
    
    // Categories chips input
    if ($('#categoryChips').length) {
        window.chipsInputs['categorySelect'] = new ChipsInput('categoryChips', 'categorySelect', 'categoryOptions', 'categorySearch');
    }
    
    // Brands chips input
    if ($('#brandChips').length) {
        window.chipsInputs['brandSelect'] = new ChipsInput('brandChips', 'brandSelect', 'brandOptions', 'brandSearch');
    }
});

function debounce(fn, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function serializeForm() {
    if (!productForm) {
        return {};
    }
    const formData = new FormData(productForm);
    const payload = {};

    for (const [key, value] of formData.entries()) {
        if (key === '_token' || key === '_method') {
            continue;
        }
        if (value instanceof File) {
            continue;
        }
        const normalizedValue = typeof value === 'string' ? value : '';
        if (payload[key] !== undefined) {
            if (!Array.isArray(payload[key])) {
                payload[key] = [payload[key]];
            }
            payload[key].push(normalizedValue);
        } else {
            payload[key] = normalizedValue;
        }
    }

    return payload;
}

function saveFormDraft() {
    if (isEditMode || !productForm) {
        return;
    }
    try {
        const serialized = serializeForm();
        localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(serialized));
    } catch (error) {
        // Silently fail if localStorage is not available
    }
}

function restoreFormDraft() {
    if (!productForm) {
        return;
    }
    const raw = localStorage.getItem(FORM_STORAGE_KEY);
    if (!raw) {
        return;
    }

    let data;
    try {
        data = JSON.parse(raw);
    } catch (error) {
        localStorage.removeItem(FORM_STORAGE_KEY);
        return;
    }

    Object.entries(data).forEach(([name, storedValue]) => {
        const $fields = $(productForm).find('[name="' + name + '"]');
        if (!$fields.length) {
            return;
        }

        const values = Array.isArray(storedValue) ? storedValue : [storedValue];
        const primaryField = $fields[0];

        if (primaryField.tagName === 'SELECT' && primaryField.multiple) {
            const options = Array.from(primaryField.options);
            options.forEach(option => {
                option.selected = values.includes(option.value);
            });
            $(primaryField).trigger('change');
            return;
        }

        if (primaryField.type === 'checkbox' || primaryField.type === 'radio') {
            $fields.each(function() {
                $(this).prop('checked', values.includes($(this).val()));
            });
            $fields.first().trigger('change');
            return;
        }

        $fields.each((index, field) => {
            const value = values[index] !== undefined ? values[index] : values[0];
            if (value !== undefined) {
                $(field).val(value);
            }
        });

        $(primaryField).trigger('change');
    });
}

function clearFormDraft() {
    localStorage.removeItem(FORM_STORAGE_KEY);
}

// Single-Page Navigation Functions
function initializeSinglePageNavigation() {
    const $navLinks = $('#productFormNav .nav-link');
    const $sections = $('.form-section');
    
    // Smooth scroll on navigation click
    $navLinks.on('click', function(e) {
        e.preventDefault();
        const targetId = $(this).attr('href');
        const $targetSection = $(targetId);
        
        if ($targetSection.length) {
            const $navBar = $('#productFormNav');
            const navBarHeight = $navBar.length ? $navBar.outerHeight() : 0;
            const offsetTop = $targetSection.offset().top - navBarHeight - 20;
            
            $('html, body').animate({
                scrollTop: offsetTop
            }, 500);
            
            // Update active state
            updateActiveNavLink($(this));
            
            // If navigating to images section, populate variant images
            if (targetId === '#section-images' && window.populateVariantImagesContainer) {
                setTimeout(function() {
                    window.populateVariantImagesContainer();
                }, 600);
            }
        }
    });
    
    // Scroll spy - update active nav link based on scroll position
    let scrollTimeout;
    $(window).on('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            updateActiveNavFromScroll();
        }, 100);
    });
    
    // Handle URL hash on page load
    if (window.location.hash) {
        const hash = window.location.hash;
        const $targetSection = $(hash);
        if ($targetSection.length) {
            setTimeout(() => {
                const $navBar = $('#productFormNav');
                const navBarHeight = $navBar.length ? $navBar.outerHeight() : 0;
                const offsetTop = $targetSection.offset().top - navBarHeight - 20;
                $('html, body').animate({
                    scrollTop: offsetTop
                }, 500);
            }, 100);
        }
    }
    
    // Update URL hash when scrolling to sections using IntersectionObserver
    const observerOptions = {
        root: null,
        rootMargin: '-120px 0px -50% 0px',
        threshold: [0, 0.25, 0.5, 0.75, 1]
    };
    
    let currentActiveSection = null;
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.intersectionRatio > 0.25) {
                const sectionId = entry.target.id;
                const $navLink = $('#productFormNav .nav-link[href="#' + sectionId + '"]');
                if ($navLink.length && currentActiveSection !== sectionId) {
                    currentActiveSection = sectionId;
                    updateActiveNavLink($navLink);
                    // Update URL without triggering scroll
                    if (history.replaceState) {
                        history.replaceState(null, null, '#' + sectionId);
                    }
                }
            }
        });
    }, observerOptions);
    
    $sections.each(function() {
        observer.observe(this);
    });
    
    // Initial active section detection
    updateActiveNavFromScroll();
}

function updateActiveNavLink($activeLink) {
    $('#productFormNav .nav-link').removeClass('active');
    $activeLink.addClass('active');
}

function updateActiveNavFromScroll() {
    const $navBar = $('#productFormNav');
    const navBarHeight = $navBar.length ? $navBar.outerHeight() : 0;
    const scrollPosition = $(window).scrollTop() + navBarHeight + 100;
    
    const $sections = $('.form-section');
    let $currentSection = null;
    
    $sections.each(function() {
        const $section = $(this);
        const sectionTop = $section.offset().top;
        const sectionHeight = $section.outerHeight();
        
        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            $currentSection = $section;
        }
    });
    
    if ($currentSection && $currentSection.length) {
        const sectionId = $currentSection.attr('id');
        const $navLink = $('#productFormNav .nav-link[href="#' + sectionId + '"]');
        if ($navLink.length) {
            updateActiveNavLink($navLink);
        }
    }
}

// Function to scroll to a specific section programmatically
function scrollToSection(sectionId) {
    // Handle both with and without # prefix
    const id = sectionId.startsWith('#') ? sectionId.substring(1) : sectionId;
    const $targetSection = $('#' + id);
    
    if ($targetSection.length) {
        const $navBar = $('#productFormNav');
        const navBarHeight = $navBar.length ? $navBar.outerHeight() : 0;
        const offsetTop = $targetSection.offset().top - navBarHeight - 20;
        
        $('html, body').animate({
            scrollTop: Math.max(0, offsetTop)
        }, 500);
        
        // Update active nav link
        const $navLink = $('#productFormNav .nav-link[href="#' + id + '"]');
        if ($navLink.length) {
            updateActiveNavLink($navLink);
        }
    }
}

// Enhanced error handling to navigate to section with errors
function displayValidationErrors(errors) {
    // Clear previous errors
    clearFieldErrors();

    const collectedMessages = [];
    const errorTabs = new Set();

    // Display new errors and track which tabs have errors
    Object.keys(errors).forEach(field => {
        const normalizedField = normalizeFieldName(field);
        const fieldAliasMap = {
            'slug': 'seo_url_slug'
        };

        const fallbackSelectorMap = {
            'slug': '#urlSlug'
        };

        const resolvedField = fieldAliasMap[normalizedField] || fieldAliasMap[field] || normalizedField;

        let $input = $('[name="' + resolvedField + '"]');
        if (!$input.length && fallbackSelectorMap[field]) {
            $input = $(fallbackSelectorMap[field]);
        }

        if ($input.length) {
            // Determine which section contains this field
            const $formSection = $input.closest('.form-section');
            if ($formSection.length) {
                const sectionId = $formSection.attr('id');
                if (sectionId) {
                    errorTabs.add(sectionId);
                }
            } else {
                // Fallback: try to find section by field name patterns
                const fieldName = resolvedField.toLowerCase();
                if (fieldName.includes('name') || fieldName.includes('sku') || fieldName.includes('description') || fieldName.includes('price')) {
                    errorTabs.add('section-basic-info');
                } else if (fieldName.includes('category') || fieldName.includes('brand') || fieldName.includes('tag')) {
                    errorTabs.add('section-categories');
                } else if (fieldName.includes('variant')) {
                    errorTabs.add('section-variants');
                } else if (fieldName.includes('image')) {
                    errorTabs.add('section-images');
                }
            }
            
            highlightField($input[0]);
            let $feedback = $input.parent().find('.invalid-feedback');

            if (!$feedback.length) {
                const $group = $input.closest('.form-group, .form-floating, .form-control-wrap, .input-group, .mb-3, .col-md-6, div');
                if ($group.length) {
                    $feedback = $group.find('.invalid-feedback');
                }
            }
            if ($feedback.length) {
                $feedback.text(errors[field][0]);
            }
            
            $input.addClass('is-invalid');
        }

        const fieldErrors = errors[field] || errors[normalizedField];

        if (Array.isArray(fieldErrors)) {
            fieldErrors.forEach(message => {
                collectedMessages.push(message);
            });
        }
    });

    // Scroll to first section with errors
    if (errorTabs.size > 0) {
        const firstErrorSection = Array.from(errorTabs)[0];
        scrollToSection(firstErrorSection);
    }

    // Focus on first error field
    const $firstErrorField = $('.is-invalid').first();
    if ($firstErrorField.length) {
        setTimeout(() => {
            $firstErrorField.focus();
            $('html, body').animate({
                scrollTop: $firstErrorField.offset().top - 100
            }, 300);
        }, 300);
    }

    if (collectedMessages.length) {
        const uniqueMessages = [...new Set(collectedMessages)];
        const formattedMessages = uniqueMessages.map(message => `• ${message}`).join('\n');
        showUserFriendlyAlert('Validation Errors', formattedMessages, 'danger');
    }
}
</script>
@endpush

<style>
/* Shake animation for error fields */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* Enhanced error styling */
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    animation: shake 0.5s ease-in-out;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
    font-weight: 500;
}

/* Enhanced form control styling */
.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Select styling for errors */
.form-select.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Textarea styling for errors */
textarea.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Smooth scrolling for focus */
html {
    scroll-behavior: smooth;
}

/* Enhanced button styling */
.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Sidebar card styling */
.col-lg-4 .card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.col-lg-4 .card-header {
    background-color: #f8f9fa;
    /* border-bottom: 1px solid #e9ecef; */
}

.col-lg-4 .card-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
}

/* Form styling in sidebar */
.col-lg-4 .form-label-sm {
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.col-lg-4 .form-control-sm,
.col-lg-4 .form-select-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.col-lg-4 .form-text-sm {
    font-size: 0.6875rem;
    margin-top: 0.125rem;
}

/* Chips Input Styling */
.chips-input-container {
    position: relative;
    width: 100%;
}

.chips-input {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    padding: 0.25rem;
    background-color: #fff;
    cursor: pointer;
    position: relative;
}

.chips-input:hover {
    border-color: #86b7fe;
}

.chips-input:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.chips-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    min-height: 24px;
}

.chip {
    display: inline-flex;
    align-items: center;
    background-color: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    gap: 0.25rem;
}

.chip-text {
    font-weight: 500;
}

.chip-remove {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    margin: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.chip-remove:hover {
    background-color: #dc3545;
    color: white;
}

.chip-remove i {
    font-size: 0.5rem;
}

.chips-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 1000;
    display: none;
    max-height: 200px;
    overflow-y: auto;
}

.chips-search {
    padding: 0.5rem;
    /* border-bottom: 1px solid #e9ecef; */
}

.chips-options {
    max-height: 150px;
    overflow-y: auto;
}

.chips-option {
    padding: 0.5rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s ease;
}

.chips-option:hover {
    background-color: #f8f9fa;
}

.chips-option.selected {
    background-color: #e3f2fd;
    color: #1976d2;
    font-weight: 500;
}

.chips-option:last-child {
    border-bottom: none;
}

/* Two Column Layout Styling */
.row.g-4 {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.col-lg-8, .col-lg-4 {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

/* Ensure cards in sidebar have proper spacing */
.col-lg-4 .card {
    margin-bottom: 1.5rem;
}

.col-lg-4 .card:last-child {
    margin-bottom: 0;
}

/* Debug: Add borders to see layout (remove in production) */
.col-lg-8 {
    border-left: 2px solid #e9ecef;
    padding-left: 1rem;
}

.col-lg-4 {
    border-left: 2px solid #dee2e6;
    padding-left: 1rem;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .col-lg-8, .col-lg-4 {
        border-left: none;
        padding-left: 0.5rem;
    }
}
</style>
 
 