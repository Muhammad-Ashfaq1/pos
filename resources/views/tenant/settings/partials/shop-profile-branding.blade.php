@php
    $logoUrl = $form['logo_url'] ?? null;
    $primaryColor = old('primary_color', $form['primary_color'] ?? \App\Models\Tenant::DEFAULT_BRAND_COLOR);
@endphp

<form id="branding-form" action="{{ route('tenant.settings.shop-profile.branding.save') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row gy-6">
        <div class="col-12">
            <h5 class="card-header p-0 mb-5">Branding</h5>

            <h5 class="branding-section-heading">Company Logo</h5>
            <div class="branding-section-wrapper d-flex">
                <div class="branding-logo-section">
                    <x-file-upload
                        name="logo"
                        id="dropzone-branding-logo"
                        label="Company Logo"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                        :multiple="false"
                        :maxFiles="1"
                        :maxFilesize="5"
                        helpText="(File will be uploaded when you hit upload.)"
                    />
                </div>

                <div class="branding-color-section">
                    <h5 class="branding-section-heading">Primary color</h5>
                    <div class="branding-color-picker">
                        <input
                            type="color"
                            id="primary_color_input"
                            name="primary_color"
                            class="branding-color-input"
                            value="{{ $primaryColor }}"
                            title="Primary color">
                        <button
                            type="button"
                            class="btn btn-icon btn-text-secondary rounded-pill branding-color-edit-btn"
                            id="primary_color_trigger"
                            title="Edit color">
                            <i class="icon-base ti tabler-pencil"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">Leave the app default to keep the normal theme colors.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <button type="button" id="save-branding-btn" class="btn btn-primary">Save Changes</button>
        </div>
    </div>

    <div class="row px-1 py-4">
        <div class="company-l col-12" id="current-logo-section">
            @if ($logoUrl)
                <div class="company-logo-preview">
                    <p class="text-muted mb-2">Current logo:</p>
                    <img src="{{ $logoUrl }}" alt="Company logo" class="img-thumbnail" style="max-height: 150px;">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                        <label class="form-check-label" for="remove_logo">Remove current logo</label>
                    </div>
                </div>
            @else
                <input type="hidden" name="remove_logo" id="remove_logo" value="0">
            @endif
        </div>
    </div>
</form>
