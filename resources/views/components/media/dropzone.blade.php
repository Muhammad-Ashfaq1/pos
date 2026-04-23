@props([
    'id' => 'media-dropzone',
    'label' => 'Images',
    'inputName' => 'images[]',
    'primaryInputName' => 'primary_image_ref',
    'removedInputName' => 'removed_image_ids[]',
    'accept' => 'image/jpeg,image/jpg,image/png,image/gif,image/webp',
    'maxFiles' => 20,
    'helpText' => 'Upload one featured image or multiple gallery images. Mark any image as primary.',
])

@once
    <style>
        .app-media-dropzone {
            border: 1px solid var(--bs-border-color);
            border-radius: 1rem;
            background: var(--bs-paper-bg);
            padding: 1rem;
        }

        .app-media-dropzone__surface {
            border: 2px dashed var(--bs-border-color);
            border-radius: 0.875rem;
            min-height: 180px;
            background: var(--bs-body-bg);
            padding: 1rem;
        }

        .app-media-dropzone__surface.has-files .dz-message {
            display: none;
        }

        .app-media-dropzone__surface .dz-message {
            margin: 0;
            padding: 2rem 1rem;
            text-align: center;
            color: var(--bs-secondary-color);
        }

        .app-media-dropzone__surface .dz-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .app-media-dropzone__surface .dz-preview {
            width: 11rem;
            margin: 0;
            background: var(--bs-paper-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.875rem;
            overflow: hidden;
        }

        .app-media-dropzone__surface .dz-thumbnail {
            width: 100%;
            height: 9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bs-body-bg);
            overflow: hidden;
        }

        .app-media-dropzone__surface .dz-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .app-media-dropzone__surface .dz-details {
            padding: 0.75rem;
        }

        .app-media-dropzone__surface .dz-filename,
        .app-media-dropzone__surface .dz-size {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .app-media-dropzone__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0 0.75rem 0.75rem;
            align-items: center;
        }

        .app-media-dropzone__meta .badge {
            font-size: 0.7rem;
        }

        .app-media-dropzone__meta .btn {
            --bs-btn-padding-y: 0.2rem;
            --bs-btn-padding-x: 0.55rem;
            --bs-btn-font-size: 0.75rem;
        }

        @media (max-width: 575.98px) {
            .app-media-dropzone__surface .dz-preview {
                width: calc(50% - 0.5rem);
            }
        }
    </style>
@endonce

<div
    class="app-media-dropzone"
    data-media-dropzone
    data-max-files="{{ $maxFiles }}"
>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <label class="form-label mb-1">{{ $label }}</label>
            <p class="text-muted small mb-0">{{ $helpText }}</p>
        </div>
    </div>

    <div id="{{ $id }}" class="dropzone app-media-dropzone__surface">
        <div class="dz-message">
            <div class="fw-medium mb-1">Drop images here or click to browse</div>
            <div class="small">JPG, PNG, GIF, and WEBP up to 5 MB each</div>
        </div>
        <div class="dz-preview-container"></div>
    </div>

    <input type="file" name="{{ $inputName }}" id="{{ $id }}_input" accept="{{ $accept }}" multiple hidden>
    <input type="hidden" name="{{ $primaryInputName }}" id="{{ $id }}_primary">
    <div id="{{ $id }}_removed"></div>
    <div class="invalid-feedback d-block" id="{{ $id }}_feedback"></div>
</div>
