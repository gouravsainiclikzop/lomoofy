@extends('layouts.admin')

@section('title', 'Blogs Management')

@push('styles')
<style>
    .blog-thumbnail {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    .action-buttons {
        display: flex;
        gap: 0.5rem;
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
                        <li class="breadcrumb-item active" aria-current="page">Blogs</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Blogs Management</h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('blogs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Blog
                </a>
            </div>
        </div>

        <!-- Blogs List Card -->
        <div class="card">
            <div class="card-body">
                <div id="alertContainer"></div>
                
                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search blogs...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Blogs Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="blogsTable">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Thumbnail</th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Added By</th>
                                <th>Published Date</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="blogsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this blog post?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let blogToDelete = null;

$(document).ready(function() {
    loadBlogs();

    // Search
    $('#searchBtn').on('click', function() {
        loadBlogs();
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            loadBlogs();
        }
    });

    // Delete blog
    $(document).on('click', '.delete-blog', function() {
        blogToDelete = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (blogToDelete) {
            deleteBlog(blogToDelete);
        }
    });
});

function loadBlogs() {
    const search = $('#searchInput').val();

    $.ajax({
        url: '{{ route("blogs.data") }}',
        type: 'GET',
        data: {
            search: search,
            sort_by: 'created_at',
            sort_direction: 'desc'
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                renderBlogs(response.data);
            }
        },
        error: function(xhr) {
            showAlert('danger', 'Error loading blogs');
        }
    });
}

function renderBlogs(blogs) {
    const tbody = $('#blogsTableBody');
    tbody.empty();

    if (blogs.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted">No blogs found</td>
            </tr>
        `);
        return;
    }

    blogs.forEach(function(blog) {
        const row = `
            <tr>
                <td>
                    <img src="${blog.thumbnail_image}" alt="${blog.title}" class="blog-thumbnail">
                </td>
                <td>${blog.title}</td>
                <td><code>${blog.slug}</code></td>
                <td>${blog.added_by || '-'}</td>
                <td>${blog.published_date || '-'}</td>
                <td>
                    <div class="action-buttons">
                        <a href="/admin/blogs/${blog.id}/edit" class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-blog" data-id="${blog.id}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function deleteBlog(id) {
    $.ajax({
        url: '/admin/blogs/' + id,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#deleteModal').modal('hide');
                showAlert('success', response.message);
                loadBlogs();
            }
        },
        error: function(xhr) {
            showAlert('danger', xhr.responseJSON?.message || 'Error deleting blog');
        }
    });
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alertContainer').html(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush
