<?php
/**
 * @file
 * Ctools export ui plugin to handle Linkit profiles.
 */

$plugin = array(
  'schema' => 'linkit_profiles',
  'access' => 'administer linkit',
  'create access' => 'administer linkit',

  'menu' => array(
    //'menu prefix' => 'admin/config/content/',
    'menu item' => 'linkit',
    'menu title' => 'Linkit Profiles',
    'menu description' => 'Manage internal linking and control what results are shown in the autocomplete search.',
    'items' => array(
        'add' => array(
        'path' => 'add',
        'title' => 'New profile',
        'page callback' => 'ctools_export_ui_switcher_page',
        'page arguments' => array('linkit_profiles', 'add'),
        'access callback' => 'ctools_export_ui_task_access',
        'access arguments' => array('linkit_profiles', 'add'),
        'type' => MENU_LOCAL_ACTION,
        'weight' => -100,
      ),
    ),
  ),

  'title singular' => t('Linkit profile'),
  'title plural' => t('Linkit profiles'),
  'title singular proper' => t('Dynamic formatter preset'),
  'title plural proper' => t('Dynamic formatter presets'),

  'form' => array(
     'settings' => 'linkit_profiles_export_ui_form',
     'submit' => 'linkit_profiles_export_ui_form_submit',
   ),
);

/**
 * Generate a profile form.
 *
 * @param $form
 *   A form array
 *
 * @param $form_state
 *   A
 */
