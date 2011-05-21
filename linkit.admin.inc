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

  $output = theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-profiles')));

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

  $node_display_settings_options = array(
    'nid' => t('Display node id (nid)'),
    'content_type' => t('Display node content type'),
    'status' => t('Display node published status'),
    'language' => t('Display node language'),
    'created' => t('Display node created time'),
    'changed' => t('Display node changed time'),
    'show_unpublished' => t('Show unpublished nodes in the result'),
  );
  
  if (module_exists('book')) {
    $node_display_settings_options += array('show_books' => t('Display the book a node belong to'));
  }
  
  $form['node'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    // KOLLA PÅ #STATES
  );

  $form['node']['display_settings'] = array(
    '#title' => t('Information to display in the autocomplete field'),
    '#type' => 'checkboxes',
    '#options' => $node_display_settings_options,
    '#default_value' => $profile['node']['display_settings'],
  );
  
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
  form_state_values_clean($form_state);
  // Unset actions values
  unset($form_state['values']['actions']);
  
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
