{{-- Basic Product Information Section - Compact --}}
<div class="card mb-3" id="basicInfoSection">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>Basic Information
            </h6>  
            <button type="button" class="btn btn-sm btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#basicInfoModal">
                <i class="fas fa-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            {{-- Product Name --}}
            <div class="col-md-6">
                <label for="productName" class="form-label form-label-sm">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="productName" name="name" 
                       value="{{ old('name', $product->name ?? '') }}" required placeholder="Enter product name">
                <div class="invalid-feedback"></div>
            </div>

            {{-- URL Slug --}}
            <div class="col-md-6">
                <label for="urlSlug" class="form-label form-label-sm">URL Slug</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">{{ url('/products/') }}/</span>
                    <input type="text" class="form-control form-control-sm" id="urlSlug" name="seo_url_slug" 
                           value="{{ old('seo_url_slug', $product->seo_url_slug ?? ($product->slug ?? '')) }}" 
                           placeholder="product-url-slug">
                </div>
                <small class="text-muted small">Custom URL for this product</small>
                <div class="invalid-feedback"></div>
            </div> 
           
            {{-- Short Description - Compact --}}
            <div class="col-12 ">
                <label for="productShortDescription" class="form-label form-label-sm">Short Description</label>
                <textarea class="form-control form-control-sm" id="productShortDescription" name="short_description" 
                          placeholder="Brief product summary...">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                <div class="invalid-feedback"></div>
            </div>


            {{-- Product Status, Featured Product, GST Type, GST Percentage - Single Row --}}
            <div class="col-3">
                <label for="productStatus" class="form-label form-label-sm">Product Status <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="productStatus" name="status" required>
                    <option value="published" {{ old('status', $product->status ?? 'published') == 'published' ? 'selected' : '' }}>Active</option>
                    <option value="hidden" {{ old('status', $product->status ?? 'published') == 'hidden' ? 'selected' : '' }}>Draft</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="col-3">
                <label for="featuredProduct" class="form-label form-label-sm">Featured Product</label>
                <select class="form-select form-select-sm" id="featuredProduct" name="featured">
                    <option value="0" {{ old('featured', $product->featured ?? false) ? '' : 'selected' }}>No</option>
                    <option value="1" {{ old('featured', $product->featured ?? false) ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div class="col-3">
                <label for="gstType" class="form-label form-label-sm">GST Type</label>
                @php
                    $gstTypeValue = old('gst_type', isset($product) && $product->exists ? ($product->gst_type === false ? '0' : '1') : '1');
                @endphp
                <select class="form-select form-select-sm" id="gstType" name="gst_type">
                    <option value="1" {{ $gstTypeValue == '1' || $gstTypeValue === true || $gstTypeValue === 1 ? 'selected' : '' }}>Inclusive of GST</option>
                    <option value="0" {{ $gstTypeValue == '0' || $gstTypeValue === false || $gstTypeValue === 0 ? 'selected' : '' }}>Exclusive of GST</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="col-3">
                <label for="gstPercentage" class="form-label form-label-sm">GST Percentage</label>
                <select class="form-select form-select-sm" id="gstPercentage" name="gst_percentage">
                    <option value="">Select GST Percentage</option>
                    <option value="0" {{ old('gst_percentage', $product->gst_percentage ?? '') == '0' || old('gst_percentage', $product->gst_percentage ?? '') == 0 ? 'selected' : '' }}>0%</option>
                    <option value="3" {{ old('gst_percentage', $product->gst_percentage ?? '') == '3' || old('gst_percentage', $product->gst_percentage ?? '') == 3 ? 'selected' : '' }}>3%</option>
                    <option value="5" {{ old('gst_percentage', $product->gst_percentage ?? '') == '5' || old('gst_percentage', $product->gst_percentage ?? '') == 5 ? 'selected' : '' }}>5%</option>
                    <option value="12" {{ old('gst_percentage', $product->gst_percentage ?? '') == '12' || old('gst_percentage', $product->gst_percentage ?? '') == 12 ? 'selected' : '' }}>12%</option>
                    <option value="18" {{ old('gst_percentage', $product->gst_percentage ?? '') == '18' || old('gst_percentage', $product->gst_percentage ?? '') == 18 ? 'selected' : '' }}>18%</option>
                    <option value="28" {{ old('gst_percentage', $product->gst_percentage ?? '') == '28' || old('gst_percentage', $product->gst_percentage ?? '') == 28 ? 'selected' : '' }}>28%</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

        </div>

       
      
    </div>
</div>

<style>
/* Compact form styling */
.form-label-sm {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.form-control-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
}

/* Collapsible description styling */
#descriptionCollapse {
    margin-top: 0.5rem;
}

/* RichTextEditor styling */
#productShortDescription {
    min-height: 150px;
}

/* Compact card styling */
.card-header.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.card-body.py-3 {
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .row.g-2 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
}
</style>

