{{-- Digital Product Settings --}}
<div class="card mb-4" id="digitalSection" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-download me-2"></i>Digital Product Settings
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Download Files --}}
            <div class="col-12">
                <label for="downloadFiles" class="form-label">Download Files</label>
                <input type="file" class="form-control" id="downloadFiles" name="download_files[]" multiple 
                       accept=".zip,.pdf,.mp3,.mp4,.doc,.docx,.txt">
                <small class="text-muted">Upload files that customers will download after purchase</small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Download Limit --}}
            <div class="col-md-6">
                <label for="downloadLimit" class="form-label">Download Limit</label>
                <input type="number" class="form-control" id="downloadLimit" name="download_limit" 
                       min="0" value="{{ old('download_limit', $product->download_limit ?? '') }}" 
                       placeholder="Leave empty for unlimited">
                <small class="text-muted">Maximum number of downloads per customer</small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Download Expiry --}}
            <div class="col-md-6">
                <label for="downloadExpiry" class="form-label">Download Expiry (days)</label>
                <input type="number" class="form-control" id="downloadExpiry" name="download_expiry" 
                       min="0" value="{{ old('download_expiry', $product->download_expiry ?? '') }}" 
                       placeholder="Leave empty for no expiry">
                <small class="text-muted">Number of days after purchase when downloads expire</small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- License Key --}}
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="generateLicenseKey" name="generate_license_key" 
                           {{ old('generate_license_key', $product->generate_license_key ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="generateLicenseKey">
                        <strong>Generate License Keys</strong>
                        <small class="text-muted d-block">Automatically generate unique license keys for each purchase</small>
                    </label>
                </div>
            </div>

            {{-- License Key Format --}}
            <div class="col-md-6" id="licenseKeyFormat" style="display: none;">
                <label for="licenseKeyFormat" class="form-label">License Key Format</label>
                <select class="form-select" id="licenseKeyFormat" name="license_key_format">
                    <option value="random" {{ old('license_key_format', $product->license_key_format ?? 'random') == 'random' ? 'selected' : '' }}>
                        Random String
                    </option>
                    <option value="uuid" {{ old('license_key_format', $product->license_key_format ?? '') == 'uuid' ? 'selected' : '' }}>
                        UUID
                    </option>
                    <option value="custom" {{ old('license_key_format', $product->license_key_format ?? '') == 'custom' ? 'selected' : '' }}>
                        Custom Format
                    </option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Custom License Format --}}
            <div class="col-md-6" id="customLicenseFormat" style="display: none;">
                <label for="customLicenseFormat" class="form-label">Custom Format</label>
                <input type="text" class="form-control" id="customLicenseFormat" name="custom_license_format" 
                       value="{{ old('custom_license_format', $product->custom_license_format ?? '') }}" 
                       placeholder="e.g., XXXXX-XXXXX-XXXXX">
                <small class="text-muted">Use X for random characters</small>
                <div class="invalid-feedback"></div>
            </div>

            {{-- Digital Rights Management --}}
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="enableDRM" name="enable_drm" 
                           {{ old('enable_drm', $product->enable_drm ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enableDRM">
                        <strong>Enable DRM Protection</strong>
                        <small class="text-muted d-block">Add digital rights management to protect files</small>
                    </label>
                </div>
            </div>

            {{-- Watermark Settings --}}
            <div class="col-12" id="watermarkSettings" style="display: none;">
                <h6 class="mb-3">Watermark Settings</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="watermarkText" class="form-label">Watermark Text</label>
                        <input type="text" class="form-control" id="watermarkText" name="watermark_text" 
                               value="{{ old('watermark_text', $product->watermark_text ?? '') }}" 
                               placeholder="Customer email or name">
                    </div>
                    <div class="col-md-6">
                        <label for="watermarkOpacity" class="form-label">Opacity (%)</label>
                        <input type="range" class="form-range" id="watermarkOpacity" name="watermark_opacity" 
                               min="10" max="100" value="{{ old('watermark_opacity', $product->watermark_opacity ?? 50) }}">
                        <div class="d-flex justify-content-between">
                            <small>10%</small>
                            <small>100%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateLicenseKeyCheckbox = document.getElementById('generateLicenseKey');
    const licenseKeyFormat = document.getElementById('licenseKeyFormat');
    const customLicenseFormat = document.getElementById('customLicenseFormat');
    const licenseKeyFormatSelect = document.getElementById('licenseKeyFormat');
    const enableDRMCheckbox = document.getElementById('enableDRM');
    const watermarkSettings = document.getElementById('watermarkSettings');

    // License key settings
    generateLicenseKeyCheckbox.addEventListener('change', function() {
        licenseKeyFormat.style.display = this.checked ? 'block' : 'none';
    });

    licenseKeyFormatSelect.addEventListener('change', function() {
        customLicenseFormat.style.display = this.value === 'custom' ? 'block' : 'none';
    });

    // DRM settings
    enableDRMCheckbox.addEventListener('change', function() {
        watermarkSettings.style.display = this.checked ? 'block' : 'none';
    });

    // Initial state
    licenseKeyFormat.style.display = generateLicenseKeyCheckbox.checked ? 'block' : 'none';
    customLicenseFormat.style.display = licenseKeyFormatSelect.value === 'custom' ? 'block' : 'none';
    watermarkSettings.style.display = enableDRMCheckbox.checked ? 'block' : 'none';
});
</script>
