<?php
/**
 * @file
 * Admin page callbacks for the Linkit module.
 */

/**
 * 
 */
define('LINKIT_ATTR_ENABLED', 1);

/**
 * 
 */
define('LINKIT_ATTR_DISABLED', 0);

/**
 * Menu callback; Displays a list of all profiles.
 */
function linkit_admin_page() {
  $header = array(t('Profile name'), array('data' => t('Operations'), 'colspan' => 2));

  // Get the current profile settings variable.
  $profile_settings = variable_get('linkit_profiles', array());

  $rows = array();
  foreach ($profile_settings AS $profile) {
    $rows[] = array(
      check_plain($profile['name']),
      l(t('Edit'), 'admin/config/content/linkit/profile/' . $profile['machine_name']), 
      l(t('Delete'), 'admin/config/content/linkit/profile/' . $profile['machine_name'] . '/delete'),
    );
  }

  $output = '<h2>' . t('Profiles') . '</h2>';
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-profiles')));
  $linkit_profile_assignment = drupal_get_form('linkit_profile_assignment');
  $output .= drupal_render($linkit_profile_assignment );
  return $output;
}

 /**
 * Menu callback; Display a text format form.
 */
function linkit_admin_profile_page($profile = NULL) {
  if (!isset($profile['name'])) {
    drupal_set_title(t('Add new linkit profile'));
    $profile = array();
  }
  return drupal_get_form('linkit_admin_settings', $profile);
}

/**
 * Generate a profile form.
 *
 * @ingroup forms.
 * @see linkit_admin_settings_validate().
 * @see linkit_admin_settings_submit().
 */
