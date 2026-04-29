/**
 * Shop Regional Settings Management
 */

(function () {
    'use strict';

    const notyf = typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify
        ? window.Notiflix.Notify
        : {
            success(message) { alert(message); },
            failure(message) { alert(message); },
        };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            return;
        }

        const routes = window.shopRegionalSettingsRoutes || {};

        $('#saveShopRegionalSettingsButton').on('click', function () {
            const $form = $('#shopRegionalSettingsForm');
            const $button = $(this);
            const originalText = $button.html();

            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            $.ajax({
                url: routes.save,
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success(response) {
                    notyf.success(response.message || 'Regional settings saved successfully.');
                    $button.prop('disabled', false).html(originalText);
                },
                error(xhr) {
                    $button.prop('disabled', false).html(originalText);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errorMessages = [];

                        $.each(xhr.responseJSON.errors, function (_field, messages) {
                            errorMessages.push(messages.join(', '));
                        });

                        notyf.failure(errorMessages.join(', '));

                        return;
                    }

                    notyf.failure(xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.');
                },
            });
        });
    });
})();
