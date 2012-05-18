<?php
/**
 * @file
 * Ctools export ui plugin to handle Linkit profiles.
 */

$plugin = array(
  'schema' => 'linkit_profiles',
  'access' => 'administer linkit',

  'menu' => array(
    'menu prefix' => 'admin/config/content',
    'menu item' => 'linkit',
    'menu title' => 'Linkit Profiles',
    'menu description' => t('Manage Linkit profiles.'),
  ),

  'title singular' => t('Linkit profile'),
  'title plural' => t('Linkit profiles'),
  'title singular proper' => t('Linkit profile'),
  'title plural proper' => t('Linkit profiles'),

  'handler' => 'linkit_profiles',
);

/**
 * Linkit profile settings form.
 *
 * @param $form
 *   A nested array of form elements that comprise the form.
 * @param $form_state
 *   An associative array containing the current state of the form.
 */
function linkit_profiles_form(&$form, &$form_state) {
  // Set data as a tree.
  $form['data']['#tree'] = TRUE;

  $profile = $form_state['item'];

  // Append serach plugin form element to the profile form.
  _linkit_build_search_plugin_form_fields($form, $profile);

   // Append insert plugin form element to the profile form.
  _linkit_build_insert_plugin_form_fields($form, $profile);

  // Append attribute plugin form element to the profile form.
  _linkit_build_attribute_plugin_form_fields($form, $profile);

  // @TODO: Move autocomplete settings to its own function.
  $form['data']['autocomplete'] = array(
    '#type' => 'fieldset',
    '#title' => t('Autocomplete options'),
    '#description' => t('Linkit uses !bac which may be configured with focus on performance.', array('!bac' => l('Better Autocomplete', 'https://github.com/betamos/Better-Autocomplete'))),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#weight' => 100,
  );

  $form['data']['autocomplete']['charLimit'] = array(
    '#title' => t('Character limit'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['charLimit']) ? $profile->data['autocomplete']['charLimit'] : '3',
    '#size' => 5,
    '#description' => t('Minimum number of characters to perform server polling. A higher value reduces server load. <em>Default: 3</em>'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  $form['data']['autocomplete']['wait'] = array(
    '#title' => t('Key press delay'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['wait']) ? $profile->data['autocomplete']['wait'] : '350',
    '#size' => 5,
    '#description' => t('Time from last key press to poll the server. A higher value reduces server load. <em>Default: 350</em>'),
    '#field_suffix' => t('ms'),
    '#element_validate' => array('linkit_isnumeric_validate'),
);

  $form['data']['autocomplete']['remoteTimeout'] = array(
    '#title' => t('Remote timeout'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['remoteTimeout']) ? $profile->data['autocomplete']['remoteTimeout'] : '10000',
    '#size' => 5,
    '#description' => t('Client side timeout for a request to the server. <em>Default: 10000</em>'),
    '#field_suffix' => t('ms'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );
}

/**
 * Linkit profile validate callback.
 * @see linkit_profiles_form().
 */
function linkit_profiles_form_validate(&$form, &$form_state) {
}


/**
 * Linkit profile submit callback.
 * @see linkit_profiles_form().
 */
function linkit_profiles_form_submit(&$form, &$form_state) {
}

/**
 * Append search plugin form element to the profile form.
 * @see linkit_profiles_form().
 *
 * @param $profile
 *   A profile object contains all settings for the profile.
 */
function _linkit_build_search_plugin_form_fields(&$form, LinkitProfile $profile) {
  // Load all search pluings.
  $search_plugins = linkit_search_plugin_load_all();

  $form['data']['search_plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Search pluings'),
    '#description' => t('Linkit is all about the search plugins. They define what content Linkit will present in the autocomplete search field.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
  );

  $form['data']['search_plugins_fieldset']['search_plugins'] = array(
    '#markup' => '',
    '#tree' => TRUE,
    '#parents' => array('data', 'search_plugins'),
    '#theme' => 'linkit_plugin_form_table',
  );

  $form['data']['search_plugins_fieldset']['search_plugins_settings'] = array(
    '#markup' => '',
    '#tree' => TRUE,
    '#parents' => array('data'),
  );

  // Used to store plugin form elements temporary so we can use this to sort by
  // weight later.
  $tmp = array();

  foreach ($search_plugins AS $name => $plugin_definition) {
    // Get a plugin instance.
    $plugin = LinkitSearchPlugin::factory($plugin_definition, $profile);

    $tmp[$name]['name'] = array('#markup' => $plugin->ui_title());
    $tmp[$name]['description'] = array('#markup' => $plugin->ui_description());
    $tmp[$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $plugin->ui_title())),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['search_plugins'][$name]['enabled'])
        ? $profile->data['search_plugins'][$name]['enabled'] : FALSE,
    );
    $tmp[$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $plugin->ui_title())),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['search_plugins'][$name]['weight'])
        ? $profile->data['search_plugins'][$name]['weight'] : '',
    );

    // Append the search pluing specific settings.
    $plugin_specific_settings = $plugin->buildSettingsForm();

    if ($plugin_specific_settings) {
      // Add the handler settings form to the generic.
      $form['data']['search_plugins_fieldset']['search_plugins_settings'] += $plugin_specific_settings;
    }
  }

  // Sort by weight.
  uasort($tmp, '_linkit_sort_plugins_by_weight');

  foreach ($tmp AS $name => $element) {
    $form['data']['search_plugins_fieldset']['search_plugins'][$name] = $element;
  }
}

