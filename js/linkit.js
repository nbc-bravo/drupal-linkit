/**
 * @file
 * Linkit dialog functions.
 */

// Create the Linkit namespaces.
Drupal.linkit = Drupal.linkit || {};

(function ($) {

Drupal.behaviors.linkit = {
  attach: function(context, settings) {
    // If there is no fields, just stop here.
    if (settings.linkit.fields == null) {
      return false;
    }
    $.each(settings.linkit.fields, function(field_name, field) {
      $('#' + field_name, context).once('linkit_field', function() {
        $('.linkit-field-' + field_name).click(function() {
          Drupal.linkit.dialog.buildDialog('/linkit/dashboard/' + field.profile);
          return false;
        });
      });
    });
  }
};

})(jQuery);