/**
 * @file
 * Raw HTML insert plugin for Linkit.
 */
(function ($) {
Drupal.linkit.addInsertPlugin('raw_url',  {
  insert : function(data, settings) {
    return data.path;
  }
});

})(jQuery);