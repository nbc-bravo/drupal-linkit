<?php
/**
 * @file
 * Linkit Plugin interface.
 *
 * Provides an interface and classes to implement Linkit plugins.
 */

/**
 * Linkit plugin interface.
 */
interface LinkitPluginInterface {
  public function autocomplete_callback();
  //public function path_info_callback();
}

/**
 *
 * LinkitPlugin.
 */
abstract class LinkitPlugin implements LinkitPluginInterface {

  function init($plugin, $profile) {
    $this->plugin = $plugin;
    $this->profile = $profile;
  }

  function setSearchString($serachString) {
    $this->searchString = $serachString;
  }

  /**
   * Returns a string to use as the search result label.
   */
  function buildLabel($label) {
    return check_plain($label);
  }

  /**
   * Returns an URL based in the path and the options.
   */
  function buildPath($path, $options = array()) {
    return url($path, $options);
  }

  /**
   * If there is a "result_description", run it thro token_replace.
   */
  function buildDescription($item) {
    if (isset($this->profile->data[$this->plugin['name']]['result_description'])) {
      return token_replace(check_plain($this->profile->data[$this->plugin['name']]['result_description']), array(
       $this->plugin_name => $item,
      ));
    }
  }

  /**
   * Returns a string to use as the search result label.
   */
  function buildGroup($groupname) {
    return check_plain($groupname);
  }

  /**
   * Returns a string with CSS classes that will be added to the search result
   * row for this item.
   *
   * @return
   *   A string with CSS classes
   */
  function buildRowClass() {}

  /**
   * Generate a settings form for this handler.
   * Uses the standard Drupal FAPI.
   *
   * @return
   *   An array containing any custom form elements to be displayed in the
   *   profile editing form
   */
   function buildSettingsForm() {}
}