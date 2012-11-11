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
  $form['data'] = array(
    '#tree' => TRUE,
    '#type' => 'vertical_tabs',
  );

  $profile = $form_state['item'];

  // Append search plugin form element to the profile form.
  _linkit_build_search_plugin_form_fields($form, $profile);

   // Append insert plugin form element to the profile form.
  _linkit_build_insert_plugin_form_fields($form, $profile);

  // Append attribute plugin form element to the profile form.
  _linkit_build_attribute_plugin_form_fields($form, $profile);

  // Append autocomplete (BAC) form element to the profile form.
  _linkit_build_autocomplete_form_fields($form, $profile);

  if (module_exists('wysiwyg') || module_exists('ckeditor')) {
    // Append text format form element to the profile form.
    _linkit_build_text_format_form_fields($form, $profile);
  }
}

/**
 * Linkit profile validate callback.
 *
 * @see linkit_profiles_form()
 */
function linkit_profiles_form_validate(&$form, &$form_state) {
}


/**
 * Linkit profile submit callback.
 *
 * @see linkit_profiles_form()
 */
function linkit_profiles_form_submit(&$form, &$form_state) {
}

/**
 * Append text format form element to the profile form.
 *
 * @param LinkitProfile $profile
 *   A profile object contains all settings for the profile.
 *
 * @see linkit_profiles_form()
 */
function _linkit_build_text_format_form_fields(&$form, LinkitProfile $profile) {
   $form['data']['text_format'] = array(
    '#type' => 'fieldset',
    '#title' => t('Text formats'),
    '#description' => t('Attach this profile to one or more text formats. This is only used by editors like TinyMCE and CKEditor.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
  );

  $formats = array();
  foreach(filter_formats() as $format) {
    $formats[$format->format] = $format->name;
  }

  $form['data']['text_format']['format'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Formats'),
    '#options' => $formats,
    '#empty_option' => t('- Select a text format -'),
    '#default_value' => isset($profile->data['text_format'])
      ? $profile->data['text_format'] : array(),
    '#parents' => array('data', 'text_format'),
  );
}

/**
 * Append search plugin form element to the profile form.
 *
 * @param LinkitProfile $profile
 *   A profile object contains all settings for the profile.
 *
 * @see linkit_profiles_form()
 */
function _linkit_build_search_plugin_form_fields(&$form, LinkitProfile $profile) {
  // Load all search plugins.
  $search_plugins = linkit_search_plugin_load_all();

  $form['data']['search_plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Search plugins'),
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

    // Append the search plugin specific settings.
    $plugin_specific_settings = $plugin->buildSettingsForm();

    if ($plugin_specific_settings) {
      // Add the handler settings form to the generic.
      $form['data']['search_plugins_fieldset']['search_plugins_settings'] += $plugin_specific_settings;
    }
  }

  // Sort by weight.
  uasort($tmp, '_linkit_sort_plugins_by_weight_default_value');

  foreach ($tmp AS $name => $element) {
    $form['data']['search_plugins_fieldset']['search_plugins'][$name] = $element;
  }
}

/**
 * Append insert plugin form element to the profile form.
 *
 * @param LinkitProfile $profile
 *   A profile object contains all settings for the profile.
 *
 * @see linkit_profiles_form()
 */
