/**
 * @file
 * Linkit dialog functions.
 */

// Create the Linkit namespaces.
Drupal.linkit = Drupal.linkit || {};
Drupal.linkitCache = Drupal.linkitCache || {};
Drupal.linkit.source = Drupal.linkit.source || {};
Drupal.linkit.insertPlugins = Drupal.linkit.insertPlugins || {};

(function ($) {

Drupal.behaviors.linkit = {
  attach: function(context, settings) {}
};

/**
 * @TODO: Document this.
 */
Drupal.linkitCacheAdd = function (name, value) {
  Drupal.linkitCache[name] = value;
};

/**
 * @TODO: Document this.
 */
Drupal.linkit.getLinkitCache = function () {
  return Drupal.linkitCache;
};

/**
 * @TODO: Document this.
 */
Drupal.linkit.addInsertPlugin = function(name, plugin) {
  Drupal.linkit.insertPlugins[name] = plugin;
}

/**
 * @TODO: Document this.
 */
Drupal.linkit.getInsertPlugin = function(name) {
  return Drupal.linkit.insertPlugins[name];
}
})(jQuery);