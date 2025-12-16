@extends('layouts.admin')

@section('title', 'Field Management')

@push('styles')
<style>
    /* Toast z-index fix */
    .toast-container {
        z-index: 9999 !important;
    }
    .toast {
        z-index: 10000 !important;
    }
    
    .field-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: white;
    }
    .field-item:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .field-item.editing {
        border-color: #f5c000;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    /* Active Tab Styling */
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s;
    }
    
    .nav-tabs .nav-link:hover {
        color: #f5c000;
        border-bottom-color: #dee2e6;
    }
    
    .nav-tabs .nav-link.active {
        color: #f5c000;
        font-weight: 600;
        border-bottom-color: #f5c000;
        background-color: transparent;
    }
    
    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
    }
    
    /* Form Preview Styles */
    .form-preview-container {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        max-height: 800px;
        overflow-y: auto;
    }
    
    .form-preview-group {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        min-height: 100px;
    }
    
    .form-preview-group-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .draggable-field {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        cursor: move;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.2s;
    }
    
    .draggable-field:hover {
        background: #e9ecef;
        border-color: #f5c000;
    }
    
    .draggable-field.drag-handle-active {
        cursor: grabbing;
    }
    
    .drag-handle {
        cursor: grab;
        color: #6c757d;
        font-size: 1.2rem;
    }
    
    .drag-handle:active {
        cursor: grabbing;
    }
    
    .field-preview-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .field-preview-label {
        font-weight: 500;
        color: #212529;
        margin-bottom: 0;
        white-space: nowrap;
    }
    
    .field-preview-meta {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0;
    }
    
    .field-preview-badges {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: #e9ecef;
    }
    
    .sortable-drag {
        opacity: 0.8;
    }
    
    .field-inactive {
        opacity: 0.5;
        background: #f8f9fa;
    }
    
    .empty-group-placeholder {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
        font-style: italic;
        border: 2px dashed #dee2e6;
        border-radius: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <!-- Header -->
                <div class="mb-4">
                    <nav class="mb-2" aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-sa-simple">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customer Management</a></li>
                            <li class="breadcrumb-item active">Field Management</li>
                        </ol>
                    </nav>
                    <h1 class="h3 m-0">Field Management</h1>
                    <p class="text-muted mb-0">Manage form fields for customer entry. Control labels, visibility, requirements, and conditional display rules.</p>
                </div>

                <!-- Tabs -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-pane" type="button" role="tab" aria-controls="preview-pane" aria-selected="true">
                                    Form Preview
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fields-tab" data-bs-toggle="tab" data-bs-target="#fields-pane" type="button" role="tab" aria-controls="fields-pane" aria-selected="false">
                                    Form Fields
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="fieldManagementTabs">
                            <!-- Form Preview Tab -->
                            <div class="tab-pane fade show active" id="preview-pane" role="tabpanel" aria-labelledby="preview-tab">
                                <div class="mb-3">
                                    <h5 class="mb-2">Form Preview - Drag & Drop to Reorder</h5>
                                    <small class="text-muted">Drag fields to change their order and group. Changes are saved automatically.</small>
                                </div>
                                <div class="form-preview-container" id="formPreviewContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading form preview...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Loading form preview...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Fields Table Tab -->
                            <div class="tab-pane fade" id="fields-pane" role="tabpanel" aria-labelledby="fields-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="fieldsTable">
                                        <thead>
                                            <tr>
                                                <th>Field Key</th>
                                                <th>Label</th>
                                                <th>Input Type</th>
                                                <th>Group</th>
                                                <th>Required</th>
                                                <th>Visible</th>
                                                <th>Sort Order</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                                <th class="w-min" data-orderable="false">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="fieldModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fieldModalTitle">Edit Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="fieldForm">
                <div class="modal-body">
                    <input type="hidden" id="fieldId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Field Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldKey" name="field_key" required readonly>
                            <div class="form-text">Field key cannot be changed</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldLabel" name="label" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Input Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="fieldInputType" name="input_type" required>
                                <option value="">Select Type</option>
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="password">Password</option>
                                <option value="tel">Telephone</option>
                                <option value="number">Number</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="date">Date</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Field Group</label>
                            <select class="form-select" id="fieldGroup" name="field_group">
                                <option value="">Select Group</option>
                                <option value="basic_info">Basic Info</option>
                                <option value="credentials">Credentials</option>
                                <option value="address">Address</option>
                                <option value="business">Business</option>
                                <option value="preferences">Preferences</option>
                                <option value="qol">Quality-of-Life</option>
                                <option value="internal">Internal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" id="fieldPlaceholder" name="placeholder">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="fieldSortOrder" name="sort_order" value="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class='bx bx-shield-quarter'></i> Field Validation Rules
                                <small class="text-muted">(Optional - Set rules to ensure data quality)</small>
                            </label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <!-- Basic Requirements -->
                                    <div class="mb-4 validation-section" id="valRequiredSection">
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-check-circle'></i> Basic Requirements
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valRequired" name="val_required">
                                                    <label class="form-check-label" for="valRequired">
                                                        <strong>Field is Required</strong>
                                                        <small class="d-block text-muted">User must fill this field before submitting</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Format Validation -->
                                    <div class="mb-4 validation-section" id="valEmailSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-edit'></i> Format Validation
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valEmail" name="val_email">
                                                    <label class="form-check-label" for="valEmail">
                                                        <strong>Must be a valid Email</strong>
                                                        <small class="d-block text-muted">Example: user@example.com</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- URL Validation -->
                                    <div class="mb-4 validation-section" id="valUrlSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-link'></i> URL Validation
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valUrl" name="val_url">
                                                    <label class="form-check-label" for="valUrl">
                                                        <strong>Must be a valid Website URL</strong>
                                                        <small class="d-block text-muted">Example: https://www.example.com</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Character Type Validation -->
                                    <div class="mb-4 validation-section" id="valCharacterTypeSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-font'></i> Allowed Character Types
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valNumeric" name="val_numeric">
                                                    <label class="form-check-label" for="valNumeric">
                                                        <strong>Numbers Only</strong>
                                                        <small class="d-block text-muted">Only digits (0-9) allowed</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valAlpha" name="val_alpha">
                                                    <label class="form-check-label" for="valAlpha">
                                                        <strong>Letters Only</strong>
                                                        <small class="d-block text-muted">Only alphabets (A-Z, a-z) allowed</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valAlphaNum" name="val_alpha_num">
                                                    <label class="form-check-label" for="valAlphaNum">
                                                        <strong>Letters & Numbers</strong>
                                                        <small class="d-block text-muted">Alphabets and digits allowed</small>
                                                    </label>
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Numeric Validation -->
                                    <div class="mb-4 validation-section" id="valNumericSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-hash'></i> Number Validation
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="valNumeric" name="val_numeric">
                                                    <label class="form-check-label" for="valNumeric">
                                                        <strong>Must be a Number</strong>
                                                        <small class="d-block text-muted">Only numeric values (0-9, decimals) allowed</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Length Restrictions -->
                                    <div class="mb-4 validation-section" id="valLengthSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-ruler'></i> Text Length Restrictions
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <strong>Minimum Characters</strong>
                                                    <small class="text-muted d-block">User must enter at least this many characters</small>
                                                </label>
                                                <input type="number" class="form-control" id="valMin" name="val_min" min="0" placeholder="e.g., 3 (minimum 3 characters)">
                                                <small class="form-text text-muted">Leave empty if no minimum required</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <strong>Maximum Characters</strong>
                                                    <small class="text-muted d-block">User cannot enter more than this many characters</small>
                                                </label>
                                                <input type="number" class="form-control" id="valMax" name="val_max" min="0" placeholder="e.g., 255 (maximum 255 characters)">
                                                <small class="form-text text-muted">Leave empty if no maximum limit</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Number Range (for numeric fields) -->
                                    <div class="mb-4 validation-section" id="valNumberRangeSection" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary mb-3">
                                            <i class='bx bx-slider'></i> Number Range
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <strong>Minimum Value</strong>
                                                    <small class="text-muted d-block">Smallest number allowed</small>
                                                </label>
                                                <input type="number" class="form-control" id="valMinVal" name="val_min_val" placeholder="e.g., 0 (cannot be less than 0)">
                                                <small class="form-text text-muted">Leave empty if no minimum</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <strong>Maximum Value</strong>
                                                    <small class="text-muted d-block">Largest number allowed</small>
                                                </label>
                                                <input type="number" class="form-control" id="valMaxVal" name="val_max_val" placeholder="e.g., 100 (cannot be more than 100)">
                                                <small class="form-text text-muted">Leave empty if no maximum</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview -->
                                    <div class="mt-4 p-3 bg-white rounded border">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <strong class="text-muted">Generated Validation Rule:</strong>
                                                <code id="validationPreview" class="ms-2">-</code>
                                            </div>
                                            <small class="text-muted">
                                                <i class='bx bx-info-circle'></i> This is what will be saved
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Help Text</label>
                            <input type="text" class="form-control" id="fieldHelpText" name="help_text">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldRequired" name="is_required" value="1">
                                <label class="form-check-label" for="fieldRequired">Required</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldVisible" name="is_visible" value="1" checked>
                                <label class="form-check-label" for="fieldVisible">Visible</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="fieldActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="optionsContainer" style="display: none;">
                            <label class="form-label">Options (for select/radio)</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div id="optionsList">
                                        <!-- Options will be added here dynamically -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addOptionBtn">
                                        <i class='bx bx-plus'></i> Add Option
                                    </button>
                                    <div class="form-text mt-2">Add options for select/radio fields. Each option needs a value and label.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="conditionalContainer" style="display: none;">
                            <label class="form-label">Conditional Rules (JSON)</label>
                            <textarea class="form-control" id="fieldConditionalRules" name="conditional_rules" rows="3" placeholder='{"depends_on": "field_key", "show_when": "value"}'></textarea>
                            <div class="form-text">Enter as JSON object with "depends_on" and "show_when" keys</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Field</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
        <div class="toast-header">
            <i class='bx bx-check-circle text-success me-2'></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let fieldsTable;
let isEditMode = false;
let formFields = [];
let sortableInstances = {};

$(document).ready(function() {
    // Initialize DataTable when fields tab is shown
    $('#fields-tab').on('shown.bs.tab', function() {
        if (!$.fn.DataTable.isDataTable('#fieldsTable')) {
            initializeDataTable();
        }
    });
    
    // Initialize on page load if fields tab is active (though preview is default)
    // DataTable will initialize when user switches to fields tab
    
    loadFormPreview();
    
    // Show options field for select/radio
    $('#fieldInputType').on('change', function() {
        const type = $(this).val();
        if (type === 'select' || type === 'radio') {
            $('#optionsContainer').show();
            // Add one empty option row if list is empty
            if ($('#optionsList .option-row').length === 0) {
                addOptionRow();
            }
        } else {
            $('#optionsContainer').hide();
        }
        
        // Update validation rules visibility based on input type
        updateValidationRulesVisibility(type);
    });
    
    // Initialize validation visibility on page load
    const currentInputType = $('#fieldInputType').val();
    if (currentInputType) {
        updateValidationRulesVisibility(currentInputType);
    } else {
        // Hide all sections by default if no input type selected
        $('.validation-section').hide();
        $('#valRequiredSection').show();
    }
    
    // Add Option Button
    $(document).on('click', '#addOptionBtn', function() {
        addOptionRow('', '');
    });
    
    // Remove Option
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-row').remove();
    });
    
    // Listen to validation rule changes
    $('#valRequired, #valEmail, #valNumeric, #valAlpha, #valAlphaNum, #valUrl, #valMin, #valMax, #valMinVal, #valMaxVal').on('change input', function() {
        updateValidationPreview();
    });
    
    // Form Submission
    $('#fieldForm').on('submit', function(e) {
        e.preventDefault();
        saveField();
    });
});

