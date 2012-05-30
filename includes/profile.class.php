<?php
/**
 * @file
 * Linkit Profile class.
 */

/**
 * Linkit Profile class implementation.
 */
class LinkitProfile {

  /**
   * All active attributes for this profile.
   *
   * @var array
   */
  protected $attributes = array();


  /**
   *
   */
  public function init($data) {
    $this->setAttributes($data);
  }

  /**
   * Set all active attribures.
   */
  public function setAttributes($data) {
    foreach ($data['attribute_plugins'] as $attribute_name => $attribute) {
      if ($attribute['enabled']) {
        // Load the attribute plugin.
        $attribute_plugin = linkit_attribute_plugin_load($attribute_name);

        // Call the callback to get the FAPI element.
        if (isset($attribute_plugin['callback']) && function_exists($attribute_plugin['callback'])) {
          $attribute_html = $attribute_plugin['callback']($attribute_plugin, $attribute);
          $this->attributes[$attribute_name] = $attribute_html;
        }
      }
    }
  }

  /**
   * @return
   *   An array with all active attributes for this profile.
   */
  public function getAttributes() {
    return $this->attributes;
  }

}