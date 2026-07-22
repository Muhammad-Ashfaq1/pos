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

  const clearFieldError = function ($element) {
    const $field = $element.closest('[data-card-field]');
    $element.removeClass('is-invalid');

    if ($field.length) {
      $field.find('[data-card-error]').text('');
    } else {
      $element.siblings('.invalid-feedback').first().text('');
      $element.closest('.position-relative').find('.invalid-feedback').first().text('');
    }

    if ($element.hasClass('select2-hidden-accessible')) {
      setSelect2ErrorState($element, false);
    }
  };

  const clearValidation = function ($form) {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('[data-card-error]').text('');
    $form.find('.invalid-feedback').text('');
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

      const $field = $element.closest('[data-card-field]');
      if ($field.length) {
        $field.find('[data-card-error="' + baseField + '"]').first().text(message);
        return;
      }

      const $feedback = $element.siblings('.invalid-feedback').first();
      if ($feedback.length) {
        $feedback.text(message);
        return;
      }

      $element.closest('.position-relative').find('.invalid-feedback').first().text(message);
    });
  };

  const forceBelowDropdownAdapter = function () {
    if (!$.fn.select2 || !$.fn.select2.amd) {
      return null;
    }

    const Utils = $.fn.select2.amd.require('select2/utils');
    const Dropdown = $.fn.select2.amd.require('select2/dropdown');
    const DropdownSearch = $.fn.select2.amd.require('select2/dropdown/search');
    const AttachBody = $.fn.select2.amd.require('select2/dropdown/attachBody');

    function AttachBodyForceBelow() {}

    AttachBodyForceBelow.prototype._positionDropdown = function () {
      const offset = this.$container.offset();
      const containerBottom = offset.top + this.$container.outerHeight(false);
      const css = {
        left: offset.left,
        top: containerBottom
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

    return Utils.Decorate(
      Utils.Decorate(Utils.Decorate(Dropdown, DropdownSearch), AttachBody),
      AttachBodyForceBelow
    );
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
      const selectOptions = {
        width: '100%',
        placeholder: $select.data('placeholder') || 'Select a product',
        allowClear: true,
        closeOnSelect: false,
        minimumResultsForSearch: 0,
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $select.parent()
      };

      if (dropdownAdapter) {
        selectOptions.dropdownAdapter = dropdownAdapter;
      }

      $select.select2(selectOptions).on('change', function () {
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
