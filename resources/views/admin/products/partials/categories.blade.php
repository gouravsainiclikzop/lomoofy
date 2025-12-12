@php
    $existingBrandIds = isset($product) && $product->exists ? $product->brands->pluck('id')->toArray() : [];
    $selectedBrandIds = old('brand_ids', $existingBrandIds);
    if (empty($selectedBrandIds) && isset($otherBrandId)) {
        $selectedBrandIds = [$otherBrandId];
    }

    // Get selected category_ids (multiple category selection)
    $selectedCategoryIds = old('category_ids', []);
    if (empty($selectedCategoryIds) && isset($product) && $product->exists) {
        // Get all categories from pivot table
        $productCategories = $product->categories;
        if ($productCategories->count() > 0) {
            $selectedCategoryIds = $productCategories->pluck('id')->toArray();
        } else {
            // Fallback: get from category_id field if exists
            if ($product->category_id) {
                $selectedCategoryIds = [$product->category_id];
            }
        }
    }

    // Helper function to build category path
    function buildCategoryPath($category, $categoriesById) {
        $path = [];
        $current = $category;
        $maxDepth = 20; // Prevent infinite loops
        $depth = 0;
        
        while ($current && $depth < $maxDepth) {
            array_unshift($path, $current->name);
            if ($current->parent_id) {
                $current = $categoriesById->get($current->parent_id);
            } else {
                $current = null;
            }
            $depth++;
        }
        
        return implode(' > ', $path);
    }

    $categoriesById = $categories->keyBy('id');
    
    // Build hierarchical category list with paths
    $allCategoriesData = $categories->map(function ($category) use ($categoriesById) {
        $path = buildCategoryPath($category, $categoriesById);
        $depth = 0;
        $current = $category;
        while ($current && $current->parent_id) {
            $depth++;
            $current = $categoriesById->get($current->parent_id);
        }
        
        return [
            'id' => $category->id,
            'name' => $category->name,
            'path' => $path,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'depth' => $depth,
        ];
    })->values();
@endphp

