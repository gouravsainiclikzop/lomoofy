@extends('layouts.admin')

@section('title', 'Product Attributes Management')

@section('content')
<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Master Data</li>
                        <li class="breadcrumb-item active">Attributes</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Product Attributes Management</h1>
                <p class="text-muted mb-0">Manage product attributes like Color, Size, Material, etc. Attributes help define product characteristics and enable variations.</p>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-none" id="bulkDeleteAttributesBtn" onclick="bulkDeleteAttributes()">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-primary" onclick="openAttributeModal()">
                    <i class="fas fa-plus"></i> Add Attribute
                </button>
            </div>
        </div>

        <!-- Information Card -->
        <!-- <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-primary me-2"></i>What are Product Attributes?
                </h5>
                <p class="card-text mb-3">
                    Product attributes define the characteristics and properties of your products. They help customers make informed decisions and enable you to create product variations.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Common Attribute Types:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Text Input:</strong> Free text (e.g., Product Name, Description)</li>
                            <li><strong>Dropdown Select:</strong> Predefined options (e.g., Size: S, M, L, XL)</li>
                            <li><strong>Color Picker:</strong> Color selection with visual picker</li>
                            <li><strong>Number Input:</strong> Numeric values (e.g., Weight, Dimensions)</li>
                            <li><strong>Date Picker:</strong> Date selection (e.g., Expiry Date, Launch Date)</li>
                            <li><strong>Yes/No:</strong> Boolean values (e.g., Organic, Waterproof)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Attribute Properties:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Variation:</strong> Can be used to create product variants</li>
                            <li><strong>Visible:</strong> Shown to customers on product pages</li>
                            <li><strong>Required:</strong> Must be filled when creating products</li>
                            <li><strong>Sort Order:</strong> Controls display order in forms</li>
                            <li><strong>Values:</strong> Predefined options for select-type attributes</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <strong> Pro Tip:</strong> Use variation attributes (like Size, Color) to create multiple product variants from a single base product. This helps manage inventory and pricing more efficiently.
                </div>
            </div>
        </div> -->

        <!-- Attributes Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">All Attributes</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSortable()">
                            <i class="fas fa-sort"></i> Reorder
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportAttributes()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="attributesTable">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllAttributes" class="form-check-input">
                                </th>
                                <th width="50">Order</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Values</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attributesTableBody">
                            @foreach($attributes as $attribute)
                            <tr data-attribute-id="{{ $attribute->id }}" data-sort-order="{{ $attribute->sort_order }}">
                                <td>
                                    <input type="checkbox" class="form-check-input attribute-checkbox" value="{{ $attribute->id }}">
                                </td>
                                <td>
                                    <div class="sort-handle" style="cursor: move;">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $attribute->name }}</strong>
                                        <small class="text-muted d-block">{{ $attribute->description }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($attribute->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $attribute->values->count() }} values</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-attribute-action="edit" data-attribute-id="{{ $attribute->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" data-attribute-action="manage-values" data-attribute-id="{{ $attribute->id }}">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" data-attribute-action="delete" data-attribute-id="{{ $attribute->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attribute Modal -->
<div class="modal fade" id="attributeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attributeModalTitle">Add Attribute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="attributeForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" id="attributeSlug" required readonly>
                            <small class="text-muted">Auto-generated from name</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required>
                                <option value="">Select Type</option>
                                <option value="text">Text Input</option>
                                <option value="select">Dropdown Select</option>
                                <option value="color">Color Picker</option>
                                <option value="number">Number Input</option>
                                <option value="date">Date Picker</option>
                                <option value="boolean">Yes/No</option>
                                <option value="image">Image Upload</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAttribute()">Save Attribute</button>
            </div>
        </div>
    </div>
</div>

<!-- Attribute Values Modal -->
<div class="modal fade" id="attributeValuesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Attribute Values</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 id="attributeValuesTitle">Attribute Values</h6>
                </div>
                <div id="attributeValueForm" class="card mb-3">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5" id="attributeValueInputWrapper"></div>
                            <div class="col-md-3 d-none" id="attributeValueColorWrapper">
                                <label class="form-label form-label-sm mb-1">Color</label>
                                <input type="hidden" class="form-control form-control-sm" id="newAttributeColor" value="#000000">
                                <div id="newAttributeColorPickerContainer"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm mb-1">Sort Order</label>
                                <input type="number" class="form-control form-control-sm" id="newAttributeSortOrder" min="0" placeholder="0">
                            </div>
                            <div class="col-md-2 text-md-end">
                                <label class="form-label form-label-sm mb-1 invisible">Add</label>
                                <button type="button" class="btn btn-primary btn-sm w-100" onclick="addAttributeValue()">
                                    <i class="fas fa-plus"></i> Add Value
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="attributeValuesContainer">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAttributeId = null;
let currentAttributeType = null;
let isSortable = false;

const ATTRIBUTES_BASE_URL = "{{ url('attributes') }}";
const ATTRIBUTES_STORE_URL = "{{ route('attributes.store') }}";

function normalizeColorValue(value) {
    if (typeof value === 'string' && /^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/.test(value)) {
        return value;
    }
    return '#000000';
}

function createValueControl(type, attributeValue, valueId) {
    const valueData = (attributeValue && attributeValue.value !== undefined && attributeValue.value !== null)
        ? attributeValue.value
        : '';
    let control;

    if (type === 'boolean') {
        control = document.createElement('select');
        control.className = 'form-select form-select-sm';
        control.setAttribute('data-value-input', 'true');

        const options = [
            { label: 'Select option', value: '' },
            { label: 'Yes', value: 'Yes' },
            { label: 'No', value: 'No' }
        ];

        const normalized = valueData ? String(valueData).toLowerCase() : '';
        let currentValue = '';
        if (normalized === 'yes' || normalized === '1' || normalized === 'true') {
            currentValue = 'Yes';
        } else if (normalized === 'no' || normalized === '0' || normalized === 'false') {
            currentValue = 'No';
        }

        options.forEach(function(option) {
            const opt = document.createElement('option');
            opt.value = option.value;
            opt.textContent = option.label;
            control.appendChild(opt);
        });

        control.value = currentValue;
        control.addEventListener('change', function() {
            updateAttributeValue(valueId, 'value', control.value);
        });
    } else {
        control = document.createElement('input');
        control.className = 'form-control form-control-sm';
        control.setAttribute('data-value-input', 'true');

        if (type === 'number') {
            control.type = 'number';
            control.step = '0.01';
            control.placeholder = 'Enter number';
        } else if (type === 'date') {
            control.type = 'date';
        } else {
            control.type = 'text';
            control.placeholder = 'Enter value';
        }

        control.value = valueData !== undefined && valueData !== null ? valueData : '';
        control.addEventListener('change', function() {
            updateAttributeValue(valueId, 'value', control.value);
        });
    }

    return control;
}

// Auto-generate slug from name
function generateSlug(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')      // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
}

// Initialize sortable functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    initializeAttributeActionListeners();
    initializeBulkDelete();
    
    // Setup slug auto-generation from name field
    setupSlugAutoGeneration();
});

