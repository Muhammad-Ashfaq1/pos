'use strict';

(function ($, api) {
  if (!$ || !api) {
    return;
  }

  const $list = $('#cp-vehicles-list');

  const render = function (vehicles) {
    if (!vehicles || !vehicles.length) {
      $list.html(api.emptyHtml('tabler-car', 'No vehicles on file yet.'));
      return;
    }

    $list.html(vehicles.map(function (vehicle) {
      const label = vehicle.label || 'Vehicle';
      let meta = 'No plate on file';
      if (vehicle.plate_number) {
        meta = 'Plate ' + vehicle.plate_number;
      } else if (vehicle.registration_number) {
        meta = 'Reg ' + vehicle.registration_number;
      }
      if (vehicle.color) {
        meta += ' · ' + vehicle.color;
      }
      if (vehicle.odometer != null && vehicle.odometer !== '') {
        meta += ' · ' + Number(vehicle.odometer).toLocaleString() + ' mi';
      }

      return '<div class="cp-list-item">' +
        '<div class="d-flex align-items-start gap-3 min-w-0">' +
        '<span class="cp-vehicle-icon"><i class="ti tabler-car"></i></span>' +
        '<div class="min-w-0">' +
        '<div class="d-flex align-items-center gap-2 flex-wrap">' +
        '<span class="fw-semibold">' + api.escapeHtml(label) + '</span>' +
        (vehicle.is_default ? '<span class="badge bg-label-primary">Default</span>' : '') +
        '</div>' +
        '<div class="small text-muted mt-1">' + api.escapeHtml(meta) + '</div>' +
        '</div></div></div>';
    }).join(''));
  };

  const load = function () {
    $list.html(api.loadingHtml('Loading vehicles...'));

    api.get('/vehicles')
      .then(function (payload) {
        render(payload.data || []);
      })
      .catch(function (error) {
        const message = api.showError(error, 'Unable to load vehicles.');
        $list.html(api.emptyHtml('tabler-alert-circle', message));
      });
  };

  $(load);
})(window.jQuery, window.CustomerApi);
