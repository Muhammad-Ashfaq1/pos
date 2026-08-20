/**
 * Shared DataTables toolbar for pos-listing pages:
 * Title | Search | Filter | Add  in one row.
 *
 * Title / filter / add live in the Blade header (always visible).
 * This only relocates the DataTables search input into that row.
 */
(function ($) {
  'use strict';

  if (window.PosListingToolbar) {
    return;
  }

  window.PosListingToolbar = {
    /**
     * @param {DataTable.Api} table
     * @param {string} actionsSelector  unused (kept for call-site compatibility)
     */
    align: function (table) {
      if (!table || typeof table.table !== 'function') {
        return;
      }

      const $container = $(table.table().container());
      const $panel = $container.closest('.pos-listing-panel');
      if (!$panel.length) {
        return;
      }

      const $slot = $panel.find('.pos-listing-search-slot').first();
      const $search = $container.find('.dt-search').first();

      if ($slot.length && $search.length && !$slot.find('.dt-search').length) {
        $slot.removeAttr('aria-hidden').empty().append($search);
      }

      // Hide the now-empty DataTables top chrome row
      $container.find('.dt-layout-row').first().addClass('is-pos-listing-top-moved');
    }
  };
})(jQuery);