function setupSlugAutoGeneration() {
    // Use event delegation since modal content is dynamic
    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'name' && e.target.closest('#attributeForm')) {
            const slugInput = document.getElementById('attributeSlug');
            if (slugInput && !slugInput.dataset.edited) {
                slugInput.value = generateSlug(e.target.value);
            }
        }
    });
    
    // Also handle when modal is opened
    const attributeModal = document.getElementById('attributeModal');
    if (attributeModal) {
        attributeModal.addEventListener('shown.bs.modal', function() {
            const nameInput = document.querySelector('#attributeForm input[name="name"]');
            const slugInput = document.getElementById('attributeSlug');
            
            if (nameInput && slugInput) {
                // Remove any existing listeners to avoid duplicates
                const newNameInput = nameInput.cloneNode(true);
                nameInput.parentNode.replaceChild(newNameInput, nameInput);
                
                // Add input listener
                newNameInput.addEventListener('input', function() {
                    if (!slugInput.dataset.edited) {
                        slugInput.value = generateSlug(this.value);
                    }
                });
                
                // Allow manual editing but mark as edited
                slugInput.addEventListener('input', function() {
                    this.dataset.edited = 'true';
                });
            }
        });
    }
}

function initializeBulkDelete() {
    // Select All Attributes
    const selectAllCheckbox = document.getElementById('selectAllAttributes');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.attribute-checkbox').forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            updateBulkDeleteAttributesButton();
        });
    }
    
    // Individual checkbox change - use event delegation
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('attribute-checkbox')) {
            updateBulkDeleteAttributesButton();
            updateSelectAllState();
        }
    });
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('selectAllAttributes');
    const checkboxes = document.querySelectorAll('.attribute-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.attribute-checkbox:checked');
    
    if (selectAllCheckbox && checkboxes.length > 0) {
        if (checkedCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCheckboxes.length === checkboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
}

function updateBulkDeleteAttributesButton() {
    const checkedCount = document.querySelectorAll('.attribute-checkbox:checked').length;
    const bulkDeleteBtn = document.getElementById('bulkDeleteAttributesBtn');
    if (bulkDeleteBtn) {
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('d-none');
        } else {
            bulkDeleteBtn.classList.add('d-none');
        }
    }
}

