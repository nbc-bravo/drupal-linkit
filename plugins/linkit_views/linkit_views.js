// $Id$

Drupal.linkit.plugins['views'] = {
  invoke: function(str, basePath) {
    var matches = str.match(/\[path:(.*)\]/i);
    return basePath + matches[1];
  }
};