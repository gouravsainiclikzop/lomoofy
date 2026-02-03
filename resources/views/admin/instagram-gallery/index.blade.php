@extends('layouts.admin')

@section('title', 'Instagram Gallery')

@push('styles')
<style>
    .image-preview-sm { max-width: 60px; max-height: 60px; border-radius: 4px; border: 1px solid #dee2e6; }
    .gallery-item-row { border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: #f8f9fa; }
    .sortable-ghost { opacity: 0.4; background: #f8f9fa; }
    .drag-handle { cursor: grab; color: #6c757d; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="py-5">
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Instagram Gallery</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Instagram Gallery</h1>
            </div>
        </div>

        <!-- Add More: New gallery items in a single operation -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add gallery items</h5>
                <small class="text-muted">Add one or more items below, then click Save all.</small>
            </div>
            <div class="card-body">
                <form id="bulkGalleryForm">
                    @csrf
                    <div id="bulkItemsContainer">
                        <div class="gallery-item-row bulk-item-row" data-index="0">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Image <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control form-control-sm bulk-image" name="items[0][thumbnail_image]" accept="image/*" required>
                                    <small class="text-muted">recommended size: 500px*500px</small>
                                    <div class="bulk-preview mt-1" style="display:none;"><img src="" alt="" class="image-preview-sm"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Instagram link</label>
                                    <input type="url" class="form-control form-control-sm" name="items[0][instagram_link]" placeholder="https://instagram.com/...">
                                        <small> &nbsp;</small>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="items[0][is_active]" value="1" id="bulkActive0" checked>  
                                        <label class="form-check-label" for="bulkActive0">Active</label> 
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-bulk-row" data-index="0" style="display:none;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addMoreRowsBtn"><i class="fas fa-plus"></i> Add More</button>
                        <button type="submit" class="btn btn-primary btn-sm ms-2" id="saveBulkBtn"><i class="fas fa-save"></i> Save all</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing gallery items -->
        <div class="card">
            <div class="p-4">
                <input type="text" placeholder="Search by link..." class="form-control form-control--search" id="tableSearch"/>
            </div>
            <div class="sa-divider"></div>
            <div class="table-responsive">
                <table class="table table-hover" id="galleryTable">
                    <thead>
                        <tr>
                            <th width="40"><i class="bx bx-menu drag-handle"></i></th>
                            <th width="80">Image</th>
                            <th>Instagram link</th>
                            <th width="100">Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="galleryTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit single item modal -->
<div class="modal fade" id="editGalleryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit gallery item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editAlertContainer"></div>
                <form id="editGalleryForm" enctype="multipart/form-data">
                    <input type="hidden" id="editItemId" name="id">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="editThumbnail" name="thumbnail_image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image.</small>
                        <div class="mt-2" id="editCurrentImageWrap" style="display:none;">
                            <img id="editCurrentImage" src="" alt="" class="image-preview-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instagram link</label>
                        <input type="url" class="form-control" id="editInstagramLink" name="instagram_link" placeholder="https://instagram.com/...">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" value="1">
                        <label class="form-check-label" for="editIsActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let bulkIndex = 1;
    const csrf = $('meta[name="csrf-token"]').attr('content');

    function showAlert(type, msg) {
        const alert = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $('.container-fluid').prepend(alert);
        setTimeout(function() { $('.alert').first().fadeOut(function() { $(this).remove(); }); }, 4000);
    }

    function loadGallery() {
        const search = $('#tableSearch').val();
        $('#galleryTableBody').html('<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>');

        $.ajax({
            url: '{{ route("instagram-gallery.data") }}',
            method: 'GET',
            data: { search: search },
            success: function(res) {
                if (res.success && res.data.length) {
                    let html = '';
                    res.data.forEach(function(item) {
                        html += '<tr data-id="' + item.id + '">' +
                            '<td><i class="bx bx-menu drag-handle"></i></td>' +
                            '<td>' + (item.thumbnail_image ? '<img src="' + item.thumbnail_image + '" class="image-preview-sm" alt="">' : '-') + '</td>' +
                            '<td><a href="' + (item.instagram_link || '#') + '" target="_blank" rel="noopener">' + (item.instagram_link || '-') + '</a></td>' +
                            '<td><div class="form-check form-switch"><input class="form-check-input status-toggle" type="checkbox" ' + (item.is_active ? 'checked' : '') + ' data-id="' + item.id + '"><label class="form-check-label">Active</label></div></td>' +
                            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-item" data-id="' + item.id + '">Edit</button> <button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' + item.id + '">Delete</button></td>' +
                            '</tr>';
                    });
                    $('#galleryTableBody').html(html);
                    initSortable();
                } else {
                    $('#galleryTableBody').html('<tr><td colspan="5" class="text-center py-5">No gallery items yet. Add some above.</td></tr>');
                }
            },
            error: function() {
                $('#galleryTableBody').html('<tr><td colspan="5" class="text-center py-5 text-danger">Failed to load.</td></tr>');
            }
        });
    }

    function initSortable() {
        const tbody = document.getElementById('galleryTableBody');
        if (!tbody || typeof Sortable === 'undefined') return;
        if (tbody._sortable) tbody._sortable.destroy();
        tbody._sortable = Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                const items = [];
                $('#galleryTableBody tr[data-id]').each(function(i) {
                    items.push({ id: $(this).data('id'), sort_order: i + 1 });
                });
                $.ajax({
                    url: '{{ route("instagram-gallery.update-sort-order") }}',
                    method: 'POST',
                    data: { items: items, _token: csrf },
                    success: function(res) { if (res.success) showAlert('success', res.message); }
                });
            }
        });
    }

    // Add More rows
    $('#addMoreRowsBtn').on('click', function() {
        const row = '<div class="gallery-item-row bulk-item-row" data-index="' + bulkIndex + '">' +
            '<div class="row g-3 align-items-end">' +
            '<div class="col-md-3"><label class="form-label">Image <span class="text-danger">*</span></label>' +
            '<input type="file" class="form-control form-control-sm bulk-image" name="items[' + bulkIndex + '][thumbnail_image]" accept="image/*" required>' +
            '<div class="bulk-preview mt-1" style="display:none;"><img src="" alt="" class="image-preview-sm"></div></div>' +
            '<div class="col-md-4"><label class="form-label">Instagram link</label>' +
            '<input type="url" class="form-control form-control-sm" name="items[' + bulkIndex + '][instagram_link]" placeholder="https://instagram.com/..."></div>' +
            '<div class="col-md-2"><div class="form-check form-switch">' +
            '<input class="form-check-input" type="checkbox" name="items[' + bulkIndex + '][is_active]" value="1" id="bulkActive' + bulkIndex + '" checked>' +
            '<label class="form-check-label" for="bulkActive' + bulkIndex + '">Active</label></div></div>' +
            '<div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-bulk-row" data-index="' + bulkIndex + '">Remove</button></div>' +
            '</div></div>';
        $('#bulkItemsContainer').append(row);
        bulkIndex++;
        $('#bulkItemsContainer .bulk-item-row').each(function(i) {
            $(this).find('.remove-bulk-row').toggle(i > 0);
        });
    });

    $(document).on('click', '.remove-bulk-row', function() {
        $(this).closest('.bulk-item-row').remove();
        $('#bulkItemsContainer .bulk-item-row').each(function(i) {
            $(this).attr('data-index', i).find('.remove-bulk-row').toggle(i > 0);
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) $(this).attr('name', name.replace(/\[\d+\]/, '[' + i + ']'));
            });
        });
    });

    $(document).on('change', '.bulk-image', function() {
        const file = this.files[0];
        const $row = $(this).closest('.bulk-item-row');
        const $preview = $row.find('.bulk-preview img');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { $preview.attr('src', e.target.result); $row.find('.bulk-preview').show(); };
            reader.readAsDataURL(file);
        } else {
            $preview.attr('src', ''); $row.find('.bulk-preview').hide();
        }
    });

    // Save bulk (only rows that have a file selected)
    $('#bulkGalleryForm').on('submit', function(e) {
        e.preventDefault();
        const $rows = $('#bulkItemsContainer .bulk-item-row');
        const formData = new FormData();
        formData.append('_token', csrf);
        let count = 0;
        $rows.each(function(i) {
            const $file = $(this).find('.bulk-image')[0];
            if ($file && $file.files && $file.files[0]) {
                formData.append('items[' + count + '][thumbnail_image]', $file.files[0]);
                formData.append('items[' + count + '][instagram_link]', $(this).find('input[name*="instagram_link"]').val() || '');
                formData.append('items[' + count + '][is_active]', $(this).find('input[type="checkbox"]').is(':checked') ? '1' : '0');
                count++;
            }
        });
        if (count === 0) {
            showAlert('warning', 'Please add at least one image.');
            return;
        }
        $('#saveBulkBtn').prop('disabled', true);
        $.ajax({
            url: '{{ route("instagram-gallery.store-bulk") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    showAlert('success', res.message);
                    $('#bulkGalleryForm')[0].reset();
                    $('#bulkItemsContainer .bulk-preview').hide();
                    $('#bulkItemsContainer .bulk-item-row').not(':first').remove();
                    $('#bulkItemsContainer .bulk-item-row:first').find('.bulk-preview').hide();
                    bulkIndex = 1;
                    loadGallery();
                }
                $('#saveBulkBtn').prop('disabled', false);
            },
            error: function(xhr) {
                $('#saveBulkBtn').prop('disabled', false);
                if (xhr.status === 422) {
                    const err = xhr.responseJSON.errors;
                    showAlert('danger', err[Object.keys(err)[0]] ? (Array.isArray(err[Object.keys(err)[0]]) ? err[Object.keys(err)[0]][0] : err[Object.keys(err)[0]]) : 'Validation failed');
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'Error saving.');
                }
            }
        });
    });

    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        const id = $(this).data('id');
        const isActive = $(this).is(':checked');
        $.ajax({
            url: '{{ route("instagram-gallery.update-status", ":id") }}'.replace(':id', id),
            method: 'POST',
            data: { is_active: isActive ? 1 : 0, _token: csrf },
            success: function(res) { if (res.success) showAlert('success', 'Status updated'); },
            error: function() { showAlert('danger', 'Failed to update'); }
        });
    });

    // Edit
    $(document).on('click', '.edit-item', function() {
        const id = $(this).data('id');
        $.get('{{ route("instagram-gallery.show", ":id") }}'.replace(':id', id), function(res) {
            if (res.success) {
                const d = res.data;
                $('#editItemId').val(d.id);
                $('#editInstagramLink').val(d.instagram_link || '');
                $('#editIsActive').prop('checked', d.is_active);
                $('#editThumbnail').val('');
                if (d.thumbnail_image) {
                    $('#editCurrentImage').attr('src', d.thumbnail_image);
                    $('#editCurrentImageWrap').show();
                } else {
                    $('#editCurrentImageWrap').hide();
                }
                $('#editGalleryModal').modal('show');
            }
        });
    });

    $('#saveEditBtn').on('click', function() {
        const id = $('#editItemId').val();
        const formData = new FormData($('#editGalleryForm')[0]);
        formData.append('_method', 'PUT');
        formData.append('is_active', $('#editIsActive').is(':checked') ? '1' : '0');
        $.ajax({
            url: '{{ route("instagram-gallery.update", ":id") }}'.replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#editGalleryModal').modal('hide');
                    showAlert('success', res.message);
                    loadGallery();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const err = xhr.responseJSON.errors;
                    $('#editAlertContainer').html('<div class="alert alert-danger">' + (err[Object.keys(err)[0]] ? (Array.isArray(err[Object.keys(err)[0]]) ? err[Object.keys(err)[0]][0] : err[Object.keys(err)[0]]) : 'Validation failed') + '</div>');
                }
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-item', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this gallery item?')) return;
        $.ajax({
            url: '{{ route("instagram-gallery.destroy", ":id") }}'.replace(':id', id),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf },
            success: function(res) {
                if (res.success) {
                    showAlert('success', res.message);
                    loadGallery();
                }
            },
            error: function() { showAlert('danger', 'Failed to delete'); }
        });
    });

    $('#tableSearch').on('keyup', function() { loadGallery(); });
    $('#bulkItemsContainer .remove-bulk-row').first().hide();
    loadGallery();
});
</script>
@endpush
