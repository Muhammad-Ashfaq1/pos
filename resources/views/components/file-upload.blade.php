@props([
    'name' => 'logo',
    'id' => 'dropzone-branding-logo',
    'label' => 'Company Logo',
    'accept' => 'image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml',
    'multiple' => false,
    'maxFiles' => 1,
    'maxFilesize' => 5,
    'helpText' => '(File will be uploaded when you hit upload.)',
    'wrapperClass' => '',
    'dropzoneClass' => 'dropzone branding-dropzone dz-clickable',
])

@php
    $fileInputId = $id.'-input';
    $previewContainerId = $id.'-previews';
@endphp

<div class="{{ $wrapperClass }}">
    <div
        class="{{ $dropzoneClass }}"
        id="{{ $id }}"
        data-file-input="{{ $fileInputId }}"
        data-previews="{{ $previewContainerId }}"
        data-accept="{{ $accept }}"
        data-max-files="{{ (int) $maxFiles }}"
        data-max-filesize="{{ (int) $maxFilesize }}"
        data-input-name="{{ $name }}">
        <div class="dz-message needsclick">
            <div class="dztxt d-flex flex-column align-items-center">
                <span>{{ $label }}</span>
                @if ($helpText)
                    <span>{{ $helpText }}</span>
                @endif
            </div>
        </div>
        <div id="{{ $previewContainerId }}" class="dz-preview-container"></div>
    </div>
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $fileInputId }}"
        accept="{{ $accept }}"
        @if ($multiple) multiple @endif
        style="display: none;">
    <div class="text-danger small mt-1" id="{{ $id }}-error"></div>
</div>
