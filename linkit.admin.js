(function ($) {

/**
 * Update fields dropped into new status groups.
 *
 * This behavior is dependent on the tableDrag behavior, since it uses the
 * objects initialized in that behavior to update the row.
 */
Drupal.behaviors.linkitDrag = {
  attach: function (context, settings) {
    $('table.linkit-drag-table').each(function() {
      var table = $(this);
      var tableDrag = Drupal.tableDrag[table.attr('id')]; // Get the attributes tableDrag object.

      // tableDrag is required.
      if (typeof Drupal.tableDrag == 'undefined' || typeof Drupal.tableDrag[table.attr('id')] == 'undefined') {
        return;
      }

      // Fix colspans (as Drupal core mess these things up).
      var fixCols = function () {
        var colsWithColspan = $('th[colspan], td[colspan], .has-colspan', table).addClass('has-colspan');
        var cols = $('tr th:visible', table);
        var index = cols.index($(cols).get(-1)) + 1;
        colsWithColspan.each(function() {
          if(index == 1) {
            $(this).removeAttr('colspan');
          }
          else {
             $(this).attr('colspan', index);
          }
        });
      }

      // Triggers when the "show row wights" link is clicked.
      $('.tabledrag-toggle-weight').click(function() {
        fixCols();
      });

      // Call this at load.
      fixCols();

    });
  }
};

})(jQuery);
