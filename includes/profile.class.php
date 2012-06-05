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
   * The active profile.
   *
   * @var array Profile data (settings).
   */
  public $data;

  /**
   * All active attributes for this profile.
   *
   * @var array
   */
  protected $attributes;

  /**
   * All enabled search pluings for this profile.
   *
   * @var array
   */
  protected $enabled_serach_pluings;

  /**
   * Set all active attribures.
   */
  public function setAttributes() {
    foreach ($this->data['attribute_plugins'] as $attribute_name => $attribute) {
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
   * Set all enabled search pluings.
   */
  public function setEnabledSerachPluings() {
    // Sort plugins by weight.
    uasort($this->data['search_plugins'], 'linkit_sort_plugins_by_weight');

    foreach ($this->data['search_plugins'] as $plugin_name => $plugin) {
      if ($plugin['enabled']) {
        // Load plugin definition.
        $plugin_definition = linkit_search_plugin_load($plugin_name);

        // Get a Linkit serach plugin object.
        $search_plugin = LinkitSearchPlugin::factory($plugin_definition, $this);

        $this->enabled_serach_pluings[$plugin_name] = $search_plugin;
      }
    }
  }

  /**
   * @return
   *   An array with all active attributes for this profile.
   */
  public function getAttributes() {
    if (!isset($this->attributes)) {
      $this->setAttributes();
    }
    return $this->attributes;
  }

  /**
   * @return
   *   An array with all active attributes for this profile.
   */
  public function getEnabledSerachPluings() {
     if (!isset($this->enabled_serach_pluings)) {
      $this->setEnabledSerachPluings();
    }
    return $this->enabled_serach_pluings;
  }

}