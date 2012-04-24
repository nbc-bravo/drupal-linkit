/**
 * @File
 */

Drupal.linkit.addPlugin('html_link', {
  insert : function(url, settings) {
    return settings.pattern.replace('%url', url);
  }
});
