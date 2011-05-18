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
    
    linkit_include('node');
    linkit_include('taxonomy');

    // @Todo: 
    // Write profile name
    // Select plugins
    // Select settings

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
  
  return system_settings_form($form);
}