@extends('layouts.admin')

@section('title', 'Contact Messages')

@push('styles')
<style>
    .unread-contact {
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .read-contact {
        background-color: #ffffff;
    }
    .message-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
                        <li class="breadcrumb-item active" aria-current="page">Contact Messages</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Contact Messages</h1>
            </div>
        </div>

        <div id="alertContainer"></div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Status</label>
                        <select class="form-select" id="filterReadStatus">
                            <option value="">All Messages</option>
                            <option value="false">Unread</option>
                            <option value="true">Read</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <i class="fas fa-redo"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts Table -->
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover" id="contactsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
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

<!-- View Contact Modal -->
<div class="modal fade" id="viewContactModal" tabindex="-1" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">Contact Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contactDetails">
                <!-- Contact details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const contactPermissions = {
    view: @json(auth()->user()->hasPermission('contact_messages.view')),
    update: @json(auth()->user()->hasPermission('contact_messages.update')),
    delete: @json(auth()->user()->hasPermission('contact_messages.delete'))
};

let contactsTable;

$(document).ready(function() {
    initializeDataTable();

    // Filter by read status
    $('#filterReadStatus').on('change', function() {
        contactsTable.ajax.reload();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterReadStatus').val('');
        contactsTable.ajax.reload();
    });

    // View contact
    $(document).on('click', '.view-contact', function() {
        if (!contactPermissions.view) {
            alert('You do not have permission to view contact messages.');
            return;
        }
        const contactId = $(this).data('id');
        viewContact(contactId);
    });

    // Toggle read status
    $(document).on('click', '.toggle-read', function() {
        if (!contactPermissions.update) {
            alert('You do not have permission to update contact messages.');
            return;
        }
        const contactId = $(this).data('id');
        toggleReadStatus(contactId);
    });

    // Delete contact
    $(document).on('click', '.delete-contact', function() {
        if (!contactPermissions.delete) {
            alert('You do not have permission to delete contact messages.');
            return;
        }
        const contactId = $(this).data('id');
        deleteContact(contactId);
    });
});

function initializeDataTable() {
    contactsTable = $('#contactsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("contacts.data") }}',
            data: function(d) {
                d.is_read = $('#filterReadStatus').val();
            }
        },
        columns: [
            { 
                data: 'name', 
                name: 'name',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'email', 
                name: 'email',
                render: function(data) {
                    return `<a href="mailto:${data}">${data}</a>`;
                }
            },
            { 
                data: 'subject', 
                name: 'subject'
            },
            { 
                data: 'message', 
                name: 'message',
                render: function(data) {
                    return `<div class="message-preview" title="${data}">${data}</div>`;
                }
            },
            { 
                data: 'is_read', 
                name: 'is_read',
                render: function(data) {
                    if (data) {
                        return '<span class="badge bg-success">Read</span>';
                    } else {
                        return '<span class="badge bg-warning">Unread</span>';
                    }
                }
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString();
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="d-flex gap-2">';
                    if (contactPermissions.view) {
                        actions += `<button class="btn btn-sm btn-outline-primary view-contact" data-id="${row.id}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>`;
                    }
                    if (contactPermissions.update) {
                        actions += `<button class="btn btn-sm btn-outline-info toggle-read" data-id="${row.id}" title="${row.is_read ? 'Mark as Unread' : 'Mark as Read'}">
                                <i class="fas fa-${row.is_read ? 'envelope' : 'envelope-open'}"></i>
                            </button>`;
                    }
                    if (contactPermissions.delete) {
                        actions += `<button class="btn btn-sm btn-outline-danger delete-contact" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }
                    actions += '</div>';
                    return actions || '<span class="text-muted">-</span>';
                }
            }
        ],
        order: [[5, 'desc']], // Order by created_at desc
        rowCallback: function(row, data) {
            if (!data.is_read) {
                $(row).addClass('unread-contact');
            } else {
                $(row).addClass('read-contact');
            }
        }
    });
}

function viewContact(id) {
    $.ajax({
        url: '{{ route("contacts.show", ":id") }}'.replace(':id', id),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const contact = response.data;
                const html = `
                    <div class="mb-3">
                        <strong>Name:</strong> ${contact.name}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <a href="mailto:${contact.email}">${contact.email}</a>
                    </div>
                    <div class="mb-3">
                        <strong>Subject:</strong> ${contact.subject || 'No Subject'}
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <div class="border p-3 mt-2" style="background-color: #f8f9fa; border-radius: 0.25rem;">
                            ${contact.message.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong> ${new Date(contact.created_at).toLocaleString()}
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        ${contact.is_read ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning">Unread</span>'}
                    </div>
                `;
                $('#contactDetails').html(html);
                $('#viewContactModal').modal('show');
                 
                contactsTable.ajax.reload();
            }
        },
        error: function() {
            showToast('Error', 'Failed to load contact details.', 'danger');
        }
    });
}

function toggleReadStatus(id) {
    $.ajax({
        url: '{{ route("contacts.toggle-read", ":id") }}'.replace(':id', id),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message, 'success');
                contactsTable.ajax.reload();
            }
        },
        error: function() {
            showToast('Error', 'Failed to update status.', 'danger');
        }
    });
}

function deleteContact(id) {
    if (!confirm('Are you sure you want to delete this contact message?')) {
        return;
    }

    $.ajax({
        url: '{{ route("contacts.destroy", ":id") }}'.replace(':id', id),
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast('Success', response.message, 'success');
                contactsTable.ajax.reload();
            }
        },
        error: function() {
            showToast('Error', 'Failed to delete contact.', 'danger');
        }
    });
}

function showToast(title, message, type) { 
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else { 
        alert(message);
    }
}
</script>
@endpush