// Option management functions (must be global)
function addOptionRow(value = '', label = '') {
    const optionIndex = $('#optionsList .option-row').length;
    const optionHtml = `
        <div class="option-row mb-2 p-2 border rounded bg-white">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small">Value</label>
                    <input type="text" class="form-control form-control-sm option-value" placeholder="e.g., option1" value="${value}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Label</label>
                    <input type="text" class="form-control form-control-sm option-label" placeholder="e.g., Option 1" value="${label}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-option" title="Remove option">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#optionsList').append(optionHtml);
}

function getOptionsFromForm() {
    const options = [];
    $('#optionsList .option-row').each(function() {
        const value = $(this).find('.option-value').val().trim();
        const label = $(this).find('.option-label').val().trim();
        if (value && label) {
            options.push({ value: value, label: label });
        }
    });
    return options.length > 0 ? options : null;
}

function populateOptionsFromJSON(optionsJSON) {
    $('#optionsList').empty();
    
    if (!optionsJSON) {
        // Add one empty row by default
        addOptionRow();
        return;
    }
    
    let options = [];
    if (typeof optionsJSON === 'string') {
        try {
            options = JSON.parse(optionsJSON);
        } catch (e) {
            console.error('Error parsing options JSON:', e);
            addOptionRow();
            return;
        }
    } else if (Array.isArray(optionsJSON)) {
        options = optionsJSON;
    }
    
    if (options.length === 0) {
        addOptionRow();
    } else {
        options.forEach(option => {
            const value = option.value || '';
            const label = option.label || '';
            addOptionRow(value, label);
        });
    }
}

function initializeDataTable() {
    // Only initialize if table exists and DataTable is not already initialized
    if ($.fn.DataTable.isDataTable('#fieldsTable')) {
        fieldsTable = $('#fieldsTable').DataTable();
        return;
    }
    
    fieldsTable = $('#fieldsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("field-management.data") }}',
        columns: [
            { data: 'field_key' },
            { data: 'label' },
            { data: 'input_type' },
            { data: 'field_group' },
            {
                data: 'is_required',
                render: function(data, type, row) {
                    const badgeClass = data ? 'bg-danger' : 'bg-secondary';
                    const text = data ? 'Yes' : 'No';
                    const cursor = row.is_system ? 'not-allowed' : 'pointer';
                    const title = row.is_system ? 'System field - Cannot be changed' : 'Click to toggle';
                    return `<span class="badge ${badgeClass} ${row.is_system ? '' : 'toggle-required'}" data-id="${row.id}" data-value="${data ? 1 : 0}" style="cursor: ${cursor};" title="${title}">${text}</span>`;
                }
            },
            {
                data: 'is_visible',
                render: function(data, type, row) {
                    const badgeClass = data ? 'bg-success' : 'bg-secondary';
                    const text = data ? 'Yes' : 'No';
                    const cursor = row.is_system ? 'not-allowed' : 'pointer';
                    const title = row.is_system ? 'System field - Cannot be hidden' : 'Click to toggle';
                    return `<span class="badge ${badgeClass} ${row.is_system ? '' : 'toggle-visible'}" data-id="${row.id}" data-value="${data ? 1 : 0}" style="cursor: ${cursor};" title="${title}">${text}</span>`;
                }
            },
            { data: 'sort_order' },
            {
                data: 'is_active',
                render: function(data, type, row) {
                    const badgeClass = data ? 'bg-success' : 'bg-secondary';
                    const text = data ? 'Active' : 'Inactive';
                    const cursor = row.is_system ? 'not-allowed' : 'pointer';
                    const title = row.is_system ? 'System field - Cannot be deactivated' : 'Click to toggle';
                    return `<span class="badge ${badgeClass} ${row.is_system ? '' : 'toggle-status'}" data-id="${row.id}" data-value="${data ? 1 : 0}" style="cursor: ${cursor};" title="${title}">${text}</span>`;
                }
            },
            {
                data: 'is_system',
                name: 'is_system',
                render: function(data) {
                    return data ? '<span class="badge bg-info"><i class="bx bx-lock"></i> System</span>' : '<span class="badge bg-secondary">Custom</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (row.is_system) {
                        return `<span class="badge bg-info" title="System Field - Cannot be edited or deleted"><i class='bx bx-lock'></i> System</span>`;
                    }
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-secondary edit-field" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[8, 'desc'], [7, 'desc'], [6, 'asc']] // Order by System (is_system) DESC, Status (is_active) DESC, then Sort Order ASC
    });
}

// Edit Field
$(document).on('click', '.edit-field', function() {
    const id = $(this).data('id');
    const row = fieldsTable.row($(this).closest('tr')).data();
    
    // Check if it's a system field
    if (row && row.is_system) {
        showToast('Error', 'System fields cannot be edited. These are core required fields.', 'danger');
        return;
    }
    
    $.ajax({
        url: `/field-management/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const field = response.data;
                isEditMode = true;
                $('#fieldModalTitle').text('Edit Field');
                $('#fieldId').val(field.id);
                $('#fieldKey').val(field.field_key);
                $('#fieldLabel').val(field.label);
                $('#fieldInputType').val(field.input_type).trigger('change');
                $('#fieldGroup').val(field.field_group);
                $('#fieldPlaceholder').val(field.placeholder);
                $('#fieldSortOrder').val(field.sort_order);
                $('#fieldHelpText').val(field.help_text);
                $('#fieldRequired').prop('checked', field.is_required);
                $('#fieldVisible').prop('checked', field.is_visible);
                $('#fieldActive').prop('checked', field.is_active);
                
                // Parse and populate validation rules
                parseValidationRules(field.validation_rules);
                
                // Update validation visibility based on input type
                updateValidationRulesVisibility(field.input_type);
                
                if (field.options) {
                    populateOptionsFromJSON(field.options);
                    $('#optionsContainer').show();
                } else {
                    $('#optionsList').empty();
                    addOptionRow();
                }
                
                if (field.conditional_rules) {
                    $('#fieldConditionalRules').val(JSON.stringify(field.conditional_rules, null, 2));
                    $('#conditionalContainer').show();
                }
                
                $('#fieldModal').modal('show');
            }
        }
    });
});

