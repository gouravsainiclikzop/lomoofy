{{-- Product Images Section --}}
<div class="card mb-3" id="imagesSection">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-images me-2"></i>Featured Product Images
            </h6> 
            
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#imagesInfoModal">
                <svg class="svg-inline--fa fa-info-circle fa-w-16" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg><!-- <i class="fas fa-info-circle"></i> Font Awesome fontawesome.com -->
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            {{-- Image Upload with Drag & Drop --}}
            <div class="col-12">
                <label class="form-label form-label-sm">
                    <i class="fas fa-upload me-2"></i>Upload Product Images
                </label>
                <div class="image-upload-area" id="imageUploadArea">
                    <input type="file" class="d-none" id="productImages" name="images[]" 
                           multiple accept="image/*" onchange="previewImages(this)">
                    <div class="upload-placeholder text-center p-5 border-2 border-dashed rounded">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-2">
                            <strong>Drag & drop images here</strong> or 
                            <label for="productImages" class="text-primary cursor-pointer">
                                <u>browse</u>
                            </label>
                        </p>
                        <small class="text-muted">Supports JPG, PNG, GIF. Multiple files allowed.</small>
                        <small class="text-info d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Recommended dimensions:</strong> 620 × 780 pixels
                        </small>
                    </div>
                </div>
                <small class="text-muted small mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    First image will be set as primary. You can change it later.
                </small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Image Preview with Sortable --}}
            <div class="col-12" id="imagePreviewContainer" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label form-label-sm mb-0">
                        <i class="fas fa-images me-2"></i>Image Preview & Order
                    </label>
                </div>
                <div class="row g-3" id="imagePreviewGrid">
                    <!-- Image previews will be inserted here -->
                </div>
            </div>

            {{-- Existing Images (for edit mode) --}}
            @if(isset($product) && $product->images->count() > 0)
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label form-label-sm mb-0">
                        <i class="fas fa-images me-2"></i>Current Product Images
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-info" id="viewCurrentProductImagesBtn" data-product-id="{{ $product->id }}">
                        <i class="fas fa-images me-1"></i> View Current Product Images ({{ $product->images->count() }})
                    </button>
                </div>
                {{-- Hidden grid for form data --}}
                <div class="row g-3" id="existingImagesGrid" style="display: none;">
                    @foreach($product->images->sortBy('sort_order') as $index => $image)
                    <div class="col-md-2 col-sm-3 col-6 image-item" data-image-id="{{ $image->id }}" data-sort-order="{{ $image->sort_order }}">
                        <div class="card h-100 shadow-sm position-relative">
                            @if($image->is_primary)
                                <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                                    <i class="fas fa-star me-1"></i>Primary
                                </span>
                            @endif
                            <img src="{{ Storage::url($image->image_path) }}" class="card-img-top" 
                                 style="height: 180px; object-fit: cover; cursor: move;" 
                                 alt="{{ $image->alt_text ?? 'Product image' }}"
                                 draggable="true">
                            <div class="card-body p-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="primary_image_index" 
                                           value="{{ $index }}" id="primaryImage{{ $index }}"
                                           {{ $image->is_primary ? 'checked' : '' }}
                                           onchange="updatePrimaryImage({{ $index }})">
                                    <label class="form-check-label small" for="primaryImage{{ $index }}">
                                        Set as Primary
                                    </label>
                                </div>
                                <input type="hidden" name="image_sort_order[{{ $image->id }}]" value="{{ $image->sort_order }}">
                                <input class="form-check-input d-none" type="checkbox" name="remove_images[]" 
                                       value="{{ $image->id }}" id="removeImage{{ $image->id }}">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100"
                                        onclick="markImageForRemoval({{ $image->id }})">
                                    <i class="fas fa-trash-alt me-1"></i> Remove
                                </button>
                            </div>
                            @if($image->alt_text)
                                <div class="card-footer bg-light py-1">
                                    <small class="text-muted text-truncate d-block" title="{{ $image->alt_text }}">
                                        <i class="fas fa-tag me-1"></i>{{ $image->alt_text }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Current Product Images View Modal -->
<div class="modal fade" id="currentProductImagesModal" tabindex="-1" aria-labelledby="currentProductImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="currentProductImagesModalLabel">
                    <i class="fas fa-images me-2"></i>Current Featured Product Images
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="currentProductImagesContainer" class="row g-3">
                    <!-- Images will be loaded here -->
                </div>
                <div id="currentProductImagesEmpty" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p>No images available for this product.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Images Information Modal -->
<div class="modal fade" id="imagesInfoModal" tabindex="-1" aria-labelledby="imagesInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagesInfoModalLabel">
                    <i class="fas fa-images me-2"></i>Product Images Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-upload text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Image Upload:</strong> Select multiple images for your product <small class="text-muted">(Supports JPG, PNG, GIF formats)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-star text-warning me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Primary Image:</strong> The main image shown in product listings <small class="text-muted">(First uploaded image becomes primary by default)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-eye text-success me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Image Preview:</strong> See how your images will look before saving <small class="text-muted">(Helps ensure quality and composition)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-trash text-danger me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Remove Images:</strong> Delete unwanted images from your product <small class="text-muted">(Check the remove box and save to delete)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Recommended image dimensions:</strong> <span class="text-info">620 × 780 pixels</span> <small class="text-muted">(For best display quality, use these dimensions)</small></div>
                        <div><strong>Use high-quality images</strong> that showcase your product clearly <small class="text-muted">(Ensure images are sharp and well-lit)</small></div>
                        <div><strong>Include multiple angles</strong> to give customers a complete view <small class="text-muted">(Front, back, side views, details)</small></div>
                        <div><strong>Choose a compelling primary image</strong> that represents your product best <small class="text-muted">(This appears in search results and listings)</small></div>
                        <div><strong>Optimize file sizes</strong> for faster loading without losing quality <small class="text-muted">(Use image compression tools)</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle View Current Product Images button
    const viewCurrentProductImagesBtn = document.getElementById('viewCurrentProductImagesBtn');
    if (viewCurrentProductImagesBtn) {
        viewCurrentProductImagesBtn.addEventListener('click', function() {
            const existingImagesGrid = document.getElementById('existingImagesGrid');
            if (!existingImagesGrid) {
                return;
            }
            
            const imageItems = existingImagesGrid.querySelectorAll('.image-item');
            const container = document.getElementById('currentProductImagesContainer');
            const emptyMessage = document.getElementById('currentProductImagesEmpty');
            
            if (imageItems.length === 0) {
                container.innerHTML = '';
                container.style.display = 'none';
                emptyMessage.style.display = 'block';
            } else {
                container.innerHTML = '';
                container.style.display = 'flex';
                emptyMessage.style.display = 'none';
                
                imageItems.forEach((item, index) => {
                    const removeCheckbox = item.querySelector('input[type="checkbox"][name*="remove_images"]');
                    const isMarkedForRemoval = removeCheckbox && removeCheckbox.checked;
                    
                    // Skip images that are marked for removal - they won't appear in modal
                    if (isMarkedForRemoval) {
                        return;
                    }
                    
                    const img = item.querySelector('img');
                    const imageId = item.getAttribute('data-image-id');
                    const isPrimary = item.querySelector('.badge.bg-warning') !== null;
                    const altText = img ? img.getAttribute('alt') : '';
                    const imageSrc = img ? img.getAttribute('src') : '';
                    const sortOrder = item.getAttribute('data-sort-order');
                    const primaryRadio = item.querySelector('input[type="radio"]');
                    
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-sm-4 col-6';
                    
                    const card = document.createElement('div');
                    card.className = 'card h-100 shadow-sm position-relative';
                    if (isMarkedForRemoval) {
                        card.style.opacity = '0.5';
                    }
                    
                    let cardContent = '';
                    if (isPrimary) {
                        cardContent += '<span class="badge bg-warning position-absolute top-0 start-0 m-2"><i class="fas fa-star me-1"></i>Primary</span>';
                    }
                    if (isMarkedForRemoval) {
                        cardContent += '<span class="badge bg-danger position-absolute top-0 end-0 m-2"><i class="fas fa-trash me-1"></i>Marked for removal</span>';
                    }
                    
                    const isChecked = primaryRadio && primaryRadio.checked;
                    
                    cardContent += `
                        <img src="${imageSrc}" class="card-img-top" style="height: 200px; object-fit: cover; cursor: pointer;" alt="${altText}" onclick="window.open('${imageSrc}', '_blank')">
                        <div class="card-body p-2">
                            <div class="form-check mb-2">
                                <input class="form-check-input modal-primary-radio" type="radio" name="modal_primary_image_index" 
                                       value="${index}" id="modalPrimaryImage${index}" data-image-index="${index}" data-image-id="${imageId}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label small" for="modalPrimaryImage${index}" style="cursor: pointer;">
                                    Set as Primary
                                </label>
                            </div>
                            <small class="text-muted d-block mb-2">Sort Order: ${sortOrder || 0}</small>
                            ${altText ? `<small class="text-muted d-block text-truncate" title="${altText}"><i class="fas fa-tag me-1"></i>${altText}</small>` : ''}
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-2 modal-remove-btn" data-image-id="${imageId}">
                                <i class="fas fa-trash-alt me-1"></i> Mark for Removal
                            </button>
                        </div>
                    `;
                    
                    card.innerHTML = cardContent;
                    col.appendChild(card);
                    container.appendChild(col);
                    
                    // Add event listener to sync with hidden grid
                    const modalRadio = col.querySelector('.modal-primary-radio');
                    if (modalRadio) {
                        modalRadio.addEventListener('change', function() {
                            if (this.checked) {
                                // Uncheck all other radio buttons in modal
                                container.querySelectorAll('.modal-primary-radio').forEach(radio => {
                                    if (radio !== this) {
                                        radio.checked = false;
                                    }
                                });
                                
                                // Update the corresponding radio in hidden grid
                                const hiddenRadio = document.getElementById('primaryImage' + this.dataset.imageIndex);
                                if (hiddenRadio) {
                                    hiddenRadio.checked = true;
                                    updatePrimaryImage(parseInt(this.dataset.imageIndex));
                                }
                                
                                // Update visual badge
                                container.querySelectorAll('.badge.bg-warning').forEach(badge => {
                                    badge.remove();
                                });
                                
                                // Add primary badge to this card
                                const cardEl = this.closest('.card');
                                if (cardEl) {
                                    const existingBadge = cardEl.querySelector('.badge.bg-warning');
                                    if (!existingBadge) {
                                        const badge = document.createElement('span');
                                        badge.className = 'badge bg-warning position-absolute top-0 start-0 m-2';
                                        badge.innerHTML = '<i class="fas fa-star me-1"></i>Primary';
                                        cardEl.insertBefore(badge, cardEl.firstChild);
                                    }
                                }
                            }
                        });
                        
                        // Also allow clicking the label
                        const label = col.querySelector(`label[for="modalPrimaryImage${index}"]`);
                        if (label) {
                            label.addEventListener('click', function(e) {
                                e.preventDefault();
                                modalRadio.click();
                            });
                        }
                    }
                    
                    // Add event listener for remove button
                    const removeBtn = col.querySelector('.modal-remove-btn');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            const imgId = this.getAttribute('data-image-id');
                            if (imgId) {
                                markImageForRemoval(parseInt(imgId));
                            }
                        });
                    }
                });
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('currentProductImagesModal'));
            modal.show();
            
            // Ensure the checked radio button is visible after modal is shown
            setTimeout(function() {
                const checkedRadio = container.querySelector('.modal-primary-radio:checked');
                if (checkedRadio) {
                    // Scroll to checked radio if needed
                    checkedRadio.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 300);
        });
    }
    
    // Drag and drop functionality
    const uploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('productImages');
    
    if (uploadArea && fileInput) {
        // Click to browse
        uploadArea.addEventListener('click', function(e) {
            if (e.target.closest('.upload-placeholder')) {
                fileInput.click();
            }
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImages(fileInput);
            }
        });
    }
    
    // Initialize sortable for existing images
    if (typeof Sortable !== 'undefined') {
        const existingGrid = document.getElementById('existingImagesGrid');
        if (existingGrid) {
            new Sortable(existingGrid, {
                animation: 150,
                handle: 'img',
                onEnd: function(evt) {
                    // Update sort order
                    const items = existingGrid.querySelectorAll('.image-item');
                    items.forEach((item, index) => {
                        const imageId = item.dataset.imageId;
                        const sortInput = item.querySelector('input[name^="image_sort_order"]');
                        if (sortInput) {
                            sortInput.value = index;
                        }
                    });
                }
            });
        }
    }
});

