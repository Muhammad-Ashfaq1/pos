/**
 * Shop Branding Settings — Dropzone + primary color (matches OnSite branding UX).
 */

(function () {
    'use strict';

    const notyf = typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify
        ? window.Notiflix.Notify
        : {
            success(message) { alert(message); },
            failure(message) { alert(message); },
        };

    function normalizeHex(value) {
        let hex = String(value || '').trim();
        if (hex && hex.charAt(0) !== '#') {
            hex = '#' + hex;
        }
        return hex.toUpperCase();
    }

    function initBrandingDropzone() {
        if (typeof Dropzone === 'undefined') {
            console.warn('Dropzone is not loaded.');
            return null;
        }

        Dropzone.autoDiscover = false;

        const dropzoneElement = document.querySelector('#dropzone-branding-logo');
        if (!dropzoneElement || dropzoneElement.dropzone) {
            return dropzoneElement ? dropzoneElement.dropzone : null;
        }

        const fileInput = document.getElementById(dropzoneElement.dataset.fileInput);
        const errorContainer = document.getElementById(dropzoneElement.id + '-error');
        const maxFiles = parseInt(dropzoneElement.dataset.maxFiles || '1', 10);
        const maxFilesize = parseInt(dropzoneElement.dataset.maxFilesize || '5', 10);
        const previewsId = dropzoneElement.dataset.previews;

        // Same template shape as OnSite — no manual Remove link here.
        // addRemoveLinks: true adds a single "Remove file" control.
        const previewTemplate = `<div class="dz-preview dz-file-preview">
            <div class="dz-details">
                <div class="dz-thumbnail">
                    <img data-dz-thumbnail>
                    <span class="dz-nopreview">No preview</span>
                    <div class="dz-success-mark"></div>
                    <div class="dz-error-mark"></div>
                    <div class="dz-error-message"><span data-dz-errormessage></span></div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
                    </div>
                </div>
                <div class="dz-filename" data-dz-name></div>
                <div class="dz-size" data-dz-size></div>
            </div>
        </div>`;

        const myDropzone = new Dropzone(dropzoneElement, {
            previewTemplate: previewTemplate,
            parallelUploads: 1,
            maxFilesize: maxFilesize,
            addRemoveLinks: true,
            dictRemoveFile: 'Remove file',
            acceptedFiles: dropzoneElement.dataset.accept || 'image/*',
            clickable: dropzoneElement,
            autoProcessQueue: false,
            url: '#',
            previewsContainer: '#' + previewsId,
            maxFiles: maxFiles,
            init: function () {
                const dropzoneInstance = this;
                const messageDiv = dropzoneElement.querySelector('.dz-message');

                function syncFileInput() {
                    if (!fileInput) {
                        return;
                    }

                    const dataTransfer = new DataTransfer();
                    dropzoneInstance.files.forEach(function (dzFile) {
                        if (dzFile instanceof File) {
                            dataTransfer.items.add(dzFile);
                        }
                    });
                    fileInput.files = dataTransfer.files;
                }

                function toggleMessage() {
                    if (!messageDiv) {
                        return;
                    }

                    if (dropzoneInstance.files.length > 0) {
                        messageDiv.style.display = 'none';
                        dropzoneElement.classList.add('has-files');
                    } else {
                        messageDiv.style.display = 'flex';
                        dropzoneElement.classList.remove('has-files');
                    }
                }

                this.on('addedfile', function (file) {
                    // Keep only the newest file when maxFiles = 1.
                    if (this.files.length > maxFiles) {
                        this.removeFile(this.files[0]);
                    }

                    toggleMessage();
                    syncFileInput();

                    const removeCheckbox = document.getElementById('remove_logo');
                    if (removeCheckbox && removeCheckbox.type === 'checkbox') {
                        removeCheckbox.checked = false;
                    }
                });

                this.on('removedfile', function () {
                    toggleMessage();
                    syncFileInput();
                });

                this.on('maxfilesexceeded', function (file) {
                    this.removeFile(file);
                    if (errorContainer) {
                        errorContainer.textContent = 'Only ' + maxFiles + ' file(s) allowed.';
                        setTimeout(function () { errorContainer.textContent = ''; }, 4000);
                    }
                });

                this.on('error', function (file, errorMessage) {
                    // Dropzone emits "Upload canceled." / URL "#" noise — ignore those.
                    let errorText = typeof errorMessage === 'string'
                        ? errorMessage
                        : (errorMessage && errorMessage.error ? errorMessage.error : '');

                    if (!errorText || errorText.indexOf('#') !== -1 || /cancel/i.test(errorText)) {
                        return;
                    }

                    if (errorContainer) {
                        errorContainer.textContent = errorText;
                        setTimeout(function () { errorContainer.textContent = ''; }, 5000);
                    }
                });
            },
        });

        dropzoneElement.dropzone = myDropzone;
        window.shopBrandingDropzone = myDropzone;

        return myDropzone;
    }

    function syncDropzoneToInput($form) {
        const dropzone = window.shopBrandingDropzone;
        if (!dropzone || !dropzone.files) {
            return;
        }

        const fileInput = $form.find('input[type="file"][name="logo"]')[0];
        if (!fileInput) {
            return;
        }

        const dataTransfer = new DataTransfer();
        dropzone.files.forEach(function (file) {
            if (file instanceof File) {
                dataTransfer.items.add(file);
            }
        });
        fileInput.files = dataTransfer.files;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            return;
        }

        initBrandingDropzone();

        const routes = window.shopBrandingSettingsRoutes || {};
        const $form = $('#branding-form');
        const $colorInput = $('#primary_color_input');
        const $colorTrigger = $('#primary_color_trigger');

        $colorTrigger.on('click', function (e) {
            e.preventDefault();
            $colorInput.trigger('click');
        });

        $('#save-branding-btn').on('click', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const originalText = $btn.html();

            $colorInput.val(normalizeHex($colorInput.val()));
            syncDropzoneToInput($form);

            if (typeof window.appSetButtonLoading === 'function') {
                window.appSetButtonLoading($btn, true, 'Saving...', originalText);
            } else {
                $btn.prop('disabled', true).text('Saving...');
            }

            const formData = new FormData($form[0]);
            const removeChecked = $('#remove_logo').is(':checkbox')
                ? $('#remove_logo').is(':checked')
                : $('#remove_logo').val() === '1';

            if (!removeChecked) {
                formData.delete('remove_logo');
            } else {
                formData.set('remove_logo', '1');
            }

            $.ajax({
                url: routes.save || $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success(response) {
                    notyf.success(response.message || 'Branding saved successfully.');

                    window.setTimeout(function () {
                        window.location.reload();
                    }, 450);
                },
                error(xhr) {
                    if (typeof window.appSetButtonLoading === 'function') {
                        window.appSetButtonLoading($btn, false, 'Saving...', originalText);
                    } else {
                        $btn.prop('disabled', false).html(originalText);
                    }

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errorMessages = [];
                        $.each(xhr.responseJSON.errors, function (_field, messages) {
                            errorMessages.push(messages.join(', '));
                        });
                        notyf.failure(errorMessages.join(', '));
                        return;
                    }

                    notyf.failure(xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to save branding settings.');
                },
            });
        });
    });
})();