function bulkDeleteAttributes() {
    const checkedBoxes = document.querySelectorAll('.attribute-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showToast('error', 'Please select at least one attribute to delete');
        return;
    }
    
    const ids = Array.from(checkedBoxes).map(function(checkbox) {
        return checkbox.value;
    });
    
    if (!confirm(`Are you sure you want to delete ${ids.length} attribute(s)? Attributes used in products cannot be deleted.`)) {
        return;
    }
    
    fetch('{{ route("attributes.bulk-delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, return error
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
            });
        }
    })
    .then(result => {
        if (result.success) {
            showToast('success', result.message);
            location.reload();
        } else {
            // Show simplified message without listing all errors
            let errorMsg = result.message || 'Failed to delete attributes';
            showToast('error', errorMsg);
        }
    })
    .catch(error => {
        console.error('Error deleting attributes:', error);
        showToast('error', 'Failed to delete attributes: ' + error.message);
    });
}

function initializeSortable() {
    if (typeof Sortable !== 'undefined') {
        const tbody = document.getElementById('attributesTableBody');
        if (tbody) {
            new Sortable(tbody, {
                handle: '.sort-handle',
                animation: 150,
                onEnd: function(evt) {
                    updateSortOrder();
                }
            });
        }
    }
}

function initializeAttributeActionListeners() {
    document.addEventListener('click', function(event) {
        const button = event.target.closest('[data-attribute-action]');
        if (!button) {
            return;
        }

        const action = button.getAttribute('data-attribute-action');
        const attributeId = button.getAttribute('data-attribute-id');
        if (!attributeId) {
            return;
        }

        event.preventDefault();

        if (action === 'view-values') {
            viewAttributeValues(attributeId);
        } else if (action === 'manage-values') {
            manageAttributeValues(attributeId);
        } else if (action === 'edit') {
            editAttribute(attributeId);
        } else if (action === 'delete') {
            deleteAttribute(attributeId);
        }
    });
}

function toggleSortable() {
    const handles = document.querySelectorAll('.sort-handle');
    isSortable = !isSortable;
    
    handles.forEach(handle => {
        handle.style.display = isSortable ? 'block' : 'none';
    });
    
    if (isSortable) {
        showToast('info', 'Drag and drop to reorder attributes');
    }
}