function linkit_profiles_export_ui_form(&$form, &$form_state) {
  $profile = $form_state['item'];

  $form = array();
  $form['#theme'] = 'linkit_profiles_export_ui_form';
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'linkit') . '/css/linkit.css',
  );

  $form['data']['#tree'] = TRUE;

  $form['data']['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => isset($profile->title) ? $profile->title : 'Untitled Profile',
    '#required' => TRUE,
  );

  $form['data']['name'] = array(
    '#type' => 'machine_name',
    '#required' => TRUE,
    '#default_value' => isset($profile->name) ? $profile->name : '',
    '#maxlength' => 255,
    '#machine_name' => array(
      'exists' => 'linkit_profile_exists',
      'source' => array('data', 'title'),
    ),
    '#disabled' => !is_null($profile->name),
  );

  $form['data']['text_format'] = array(
    '#type' => 'select',
    '#title' => t('Text format'),
    '#default_value' => isset($profile->data['text_format']) ? $profile->data['text_format'] : '',
    '#options' => _linkit_get_text_formats($profile),
    '#description' => t('Attach this profile to one text format. A text format can only be attached to one profile at the same time.'),
  );

  // Append plugin form element to the setttings form.
  _linkit_build_plugin_form_fields($form, $profile);

  $form['data']['node'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[node][enabled]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['data']['node']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['node']['result_description']) ? $profile->data['node']['result_description'] : 'by [node:author] · [node:created:short]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[node:nid], [node:vid], [node:tnid], [node:type], [node:type-name], [node:title], [node:language], [node:created], [node:changed], [node:author]')),
  );

  $form['data']['node']['content_types'] = array(
    '#title' => t('Include this content types in the search result'),
    '#type' => 'checkboxes',
    '#options' => node_type_get_names(),
    '#default_value' => isset($profile->data['node']['content_types']) ? $profile->data['node']['content_types'] : array(),
    '#description' => t('If none is checked, all content types will be present in the search result.'),
  );

  $form['data']['node']['include_unpublished'] = array(
    '#title' => t('Include unpublished nodes in the result'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile->data['node']['include_unpublished']) ? $profile->data['node']['include_unpublished'] : 0,
  );

  $form['data']['node']['group_by_content_type'] = array(
    '#title' => t('Group the nodes by the content type.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile->data['node']['group_by_content_type']) ? $profile->data['node']['group_by_content_type'] : 1,
  );

  $form['data']['user'] = array(
    '#type' => 'fieldset',
    '#title' => t('User settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[user][enabled]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['data']['user']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['user']['result_description']) ? $profile->data['user']['result_description'] : '',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[user:uid], [user:name], [user:mail], [user:last-login], [user:created]')),
  );

  $form['data']['taxonomy'] = array(
    '#type' => 'fieldset',
    '#title' => t('Taxonomy term settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[taxonomy][enabled]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['data']['taxonomy']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['taxonomy']['result_description']) ? $profile->data['taxonomy']['result_description'] : '[term:vocabulary]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[term:tid], [term:name], [term:vocabulary], [term:parent]')),
  );

  $form['data']['taxonomy']['group_by_vocabulary'] = array(
    '#title' => t('Group the terms by the vocabulary.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile->data['taxonomy']['group_by_vocabulary']) ? $profile->data['taxonomy']['group_by_vocabulary'] : 0,
  );

  $form['data']['file'] = array(
    '#type' => 'fieldset',
    '#title' => t('File settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[file][enabled]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['data']['file']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['file']['result_description']) ? $profile->data['file']['result_description'] : '[file:mime] · [file:size]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[file:fid], [file:name], [file:mime], [file:size], [file:timestamp], [file:owner]')),
  );

  $image_extra_info_options = array(
    // Coder is saying "$text argument to l() should be enclosed within t() so that it is translatable." but image styles is not translatable.
    'thumbnail' => t('Image thumbnail (Uses the image style named !linkit_thumb_link)', array('!linkit_thumb_link' => l('linkit_thumb', 'admin/config/media/image-styles/edit/linkit_thumb'))),
    'dimensions' => t('Full images dimensions (width and height)'),
  );

  $form['data']['file']['image_extra_info'] = array(
    '#title' => t('If the file is an image, include this in the search result description.'),
    '#type' => 'checkboxes',
    '#options' => $image_extra_info_options,
    '#default_value' => isset($profile->data['file']['image_extra_info']) ? $profile->data['file']['image_extra_info'] : array('thumbnail', 'dimensions'),
    '#description' => t('This will be added before the "Information to display in the autocomplete field".'),
  );

  $form['data']['file']['show_scheme'] = array(
    '#title' => t('Include the file scheme in the search result description.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile->data['file']['show_scheme']) ? $profile->data['file']['show_scheme'] : array(),
    '#description' => t('This will be added after the "Information to display in the autocomplete field".'),
  );

  $form['data']['file']['group_by_scheme'] = array(
    '#title' => t('Group the files by the scheme.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile->data['file']['group_by_scheme']) ? $profile->data['file']['group_by_scheme'] : array(),
  );

  if (module_invoke('imce', 'access')) {
    $form['data']['imce'] = array(
      '#type' => 'fieldset',
      '#title' => t('IMCE'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['data']['imce']['use_imce'] = array(
      '#title' => t('Let the users use !imce for this profile.', array('!imce' => l('IMCE', 'admin/config/media/imce'))),
      '#type' => 'checkbox',
      '#default_value' => isset($profile->data['imce']['use_imce']) ? $profile->data['imce']['use_imce'] : '',
      '#description' => t('We only support public files with IMCE.'),
    );
  }

  $form['data']['autocomplete'] = array(
    '#type' => 'fieldset',
    '#title' => t('Autocomplete options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#weight' => 100,
  );

  $form['data']['autocomplete']['charLimit'] = array(
    '#title' => t('The minimum number of chars to do an AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['charlimit']) ? $profile->data['autocomplete']['charlimit'] : '3',
    '#size' => 5,
    '#description' => t('A typical use case for this limit is to reduce server load. (Default : 3)'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  $form['data']['autocomplete']['wait'] = array(
    '#title' => t('The time in ms between last keypress and AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['wait']) ? $profile->data['autocomplete']['wait'] : '250',
    '#size' => 5,
    '#description' => t('(Default : 250)'),
    '#field_suffix' => t('milliseconds'),
    '#element_validate' => array('linkit_isnumeric_validate'),
   );

  $form['data']['autocomplete']['remoteTimeout'] = array(
    '#title' => t('Timeout on AJAX calls'),
    '#type' => 'textfield',
    '#default_value' => isset($profile->data['autocomplete']['ajaxtimeout']) ? $profile->data['autocomplete']['ajaxtimeout'] : '5000',
    '#size' => 5,
    '#description' => t('(Default : 5000)'),
    '#field_suffix' => t('milliseconds'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  // Append attributes form element to the setttings form.
  _linkit_build_attribute_form_fields($form, $profile);
}

/**
 * Element validate callback for fields that should be numeric.
 */
function linkit_isnumeric_validate($element, &$form_state, $form) {
  if (!empty($element['#value']) && !is_numeric($element['#value'])) {
    form_error($element, $element['#title'] . ' should only contains numbers.');
  }

  // Alter the value by typecasting into an integer.
  $form_element = &$form_state['values'];
  foreach ($element['#array_parents'] as $current) {
    $form_element = &$form_element[$current];
  }
  $form_element = (int) $form_element;
}

/**
 * Processes variables
 *
 * The $variables array contains the following arguments:
 * - $form
 *
 * @see linkit_profile_form()
 */
function template_preprocess_linkit_profiles_export_ui_form(&$variables) {
  _linkit_preprocess_tabledrag_variables($variables, 'plugins');
  _linkit_preprocess_tabledrag_variables($variables, 'attributes');
}

/**
 * Returns HTML for the linkit_profile_form form.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 *
 * @ingroup themeable
 */
function theme_linkit_profiles_export_ui_form($variables) {
  $form = &$variables['form'];

  $plugin_table = _linkit_theme_profile_form_table($variables, 'plugins');
  $form['data']['plugins_fieldset']['plugins']['#markup'] = $plugin_table;

  $attribute_table = _linkit_theme_profile_form_table($variables, 'attributes');
  $form['data']['attributes_fieldset']['attributes']['#markup'] = $attribute_table;

  $output = drupal_render_children($form);
  return $output;
}

/**
 * Sort plugins by weight.
 */
function _linkit_sort_plugins_by_weight($a, $b) {
  return $a["weight"]['#default_value'] >= $b["weight"]['#default_value'];
}

/**
 * Sort attribute by weight.
 */
function _linkit_sort_attributes_by_weight($a, $b) {
  return $a["#weight"] >= $b["#weight"];
}

/**
 * Append plugin form element to the setttings form.
 */
function _linkit_build_plugin_form_fields(&$form, $profile) {
  $form['data']['plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Plugins'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
    '#theme' => '_linkit_theme_profile_form_table',
  );

  $form['data']['plugins_fieldset']['plugins'] = array(
    '#markup' => '',
    '#tree' => TRUE,
    '#parents' => array('data', 'plugins'),
  );

  // Get all plugins.
  $plugins = linkit_get_plugins();

  // Used to store plugin form elements temporary so we can use this to sort by weight.
  $tmp_plugins = array();

  foreach ($plugins AS $name => $plugin) {
    $tmp_plugins[$name]['name'] = array('#markup' => t($plugin['title']));
    $tmp_plugins[$name]['description'] = array('#markup' => t($plugin['description']));
    $tmp_plugins[$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => t($plugin['title']))),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['plugins'][$name]['weight']) ? $profile->data['plugins'][$name]['weight'] : LINKIT_DEFAULT_WEIGHT,
    );
    $tmp_plugins[$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => t($plugin['title']))),
      '#title_display' => 'invisible',
    );
    if (isset($profile->data['plugins'])) {
      $tmp_plugins[$name]['enabled']['#default_value'] = isset($profile->data['plugins'][$name]['enabled']) ? $profile->data['plugins'][$name]['enabled'] : FALSE;
    }
    // Default enabled plugins
    elseif (in_array($name, array('node', 'external', 'file'))) {
      $tmp_plugins[$name]['enabled']['#default_value'] = TRUE;
    }
  }

  // Sort by #weight.
  uasort($tmp_plugins, '_linkit_sort_plugins_by_weight');

  foreach ($tmp_plugins AS $name => $plugin) {
    $form['data']['plugins_fieldset']['plugins'][$name] = $plugin;
  }

}

/**
 * Append attributes form element to the setttings form.
 */
function _linkit_build_attribute_form_fields(&$form, $profile) {

  $form['data']['attributes_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Attributes'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
  );

  $form['data']['attributes_fieldset']['attributes'] = array(
    '#markup' => '',
    '#tree' => TRUE,
    '#parents' => array('data', 'attributes'),

  );

  $attrs = linkit_get_attributes($profile);

  // Sort by #weight.
  uasort($attrs, '_linkit_sort_attributes_by_weight');

  foreach ($attrs as $name => $attr) {
    $form['data']['attributes_fieldset']['attributes'][$name]['name'] = array('#markup' => check_plain($attr['#title']));

    $form['data']['attributes_fieldset']['attributes'][$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['attributes'][$name]['weight']) ? $profile->data['attributes'][$name]['weight'] : LINKIT_DEFAULT_WEIGHT,
    );

    $form['data']['attributes_fieldset']['attributes'][$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile->data['attributes'][$name]['enabled']) ? $profile->data['attributes'][$name]['enabled'] : FALSE,
    );
  }
}

/**
 * Preprocess tabledrag variables
 *
 * @param $variables
 * @param $type
 *   "plugin" or "attribute"
 */
function _linkit_preprocess_tabledrag_variables(&$variables, $type) {
  // Add each attribute in the form to the appropriate place in the attribute listing.
  foreach (element_children($variables['form']['data'][$type . '_fieldset'][$type]) as $i) {
    $element = &$variables['form']['data'][$type . '_fieldset'][$type][$i];

    // Set special classes needed for table drag and drop.
    $element['weight']['#attributes']['class'] = array('weight');

    $variables['linkit_' . $type . '_listing'][$i] = new stdClass();
    $variables['linkit_' . $type . '_listing'][$i]->title = drupal_render($element['name']);
    $variables['linkit_' . $type . '_listing'][$i]->enabled = drupal_render($element['enabled']);
    $variables['linkit_' . $type . '_listing'][$i]->weight_select = drupal_render($element['weight']);
    $variables['linkit_' . $type . '_listing'][$i]->printed = FALSE;

    // Add description if it extists.
    if (isset($element['description'])) {
      $variables['linkit_' . $type . '_listing'][$i]->description = drupal_render($element['description']);
    }
  }
}

/**
 * Helper function to render settings tables.
 *
 * @param $variables.
 * @param $type
 *   "plugin" or "attribute".
 *
 * @see theme_linkit_profile_form()
 */
function _linkit_theme_profile_form_table($variables, $type) {
  $rows = array();
  $has_description = FALSE;

  // Build table rows.
  foreach ($variables['linkit_' . $type . '_listing'] as $delta => $element) {
    $fields = array(
      $element->title,
      $element->weight_select,
      $element->enabled
    );

    if (isset($element->description)) {
      $has_description = TRUE;
      $fields[] = $element->description;
    }

    $rows[$delta]['data'] = $fields;
    $rows[$delta]['class'] = array('draggable', 'tabledrag-leaf');
  }

  drupal_add_tabledrag('linkit-' . $type, 'order', 'sibling', 'weight');

  $header = array(
    t('Name'),
    t('Weight'),
    t('Enabled'),
  );

  if ($has_description) {
    $header[] = t('Description');
  }

  return theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-' . $type), 'sticky' => FALSE));
}
/**
 * Save callback for linkit_profiles_export_ui_form.
 *
 *
 *
 * @param array $form
 * @param array $form_state
 */
function linkit_profiles_export_ui_form_submit(&$form, &$form_state) {

  $form_state['item']->title = trim($form_state['values']['data']['title']);
  $form_state['item']->name = $form_state['values']['data']['name'];

  // Unset this two values as this will not be saves in the data field.
  unset($form_state['values']['data']['title'], $form_state['values']['data']['name']);

  $form_state['item']->data = serialize($form_state['values']['data']);

  // CTools will work on this variable alter and screw it up, so unset it.
  unset($form_state['values']['data']);
}