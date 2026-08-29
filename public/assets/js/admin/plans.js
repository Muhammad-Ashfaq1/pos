(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#planModal');
  const $form = $('#planForm');
  const $submitButton = $('#planSubmitBtn');
  const $table = $('.plans-datatables');
  const $durationType = $('#plan_duration_type');
  let planTable = null;

  if (!$table.length || !$form.length) {
    return;
  }

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
  });

  const notify = function (type, message) {
    if (typeof window.appNotify === 'function') {
      window.appNotify(type, message);
    }
  };

  const alignToolbar = function (table) {
    if (window.PosListingToolbar) {
      window.PosListingToolbar.align(table);
    }
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const setSelect2ErrorState = function ($element, invalid) {
    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  const initStaticSelect2 = function () {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $('.select2').each(function () {
      const $this = $(this);

      if ($this.data('select2')) {
        return;
      }

      const dropdownParentSelector = $this.data('dropdown-parent');

      if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
        $this.wrap('<div class="position-relative"></div>');
      }

      $this.select2({
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      }).on('change', function () {
        setSelect2ErrorState($this, false);
        $this.closest('.col-md-6, .mb-3, .position-relative').find('.invalid-feedback').first().text('');
      });
    });
  };

  const resetForm = function () {
    $form[0].reset();
    $('#plan_id').val('');
    $durationType.val('monthly').trigger('change');
    $('#plan_is_active').prop('checked', true);
    $('#planModalLabel').text('Add Plan');
    $submitButton.text($submitButton.data('create-text') || 'Save Plan');
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    setSelect2ErrorState($durationType, false);
  };

  const fillForm = function (row) {
    $('#plan_id').val(row.id);
    $('#plan_name').val(row.name);
    $('#plan_description').val(row.description || '');
    $('#plan_price').val(row.price ?? '');
    $('#plan_is_active').prop('checked', !!row.is_active);
    $durationType.val(row.duration_type || 'monthly').trigger('change');
    $('#planModalLabel').text('Edit Plan');
    $submitButton.text($submitButton.data('update-text') || 'Update Plan');
  };

  planTable = new DataTable($table[0], {
    processing: true,
    serverSide: true,
    ajax: {
      global: false,
      url: window.planListingUrl,
      data: function (d) {
        d.status = $('#planStatusFilter').val();
        d.sort = $('#planSortFilter').val();
      }
    },
    pageLength: 10,
    layout: {
      topStart: { search: { placeholder: 'Search plans', text: '_INPUT_', className: 'form-control' } },
      topEnd: null,
      bottomStart: { rowClass: 'row mx-3 my-md-0 me-3 ms-0 justify-content-between', features: ['info', { pageLength: { menu: [10, 25, 50, 100], text: '_MENU_' } }] },
      bottomEnd: 'paging'
    },
    language: { emptyTable: 'No plans found', search: '', searchPlaceholder: 'Search plans' },
    columns: [
      { data: null, orderable: false, searchable: false, render: function (_d, _t, _r, meta) { return meta.settings._iDisplayStart + meta.row + 1; } },
      { data: 'name', render: function (data, _t, row) { return '<span class="fw-semibold">' + escapeHtml(data) + '</span>' + (row.description ? '<small class="text-muted d-block">' + escapeHtml(row.description) + '</small>' : ''); } },
      { data: 'duration_label', render: function (data) { return escapeHtml(data || '—'); } },
      { data: 'price', render: function (data) { return data ? '$' + escapeHtml(data) : '—'; } },
      { data: 'is_active', render: function (data) { return data ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>'; } },
      { data: null, orderable: false, searchable: false, className: 'text-center', render: function (_d, _t, row) {
        return '<div class="d-inline-flex gap-1">' +
          '<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-plan-btn" data-id="' + row.id + '" title="Edit"><i class="icon-base ti tabler-edit"></i></button>' +
          '<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-plan-btn" data-url="' + escapeHtml(row.delete_url) + '" data-name="' + escapeHtml(row.name) + '" title="Delete"><i class="icon-base ti tabler-trash"></i></button>' +
        '</div>';
      }}
    ],
    drawCallback: function () { alignToolbar(this.api()); }
  });

  initStaticSelect2();
  alignToolbar(planTable);

  $('#planStatusFilter, #planSortFilter').on('change', function () {
    planTable.ajax.reload(null, false);
  });

  $('#addPlanBtn').on('click', resetForm);

  $(document).on('click', '.edit-plan-btn', function () {
    const row = planTable.row($(this).closest('tr')).data();
    if (!row) return;
    resetForm();
    fillForm(row);
    if ($modal.length && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance($modal[0]).show();
    }
  });

  $form.on('submit', function (e) {
    e.preventDefault();
    $submitButton.prop('disabled', true);
    $.ajax({ url: $form.attr('action'), method: 'POST', data: $form.serialize() })
      .done(function (response) {
        notify('success', response.message || 'Plan saved.');
        if ($modal.length && window.bootstrap) bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
        planTable.ajax.reload(null, false);
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          Object.entries(xhr.responseJSON.errors).forEach(function (entry) {
            const field = entry[0];
            const messages = entry[1];
            const $input = $form.find('[name="' + field + '"]');

            if ($input.length) {
              $input.addClass('is-invalid');

              if ($input.hasClass('select2-hidden-accessible')) {
                setSelect2ErrorState($input, true);
              }

              $input.closest('.col-md-6, .position-relative').find('.invalid-feedback').first().text(messages[0]);
            }
          });
          return;
        }
        notify('error', xhr.responseJSON?.message || 'Unable to save plan.');
      })
      .always(function () { $submitButton.prop('disabled', false); });
  });

  $(document).on('click', '.delete-plan-btn', function () {
    const deleteUrl = $(this).data('url');
    const name = $(this).data('name');
    if (!window.PosConfirm) return;
    window.PosConfirm.open({
      title: 'Delete plan?',
      message: 'This will remove "' + name + '".',
      confirmText: 'Yes, delete it',
      cancelText: 'Cancel',
      tone: 'danger',
      onConfirm: function () {
        return $.ajax({ url: deleteUrl, method: 'DELETE' }).then(
          function (response) { planTable.ajax.reload(null, false); notify('success', response.message || 'Plan deleted.'); },
          function (xhr) { throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete plan.'); }
        );
      }
    });
  });
})(jQuery);