function updateSortOrder() {
    const rows = document.querySelectorAll('#attributesTableBody tr');
    const sortData = [];
    
    rows.forEach((row, index) => {
        const attributeId = row.dataset.attributeId;
        sortData.push({
            id: parseInt(attributeId),
            sort_order: index
        });
    });
    
    fetch('{{ route("attributes.update-sort-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ attributes: sortData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Sort order updated successfully');
        }
    })
    .catch(error => {
        console.error('Error updating sort order:', error);
        showToast('error', 'Failed to update sort order');
    });
}

function openAttributeModal(attributeId = null) {
    currentAttributeId = attributeId;
    const modalElement = document.getElementById('attributeModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const form = document.getElementById('attributeForm');
    
    if (attributeId) {
        document.getElementById('attributeModalTitle').textContent = 'Edit Attribute';
        // Load attribute data for editing
        loadAttributeData(attributeId);
    } else {
        document.getElementById('attributeModalTitle').textContent = 'Add Attribute';
        form.reset();
        // Reset slug field and enable auto-generation
        const slugInput = document.getElementById('attributeSlug');
        const nameInput = form.querySelector('input[name="name"]');
        if (slugInput) {
            slugInput.dataset.edited = '';
            slugInput.value = '';
        }
        // Trigger slug generation if name has value (in case form wasn't fully reset)
        if (nameInput && nameInput.value) {
            slugInput.value = generateSlug(nameInput.value);
        }
    }
    
    modal.show();
}

function loadAttributeData(attributeId) {
    fetch(ATTRIBUTES_BASE_URL + '/' + attributeId)
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('attributeForm');
            form.name.value = data.name;
            form.slug.value = data.slug;
            form.type.value = data.type;
            form.description.value = data.description || '';
            form.sort_order.value = data.sort_order || '';
            
            // Mark slug as edited when loading existing data
            const slugInput = document.getElementById('attributeSlug');
            if (slugInput) {
                slugInput.dataset.edited = 'true';
            }
        })
        .catch(error => {
            console.error('Error loading attribute:', error);
            showToast('error', 'Failed to load attribute data');
        });
}

function saveAttribute() {
    const form = document.getElementById('attributeForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Convert checkboxes to boolean
    
    const url = currentAttributeId
        ? ATTRIBUTES_BASE_URL + '/' + currentAttributeId + '/update'
        : ATTRIBUTES_STORE_URL;
    const method = 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('success', result.message);
            const modalElement = document.getElementById('attributeModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
            location.reload();
        } else {
            showToast('error', result.message || 'Failed to save attribute');
            if (result.errors) {
                displayValidationErrors(result.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error saving attribute:', error);
        showToast('error', 'Failed to save attribute');
    });
}

function editAttribute(attributeId) {
    openAttributeModal(attributeId);
}

function deleteAttribute(attributeId) {
    if (confirm('Are you sure you want to delete this attribute? This action cannot be undone.')) {
        fetch(ATTRIBUTES_BASE_URL + '/' + attributeId + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast('success', result.message);
                location.reload();
            } else {
                showToast('error', result.message || 'Failed to delete attribute');
            }
        })
        .catch(error => {
            console.error('Error deleting attribute:', error);
            showToast('error', 'Failed to delete attribute');
        });
    }
}

function viewAttributeValues(attributeId) {
    // This function is an alias for manageAttributeValues
    manageAttributeValues(attributeId);
}

function manageAttributeValues(attributeId) {
    currentAttributeId = attributeId;
    const modalElement = document.getElementById('attributeValuesModal');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

    fetch(ATTRIBUTES_BASE_URL + '/' + attributeId)
        .then(response => response.json())
        .then(attribute => {
            currentAttributeType = attribute.type ? attribute.type : 'text';
            document.getElementById('attributeValuesTitle').textContent = 'Manage Attribute Values - ' + attribute.name;
            configureAttributeValueForm();
            loadAttributeValues(Array.isArray(attribute.values) ? attribute.values : []);
            modalInstance.show();
        })
        .catch(error => {
            console.error('Error loading attribute values:', error);
            showToast('error', 'Failed to load attribute values');
        });
}

