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
      $('#' + field, context).once('linkit_field', function() {
        $('.linkit-field-' + field).click(function() {
          // We dont have an editor here, but we need to give this instance a
          // name.
          Drupal.linkit.setEditorName('field');
          // Set the name of the editor field, this is just for CKeditor.
          Drupal.linkit.setEditorField(field);

          Drupal.linkit.dialog.buildDialog(settings.linkit.url.field);
          return false;
        });
      });
    });
  }
};

})(jQuery);