function previewImages(input) {
    const container = document.getElementById('imagePreviewContainer');
    const grid = document.getElementById('imagePreviewGrid');
    const requiredWidth = 620;
    const requiredHeight = 780;
    
    if (input.files && input.files.length > 0) {
        container.style.display = 'block';
        grid.innerHTML = '';
        
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const matchesRecommended = img.width === requiredWidth && img.height === requiredHeight;
                    const col = document.createElement('div');
                    col.className = 'col-md-2 col-sm-3 col-6';
                    col.innerHTML = `
                        <div class="card h-100 shadow-sm">
                            <img src="${e.target.result}" class="card-img-top" 
                                 style="height: 180px; object-fit: cover;" alt="Preview">
                            <div class="card-body p-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="primary_image_index" 
                                           value="new_${index}" id="newPrimaryImage${index}" ${index === 0 ? 'checked' : ''}>
                                    <label class="form-check-label small" for="newPrimaryImage${index}">
                                        Set as Primary
                                    </label>
                                </div>
                                ${matchesRecommended ? 
                                    `<small class="text-success d-block">
                                        <i class="fas fa-check-circle me-1"></i>${img.width} × ${img.height} px<br>
                                        <small class="text-muted">Matches recommended size</small>
                                    </small>` :
                                    `<small class="text-info d-block">
                                        <i class="fas fa-info-circle me-1"></i>${img.width} × ${img.height} px<br>
                                        <small class="text-muted">Recommended: ${requiredWidth} × ${requiredHeight} px</small>
                                    </small>`
                                }
                                <small class="text-muted d-block text-truncate mt-1" title="${file.name}">
                                    <i class="fas fa-file-image me-1"></i>${file.name}
                                </small>
                                <small class="text-muted d-block">
                                    ${(file.size / 1024).toFixed(1)} KB
                                </small>
                            </div>
                        </div>
                    `;
                    grid.appendChild(col);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    } else {
        container.style.display = 'none';
    }
}

