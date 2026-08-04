/**
 * Shared Flatpickr date picker — app-wide YYYY-MM-DD UI.
 *
 * Targets: input.app-datepicker, input[type="date"]
 */
(function (window, document, $) {
  'use strict';

  const SELECTOR = 'input.app-datepicker, input[type="date"]';

  function getFlatpickr() {
    if (typeof window.flatpickr === 'function') {
      return window.flatpickr;
    }
    // Webpack UMD may expose { flatpickr: fn } on window
    if (window.flatpickr && typeof window.flatpickr.flatpickr === 'function') {
      window.flatpickr = window.flatpickr.flatpickr;
      return window.flatpickr;
    }
    return null;
  }

  function readAttr(el, name, fallback) {
    const value = el.getAttribute(name);
    return value !== null && value !== '' ? value : fallback;
  }

  function getBootstrapFocusTrap(el) {
    if (!el || !window.bootstrap) return null;

    const modalEl = el.closest('.modal');
    if (modalEl && bootstrap.Modal) {
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal && modal._focustrap) return modal._focustrap;
    }

    const offcanvasEl = el.closest('.offcanvas');
    if (offcanvasEl && bootstrap.Offcanvas) {
      const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
      if (offcanvas && offcanvas._focustrap) return offcanvas._focustrap;
    }

    return null;
  }

  // Stop Bootstrap focus-trap from yanking focus out of the body-appended calendar
  let focusGuardBound = false;
  function ensureFocusGuard() {
    if (focusGuardBound) return;
    focusGuardBound = true;
    document.addEventListener(
      'focusin',
      function (event) {
        const target = event.target;
        if (!target || !target.closest) return;
        if (target.closest('.flatpickr-calendar')) {
          event.stopPropagation();
        }
      },
      true
    );
  }

  function buildOptions(el) {
    ensureFocusGuard();

    const options = {
      dateFormat: readAttr(el, 'data-date-format', 'Y-m-d'),
      allowInput: true,
      clickOpens: true,
      disableMobile: true,
      monthSelectorType: 'static',
      // Always on body so modal/offcanvas overflow does not clip the calendar
      appendTo: document.body,
      static: false,
      position: 'below',
      onReady: function (_selected, _dateStr, instance) {
        if (instance && instance.calendarContainer) {
          instance.calendarContainer.classList.add('app-datepicker-calendar');
        }
      },
      onOpen: function (_selected, _dateStr, instance) {
        if (!instance || !instance.calendarContainer) return;

        instance.calendarContainer.style.zIndex = '200050';
        instance.calendarContainer.style.display = 'inline-block';
        instance.calendarContainer.style.visibility = 'visible';
        instance.calendarContainer.style.opacity = '1';

        // Bootstrap focus trap closes body-appended calendars immediately
        const trap = getBootstrapFocusTrap(instance.element);
        if (trap && typeof trap.deactivate === 'function') {
          trap.deactivate();
          instance._appDatepickerTrap = trap;
        }
      },
      onClose: function (_selected, _dateStr, instance) {
        if (instance && instance._appDatepickerTrap && typeof instance._appDatepickerTrap.activate === 'function') {
          instance._appDatepickerTrap.activate();
          instance._appDatepickerTrap = null;
        }
      },
    };

    const minDate = readAttr(el, 'min', readAttr(el, 'data-date-min', null));
    const maxDate = readAttr(el, 'max', readAttr(el, 'data-date-max', null));
    const defaultDate = el.value || null;

    if (minDate) options.minDate = minDate;
    if (maxDate) options.maxDate = maxDate;
    if (defaultDate) options.defaultDate = defaultDate;

    if (el.hasAttribute('data-enable-time') || el.classList.contains('app-datepicker-time')) {
      options.enableTime = true;
      options.dateFormat = readAttr(el, 'data-date-format', 'Y-m-d H:i');
    }

    return options;
  }

  function normalizeInput(el) {
    if (el.getAttribute('type') === 'date') {
      el.setAttribute('type', 'text');
    }
    el.classList.add('app-datepicker', 'form-control');
    if (!el.getAttribute('placeholder')) {
      el.setAttribute('placeholder', 'YYYY-MM-DD');
    }
    el.setAttribute('autocomplete', 'off');
    el.setAttribute('inputmode', 'none');
  }

  function initOne(el) {
    const flatpickr = getFlatpickr();
    if (!flatpickr || !el) {
      return null;
    }

    if (el._flatpickr) {
      return el._flatpickr;
    }

    if (el.disabled) {
      return null;
    }

    normalizeInput(el);

    try {
      return flatpickr(el, buildOptions(el));
    } catch (error) {
      console.warn('AppDatepicker init failed', error);
      return null;
    }
  }

  function init(root) {
    const flatpickr = getFlatpickr();
    if (!flatpickr) {
      console.warn('AppDatepicker: flatpickr library missing');
      return;
    }

    const scope = !root
      ? document
      : root.nodeType
        ? root
        : document.querySelector(root);

    if (!scope || !scope.querySelectorAll) {
      return;
    }

    scope.querySelectorAll(SELECTOR).forEach(initOne);
  }

  function resolveEl(el) {
    if (!el) return null;
    if (typeof el === 'string') return document.querySelector(el);
    if (el.jquery) return el[0];
    return el;
  }

  function set(el, value) {
    const input = resolveEl(el);
    if (!input) return;

    if (!input._flatpickr) {
      initOne(input);
    }

    if (input._flatpickr) {
      if (value) {
        input._flatpickr.setDate(value, true);
      } else {
        input._flatpickr.clear();
      }
      return;
    }

    input.value = value || '';
  }

  function setMin(el, minDate) {
    const input = resolveEl(el);
    if (!input) return;

    if (minDate) {
      input.setAttribute('min', minDate);
    } else {
      input.removeAttribute('min');
    }

    if (!input._flatpickr) {
      initOne(input);
    }

    if (input._flatpickr) {
      input._flatpickr.set('minDate', minDate || null);
    }
  }

  function setMax(el, maxDate) {
    const input = resolveEl(el);
    if (!input) return;

    if (maxDate) {
      input.setAttribute('max', maxDate);
    } else {
      input.removeAttribute('max');
    }

    if (!input._flatpickr) {
      initOne(input);
    }

    if (input._flatpickr) {
      input._flatpickr.set('maxDate', maxDate || null);
    }
  }

  function open(el) {
    const input = resolveEl(el);
    if (!input) return;
    if (!input._flatpickr) initOne(input);
    if (input._flatpickr) input._flatpickr.open();
  }

  window.AppDatepicker = {
    init: init,
    set: set,
    setMin: setMin,
    setMax: setMax,
    open: open,
  };

  function boot() {
    const tryInit = function (attemptsLeft) {
      if (!getFlatpickr()) {
        if (attemptsLeft <= 0) {
          console.warn('AppDatepicker: flatpickr library missing');
          return;
        }
        window.setTimeout(function () {
          tryInit(attemptsLeft - 1);
        }, 50);
        return;
      }

      init(document);
    };

    tryInit(40);

    document.addEventListener('shown.bs.modal', function (event) {
      init(event.target);
    });

    document.addEventListener('shown.bs.offcanvas', function (event) {
      init(event.target);
    });

    // Re-init only when new date inputs appear (avoid loops from calendar DOM)
    if (window.MutationObserver) {
      let timer = null;
      const observer = new MutationObserver(function (mutations) {
        let needsInit = false;
        for (let i = 0; i < mutations.length; i++) {
          const nodes = mutations[i].addedNodes;
          for (let j = 0; j < nodes.length; j++) {
            const node = nodes[j];
            if (!node || node.nodeType !== 1) continue;
            if (node.classList && node.classList.contains('flatpickr-calendar')) continue;
            if (node.matches && node.matches(SELECTOR)) {
              needsInit = true;
              break;
            }
            if (node.querySelectorAll && node.querySelectorAll(SELECTOR).length) {
              needsInit = true;
              break;
            }
          }
          if (needsInit) break;
        }
        if (!needsInit) return;
        window.clearTimeout(timer);
        timer = window.setTimeout(function () {
          init(document);
        }, 100);
      });
      observer.observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  if ($ && $.fn && !$.fn.__appDatepickerValPatched) {
    const originalVal = $.fn.val;
    $.fn.val = function () {
      if (arguments.length === 0) {
        return originalVal.apply(this, arguments);
      }

      const value = arguments[0];
      const result = originalVal.apply(this, arguments);

      this.each(function () {
        if (!this._flatpickr) return;
        const next = value == null ? '' : String(value);
        const current = this._flatpickr.input ? this._flatpickr.input.value : this.value;
        if (next === current) return;
        if (next) {
          this._flatpickr.setDate(next, false);
        } else {
          this._flatpickr.clear();
        }
      });

      return result;
    };
    $.fn.__appDatepickerValPatched = true;
  }
})(window, document, window.jQuery);
