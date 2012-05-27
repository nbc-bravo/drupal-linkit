<?php
/**
 * @file
 * Define Linkit id attribute plugin.
 */

$plugin = array(
  'name' => t('Id'),
  'callback' => 'linkit_attribute_plugin_id',
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
function linkit_attribute_plugin_id($settings = array()) {
  return array(
    '#type' => 'textfield',
    '#title' => 'id',
    '#maxlength' => 255,
    '#size' => 40,
    '#default_value' => '',
    '#weight' => isset($settings['weight']) ? $settings['weight'] : 0,
  );
}