/**
 * @file
 * Linkit dialog functions.
 */

// Create the Linkit namespaces.
Drupal.linkit = Drupal.linkit || {};

(function ($) {

Drupal.behaviors.linkit = {
  attach: function(context, settings) {
    console.log(settings);
    // If there is no fields, just stop here.
    if (settings.linkit.fields == null) {
      return false;
    }
    $.each(settings.linkit.fields, function(field) {
      $('#' + field, context).once('linkit_field', function() {
        $('.linkit-field-' + field).click(function() {
          Drupal.linkit.dialog.buildDialog('/linkit/dashboard');
          return false;
        });
      });
    });
  }
};

})(jQuery);