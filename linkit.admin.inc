<?php
/**
 * @file
 * Admin page callbacks for the Linkit module.
 */

/**
 * Menu callback; Displays a list of all profiles.
 */
function linkit_admin_page() {
  $header = array(t('Profile name'), array('data' => t('Operations'), 'colspan' => 2));

  // Get all created profiles.
  $profiles = linkit_get_profiles();

  $rows = array();
  foreach ($profiles AS $profile) {
    $rows[] = array(
      check_plain($profile['name']),
      l(t('Edit'), 'admin/config/content/linkit/profile/' . $profile['machine_name']),
      l(t('Delete'), 'admin/config/content/linkit/profile/' . $profile['machine_name'] . '/delete'),
    );
  }

  $output = '<h2>' . t('Profiles') . '</h2>';
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-profiles')));
  $linkit_profile_assignment = drupal_get_form('linkit_profile_assignment');
  $output .= drupal_render($linkit_profile_assignment);

  return $output;
}

 /**
 * Menu callback; Display a text format form.
 */
function linkit_admin_profile_page($profile = array()) {
  if (!isset($profile['name'])) {
    drupal_set_title(t('Add new linkit profile'));
  }
  return drupal_get_form('linkit_profile_form', $profile);
}

/**
 * Generate a profile form.
 */
function linkit_profile_form($form, &$form_state, $profile) {
  $form = array();

  $form['#tree'] = TRUE;

  $form['#attached']['css'] = array(
    drupal_get_path('module', 'linkit') . '/css/linkit.css',
  );

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'linkit') . '/linkit.admin.js',
  );

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => isset($profile['name']) ? $profile['name'] : '',
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
        'input[name="plugins[node][status]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['node']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['node']['result_description']) ? $profile['node']['result_description'] : '',
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

  $form['user'] = array(
    '#type' => 'fieldset',
    '#title' => t('User settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[user][status]"]' => array('checked' => FALSE),
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
        'input[name="plugins[taxonomy][status]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['taxonomy']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['taxonomy']['result_description']) ? $profile['taxonomy']['result_description'] : '',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[term:tid], [term:name], [term:vocabulary], [term:parent]')),
  );

  $form['file'] = array(
    '#type' => 'fieldset',
    '#title' => t('File settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="plugins[file][status]"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['file']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['file']['result_description']) ? $profile['file']['result_description'] : '',
    '#size' => 120,
    '#maxlength' => 255,
    '#description' => t('Available tokens are: %tokens.', array('%tokens' => '[file:fid], [file:name], [file:mime], [file:size], [file:timestamp], [file:owner]')),
  );

  $form['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#weight' => 100,
  );

  $form['advanced']['charlimit'] = array(
    '#title' => t('The minimum number of chars to do an AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['advanced']['charlimit']) ? $profile['advanced']['charlimit'] : '',
    '#size' => 5,
    '#description' => t('A typical use case for this limit is to reduce server load. (Default : 3)'),
  );

  $form['advanced']['wait'] = array(
    '#title' => t('TThe time in ms between last keypress and AJAX call'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['advanced']['wait']) ? $profile['advanced']['wait'] : '',
    '#size' => 5,
    '#description' => t('(Default : 250)'),
    '#field_suffix' => t('milliseconds'),
  );

  $form['advanced']['getparam'] = array(
    '#title' => t(' The get parameter for AJAX calls'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['advanced']['getparam']) ? $profile['advanced']['getparam'] : '',
    '#size' => 20,
    '#description' => t('(Default : "s")'),
    '#field_prefix' => t('?'),
    '#field_suffix' => t('=[Searchstring]'),
  );

  $form['advanced']['ajaxtimtout'] = array(
    '#title' => t('Timeout on AJAX calls'),
    '#type' => 'textfield',
    '#default_value' => isset($profile['advanced']['ajaxtimtout']) ? $profile['advanced']['ajaxtimtout'] : '',
    '#size' => 5,
    '#description' => t('(Default : 5000)'),
    '#field_suffix' => t('milliseconds'),
  );

  // Append attributes form element to the setttings form.
  _linkit_build_attribute_form_fields($form, $profile);

 
  $form['actions'] = array(
    '#type' => 'actions',
    '#weight' => 101,
  );
  
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }

  return $form;
}

/**
 * Validate profile form submissions.
 */
function linkit_profile_form_validate($form, &$form_state) {
}

/**
 * Process profile form submissions.
 */
