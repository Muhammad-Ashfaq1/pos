/**
 * Universal Keyboard Navigation & Shortcuts for POS Modals
 *
 * Features:
 * 1. Smooth Tab / Shift+Tab cycling within modals:
 *    - Logical tab order from inputs -> Primary Submit button (Update/Save) -> Cancel -> Close -> First input.
 *    - Guarantees Tab lands directly on "Update Customer" / "Save" without getting trapped or skipped.
 * 2. Keyboard Shortcuts:
 *    - Ctrl + Enter / Cmd + Enter anywhere in the modal triggers primary form submit immediately.
 *    - Enter key on single-line inputs triggers form submit.
 *    - Escape key closes/cancels the modal.
 * 3. Select2 Tab Navigation:
 *    - Pressing Tab inside or on Select2 seamlessly advances to the next form field.
 * 4. Auto-Focus:
 *    - When modal opens, the first editable input is automatically focused so typing can start immediately.
 * 5. Auto-Scroll:
 *    - Smoothly scrolls .modal-body to keep keyboard-focused elements fully visible between sticky header and footer.
 */
(function ($) {
  'use strict';

  if (window.PosModalNavInitialized) {
    return;
  }
  window.PosModalNavInitialized = true;

  /**
   * Return all currently visible, interactive elements in a modal in logical tab order.
   * Footer submit button is prioritized before Cancel so Tab leads directly to Update/Save.
   */
  function getModalFocusableElements($modal) {
    const $body = $modal.find('.modal-body');
    const $footer = $modal.find('.modal-footer');
    const $header = $modal.find('.modal-header');

    const selector = [
      'input:not([type="hidden"]):not([disabled]):not([tabindex="-1"]):not([readonly])',
      'input.app-datepicker:not([disabled]):not([tabindex="-1"])',
      'input.flatpickr-input:not([disabled]):not([tabindex="-1"])',
      'input[data-enable-time]:not([disabled]):not([tabindex="-1"])',
      'select:not([disabled]):not([tabindex="-1"])',
      '.select2-selection:not([aria-disabled="true"]):not([tabindex="-1"])',
      'textarea:not([disabled]):not([readonly]):not([tabindex="-1"])',
      'button:not([disabled]):not([tabindex="-1"])',
      '[tabindex]:not([tabindex="-1"]):not([disabled])'
    ].join(', ');

    function isInteractable(el) {
      const $el = $(el);
      if ($el.is('select') && $el.hasClass('select2-hidden-accessible')) {
        return false;
      }
      if ($el.hasClass('flatpickr-input') && $el.attr('type') === 'hidden') {
        return false;
      }
      return $el.is(':visible') && $el.css('visibility') !== 'hidden' && $el.css('opacity') !== '0';
    }

    // Body interactive elements
    const bodyElements = [];
    $body.find(selector).each(function () {
      if (isInteractable(this) && !bodyElements.includes(this)) {
        bodyElements.push(this);
      }
    });

    // Primary submit button in footer
    const submitElements = [];
    const cancelElements = [];
    $footer.find(selector).each(function () {
      if (isInteractable(this)) {
        const $el = $(this);
        if ($el.is('[type="submit"]') || $el.hasClass('btn-primary') || $el.data('action') === 'submit') {
          if (!submitElements.includes(this)) submitElements.push(this);
        } else {
          if (!cancelElements.includes(this)) cancelElements.push(this);
        }
      }
    });

    // Header close button
    const headerElements = [];
    $header.find('button.btn-close:visible, [data-bs-dismiss="modal"]:visible').each(function () {
      if (!headerElements.includes(this)) {
        headerElements.push(this);
      }
    });

    // Logical order: Body Fields -> Submit Button -> Cancel Button -> Header Close Button
    return [...bodyElements, ...submitElements, ...cancelElements, ...headerElements];
  }

  /**
   * Scroll modal body smoothly so the focused element is comfortably visible.
   */
  function ensureElementVisible($modal, element) {
    const $modalBody = $modal.find('.modal-body');
    if (!$modalBody.length || !element) return;

    const $el = $(element).closest('.select2-container').length ? $(element).closest('.select2-container') : $(element);
    const bodyTop = $modalBody.offset().top;
    const bodyHeight = $modalBody.innerHeight();
    const bodyBottom = bodyTop + bodyHeight;

    const elTop = $el.offset().top;
    const elHeight = $el.outerHeight();
    const elBottom = elTop + elHeight;

    const padding = 20;

    if (elTop < bodyTop + padding) {
      // Element is scrolled above visible area
      const scrollDiff = (bodyTop + padding) - elTop;
      $modalBody.stop().animate({ scrollTop: Math.max(0, $modalBody.scrollTop() - scrollDiff) }, 150);
    } else if (elBottom > bodyBottom - padding) {
      // Element is scrolled below visible area
      const scrollDiff = elBottom - (bodyBottom - padding);
      $modalBody.stop().animate({ scrollTop: $modalBody.scrollTop() + scrollDiff }, 150);
    }
  }

  /**
   * Auto-focus the first visible, editable field when a modal opens.
   */
  $(document).on('shown.bs.modal', '.modal', function () {
    const $modal = $(this);
    const focusable = getModalFocusableElements($modal);

    if (focusable.length > 0) {
      // Focus first body input
      const firstInput = focusable[0];
      setTimeout(function () {
        if ($(firstInput).is(':visible')) {
          firstInput.focus();
          if (typeof firstInput.select === 'function' && $(firstInput).is('input[type="text"], input[type="email"], input[type="number"]')) {
            // Only select if empty or desired
          }
          ensureElementVisible($modal, firstInput);
        }
      }, 60);
    }
  });

  /**
   * Global Keyboard Navigation within Modals (Capture Phase)
   */
  function handleModalKeydown(e) {
    const $openModal = $('.modal.show').last();
    if (!$openModal.length) return;

    const target = e.target;
    const isCtrlOrCmd = e.ctrlKey || e.metaKey;

    // 1. Shortcut: Ctrl+Enter or Cmd+Enter anywhere in modal -> Submit
    if (isCtrlOrCmd && e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      const $submitBtn = $openModal.find('.modal-footer button[type="submit"]:visible, .modal-footer .btn-primary:visible, button[type="submit"]:visible').first();
      if ($submitBtn.length && !$submitBtn.prop('disabled')) {
        $submitBtn.addClass('active');
        setTimeout(() => $submitBtn.removeClass('active'), 150);
        $submitBtn.trigger('click');
      }
      return;
    }

    // 2. Shortcut: Enter in single-line text input -> Submit form
    if (e.key === 'Enter' && !isCtrlOrCmd && !e.shiftKey && !e.altKey) {
      const $target = $(target);
      const isSingleLineInput = $target.is('input:not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="checkbox"]):not([type="radio"])');
      const isSelect2Search = $target.hasClass('select2-search__field');
      const isFlatpickrInput = $target.hasClass('app-datepicker') || $target.hasClass('flatpickr-input');

      // If on datepicker or single-line input
      if (isSingleLineInput && !isSelect2Search && !isFlatpickrInput) {
        e.preventDefault();
        e.stopPropagation();
        const $submitBtn = $openModal.find('.modal-footer button[type="submit"]:visible, .modal-footer .btn-primary:visible, button[type="submit"]:visible').first();
        if ($submitBtn.length && !$submitBtn.prop('disabled')) {
          $submitBtn.addClass('active');
          setTimeout(() => $submitBtn.removeClass('active'), 150);
          $submitBtn.trigger('click');
        }
        return;
      }
    }

    // 3. Escape key: Close open dropdowns/calendars first before closing modal
    if (e.key === 'Escape') {
      let handled = false;
      $openModal.find('.app-datepicker, .flatpickr-input, [data-enable-time]').each(function () {
        if (this._flatpickr && this._flatpickr.isOpen) {
          this._flatpickr.close();
          handled = true;
        }
      });
      if ($('.select2-container--open').length) {
        try {
          $openModal.find('select.select2-hidden-accessible').select2('close');
          handled = true;
        } catch (err) {}
      }
      if (handled) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
    }

    // 4. Tab and Shift+Tab Smooth Navigation
    if (e.key === 'Tab') {
      const focusable = getModalFocusableElements($openModal);
      if (!focusable || focusable.length === 0) return;

      // Check if current target is Select2 search input (dropdown open)
      const isSelect2Search = $(target).hasClass('select2-search__field');
      const isInsideFlatpickr = Boolean($(target).closest('.flatpickr-calendar').length);
      const isDatepicker = $(target).hasClass('app-datepicker') || $(target).hasClass('flatpickr-input') || $(target).is('[data-enable-time]');

      let currentElement = target;

      if (isSelect2Search) {
        // Find which select2 container opened this
        const $openSelect2 = $openModal.find('.select2-container--open');
        if ($openSelect2.length) {
          const $selection = $openSelect2.find('.select2-selection');
          if ($selection.length) {
            currentElement = $selection[0];
          }
        }
      } else if (isInsideFlatpickr) {
        // Target is inside an open calendar container (e.g. day cell, time input, am/pm)
        const calendarEl = $(target).closest('.flatpickr-calendar')[0];
        $openModal.find('.app-datepicker, .flatpickr-input, [data-enable-time]').each(function () {
          const fp = this._flatpickr;
          if (fp && fp.calendarContainer === calendarEl) {
            currentElement = fp.altInput || fp.input || this;
            return false;
          }
        });
      } else if (isDatepicker) {
        // Target is the input element or its associated flatpickr input
        $openModal.find('.app-datepicker, .flatpickr-input, [data-enable-time]').each(function () {
          const fp = this._flatpickr;
          if (this === target || (fp && (fp.input === target || fp.altInput === target))) {
            currentElement = fp ? (fp.altInput || fp.input || this) : this;
            return false;
          }
        });
      }

      let currentIndex = focusable.indexOf(currentElement);

      // If current element wasn't directly found, find closest match or container
      if (currentIndex === -1) {
        const $closestContainer = $(target).closest('.select2-selection, button, input, select, textarea');
        if ($closestContainer.length) {
          currentIndex = focusable.indexOf($closestContainer[0]);
        }
      }

      let nextIndex;
      if (e.shiftKey) {
        // Shift + Tab (Backward)
        if (currentIndex <= 0) {
          // If on first field or outside, cycle to the submit button or last element
          const submitIndex = focusable.findIndex(el => $(el).is('[type="submit"], .btn-primary'));
          nextIndex = submitIndex !== -1 ? submitIndex : focusable.length - 1;
        } else {
          nextIndex = currentIndex - 1;
        }
      } else {
        // Tab (Forward)
        if (currentIndex === -1 || currentIndex >= focusable.length - 1) {
          // Wrap to first element
          nextIndex = 0;
        } else {
          nextIndex = currentIndex + 1;
        }
      }

      const nextElement = focusable[nextIndex];
      if (nextElement) {
        e.preventDefault();
        e.stopPropagation();

        // Close any open Flatpickr calendars cleanly before advancing
        $openModal.find('.app-datepicker, .flatpickr-input, [data-enable-time]').each(function () {
          if (this._flatpickr && this._flatpickr.isOpen) {
            try {
              this._flatpickr.close();
            } catch (err) {}
          }
        });
        $('.flatpickr-calendar.open').each(function () {
          $(this).removeClass('open').hide();
        });

        // Close any open Select2 dropdowns cleanly before advancing
        if ($('.select2-container--open').length) {
          try {
            $openModal.find('select.select2-hidden-accessible').select2('close');
          } catch (err) {}
        }

        // Highlight focused element
        $openModal.find('.is-keyboard-focused').removeClass('is-keyboard-focused');
        $(nextElement).addClass('is-keyboard-focused');
        nextElement.focus();
        ensureElementVisible($openModal, nextElement);
      }
    }
  }

  // Register in capture phase so our handler runs before any third-party calendar focus traps
  document.addEventListener('keydown', handleModalKeydown, true);

  // Remove focus highlighting on blur
  $(document).on('blur', '.modal button, .modal input, .modal select, .modal textarea, .modal .select2-selection, .modal .nav-link', function () {
    $(this).removeClass('is-keyboard-focused');
  });

  // Track focus styling for keyboard users
  $(document).on('focus', '.modal button, .modal input, .modal select, .modal textarea, .modal .select2-selection, .modal .nav-link', function () {
    const $modal = $(this).closest('.modal');
    if ($modal.length) {
      ensureElementVisible($modal, this);
    }
  });

})(jQuery);
