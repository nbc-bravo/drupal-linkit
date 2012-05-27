<?php
/**
 * @file
 * Define Linkit accesskey attribute plugin.
 */

$plugin = array(
  'name' => t('Accesskey'),
  'callback' => 'linkit_attribute_plugin_accesskey',
);

/**
 * Create a FAPI element.
 *
 * @param array $settings
 *   An array of settings for this attribute. This is from the profile settings.
 *
 * @return
 *   An FAPI element.
 *
 * @see LinkitProfile::setAttributes()
 */
function linkit_attribute_plugin_accesskey($settings = array()) {
  return array(
    '#type' => 'textfield',
    '#title' => 'accesskey',
    '#maxlength' => 255,
    '#size' => 40,
    '#default_value' => '',
    '#weight' => isset($settings['weight']) ? $settings['weight'] : 0,
  );
}