<?php
// $Id$

/**
 * @file
 * Admin page callbacks for the Linkit module.
 */

/**
 * Form configure the Linikt.
 */
function linkit_admin_settings() {
  
  $settings = variable_get('linkit_node', array());
  
  $form['node'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description' => t('Select which data that should be added to the search results in the autocomplete list'),
  );

  $form['node']['nid'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display node id'),
    '#default_value' => $settings['nid'] ? $settings['nid'] : 0,
  );

  $form['node']['content_type'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display node content type'),
    '#default_value' => $settings['content_type'] ? $settings['content_type'] : 0,
  );

  $form['node']['language'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display node language'),
    '#default_value' => $settings['language'] ? $settings['language'] : 0,
  );

  $form['node']['status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display node status'),
    '#default_value' => $settings['status'] ? $settings['status'] : 0,
  );

   $form['node']['show_unpublished'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show unpublished nodes in the result'),
    '#default_value' => $settings['show_unpublished'] ? $settings['show_unpublished'] : 0,
  );

  $form['submit'] = array('#type' => 'submit', '#value' => t('Save'));

  return $form;
}


function linkit_admin_settings_submit($form, &$form_state) {
  $settings_array = array();
  $settings_array['nid'] = $form_state['values']['nid'];
  $settings_array['content_type'] = $form_state['values']['content_type'];
  $settings_array['language'] = $form_state['values']['language'];
  $settings_array['status'] = $form_state['values']['status'];
  $settings_array['show_unpublished'] = $form_state['values']['show_unpublished'];
  variable_set('linkit_node', $settings_array);
  drupal_set_message(t('Settings has been saved.'));
}