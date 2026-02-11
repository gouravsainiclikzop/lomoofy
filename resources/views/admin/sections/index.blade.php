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
                @if(auth()->user()->hasPermission('section_management.update'))
                    <button type="button" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#colorThemeModal" title="Manage Color Theme">
                        <i class='bx bx-palette me-1'></i>
                        <span>Color Theme</span>
                        @if($companySettings->active_color_theme ?? null)
                            @php
                                $themeName = str_replace('_', ' ', $companySettings->active_color_theme);
                                $themeName = ucwords($themeName);
                            @endphp
                            <span class="badge bg-warning text-dark ms-1">{{ $themeName }}</span>
                        @else
                            <span class="badge bg-secondary ms-1">None</span>
                        @endif
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#fontFamilyModal" title="Manage Font Family">
                        <i class='bx bx-font me-1'></i>
                        <span>Font Family</span>
                        @if($companySettings->font_family ?? null)
                            @php
                                $fontName = explode(',', $companySettings->font_family)[0];
                                $fontName = str_replace(["'", '"'], '', $fontName);
                                $fontName = strlen($fontName) > 15 ? substr($fontName, 0, 15) . '...' : $fontName;
                            @endphp
                            <span class="badge bg-info text-dark ms-1">{{ $fontName }}</span>
                        @endif
                    </button>
                @endif
                <button type="button" class="btn btn-outline-primary btn-sm"  onclick="window.location.href='{{ route("dashboard") }}'">
                    <i class='bx bx-arrow-back me-1'></i> Back to Dashboard
                </button>
                <button type="button" class="btn btn-outline-success btn-sm"  title="Refresh sections" onclick="refreshIframe()">
                    <i class='bx bx-check-square me-1'></i> Refresh
                </button>  
             </div>
         </div>

        <!-- Sections List -->
        <div class="card">
            <div class="card-body p-3">
                <iframe
                    id="frontendPreview"
                    src="{{ url('/') }}"
                    title="Frontend preview"
                    class="w-100 border rounded"
                    style="min-height: 80vh; height: 70vh;"
                ></iframe>
                <div id="sectionsContainer" class="mt-3">

                </div>
            </div>
        </div>

    </div>
    </div>

   
    <!-- Color Theme Management Modal -->