function configureAttributeValueForm() {
    const wrapper = document.getElementById('attributeValueInputWrapper');
    if (!wrapper) {
        return;
    }

    const type = currentAttributeType ? currentAttributeType : 'text';
    wrapper.innerHTML = '';

    const label = document.createElement('label');
    label.className = 'form-label form-label-sm mb-1';
    label.innerHTML = 'Value <span class="text-danger">*</span>';
    wrapper.appendChild(label);

    let control;

    if (type === 'boolean') {
        control = document.createElement('select');
        control.className = 'form-select form-select-sm';
        control.id = 'newAttributeValue';

        const options = [
            { label: 'Select option', value: '' },
            { label: 'Yes', value: 'Yes' },
            { label: 'No', value: 'No' }
        ];

        options.forEach(function(option) {
            const opt = document.createElement('option');
            opt.value = option.value;
            opt.textContent = option.label;
            control.appendChild(opt);
        });
    } else {
        control = document.createElement('input');
        control.className = 'form-control form-control-sm';
        control.id = 'newAttributeValue';

        if (type === 'number') {
            control.type = 'number';
            control.step = '0.01';
            control.placeholder = 'Enter numeric value';
        } else if (type === 'date') {
            control.type = 'date';
        } else {
            control.type = 'text';
            control.placeholder = 'Enter value';
        }
    }

    wrapper.appendChild(control);

    const colorWrapper = document.getElementById('attributeValueColorWrapper');
    const colorInput = document.getElementById('newAttributeColor');
    const colorPickerContainer = document.getElementById('newAttributeColorPickerContainer');
    if (colorWrapper) {
        if (type === 'color') {
            colorWrapper.classList.remove('d-none');
            if (colorInput) {
                colorInput.value = '#000000';
            }
            // Initialize Pickr for new attribute color
            if (colorPickerContainer && colorInput) {
                setTimeout(() => {
                    // Clear existing picker
                    if (colorPickers['newAttributeColor']) {
                        colorPickers['newAttributeColor'].destroy();
                    }
                    // Create button for picker
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-sm w-100';
                    colorPickerContainer.innerHTML = '';
                    colorPickerContainer.appendChild(button);
                    
                    // Initialize Pickr
                    initializeColorPicker(button, colorInput, '#000000', function(newColor) {
                        colorInput.value = newColor;
                    });
                }, 100);
            }
        } else {
            colorWrapper.classList.add('d-none');
        }
    }

    const sortInput = document.getElementById('newAttributeSortOrder');
    if (sortInput) {
        sortInput.value = '';
    }
}

