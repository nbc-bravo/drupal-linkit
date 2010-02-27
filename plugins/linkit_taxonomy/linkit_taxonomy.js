// $Id$

Drupal.linkit.plugins['taxonomy'] = {
  invoke: function(str) {
    var matches = str.match(/\[tid:(\d+)\]/i);
    return 'internal:taxonomy/term/'+matches[1];
  }
};
