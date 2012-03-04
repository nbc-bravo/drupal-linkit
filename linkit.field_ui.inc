<?php
/**
 * @file
 * Implementation for Field UI and Linkit.
 */

/**
 * Return a list of allowed field types.
 */
function linkit_allowed_field_types() {
  $allowed_field_types = array(
    'entityreference',
    'link_field',
  );
  drupal_alter('linkit_allowed_field_types', $allowed_field_types);
  return $allowed_field_types;
}

/**
 * Return a list of allowed widget types.
 */
function linkit_allowed_widget_types() {
  $allowed_widget_types = array(
    'entityreference_autocomplete',
    'link_field',
  );
  drupal_alter('linkit_allowed_widget_types', $allowed_widget_types);
  return $allowed_widget_types;
}

/**
 * Implements hook_form_FIELD_UI_FIELD_EDIT_FORM_alter().
 */
function linkit_form_field_ui_field_edit_form_alter(&$form, &$form_state) {
  $instance = $form['#instance'];

  $allowed_field_types = linkit_allowed_field_types();
  $allowed_widget_types = linkit_allowed_widget_types();


  // Add the linkit settings to the field instance form.
  if ($form['#field']['type'] == 'text' && $form['instance']['widget']['type']['#value'] == 'text_textfield') {
    $form['instance']['settings']['linkit'] = array(
      '#type' => 'fieldset',
      '#title' => t('Linkit field settings'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );
    $form['instance']['settings']['linkit']['enable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable Linkit support.'),
      '#default_value' => isset($instance['settings']['linkit']['enable'])
        ? $instance['settings']['linkit']['enable'] : 0,
    );
  }
}