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
  // Remove the title bar from the dialog.
  .siblings(".ui-dialog-titlebar").remove();

  // Create the AJAX object.
  var ajax_settings = {};
  ajax_settings.event = 'alinkitevent';
  ajax_settings.url = src;
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

    var linkitCache = Drupal.linkit.getLinkitCache();

    // Run the afterInit function.
    Drupal.linkit.getDialogHelper(linkitCache.helper).afterInit();

    // Set focus in the search field.
    $('#linkit-modal .linkit-search-element').focus();

    $('#linkit-modal').dialog('option', 'position', ['center', 50]);
  };

  // Call the ajax.eventResponse method to trigger the ajax to run.
  Drupal.ajax['linkit-modal'].eventResponse(Drupal.ajax['linkit-modal'].element, null);


  // Move the dialog when the main window moves.
  $(window).bind("scroll resize", function() {
    $('#linkit-modal').dialog('option', 'position', ['center', 50]);
  });
};

/**
 * Return the Iframe that we use in the dialog.
 */
Drupal.linkit.dialog.createDialog = function(src) {
  var $linkitModal = $('<div />').attr('id', 'linkit-modal'),
      linkitCache = Drupal.linkit.getLinkitCache();

  // Replace the URL placeholder with the profile to use fot the BAC calls.
  Drupal.settings.linkit.autocompletePathParsed = Drupal.settings.linkit.autocompletePath.replace('___profile___', linkitCache.profile);

  // Create a dialog dig in the <body>.
  $('body').append($linkitModal);

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
    position: ['center', 50],
    overlay: {
      backgroundColor: '#000000',
      opacity: 0.4
    },
    minHeight: 0,
    zIndex : 210000,
    close: Drupal.linkit.dialog.close
  };
};

/**
 * Close the Linkit dialog.
 */
Drupal.linkit.dialog.close = function () {
  if (Drupal.linkit.$searchInput) {
    Drupal.linkit.$searchInput.betterAutocomplete('destroy');
  }
  $('#linkit-modal').dialog('destroy').remove();

  // Unset the linkit cache.
  Drupal.linkitCache = {};
};

/**
 * We have to bind our buttons to the dialog.
 */
Drupal.behaviors.linkitDialogButtons = {
  attach: function (context, settings) {
    $('.linkit-insert', context).click(function() {
      var linkitCache = Drupal.linkit.getLinkitCache();
      // Call the insertLink() function.
      Drupal.linkit.getDialogHelper(linkitCache.helper).insertLink(Drupal.linkit.dialog.getLink());
      // Close the dialog.
      Drupal.linkit.dialog.close();
      return false;
    });

    $('#linkit-cancel', context).bind('click', Drupal.linkit.dialog.close);
  }
};

/**
 * Retrieve a link object by extracting values from the form.
 *
 * @return
 *   The link object.
 *
 * @see Drupal.linkit.dialog.populateFields.
 */
  Drupal.linkit.dialog.getLink = function() {
    var link = {
      path: $('#linkit-modal .linkit-path-element').val(),
      attributes: {}
    };
    $.each(Drupal.linkit.dialog.additionalAttributes(), function(f, name) {
     link.attributes[name] = $('#linkit-modal .linkit-attributes .linkit-attribute-' + name).val();
    });
  return link;
};

/**
 * Retrieve a list of the currently available additional attributes in the
 * dashboard. The attribute "href" is excluded.
 *
 * @return
 *   An array with the names of the attributes.
 */
Drupal.linkit.dialog.additionalAttributes = function() {
  var attributes = [];
  $('#linkit-modal .linkit-attributes .linkit-attribute').each(function() {
    // Remove the 'linkit_' prefix.
    attributes.push($(this).attr('name').substr(7));
  });
  return attributes;
};

/**
 * Populate fields on the dashboard.
 *
 * @param link
 *   An object with the following properties (all are optional):
 *   - path: The anchor's href.
 *   - text: The text that should be linked. Has no effect if already set.
 *   - attributes: An object with additional attributes for the anchor element.
 */
Drupal.linkit.dialog.populateFields = function(link) {
  link = link || {};
  link.attributes = link.attributes || {};
  $('#linkit-modal .linkit-path-element').val(link.path);
  $.each(link.attributes, function(name, value) {
    $('#linkit-modal .linkit-attributes .linkit-attribute-' + name).val(value);
  });
  Drupal.linkit.dialog.requiredFieldsValidation();
};

/**
 * Check for mandatory text fields in the form and disable for submissions
 * if any of the fields are empty.
 */
Drupal.linkit.dialog.requiredFieldsValidation = function() {
  var allowed = true;
  $('#linkit-modal .required').each(function() {
    if (!$(this).val()) {
      allowed = false;
      return false;
    }
  });
  if (allowed) {
    $('#linkit-modal .linkit-insert')
      .removeAttr('disabled')
      .removeClass('form-button-disabled');
  }
  else {
    $('#linkit-modal .linkit-insert')
      .attr('disabled', 'disabled')
      .addClass('form-button-disabled');
  }
};

})(jQuery);