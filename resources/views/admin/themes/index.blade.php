@extends('layouts.admin')

@section('title', 'Themes')

@push('styles')
<style>
    .themes-page .software-id-card {
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .themes-page .software-id-card .card-body {
        padding: 1.5rem 1.75rem;
    }
    .themes-page .software-id-card .form-label {
        font-weight: 500;
        color: #374151;
    }
    .themes-page .software-id-card .form-control {
        border-radius: 8px;
    }
    .themes-page .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
        font-size: 0.9rem;
        border: 1px solid #e2e8f0;
    }
    .themes-page .filter-badge code {
        background: #fff;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.85rem;
        border: 1px solid #e2e8f0;
    }
    .themes-page .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        border-radius: 12px;
        background: linear-gradient(180deg, #fafbfc 0%, #f8f9fa 100%);
        border: 1px dashed #d1d5db;
    }
    .themes-page .empty-state .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 50%;
        color: #9ca3af;
        font-size: 1.75rem;
    }
    .theme-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #fff;
    }
    .theme-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12), 0 4px 8px -4px rgba(0, 0, 0, 0.06);
        transform: translateY(-4px);
    }
    .theme-card__thumb-wrap {
        position: relative;
        padding-top: 62.5%;
        background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
        overflow: hidden;
    }
    .theme-card__thumb {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .theme-card:hover .theme-card__thumb {
        transform: scale(1.03);
    }
    .theme-card__thumb-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #94a3b8;
        font-size: 2.75rem;
        opacity: 0.8;
    }
    .theme-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%);
        opacity: 0;
        transition: opacity 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .theme-card__thumb-wrap:hover .theme-card__overlay {
        opacity: 1;
    }
    .theme-card__overlay .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
    }
    .theme-card__body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .theme-card__title {
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 0.5rem;
        color: #1f2937;
        line-height: 1.4;
    }
    .theme-card__meta {
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 0.875rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }
    .theme-card__meta code {
        font-size: 0.75rem;
        padding: 0.15rem 0.4rem;
        background: #f3f4f6;
        border-radius: 4px;
    }
    .theme-card__footer {
        margin-top: auto;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .theme-card__footer .btn {
        border-radius: 8px;
        font-weight: 500;
    }
    .theme-preview-modal .modal-dialog {
        max-width: 920px;
    }
    .theme-preview-modal .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }
    .theme-preview-modal .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .theme-preview-modal .modal-body {
        padding: 1.5rem;
    }
    .theme-preview-modal .modal-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