/**
 * Append insert plugin form element to the profile form.
 * @see linkit_profiles_form().
 *
 * @param $profile
 *   A profile object contains all settings for the profile.
 */
function _linkit_build_insert_plugin_form_fields(&$form, $profile) {
  // Make a list of all insert pluings.
  $insert_pluings = array();
  foreach (linkit_insert_plugin_load_all() as $name => $plugin) {
    $insert_pluings[$name] = $plugin['name'];
  }

  $form['data']['insert_plugin'] = array(
    '#type' => 'fieldset',
    '#title' => t('Insert plugin'),
    '#description' => t(''),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
  );

  // Settings for insert plugins.
  $form['data']['insert_plugin']['plugin'] = array(
    '#type' => 'select',
    '#title' => t('Insert plugin'),
    '#options' => $insert_pluings,
    '#empty_option' => t('- Select an insert plugin -'),
    '#default_value' => isset($profile->data['insert_plugin'])
      ? $profile->data['insert_plugin'] : '',
    '#required' => TRUE,
  );
}

/**
 * Append attribute plugin form element to the profile form.
 * @see linkit_profiles_form().
 *
 * @param $profile
 *   A profile object contains all settings for the profile.
 */
function _linkit_build_attribute_plugin_form_fields(&$form, $profile) {
  // Load all attribute pluings.
  $attributes = linkit_attribute_load_all();

  $form['data']['attribute_plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Attributes'),
    '#description' => t('Attributes is HTML attributes.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
  );

  $form['data']['attribute_plugins_fieldset']['attribute_plugins'] = array(
    '#markup' => '',
    '#tree' => TRUE,
    '#parents' => array('data', 'attribute_plugins'),
    '#theme' => 'linkit_plugin_form_table',
  );

  // Used to store plugin form elements temporary so we can use this to sort by
  // weight later.
  $tmp = array();

  foreach ($attributes AS $name => $attribute) {
    $tmp[$name]['name'] = array('#markup' => $attribute['name']);
    $tmp[$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $attribute['name'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['attribute_plugins'][$name]['enabled'])
        ? $profile->data['attribute_plugins'][$name]['enabled'] : FALSE,
    );
    $tmp[$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $attribute['name'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['attribute_plugins'][$name]['weight'])
        ? $profile->data['attribute_plugins'][$name]['weight'] : '',
    );
  }

  // Sort by weight.
  uasort($tmp, '_linkit_sort_plugins_by_weight');

  foreach ($tmp AS $name => $element) {
    $form['data']['attribute_plugins_fieldset']['attribute_plugins'][$name] = $element;
  }
}

/**
 * Function used by uasort to sort pluing arrays by weight.
 */
function _linkit_sort_plugins_by_weight($a, $b) {
  return $a["weight"]['#default_value'] >= $b["weight"]['#default_value'];
}

/**
 * Extract tokens that can be used by the $type.
 *
 * @param $type
 *   A string with the entity type.
 */
function linkit_extract_tokens($type) {
  // token_info() has it own static cache, so we can call it as we like.
  $tokens = token_info();
  // If no tokens for the type exists, return an empty array.
  if (!isset($tokens['tokens'][$type])) {
    return array();
  }

  $available_tokens = array();
  foreach ($tokens['tokens'][$type] as $token_key => $token) {
    $available_tokens[] = '[' . $type . ':' . $token_key . ']';
  }

  return $available_tokens;
}

/**
 * Element validate callback for fields that should be numeric.
 *
 * This function is assigned as an #element_validate callback in
 * linkit_profiles_form().
 */
function linkit_isnumeric_validate($element, &$form_state, $form) {
  if (!empty($element['#value']) && !is_numeric($element['#value'])) {
    form_error($element, $element['#title'] . ' should only contains numbers.');
  }
}