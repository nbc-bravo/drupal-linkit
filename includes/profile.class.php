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
   * @var LinkitProfile object
   */
  protected $profile;

  /**
   * All active attributes for this profile.
   *
   * @var array
   */
  protected $attributes = array();

  /**
   * All enabled search pluings for this profile.
   *
   * @var array
   */
  protected $enabled_serach_pluings = array();


  /**
   *
   */
  public function init($profile) {
    $this->profile = $profile;
    $this->setAttributes();
    $this->setEnabledSerachPluings();
  }

  /**
   * Set all active attribures.
   */
  public function setAttributes() {
    foreach ($this->profile->data['attribute_plugins'] as $attribute_name => $attribute) {
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
    uasort($this->profile->data['search_plugins'], 'linkit_sort_plugins_by_weight');

    foreach ($this->profile->data['search_plugins'] as $plugin_name => $plugin) {
      if ($plugin['enabled']) {
        // Load plugin definition.
        $plugin_definition = linkit_search_plugin_load($plugin_name);

        // Get a Linkit serach plugin object.
        $search_plugin = LinkitSearchPlugin::factory($plugin_definition, $this->profile);

        $this->enabled_serach_pluings[$plugin_name] = $search_plugin;
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

  /**
   * @return
   *   An array with all active attributes for this profile.
   */
  public function getEnabledSerachPluings() {
    return $this->enabled_serach_pluings;
  }

}