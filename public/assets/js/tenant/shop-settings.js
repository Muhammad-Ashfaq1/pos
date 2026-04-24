(function ($) {
  'use strict';

  const initSelect2 = function () {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $('.select2').each(function () {
      const $this = $(this);

      if ($this.data('select2')) {
        return;
      }

      if (!$this.parent().hasClass('position-relative')) {
        $this.wrap('<div class="position-relative"></div>');
      }

      $this.select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') || '',
        width: '100%',
      });
    });
  };

  const syncBusinessHourRow = function ($row) {
    const closed = $row.find('.business-hours-closed-toggle').is(':checked');

    $row.find('[data-business-hours-time]').each(function () {
      $(this).prop('disabled', closed).toggleClass('bg-label-secondary', closed);
    });
  };

  const bindBusinessHours = function () {
    $('[data-business-hours-row]').each(function () {
      syncBusinessHourRow($(this));
    });

    $(document).on('change', '.business-hours-closed-toggle', function () {
      syncBusinessHourRow($(this).closest('[data-business-hours-row]'));
    });
  };

  $(function () {
    initSelect2();
    bindBusinessHours();
  });
})(jQuery);
