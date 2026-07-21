(function ($) {
  'use strict';

  $(function () {
    const currencySymbol = window.employeeCards?.currencySymbol || window.appCurrency?.symbol || '';
    const initialModule = window.employeeCards?.initialModule || 'discount';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $forms = $('.js-employee-card-form');
    const $addCardBtn = $('#addCardBtn');
    const $addCardLabel = $('[data-add-card-label]');
    const listClassByType = {
      gift: 'gift-card-list',
      reward: 'reward-card-list',
      discount: 'employee-loyalty-cards'
    };

    if (!$forms.length || !window.CardForm) {
      return;
    }

    if (csrfToken) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json'
        }
      });
    }

    const updateDiscountFields = function ($form) {
      window.CardForm.updateDiscountFields($form, { currencySymbol: currencySymbol });
    };

    const activateModule = function (module) {
      const $tab = $('[data-card-section="' + module + '"]');
      if (!$tab.length || !$addCardBtn.length || !$addCardLabel.length) {
        return;
      }

      $('[data-card-section]').each(function () {
        const active = this.dataset.cardSection === module;
        $(this).toggleClass('active', active).attr('aria-selected', active ? 'true' : 'false');
      });

      $('[data-card-panel]').each(function () {
        $(this).toggleClass('d-none', this.dataset.cardPanel !== module);
      });

      $addCardBtn.attr('data-bs-target', $tab.data('card-modal'));
      $addCardLabel.text($tab.data('card-label'));
      $addCardBtn.attr('data-active-module', module);
    };

    const bumpTabCount = function (cardType) {
      const $count = $('[data-card-section="' + cardType + '"]').children('span').first();
      if (!$count.length) {
        return;
      }

      const next = (parseInt($count.text(), 10) || 0) + 1;
      $count.text(String(next));
    };

    const prependCreatedCard = function (cardType, html) {
      if (!html) {
        return;
      }

      const $panel = $('[data-card-panel="' + cardType + '"]');
      if (!$panel.length) {
        return;
      }

      let $list = $panel.find('[data-card-list="' + cardType + '"]');
      if (!$list.length) {
        $panel.find('.employee-orders-empty').remove();
        $list = $('<div></div>')
          .addClass(listClassByType[cardType] || 'employee-loyalty-cards')
          .attr('data-card-list', cardType);
        $panel.append($list);
      }

      $list.prepend(html);
      bumpTabCount(cardType);
    };

    activateModule(initialModule);
    window.CardForm.initProductSelects();

    $forms.each(function () {
      const $form = $(this);
      const $modal = $form.closest('.modal');
      const $submitButton = $form.find('[data-card-submit]');

      updateDiscountFields($form);

      $form.on('change', '[data-card-discount-type]', function () {
        window.CardForm.clearFieldError($(this));
        updateDiscountFields($form);
      });

      $form.on('input change', 'input, select', function () {
        window.CardForm.clearFieldError($(this));
      });

      $modal.on('hidden.bs.modal', function () {
        $form[0].reset();
        $form.find('.card-product-select').val(null).trigger('change');
        window.CardForm.clearValidation($form);
        updateDiscountFields($form);
        window.CardForm.setButtonLoading($submitButton, false);
      });

      $form.on('submit', function (event) {
        event.preventDefault();

        if (!$form[0].checkValidity()) {
          $form[0].reportValidity();
          return;
        }

        window.CardForm.clearValidation($form);
        window.CardForm.setButtonLoading($submitButton, true);

        $.ajax({
          url: $form.attr('action'),
          method: 'POST',
          data: $form.serialize()
        })
          .done(function (response) {
            const cardType = response.card_type || $form.data('cardType');
            prependCreatedCard(cardType, response.html);

            const modalInstance = window.bootstrap?.Modal?.getInstance($modal[0]);
            if (modalInstance) {
              modalInstance.hide();
            } else {
              $modal.modal('hide');
            }

            if (typeof window.appNotify === 'function') {
              window.appNotify('success', response.message || 'Card created successfully.');
            }
          })
          .fail(function (xhr) {
            if (xhr.status === 422) {
              window.CardForm.renderValidationErrors($form, xhr.responseJSON?.errors || {});
              return;
            }

            if (typeof window.appNotify === 'function') {
              window.appNotify('error', xhr.responseJSON?.message || 'Unable to create card.');
            }
          })
          .always(function () {
            window.CardForm.setButtonLoading($submitButton, false);
          });
      });
    });
  });
})(window.jQuery);