function linkit_admin_settings($form, &$form_state, $profile) {
  $form = array();

  $form['#tree'] = TRUE;

  $form['#attached']['css'] = array(
    drupal_get_path('module', 'linkit') . '/css/linkit.css',
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

  $form['enabled_plugins_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enabled plugins'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
  );

  $plugins = array();
  foreach (linkit_load_plugins() AS $name => $plugin) {
    $plugins[$name]['name'] =  $plugin['title'];
    $plugins[$name]['desc'] =  $plugin['description'];
  }

  $form['enabled_plugins_fieldset']['enabled_plugins'] = array(
    '#type' => 'tableselect', 
    '#header' => array(
      'name' => array('data' => t('Name')),
      'desc' => array('data' => t('Description')),
    ), 
    '#options' => $plugins, 
    '#empty' => t('No plugins available'),
    '#default_value' => isset($profile['enabled_plugins']) ? $profile['enabled_plugins'] : array(),
    '#js_select' => FALSE,
  );

  $form['enabled_attributes_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enabled attributes'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
  );

  $form['enabled_attributes_fieldset']['enabled_attributes'] = array(
    '#markup' => '',
    '#tree' => TRUE,
  );

  $enabled_attributes_weight = 0;
  $attrs = _linkit_get_attributes(isset($profile['machine_name']) ? $profile['machine_name'] : NULL);

  // Sort by #weight.
  uasort($attrs, "_linkit_sort_attributes");

  foreach ($attrs as $name => $attr) {
    $form['enabled_attributes_fieldset']['enabled_attributes'][$name]['name'] = array('#markup' => check_plain($attr['#title']));
    
    $form['enabled_attributes_fieldset']['enabled_attributes'][$name]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight for @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['enabled_attributes'][$name]['weight']) ? $profile['enabled_attributes'][$name]['weight'] : $enabled_attributes_weight,
    );
    
    $form['enabled_attributes_fieldset']['enabled_attributes'][$name]['status'] = array(
      '#type' => 'select',
      '#options' => array(
        LINKIT_ATTR_ENABLED => t('Enabled'),
        LINKIT_ATTR_DISABLED => t('Disabled'),
      ),
      '#title' => t('Status for @title', array('@title' => $attr['#title'])),
      '#title_display' => 'invisible',
      '#default_value' => isset($profile['enabled_attributes'][$name]['status']) ? $profile['enabled_attributes'][$name]['status'] : 0,
    );
    $enabled_attributes_weight++;
  }

  $form['node'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => TRUE,
    '#states' => array(
      'invisible' => array(
        'input[name="enabled_plugins[node]"]' => array('checked' =>FALSE),
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

  // @TODO: Change this to exclude?
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
        'input[name="enabled_plugins[user]"]' => array('checked' =>FALSE),
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
        'input[name="enabled_plugins[taxonomy]"]' => array('checked' =>FALSE),
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
        'input[name="enabled_plugins[file]"]' => array('checked' =>FALSE),
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
 * Validate profile form submissions.
 */
function linkit_admin_settings_validate($form, &$form_state) {
}

/**
 * Process profile form submissions.
 */
function linkit_admin_settings_submit($form, &$form_state) {

  // Exclude unnecessary elements.
  _linkit_form_state_values_clean($form_state);

  // Get the current profile settings variable.
  $profile_settings = variable_get('linkit_profiles', array());

  // Save all vaules into the variable.
  // This so other modules can preform hook_gform_alter here and get there
  // variables saved aswell.
  foreach ($form_state['values'] as $key => $value) {
    $profile_settings[$form_state['values']['machine_name']][$key] = $value;
  }

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_profiles', $profile_settings);

  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

/**
 * Processes variables 
 *
 * The $variables array contains the following arguments:
 * - $form
 *
 * @see linkit_admin_settings()
 */
function template_preprocess_linkit_admin_settings(&$variables) {
  $variables['linkit_attr_status'] = array(
    LINKIT_ATTR_ENABLED => t('Enabled'),
    LINKIT_ATTR_DISABLED => t('Disabled'),
  );

  $variables['linkit_attr_listing'] = array(
    LINKIT_ATTR_ENABLED => array(),
    LINKIT_ATTR_DISABLED => array(),
  );

   // Add each attribute in the form to the appropriate place in the attribute listing.
  foreach (element_children($variables['form']['enabled_attributes_fieldset']['enabled_attributes']) as $i) {
    $attr = &$variables['form']['enabled_attributes_fieldset']['enabled_attributes'][$i];
 
    // Fetch the status for the current attribute.
    $status = (isset($attr['status']['#default_value']) ? $attr['status']['#default_value'] : LINKIT_ATTR_DISABLED);

    // Set special classes needed for table drag and drop.
    $attr['status']['#attributes']['class'] = array('attribute-status-select', 'attribute-status-' . $status);
    $attr['weight']['#attributes']['class'] = array('attribute-weight', 'attribute-weight-' . $status);
    
    $variables['linkit_attr_listing'][$status][$i] = new stdClass();
    $variables['linkit_attr_listing'][$status][$i]->row_class = !empty($attr['#attributes']['class']) ? implode(' ', $attr['#attributes']['class']) : '';
    $variables['linkit_attr_listing'][$status][$i]->title = drupal_render($attr['name']);
    $variables['linkit_attr_listing'][$status][$i]->status_select = drupal_render($attr['status']);
    $variables['linkit_attr_listing'][$status][$i]->weight_select = drupal_render($attr['weight']);
    $variables['linkit_attr_listing'][$status][$i]->printed = FALSE;
  }
}

/**
 * Returns HTML for the linkit_admin_settings form.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 *
 * @ingroup themeable
 */
function theme_linkit_admin_settings($variables) {
  $form = &$variables['form'];
  $output = '';

  $header = array(
    t('Name'),
    t('Weight'),
    t('Status')
  );

  $rows = array();
  foreach ($variables['linkit_attr_status'] as $status => $title) {
    // Build status headers.
    $rows[] = array(
      'data' => array(
        array(
          'data' => $title,
          'colspan' => '3',
        ),
      ),
      'class' => array('linkit-status-header'),
    );

    foreach($variables['linkit_attr_listing'][$status] as $delta => $attr) {
      $rows[] = array(
        'data' => array(
          $attr->title,
          $attr->weight_select,
          $attr->status_select,
        ),
        'class' => array('draggable'),
      );
    }

    drupal_add_tabledrag('linkit-attributes', 'order', 'sibling', 'attribute-weight', 'attribute-weight-' . $status);
  }

  $table =  theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-attributes')));
  $form['enabled_attributes_fieldset']['enabled_attributes']['#markup'] = $table;
  $output = drupal_render_children($form);

  return $output;
}

/**
 * Menu callback; confirm removal of a Linkit profile.
 *
 * @ingroup forms.
 * @see linkit_admin_profile_delete_submit().
 */
function linkit_admin_profile_delete($form, &$form_state, $profile) {
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
function linkit_admin_profile_delete_submit($form, &$form_state) {
  $profile = $form['#profile'];

  // Get the current profile settings variable.
  $profile_settings = variable_get('linkit_profiles', array());

  // Delete the profile from the variable.
  unset($profile_settings[$profile['machine_name']]);

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_profiles', $profile_settings);

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
      '#default_value' => isset($roles_profiles[$rid]) ? isset($roles_profiles[$rid]) : '',
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
  // Get all profiles.
  $profile_settings = variable_get('linkit_profiles', array());
  $profiles = array();

  foreach ($profile_settings AS $profile) {
    $profiles[$profile['machine_name']] = $profile['name'];
  }

  return $profiles;
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
 * @see form_state_values_clean().
 */
function _linkit_form_state_values_clean(&$form_state) {
  // Exclude unnecessary elements.
  form_state_values_clean($form_state);
  // Unset actions values.
  unset($form_state['values']['actions']);
}

/**
 * Sort attributes by weight.
 */
function _linkit_sort_attributes($a, $b) {
  return $a["#weight"] >= $b["#weight"];
}