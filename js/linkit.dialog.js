/**
 * @file
 * Linkit dialog functions
 */

// Create the linkit dialog namespace.
Drupal.linkit.dialog = Drupal.linkit.dialog || {};

(function($) {

/**
 * Dialog default options.
 */
Drupal.linkit.dialog.dialogOptions = function() {
  return {
    buttons: Drupal.linkit.dialog.dialogButtons(),
    dialogClass: 'linkit-wrapper',
    modal: true,
    draggable: false,
    resizable: false,
    minWidth: 800,
    width: 800,
    height: 550,
    position: 'center',
    overlay: {
      backgroundColor: '#000000',
      opacity: 0.4
    }
  }
}

/**
 * Define the dialog buttons.
 */
Drupal.linkit.dialog.dialogButtons = function () {

  var close = Drupal.t('Close');
  var buttons = {};

  buttons[close] = function () {
    $(this).dialog("destroy");
    $(this).remove();
  };

  return buttons;
}

/**
 * Populate the title and path fields when a linkable object is selected
 *
 * @param title
 *   The title of the link, only populated if title is empty
 * @param path
 *   The target path of the link
 * @param {Boolean} silent
 *   Only populate fields, do not focus and select the title field
 *
 * other fields may be populated by the editor.
 */
Drupal.linkit.dialog.populateLink = function(text, path) {
  $('#linkit').data('text', text);
  $('#linkit #edit-path').val(path);
  $('#linkit #edit-search').blur();
};

/**
 * Open the IMCE file browser
 */
Drupal.linkit.dialog.openFileBrowser = function () {
  window.open(decodeURIComponent(Drupal.settings.linkit.IMCEurl), '', 'width=760,height=560,resizable=1');
};

/**
 * When a file is inserted through IMCE, this function is called
 * See IMCE api for details
 *
 * @param file
 *   The file object that was selected inside IMCE
 * @param win
 *   The IMCE window object
 */
Drupal.linkit.dialog.IMCECallback = function(file, win) {
  Drupal.linkit.dialog.populateLink(file.name,
      win.imce.decode(Drupal.settings.basePath +
                      Drupal.settings.linkit.publicFilesDirectory +
                      '/' + file.relpath));
  win.close();
};

/**
 * Show a message if there is no selection.
 */
Drupal.linkit.dialog.noselection = function() {
  var info_text = Drupal.t('<em class="notice">Notice: No selection was found, your link text will appear as the item title you are linking to.</em>');
  $('#linkit-dashboard-form').prepend(info_text);
};

/**
 * Return the Iframe that we use in the dialog.
 */
Drupal.linkit.dialog.createDialog = function(src) {
  var content = $('<div>').attr('id', 'linkit-modal').load(src, function() {
    // Run all the behaviors again for this new context.
    Drupal.attachBehaviors(this, Drupal.settings);
    // Initialize Linkit editor js.
    LinkitDialog.init();
  });
  return content;
}

/**
 * Build the dialog
 *
 * @param url
 *   The url to call in the iframe.
 */
Drupal.linkit.dialog.buildDialog = function (url) {
   // Get the options for the dialog.
   var dialogOptions = Drupal.linkit.dialog.dialogOptions();

   // Build the dialog element.
   var linkitDialog = Drupal.linkit.dialog.createDialog(url);

   var dia = linkitDialog.dialog(dialogOptions);


   // Remove the title bar from the dialog.
   linkitDialog.parents(".ui-dialog").find(".ui-dialog-titlebar").remove();
}

})(jQuery);