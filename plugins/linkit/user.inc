<?php
/**
 * @file
 * Linkit User Plugin.
 */

/**
 * Define Linkit user plugin class.
 */
class LinkitPluginUser extends LinkitPluginEntity {
  /**
   * The user entity doesn't add any label in their entity keys as they define a
   * "label callback" instead. Therefor we have to tell which field the user
   * entity have as label.
   */
  var $entity_field_label = 'name';
}