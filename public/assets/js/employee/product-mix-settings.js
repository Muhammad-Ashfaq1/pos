(function () {
  'use strict';

  const form = document.querySelector('[data-product-mix-picker]');
  if (!form) return;

  const maxSelected = Number(form.getAttribute('data-max-selected') || 4);
  const countEl = form.querySelector('[data-selected-count]');
  const previewGrid = form.querySelector('[data-pm-slots]');
  const previewEmpty = form.querySelector('[data-pm-preview-empty]');

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function checkedInputs() {
    return Array.from(form.querySelectorAll('input[name="cards[]"]:checked:not(:disabled)'));
  }

  function optionFromInput(input) {
    return input.closest('[data-pm-option]');
  }

  function slotHtml(option) {
    const label = escapeHtml(option.getAttribute('data-pm-label') || 'Card');
    const icon = escapeHtml(option.getAttribute('data-pm-icon') || 'tabler-chart-bar');
    const tone = escapeHtml(option.getAttribute('data-pm-tone') || 'primary');
    const meta = escapeHtml(option.getAttribute('data-pm-meta') || '');
    const value = escapeHtml(option.getAttribute('data-pm-value') || '0');

    return (
      '<div class="pos-glass-card pos-tone-' + tone + ' h-100">' +
        '<div class="pos-stat-body">' +
          '<div class="pos-stat-head">' +
            '<span class="pos-stat-icon"><i class="icon-base ti ' + icon + '" aria-hidden="true"></i></span>' +
            '<h6 class="pos-stat-label">' + label + '</h6>' +
          '</div>' +
          '<p class="pos-stat-value">' + value + '</p>' +
          '<p class="pos-stat-desc mb-0">' + meta + '</p>' +
        '</div>' +
      '</div>'
    );
  }

  function fillSlots(options) {
    if (!previewGrid) return;

    if (!options.length) {
      previewGrid.innerHTML = '';
      if (previewEmpty) previewEmpty.hidden = false;
      return;
    }

    if (previewEmpty) previewEmpty.hidden = true;
    previewGrid.innerHTML = options.map(slotHtml).join('');
  }

  function refresh() {
    const checked = checkedInputs();
    const selectedOptions = checked.map(optionFromInput).filter(Boolean);

    form.querySelectorAll('[data-pm-option]').forEach(function (option) {
      const input = option.querySelector('input[name="cards[]"]');
      const isChecked = Boolean(input && input.checked);
      option.classList.toggle('is-selected', isChecked);

      if (input && !isChecked && checked.length >= maxSelected) {
        input.disabled = true;
        option.classList.add('is-disabled');
      } else if (input) {
        input.disabled = false;
        option.classList.remove('is-disabled');
      }
    });

    if (countEl) {
      countEl.textContent = String(checked.length);
    }

    const groupCounts = {};
    form.querySelectorAll('[data-pm-option]').forEach(function (option) {
      const group = option.getAttribute('data-pm-group') || '';
      const input = option.querySelector('input[name="cards[]"]');
      if (!groupCounts[group]) groupCounts[group] = 0;
      if (input && input.checked) groupCounts[group] += 1;
    });

    Object.keys(groupCounts).forEach(function (group) {
      const countNode = form.querySelector('[data-group-count="' + group.replace(/"/g, '\\"') + '"]');
      if (countNode) countNode.textContent = String(groupCounts[group]);
    });

    fillSlots(selectedOptions);
  }

  form.addEventListener('change', function (event) {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) || target.name !== 'cards[]') {
      return;
    }

    if (target.checked && checkedInputs().length > maxSelected) {
      target.checked = false;
      if (typeof window.appNotify === 'function') {
        window.appNotify('warning', 'You can select a maximum of ' + maxSelected + ' cards.');
      }
    }

    refresh();
  });

  refresh();
})();
