/**
 * @file
 * Linkit dialog functions.
 */

// Create the Linkit namespaces.
Drupal.linkit = Drupal.linkit || {};
Drupal.linkit.source = Drupal.linkit.source || {};
Drupal.linkit.insertPlugins = Drupal.linkit.insertPlugins || {};
Drupal.linkitCache = Drupal.linkitCache || {};

(function ($) {

Drupal.behaviors.linkit = {
  attach: function(context, settings) {
    if ($('#linkit-modal #edit-linkit-search', context).length == 0) {
      return;
    }

    Drupal.linkit.$searchInput = $('#linkit-modal #edit-linkit-search', context);

    // Create a "Better Autocomplete" object, see betterautocomplete.js
    Drupal.linkit.$searchInput.betterAutocomplete('init',
      settings.linkit.autocompletePath,
      settings.linkit.autocomplete,
      { // Callbacks
      select: function(result) {
        // Only change the link text if it is empty
        if (typeof result.disabled != 'undefined' && result.disabled) {
          return false;
        }

        Drupal.linkit.dialog.populateFields({
          path: result.path
        });

        // Store the result title (Used when no selection is made bythe user).
        Drupal.linkitCacheAdd('link_tmp_title', result.title);

       $('#linkit-modal #edit-linkit-path').focus();
      },
      constructURL: function(path, search) {
        return path + encodeURIComponent(search);
      },
      insertSuggestionList: function($results, $input) {
        $results.width($input.outerWidth() - 2) // Subtract border width.
          .css({
            position: 'absolute',
            left: $input.offset().left,
            top: $input.offset().top + $input.outerHeight(),
            zIndex: 2000,
            maxHeight: '330px',
            // Visually indicate that results are in the topmost layer
            boxShadow: '0 0 15px rgba(0, 0, 0, 0.5)'
          })
          .hide()
          .insertAfter($('#linkit-modal', context).parent());
        }
    });

    $('#linkit-modal .form-text.required', context).bind({
      //keyup: Drupal.linkit.dialog.requiredFieldsValidation,
      //change: Drupal.linkit.dialog.requiredFieldsValidation
    });

    //Drupal.linkit.dialog.requiredFieldsValidation();
  }
};

/**
 * @TODO: Document this.
 */
Drupal.linkitCacheAdd = function (name, value) {
  Drupal.linkitCache[name] = value;
};

/**
 * @TODO: Document this.
 */
Drupal.linkit.getLinkitCache = function () {
  return Drupal.linkitCache;
};

/**
 * @TODO: Document this.
 */
Drupal.linkit.addInsertPlugin = function(name, plugin) {
  Drupal.linkit.insertPlugins[name] = plugin;
}

/**
 * @TODO: Document this.
 */
Drupal.linkit.getInsertPlugin = function(name) {
  return Drupal.linkit.insertPlugins[name];
}
})(jQuery);