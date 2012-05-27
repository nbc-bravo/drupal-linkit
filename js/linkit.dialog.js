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

      // Run all the behaviors again for this new context.
      Drupal.attachBehaviors($('.linkit-wrapper'), Drupal.settings);
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

/**
 * jQuery dialog buttons is located outside the IFRAME where Linkit dashboard
 * is shown and they cant trigger events in the IFRAME.
 * Our own buttons for inserting a link and cancel is inside that IFRAME and
 * can't destroy the dialog, so we have to bind our buttons to the dialog button.
 */
Drupal.behaviors.linkitDialogButtons = {
  attach: function (context, settings) {
    $('#linkit-modal #linkit-dashboard-form', context).submit(function() {
      var linkitCache = Drupal.linkit.getLinkitCache();
      // Call the insertLink() function.
      Drupal.linkit.source[linkitCache.source].insertLink(Drupal.linkit.dialog.getLink());
      // Close the dialog.
      Drupal.linkit.dialog.close();
      return false;
    });

    $('#linkit-modal #linkit-cancel', context).bind('click', Drupal.linkit.dialog.close);
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
      path: $('#linkit-modal #edit-linkit-path').val(),
      attributes: {}
    };
    $.each(Drupal.linkit.dialog.additionalAttributes(), function(f, name) {
     link.attributes[name] = $('#linkit-modal #edit-linkit-attributes #edit-linkit-' + name).val();
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
  $('#linkit-modal #edit-linkit-attributes .linkit-attribute').each(function() {
    // Remove the 'linkit_' prefix.
    attributes.push($(this).attr('name').substr(7));
  });
  return attributes;
};

})(jQuery);