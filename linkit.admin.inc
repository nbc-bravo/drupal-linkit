<?php

/**
 * @file
 * Admin page callbacks for the Linkit module.
 */
function linkit_admin() {
  
  $header = array(t('Profile name'), array('data' => t('Operations'), 'colspan' => 2));

  $rows[] = array('', '' );

  $output = theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'linkit-profiles')));

  return $output;

}

function linkit_admin_settings() {
  $form = array();
    
    // Include all the plugins
    linkit_include('node');
    linkit_include('taxonomy');
    
    // @Todo: 
    // Write profile name
    // Select plugins
    // Select settings
  
  $form['#tree'] = TRUE;

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => $profile->name,
    '#required' => TRUE,
  );
  
  $form['machine_name'] = array(
    '#type' => 'machine_name',
    '#required' => TRUE,
    '#default_value' => $profile->machine_name,
    '#maxlength' => 255,
    '#machine_name' => array(
      'exists' => 'linkit_profile_exists', // TODO: FIX THIS CALLBACK
    ),
    '#disabled' => !empty($profile->machine_name),
  );
  
    /*
    $node_settings = variable_get('linkit_node', array());

    // Prevent "PHP notice: Undefined variable"
    _linkit_node_get_default_settings($node_settings);

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
    
    $form['linkit_node'] = array(
      '#type' => 'fieldset',
      '#title' => t('Node settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('Linkit node settings'),
      '#tree' => TRUE,
    );

    $form['linkit_node']['display_settings']  = array(
      '#title' => t('Information to display in the autocomplete field'),
      '#type' => 'checkboxes',
      '#options' => $node_display_settings_options,
      '#default_value' => $node_settings['display_settings'],
    );
    
    $form['linkit_node']['content_types'] = array(
      '#title' => t('Include this content types in the search result'),
      '#type' => 'checkboxes',
      '#options' => node_type_get_names(),
      '#default_value' => $node_settings['content_types'], 
      '#description' => t('If none is checked, all content types will be present in the search result.'),
    );
    
    $term_settings = variable_get('linkit_term', array());

    // Prevent "PHP notice: Undefined variable"
    _linkit_taxonomy_get_default_settings($term_settings);
    
    $term_display_settings_options = array(
      'show_parent' => t('Display the parent of the term'),
    );

    $form['linkit_term'] = array(
      '#type' => 'fieldset',
      '#title' => t('Taxonomy term settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('Linkit term settings'),
      '#tree' => TRUE,
    );

    $form['linkit_term']['display_settings'] = array(
      '#title' => t('Information to display in the autocomplete field'),
      '#type' => 'checkboxes',
      '#options' => $term_display_settings_options,
      '#default_value' => $term_settings['display_settings'],
    );
    */
  
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
  
  $settings = array();
  foreach ($form_state['values'] as $key => $value) {
    if (is_array($value) && isset($form_state['values']['array_filter'])) {
      $value = array_keys(array_filter($value));
    }

    $settings[$key] = $value;
  }
  
  
  variable_set('linkit_profile_' . $form_state['values']['machine_name'], $settings);

  drupal_set_message(t('The configuration options have been saved.')); // @TODO: Add profile title tpo the text 
}