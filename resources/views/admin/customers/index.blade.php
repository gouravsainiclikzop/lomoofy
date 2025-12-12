@extends('layouts.admin')

@section('title', 'Customers')

@push('styles')
<style>
.field-group {
    margin-bottom: 1.5rem;
}
.field-group-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    /* border-bottom: 1px solid #e9ecef; */
    color: #495057;
}
.conditional-field {
    display: none;
}
.field-help-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customers</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Customers</h1>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Add Customer
                </button>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                <table class="table table-hover" id="customersTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Created</th>
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

<!-- Create/Edit Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="customerId" name="id">
                    <div id="customerFormFields">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading form fields...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading form fields...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveCustomerBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
@endsection

@push('styles')
<style>
    /* Toast z-index fix */
    .toast-container {
        z-index: 9999 !important;
    }
    .toast {
        z-index: 10000 !important;
    }
</style>
@endpush

@push('scripts')
<script>
let customersTable;
let formFields = [];
let fieldGroups = {};
let isEditMode = false;
let editingCustomerData = null;

$(document).ready(function() {
    initializeDataTable();
    
    // Search functionality
    $('#table-search').on('keyup', function() {
        customersTable.search(this.value).draw();
    });
    
    // Modal events
    $('#customerModal').on('show.bs.modal', function() {
        loadFormFields();
    });
    
    $('#customerModal').on('hidden.bs.modal', function() {
        $('#customerForm')[0].reset();
        $('#customerId').val('');
        isEditMode = false;
        editingCustomerData = null;
        $('#customerModalLabel').text('Add New Customer');
    });
    
    // Add Customer Button
    $('#addCustomerBtn').on('click', function() {
        isEditMode = false;
        editingCustomerData = null;
        $('#customerModalLabel').text('Add New Customer');
    });
    
    // Form Submission
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        saveCustomer();
    });
});

function initializeDataTable() {
    customersTable = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("customers.data") }}',
        columns: [
            {
                data: 'profile_image',
                orderable: false,
                searchable: false,
                render: function(data) {
                    if (data) {
                        return '<img src="/storage/' + data + '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Customer">';
                    }
                    return '<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>';
                }
            },
            { data: 'full_name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'address' },
            {
                data: 'is_active',
                render: function(data) {
                    return data ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>';
                }
            },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-secondary edit-customer" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-customer" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[6, 'desc']]
    });
}

function loadFormFields() {
    $.ajax({
        url: '{{ route("customers.fields") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                formFields = response.data;
                renderFormFields();
                
                // If editing, populate form
                if (isEditMode && editingCustomerData) {
                    populateForm(editingCustomerData);
                }
            } else {
                $('#customerFormFields').html('<div class="alert alert-danger">Failed to load form fields</div>');
            }
        },
        error: function() {
            $('#customerFormFields').html('<div class="alert alert-danger">Error loading form fields</div>');
        }
    });
}

