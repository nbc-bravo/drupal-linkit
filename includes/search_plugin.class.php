<?php
/**
 * @file
 * Linkit Search plugin interface.
 *
 * Provides an interface and classes to implement Linkit search plugins.
 */

/**
 * Defines a common interface for a Linkit search plugin.
 */
interface LinkitSearchPluginInterface {

  /**
   * Executes the search plugin query.
   *
   * @param $serach_string
   *   A string that contains the text to search for.
   *
   * @return
   *   An array with search plugin results.
   */
  public function execute($serach_string);
}

/**
 * Base class for Linkit search pluings.
 */
abstract class LinkitSearchPlugin implements LinkitSearchPluginInterface {

  /**
   * Initialize this search plugin with the search plugin and the profile.
   *
   * @param array $plugin
   *   The plugin array.
   * @param object LinkitProfile $profile
   *   The Linkit profile object.
   */
  public function __construct($plugin, LinkitProfile $profile) {
    $this->plugin = $plugin;
    $this->profile = $profile;
  }

  /**
   * Search plugin factory method.
   *
   * @param $plugin
   *   A search pluing object.
   *
   * @param LinkitProfile $profile
   *   A LinkitProfile object.
   *
   * @return
   *   An instance of the search plugin class or an instance of the
   *   LinkitSearchPluginBroken class.
   */
  public static function factory($plugin, LinkitProfile $profile = NULL) {
    ctools_include('plugins');

    // Make sure there is a handler class set in the plugin defintion.
    // @TODO: Test this exception.
    if (!isset($plugin['handler']['class'])) {
      throw new Exception('Handler class not found');
    }

    if (class_exists($plugin['handler']['class'])) {
      return new $plugin['handler']['class']($plugin, $profile);
    }
    else {
      // The plugin handler class is defined but it cannot be found, so lets
      // instantiate the LinkitSearchPluginBroken instead.
      return new LinkitSearchPluginBroken($plugin, $profile);
    }
  }

  /**
   * Return a string representing this handler's name in the UI.
   */
  public function ui_title() {
    if (!isset($this->plugin['ui_title'])) {
      return check_plain($this->plugin['module'] . ':' . $this->plugin['name']);
    }
    return check_plain($this->plugin['ui_title']);
  }

  /**
   * Return a string representing this handler's description in the UI.
   */
  public function ui_description() {
    if (isset($this->plugin['ui_description'])) {
      return check_plain($this->plugin['ui_description']);
    }
  }

  /**
   * Build the label that will be used in the search result for each row.
   */
  protected function buildLabel($label) {
    return check_plain($label);
  }

  /**
   * Build an URL based in the path and the options.
   */
  protected function buildPath($path, $options = array()) {
    return url($path, $options);
  }

  /**
   * Build the search row description.
   *
   * If there is a "result_description", run it thro token_replace.
   *
   * @param object $data
   *   An object that will be used in the token_place function
   *
   * @see token_replace()
   */
  protected function buildDescription($data) {
    if (isset($this->profile->data[$this->plugin['name']]['result_description'])) {
      return token_replace(check_plain($this->profile->data[$this->plugin['name']]['result_description']), array(
        $this->plugin_name => $data,
      ));
    }
  }

  /**
   * Returns a string to use as the search result group name.
   */
  protected function buildGroup($group_name) {
    return check_plain($group_name);
  }

  /**
   * Returns a string with CSS classes that will be added to the search result
   * row for this item.
   *
   * @return
   *   A string with CSS classes
   */
  protected function buildRowClass($row_classes) {
    if (is_array($row_classes)) {
      $row_classes = implode(' ', $row_classes);
    }
    return $row_classes;
  }

  /**
   * Generate a settings form for this handler.
   * Uses the standard Drupal FAPI.
   *
   * @return
   *   An array containing any custom form elements to be displayed in the
   *   profile editing form
   */
  public function buildSettingsForm() {}

  /**
   * Determine if the handler is considered 'broken', meaning it's a
   * a placeholder used when a handler can't be found.
   */
  public function broken() { }
}

/**
 * A special handler to take the place of missing or broken handlers.
 */
class LinkitSearchPluginBroken extends LinkitSearchPlugin {

  public function ui_title() { return t('Broken/missing handler'); }

  public function ui_description() {}
  public function execute($serach_string) {}

  /**
   * Determine if the handler is considered 'broken'.
   */
  public function broken() { return TRUE; }
}