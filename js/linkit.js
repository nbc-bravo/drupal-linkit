/**
 * @file
 * Linkit dialog functions.
 */

(function ($) {

// Create the Linkit namespaces.
Drupal.linkit = Drupal.linkit || {};
Drupal.linkit.dialogHelper = Drupal.linkit.dialogHelper || {};

/**
 * Create the modal dialog.
 */
Drupal.linkit.createModal = function(profile) {
  // Create the modal dialog element.
  Drupal.linkit.createModalElement()
  // Create jQuery UI Dialog.
  .dialog(Drupal.linkit.modalOptions())
  // Remove the title bar from the modal.
  .siblings(".ui-dialog-titlebar").remove();

  // Make the modal seem "fixed".
  $(window).bind("scroll resize", function() {
    $('#linkit-modal').dialog('option', 'position', ['center', 50]);
  });

  // Get modal content.
  Drupal.linkit.getDashboard(profile);
}

/**
 * Create and append the modal element.
 */
Drupal.linkit.createModalElement = function() {
  // Create a new div and give it an ID of linkit-modal.
  // This is the dashboard container.
  var linkitModal = $('<div id="linkit-modal"></div>');

  // Create a modal div in the <body>.
  $('body').append(linkitModal);

  return linkitModal;
}

/**
 * Default jQuery dialog options used when creating the Linkit modal.
 */
Drupal.linkit.modalOptions = function() {
  return {
    dialogClass: 'linkit-wrapper',
    modal: true,
    draggable: false,
    resizable: false,
    width: 520,
    position: ['center', 50],
    overlay: {
      backgroundColor: '#000000',
      opacity: 0.4
    },
    minHeight: 0,
    zIndex : 210000,
    close: Drupal.linkit.modalClose
  };
}

/**
 * Close the Linkit modal and destroy the BAC session.
 */
Drupal.linkit.modalClose = function () {
  if (Drupal.linkit.$searchInput) {
    Drupal.linkit.$searchInput.betterAutocomplete('destroy');
  }

  $('#linkit-modal').dialog('destroy').remove();
};

/**
 *
 */
Drupal.linkit.getDashboard = function (profile) {
  // Create the AJAX object.
  var ajax_settings = {};
  ajax_settings.event = 'LinkitDashboard';
  ajax_settings.url = Drupal.settings.linkit.dashboardPath + profile;
  ajax_settings.progress = {
    type: 'throbber',
    message : Drupal.t('Loading Linkit dashboard...')
  };

  Drupal.ajax['linkit-modal'] = new Drupal.ajax('linkit-modal', $('#linkit-modal')[0], ajax_settings);

  // @TODO: Jquery 1.5 accept success setting to be an array of functions.
  // But we have to wait for jquery to get updated in Drupal core.
  // In the meantime we have to override it.
  Drupal.ajax['linkit-modal'].options.success = function (response, status) {
    if (typeof response == 'string') {
      response = $.parseJSON(response);
    }

    // Call the ajax success method.
    Drupal.ajax['linkit-modal'].success(response, status);

    // var linkitCache = Drupal.linkit.getLinkitCache();

    // Run the afterInit function.
    //Drupal.linkit.getDialogHelper(linkitCache.helper).afterInit();

    // Set focus in the search field.
    $('#linkit-modal .linkit-search-element').focus();
  };

  // Trigger the ajax event.
  $('#linkit-modal').trigger('LinkitDashboard');
}

/**
 * Register new dialog helper.
 */
Drupal.linkit.registerDialogHelper = function(name, helper) {
  Drupal.linkit.dialogHelper[name] = helper;
}

/**
 * Register new dialog helper.
 */
Drupal.linkit.getDialogHelper = function(name) {
  return Drupal.linkit.dialogHelper[name];
}

})(jQuery);
