/**
 * @file
 * Markdown insert plugin for Linkit.
 *
 * Notes: Markdown dont support any attributes exept "title".
 * [An example](http://example.com/ "Title")
 */
(function ($) {

Drupal.linkit.addInsertPlugin('markdown', {
  insert : function(data) {
    var linkitCache = Drupal.linkit.getLinkitCache(),
    pattern = '[!text](!url!title)',
    args = {
      '!text' : linkitCache.link_tmp_title,
      '!url' : data.path,
      '!title' : data.attributes.title ? ' "' + data.attributes.title + '"' : ''
    };

    return Drupal.formatString(pattern, args);
  }
});

})(jQuery);