function loadAttributeValues(values) {
    const container = document.getElementById('attributeValuesContainer');
    if (!Array.isArray(values) || values.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No values found. Add some values for this attribute.</p>';
        return;
    }

    container.innerHTML = '';
    const type = currentAttributeType ? currentAttributeType : 'text';

    values.forEach(function(attributeValue) {
        const card = document.createElement('div');
        card.className = 'card mb-2';
        card.setAttribute('data-value-id', attributeValue.id);

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body py-2';

        const row = document.createElement('div');
        row.className = 'row align-items-center';

        const valueCol = document.createElement('div');
        valueCol.className = 'col-md-4';
        const valueLabel = document.createElement('label');
        valueLabel.className = 'form-label form-label-sm mb-1';
        valueLabel.textContent = 'Value';
        const valueControl = createValueControl(type, attributeValue, attributeValue.id);
        valueCol.appendChild(valueLabel);
        valueCol.appendChild(valueControl);
        row.appendChild(valueCol);

        if (type === 'color') {
            const colorCol = document.createElement('div');
            colorCol.className = 'col-md-3';
            const colorLabel = document.createElement('label');
            colorLabel.className = 'form-label form-label-sm mb-1';
            colorLabel.textContent = 'Color';

            const colorInput = document.createElement('input');
            colorInput.type = 'hidden';
            colorInput.className = 'form-control form-control-sm color-input';
            colorInput.id = 'colorInput_' + attributeValue.id;
            const colorValue = normalizeColorValue(attributeValue && attributeValue.color_code ? attributeValue.color_code : '#000000');
            colorInput.value = colorValue;
            
            // Create button for picker
            const colorButton = document.createElement('button');
            colorButton.type = 'button';
            colorButton.className = 'btn btn-sm w-100';
            colorCol.appendChild(colorInput);
            colorCol.appendChild(colorButton);
            
            // Initialize Pickr after element is added to DOM
            setTimeout(() => {
                initializeColorPicker(colorButton, colorInput, colorValue, function(newColor) {
                    updateAttributeValue(attributeValue.id, 'color_code', newColor);
                });
            }, 100);

            colorCol.appendChild(colorLabel);
            colorCol.appendChild(colorInput);
            row.appendChild(colorCol);
        }

        const sortCol = document.createElement('div');
        sortCol.className = type === 'color' ? 'col-md-2' : 'col-md-3';
        const sortLabel = document.createElement('label');
        sortLabel.className = 'form-label form-label-sm mb-1';
        sortLabel.textContent = 'Sort';

        const sortInput = document.createElement('input');
        sortInput.type = 'number';
        sortInput.className = 'form-control form-control-sm';
        sortInput.placeholder = 'Order';
        if (attributeValue && attributeValue.sort_order !== null && attributeValue.sort_order !== undefined) {
            sortInput.value = attributeValue.sort_order;
        }
        sortInput.addEventListener('change', function() {
            updateAttributeValue(attributeValue.id, 'sort_order', sortInput.value);
        });

        sortCol.appendChild(sortLabel);
        sortCol.appendChild(sortInput);
        row.appendChild(sortCol);

        const actionCol = document.createElement('div');
        actionCol.className = 'col-md-3 text-end';
        const actionLabel = document.createElement('label');
        actionLabel.className = 'form-label form-label-sm mb-1 invisible';
        actionLabel.textContent = 'Actions';

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-outline-danger btn-sm';
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.addEventListener('click', function() {
            deleteAttributeValue(attributeValue.id);
        });

        actionCol.appendChild(actionLabel);
        actionCol.appendChild(deleteBtn);
        row.appendChild(actionCol);

        cardBody.appendChild(row);
        card.appendChild(cardBody);
        container.appendChild(card);
    });
}

function addAttributeValue() {
    if (!currentAttributeId) return;
    const valueControl = document.getElementById('newAttributeValue');
    const sortControl = document.getElementById('newAttributeSortOrder');
    const colorControl = document.getElementById('newAttributeColor'); // Hidden input that stores the color value

    if (!valueControl) {
        return;
    }

    let newValue = '';
    const type = currentAttributeType ? currentAttributeType : 'text';

    if (type === 'boolean') {
        newValue = valueControl.value;
        if (!newValue) {
            showToast('error', 'Please select a value');
            return;
        }
    } else if (type === 'number') {
        newValue = valueControl.value;
        if (newValue === '') {
            showToast('error', 'Please enter a numeric value');
            return;
        }
    } else if (type === 'date') {
        newValue = valueControl.value;
        if (!newValue) {
            showToast('error', 'Please select a date');
            return;
        }
    } else {
        newValue = valueControl.value.trim();
        if (!newValue) {
            showToast('error', 'Please enter a value');
            return;
        }
    }

    const sortOrderValue = (() => {
        if (!sortControl || sortControl.value === '') {
            return null;
        }
        const parsed = parseInt(sortControl.value, 10);
        return Number.isNaN(parsed) ? null : parsed;
    })();

    fetch(ATTRIBUTES_BASE_URL + '/' + currentAttributeId + '/values', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            value: newValue,
            sort_order: sortOrderValue,
            color_code: type === 'color' && colorControl ? colorControl.value : null
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('success', result.message);
            if (valueControl) {
                if (type === 'boolean') {
                    valueControl.value = '';
                } else if (type === 'number') {
                    valueControl.value = '';
                } else if (type === 'date') {
                    valueControl.value = '';
                } else {
                    valueControl.value = '';
                }
            }
            if (sortControl) {
                sortControl.value = '';
            }
            if (type === 'color' && colorControl) {
                colorControl.value = '#000000';
            }
            manageAttributeValues(currentAttributeId); // Reload values
            updateAttributeTableCount(currentAttributeId); // Update main table count
        } else {
            showToast('error', result.message || 'Failed to add attribute value');
        }
    })
    .catch(error => {
        console.error('Error adding attribute value:', error);
        showToast('error', 'Failed to add attribute value');
    });
}

