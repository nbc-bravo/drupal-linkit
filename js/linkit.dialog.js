/**
 * @file
 * Linkit dialog functions.
 */

// Create the linkit dialog namespace.
Drupal.linkit.dialog = Drupal.linkit.dialog || {};

(function($) {

/**
 * Build the dialog.
 */
Drupal.linkit.dialog.buildDialog = function (src) {
  // Build the dialog element.
  Drupal.linkit.dialog.createDialog(src)
  // Create jQuery UI Dialog.
  .dialog(Drupal.linkit.dialog.dialogOptions())
};

/**
 * Return the Iframe that we use in the dialog.
 */
Drupal.linkit.dialog.createDialog = function(src) {
  var $linkitModal = $('<div />').attr('id', 'linkit-modal');

  // Create a dialog dig in the <body>.
  $('body').append($linkitModal);
  // @TODO: Fix error handling for the ajax request.
  $.ajax({
    url : src,
    beforeSend : function() {
      // Add new throbber
      var throbber = $('<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>');
      $linkitModal.append(throbber);
    },
    success : function(data) {
      // Insert the respons.
      $linkitModal.append(data);
      // Delete exsisting throbbers.
      $('.ajax-progress-throbber', $linkitModal).remove();

      // Set focus in the search field.
      $('.linkit-wrapper #edit-linkit-search').focus();

    }
  });

  return $linkitModal;
};

/**
 * Dialog default options.
 */
Drupal.linkit.dialog.dialogOptions = function() {
  return {
    dialogClass: 'linkit-wrapper',
    modal: true,
    draggable: false,
    resizable: false,
    width: 520,
    position: 'center',
    overlay: {
      backgroundColor: '#000000',
      opacity: 0.4
    },
    close: Drupal.linkit.dialog.close
  };
};

/**
 * Close the Linkit dialog.
 * Return false so the default browser behavior will not submit the form in the
 * dialog.
 */
Drupal.linkit.dialog.close = function () {
  if (Drupal.linkit.$searchInput) {
    Drupal.linkit.$searchInput.betterAutocomplete('destroy');
  }
  $('#linkit-modal').dialog('destroy').remove();

  // Unset the linkit cache.
  Drupal.linkitCache = {};
  return false;
};

})(jQuery);