<!-- RichTextEditor -->
<link rel="stylesheet" href="{{ asset('frontend/js/richtexteditor/rte_theme_default.css') }}" />
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src="{{ asset('frontend/js/richtexteditor/lang/rte-lang-en.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shortDescriptionTextarea = document.getElementById('productShortDescription');
    const productNameInput = document.getElementById('productName');
    const urlSlugInput = document.getElementById('urlSlug');
    let shortDescriptionEditor = null;
    let slugManuallyEdited = false;

    const slugify = (value = '') => value
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');

    if (urlSlugInput) {
        if (!urlSlugInput.value && productNameInput && productNameInput.value) {
            urlSlugInput.value = slugify(productNameInput.value);
        } else if (urlSlugInput.value) {
            urlSlugInput.value = slugify(urlSlugInput.value);
        }

        urlSlugInput.addEventListener('input', function() {
            if (this.value.trim().length === 0) {
                slugManuallyEdited = false;
                this.value = '';
                return;
            }

            slugManuallyEdited = true;
            this.value = slugify(this.value);
        });

        urlSlugInput.addEventListener('blur', function() {
            this.value = slugify(this.value);
        });
    }

    if (productNameInput && urlSlugInput) {
        productNameInput.addEventListener('input', function() {
            if (!slugManuallyEdited || !urlSlugInput.value.trim()) {
                urlSlugInput.value = slugify(this.value);
            }
        });
    }

    // Initialize RichTextEditor for Short Description
    if (shortDescriptionTextarea && typeof RichTextEditor !== 'undefined') {
        // Configure RichTextEditor with all options enabled
        RTE_DefaultConfig.url_base = "{{ asset('frontend/js/richtexteditor') }}";
        RTE_DefaultConfig.toolbar = "full"; // Use full toolbar with all options
        RTE_DefaultConfig.editorResizeMode = "both"; // Allow both width and height resize
        RTE_DefaultConfig.showTagList = true; // Show tag list
        RTE_DefaultConfig.showStatistics = true; // Show statistics
        RTE_DefaultConfig.showPlusButton = true; // Show plus button
        RTE_DefaultConfig.showFloatTextToolBar = true; // Show float text toolbar
        RTE_DefaultConfig.showFloatLinkToolBar = true; // Show float link toolbar
        RTE_DefaultConfig.showFloatImageToolBbar = true; // Show float image toolbar
        RTE_DefaultConfig.showFloatTableToolBar = true; // Show float table toolbar
        RTE_DefaultConfig.showFloatParagraph = true; // Show float paragraph
        RTE_DefaultConfig.enableDragDrop = true; // Enable drag and drop
        RTE_DefaultConfig.enableObjectResizing = true; // Enable object resizing
        RTE_DefaultConfig.toggleBorder = true; // Enable toggle border
        
        // Initialize the editor
        shortDescriptionEditor = new RichTextEditor(shortDescriptionTextarea);
        
        // Update textarea on change
        shortDescriptionEditor.attachEvent("textchanged", function() {
            shortDescriptionTextarea.value = shortDescriptionEditor.getHTMLCode();
        });
    }

    // Update textarea before form submission
    const productForm = document.getElementById('productForm') || document.querySelector('form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            if (shortDescriptionEditor && typeof shortDescriptionEditor.getHTMLCode === 'function') {
                shortDescriptionTextarea.value = shortDescriptionEditor.getHTMLCode();
            }
        });
    }
});
</script>

<!-- Basic Information Modal -->
<div class="modal fade" id="basicInfoModal" tabindex="-1" aria-labelledby="basicInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="basicInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Basic Information Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-tag text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Product Name:</strong> The main title of your product <small class="text-muted">(Required - this is what customers see first)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-link text-secondary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>URL Slug:</strong> The readable part of your product URL <small class="text-muted">(Optional - keep it short, descriptive and unique)</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-eye text-info me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Product Status:</strong> Controls when and how the product is visible <small class="text-muted">(Active = Published, Draft = Hidden)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-star text-warning me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Featured Product:</strong> Highlight this product in featured sections <small class="text-muted">(Makes product more prominent on homepage and category pages)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-align-left text-warning me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Short Description:</strong> Brief summary for product listings and search results <small class="text-muted">(Optional - appears in product cards and previews)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt text-secondary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Description:</strong> Detailed product information with rich text formatting <small class="text-muted">(Optional - full product details for customers)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Use clear, descriptive product names</strong> that customers can easily understand <small class="text-muted">(Improves searchability and clarity)</small></div>
                        <div><strong>Write compelling short descriptions</strong> that highlight key benefits <small class="text-muted">(Increases conversion rates)</small></div>
                        <div><strong>Use rich descriptions</strong> with formatting, images, and detailed specifications <small class="text-muted">(Builds customer confidence)</small></div>
                        <div><strong>Keep slugs clean and keyword-friendly</strong> using lowercase letters and hyphens <small class="text-muted">(Helps with SEO and readability)</small></div>
                        <div><strong>Note:</strong> SKU, pricing, stock, and dimensions are managed at the variant level in the Variants section <small class="text-muted">(Each variant has its own SKU, pricing, and inventory)</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