function updatePrimaryImage(index) {
    // Visual feedback for primary image selection
    document.querySelectorAll('[name="primary_image_index"]').forEach(radio => {
        const card = radio.closest('.card');
        if (card) {
            const badge = card.querySelector('.badge.bg-warning');
            if (radio.checked) {
                if (!badge) {
                    const primaryBadge = document.createElement('span');
                    primaryBadge.className = 'badge bg-warning position-absolute top-0 start-0 m-2';
                    primaryBadge.innerHTML = '<i class="fas fa-star me-1"></i>Primary';
                    card.style.position = 'relative';
                    card.querySelector('.card-img-top').parentNode.insertBefore(primaryBadge, card.querySelector('.card-img-top'));
                }
            } else if (badge) {
                badge.remove();
            }
        }
    });
}

function markImageForRemoval(imageId) {
    const checkbox = document.getElementById('removeImage' + imageId);
    if (!checkbox) {
        console.error('Checkbox not found for image ID:', imageId);
        return;
    }
    
    const isCurrentlyMarked = checkbox.checked;
    
    // Mark for removal (always set to checked, no toggle)
    checkbox.checked = true;
    
    // Update button in hidden grid
    const hiddenButton = checkbox.nextElementSibling;
    if (hiddenButton && hiddenButton.tagName === 'BUTTON') {
        hiddenButton.disabled = true;
        hiddenButton.classList.remove('btn-outline-danger');
        hiddenButton.classList.add('btn-danger');
        hiddenButton.innerHTML = '<i class="fas fa-check me-1"></i>Marked for removal';
    }
    
    // Update card opacity in hidden grid
    const card = checkbox.closest('.image-item');
    if (card) {
        card.style.opacity = '0.5';
    }
    
    // Remove from modal if it's open
    const modal = document.getElementById('currentProductImagesModal');
    if (modal && modal.classList.contains('show')) {
        const modalContainer = document.getElementById('currentProductImagesContainer');
        if (modalContainer) {
            // Find and remove the card in modal by image ID
            const modalCards = modalContainer.querySelectorAll('.card');
            modalCards.forEach(modalCard => {
                const modalButton = modalCard.querySelector('.modal-remove-btn[data-image-id="' + imageId + '"]');
                if (modalButton) {
                    // Find the parent column and remove it
                    const col = modalCard.closest('.col-md-3, .col-sm-4, .col-6');
                    if (col) {
                        col.remove();
                    }
                }
            });
            
            // Check if modal is now empty
            const remainingCards = modalContainer.querySelectorAll('.card');
            const emptyMessage = document.getElementById('currentProductImagesEmpty');
            if (remainingCards.length === 0) {
                modalContainer.style.display = 'none';
                if (emptyMessage) {
                    emptyMessage.style.display = 'block';
                }
            }
        }
    }
}

