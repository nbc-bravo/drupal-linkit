<?php
/**
 * @file
 * Implementation for Field UI and Linkit.
 */

/**
 * Implements hook_form_FIELD_UI_FIELD_EDIT_FORM_alter().
 */
function linkit_form_field_ui_field_edit_form_alter(&$form, &$form_state) {
  $instance = $form['#instance'];
  // Add the linkit settings to the field instance form.
  if ($form['#field']['type'] == 'text' && $form['instance']['widget']['type']['#value'] == 'text_textfield') {
    $form['instance']['settings']['linkit'] = array(
      '#type' => 'fieldset',
      '#title' => t('Linkit field settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['instance']['settings']['linkit']['enable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable Linkit support.'),
      '#default_value' => $instance['settings']['linkit']['enable'],
    );
  }
}