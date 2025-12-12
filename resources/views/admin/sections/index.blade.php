@extends('layouts.admin')

@section('title', 'Page Sections Management')

@section('content')
<div class="container-fluid">
    <div class="py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div class="flex-grow-1">
                <h4 class="m-0">
                    <span id="pageNamePrefix" style="display: none;"></span>
                    <span id="headerTitle">Page Sections Management</span>
                </h4>
                <small class="text-muted">Manage sections and variants for pages</small>
                    </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- Page Selector -->
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small">Page:</label>
                    <select class="form-select form-select-sm" id="pageSelector" style="min-width: 200px;">
                        <option value="">Loading pages...</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm" id="initializeHomeBtn" title="Initialize default sections for home page">
                    <i class='bx bx-check-square me-1'></i> Initialize Home Sections
                            </button>
                <button type="button" class="btn btn-primary btn-sm" id="saveSortOrderBtn" style="display:none;">
                    <i class='bx bx-save me-1'></i> Save Order
                            </button>
                        </div>
                    </div>

        <!-- Sections List -->
                    <div class="card">
            <div class="card-body p-3">
                            <div id="sectionsContainer">
                    <!-- Module Information Toggle -->
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-link text-info p-0 module-info-toggle" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#moduleInfo"
                                aria-expanded="false"
                                aria-controls="moduleInfo"
                                title="Module information">
                            <i class='bx bx-info-circle' style="font-size: 1.5rem;"></i>
                        </button>
                    </div>
                    
                    <!-- Module Information Panel -->
                    <div class="collapse mb-3" id="moduleInfo">
                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-start">
                                <i class='bx bx-info-circle me-2 mt-1' style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-2 fw-semibold">Page Sections Management Module</h6>
                                    <p class="mb-2 small">This module allows you to manage and customize page sections for your website. You can organize different sections like banners, product displays, discounts, and more across various pages.</p>
                                    <div class="mb-0">
                                        <strong class="small">How to use this module:</strong>
                                        <ul class="small mb-0 mt-1">
                                            <li><strong>Select a page:</strong> Choose a page from the dropdown menu above to manage its sections</li>
                                            <li><strong>Initialize sections:</strong> Click "Initialize Home Sections" to create default sections for the home page (if not already created)</li>
                                            <li><strong>Activate variants:</strong> Each section has multiple variants - toggle ON the variant you want to display (only one variant can be active per section)</li>
                                            <li><strong>Upload images:</strong> Hover over variant images to upload preview images that help identify the variant</li>
                                            <li><strong>Reorder sections:</strong> Drag sections using the menu icon to change their display order on the page</li>
                                            <li><strong>Save order:</strong> After rearranging sections, click "Save Order" to apply the changes</li>
                                            <li><strong>Section types:</strong> Manage different section types like Popular Products, New Arrivals, Banners, Discounts, Brand Logos, Blogs, and Service Highlights</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center py-5 text-muted" id="emptyState">
                        <i class='bx bx-file' style='font-size: 3rem;'></i>
                        <p class="mt-2">Please select a page to manage its sections</p>
                                </div>
                                <div id="sectionsList"></div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
@endsection