<div class="container-fluid themes-page">
    <div class="py-5">
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Themes</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Themes</h1>
                <p class="text-muted small mb-0 mt-1">Manage and preview themes for your store</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Software ID card -->
        <div class="card mb-4 software-id-card">
            <div class="card-body"> 
                <form action="{{ route('themes.update-software-id') }}" method="post" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label for="software_id" class="form-label">Software ID</label>
                        <input type="text" name="software_id" id="software_id" class="form-control form-control--search @error('software_id') is-invalid @enderror" value="{{ old('software_id', $softwareId ?? '') }}" placeholder="e.g. my-store-001" required>
                        @error('software_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(empty($softwareId))
        <div class="card border-0">
            <div class="card-body p-0">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bx bx-palette"></i></div>
                    <h6 class="mb-2">No Software ID set</h6>
                    <p class="text-muted small mb-0 mx-auto" style="max-width: 360px;">Set a Software ID above to load themes for this store. Themes are managed by the Master Panel via API.</p>
                </div>
            </div>
        </div>
        @else
        <!-- Themes for current software_id -->
        <div class="filter-badge mb-4">
            <i class="bx bx-filter-alt text-muted"></i>
            <span class="text-muted">Showing themes for</span>
            <code>{{ $softwareId }}</code>
        </div>

        @if($themes->isEmpty())
        <div class="card border-0">
            <div class="card-body p-0">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bx bx-folder-open"></i></div>
                    <h6 class="mb-2">No themes found</h6>
                    <p class="text-muted small mb-0 mx-auto" style="max-width: 360px;">Themes for this Software ID will appear here once pushed from the Master Panel via API.</p>
                </div>
            </div>
        </div>
        @else
        <div class="row g-4">
            @foreach($themes as $theme)
            @php $isExternal = $theme->isExternal(); @endphp
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="theme-card card border-0 shadow-sm">
                    <div class="theme-card__thumb-wrap">
                        @if($theme->theme_thumbnail)
                            <img src="{{ asset('storage/' . $theme->theme_thumbnail) }}" alt="{{ $theme->theme_name }}" class="theme-card__thumb">
                        @else
                            <div class="theme-card__thumb-placeholder">
                                <i class="bx bx-palette"></i>
                            </div>
                        @endif
                        <div class="theme-card__overlay">
                            @if($isExternal && $theme->preview_url)
                                <a href="{{ $theme->preview_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                    <i class="bx bx-link-external me-1"></i> Preview
                                </a>
                            @elseif(!$isExternal && $theme->theme_thumbnail)
                                <button type="button" class="btn btn-sm btn-primary theme-preview-btn" data-bs-toggle="modal" data-bs-target="#themePreviewModal" data-theme-name="{{ $theme->theme_name }}" data-thumb-url="{{ asset('storage/' . $theme->theme_thumbnail) }}" data-pdf-url="{{ $theme->theme_pdf ? asset('storage/' . $theme->theme_pdf) : '' }}">
                                    <i class="bx bx-search-alt me-1"></i> Preview
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="theme-card__body">
                        <h3 class="theme-card__title">{{ $theme->theme_name }}</h3>
                        <div class="theme-card__meta">
                            <code>{{ $theme->software_id }}</code>
                            <span class="badge bg-{{ $isExternal ? 'info' : 'secondary' }}">{{ $theme->type ?? 'internal' }}</span>
                        </div>
                        <div class="theme-card__footer">
                            @if($isExternal && $theme->preview_url)
                                <a href="{{ $theme->preview_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                    <i class="bx bx-link-external me-1"></i> Preview
                                </a>
                            @elseif(!$isExternal && $theme->theme_thumbnail)
                                <button type="button" class="btn btn-sm btn-primary theme-preview-btn" data-bs-toggle="modal" data-bs-target="#themePreviewModal" data-theme-name="{{ $theme->theme_name }}" data-thumb-url="{{ asset('storage/' . $theme->theme_thumbnail) }}" data-pdf-url="{{ $theme->theme_pdf ? asset('storage/' . $theme->theme_pdf) : '' }}">
                                    <i class="bx bx-search-alt me-1"></i> Preview
                                </button>
                            @endif
                            @if(!$isExternal && $theme->theme_pdf)
                                <a href="{{ asset('storage/' . $theme->theme_pdf) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-file-blank me-1"></i> PDF
                                </a>
                            @endif
                            <span class="badge bg-{{ $theme->status === 'active' ? 'success' : 'secondary' }} align-self-center ms-auto">{{ $theme->status }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
       
        @endif

        @endif
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade theme-preview-modal" id="themePreviewModal" tabindex="-1" aria-labelledby="themePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="themePreviewModalLabel">Theme Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="themePreviewImage" src="" alt="" class="img-fluid mb-3" style="max-height: 70vh;">
                <div id="themePreviewPdfWrap" class="mt-2" style="display: none;">
                    <a id="themePreviewPdfLink" href="" target="_blank" class="btn btn-outline-primary">
                        <i class="bx bx-file-blank me-1"></i> Open PDF Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('themePreviewModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (!btn || !btn.classList.contains('theme-preview-btn')) return;
            var name = btn.getAttribute('data-theme-name');
            var thumbUrl = btn.getAttribute('data-thumb-url');
            var pdfUrl = btn.getAttribute('data-pdf-url');
            document.getElementById('themePreviewModalLabel').textContent = name || 'Theme Preview';
            var img = document.getElementById('themePreviewImage');
            img.src = thumbUrl || '';
            img.alt = name || '';
            var pdfWrap = document.getElementById('themePreviewPdfWrap');
            var pdfLink = document.getElementById('themePreviewPdfLink');
            if (pdfUrl) {
                pdfWrap.style.display = 'block';
                pdfLink.href = pdfUrl;
            } else {
                pdfWrap.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
