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
                <div id="shortDescriptionEditor"></div>
                <textarea class="form-control form-control-sm d-none" id="productShortDescription" name="short_description" 
                          placeholder="Brief product summary...">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Full Description - Collapsible --}}
            <div class="col-12">
                <label for="productDescription" class="form-label form-label-sm mb-1">Detailed Description</label>

                <div id="descriptionEditor"></div>

                <textarea class="form-control d-none" 
                        id="productDescription" 
                        name="description"
                        placeholder="Detailed product description...">
                    {{ old('description', $product->description ?? '') }}
                </textarea>

                <div class="invalid-feedback"></div>
            </div>

            {{-- Product Status and Featured --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label form-label-sm d-block mb-2">
                        Product Status <span class="text-danger">*</span>
                    </label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="status" id="statusPublished" value="published"
                               {{ old('status', $product->status ?? 'published') == 'published' ? 'checked' : '' }}>
                        <label class="btn btn-outline-success btn-sm" for="statusPublished">
                            <i class="fas fa-check-circle me-1"></i>Active
                        </label>
                        <input type="radio" class="btn-check" name="status" id="statusDraft" value="hidden"
                               {{ old('status', $product->status ?? 'published') == 'hidden' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary btn-sm" for="statusDraft">
                            <i class="fas fa-eye-slash me-1"></i>Draft
                        </label>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label form-label-sm d-block mb-2">
                        Featured Product
                    </label>
                    <div class="d-flex align-items-center">
                        <div class="form-check form-switch me-2">
                            <input class="form-check-input" type="checkbox" id="featuredProduct" name="featured" 
                                   {{ old('featured', $product->featured ?? false) ? 'checked' : '' }}>
                        </div>
                        <small class="text-muted small mb-0">Highlight this product in featured sections</small>
                    </div>
                </div>
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

/* CKEditor styling */
#shortDescriptionEditor .ck-editor__editable,
#descriptionEditor .ck-editor__editable {
    min-height: 100px;
    font-size: 0.875rem;
}

#shortDescriptionEditor .ck-toolbar,
#descriptionEditor .ck-toolbar {
    font-size: 0.875rem;
}

#shortDescriptionEditor .ck-toolbar .ck-button,
#descriptionEditor .ck-toolbar .ck-button {
    padding: 0.25rem;
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

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shortDescriptionTextarea = document.getElementById('productShortDescription');
    const descriptionTextarea = document.getElementById('productDescription');
    const productNameInput = document.getElementById('productName');
    const urlSlugInput = document.getElementById('urlSlug');
    let shortDescriptionEditor = null;
    let descriptionEditor = null;
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

    // Initialize CKEditor for Short Description
    ClassicEditor
        .create(document.querySelector('#shortDescriptionEditor'), {
            toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            placeholder: 'Brief product summary...',
            height: 120
        })
        .then(editor => {
            shortDescriptionEditor = editor;
            
            // Set initial content
            const initialContent = shortDescriptionTextarea.value;
            if (initialContent) {
                editor.setData(initialContent);
            }
            
            // Update textarea on change
            editor.model.document.on('change:data', () => {
                shortDescriptionTextarea.value = editor.getData();
            });
        })
        .catch(error => {
            console.error('Error initializing Short Description CKEditor:', error);
        });

    // Initialize CKEditor for Description
    ClassicEditor
        .create(document.querySelector('#descriptionEditor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
            placeholder: 'Detailed product description...',
            height: 200
        })
        .then(editor => {
            descriptionEditor = editor;
            
            // Set initial content
            const initialContent = descriptionTextarea.value;
            if (initialContent) {
                editor.setData(initialContent);
            }
            
            // Update textarea on change
            editor.model.document.on('change:data', () => {
                descriptionTextarea.value = editor.getData();
            });
        })
        .catch(error => {
            console.error('Error initializing Description CKEditor:', error);
        });

    // Handle collapsible description
    const descriptionCollapse = document.getElementById('descriptionCollapse');
    const toggleButton = document.querySelector('[data-bs-target="#descriptionCollapse"]');
    
    if (descriptionCollapse && toggleButton) {
        descriptionCollapse.addEventListener('show.bs.collapse', function () {
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
        });
        
        descriptionCollapse.addEventListener('hide.bs.collapse', function () {
            toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i>';
        });
    }
    
    // Auto-expand description if it has content
    if (descriptionTextarea && descriptionTextarea.value.trim() !== '') {
        descriptionCollapse.classList.add('show');
        if (toggleButton) {
            toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
        }
    }

    // Update textareas before form submission
    const productForm = document.getElementById('productForm') || document.querySelector('form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            if (shortDescriptionEditor) {
                shortDescriptionTextarea.value = shortDescriptionEditor.getData();
            }
            if (descriptionEditor) {
                descriptionTextarea.value = descriptionEditor.getData();
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

