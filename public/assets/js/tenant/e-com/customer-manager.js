(function ($) {
  'use strict';

  const CustomerManager = function (options) {
    this.options = $.extend({
      modalSelector: '#customerModal',
      formSelector: '#customerForm',
      submitButtonSelector: '#customerSubmitBtn',
      onSaveSuccess: null // Callback when customer is saved
    }, options);

    this.$modal = $(this.options.modalSelector);
    this.modal = this.$modal.length ? bootstrap.Modal.getOrCreateInstance(this.$modal[0]) : null;
    this.$form = $(this.options.formSelector);
    this.$submitButton = $(this.options.submitButtonSelector);
    this.validator = null;

    this.init();
  };

  CustomerManager.prototype.init = function () {
    this.initStaticSelect2();
    this.validator = this.bindFormValidation();
    this.bindModalActions();
    this.bindSaveForm();
    this.bindCustomerTypeToggle();
    this.bindPortalPanel();
  };

  CustomerManager.prototype.bindPortalPanel = function () {
    const _this = this;
    const $panel = $('#customer_portal_panel');
    if (!$panel.length) return;

    const csrf = $('meta[name="csrf-token"]').attr('content');
    const post = (url, data) => $.ajax({ url: url, type: 'POST', data: data, headers: { 'X-CSRF-TOKEN': csrf } });

    $('#invite_portal_btn').on('click', function () {
      const url = $panel.data('invite-url');
      if (!url) { window.appNotify && window.appNotify('error', 'This customer has no email address.'); return; }
      const $btn = $(this);
      $btn.prop('disabled', true);
      post(url, {})
        .done((res) => { window.appNotify && window.appNotify('success', res.message); _this.setPortalStatus(true); })
        .fail((xhr) => window.appNotify && window.appNotify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not send invite.'))
        .always(() => $btn.prop('disabled', false));
    });

    $('#adjust_credit_btn').on('click', function () {
      const url = $panel.data('adjust-url');
      const amount = $('#adjust_credit_amount').val();
      const reason = $('#adjust_credit_reason').val();
      if (!amount || !reason) { window.appNotify && window.appNotify('error', 'Enter an amount and a reason.'); return; }
      const $btn = $(this);
      $btn.prop('disabled', true);
      post(url, { amount: amount, reason: reason })
        .done((res) => {
          window.appNotify && window.appNotify('success', res.message);
          $('#customer_portal_balance').text(res.data.credit_balance_label);
          _this.$form.find('#customer_credit_balance').val(res.data.credit_balance);
          $('#adjust_credit_amount').val(''); $('#adjust_credit_reason').val('');
          if (!$('#credit_history_wrap').hasClass('d-none')) $('#credit_history_btn').trigger('click');
        })
        .fail((xhr) => window.appNotify && window.appNotify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Adjustment failed.'))
        .always(() => $btn.prop('disabled', false));
    });

    $('#credit_history_btn').on('click', function () {
      const url = $panel.data('history-url');
      const $wrap = $('#credit_history_wrap');
      $.get(url).done((res) => {
        const rows = (res.data || []).map((t) =>
          '<tr><td class="small text-muted">' + (t.created_at_label || '') + '</td>' +
          '<td class="small text-capitalize">' + t.type + (t.order_number ? ' (' + t.order_number + ')' : '') + '</td>' +
          '<td class="small">' + (t.description || '') + '</td>' +
          '<td class="small text-end ' + (t.amount >= 0 ? 'text-success' : 'text-danger') + '">' + t.amount_label + '</td>' +
          '<td class="small text-end">' + t.balance_after_label + '</td></tr>'
        ).join('');
        $('#credit_history_body').html(rows || '<tr><td colspan="5" class="text-center text-muted py-3">No credit activity yet.</td></tr>');
        $wrap.removeClass('d-none');
      });
    });
  };

  CustomerManager.prototype.setPortalStatus = function (enabled) {
    $('#customer_portal_status')
      .toggleClass('bg-label-success', enabled)
      .toggleClass('bg-label-secondary', !enabled)
      .text('Portal: ' + (enabled ? 'On' : 'Off'));
  };

  CustomerManager.prototype.initStaticSelect2 = function () {
    const _this = this;
    if (typeof $.fn.select2 !== 'function') return;

    this.$form.find('.select2, .modal-select2').each(function () {
      const $this = $(this);
      if ($this.data('select2')) return;

      const dropdownParentSelector = $this.data('dropdown-parent');
      if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
        $this.wrap('<div class="position-relative"></div>');
      }

      const select2Config = {
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      };

      const ajaxUrl = $this.data('ajax-url');
      if (ajaxUrl) {
        select2Config.ajax = {
          url: ajaxUrl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term,
              page: params.page
            };
          },
          processResults: function (data) {
            return {
              results: data.results,
              pagination: data.pagination
            };
          },
          cache: true
        };
      }

      $this.select2(select2Config).on('change', function () {
        _this.setSelect2ErrorState($this, false);
        $this.closest('.position-relative').find('.invalid-feedback').text('');
      });
    });
  };

  CustomerManager.prototype.setSelect2ErrorState = function ($element, invalid) {
    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  CustomerManager.prototype.resetValidationState = function () {
    this.$form.find('.is-invalid').removeClass('is-invalid');
    this.$form.find('.invalid-feedback').text('');
    this.setSelect2ErrorState(this.$form.find('#customer_type'), false);
    this.setSelect2ErrorState(this.$form.find('#customer_discount_group'), false);
  };

  CustomerManager.prototype.resetForm = function () {
    this.$form[0].reset();
    this.$form.find('#customer_id').val('');
    this.$form.find('#customer_type').val('registered').trigger('change');
    this.$form.find('#customer_discount_group').val('').trigger('change');
    this.$form.find('#customer_total_visits').val(0);
    this.$form.find('#customer_lifetime_value').val('0.00');
    this.$form.find('#customer_loyalty_points_balance').val(0);
    this.$form.find('#customer_credit_balance').val('0.00');
    this.$form.find('#customerModalLabel').text('Add Customer');
    this.setSubmitButtonState(false);
    this.resetValidationState();
    if (this.validator) this.validator.resetForm();
    this.toggleDiscountGroupVisibility();
    // Portal panel is edit-only.
    $('#customer_portal_panel').addClass('d-none');
    $('#credit_history_wrap').addClass('d-none');
  };

  CustomerManager.prototype.fillForm = function (customer) {
    this.$form.find('#customer_id').val(customer.id);
    this.$form.find('#customer_type').val(customer.customer_type).trigger('change');

    const $discountGroup = this.$form.find('#customer_discount_group');
    if (customer.discount_group_id) {
      if (!$discountGroup.find("option[value='" + customer.discount_group_id + "']").length) {
        const newOption = new Option(customer.discount_group_name, customer.discount_group_id, true, true);
        $discountGroup.append(newOption).trigger('change');
      } else {
        $discountGroup.val(customer.discount_group_id).trigger('change');
      }
    } else {
      $discountGroup.val('').trigger('change');
    }

    this.$form.find('#customer_name').val(customer.name);
    this.$form.find('#customer_phone').val(customer.phone);
    this.$form.find('#customer_email').val(customer.email);
    this.$form.find('#customer_date_of_birth').val(customer.date_of_birth);
    this.$form.find('#customer_last_visit_at').val(customer.last_visit_at_form);
    this.$form.find('#customer_address').val(customer.address);
    this.$form.find('#customer_notes').val(customer.notes);
    this.$form.find('#customer_total_visits').val(customer.total_visits);
    this.$form.find('#customer_lifetime_value').val(customer.lifetime_value);
    this.$form.find('#customer_loyalty_points_balance').val(customer.loyalty_points_balance);
    this.$form.find('#customer_credit_balance').val(customer.credit_balance);
    this.$form.find('#customerModalLabel').text('Edit Customer');
    this.setSubmitButtonState(false);
    this.resetValidationState();
    this.toggleDiscountGroupVisibility();

    // Portal & store-credit panel (edit mode only).
    const $panel = $('#customer_portal_panel');
    if ($panel.length) {
      $panel.removeClass('d-none')
        .data('invite-url', customer.invite_portal_url || '')
        .data('adjust-url', customer.adjust_credit_url || '')
        .data('history-url', customer.credit_history_url || '');
      $('#customer_portal_balance').text(customer.credit_balance_label || customer.credit_balance);
      $('#credit_history_wrap').addClass('d-none');
      $('#credit_history_body').empty();
      this.setPortalStatus(Boolean(customer.has_portal_access));
      $('#invite_portal_label').text(customer.has_portal_access ? 'Resend Portal Invite' : 'Send Portal Invite');
      $('#invite_portal_btn').toggleClass('disabled', !customer.invite_portal_url);
    }
  };

  CustomerManager.prototype.setSubmitButtonState = function (loading) {
    const isEdit = Boolean(this.$form.find('#customer_id').val());
    const defaultText = isEdit ? this.$submitButton.data('update-text') : this.$submitButton.data('create-text');

    if (typeof window.appSetButtonLoading === 'function') {
      window.appSetButtonLoading(this.$submitButton, loading, 'Saving...', defaultText);
      return;
    }

    this.$submitButton.prop('disabled', loading).text(loading ? 'Saving...' : defaultText);
  };

  CustomerManager.prototype.bindFormValidation = function () {
    if (typeof $.fn.validate !== 'function') return null;

    const _this = this;
    return this.$form.validate({
      ignore: [],
      rules: {
        customer_type: { required: true },
        name: { required: true, maxlength: 150 },
        phone: { maxlength: 30 },
        email: { email: true, maxlength: 150 },
        address: { maxlength: 1000 },
        notes: { maxlength: 2000 },
        total_visits: { number: true, min: 0 },
        lifetime_value: { number: true },
        loyalty_points_balance: { number: true, min: 0 },
        credit_balance: { number: true }
      },
      messages: {
        customer_type: { required: 'Please select a customer type.' },
        name: { required: 'Please enter a customer name.', maxlength: 'The customer name may not be greater than 150 characters.' },
        email: { email: 'Please enter a valid email address.' }
      },
      errorElement: 'div',
      errorClass: 'invalid-feedback',
      highlight: function (element) {
        const $element = $(element);
        $element.addClass('is-invalid');
        if ($element.hasClass('select2-hidden-accessible')) {
          _this.setSelect2ErrorState($element, true);
        }
      },
      unhighlight: function (element) {
        const $element = $(element);
        $element.removeClass('is-invalid');
        if ($element.hasClass('select2-hidden-accessible')) {
          _this.setSelect2ErrorState($element, false);
        }
      },
      errorPlacement: function (error, element) {
        const $element = $(element);
        if ($element.hasClass('select2-hidden-accessible')) {
          $element.closest('.position-relative').find('.invalid-feedback').first().text(error.text());
          return;
        }

        const $feedback = $element.siblings('.invalid-feedback').first();
        if ($feedback.length) {
          $feedback.text(error.text());
          return;
        }

        error.insertAfter(element);
      }
    });
  };

  CustomerManager.prototype.bindModalActions = function () {
    const _this = this;
    $(document).on('click', '[data-bs-target="' + this.options.modalSelector + '"]', function () {
        const $trigger = $(this);
        if ($trigger.attr('id') === 'addCustomerBtn' || $trigger.hasClass('add-customer-btn')) {
             _this.resetForm();
        }
    });

    this.$modal.on('hidden.bs.modal', function () {
      _this.resetForm();
    });
  };

  CustomerManager.prototype.bindSaveForm = function () {
    const _this = this;
    this.$form.on('submit', function (event) {
      event.preventDefault();
      _this.resetValidationState();

      if (_this.validator && !_this.$form.valid()) return;

      _this.setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving customer...');
      }

      $.ajax({
        url: _this.$form.attr('action'),
        method: 'POST',
        data: _this.$form.serialize()
      })
        .done(function (response) {
          if (typeof window.appNotify === 'function') {
             window.appNotify('success', response.message || 'Customer saved successfully.');
          } else if (typeof toastr !== 'undefined') {
             toastr.success(response.message || 'Customer saved successfully.', 'Success');
          }
          if (_this.modal) _this.modal.hide();
          if (typeof _this.options.onSaveSuccess === 'function') {
            _this.options.onSaveSuccess(response);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 422) {
            _this.renderValidationErrors(xhr.responseJSON?.errors || {});
            return;
          }
          if (typeof window.appNotify === 'function') {
             window.appNotify('error', xhr.responseJSON?.message || 'Unable to save customer.');
          } else if (typeof toastr !== 'undefined') {
             toastr.error(xhr.responseJSON?.message || 'Unable to save customer.', 'Error');
          }
        })
        .always(function () {
          _this.setSubmitButtonState(false);
          if (window.appLoading && typeof window.appLoading.hide === 'function') {
            window.appLoading.hide(200);
          }
        });
    });
  };

  CustomerManager.prototype.renderValidationErrors = function (errors) {
    const _this = this;
    Object.entries(errors || {}).forEach(function (entry) {
      const field = entry[0];
      const message = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
      const $element = _this.$form.find('[name="' + field + '"]');

      if (!$element.length) return;

      $element.addClass('is-invalid');

      if ($element.hasClass('select2-hidden-accessible')) {
        _this.setSelect2ErrorState($element, true);
        $element.closest('.position-relative').find('.invalid-feedback').first().text(message);
        return;
      }

      const $feedback = $element.siblings('.invalid-feedback').first();
      if ($feedback.length) $feedback.text(message);
    });
  };

  CustomerManager.prototype.toggleDiscountGroupVisibility = function () {
    const $customerType = this.$form.find('#customer_type');
    const $discountGroupDiv = this.$form.find('#discount_group_div');
    const type = $customerType.val();

    if (type === 'registered' || type === 'corporate') {
      $discountGroupDiv.removeClass('d-none');
    } else {
      this.$form.find('#customer_discount_group').val('').trigger('change');
      $discountGroupDiv.addClass('d-none');
    }
  };

  CustomerManager.prototype.bindCustomerTypeToggle = function () {
    const _this = this;
    this.$form.find('#customer_type').on('change', function () {
      _this.toggleDiscountGroupVisibility();
    });

    // Trigger on modal show
    this.$modal.on('shown.bs.modal', function () {
      _this.toggleDiscountGroupVisibility();
    });
  };

  window.CustomerManager = CustomerManager;
})(window.jQuery);