// Toggle Status
$(document).on('click', '.toggle-status', function() {
    const id = $(this).data('id');
    const row = fieldsTable.row($(this).closest('tr')).data();
    
    // Check if it's a system field
    if (row && row.is_system) {
        showToast('Error', 'System fields cannot be deactivated. These are core required fields.', 'danger');
        return;
    }
    
    const currentValue = $(this).data('value');
    const newValue = currentValue == 1 ? 0 : 1;
    
    $.ajax({
        url: `/field-management/${id}/toggle-status`,
        type: 'POST',
        data: { is_active: newValue },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                fieldsTable.ajax.reload(null, false); // Reload without resetting pagination
            }
        },
        error: function() {
            showToast('error', 'Error updating status');
        }
    });
});

// Toggle Visible
$(document).on('click', '.toggle-visible', function() {
    const id = $(this).data('id');
    const row = fieldsTable.row($(this).closest('tr')).data();
    
    // Check if it's a system field and trying to hide
    if (row && row.is_system) {
        const currentValue = $(this).data('value');
        if (currentValue == 1) {
            showToast('Error', 'System fields cannot be hidden. These are core required fields.', 'danger');
            return;
        }
    }
    
    const currentValue = $(this).data('value');
    const newValue = currentValue == 1 ? 0 : 1;
    
    $.ajax({
        url: `/field-management/${id}/toggle-visible`,
        type: 'POST',
        data: { is_visible: newValue },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                fieldsTable.ajax.reload(null, false); // Reload without resetting pagination
            }
        },
        error: function() {
            showToast('error', 'Error updating visibility');
        }
    });
});