function linkit_profile_form_submit($form, &$form_state) {
  // Exclude unnecessary elements.
  _linkit_form_state_values_clean($form_state);

  // Get the current profile settings variable.
  $profiles = linkit_get_profiles();

  // Save all vaules into the variable.
  // This so other modules can preform hook_gform_alter here and get there
  // variables saved aswell.
  foreach ($form_state['values'] as $key => $value) {
    $profiles[$form_state['values']['machine_name']][$key] = $value;
  }

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_profiles', $profiles);

  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

/**
 * Processes variables
 *
 * The $variables array contains the following arguments:
 * - $form
 *
 * @see linkit_profile_form()
 */
function template_preprocess_linkit_profile_form(&$variables) {
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
function theme_linkit_profile_form($variables) {
  $form = &$variables['form'];

  $plugin_table = _linkit_theme_profile_form_table($variables, 'plugins');
  $form['plugins_fieldset']['plugins']['#markup'] = $plugin_table;

  $attribute_table = _linkit_theme_profile_form_table($variables, 'attributes');
  $form['attributes_fieldset']['attributes']['#markup'] = $attribute_table;

  $output = drupal_render_children($form);
  return $output;
}

/**
 * Menu callback; confirm removal of a Linkit profile.
 *
 * @ingroup forms.
 * @see linkit_admin_profile_delete_submit()
 */
function linkit_profile_delete_form($form, &$form_state, $profile) {
  $form['#profile'] = $profile;

  return confirm_form($form,
    t('Are you sure you want to delete the profile %profile?', array('%profile' => $profile['name'])),
    'admin/config/content/linkit',
    t('This action cannot be undone.'),
    t('Delete')
  );
}

/**
 * Process Linkit profile removal form submission.
 */
function linkit_profile_delete_form_submit($form, &$form_state) {
  $profile = $form['#profile'];

  // Get the current profile settings variable.
  $profiles = linkit_get_profiles();

  // Delete the profile from the variable.
  unset($profiles[$profile['machine_name']]);

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_profiles', $profiles);

  drupal_set_message(t('Deleted Linkit profile %profile.', array('%profile' => $profile['name'])));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

/**
 * Generate the role-profile assignments form.
 *
 * @ingroup forms.
 */
function linkit_profile_assignment($form, &$form_state) {
  $form = array();
  $drupal_roles = user_roles();

  // Get the current role -> profile configuration variable.
  $roles_profiles = variable_get('linkit_roles_profiles', array());
  foreach ($drupal_roles AS $rid => $role_name) {
    $form['roles'][$rid] = array(
      '#type' => 'select',
      '#title' => '',
      '#default_value' => isset($roles_profiles[$rid]) ? $roles_profiles[$rid] : '',
      '#options' => _linkit_get_profile_names(),
      '#empty_option' => t('- None -'),
    );
  }
  $form['#theme'] = 'linkit_profile_assignment';
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }

  return $form;
}

/**
 * Get all Linkit profiles and return there names.
 */
function _linkit_get_profile_names() {
  $profiles = linkit_get_profiles();
  $profile_names= array();
  foreach ($profiles AS $profile) {
    $profile_names[$profile['machine_name']] = $profile['name'];
  }
  return $profile_names;
}

/**
 * Theme the role-profile assignments form.
 */
function theme_linkit_profile_assignment($variables) {
  $form = $variables['form'];
  $drupal_roles = user_roles();

  $header = array(t('User role'), t('Linkit profile'));
  $rows = array();

  foreach ($drupal_roles AS $rid => $role_name) {
    $rows[] = array($role_name, drupal_render($form['roles'][$rid]));
  }

  $output = '<h2>' . t('Role-profile assignments') . '</h2>';
  $output .= theme('table', array('header' => $header, 'rows' => $rows));
  $output .= drupal_render_children($form);
  return $output;
}

/**
 * Validate profile assignment form submissions.
 */
function linkit_profile_assignment_validate($form, &$form_state) {
}

/**
 * Process profile assignment form submissions.
 */
function linkit_profile_assignment_submit($form, &$form_state) {
  // Exclude unnecessary elements.
  _linkit_form_state_values_clean($form_state);

  // Get the current role -> profile configuration variable.
  $profile_settings = variable_get('linkit_roles_profiles', array());

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_roles_profiles', $form_state['values']);

  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

/**
 * Extended form_state_values_clean.
 *
 * @see form_state_values_clean()
 */
function _linkit_form_state_values_clean(&$form_state) {
  // Exclude unnecessary elements.
  form_state_values_clean($form_state);
  // Unset actions values.
  unset($form_state['values']['actions']);
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
    $tmp_plugins[$name]['name'] = array('#markup' => check_plain($plugin['title']));
    $tmp_plugins[$name]['description'] = array('#markup' => check_plain($plugin['description']));
    $tmp_plugins[$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $plugin['title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['plugins'][$name]['weight']) ? $profile['plugins'][$name]['weight'] : LINKIT_DEFAULT_WEIGHT,
    );
    $tmp_plugins[$name]['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $plugin['title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['plugins'][$name]['status']) ? $profile['plugins'][$name]['status'] : LINKIT_DISABLED,
    );
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

    $form['attributes_fieldset']['attributes'][$name]['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['attributes'][$name]['status']) ? $profile['attributes'][$name]['status'] : LINKIT_DISABLED,
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
    $variables['linkit_' . $type . '_listing'][$i]->status = drupal_render($element['status']);
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
      $element->status
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