<div class="modal fade" id="colorThemeModal" tabindex="-1" aria-labelledby="colorThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title d-flex align-items-center gap-2" id="colorThemeModalLabel">
                    <i class='bx bx-palette' style="font-size: 1.5rem;"></i>
                    <span>Select Color Theme</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                @php
                    $themes = $companySettings->color_themes ?? [];
                    $activeTheme = $companySettings->active_color_theme ?? null;

                    $themeNames = [
                        'jewellery_luxury' => 'Jewellery Luxury',
                        'furniture_earth' => 'Furniture Earth',
                        'shoes_active' => 'Shoes Active',
                        'fashion_editorial' => 'Fashion Editorial',
                        'lifestyle_soft' => 'Lifestyle Soft',
                        'dark_inverse' => 'Dark Inverse'
                    ];
                @endphp

                <div class="d-flex flex-wrap gap-3 justify-content-center" id="colorThemesGrid">

                    <!-- No Theme -->
                    <div style="width:180px;">
                        <div class="card theme-card h-100 text-center {{ $activeTheme === null ? 'border-primary border-3' : '' }}"
                             data-theme="">
                            <div class="card-body">
                                <i class='bx bx-x-circle mb-2' style="font-size:2rem;color:#6c757d;"></i>
                                <h6 class="small mb-2">No Theme</h6>

                                @if($activeTheme === null)
                                    <span class="badge bg-primary">Active</span>
                                @else
                                    <button class="btn btn-sm btn-outline-primary activate-theme-btn" data-theme="">
                                        Activate
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Themes -->
                    @foreach($themes as $themeKey => $theme)
                        <div style="width:180px;">
                            <div class="card theme-card h-100 text-center {{ $activeTheme === $themeKey ? 'border-primary border-3' : '' }}"
                                 data-theme="{{ $themeKey }}">
                                <div class="card-body">
                                    <h6 class="small fw-semibold mb-2">
                                        {{ $themeNames[$themeKey] ?? ucwords(str_replace('_', ' ', $themeKey)) }}
                                    </h6>

                                    <!-- Compact Square Palette -->
                                    <div class="theme-palette d-flex flex-wrap gap-2 justify-content-center mb-3">
                                        @foreach(array_merge(
                                            $theme['backgrounds'] ?? [],
                                            $theme['text'] ?? [],
                                            $theme['anchors'] ?? [],
                                            $theme['borders'] ?? [],
                                            $theme['hover'] ?? [],
                                            $theme['span'] ?? []
                                        ) as $color)
                                            <span
                                                class="palette-square"
                                                style="background-color: {{ $color }};"
                                                title="{{ $color }}">
                                            </span>
                                        @endforeach
                                    </div>

                                    @if($activeTheme === $themeKey)
                                        <span class="badge bg-primary">Active</span>
                                    @else
                                        <button class="btn btn-sm btn-outline-primary activate-theme-btn"
                                                data-theme="{{ $themeKey }}">
                                            Activate
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class='bx bx-x me-1'></i> Close
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .theme-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .theme-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .theme-palette {
        max-width: 150px;
        margin: 0 auto;
    }

    .palette-square {
        width: 22px;
        height: 22px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    .palette-square:hover {
        transform: scale(1.1);
        border-color: #000;
    }
</style>



    <!-- Font Family Management Modal -->
    <div class="modal fade" id="fontFamilyModal" tabindex="-1" aria-labelledby="fontFamilyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="fontFamilyModalLabel">
                        <i class='bx bx-font' style="font-size: 1.5rem;"></i>
                        <span>Manage Font Family</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label for="fontFamilySelect" class="form-label fw-semibold mb-3">
                            <i class='bx bx-text me-2'></i>Select Font Family
                        </label>
                        <select id="fontFamilySelect" class="form-select form-select-lg">
                            <option value="">Default (System Font)</option>
                            <option value="Arial, Helvetica, sans-serif" {{ ($companySettings->font_family ?? '') === 'Arial, Helvetica, sans-serif' ? 'selected' : '' }}>Arial, Helvetica, sans-serif</option>
                            <option value="'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" {{ ($companySettings->font_family ?? '') === "'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" ? 'selected' : '' }}>Inter, Segoe UI, Roboto</option>
                            <option value="'Playfair Display', Georgia, 'Times New Roman', serif" {{ ($companySettings->font_family ?? '') === "'Playfair Display', Georgia, 'Times New Roman', serif" ? 'selected' : '' }}>Playfair Display (Serif)</option>
                            <option value="'Poppins', 'Montserrat', 'Segoe UI', sans-serif" {{ ($companySettings->font_family ?? '') === "'Poppins', 'Montserrat', 'Segoe UI', sans-serif" ? 'selected' : '' }}>Poppins, Montserrat</option>
                            <option value="Georgia, 'Times New Roman', serif" {{ ($companySettings->font_family ?? '') === "Georgia, 'Times New Roman', serif" ? 'selected' : '' }}>Georgia (Serif)</option>
                            <option value="'Courier New', Courier, monospace" {{ ($companySettings->font_family ?? '') === "'Courier New', Courier, monospace" ? 'selected' : '' }}>Courier New (Monospace)</option>
                            <option value="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif" {{ ($companySettings->font_family ?? '') === "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif" ? 'selected' : '' }}>System Font Stack</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class='bx bx-info-circle me-1'></i>
                            The selected font will be applied to the entire frontend website.
                        </div>
                    </div>

                    <!-- Font Preview Section -->
                    <div class="border rounded p-3 bg-light">
                        <label class="form-label fw-semibold mb-2">
                            <i class='bx bx-show me-2'></i>Preview
                        </label>
                        <div id="fontPreview" class="p-3 bg-white rounded border" style="min-height: 120px;">
                            <h5 class="mb-2" id="previewHeading">The quick brown fox jumps over the lazy dog</h5>
                            <p class="mb-2" id="previewParagraph">This is a preview of how your selected font will look on your website. The font family will be applied to all text elements including headings, paragraphs, and buttons.</p>
                            <small id="previewSmall">Small text preview - 1234567890</small>
                        </div>
                    </div>

                    
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class='bx bx-x me-1'></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="saveFontFamilyBtn">
                        <span class="btn-text">
                            <i class='bx bx-save me-1'></i> Save Changes
                        </span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const sectionManagementPermissions = {
    view: @json(auth()->user()->hasPermission('section_management.view')),
    update: @json(auth()->user()->hasPermission('section_management.update'))
};

$(document).ready(function() { 
    $('.activate-theme-btn').on('click', function(e) {
        if (!sectionManagementPermissions.update) {
            showToast('error', 'You do not have permission to update sections.');
            return;
        }
        e.stopPropagation();
        const themeName = $(this).data('theme') || null;
        const btn = $(this);
        const originalText = btn.html();
         
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Activating...');
        
        $.ajax({
            url: '{{ route("sections.updateColorTheme") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                theme_name: themeName
            },
            success: function(response) {
                if (response.success) { 
                    const iframe = document.getElementById('frontendPreview');
                    if (iframe) {
                        iframe.src = iframe.src;
                    }
                     
                    showToast('success', response.message);
                     
                    setTimeout(function() {
                        $('#colorThemeModal').modal('hide');
                    }, 500);
                     
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update color theme. Please try again.');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
     
    $('.theme-card').on('click', function() {
        const themeName = $(this).data('theme');
        const activateBtn = $(this).find('.activate-theme-btn');
        if (activateBtn.length && !activateBtn.prop('disabled')) {
            activateBtn.trigger('click');
        }
    });
     
    $('#fontFamilyModal').on('show.bs.modal', function() {
        updateFontPreview();
    });
 
    $('#fontFamilySelect').on('change', function() {
        updateFontPreview();
    });
 
    function updateFontPreview() {
        const fontFamily = $('#fontFamilySelect').val();
        const previewContainer = $('#fontPreview');
        
        if (fontFamily) {
            previewContainer.css('font-family', fontFamily);
        } else {
            previewContainer.css('font-family', '');
        }
    }
 
    $('#saveFontFamilyBtn').on('click', function() {
        if (!sectionManagementPermissions.update) {
            showToast('error', 'You do not have permission to update sections.');
            return;
        }
        const fontFamily = $('#fontFamilySelect').val();
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
         
        btn.prop('disabled', true);
        btnText.addClass('d-none');
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("sections.updateFontFamily") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                font_family: fontFamily
            },
            success: function(response) {
                if (response.success) { 
                    const iframe = document.getElementById('frontendPreview');
                    if (iframe) {
                        iframe.src = iframe.src;
                    }
                     
                    showToast('success', 'Font family updated successfully!');
                     
                    setTimeout(function() {
                        $('#fontFamilyModal').modal('hide');
                    }, 500);
                     
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update font family. Please try again.');
            },
            complete: function() { 
                btn.prop('disabled', false);
                btnText.removeClass('d-none');
                spinner.addClass('d-none');
            }
        });
    });
     
    function showToast(type, message) {
        const toastContainer = $('.sa-app__toasts');
        if (toastContainer.length === 0) { 
            $('body').append('<div class="sa-app__toasts position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        
        const toast = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body d-flex align-items-center">
                    <i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2' style='font-size: 1.5rem;'></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
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

function refreshIframe() {
    const $iframe = $('#frontendPreview');
    if ($iframe.length) {
        $iframe.attr('src', $iframe.attr('src'));
    }
}


</script>
@endpush
  