// Variant image input change handler (for variants table)
if (document.getElementById('variantsTableBody')) {
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('variant-image-input')) {
            // Update the row's image preview first
            const row = e.target.closest('tr');
            if (row) {
                const previewEl = row.querySelector('[data-variant-image-preview]');
                if (previewEl) {
                    const existingImages = [];
                    if (row.dataset && row.dataset.existingImages) {
                        try {
                            existingImages = JSON.parse(row.dataset.existingImages);
                        } catch (e) {
                            existingImages = [];
                        }
                    }
                    // Use updateVariantImagePreview if available from variants.blade.php
                    if (typeof updateVariantImagePreview === 'function') {
                        updateVariantImagePreview(previewEl, e.target.files, existingImages);
                    }
                }
            }
        }
    });
}
</script>

<style>
/* Small form label styling */
.form-label-sm {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

/* Image Upload Area */
.image-upload-area {
    position: relative;
}

.upload-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-placeholder:hover,
.image-upload-area.drag-over .upload-placeholder {
    background: #e7f3ff;
    border-color: #0d6efd;
    border-style: solid;
}

.cursor-pointer {
    cursor: pointer;
}

.image-item {
    transition: transform 0.2s;
}

.image-item:hover {
    transform: translateY(-2px);
}

.image-item img[draggable="true"] {
    cursor: move;
}

.image-item.sortable-ghost {
    opacity: 0.4;
}

.image-preview-card {
    transition: transform 0.2s;
}

.image-preview-card:hover {
    transform: scale(1.02);
}
</style>

