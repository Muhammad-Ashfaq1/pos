(function ($) {
  'use strict';

  const VehicleManager = function (options) {
    this.options = $.extend({
      modalSelector: '#vehicleModal',
      formSelector: '#vehicleForm',
      submitButtonSelector: '#vehicleSubmitBtn',
      onSaveSuccess: null
    }, options);

    this.$modal = $(this.options.modalSelector);
    this.modal = this.$modal.length ? bootstrap.Modal.getOrCreateInstance(this.$modal[0]) : null;
    this.$form = $(this.options.formSelector);
    this.$submitButton = $(this.options.submitButtonSelector);
    this.validator = null;

    this.init();
  };

  VehicleManager.prototype.init = function () {
    this.initSelect2();
    this.validator = this.bindFormValidation();
    this.bindModalActions();
    this.bindSaveForm();
    this.bindEntryModeSwitch();
  };

  VehicleManager.prototype.initSelect2 = function () {
    const _this = this;
    if (typeof $.fn.select2 !== 'function') return;

    this.$form.find('.select2, .customer-select2, .modal-select2').each(function () {
      const $this = $(this);
      if ($this.data('select2')) return;

      const ajaxUrl = $this.data('ajax-url');
      const options = {
        dropdownParent: $this.data('dropdown-parent') ? $($this.data('dropdown-parent')) : $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      };

      if (ajaxUrl) {
        options.ajax = {
          url: ajaxUrl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term,
              page: params.page || 1
            };
          },
          processResults: function (data) {
            return {
              results: data.results,
              pagination: { more: data.pagination ? data.pagination.more : false }
            };
          },
          cache: true
        };
      }

      $this.select2(options).on('change', function () {
        _this.setSelect2ErrorState($this, false);
      });
    });
  };

  VehicleManager.prototype.setSelect2ErrorState = function ($element, invalid) {
    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  VehicleManager.prototype.fieldFeedback = function ($field) {
    if ($field.hasClass('select2-hidden-accessible')) {
      return $field.closest('.position-relative').find('.invalid-feedback').first();
    }
    return $field.siblings('.invalid-feedback').first();
  };

  VehicleManager.prototype.applyFieldError = function (field, message) {
    if (!message) {
      return;
    }

    const $field = this.$form.find('[name="' + field + '"]').first();

    if (! $field.length) {
      return;
    }

    $field.addClass('is-invalid');
    if ($field.hasClass('select2-hidden-accessible')) {
      this.setSelect2ErrorState($field, true);
    }
    this.fieldFeedback($field).text(message).addClass('d-block').css('display', 'block');
  };

  VehicleManager.prototype.resetValidationState = function () {
    this.$form.find('.is-invalid').removeClass('is-invalid');
    this.$form.find('.invalid-feedback').text('').removeClass('d-block').css('display', '');
    this.setSelect2ErrorState(this.$form.find('.select2, .customer-select2'), false);
  };

  VehicleManager.prototype.resetForm = function () {
    this.$form[0].reset();
    this.$form.find('#vehicle_id').val('');
    this.$form.find('#vehicle_customer_entry_mode').val('existing').trigger('change');
    this.$form.find('#vehicle_customer_id').val(null).trigger('change');
    this.$form.find('#vehicle_is_default').prop('checked', false);
    this.$form.find('#vehicleModalLabel').text('Add Vehicle');
    this.setSubmitButtonState(false);
    this.resetValidationState();
    if (this.validator) this.validator.resetForm();
  };

  VehicleManager.prototype.fillForm = function (vehicle) {
    this.$form.find('#vehicle_id').val(vehicle.id);
    this.$form.find('#vehicle_customer_entry_mode').val('existing').trigger('change');
    
    if (vehicle.customer_id) {
        const $customerSelect = this.$form.find('#vehicle_customer_id');
        const newOption = new Option(vehicle.customer_name || 'Selected Customer', vehicle.customer_id, true, true);
        $customerSelect.append(newOption).trigger('change');
    }

    this.$form.find('#vehicle_plate_number').val(vehicle.plate_number);
    this.$form.find('#vehicle_registration_number').val(vehicle.registration_number);
    this.$form.find('#vehicle_make').val(vehicle.make);
    this.$form.find('#vehicle_model').val(vehicle.model);
    this.$form.find('#vehicle_year').val(vehicle.year);
    this.$form.find('#vehicle_color').val(vehicle.color);
    this.$form.find('#vehicle_engine_type').val(vehicle.engine_type);
    this.$form.find('#vehicle_odometer').val(vehicle.odometer);
    this.$form.find('#vehicle_is_default').prop('checked', Boolean(vehicle.is_default));
    this.$form.find('#vehicle_notes').val(vehicle.notes);
    
    this.$form.find('#vehicleModalLabel').text('Edit Vehicle');
    this.setSubmitButtonState(false);
    this.resetValidationState();
  };

  VehicleManager.prototype.setSubmitButtonState = function (loading) {
    const isEdit = Boolean(this.$form.find('#vehicle_id').val());
    const defaultText = isEdit ? this.$submitButton.data('update-text') : this.$submitButton.data('create-text');

    if (typeof window.appSetButtonLoading === 'function') {
      window.appSetButtonLoading(this.$submitButton, loading, 'Saving...', defaultText);
      return;
    }

    this.$submitButton.prop('disabled', loading).text(loading ? 'Saving...' : defaultText);
  };

  VehicleManager.prototype.bindFormValidation = function () {
    if (typeof $.fn.validate !== 'function') return null;

    const _this = this;
    return this.$form.validate({
      ignore: [],
      rules: {
        customer_id: { required: function() { return $('#vehicle_customer_entry_mode').val() === 'existing'; } },
        inline_customer_name: { required: function() { return $('#vehicle_customer_entry_mode').val() === 'walk_in'; }, maxlength: 150 },
        inline_customer_phone: { maxlength: 30 },
        inline_customer_email: { email: true, maxlength: 150 },
        inline_customer_address: { maxlength: 1000 },
        plate_number: { required: true, maxlength: 50 },
        registration_number: { maxlength: 80 },
        make: { maxlength: 100 },
        model: { maxlength: 100 },
        year: { number: true, min: 1900 },
        color: { maxlength: 50 },
        engine_type: { maxlength: 80 },
        odometer: { number: true, min: 0 }
      },
      messages: {
        customer_id: { required: 'Please select a customer.' },
        inline_customer_name: { required: 'Please enter walk-in customer details.', maxlength: 'Customer name may not be greater than 150 characters.' },
        inline_customer_phone: { maxlength: 'Phone number may not be greater than 30 characters.' },
        inline_customer_email: { email: 'Please enter a valid email address.', maxlength: 'Email may not be greater than 150 characters.' },
        inline_customer_address: { maxlength: 'Address may not be greater than 1000 characters.' },
        plate_number: { required: 'Please enter a plate number.', maxlength: 'Plate number may not be greater than 50 characters.' },
        registration_number: { maxlength: 'Registration number may not be greater than 80 characters.' },
        make: { maxlength: 'Make may not be greater than 100 characters.' },
        model: { maxlength: 'Model may not be greater than 100 characters.' },
        year: { number: 'Year must be numeric.', min: 'Year must be 1900 or greater.' },
        color: { maxlength: 'Color may not be greater than 50 characters.' },
        engine_type: { maxlength: 'Engine type may not be greater than 80 characters.' },
        odometer: { number: 'Odometer must be numeric.', min: 'Odometer cannot be negative.' }
      },
      errorElement: 'div',
      errorClass: 'jquery-validate-error',
      errorPlacement: function (error, element) {
        _this.applyFieldError($(element).attr('name'), error.text());
      },
      highlight: function (element) {
        const $element = $(element);
        $element.addClass('is-invalid');
        if ($element.hasClass('select2-hidden-accessible')) {
          _this.setSelect2ErrorState($element, true);
        }
      },
      unhighlight: function (element) {
        const $field = $(element);
        $field.removeClass('is-invalid');
        if ($field.hasClass('select2-hidden-accessible')) {
          _this.setSelect2ErrorState($field, false);
        }
        _this.fieldFeedback($field).text('').removeClass('d-block').css('display', '');
      }
    });
  };

  VehicleManager.prototype.bindEntryModeSwitch = function () {
    const _this = this;
    this.$form.find('#vehicle_customer_entry_mode').on('change', function () {
      const mode = $(this).val();
      _this.$form.find('.customer-mode-section').each(function () {
        const $section = $(this);
        $section.toggleClass('d-none', $section.data('mode') !== mode);
      });
    });
  };

  VehicleManager.prototype.bindModalActions = function () {
    const _this = this;
    $(document).on('click', '[data-bs-target="' + this.options.modalSelector + '"]', function () {
        const $trigger = $(this);
        if ($trigger.attr('id') === 'addVehicleBtn' || $trigger.hasClass('add-vehicle-btn')) {
             _this.resetForm();
        }
    });

    this.$modal.on('hidden.bs.modal', function () {
      _this.resetForm();
    });
  };

  VehicleManager.prototype.bindSaveForm = function () {
    const _this = this;
    this.$form.on('submit', function (event) {
      event.preventDefault();
      _this.resetValidationState();

      if (_this.validator && !_this.$form.valid()) return;

      _this.setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving vehicle...');
      }

      $.ajax({
        url: _this.$form.attr('action'),
        method: 'POST',
        data: _this.$form.serialize()
      })
        .done(function (response) {
          if (typeof window.appNotify === 'function') {
             window.appNotify('success', response.message || 'Vehicle saved successfully.');
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
             window.appNotify('error', xhr.responseJSON?.message || 'Unable to save vehicle.');
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

  VehicleManager.prototype.renderValidationErrors = function (errors) {
    const _this = this;
    Object.entries(errors || {}).forEach(function (entry) {
      const field = entry[0];
      const message = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
      _this.applyFieldError(field, message);
    });

    if (this.validator) {
      this.validator.showErrors(Object.fromEntries(
        Object.entries(errors || {}).map(function (entry) {
          return [entry[0], Array.isArray(entry[1]) ? entry[1][0] : entry[1]];
        })
      ));
    }
  };

  window.VehicleManager = VehicleManager;
})(window.jQuery);