function renderFormFields() {
    const container = $('#customerFormFields');
    container.empty();
    
    // Group fields by field_group
    fieldGroups = {};
    formFields.forEach(field => {
        const group = field.field_group || 'other';
        if (!fieldGroups[group]) {
            fieldGroups[group] = [];
        }
        fieldGroups[group].push(field);
    });
    
    // Render each group
    const groupOrder = ['basic_info', 'credentials', 'address', 'business', 'preferences', 'qol', 'internal', 'other'];
    
    groupOrder.forEach(groupKey => {
        if (fieldGroups[groupKey] && fieldGroups[groupKey].length > 0) {
            const groupTitle = getGroupTitle(groupKey);
            const groupHtml = `
                <div class="field-group" data-group="${groupKey}">
                    <h6 class="field-group-title">${groupTitle}</h6>
                    <div class="row g-3">
                        ${renderGroupFields(fieldGroups[groupKey])}
                    </div>
                </div>
            `;
            container.append(groupHtml);
        }
    });
    
    // Render any remaining groups
    Object.keys(fieldGroups).forEach(groupKey => {
        if (!groupOrder.includes(groupKey)) {
            const groupTitle = getGroupTitle(groupKey);
            const groupHtml = `
                <div class="field-group" data-group="${groupKey}">
                    <h6 class="field-group-title">${groupTitle}</h6>
                    <div class="row g-3">
                        ${renderGroupFields(fieldGroups[groupKey])}
                    </div>
                </div>
            `;
            container.append(groupHtml);
        }
    });
    
    // Initialize conditional fields
    initializeConditionalFields();
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

function renderGroupFields(fields) {
    let html = '';
    fields.forEach(field => {
        html += renderField(field);
    });
    return html;
}

function renderField(field) {
    const fieldId = `field_${field.field_key}`;
    const required = field.is_required ? '<span class="text-danger">*</span>' : '';
    const helpText = field.help_text ? `<div class="field-help-text">${field.help_text}</div>` : '';
    const conditionalClass = field.conditional_rules ? 'conditional-field' : '';
    const conditionalAttrs = field.conditional_rules ? `data-conditional="${JSON.stringify(field.conditional_rules)}"` : '';
    
    let fieldHtml = '';
    const colClass = getColClass(field.input_type);
    
    switch(field.input_type) {
        case 'text':
        case 'email':
        case 'tel':
        case 'number':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="${field.input_type}" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'password':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="password" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'textarea':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <textarea class="form-control" 
                              id="${fieldId}" 
                              name="${field.field_key}" 
                              rows="3"
                              placeholder="${field.placeholder || ''}"></textarea>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'select':
            const options = field.options || [];
            let optionsHtml = '<option value="">Select ' + field.label + '</option>';
            options.forEach(option => {
                const value = typeof option === 'object' ? option.value : option;
                const label = typeof option === 'object' ? option.label : option;
                optionsHtml += `<option value="${value}">${label}</option>`;
            });
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <select class="form-select" 
                            id="${fieldId}" 
                            name="${field.field_key}">
                        ${optionsHtml}
                    </select>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'radio':
            const radioOptions = field.options || [];
            let radioHtml = '';
            radioOptions.forEach((option, index) => {
                const value = typeof option === 'object' ? option.value : option;
                const label = typeof option === 'object' ? option.label : option;
                const radioId = `${fieldId}_${index}`;
                radioHtml += `
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="radio" 
                               id="${radioId}" 
                               name="${field.field_key}" 
                               value="${value}">
                        <label class="form-check-label" for="${radioId}">${label}</label>
                    </div>
                `;
            });
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label class="form-label">${field.label} ${required}</label>
                    ${radioHtml}
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'checkbox':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="${fieldId}" 
                               name="${field.field_key}"
                               value="1">
                        <label class="form-check-label" for="${fieldId}">${field.label}</label>
                    </div>
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'date':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="date" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        case 'file':
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="file" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
            break;
        default:
            fieldHtml = `
                <div class="${colClass} ${conditionalClass}" ${conditionalAttrs}>
                    <label for="${fieldId}" class="form-label">${field.label} ${required}</label>
                    <input type="text" 
                           class="form-control" 
                           id="${fieldId}" 
                           name="${field.field_key}" 
                           placeholder="${field.placeholder || ''}">
                    ${helpText}
                    <div class="invalid-feedback"></div>
                </div>
            `;
    }
    
    return fieldHtml;
}

function getColClass(inputType) {
    if (inputType === 'textarea') {
        return 'col-12';
    }
    return 'col-md-6';
}

function initializeConditionalFields() {
    $('.conditional-field').each(function() {
        const $field = $(this);
        const rules = $field.data('conditional');
        if (rules) {
            checkConditionalField($field, rules);
            
            if (rules.depends_on) {
                $(document).on('change', `[name="${rules.depends_on}"]`, function() {
                    checkConditionalField($field, rules);
                });
            }
        }
    });
}

function checkConditionalField($field, rules) {
    if (rules.depends_on) {
        const dependentValue = $(`[name="${rules.depends_on}"]`).val();
        const showWhen = rules.show_when;
        
        if (showWhen) {
            if (Array.isArray(showWhen)) {
                if (showWhen.includes(dependentValue)) {
                    $field.show();
                } else {
                    $field.hide();
                }
            } else if (dependentValue === showWhen) {
                $field.show();
            } else {
                $field.hide();
            }
        }
    }
}

// Edit Customer
$(document).on('click', '.edit-customer', function() {
    const id = $(this).data('id');
    $.ajax({
        url: `/customers/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                isEditMode = true;
                editingCustomerData = response.data;
                $('#customerModalLabel').text('Edit Customer');
                $('#customerId').val(response.data.id);
                $('#customerModal').modal('show');
            }
        },
        error: function() {
            showToast('error', 'Error loading customer data');
        }
    });
});

function populateForm(customer) {
    // Populate basic fields
    Object.keys(customer).forEach(key => {
        const field = $(`#field_${key}`);
        if (field.length) {
            if (field.attr('type') === 'checkbox') {
                field.prop('checked', customer[key] == 1 || customer[key] === true);
            } else if (field.attr('type') === 'radio') {
                $(`input[name="${key}"][value="${customer[key]}"]`).prop('checked', true);
            } else {
                field.val(customer[key]);
            }
        }
    });
    
    // Populate address if exists
    if (customer.addresses && customer.addresses.length > 0) {
        const address = customer.addresses[0];
        $('#field_address_type').val(address.address_type);
        $('#field_full_address').val(address.full_address);
        $('#field_landmark').val(address.landmark);
        $('#field_state').val(address.state);
        $('#field_city').val(address.city);
        $('#field_pincode').val(address.pincode);
        $('#field_delivery_instructions').val(address.delivery_instructions);
        $('#field_make_default_address').prop('checked', address.is_default);
    }
    
    // Populate custom data
    if (customer.custom_data) {
        Object.keys(customer.custom_data).forEach(key => {
            const field = $(`#field_${key}`);
            if (field.length) {
                field.val(customer.custom_data[key]);
            }
        });
    }
    
    // Trigger conditional field checks
    $('[name]').trigger('change');
}

function saveCustomer() {
    const formData = new FormData($('#customerForm')[0]);
    const url = isEditMode ? `/customers/${$('#customerId').val()}` : '{{ route("customers.store") }}';
    const method = isEditMode ? 'POST' : 'POST';
    
    // Add _method for PUT if editing
    if (isEditMode) {
        formData.append('_method', 'PUT');
    }
    
    $('#saveCustomerBtn').prop('disabled', true);
    $('#saveCustomerBtn .spinner-border').removeClass('d-none');
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                customersTable.ajax.reload();
                $('#customerModal').modal('hide');
                showToast('success', isEditMode ? 'Customer updated successfully!' : 'Customer created successfully!');
            } else {
                showErrors(response.errors || {});
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                showErrors(xhr.responseJSON.errors || {});
            } else {
                showToast('error', 'Error saving customer');
            }
        },
        complete: function() {
            $('#saveCustomerBtn').prop('disabled', false);
            $('#saveCustomerBtn .spinner-border').addClass('d-none');
        }
    });
}

function showErrors(errors) {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    Object.keys(errors).forEach(key => {
        const field = $(`#field_${key}, [name="${key}"]`).first();
        field.addClass('is-invalid');
        field.siblings('.invalid-feedback').text(errors[key][0]);
    });
}

// Delete customer
$(document).on('click', '.delete-customer', function() {
    const id = $(this).data('id');
    if (confirm('Are you sure you want to delete this customer?')) {
        $.ajax({
            url: `/customers/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    customersTable.ajax.reload();
                    showToast('success', 'Customer deleted successfully');
                }
            },
            error: function() {
                showToast('error', 'Error deleting customer');
            }
        });
    }
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
