<?php

/**
 * @file
 * Hooks and alters provided by Linkit.
 */

/**
 * Defines one or more attributes to use with Linkit.
 *
 * All attributes is defined as form elements, and it used both in the Linkit 
 * profile form and in the Linkit dashboard.
 *
 * See Drupal FAPI for more info.
 * http://api.drupal.org/api/drupal/developer--topics--forms_api_reference.html
 *
 * @param string $profile
 *   The profile the user is about to use with this attributes.
 * @return string
 *   An associative array with form elements defining your attributes.
 */
function hook_linkit_attributes($profile) {
  // The array key here will be the attribute that is inserted.
  // I.E <a my_attribute="value"></a>.
  $attributes['my_attribute'] = array(
    '#type' => 'textfield',
    '#title' => t('My attribute'),
    '#maxlength' => 255,  
    '#size' => 40,
    '#default_value' => '',
    '#weight' => isset($profile['attributes']['my_attribute']['weight']) ? $profile['attributes']['my_attribute']['weight'] : 0,
  );

  return $attributes;
}

/**
 * Alter a attribute before it has been processed.
 *
 * This hook is useful for altering the attribute form array that will be used
 * in both the Linkit profile form and in the Linkit dashboard.
 *
 * @param $attributes
 *   An associative array with form elements defining attributes.
 *
 * @see hook_linkit_attributes()
 */
function hook_linkit_attributes_alter(&$attributes) {
  $attributes['rel']['#type'] = 'select';
  $attributes['rel']['#title'] = t('Rel select');
  $attributes['rel']['#options'] = array(
    '' => t('None'), 
    'now-follow' => t('No follow'), 
    'other-rel' =>t('Other rel'),
  );
}

/**
 * Defines one or more plugins to use with Linkit.
 *
 * @return
 *   An associative array with the key being the machine name for the
 *   implementation and the values being an array with the following keys:
 *     - "title": The human readable name for the plugin.
 *     - "description": Short description for the plugin.
 *     - "file": (optional) The file where the "autocomplete callback" function 
 *       defined in.
 *     - "autocomplete callback": The function to call when the users search for
 *       something.
 *     - "path info callback": (optional) @TODO
 */
function hook_linkit_plugins() {
  $plugins['myplugin'] = array(
    'title' => t('My plugin'),
    'description' => t('My plugin implementation'),
    'file' => drupal_get_path('module', 'mymodule') . '/mymodule.inc',
    'autocomplete callback' => 'mymodule_autocomplete',
    'path info callback' => 'mymodule_path_info'
  );
  return $plugins;
}

// @TODO: Define how the 'autocomplete callback' function should work.