function _linkit_build_insert_plugin_form_fields(&$form, LinkitProfile $profile) {
  // Make a list of all insert plugins.
  $insert_plugins = array();
  foreach (linkit_insert_plugin_load_all() as $name => $plugin) {
    $insert_plugins[$name] = $plugin['name'];
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
    '#options' => $insert_plugins,
    '#empty_option' => t('- Select an insert plugin -'),
    '#default_value' => isset($profile->data['insert_plugin']['plugin'])
      ? $profile->data['insert_plugin'] : '',
    '#required' => TRUE,
  );

  $form['data']['insert_plugin']['url_method'] = array(
    '#title' => t("Insert paths as:"),
    '#type' => 'radios',
    '#title_display' => 'before',
    '#options' => array(
      LINKIT_URL_METHOD_RAW => t('Raw paths'),
      LINKIT_URL_METHOD_RAW_SLASH => t('Raw paths, with a slash (/) in the beginning.'),
    ),
    LINKIT_URL_METHOD_RAW => array(
      '#description' => t('This will insert Drupal internal paths like
         %example_link which is probably what you want if you are going to use
         this profile together with <strong>fields</strong>.
         <br/>If used in textareas, these paths typically need to be formatted
         using a input filter, but provides more flexibility in a multilingual
         site or when moving a site.', array('%example_link' => 'node/123')),
    ),
    LINKIT_URL_METHOD_RAW_SLASH => array(
      '#description' => t('This is the default behavior, it will insert paths
         like %example_link.<br/>These will work <strong>without the need for a input filter</strong>,
         but might fail after moving a site, or on multilingual sites.',
         array('%example_link' => '/node/123')),
    ),
    '#default_value' =>  isset($profile->data['insert_plugin']['url_method']) ?
      $profile->data['insert_plugin']['url_method'] : LINKIT_URL_METHOD_RAW_SLASH,
    '#description' => t('These will have no effect on already created links.'),
  );

  if (module_exists('path')) {
    $form['data']['insert_plugin']['url_method']['#options'][LINKIT_URL_METHOD_ALIAS] = t('Alias paths');
    $form['data']['insert_plugin']['url_method'][LINKIT_URL_METHOD_ALIAS] = array(
      '#description' => t('This will insert the alias for the result item.
        <br/>The paths will be runned thru the <em>url()</em> function.', array('%example_link' => 'node/123')),
    );
  }
}

/**
 * Append attribute plugin form element to the profile form.
 *
 * @param LinkitProfile $profile
 *   A profile object contains all settings for the profile.
 *
 * @see linkit_profiles_form()
 */
function _linkit_build_attribute_plugin_form_fields(&$form, LinkitProfile $profile) {
  // Load all attribute plugins.
  $attributes = linkit_attribute_plugin_load_all();

  $form['data']['attribute_plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Attributes'),
    '#description' => t('Attributes is HTML attributes that will be attached to
      the insert plugin.'),
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
  uasort($tmp, '_linkit_sort_plugins_by_weight_default_value');

  foreach ($tmp AS $name => $element) {
    $form['data']['attribute_plugins_fieldset']['attribute_plugins'][$name] = $element;
  }

  $form['data']['attribute_plugins_fieldset']['attribute_plugins_info'] = array(
    '#markup' => 'Keep in mind that not all attributes can be used by all insert plugins.',
  );
}

/**
 * Append autocomplete (BAC) form element to the profile form.
 *
 * @param LinkitProfile $profile
 *   A profile object contains all settings for the profile.
 *
 * @see linkit_profiles_form()
 */
function _linkit_build_autocomplete_form_fields(&$form, LinkitProfile $profile) {
  $form['data']['autocomplete'] = array(
    '#type' => 'fieldset',
    '#title' => t('Autocomplete options'),
    '#description' => t('Linkit uses !bac which may be configured with focus on
      performance.', array('!bac' => l(t('Better Autocomplete'),
      'https://github.com/betamos/Better-Autocomplete'))),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#weight' => 100,
  );

  $form['data']['autocomplete']['charLimit'] = array(
    '#title' => t('Character limit'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['charLimit']) ? $profile->data['autocomplete']['charLimit'] : LINKIT_CHAR_LIMIT,
    '#size' => 5,
    '#description' => t('Minimum number of characters to perform server polling.
      A higher value reduces server load. <em>Default: @default</em>', array('@default' => LINKIT_CHAR_LIMIT)),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  $form['data']['autocomplete']['wait'] = array(
    '#title' => t('Key press delay'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['wait']) ? $profile->data['autocomplete']['wait'] : LINKIT_WAIT,
    '#size' => 5,
    '#description' => t('Time from last key press to poll the server. A higher
      value reduces server load. <em>Default: @default</em>', array('@default' => LINKIT_WAIT)),
    '#field_suffix' => t('ms'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  $form['data']['autocomplete']['remoteTimeout'] = array(
    '#title' => t('Remote timeout'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['remoteTimeout']) ? $profile->data['autocomplete']['remoteTimeout'] : LINKIT_REMOTE_TIMEOUT,
    '#size' => 5,
    '#description' => t('Client side timeout for a request to the server.
      <em>Default: @default</em>', array('@default' => LINKIT_REMOTE_TIMEOUT)),
    '#field_suffix' => t('ms'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );
}

/**
 * Function used by uasort to sort plugin arrays by the weight default value.
 */
function _linkit_sort_plugins_by_weight_default_value($a, $b) {
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