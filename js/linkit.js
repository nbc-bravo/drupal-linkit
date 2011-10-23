/**
 * @file
 * Linkit dialog functions
 */

// Create the linkit namespace.
Drupal.linkit = Drupal.linkit || {};

(function ($) {

Drupal.behaviors.linkit = {

  attach: function(context, settings) {

    var $searchInput = $('#linkit-modal #edit-search', context);

    // Create a "Better Autocomplete" object, see betterautocomplete.js
    $searchInput.betterAutocomplete('init', settings.linkit.autocompletePath,
      settings.linkit.autocomplete,
      { // Callbacks
      select: function(result) {
        // Only change the link text if it is empty
        if (typeof result.disabled != 'undefined' && result.disabled) {
          return false;
        }
        Drupal.linkit.populateLink(result.title, result.path);
      },
      constructURL: function(path, search) {
        return path + encodeURIComponent(search);
      }
    });

    if (context === window.document) {
      //$searchInput.focus();
    }

    // Open IMCE
    $('#linkit-imce').click(function() {
      Drupal.linkit.openFileBrowser();
      return false;
    });
  }
};

})(jQuery);