{{-- Categories & Tags Section --}}
<div class="card mb-3" id="categoriesSection">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-tags me-2"></i>Categories & Tags
            </h6>  
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#categoriesInfoModal">
                <i class="fas fa-info-circle"></i>
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            {{-- Brands Selection --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="productBrands" class="form-label form-label-sm">
                        Select Brands <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm select2-multiple" id="productBrands" name="brand_ids[]" multiple required data-other-brand-id="{{ $otherBrandId ?? '' }}">
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" 
                                    {{ in_array($brand->id, $selectedBrandIds ?? []) ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Select one or more brands. First selected brand will be the primary brand.
                    </small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Category Selection (Hierarchical Tree) --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="productCategory" class="form-label form-label-sm">
                        Select Category <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm select2-multiple" id="productCategory" name="category_ids[]" multiple required data-placeholder="Select Categories">
                        @foreach($allCategoriesData->sortBy('path') as $category)
                            <option value="{{ $category['id'] }}" 
                                    data-path="{{ $category['path'] }}"
                                    data-depth="{{ $category['depth'] ?? 0 }}"
                                    {{ in_array($category['id'], $selectedCategoryIds ?? []) ? 'selected' : '' }}>
                                {{ $category['path'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Select one or more categories. The full path (e.g., Men → T-Shirt → Polos → Essential Polos) will be displayed for each selected category.
                    </small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Tags - Full Row --}}
            <div class="row g-3">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="productTags" class="form-label form-label-sm">
                            Product Tags
                        </label>
                        {{-- Hidden input to store comma-separated tags for form submission --}}
                        <input type="hidden" id="productTagsHidden" name="tags" 
                               value="{{ old('tags', $product->tags ?? '') }}">
                        {{-- Tags input field --}}
                        <input type="text" class="form-control form-control-sm" id="productTags" 
                               placeholder="Type and press Enter or comma to add tags" 
                               autocomplete="off">
                        {{-- Tags chips wrapper - displayed below input --}}
                        <div class="tags-chips-wrapper mt-2" id="tagsChipsWrapper">
                            {{-- Chips will be dynamically added here --}}
                        </div> 
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            {{-- Default Warehouse Selection --}}
            <div class="row g-3">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="productDefaultWarehouse" class="form-label form-label-sm">
                            <i class="fas fa-warehouse me-1"></i> Warehouse
                        </label>
                        <select class="form-select form-select-sm" id="productDefaultWarehouse" name="default_warehouse_id">
                            <option value="">Use Primary Warehouse{{ $defaultWarehouse ? ' (' . $defaultWarehouse->name . ')' : ' (None Set)' }}</option>
                            @foreach($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                        {{ ($selectedWarehouseId ?? null) == $warehouse->id ? 'selected' : '' }}
                                        {{ $warehouse->is_default ? 'data-is-primary="true"' : '' }}>
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                    @if($warehouse->is_default)
                                        - Primary
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Select a default warehouse for this product. If not set, the system's primary warehouse will be used.
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Categories & Tags Information Modal -->
<div class="modal fade" id="categoriesInfoModal" tabindex="-1" aria-labelledby="categoriesInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoriesInfoModalLabel">
                    <i class="fas fa-tags me-2"></i>Categories & Tags Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-building text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Brands:</strong> Select one or more brands for this product <small class="text-muted">(Required - determines available categories and primary brand)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-folder text-success me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Category:</strong> Select the deepest category in the hierarchy where this product belongs <small class="text-muted">(The full category path will be displayed in the dropdown)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-tags text-info me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Tags:</strong> Keywords for search and filtering <small class="text-muted">(Comma-separated keywords like "electronics, smartphone, latest")</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Select relevant brands</strong> that match your product's manufacturer or distributor <small class="text-muted">(Builds brand association and trust)</small></div>
                        <div><strong>Choose the deepest category</strong> in the hierarchy that best represents your product <small class="text-muted">(Helps customers find your product easily)</small></div>
                        <div><strong>Use descriptive tags</strong> that customers might search for <small class="text-muted">(Improves search functionality and SEO)</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<script id="allCategoriesJSON" type="application/json">
    {!! $allCategoriesData->toJson(JSON_UNESCAPED_UNICODE) !!}
</script>
<script id="selectedCategoryIdsJSON" type="application/json">
    {!! json_encode($selectedCategoryIds ?? []) !!}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoriesDataScript = document.getElementById('allCategoriesJSON');
    const ALL_CATEGORIES = categoriesDataScript ? JSON.parse(categoriesDataScript.textContent) : [];
    const selectedCategoryIdScript = document.getElementById('selectedCategoryIdJSON');
    const selectedCategoryId = selectedCategoryIdScript ? JSON.parse(selectedCategoryIdScript.textContent) : null;
    
    console.log('=== CATEGORIES SECTION WITH SELECT2 INITIALIZED ===');

    // Initialize Select2 for multiple selection (Brands)
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select options...',
        allowClear: true,
        width: '100%',
        closeOnSelect: false,
        templateResult: function(data) {
            if (!data.id) {
                return data.text;
            }
            
            // Add checkbox styling with data attribute for option value
            var $result = $(
                '<span class="select2-result-item" data-option-value="' + data.id + '">' +
                    '<input type="checkbox" class="me-2 select2-checkbox" data-option-value="' + data.id + '" ' + (data.selected ? 'checked' : '') + '>' +
                    data.text +
                '</span>'
            );
            
            return $result;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });
    
    // Initialize Select2 for multiple category selection with hierarchical display and checkboxes
    $('#productCategory').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Categories...',
        allowClear: true,
        width: '100%',
        closeOnSelect: false,
        templateResult: function(data) {
            if (!data.id) {
                return data.text;
            }
            
            // Add checkbox styling with data attribute for option value
            var $result = $('<span class="select2-result-item" data-option-value="' + data.id + '"></span>');
            
            // Display with indentation based on depth
            var category = ALL_CATEGORIES.find(function(cat) {
                return String(cat.id) === String(data.id);
            });
            
            var displayText = data.text;
            if (category) {
                var indent = '&nbsp;'.repeat(category.depth * 3);
                displayText = indent + (category.depth > 0 ? '└─ ' : '') + data.text;
            }
            
            $result.html(
                '<input type="checkbox" class="me-2 select2-checkbox" data-option-value="' + data.id + '" ' + (data.selected ? 'checked' : '') + '>' +
                displayText
            );
            
            return $result;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });
    
    // Update checkbox states for category select
    function updateCategoryCheckboxes() {
        var $select = $('#productCategory');
        var selectedValues = $select.val() || [];
        
        $select.next('.select2-container').find('.select2-results__option').each(function() {
            var $option = $(this);
            var $checkbox = $option.find('.select2-checkbox');
            
            if ($checkbox.length) {
                var optionValue = $checkbox.data('option-value');
                var isSelected = false;
                if (optionValue !== undefined && optionValue !== null) {
                    optionValue = String(optionValue);
                    for (var i = 0; i < selectedValues.length; i++) {
                        if (String(selectedValues[i]) === optionValue) {
                            isSelected = true;
                            break;
                        }
                    }
                }
                
                if (!isSelected) {
                    isSelected = $option.hasClass('select2-results__option--selected');
                }
                
                $checkbox.prop('checked', isSelected);
            }
        });
    }
    
    // Handle clicks on category Select2 option items to toggle checkboxes
    $(document).on('click', '#productCategory + .select2-container .select2-results__option', function(e) {
        var $option = $(this);
        var $checkbox = $option.find('.select2-checkbox');
        
        if ($checkbox.length && !$(e.target).is('input[type="checkbox"]')) {
            var $select = $('#productCategory');
            var selectedValues = $select.val() || [];
            var optionValue = $checkbox.data('option-value');
            
            var currentlySelected = false;
            if (optionValue !== undefined && optionValue !== null) {
                optionValue = String(optionValue);
                for (var i = 0; i < selectedValues.length; i++) {
                    if (String(selectedValues[i]) === optionValue) {
                        currentlySelected = true;
                        break;
                    }
                }
            }
            
            var newState = !currentlySelected;
            $checkbox.prop('checked', newState);
            
            setTimeout(function() {
                updateCategoryCheckboxes();
            }, 0);
            
            setTimeout(function() {
                updateCategoryCheckboxes();
            }, 50);
        }
    });
    
    // Update checkbox states when category selection changes
    $('#productCategory').on('select2:select select2:unselect', function(e) {
        var $select = $(this);
        setTimeout(function() {
            updateCategoryCheckboxes();
        }, 100);
    });
    
    // Update checkboxes when dropdown opens
    $('#productCategory').on('select2:open', function() {
        var $select = $(this);
        setTimeout(function() {
            updateCategoryCheckboxes();
        }, 150);
    });
    
    // Function to update checkbox states based on Select2 selection
    function updateSelect2Checkboxes($select) {
        var selectedValues = $select.val() || [];
        
        // Find all options in the dropdown
        $select.next('.select2-container').find('.select2-results__option').each(function() {
            var $option = $(this);
            var $checkbox = $option.find('.select2-checkbox');
            
            if ($checkbox.length) {
                // Get option value from data attribute
                var optionValue = $checkbox.data('option-value') || $option.find('.select2-result-item').data('option-value');
                
                // Check if this value is in the selected values array
                var isSelected = false;
                if (optionValue !== undefined && optionValue !== null) {
                    // Convert to string for comparison
                    optionValue = String(optionValue);
                    for (var i = 0; i < selectedValues.length; i++) {
                        if (String(selectedValues[i]) === optionValue) {
                            isSelected = true;
                            break;
                        }
                    }
                }
                
                // Also check the selected class as fallback
                if (!isSelected) {
                    isSelected = $option.hasClass('select2-results__option--selected');
                }
                
                $checkbox.prop('checked', isSelected);
            }
        });
    }
    
    // Handle clicks on Select2 option items to toggle checkboxes
    $(document).on('click', '.select2-results__option', function(e) {
        var $option = $(this);
        var $checkbox = $option.find('.select2-checkbox');
        
        // Don't interfere if clicking directly on checkbox
        if ($checkbox.length && !$(e.target).is('input[type="checkbox"]')) {
            // Find the parent select element
            var $select = $option.closest('.select2-container').prev('select');
            var selectedValues = $select.val() || [];
            
            // Get the option value
            var optionValue = $checkbox.data('option-value') || $option.find('.select2-result-item').data('option-value');
            
            // Determine current selection state
            var currentlySelected = false;
            if (optionValue !== undefined && optionValue !== null) {
                optionValue = String(optionValue);
                for (var i = 0; i < selectedValues.length; i++) {
                    if (String(selectedValues[i]) === optionValue) {
                        currentlySelected = true;
                        break;
                    }
                }
            }
            
            // Toggle checkbox immediately to the new state (opposite of current)
            var newState = !currentlySelected;
            $checkbox.prop('checked', newState);
            
            // Use setTimeout with 0 to ensure it runs after current execution
            setTimeout(function() {
                // Update again after Select2 processes to ensure accuracy
                updateSelect2Checkboxes($select);
            }, 0);
            
            // Also update after a short delay to catch any async updates
            setTimeout(function() {
                updateSelect2Checkboxes($select);
            }, 50);
        }
    });
    
    // Update checkbox states when Select2 selection changes
    $('.select2-multiple').on('select2:select select2:unselect', function(e) {
        var $select = $(this);
        setTimeout(function() {
            updateSelect2Checkboxes($select);
        }, 100);
    });
    
    // Also update checkboxes when dropdown opens to show current state
    $('.select2-multiple').on('select2:open', function() {
        var $select = $(this);
        setTimeout(function() {
            updateSelect2Checkboxes($select);
        }, 150);
    });
    
    // Set selected categories if editing
    const selectedCategoryIdsScript = document.getElementById('selectedCategoryIdsJSON');
    const selectedCategoryIds = selectedCategoryIdsScript ? JSON.parse(selectedCategoryIdsScript.textContent) : [];
    if (selectedCategoryIds && selectedCategoryIds.length > 0) {
        $('#productCategory').val(selectedCategoryIds).trigger('change');
        // Load attributes for all selected categories
        if (selectedCategoryIds.length > 0) {
            loadAttributesByCategories(selectedCategoryIds);
        }
    } else {
        // If no categories are selected, load all attributes by default
        loadAllAttributes();
    }
    
    // Load attributes when category changes (combine attributes from all selected categories)
    let categoryChangeTimeout;
    $('#productCategory').on('change', function() {
        // Debounce to prevent multiple rapid calls
        clearTimeout(categoryChangeTimeout);
        categoryChangeTimeout = setTimeout(function() {
            const categoryIds = $('#productCategory').val();
            if (categoryIds && categoryIds.length > 0 && Array.isArray(categoryIds)) {
                // Load attributes for all selected categories and combine them
                loadAttributesByCategories(categoryIds);
            } else {
                clearAttributes();
            }
        }, 300);
    });
    
    console.log('=== SELECT2 INITIALIZATION COMPLETE ===');
    
    // Tags Input Implementation
    (function() {
        const tagsInput = document.getElementById('productTags');
        const tagsChipsWrapper = document.getElementById('tagsChipsWrapper');
        const tagsHiddenInput = document.getElementById('productTagsHidden');
        let tagsArray = [];
        
        // Load existing tags from hidden input on page load
        function loadExistingTags() {
            const existingTags = tagsHiddenInput.value;
            if (existingTags && existingTags.trim()) {
                tagsArray = existingTags.split(',').map(tag => tag.trim()).filter(tag => tag);
                renderTags();
            }
        }
        
        // Render all tags as chips
        function renderTags() {
            tagsChipsWrapper.innerHTML = '';
            tagsArray.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.innerHTML = `
                    <span class="tag-chip-text">${escapeHtml(tag)}</span>
                    <button type="button" class="tag-chip-remove" data-index="${index}" aria-label="Remove tag">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                tagsChipsWrapper.appendChild(chip);
            });
            
            // Update hidden input
            tagsHiddenInput.value = tagsArray.join(',');
        }
        
        // Add a new tag
        function addTag(tagText) {
            const trimmedTag = tagText.trim();
            if (trimmedTag && !tagsArray.includes(trimmedTag)) {
                tagsArray.push(trimmedTag);
                renderTags();
                tagsInput.value = '';
            }
        }
        
        // Remove a tag
        function removeTag(index) {
            tagsArray.splice(index, 1);
            renderTags();
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Handle input events
        if (tagsInput) {
            tagsInput.addEventListener('keydown', function(e) {
                // Add tag on Enter or comma
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const tagText = this.value.trim();
                    if (tagText) {
                        addTag(tagText);
                    }
                }
                // Remove last tag on Backspace if input is empty
                else if (e.key === 'Backspace' && this.value === '' && tagsArray.length > 0) {
                    removeTag(tagsArray.length - 1);
                }
            });
            
            // Also handle paste events to split by commas
            tagsInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const pastedText = this.value;
                    if (pastedText.includes(',')) {
                        const tags = pastedText.split(',').map(t => t.trim()).filter(t => t);
                        tags.forEach(tag => {
                            if (!tagsArray.includes(tag)) {
                                tagsArray.push(tag);
                            }
                        });
                        renderTags();
                        this.value = '';
                    }
                }, 0);
            });
        }
        
        // Handle chip remove button clicks
        if (tagsChipsWrapper) {
            tagsChipsWrapper.addEventListener('click', function(e) {
                if (e.target.closest('.tag-chip-remove')) {
                    const button = e.target.closest('.tag-chip-remove');
                    const index = parseInt(button.getAttribute('data-index'));
                    if (!isNaN(index)) {
                        removeTag(index);
                    }
                }
            });
        }
        
        // Load existing tags on page load
        loadExistingTags();
    })();

    // Load attributes by multiple categories (combines attributes from all)
    function loadAttributesByCategories(categoryIds) {
        if (!categoryIds || !Array.isArray(categoryIds) || categoryIds.length === 0) {
            clearAttributes();
            return;
        }

        // If only one category, use the single category function
        if (categoryIds.length === 1) {
            loadAttributesByCategory(categoryIds[0]);
            return;
        }

        // Load attributes for all categories in parallel using jQuery's $.when()
        const ajaxCalls = categoryIds.map(categoryId => {
            return $.ajax({
                url: '{{ route("products.attributes-by-category") }}',
                type: 'GET',
                data: { category_id: categoryId }
            });
        });

        $.when.apply($, ajaxCalls)
            .done(function() {
                // Get all responses (arguments will be [data, textStatus, jqXHR] for each call)
                const responses = [];
                for (let i = 0; i < arguments.length; i++) {
                    // Each argument is [data, textStatus, jqXHR]
                    if (Array.isArray(arguments[i]) && arguments[i].length > 0) {
                        responses.push(arguments[i][0]);
                    } else {
                        responses.push(arguments[i]);
                    }
                }
                
                // Combine all variant attributes (remove duplicates by ID)
                const allVariantAttributes = [];
                const attributeIdsSeen = new Set();
                
                responses.forEach(function(response) {
                    if (response && response.success && Array.isArray(response.variant_attributes)) {
                        response.variant_attributes.forEach(function(attr) {
                            if (!attributeIdsSeen.has(attr.id)) {
                                attributeIdsSeen.add(attr.id);
                                allVariantAttributes.push(attr);
                            }
                        });
                    }
                });

                // Combine all static attributes (remove duplicates by ID)
                const allStaticAttributes = [];
                const staticAttributeIdsSeen = new Set();
                
                responses.forEach(function(response) {
                    if (response && response.success && Array.isArray(response.static_attributes)) {
                        response.static_attributes.forEach(function(attr) {
                            if (!staticAttributeIdsSeen.has(attr.id)) {
                                staticAttributeIdsSeen.add(attr.id);
                                allStaticAttributes.push(attr);
                            }
                        });
                    }
                });

                console.log('Combined attributes from', categoryIds.length, 'categories:', {
                    variant_attributes: allVariantAttributes.length,
                    static_attributes: allStaticAttributes.length
                });

                // Update variant attributes in variants tab (checkboxes)
                updateVariantAttributes(allVariantAttributes);
                
                // Update variant attributes checkbox list (if function exists)
                if (typeof window.updateVariantAttributesSelect2 === 'function') {
                    window.updateVariantAttributesSelect2(allVariantAttributes);
                }
                
                // Update static attributes in product attributes tab
                updateStaticAttributes(allStaticAttributes);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error loading attributes by categories:', {
                    status: textStatus,
                    error: errorThrown,
                    responseText: jqXHR.responseText,
                    statusCode: jqXHR.status
                });
                
                // Try to parse error response
                let errorMessage = 'Failed to load attributes for selected categories';
                let errorTitle = 'Error Loading Attributes';
                
                try {
                    if (jqXHR.responseText) {
                        const errorResponse = JSON.parse(jqXHR.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    }
                } catch (e) {
                    // If response is not JSON, use default message based on status
                    if (jqXHR.status === 404) {
                        errorMessage = 'One or more categories not found';
                    } else if (jqXHR.status === 500) {
                        errorMessage = 'Server error while loading attributes';
                    }
                }
                
                if (typeof showUserFriendlyAlert === 'function') {
                    showUserFriendlyAlert(errorTitle, errorMessage, 'error');
                } else {
                    alert(errorTitle + ': ' + errorMessage);
                }
                
                clearAttributes();
            });
    }

    // Load attributes by category
    function loadAttributesByCategory(categoryId) {
        if (!categoryId) {
            clearAttributes();
            return;
        }

        $.ajax({
            url: '{{ route("products.attributes-by-category") }}',
            type: 'GET',
            data: { category_id: categoryId },
            success: function(response) {
                if (response.success) {
                    // Update variant attributes in variants tab (checkboxes)
                    updateVariantAttributes(response.variant_attributes);
                    
                    // Update variant attributes Select2 dropdown (if function exists)
                    if (typeof window.updateVariantAttributesSelect2 === 'function') {
                        window.updateVariantAttributesSelect2(response.variant_attributes);
                    }
                    
                    // Update static attributes in product attributes tab
                    updateStaticAttributes(response.static_attributes);
                } else {
                    console.warn('Attributes loaded but success flag is false:', response);
                    clearAttributes();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading attributes by category:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                // Try to parse error response
                let errorMessage = 'Failed to load attributes for this category';
                let errorTitle = 'Error Loading Attributes';
                
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    }
                } catch (e) {
                    // If response is not JSON, use default message based on status
                    if (xhr.status === 404) {
                        errorMessage = 'The attributes endpoint was not found. Please contact support.';
                        errorTitle = 'Route Not Found';
                    } else if (xhr.status === 500) {
                        errorMessage = 'A server error occurred while loading attributes. Please try again or contact support.';
                        errorTitle = 'Server Error';
                    } else if (xhr.status === 0) {
                        errorMessage = 'Unable to connect to the server. Please check your internet connection.';
                        errorTitle = 'Connection Error';
                    }
                }
                
                // Show user-friendly alert if function exists
                if (typeof showUserFriendlyAlert === 'function') {
                    showUserFriendlyAlert(errorTitle, errorMessage, 'error');
                } else {
                    // Fallback to console and alert
                    console.error('Error details:', errorMessage);
                    alert(errorTitle + ': ' + errorMessage);
                }
                
                clearAttributes();
            }
        });
    }

    // Update variant attributes in variants tab
    function updateVariantAttributes(variantAttributes) {
        const availableAttributesContainer = document.getElementById('availableAttributes');
        const selectedAttributesContainer = document.getElementById('selectedAttributes');
        if (!availableAttributesContainer) return;

        // Clear existing attributes
        availableAttributesContainer.innerHTML = '';

        // Clear selected attributes display (but don't clear the selectedAttributeIds array - let the variants script handle that)
        if (selectedAttributesContainer) {
            selectedAttributesContainer.innerHTML = '<p class="text-muted text-center">No attributes selected</p>';
        }

        if (variantAttributes.length === 0) {
            availableAttributesContainer.innerHTML = '<p class="text-muted text-center">No variant attributes available for this category</p>';
            return;
        }

        // Add variant attributes (all unchecked by default)
        variantAttributes.forEach(function(attr) {
            const attrDiv = document.createElement('div');
            attrDiv.className = 'form-check attribute-item';
            attrDiv.setAttribute('data-attribute-id', attr.id);
            attrDiv.setAttribute('data-attribute-type', attr.type);
            
            attrDiv.innerHTML = `
                <input class="form-check-input" type="checkbox" 
                       id="attr_${attr.id}" 
                       value="${attr.id}">
                <label class="form-check-label" for="attr_${attr.id}">
                    ${escapeHtml(attr.name)}
                    <small class="text-muted d-block">${escapeHtml(attr.description || '')}</small>
                    <small class="text-info d-block">Type: ${escapeHtml(attr.type)}</small>
                </label>
            `;
            
            availableAttributesContainer.appendChild(attrDiv);
        });
    }

    // Update static attributes in product attributes tab
    function updateStaticAttributes(staticAttributes) {
        const staticAttributesList = document.getElementById('staticAttributesList');
        const attributesInfoAlert = document.getElementById('attributesInfoAlert');
        
        if (!staticAttributesList) return;

        // Hide info alert
        if (attributesInfoAlert) {
            attributesInfoAlert.style.display = 'none';
        }

        // Clear existing attributes
        staticAttributesList.innerHTML = '';

        if (staticAttributes.length === 0) {
            staticAttributesList.innerHTML = '<p class="text-muted text-center">No static attributes available for this category</p>';
            staticAttributesList.style.display = 'block';
            return;
        }

        // Add static attributes
        staticAttributes.forEach(function(attr) {
            const attrGroup = document.createElement('div');
            attrGroup.className = 'attribute-field-group' + (attr.is_required ? ' required' : '');
            
            let inputHtml = '';
            const fieldName = `static_attributes[${attr.id}]`;
            const fieldId = `static_attr_${attr.id}`;
            
            // Generate input based on attribute type
            switch(attr.type) {
                case 'select':
                    inputHtml = `<select class="form-select" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''}>
                        <option value="">-- Select ${escapeHtml(attr.name)} --</option>`;
                    if (attr.values && attr.values.length > 0) {
                        attr.values.forEach(function(value) {
                            inputHtml += `<option value="${value.id}">${escapeHtml(value.value)}</option>`;
                        });
                    }
                    inputHtml += `</select>`;
                    break;
                    
                case 'multiselect':
                    inputHtml = `<select class="form-select select2-multiple" id="${fieldId}" name="${fieldName}[]" multiple ${attr.is_required ? 'required' : ''}>
                        <option value="">-- Select ${escapeHtml(attr.name)} --</option>`;
                    if (attr.values && attr.values.length > 0) {
                        attr.values.forEach(function(value) {
                            inputHtml += `<option value="${value.id}">${escapeHtml(value.value)}</option>`;
                        });
                    }
                    inputHtml += `</select>`;
                    break;
                    
                case 'boolean':
                    inputHtml = `<select class="form-select" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''}>
                        <option value="">-- Select --</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>`;
                    break;
                    
                case 'number':
                    inputHtml = `<input type="number" class="form-control" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''} step="any">`;
                    break;
                    
                case 'date':
                    inputHtml = `<input type="date" class="form-control" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''}>`;
                    break;
                    
                case 'file':
                    inputHtml = `<input type="file" class="form-control" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''}>`;
                    break;
                    
                case 'text':
                default:
                    inputHtml = `<input type="text" class="form-control" id="${fieldId}" name="${fieldName}" ${attr.is_required ? 'required' : ''} placeholder="Enter ${escapeHtml(attr.name)}">`;
                    break;
            }
            
            attrGroup.innerHTML = `
                <label for="${fieldId}">
                    ${escapeHtml(attr.name)}
                    ${attr.is_required ? '<span class="text-danger">*</span>' : ''}
                </label>
                ${attr.description ? `<div class="attribute-description">${escapeHtml(attr.description)}</div>` : ''}
                ${inputHtml}
                <input type="hidden" name="static_attributes_meta[${attr.id}][type]" value="${attr.type}">
            `;
            
            staticAttributesList.appendChild(attrGroup);
        });

        staticAttributesList.style.display = 'block';
        
        // Initialize Select2 for multiselect attributes
        setTimeout(function() {
            staticAttributesList.querySelectorAll('.select2-multiple').forEach(function(select) {
                if (!$(select).hasClass('select2-hidden-accessible')) {
                    $(select).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Select options...'
                    });
                }
            });
        }, 100);
    }

    // Clear attributes
    function clearAttributes() {
        // Load all attributes when no categories are selected
        loadAllAttributes();
    }
    
    // Load all attributes (variant attributes only for variants section)
    function loadAllAttributes() {
        $.ajax({
            url: '{{ route("products.attributes") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.attributes) {
                    // Filter only variant attributes (is_variation = true)
                    const variantAttributes = response.attributes.filter(function(attr) {
                        return attr.is_variation === true || attr.is_variation === 1 || attr.is_variation === '1';
                    }).map(function(attr) {
                        return {
                            id: attr.id,
                            name: attr.name || '',
                            slug: attr.slug || '',
                            type: attr.type || 'text',
                            description: attr.description || '',
                            is_required: attr.is_required || false,
                            is_visible: attr.is_visible !== false,
                            sort_order: attr.sort_order || 0,
                            values: (attr.values || []).map(function(value) {
                                return {
                                    id: value.id || null,
                                    value: value.value || '',
                                    color_code: value.color_code || null,
                                    image_path: value.image_path || null,
                                    sort_order: value.sort_order || 0,
                                };
                            })
                        };
                    });
                    
                    // Filter static attributes
                    const staticAttributes = response.attributes.filter(function(attr) {
                        return attr.is_variation === false || attr.is_variation === 0 || attr.is_variation === '0';
                    }).map(function(attr) {
                        return {
                            id: attr.id,
                            name: attr.name || '',
                            slug: attr.slug || '',
                            type: attr.type || 'text',
                            description: attr.description || '',
                            is_required: attr.is_required || false,
                            is_visible: attr.is_visible !== false,
                            sort_order: attr.sort_order || 0,
                            values: (attr.values || []).map(function(value) {
                                return {
                                    id: value.id || null,
                                    value: value.value || '',
                                    color_code: value.color_code || null,
                                    image_path: value.image_path || null,
                                    sort_order: value.sort_order || 0,
                                };
                            })
                        };
                    });
                    
                    // Update variant attributes in variants tab (checkboxes)
                    updateVariantAttributes(variantAttributes);
                    
                    // Update variant attributes checkbox list (if function exists)
                    if (typeof window.updateVariantAttributesSelect2 === 'function') {
                        window.updateVariantAttributesSelect2(variantAttributes);
                    }
                    
                    // Update static attributes in product attributes tab
                    updateStaticAttributes(staticAttributes);
                } else {
                    console.warn('Failed to load all attributes:', response);
                    // Fallback: show empty state
                    const staticAttributesList = document.getElementById('staticAttributesList');
                    const attributesInfoAlert = document.getElementById('attributesInfoAlert');
                    
                    if (staticAttributesList) {
                        staticAttributesList.innerHTML = '';
                        staticAttributesList.style.display = 'none';
                    }
                    
                    if (attributesInfoAlert) {
                        attributesInfoAlert.style.display = 'block';
                    }
                    
                    const availableAttributesContainer = document.getElementById('availableAttributes');
                    if (availableAttributesContainer) {
                        availableAttributesContainer.innerHTML = '<p class="text-muted text-center">No attributes available</p>';
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading all attributes:', error);
                // Fallback: show empty state
                const staticAttributesList = document.getElementById('staticAttributesList');
                const attributesInfoAlert = document.getElementById('attributesInfoAlert');
                
                if (staticAttributesList) {
                    staticAttributesList.innerHTML = '';
                    staticAttributesList.style.display = 'none';
                }
                
                if (attributesInfoAlert) {
                    attributesInfoAlert.style.display = 'block';
                }
                
                const availableAttributesContainer = document.getElementById('availableAttributes');
                if (availableAttributesContainer) {
                    availableAttributesContainer.innerHTML = '<p class="text-muted text-center">Error loading attributes</p>';
                }
            }
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

});
</script>

<style>
 
 
 

.select2-container--bootstrap-5 .select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

.select2-container--bootstrap-5 .select2-results__option {
    padding: 0.5rem 0.75rem;
}

.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #f5c000;
    color: white;
}

.select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
}

/* Custom checkbox styling in dropdown */
.select2-result-item input[type="checkbox"] {
    margin-right: 0.5rem;
    cursor: pointer;
}

.select2-result-item input[type="checkbox"]:checked {
    accent-color: #f5c000;
}

/* Ensure checkbox is visible and styled properly */
.select2-results__option .select2-checkbox {
    pointer-events: auto;
    cursor: pointer;
}

/* Small form label styling */
.form-label-sm {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

/* Select2 small sizing - scoped to categories section */
#categoriesSection .select2-container--bootstrap-5 .select2-selection--single,
#categoriesSection .select2-container--bootstrap-5 .select2-selection--multiple {
    min-height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

#categoriesSection .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
    font-size: 0.75rem;
    padding: 0.125rem 0.375rem;
    margin: 0.125rem;
}

#categoriesSection .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
    padding: 0;
}

/* Tags Input Styling */
.tags-chips-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    min-height: 1.5rem;
}

.tag-chip {
    display: inline-flex;
    align-items: center;
    background-color: #f5c000;
    color: white;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    gap: 0.25rem;
    line-height: 1.4;
}

.tag-chip-text {
    white-space: nowrap;
}

.tag-chip-remove {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
    margin: 0;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
    opacity: 0.8;
}

.tag-chip-remove:hover {
    background-color: rgba(255, 255, 255, 0.2);
    opacity: 1;
}

.tag-chip-remove i {
    font-size: 0.625rem;
    line-height: 1;
}

/* Radio button labels smaller */
.form-check-label {
    font-size: 0.875rem;
}

.tag-chip-remove:focus {
    outline: 2px solid rgba(255, 255, 255, 0.5);
    outline-offset: 2px;
}
</style>