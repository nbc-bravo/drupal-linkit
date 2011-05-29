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
  
  // Get the current profile settings variable
  $profile_settings = variable_get('linkit_profiles', array());
  
  $rows = array();
  foreach($profile_settings AS $profile) {
    $rows[] = array(
      $profile['name'], 
      l(t('Edit'), 'admin/config/content/linkit/profile/'. $profile['machine_name']), 
      l(t('Delete'), 'admin/config/content/linkit/profile/'. $profile['machine_name'] . '/delete'),
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
    $profile = array(
      'name' => NULL,
      'machine_name' => NULL,
    );
  }
  return drupal_get_form('linkit_admin_settings', $profile);
}

/**
 * Generate a profile form.
 *
 * @ingroup forms
 * @see linkit_admin_settings_validate()
 * @see linkit_admin_settings_submit()
 */
function linkit_admin_settings($form, &$form_state, $profile) {
  $form = array();
      
  $form['#tree'] = TRUE;

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => $profile['name'],
    '#required' => TRUE,
  );

  $form['machine_name'] = array(
    '#type' => 'machine_name',
    '#required' => TRUE,
    '#default_value' => $profile['machine_name'],
    '#maxlength' => 255,
    '#machine_name' => array(
      'exists' => 'linkit_profile_exists',
    ),
    '#disabled' => !empty($profile['machine_name']),
  );
 
  $form['node'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    // KOLLA PÅ #STATES
  );
  
   $form['node']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => $profile['node']['result_description'],
    '#description' => t('@TODO Better text here. You can use tokens here'),
  );

  
  // @TODO: Change this to exclude?
  $form['node']['content_types'] = array(
    '#title' => t('Include this content types in the search result'),
    '#type' => 'checkboxes',
    '#options' => node_type_get_names(),
    '#default_value' => $profile['node']['content_types'], 
    '#description' => t('If none is checked, all content types will be present in the search result.'),
  );

  $term_display_settings_options = array(
    'show_parent' => t('Display the parent of the term'),
  );

  $form['user'] = array(
    '#type' => 'fieldset',
    '#title' => t('User settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    // KOLLA PÅ #STATES
  );
  
  $form['user']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => $profile['user']['result_description'],
    '#description' => t('@TODO Better text here. You can use tokens here'),
  );

  $form['taxonomy'] = array(
    '#type' => 'fieldset',
    '#title' => t('Taxonomy term settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
  );

  $form['taxonomy']['display_settings'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'checkboxes',
    '#options' => $term_display_settings_options,
    '#default_value' => $profile['taxonomy']['display_settings'],
  );

  $form['taxonomy']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => $profile['taxonomy']['result_description'],
    '#description' => t('@TODO Better text here. You can use tokens here'),
  );

  $form['file'] = array(
    '#type' => 'fieldset',
    '#title' => t('File settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    // KOLLA PÅ #STATES
  );
  
  $form['file']['result_description'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'textfield',
    '#default_value' => $profile['file']['result_description'],
    '#description' => t('@TODO Better text here. You can use tokens here'),
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
  
  // Get the current profile settings variable
  $profile_settings = variable_get('linkit_profiles', array());
  
  // Save all vaules into the variable
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
 * Menu callback; confirm removal of a Linkit profile.
 *
 * @ingroup forms
 * @see linkit_admin_profile_delete_submit()
 */
function linkit_admin_profile_delete($form, &$form_state, $profile) {
  $form['#profile'] = $profile;

  return confirm_form($form,
    t('Are you sure you want to delete the profile %profile?', array('%profile' => $profile['name'])),
    'admin/config/content/linkit',
    t('@TODO: Help text abpout delete. This action cannot be undone.'),
    t('Delete')
  );
}

/**
 * Process Linkit profile removal form submission.
 */
function linkit_admin_profile_delete_submit($form, &$form_state) {
  $profile = $form['#profile'];
  
  // Get the current profile settings variable
  $profile_settings = variable_get('linkit_profiles', array());
  
  // Delete the profile from the variable
  unset($profile_settings[$profile['machine_name']]);

  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_profiles', $profile_settings);

  drupal_set_message(t('Deleted Linkit profile %profile.', array('%profile' => $profile['name'])));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

/**
 * Enter description here ...
 */
function linkit_profile_assignment($form, &$form_state) {
  $form = array();
  $drupal_roles = user_roles();

  // Get the current role -> profile configuration variable
  $profile_settings = variable_get('linkit_roles_profiles', array()); // @TODO: named "profile_settings" ??

  foreach($drupal_roles AS $rid => $role_name) {
    $form['roles'][$rid] = array(
      '#type' => 'select', 
      '#title' => '', 
      '#default_value' => $profile_settings[$rid],
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
 * Enter description here ...
 */
function _linkit_get_profile_names() {
  // Get all profiles
  $profile_settings = variable_get('linkit_profiles', array());
  $profiles = array();
 
  foreach($profile_settings AS $profile) {
    $profiles[$profile['machine_name']] = $profile['name'];
  }
  
  return $profiles;
}

/**
 * Enter description here ...
 */
function theme_linkit_profile_assignment($variables) {
  $form = $variables['form'];
  $drupal_roles = user_roles();

  $header = array(t('User role'), t('Linkit profile'));
  $rows = array();

  foreach($drupal_roles AS $rid => $role_name) {
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
  
  // Get the current role -> profile configuration variable
  $profile_settings = variable_get('linkit_roles_profiles', array());
    
  // Save the hole settings array. Note: This includes ALL profiles.
  variable_set('linkit_roles_profiles', $form_state['values']);
  
  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/content/linkit';
}

function _linkit_form_state_values_clean(&$form_state) {
   // Exclude unnecessary elements.
  form_state_values_clean($form_state);
  // Unset actions values
  unset($form_state['values']['actions']);
}