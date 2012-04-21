
Drupal.linkit.addPlugin('linkit_insert_pattern', {
  insert : function(url, settings) {
    return settings.pattern.replace('%url', url);
  }
});