@push('styles')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    .section-group {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
        cursor: move;
    }
    
    .section-group:hover {
        border-color: #4f46e5;
        box-shadow: 0 1px 4px rgba(79, 70, 229, 0.1);
    }
    
    .section-group.sortable-ghost {
        opacity: 0.4;
        background: #f3f4f6;
    }
    
    .section-group.sortable-drag {
        opacity: 0.8;
    }
    
    .section-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .section-group-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2937;
    }
    
    .drag-handle {
        cursor: grab;
        color: #9ca3af;
        font-size: 1rem;
    }
    
    .drag-handle:active {
        cursor: grabbing;
    }
    
    .variants-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .variant-item {
        flex: 1;
        min-width: 180px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 0.5rem;
        transition: all 0.2s;
        position: relative;
    }
    
    .variant-item.active {
        border-color: #10b981;
        background: #ecfdf5;
    }
    
    .variant-item.inactive {
        opacity: 0.6;
    }
    
    .variant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }
    
    .variant-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
    }
    
    .variant-toggle {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }
    
    .variant-badge {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        font-size: 0.65rem;
        padding: 0.15rem 0.35rem;
    }
    
    .variant-image {
        width: 100%;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 0.25rem;
        border: 1px solid #e5e7eb;
    }
    
    .variant-content {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
        max-height: 30px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .no-image-placeholder {
        width: 100%;
        height: 60px;
        background: #f3f4f6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }
    
    .variant-content code {
        font-size: 0.7rem;
    }
    
    .variant-image-container {
        position: relative;
        margin-bottom: 0.25rem;
    }
    
    .variant-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 4px;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }
    
    .variant-image-container:hover .variant-image-overlay {
        display: flex;
    }
    
    .variant-image-btn {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 4px;
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .variant-image-btn:hover {
        background: #fff;
        transform: scale(1.05);
    }
    
    .variant-image-upload-input {
        display: none;
    }
    
    .module-info-toggle {
        text-decoration: none !important;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .module-info-toggle:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
    
    .module-info-toggle i {
        vertical-align: middle;
    }
    
    #moduleInfo {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let sections = [];
    let pages = [];
    let sortable = null;
    let currentPageId = null;

    // Load pages on start
    loadPages();

    function loadPages() {
        $.ajax({
            url: '{{ route("sections.pages") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    pages = response.pages;
                    renderPageSelector();
                    // Auto-select home page if available
                    const homePage = pages.find(p => p.url === '/' || p.name.toLowerCase().includes('home'));
                    if (homePage) {
                        $('#pageSelector').val(homePage.id).trigger('change');
                    } else if (pages.length > 0) {
                        $('#pageSelector').val(pages[0].id).trigger('change');
                    }
                }
            }
        });
    }

    function renderPageSelector() {
        const selector = $('#pageSelector');
        selector.empty();

        if (pages.length === 0) {
            selector.append('<option value="">No pages available</option>');
            return;
        }

        pages.forEach(function(page) {
            const selected = currentPageId === page.id ? 'selected' : '';
            selector.append(`<option value="${page.id}" ${selected}>${page.name} (${page.url})</option>`);
        });
    }

    // Page selector change
    $('#pageSelector').on('change', function() {
        const pageId = $(this).val();
        if (pageId) {
            loadPageSections(pageId);
        } else {
            $('#pageNamePrefix').hide();
            $('#headerTitle').text('Page Sections Management');
            $('#sectionsList').empty();
            $('#emptyState').html(`
                <i class='bx bx-file' style='font-size: 3rem;'></i>
                <p class="mt-2">Please select a page to manage its sections</p>
            `).show();
            $('#saveSortOrderBtn').hide();
        }
    });

    function loadPageSections(pageId) {
        currentPageId = pageId;
        
        // Update page name prefix in header
        const selectedPage = pages.find(p => p.id == pageId); 
        if (selectedPage) {
            $('#pageNamePrefix').text(selectedPage.name + ' - ').show();
            $('#headerTitle').text('Sections');
        } else {
            $('#pageNamePrefix').hide();
            $('#headerTitle').text('Page Sections Management');
        }
        
        $('#emptyState').html(`
            <i class='bx bx-loader-circle bx-spin' style='font-size: 3rem;'></i>
            <p class="mt-2">Loading sections...</p>
        `).show();
        $('#sectionsList').empty();
        $('#saveSortOrderBtn').hide();

        $.ajax({
            url: '{{ route("sections.page") }}',
            type: 'GET',
            data: { page_id: pageId },
            success: function(response) {
                if (response.success) {
                    sections = response.sections;
                    renderSections();
                } else {
                    $('#emptyState').html(`
                        <i class='bx bx-error-circle' style='font-size: 3rem; color: #ef4444;'></i>
                        <p class="mt-2 text-danger">${response.message || 'Error loading sections'}</p>
                    `);
                }
            },
            error: function() {
                $('#emptyState').html(`
                    <i class='bx bx-error-circle' style='font-size: 3rem; color: #ef4444;'></i>
                    <p class="mt-2 text-danger">Failed to load sections. Please refresh the page.</p>
                `);
            }
        });
    }

    function renderSections() {
        const container = $('#sectionsList');
        container.empty();

        if (sections.length === 0) {
            $('#emptyState').html(`
                <i class='bx bx-inbox' style='font-size: 3rem;'></i>
                <p class="mt-2">No sections found for this page.</p>
            `).show();
            $('#saveSortOrderBtn').hide();
            return;
        }

        $('#emptyState').hide();
        $('#saveSortOrderBtn').show();

        // Get selected page name for prefix
        const selectedPage = pages.find(p => p.id == currentPageId);
        const pageNamePrefix = selectedPage ? selectedPage.name + ' - ' : '';

        sections.forEach(function(sectionGroup, index) {
            const variantsHtml = sectionGroup.variants.map(function(variant) {
                const activeClass = variant.is_active ? 'active' : 'inactive';
                const badgeClass = variant.is_active ? 'bg-success' : 'bg-secondary';
                const imageHtml = variant.image_url ? 
                    `<img src="${variant.image_url}" class="variant-image" alt="Variant ${variant.variation_number || ''}">` :
                    `<div class="no-image-placeholder">
                        <i class='bx bx-image' style='font-size: 1rem;'></i>
                </div>`;

                const variantName = variant.variation_number ? 
                    `Variant ${variant.variation_number}` : 
                    'Default';

                return `
                    <div class="variant-item ${activeClass}" data-variant-id="${variant.id}">
                        <span class="badge ${badgeClass} variant-badge">${variant.is_active ? 'Active' : 'Inactive'}</span>
                        <div class="variant-image-container">
                            ${imageHtml}
                            <div class="variant-image-overlay">
                                <button type="button" class="variant-image-btn" onclick="document.getElementById('imageUpload${variant.id}').click()">
                                    <i class='bx bx-upload'></i> Upload
                                </button>
                                ${variant.image_url ? `<button type="button" class="variant-image-btn" onclick="removeVariantImage(${variant.id})">
                                    <i class='bx bx-trash'></i> Remove
                                </button>` : ''}
                            </div>
                            <input type="file" id="imageUpload${variant.id}" class="variant-image-upload-input" accept="image/*" onchange="uploadVariantImage(${variant.id}, this)">
                        </div>
                        <div class="variant-header">
                            <div class="variant-name">${variantName}</div>
                            <div class="variant-toggle">
                                <label class="form-check form-switch mb-0">
                                    <input class="form-check-input variant-switch" 
                                           type="checkbox" 
                                           data-variant-id="${variant.id}"
                                           ${variant.is_active ? 'checked' : ''}>
                                    <span class="form-check-label" style="font-size: 0.75rem;">${variant.is_active ? 'ON' : 'OFF'}</span>
                                </label>
                            </div>
                        </div>
                        <div class="variant-content">
                            <code>${variant.section_id}</code>
                        </div>
                    </div>
                `;
            }).join('');

            const sectionHtml = `
                <div class="section-group" data-base-name="${sectionGroup.base_name}" data-sort-order="${index}">
                    <div class="section-group-header">
                        <div class="section-group-title">
                            <i class='bx bx-menu drag-handle'></i>
                            <span>${pageNamePrefix}${sectionGroup.display_name} section</span>
                            <span class="badge bg-primary" style="font-size: 0.7rem;">${sectionGroup.variants.length} variant${sectionGroup.variants.length !== 1 ? 's' : ''}</span>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Order: <strong>${index + 1}</strong>
                        </div>
                        </div>
                    <div class="variants-container">
                        ${variantsHtml}
                    </div>
                </div>
            `;
            container.append(sectionHtml);
        });

        initSortable();
        initVariantToggles();
    }

    function initSortable() {
        const el = document.getElementById('sectionsList');
        if (!el) return;

        if (sortable) sortable.destroy();

        sortable = Sortable.create(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                $('.section-group').each(function(index) {
                    $(this).find('.text-muted strong').text(index + 1);
                    $(this).attr('data-sort-order', index);
                });
            }
        });
    }

    function initVariantToggles() {
        $('.variant-switch').off('change').on('change', function() {
            const variantId = $(this).data('variant-id');
            const isActive = $(this).is(':checked');
            const variantItem = $(this).closest('.variant-item');
            const switchLabel = $(this).siblings('.form-check-label');

            // Disable toggle while processing
            $(this).prop('disabled', true);

        $.ajax({
                url: '{{ route("sections.toggleVariant") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                    id: variantId,
                    is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                        // Update UI
                        if (isActive) {
                            variantItem.removeClass('inactive').addClass('active');
                            variantItem.find('.variant-badge').removeClass('bg-secondary').addClass('bg-success').text('Active');
                            switchLabel.text('ON');
                            
                            // Deactivate other variants in the same section group
                            const baseName = variantItem.closest('.section-group').data('base-name');
                            variantItem.closest('.section-group').find('.variant-item').not(variantItem).each(function() {
                                $(this).removeClass('active').addClass('inactive');
                                $(this).find('.variant-switch').prop('checked', false);
                                $(this).find('.variant-badge').removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                                $(this).find('.form-check-label').text('OFF');
                            });
                        } else {
                            variantItem.removeClass('active').addClass('inactive');
                            variantItem.find('.variant-badge').removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                            switchLabel.text('OFF');
                        }
                        
                    showToast('success', response.message);
                    }
                },
                error: function(xhr) {
                    // Revert toggle state
                    $(this).prop('checked', !isActive);
                    const errorMsg = xhr.responseJSON?.message || 'Failed to update variant status';
                    showToast('error', errorMsg);
                },
                complete: function() {
                    $(this).prop('disabled', false);
                }.bind(this)
        });
    });
    }

    // Initialize Home Page Sections
    $('#initializeHomeBtn').on('click', function(e) {
        const btn = $(this);
        const originalHtml = btn.html();
        
        // Check if sections already exist
        const hasSections = sections.length > 0;
        
        let confirmMessage = 'This will initialize all sections for the home page:\n\n';
        confirmMessage += '• Banner & Slider (3 variants)\n';
        confirmMessage += '• Popular Products (2 variants)\n';
        confirmMessage += '• New Arrivals (2 variants)\n';
        confirmMessage += '• Discount (2 variants)\n';
        confirmMessage += '• Brand Logos (1 variant)\n';
        confirmMessage += '• Blogs (1 variant)\n';
        confirmMessage += '• Service Highlight (1 variant)\n\n';
        
        if (hasSections) {
            confirmMessage += 'Existing sections will be skipped. To recreate all sections, hold SHIFT and click again.\n\n';
            confirmMessage += 'Continue?';
        } else {
            confirmMessage += 'Continue?';
        }
        
        if (!confirm(confirmMessage)) {
            return;
        }

        // Check if Shift key is pressed (force recreate)
        const forceRecreate = e.shiftKey && hasSections;
        
        if (forceRecreate) {
            if (!confirm('⚠️ WARNING: This will DELETE all existing sections and recreate them from scratch!\n\nAll images and customizations will be lost. Are you sure?')) {
                return;
            }
        }

        btn.prop('disabled', true);
        btn.html('<i class="bx bx-loader-circle bx-spin me-1"></i> Initializing...');

        $.ajax({
            url: '{{ route("sections.initializeHome") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                force: forceRecreate ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    // Reload sections if home page is selected
                    if (currentPageId) {
                        const selectedPage = pages.find(p => p.id === currentPageId);
                        if (selectedPage && (selectedPage.url === '/' || selectedPage.name.toLowerCase().includes('home'))) {
                            loadPageSections(currentPageId);
                        }
                    }
                } else {
                    showToast('error', response.message || 'Failed to initialize sections');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to initialize sections';
                showToast('error', errorMsg);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });

    // Save sort order
    $('#saveSortOrderBtn').on('click', function() {
        const sectionsData = [];
        $('.section-group').each(function(index) {
            const baseName = $(this).data('base-name');
            const sectionGroup = sections.find(s => s.base_name === baseName);
            
            if (sectionGroup) {
                sectionGroup.variants.forEach(function(variant) {
                    sectionsData.push({
                        id: variant.id,
                        sort_order: index
                    });
                });
            }
        });

        $.ajax({
            url: '{{ route("sections.updateSortOrder") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                sections: sectionsData
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    loadPageSections(currentPageId);
                }
            }
        });
    });

    // Upload variant image
    window.uploadVariantImage = function(variantId, input) {
        if (!input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('id', variantId);
        formData.append('image', file);

        const variantItem = $(input).closest('.variant-item');
        const imageContainer = variantItem.find('.variant-image-container');
        const originalHtml = imageContainer.html();

        // Show loading state
        imageContainer.html(`
            <div class="no-image-placeholder">
                <i class='bx bx-loader-circle bx-spin' style='font-size: 1.5rem;'></i>
            </div>
        `);

        $.ajax({
            url: '{{ route("sections.updateVariantImage") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    // Update the image
                    const imageHtml = response.section.image_url ? 
                        `<img src="${response.section.image_url}" class="variant-image" alt="Variant">` :
                        `<div class="no-image-placeholder">
                            <i class='bx bx-image' style='font-size: 1rem;'></i>
                        </div>`;
                    
                    // Update local data
                    sections.forEach(function(sectionGroup) {
                        sectionGroup.variants.forEach(function(variant) {
                            if (variant.id === variantId) {
                                variant.image_url = response.section.image_url;
                            }
                        });
                    });
                    
                    imageContainer.html(imageHtml + `
                        <div class="variant-image-overlay">
                            <button type="button" class="variant-image-btn" onclick="document.getElementById('imageUpload${variantId}').click()">
                                <i class='bx bx-upload'></i> Upload
                            </button>
                            ${response.section.image_url ? `<button type="button" class="variant-image-btn" onclick="removeVariantImage(${variantId})">
                                <i class='bx bx-trash'></i> Remove
                            </button>` : ''}
                        </div>
                        <input type="file" id="imageUpload${variantId}" class="variant-image-upload-input" accept="image/*" onchange="uploadVariantImage(${variantId}, this)">
                    `);
                } else {
                    showToast('error', response.message || 'Failed to upload image');
                    imageContainer.html(originalHtml);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to upload image';
                showToast('error', errorMsg);
                imageContainer.html(originalHtml);
            }
        });

        // Reset input
        input.value = '';
    };

    // Remove variant image
    window.removeVariantImage = function(variantId) {
        if (!confirm('Are you sure you want to remove this image?')) {
            return;
        }

        const variantItem = $(`.variant-item[data-variant-id="${variantId}"]`);
        const imageContainer = variantItem.find('.variant-image-container');
        const originalHtml = imageContainer.html();

        // Show loading state
        imageContainer.html(`
            <div class="no-image-placeholder">
                <i class='bx bx-loader-circle bx-spin' style='font-size: 1.5rem;'></i>
            </div>
        `);

        $.ajax({
            url: '{{ route("sections.updateVariantImage") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: variantId,
                remove_image: true
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    // Update the image
                    const imageHtml = `<div class="no-image-placeholder">
                        <i class='bx bx-image' style='font-size: 1rem;'></i>
                    </div>`;
                    
                    // Update local data
                    sections.forEach(function(sectionGroup) {
                        sectionGroup.variants.forEach(function(variant) {
                            if (variant.id === variantId) {
                                variant.image_url = null;
                            }
                        });
                    });
                    
                    imageContainer.html(imageHtml + `
                        <div class="variant-image-overlay">
                            <button type="button" class="variant-image-btn" onclick="document.getElementById('imageUpload${variantId}').click()">
                                <i class='bx bx-upload'></i> Upload
                            </button>
                        </div>
                        <input type="file" id="imageUpload${variantId}" class="variant-image-upload-input" accept="image/*" onchange="uploadVariantImage(${variantId}, this)">
                    `);
                } else {
                    showToast('error', response.message || 'Failed to remove image');
                    imageContainer.html(originalHtml);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to remove image';
                showToast('error', errorMsg);
                imageContainer.html(originalHtml);
            }
        });
    };

    // Toast notification
    function showToast(type, message) {
        const toastContainer = $('.sa-app__toasts');
        if (!toastContainer.length) {
            $('body').append('<div class="sa-app__toasts position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const iconClass = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';

        const toast = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
                <div class="toast-body d-flex align-items-center">
                    <i class='bx ${iconClass} me-2' style='font-size: 1.5rem;'></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        $('.sa-app__toasts').append(toast);
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000
        });
        toastElement.show();

        $('#' + toastId).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush
