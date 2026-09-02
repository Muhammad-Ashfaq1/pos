(function (window, $) {
  'use strict';

  if (!$) {
    return;
  }

  const fieldElement = function ($form, field) {
    let $element = $form.find('[name="' + field + '"]').first();

    if (!$element.length && field.indexOf('.') !== -1) {
      const baseField = field.split('.')[0];
      $element = $form.find('[name="' + baseField + '[]"], [name="' + baseField + '"]').first();
    }

    return $element;
  };

  const setSelect2ErrorState = function ($element, invalid) {
    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  const fieldFeedback = function ($element, baseField) {
    const $field = $element.closest('[data-card-field]');
    if ($field.length && baseField) {
      const $cardError = $field.find('[data-card-error="' + baseField + '"]').first();
      if ($cardError.length) {
        return $cardError;
      }
    }

    if ($element.hasClass('select2-hidden-accessible')) {
      return $element.closest('.position-relative').find('.invalid-feedback').first();
    }

    const $siblings = $element.siblings('.invalid-feedback').first();
    if ($siblings.length) {
      return $siblings;
    }

    return $element.closest('.position-relative').find('.invalid-feedback').first();
  };

  const clearFieldError = function ($element) {
    const fieldName = $element.attr('name') ? $element.attr('name').split('.')[0].replace('[]', '') : '';
    $element.removeClass('is-invalid');

    const $feedback = fieldFeedback($element, fieldName);
    if ($feedback.length) {
      $feedback.text('').removeClass('d-block').css('display', '');
    }

    const $field = $element.closest('[data-card-field]');
    if ($field.length) {
      $field.find('[data-card-error]').text('').removeClass('d-block').css('display', '');
    }

    if ($element.hasClass('select2-hidden-accessible')) {
      setSelect2ErrorState($element, false);
    }
  };

  const clearValidation = function ($form) {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('[data-card-error]').text('').removeClass('d-block').css('display', '');
    $form.find('.invalid-feedback').text('').removeClass('d-block').css('display', '');
    $form.find('.select2-hidden-accessible').each(function () {
      setSelect2ErrorState($(this), false);
    });
  };

  const renderValidationErrors = function ($form, errors) {
    Object.entries(errors || {}).forEach(function (entry) {
      const field = entry[0];
      const baseField = field.split('.')[0];
      const message = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
      const $element = fieldElement($form, field);

      if (!$element.length) {
        return;
      }

      $element.addClass('is-invalid');
      if ($element.hasClass('select2-hidden-accessible')) {
        setSelect2ErrorState($element, true);
      }

      const $feedback = fieldFeedback($element, baseField);
      if ($feedback.length) {
        $feedback.text(message).addClass('d-block').css('display', 'block');
      }
    });
  };

  const forceBelowDropdownAdapter = function () {
    if (!$.fn.select2 || !$.fn.select2.amd) {
      return null;
    }

    const Utils = $.fn.select2.amd.require('select2/utils');
    const Dropdown = $.fn.select2.amd.require('select2/dropdown');
    const AttachBody = $.fn.select2.amd.require('select2/dropdown/attachBody');

    function AttachBodyForceBelow() {}

    AttachBodyForceBelow.prototype._positionDropdown = function () {
      const offset = this.$container.offset();
      const css = {
        left: offset.left,
        top: offset.top + this.$container.outerHeight(false)
      };

      let $offsetParent = this.$dropdownParent;
      if (!$offsetParent || !$offsetParent.length) {
        $offsetParent = $(document.body);
      }

      if ($offsetParent[0] !== document.body) {
        const parentOffset = $offsetParent.offset();
        css.top -= parentOffset.top;
        css.left -= parentOffset.left;
        css.top += $offsetParent.scrollTop();
        css.left += $offsetParent.scrollLeft();
      }

      this.$dropdown
        .removeClass('select2-dropdown--above')
        .addClass('select2-dropdown--below');
      this.$container
        .removeClass('select2-container--above')
        .addClass('select2-container--below');

      this.$dropdownContainer.css(css);
    };

    // AttachBody only — do NOT decorate DropdownSearch (breaks multi-select results).
    return Utils.Decorate(Utils.Decorate(Dropdown, AttachBody), AttachBodyForceBelow);
  };

  /**
   * Shared product multi-select with modal-safe dropdown placement.
   *
   * @param {{ $root?: JQuery, onChange?: Function }} [options]
   */
  const initProductSelects = function (options) {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    options = options || {};
    const $root = options.$root || $(document);
    const onChange = typeof options.onChange === 'function' ? options.onChange : null;
    const dropdownAdapter = forceBelowDropdownAdapter();

    $root.find('.card-product-select').each(function () {
      const $select = $(this);

      if ($select.data('select2')) {
        return;
      }

      const dropdownParentSelector = $select.data('dropdown-parent');
      const $dropdownParent = dropdownParentSelector
        ? $(dropdownParentSelector)
        : $select.parent();

      const selectOptions = {
        width: '100%',
        placeholder: $select.data('placeholder') || 'Select a product',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $dropdownParent.length ? $dropdownParent : $(document.body)
      };

      if (dropdownAdapter) {
        selectOptions.dropdownAdapter = dropdownAdapter;
      }

      $select
        .select2(selectOptions)
        .on('change select2:select select2:unselect', function () {
          $select.next('.select2').find('.select2-search__field').css('width', '100%');
          clearFieldError($select);
          if (onChange) {
            onChange($select);
          }
        });
    });
  };

  /**
   * Toggle percentage vs fixed amount UI for discount cards.
   *
   * @param {JQuery} $form
   * @param {{ currencySymbol?: string }} [options]
   */
  const updateDiscountFields = function ($form, options) {
    const $discountType = $form.find('[data-card-discount-type]');
    if (!$discountType.length) {
      return;
    }

    options = options || {};
    const currencySymbol =
      options.currencySymbol ||
      window.appCurrency?.symbol ||
      window.currencySymbol ||
      '';

    const isPercentage = $discountType.val() === 'percentage';
    $form.find('[data-card-value-label]').html(
      (isPercentage ? 'Discount Percentage' : 'Fixed Amount') +
        ' <span class="text-danger">*</span>'
    );
    $form.find('[data-card-value-prefix]').text(isPercentage ? '%' : currencySymbol);
    $form.find('[data-card-value]').attr('max', isPercentage ? '100' : null);
  };

  const setButtonLoading = function ($button, loading, loadingText) {
    if (!$button || !$button.length) {
      return;
    }

    const defaultText =
      $button.data('default-text') ||
      $button.data('create-text') ||
      $button.text().trim();
    $button.data('default-text', defaultText);

    if (typeof window.appSetButtonLoading === 'function') {
      window.appSetButtonLoading($button, loading, loadingText || 'Saving...', defaultText);
      return;
    }

    $button.prop('disabled', loading).text(loading ? loadingText || 'Saving...' : defaultText);
  };

  window.CardForm = Object.freeze({
    clearFieldError: clearFieldError,
    clearValidation: clearValidation,
    renderValidationErrors: renderValidationErrors,
    setSelect2ErrorState: setSelect2ErrorState,
    initProductSelects: initProductSelects,
    updateDiscountFields: updateDiscountFields,
    setButtonLoading: setButtonLoading
  });
})(window, window.jQuery);
