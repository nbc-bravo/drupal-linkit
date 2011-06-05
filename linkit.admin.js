(function ($) {

/**
 * Update fields dropped into new status groups.
 *
 * This behavior is dependent on the tableDrag behavior, since it uses the
 * objects initialized in that behavior to update the row.
 */
Drupal.behaviors.linkitAttributesDrag = {
  attach: function (context, settings) {
    // tableDrag is required.
    if (typeof Drupal.tableDrag == 'undefined' || typeof Drupal.tableDrag['linkit-attributes'] == 'undefined') {
      return;
    }

    var table = $('table#linkit-attributes');
    var tableDrag = Drupal.tableDrag['linkit-attributes']; // Get the attributes tableDrag object.

    // Fix colspans (as Drupal core mess these things up).
    var fixCols = function (fieldsetWrapper, table) {
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
    $('.tabledrag-toggle-weight', table.parentsUntil('fieldset')).click(function() {
      fixCols(table.parentsUntil('fieldset'), table);
    });
    // Call this at load.
    fixCols(table.parentsUntil('fieldset'), table);

    // Add a handler for when a row is swapped, update empty status group.
    tableDrag.row.prototype.onSwap = function (swappedRow) {
      checkEmptyRegions(table, this);
    };

    // A custom message.
    Drupal.theme.tableDragChangedWarning = function () {
      return '<div class="messages warning">' + Drupal.theme('tableDragChangedMarker') + ' ' + Drupal.t('The changes to these attributes will not be saved until the <em>Save configuration</em> button is clicked.') + '</div>';
    };

    // Add a handler so when a row is dropped, update fields dropped into new status groups.
    tableDrag.onDrop = function () {
      dragObject = this;
      var groupRow = $(dragObject.rowObject.element).prevAll('tr.linkit-status-group-header-message').get(0);
      var groupnName = groupRow.className.replace(/([^ ]+[ ]+)*linkit-status-([^ ]+)-group([ ]+[^ ]+)*/, '$2');
      var groupField = $('select.attribute-status-select', dragObject.rowObject.element);

      var weightField = $('select.attribute-weight', dragObject.rowObject.element);
      var oldGroupName = weightField[0].className.replace(/([^ ]+[ ]+)*attribute-weight-([^ ]+)([ ]+[^ ]+)*/, '$2');

      if (!groupField.is('.attribute-status-' + groupnName)) {
        groupField.removeClass('attribute-status-' + oldGroupName).addClass('attribute-status-' + groupnName);
        weightField.removeClass('attribute-weight-' + oldGroupName).addClass('attribute-weight-' + groupnName);
        groupField.val(groupnName);
      }
    };

    var checkEmptyRegions = function (table, rowObject) {
      $('tr.linkit-status-group-header-message', table).each(function () {
        // If the dragged row is in this status group, but above the message row, swap it down one space.
        if ($(this).prev('tr').get(0) == rowObject.element) {
          // Prevent a recursion problem when using the keyboard to move rows up.
          if ((rowObject.method != 'keyboard' || rowObject.direction == 'down')) {
            rowObject.swap('after', this);
          }
        }
        // This status group has become empty.
        if ($(this).next('tr').is(':not(.draggable)') || $(this).next('tr').size() == 0) {
          $(this).removeClass('linkit-status-group-populated').addClass('linkit-status-group-empty');
        }
        // This status group has become populated.
        else if ($(this).is('.linkit-status-group-empty')) {
          $(this).removeClass('linkit-status-group-empty').addClass('linkit-status-group-populated');
        }
      });
    };
  }
}

})(jQuery);