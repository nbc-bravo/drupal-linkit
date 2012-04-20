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

    linkitCache.editorField.val(function( index, value ) {
      return value + ' ' + data.path;
    });
  }
};

})(jQuery);