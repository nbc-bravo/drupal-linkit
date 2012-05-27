<?php
/**
 * @file
 * Define Linkit rel attribute plugin.
 */

$plugin = array(
  'name' => t('Rel'),
  'callback' => 'linkit_attribute_plugin_rel',
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
function linkit_attribute_plugin_rel($settings = array()) {
  return array(
    '#type' => 'textfield',
    '#title' => 'rel',
    '#maxlength' => 255,
    '#size' => 40,
    '#default_value' => '',
    '#weight' => isset($settings['weight']) ? $settings['weight'] : 0,
  );
}