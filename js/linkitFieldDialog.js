/**
 * @file
 * Linkit field dialog helper.
 */

Drupal.linkit.editorDialog.field = {};

(function ($) {

Drupal.linkit.editorDialog.field = {
  /**
   * Insert the link into the field.
   *
   * @param {Object} link
   *   The link object.
   */
  insertLink : function(data) {
    var linkitCache = Drupal.linkit.getLinkitCache();
    var field = $('#' + linkitCache.editorField);
    var field_settings = Drupal.settings.linkit.fields[linkitCache.editorField];

    // Call the insert plugin.
    var link = Drupal.linkit.insertPlugins[field_settings.insert_plugin].insert(data);

    // Insert the link.
    field.val(link);
  }
};

})(jQuery);