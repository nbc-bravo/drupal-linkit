<?php

$plugin = array(
  'schema' => 'linkit_profiles',
  'access' => 'administer linkit',
  'create access' => 'administer linkit',

  'menu' => array(
    'menu prefix' => 'admin/structure',
    'menu item' => 'linkit-profiles',
    'menu title' => 'Linkit Profiles',
    'menu description' => 'Administer Linkit profiles.',
  ),

  'title singular' => t('Linkit profile'),
  'title plural' => t('Linkit profiles'),
  'title singular proper' => t('Dynamic formatter preset'),
  'title plural proper' => t('Dynamic formatter presets'),

  'form' => array(
     'settings' => 'linkit_profiles_export_ui_form',
     //'submit' => 'dynamic_formatters_export_ui_form_submit'
   ),
);

/**
 * Generate a profile form.
 */
function linkit_profiles_export_ui_form(&$form, &$form_state, $profile = array()) {
  $form = array();

  $form['#tree'] = TRUE;
$form['#theme'] = 'linkit_profiles_export_ui_form';

  $form['#attached']['css'] = array(
    drupal_get_path('module', 'linkit') . '/css/linkit.css',
  );

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => isset($profile['name']) ? $profile['name'] : 'Untitled Profile',
    '#required' => TRUE,
  );

  $form['machine_name'] = array(
    '#type' => 'machine_name',
    '#required' => TRUE,
    '#default_value' => isset($profile['machine_name']) ? $profile['machine_name'] : '',
    '#maxlength' => 255,
    '#machine_name' => array(
      'exists' => 'linkit_profile_exists',
    ),
    '#disabled' => !empty($profile['machine_name']),
  );

  // Append plugin form element to the setttings form.
  _linkit_build_plugin_form_fields($form, $profile);

  $form['node'] = array(
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

  $form['node']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['node']['result_description']) ? $profile['node']['result_description'] : 'by [node:author] · [node:created:short]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[node:nid], [node:vid], [node:tnid], [node:type], [node:type-name], [node:title], [node:language], [node:created], [node:changed], [node:author]')),
  );

  $form['node']['content_types'] = array(
    '#title' => t('Include this content types in the search result'),
    '#type' => 'checkboxes',
    '#options' => node_type_get_names(),
    '#default_value' => isset($profile['node']['content_types']) ? $profile['node']['content_types'] : array(),
    '#description' => t('If none is checked, all content types will be present in the search result.'),
  );

  $form['node']['include_unpublished'] = array(
    '#title' => t('Include unpublished nodes in the result'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile['node']['include_unpublished']) ? $profile['node']['include_unpublished'] : 0,
  );

  $form['node']['group_by_content_type'] = array(
    '#title' => t('Group the nodes by the content type.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile['node']['group_by_content_type']) ? $profile['node']['group_by_content_type'] : 1,
  );

  $form['user'] = array(
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

  $form['user']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['user']['result_description']) ? $profile['user']['result_description'] : '',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[user:uid], [user:name], [user:mail], [user:last-login], [user:created]')),
  );

  $form['taxonomy'] = array(
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

  $form['taxonomy']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['taxonomy']['result_description']) ? $profile['taxonomy']['result_description'] : '[term:vocabulary]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[term:tid], [term:name], [term:vocabulary], [term:parent]')),
  );

  $form['taxonomy']['group_by_vocabulary'] = array(
    '#title' => t('Group the terms by the vocabulary.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile['taxonomy']['group_by_vocabulary']) ? $profile['taxonomy']['group_by_vocabulary'] : 0,
  );

  $form['file'] = array(
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

  $form['file']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['file']['result_description']) ? $profile['file']['result_description'] : '[file:mime] · [file:size]',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[file:fid], [file:name], [file:mime], [file:size], [file:timestamp], [file:owner]')),
  );

  $image_extra_info_options = array(
    // Coder is saying "$text argument to l() should be enclosed within t() so that it is translatable." but image styles is not translatable.
    'thumbnail' => t('Image thumbnail (Uses the image style named !linkit_thumb_link)', array('!linkit_thumb_link' => l('linkit_thumb', 'admin/config/media/image-styles/edit/linkit_thumb'))),
    'dimensions' => t('Full images dimensions (width and height)'),
  );

  $form['file']['image_extra_info'] = array(
    '#title' => t('If the file is an image, include this in the search result description.'),
    '#type' => 'checkboxes',
    '#options' => $image_extra_info_options,
    '#default_value' => isset($profile['file']['image_extra_info']) ? $profile['file']['image_extra_info'] : array('thumbnail', 'dimensions'),
    '#description' => t('This will be added before the "Information to display in the autocomplete field".'),
  );

  $form['file']['show_scheme'] = array(
    '#title' => t('Include the file scheme in the search result description.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile['file']['show_scheme']) ? $profile['file']['show_scheme'] : array(),
    '#description' => t('This will be added after the "Information to display in the autocomplete field".'),
  );

  $form['file']['group_by_scheme'] = array(
    '#title' => t('Group the files by the scheme.'),
    '#type' => 'checkbox',
    '#default_value' => isset($profile['file']['group_by_scheme']) ? $profile['file']['group_by_scheme'] : array(),
  );

  if (module_invoke('imce', 'access')) {
    $form['imce'] = array(
      '#type' => 'fieldset',
      '#title' => t('IMCE'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['imce']['use_imce'] = array(
      '#title' => t('Let the users use !imce for this profile.', array('!imce' => l('IMCE', 'admin/config/media/imce'))),
      '#type' => 'checkbox',
      '#default_value' => isset($profile['imce']['use_imce']) ? $profile['imce']['use_imce'] : '',
      '#description' => t('We only support public files with IMCE.'),
    );
  }

  $form['autocomplete'] = array(
    '#type' => 'fieldset',
    '#title' => t('Autocomplete options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#weight' => 100,
  );

  $form['autocomplete']['charLimit'] = array(
    '#title' => t('The minimum number of chars to do an AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['autocomplete']['charlimit']) ? $profile['autocomplete']['charlimit'] : '3',
    '#size' => 5,
    '#description' => t('A typical use case for this limit is to reduce server load. (Default : 3)'),
    '#element_validate' => array('linkit_isnumeric_validate'),
  );

  $form['autocomplete']['wait'] = array(
    '#title' => t('The time in ms between last keypress and AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['autocomplete']['wait']) ? $profile['autocomplete']['wait'] : '250',
    '#size' => 5,
    '#description' => t('(Default : 250)'),
    '#field_suffix' => t('milliseconds'),
    '#element_validate' => array('linkit_isnumeric_validate'),
   );

  $form['autocomplete']['remoteTimeout'] = array(
    '#title' => t('Timeout on AJAX calls'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['autocomplete']['ajaxtimeout']) ? $profile['autocomplete']['ajaxtimeout'] : '5000',
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
  $form['plugins_fieldset']['plugins']['#markup'] = $plugin_table;

  $attribute_table = _linkit_theme_profile_form_table($variables, 'attributes');
  $form['attributes_fieldset']['attributes']['#markup'] = $attribute_table;

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
  $form['plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Plugins'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
    '#theme' => '_linkit_theme_profile_form_table',
  );

  $form['plugins_fieldset']['plugins'] = array(
    '#markup' => '',
    '#tree' => TRUE,
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
      '#default_value' => isset($profile['plugins'][$name]['weight']) ? $profile['plugins'][$name]['weight'] : LINKIT_DEFAULT_WEIGHT,
    );
    $tmp_plugins[$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => t($plugin['title']))),
      '#title_display' => 'invisible',
    );
    if (!empty($profile)) {
      $tmp_plugins[$name]['enabled']['#default_value'] = isset($profile['plugins'][$name]['enabled']) ? $profile['plugins'][$name]['enabled'] : FALSE;
    }
    // Default enabled plugins
    elseif (in_array($name, array('node', 'external', 'file'))) {
      $tmp_plugins[$name]['enabled']['#default_value'] = TRUE;
    }
  }

  // Sort by #weight.
  uasort($tmp_plugins, '_linkit_sort_plugins_by_weight');

  foreach ($tmp_plugins AS $name => $plugin) {
    $form['plugins_fieldset']['plugins'][$name] = $plugin;
  }

}

/**
 * Append attributes form element to the setttings form.
 */
function _linkit_build_attribute_form_fields(&$form, $profile) {

  $form['attributes_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Attributes'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
  );

  $form['attributes_fieldset']['attributes'] = array(
    '#markup' => '',
    '#tree' => TRUE,
  );

  $attrs = linkit_get_attributes($profile);

  // Sort by #weight.
  uasort($attrs, '_linkit_sort_attributes_by_weight');

  foreach ($attrs as $name => $attr) {
    $form['attributes_fieldset']['attributes'][$name]['name'] = array('#markup' => check_plain($attr['#title']));

    $form['attributes_fieldset']['attributes'][$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['attributes'][$name]['weight']) ? $profile['attributes'][$name]['weight'] : LINKIT_DEFAULT_WEIGHT,
    );

    $form['attributes_fieldset']['attributes'][$name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['attributes'][$name]['enabled']) ? $profile['attributes'][$name]['enabled'] : FALSE,
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
  foreach (element_children($variables['form'][$type . '_fieldset'][$type]) as $i) {
    $element = &$variables['form'][$type . '_fieldset'][$type][$i];

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

function dynamic_formatters_export_ui_form_submit(&$form, &$form_state) {
  $form_state['item']->renderer = $form_state['values']['renderer'];
  $form_state['item']->parent_style = $form_state['values']['parent_style'];
  $form_state['item']->children_style = $form_state['values']['children_style'];
  $form_state['item']->title = $form_state['values']['title'];
}
