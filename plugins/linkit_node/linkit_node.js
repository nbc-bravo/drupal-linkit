// $Id$

Drupal.linkit.plugins['node'] = {
  invoke: function(str) {
    var matches = str.match(/\[nid:(\d+)\]/i);
    return 'internal:node/'+matches[1];
  }
};