function updateAttributeValue(valueId, field, value) {
    // Get the current value from the input field to avoid validation errors
    const valueCard = document.querySelector('[data-value-id="' + valueId + '"]');
    const currentValueInput = valueCard ? valueCard.querySelector('[data-value-input]') : null;
    const currentValue = currentValueInput ? currentValueInput.value : '';

    const updateData = {};
    if (field === 'color_code') {
        updateData.color_code = value;
    } else if (field === 'sort_order') {
        updateData.sort_order = value;
    } else {
        updateData.value = value;
    }

    // Always include the current value to prevent validation errors
    if (field !== 'value') {
        updateData.value = currentValue;
    }

    fetch(ATTRIBUTES_BASE_URL + '/values/' + valueId + '/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(updateData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('success', 'Value updated successfully');
            // Get the attribute ID from the value card to update the table count
            const valueCard = document.querySelector('[data-value-id="' + valueId + '"]');
            if (valueCard && currentAttributeId) {
                updateAttributeTableCount(currentAttributeId);
            }
        } else {
            showToast('error', result.message || 'Failed to update value');
        }
    })
    .catch(error => {
        console.error('Error updating attribute value:', error);
        showToast('error', 'Failed to update value');
    });
}

function deleteAttributeValue(valueId) {
    if (confirm('Are you sure you want to delete this value?')) {
        fetch(ATTRIBUTES_BASE_URL + '/values/' + valueId + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast('success', result.message);
                manageAttributeValues(currentAttributeId); // Reload values
                updateAttributeTableCount(currentAttributeId); // Update main table count
            } else {
                showToast('error', result.message || 'Failed to delete value');
            }
        })
        .catch(error => {
            console.error('Error deleting attribute value:', error);
            showToast('error', 'Failed to delete value');
        });
    }
}

function updateAttributeTableCount(attributeId) {
    // Fetch the updated attribute data to get the new value count
    fetch(ATTRIBUTES_BASE_URL + '/' + attributeId)
        .then(response => response.json())
        .then(attribute => {
            // Find the table row for this attribute
            const tableRow = document.querySelector(`tr[data-attribute-id="${attributeId}"]`);
            if (tableRow) {
                // Find the Values column by looking for the badge with "values" text
                const valuesCell = Array.from(tableRow.querySelectorAll('td')).find(td => {
                    return td.textContent.includes('values');
                });
                if (valuesCell) {
                    // Update the badge with the new count
                    const count = Array.isArray(attribute.values) ? attribute.values.length : 0;
                    valuesCell.innerHTML = `<span class="badge bg-secondary">${count} values</span>`;
                }
            }
        })
        .catch(error => {
            console.error('Error updating attribute table count:', error);
        });
}

function exportAttributes() {
    window.open('{{ route("master-data.export") }}?type=attributes', '_blank');
}

function displayValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.querySelector('[name="' + field + '"]');
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = errors[field][0];
            }
        }
    });
}

function showToast(type, message) {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' position-fixed';
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>

<!-- Include SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endsection
