{{-- SEO Settings Section --}}
<div class="card mb-3" id="seoSection">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-search me-2"></i>SEO Settings
            </h6>  
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#seoInfoModal">
                <svg class="svg-inline--fa fa-info-circle fa-w-16" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg><!-- <i class="fas fa-info-circle"></i> Font Awesome fontawesome.com -->
            </button>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-12">
                <label for="metaDescription" class="form-label form-label-sm">Meta Data (Raw)</label>
                <textarea class="form-control form-control-sm" id="metaDescription" name="meta_description" rows="10" placeholder="Paste complete meta tags or SEO snippets">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                <small class="text-muted small">Paste meta tags, JSON-LD or other SEO snippets. Content will be saved as-is.</small>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<!-- SEO Settings Information Modal -->
<div class="modal fade" id="seoInfoModal" tabindex="-1" aria-labelledby="seoInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seoInfoModalLabel">
                    <i class="fas fa-search me-2"></i>SEO Settings Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-code text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Meta Data:</strong> Paste complete meta tags, OpenGraph data, structured data or other SEO snippets <small class="text-muted">(Stored exactly as provided)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                    </h6>
                    <div class="mb-0">
                        <div><strong>Keep original formatting</strong> when pasting meta tags or scripts <small class="text-muted">(The system preserves your exact markup)</small></div>
                        <div><strong>Include only trusted snippets</strong> such as meta tags, OpenGraph data or structured data <small class="text-muted">(Avoid untrusted scripts for security)</small></div>
                        <div><strong>Validate your markup</strong> using search engine tools <small class="text-muted">(Ensures rich results and previews render correctly)</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Small form label styling */
.form-label-sm {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.form-control-sm {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
</style>
