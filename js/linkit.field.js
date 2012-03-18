/**
 * @file
 * Linkit field ui functions
 */

// Create the linkit namespaces.
Drupal.linkit = Drupal.linkit || {};
Drupal.linkit.field = Drupal.linkit.field || {};

(function ($) {

Drupal.behaviors.linkit_field = {
  attach: function(context, settings) {
    // If there is no fields, just stop here.
    if (settings.linkit.fields == null) {
      return false;
    }
    $.each(settings.linkit.fields, function(field) {

      // Create a "Better Autocomplete" object, see betterautocomplete.js
      $('[name="' + field + '"]', context).betterAutocomplete('init',
        settings.linkit.autocompletePath,
        settings.linkit.autocomplete,
        { // Callbacks
        select: function(result) {
          // Only change the link text if it is empty
          if (typeof result.disabled != 'undefined' && result.disabled) {
            return false;
          }
          $('[name="' + field + '"]').val(function(index, value) {
            return result.path;
          });
        },
        constructURL: function(path, search) {
          return path + encodeURIComponent(search);
        },
        insertSuggestionList: function($results, $input) {
          var position = $input.position();
          $results.width($input.innerWidth())
            .css({
              position: 'absolute',
              left: parseInt(position.left, 10),
              top: position.top + $input.outerHeight(true),

              zIndex: 2000,
              maxHeight: '330px',
              // Visually indicate that results are in the topmost layer
             boxShadow: '0 0 15px rgba(0, 0, 0, 0.5)'
            })
            .hide()
            .insertAfter($input);
          }
      });
    });
  }
};

})(jQuery);