// Toggle Required
$(document).on('click', '.toggle-required', function() {
    const id = $(this).data('id');
    const row = fieldsTable.row($(this).closest('tr')).data();
    
    // Check if it's a system field and trying to make optional
    if (row && row.is_system) {
        const currentValue = $(this).data('value');
        if (currentValue == 1) {
            showToast('Error', 'System fields must remain required. These are core required fields.', 'danger');
            return;
        }
    }
    
    const currentValue = $(this).data('value');
    const newValue = currentValue == 1 ? 0 : 1;
    
    $.ajax({
        url: `/field-management/${id}/toggle-required`,
        type: 'POST',
        data: { is_required: newValue },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                fieldsTable.ajax.reload(null, false); // Reload without resetting pagination
            }
        },
        error: function() {
            showToast('error', 'Error updating required status');
        }
    });
});

// Delete Field
$(document).on('click', '.delete-field', function() {
    const id = $(this).data('id');
    const row = fieldsTable.row($(this).closest('tr')).data();
    
    // Check if it's a system field
    if (row && row.is_system) {
        showToast('Error', 'System fields cannot be deleted. These are core required fields.', 'danger');
        return;
    }
    if (confirm('Are you sure you want to delete this field?')) {
        $.ajax({
            url: `/field-management/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    fieldsTable.ajax.reload();
                    showToast('success', 'Field deleted successfully');
                }
            },
            error: function() {
                showToast('error', 'Error deleting field');
            }
        });
    }
});

// Update validation preview (must be global)
function updateValidationPreview() {
    const rules = [];
    
    if ($('#valRequired').is(':checked')) {
        rules.push('required');
    }
    if ($('#valEmail').is(':checked')) {
        rules.push('email');
    }
    if ($('#valNumeric').is(':checked')) {
        rules.push('numeric');
    }
    if ($('#valAlpha').is(':checked')) {
        rules.push('alpha');
    }
    if ($('#valAlphaNum').is(':checked')) {
        rules.push('alpha_num');
    }
    if ($('#valUrl').is(':checked')) {
        rules.push('url');
    }
    
    const min = $('#valMin').val();
    if (min) {
        rules.push(`min:${min}`);
    }
    
    const max = $('#valMax').val();
    if (max) {
        rules.push(`max:${max}`);
    }
    
    const minVal = $('#valMinVal').val();
    if (minVal) {
        rules.push(`min_value:${minVal}`);
    }
    
    const maxVal = $('#valMaxVal').val();
    if (maxVal) {
        rules.push(`max_value:${maxVal}`);
    }
    
    const pattern = $('#valPattern').val();
    if (pattern) {
        rules.push(`regex:${pattern}`);
    }
    
    const custom = $('#valCustom').val();
    if (custom) {
        rules.push(custom);
    }
    
    const preview = rules.length > 0 ? rules.join('|') : '-';
    $('#validationPreview').text(preview);
}

function parseValidationRules(rulesString) {
    // Reset all validation fields
    $('#valRequired, #valEmail, #valNumeric, #valAlpha, #valAlphaNum, #valUrl').prop('checked', false);
    $('#valMin, #valMax, #valMinVal, #valMaxVal').val('');
    
    if (!rulesString) {
        updateValidationPreview();
        return;
    }
    
    // Parse the rules string (format: "required|email|max:255|min:3")
    const rules = rulesString.split('|');
    
    rules.forEach(rule => {
        rule = rule.trim();
        
        if (rule === 'required') {
            $('#valRequired').prop('checked', true);
        } else if (rule === 'email') {
            $('#valEmail').prop('checked', true);
        } else if (rule === 'numeric') {
            $('#valNumeric').prop('checked', true);
        } else if (rule === 'alpha') {
            $('#valAlpha').prop('checked', true);
        } else if (rule === 'alpha_num') {
            $('#valAlphaNum').prop('checked', true);
        } else if (rule === 'url') {
            $('#valUrl').prop('checked', true);
        } else if (rule.startsWith('min:')) {
            $('#valMin').val(rule.split(':')[1]);
        } else if (rule.startsWith('max:')) {
            $('#valMax').val(rule.split(':')[1]);
        } else if (rule.startsWith('min_value:')) {
            $('#valMinVal').val(rule.split(':')[1]);
        } else if (rule.startsWith('max_value:')) {
            $('#valMaxVal').val(rule.split(':')[1]);
        }
    });
    
    updateValidationPreview();
}

// Function to show/hide validation rules based on input type (must be global)
function updateValidationRulesVisibility(inputType) {
    // Hide all validation sections first
    $('.validation-section').hide();
    
    // Show relevant sections based on input type
    switch(inputType) {
        case 'email':
            $('#valRequiredSection').show();
            $('#valEmailSection').show();
            $('#valLengthSection').show();
            break;
        case 'number':
            $('#valRequiredSection').show();
            $('#valNumericSection').show();
            $('#valNumberRangeSection').show();
            break;
        case 'tel':
            $('#valRequiredSection').show();
            $('#valNumericSection').show();
            $('#valLengthSection').show();
            break;
        case 'text':
        case 'textarea':
            $('#valRequiredSection').show();
            $('#valCharacterTypeSection').show();
            $('#valLengthSection').show();
            break;
        case 'password':
            $('#valRequiredSection').show();
            $('#valCharacterTypeSection').show();
            $('#valLengthSection').show();
            break;
        case 'url':
            $('#valRequiredSection').show();
            $('#valUrlSection').show();
            break;
        case 'file':
            $('#valRequiredSection').show();
            break;
        case 'date':
            $('#valRequiredSection').show();
            break;
        case 'select':
        case 'radio':
        case 'checkbox':
            $('#valRequiredSection').show();
            break;
        default:
            // Show all for unknown types
            $('.validation-section').show();
    }
}

function buildValidationRules() {
    const rules = [];
    
    if ($('#valRequired').is(':checked')) {
        rules.push('required');
    }
    if ($('#valEmail').is(':checked')) {
        rules.push('email');
    }
    if ($('#valNumeric').is(':checked')) {
        rules.push('numeric');
    }
    if ($('#valAlpha').is(':checked')) {
        rules.push('alpha');
    }
    if ($('#valAlphaNum').is(':checked')) {
        rules.push('alpha_num');
    }
    if ($('#valUrl').is(':checked')) {
        rules.push('url');
    }
    
    const min = $('#valMin').val();
    if (min) {
        rules.push(`min:${min}`);
    }
    
    const max = $('#valMax').val();
    if (max) {
        rules.push(`max:${max}`);
    }
    
    const minVal = $('#valMinVal').val();
    if (minVal) {
        rules.push(`min_value:${minVal}`);
    }
    
    const maxVal = $('#valMaxVal').val();
    if (maxVal) {
        rules.push(`max_value:${maxVal}`);
    }
    
    return rules.length > 0 ? rules.join('|') : null;
}

function saveField() {
    const formData = {
        field_key: $('#fieldKey').val(),
        label: $('#fieldLabel').val(),
        input_type: $('#fieldInputType').val(),
        field_group: $('#fieldGroup').val(),
        placeholder: $('#fieldPlaceholder').val(),
        sort_order: parseInt($('#fieldSortOrder').val()) || 0,
        validation_rules: buildValidationRules(),
        help_text: $('#fieldHelpText').val(),
        is_required: $('#fieldRequired').is(':checked') ? true : false,
        is_visible: $('#fieldVisible').is(':checked') ? true : false,
        is_active: $('#fieldActive').is(':checked') ? true : false,
    };
    
    // Get options from form
    const options = getOptionsFromForm();
    if (options) {
        formData.options = options;
    }
    
    // Parse conditional rules if provided
    const conditionalText = $('#fieldConditionalRules').val();
    if (conditionalText) {
        try {
            formData.conditional_rules = JSON.parse(conditionalText);
        } catch (e) {
            showToast('error', 'Invalid JSON format for conditional rules');
            return;
        }
    }
    
    // Only allow updates, not creation
    const url = `/field-management/${$('#fieldId').val()}`;
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                fieldsTable.ajax.reload();
                $('#fieldModal').modal('hide');
                showToast('success', 'Field updated successfully');
            } else {
                showToast('error', 'Error: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = 'Validation errors: ';
                Object.keys(errors).forEach(key => {
                    errorMsg += `${errors[key][0]}; `;
                });
                showToast('error', errorMsg.trim());
            } else {
                showToast('error', 'Error saving field');
            }
        }
    });
}

function loadFormPreview() {
    $.ajax({
        url: '{{ route("field-management.all-fields") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                formFields = response.data;
                renderFormPreview();
            }
        },
        error: function() {
            $('#formPreviewContainer').html('<div class="alert alert-danger">Error loading form preview</div>');
        }
    });
}

function renderFormPreview() {
    const container = $('#formPreviewContainer');
    container.empty();
    
    // Separate system fields from non-system fields
    const systemFields = formFields.filter(field => field.is_system === true);
    const nonSystemFields = formFields.filter(field => !field.is_system || field.is_system === false);
    
    // Group system fields by field_group
    const systemFieldGroups = {};
    systemFields.forEach(field => {
        const group = field.field_group || 'other';
        if (!systemFieldGroups[group]) {
            systemFieldGroups[group] = [];
        }
        systemFieldGroups[group].push(field);
    });
    
    // Group non-system fields by field_group
    const nonSystemFieldGroups = {};
    nonSystemFields.forEach(field => {
        const group = field.field_group || 'other';
        if (!nonSystemFieldGroups[group]) {
            nonSystemFieldGroups[group] = [];
        }
        nonSystemFieldGroups[group].push(field);
    });
    
    // Sort fields within each group by sort_order
    Object.keys(systemFieldGroups).forEach(group => {
        systemFieldGroups[group].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    });
    
    Object.keys(nonSystemFieldGroups).forEach(group => {
        nonSystemFieldGroups[group].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    });
    
    // Predefined group order
    const groupOrder = ['basic_info', 'credentials', 'address', 'business', 'preferences', 'qol', 'internal', 'other'];
    
    // Sort system field groups: first by predefined order, then alphabetically for remaining groups
    const systemGroupKeys = Object.keys(systemFieldGroups).sort((a, b) => {
        const aIndex = groupOrder.indexOf(a);
        const bIndex = groupOrder.indexOf(b);
        if (aIndex !== -1 && bIndex !== -1) return aIndex - bIndex;
        if (aIndex !== -1) return -1;
        if (bIndex !== -1) return 1;
        return a.localeCompare(b);
    });
    
    // Sort non-system field groups: first by predefined order, then alphabetically for remaining groups
    const nonSystemGroupKeys = Object.keys(nonSystemFieldGroups).sort((a, b) => {
        const aIndex = groupOrder.indexOf(a);
        const bIndex = groupOrder.indexOf(b);
        if (aIndex !== -1 && bIndex !== -1) return aIndex - bIndex;
        if (aIndex !== -1) return -1;
        if (bIndex !== -1) return 1;
        return a.localeCompare(b);
    });
    
    // Render system field groups first
    systemGroupKeys.forEach(groupKey => {
        renderFieldGroup(container, groupKey, systemFieldGroups[groupKey]);
    });
    
    // Then render non-system field groups
    nonSystemGroupKeys.forEach(groupKey => {
        renderFieldGroup(container, groupKey, nonSystemFieldGroups[groupKey]);
    });
    
    // Initialize sortable for each group
    initializeSortable();
}

function renderFieldGroup(container, groupKey, fields) {
    const groupTitle = getGroupTitle(groupKey);
    const groupId = `group-${groupKey}`;
    
    const groupHtml = `
        <div class="form-preview-group" data-group="${groupKey}" id="${groupId}">
            <div class="form-preview-group-title">
                <i class='bx bx-menu drag-handle-group'></i>
                <span>${groupTitle}</span>
            </div>
            <div class="fields-list" id="fields-${groupKey}">
                ${fields.map(field => renderDraggableField(field)).join('')}
            </div>
        </div>
    `;
    
    container.append(groupHtml);
}

function renderDraggableField(field) {
    const requiredBadge = field.is_required ? '<span class="badge bg-danger">Required</span>' : '';
    const visibleBadge = field.is_visible ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Hidden</span>';
    const activeBadge = field.is_active ? '<span class="badge bg-primary">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
    const inactiveClass = !field.is_active ? 'field-inactive' : '';
    
    return `
        <div class="draggable-field ${inactiveClass}" data-field-id="${field.field_key}" data-field-group="${field.field_group || 'other'}" data-sort-order="${field.sort_order}">
            <i class='bx bx-menu drag-handle'></i>
            <div class="field-preview-info">
                <div class="field-preview-label">${field.label}</div>
                <div class="field-preview-meta">
                    <span>${field.field_key}</span> • 
                    <span>${field.input_type}</span>
                </div>
            </div>
            <div class="field-preview-badges">
                ${activeBadge}
                ${visibleBadge}
                ${requiredBadge}
            </div>
        </div>
    `;
}

function getGroupTitle(groupKey) {
    const titles = {
        'basic_info': 'Customer Basics',
        'credentials': 'Account Credentials',
        'address': 'Address Details',
        'business': 'Business Information',
        'preferences': 'Preferences',
        'qol': 'Quality-of-Life Fields',
        'internal': 'Internal Use',
        'other': 'Other Information'
    };
    return titles[groupKey] || groupKey.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function initializeSortable() {
    // Destroy existing instances
    Object.keys(sortableInstances).forEach(key => {
        if (sortableInstances[key]) {
            sortableInstances[key].destroy();
        }
    });
    sortableInstances = {};
    
    // Initialize sortable for each group
    $('.fields-list').each(function() {
        const groupKey = $(this).closest('.form-preview-group').data('group');
        const groupId = `fields-${groupKey}`;
        
        if (typeof Sortable !== 'undefined') {
            sortableInstances[groupId] = Sortable.create(document.getElementById(groupId), {
                group: 'fields', // Allow dragging between groups
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    updateFieldOrder(evt);
                }
            });
        }
    });
}

function updateFieldOrder(evt) {
    const fieldElement = $(evt.item);
    const fieldKey = fieldElement.data('field-id');
    const newGroup = fieldElement.closest('.form-preview-group').data('group');
    const newIndex = evt.newIndex;
    
    // Get all fields in the new group after the move
    const newGroupContainer = fieldElement.closest('.fields-list');
    const fieldsInGroup = newGroupContainer.find('.draggable-field');
    
    // Calculate new sort_order based on position
    let newSortOrder = (newIndex + 1) * 10; // Use increments of 10
    
    // Update the field that was moved
    $.ajax({
        url: `/field-management/${fieldKey}/update-order`,
        type: 'POST',
        data: {
            field_group: newGroup,
            sort_order: newSortOrder
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Update other fields in the group
                fieldsInGroup.each(function(index) {
                    const $field = $(this);
                    const currentFieldKey = $field.data('field-id');
                    if (currentFieldKey !== fieldKey) {
                        const sortOrder = (index + 1) * 10;
                        $.ajax({
                            url: `/field-management/${currentFieldKey}/update-order`,
                            type: 'POST',
                            data: {
                                sort_order: sortOrder
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            async: false
                        });
                    }
                });
                
                // Reload preview and table after a short delay
                setTimeout(function() {
                    loadFormPreview();
                    fieldsTable.ajax.reload(null, false);
                }, 300);
            }
        },
        error: function() {
            showToast('error', 'Error updating field order');
            // Reload preview to revert changes
            loadFormPreview();
        }
    });
}

// Reload preview when toggles are clicked
$(document).on('click', '.toggle-status, .toggle-visible, .toggle-required', function() {
    setTimeout(function() {
        loadFormPreview();
    }, 500);
});

// Toast notification function
function showToast(type, message) {
    const toast = $('#toast');
    
    // Check if toast element exists
    if (!toast.length || !toast[0]) {
        console.error('Toast element not found');
        alert(message); // Fallback to alert
        return;
    }
    
    const toastBody = toast.find('.toast-body');
    const toastHeader = toast.find('.toast-header');

    if (toastBody.length) {
        toastBody.text(message);
    }

    if(type === 'success') {
        if (toastHeader.length) {
            toastHeader.html('<i class=\'bx bx-check-circle text-success me-2\'></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
        }
    } else {
        if (toastHeader.length) {
            toastHeader.html('<i class=\'bx bx-x-circle text-danger me-2\'></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
        }
    }

    try {
        const bsToast = new bootstrap.Toast(toast[0], {
            autohide: true,
            delay: 5000
        });
        bsToast.show();
    } catch (e) {
        console.error('Error showing toast:', e);
        alert(message); // Fallback to alert
    }
}
</script>
@endpush

