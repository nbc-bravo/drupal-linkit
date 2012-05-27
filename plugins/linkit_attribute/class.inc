<?php
/**
 * @file
 * Define Linkit class attribute plugin.
 */

$plugin = array(
  'name' => t('Class'),
  'callback' => 'linkit_attribute_plugin_class',
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
function linkit_attribute_plugin_class($settings = array()) {
  return array(
    '#type' => 'textfield',
    '#title' => 'class',
    '#maxlength' => 255,
    '#size' => 40,
    '#default_value' => '',
    '#weight' => isset($settings['weight']) ? $settings['weight'] : 0,
  );
}