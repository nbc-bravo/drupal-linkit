<?php
/**
 * @file
 * Implementation for Fields and Linkit.
 */


/**
 * Return a list of allowed field types.
 */
function linkit_allowed_field_types() {
  $allowed_field_types = array(
    'link_field',
    'text',
  );
  drupal_alter('linkit_allowed_field_types', $allowed_field_types);
  return $allowed_field_types;
}

/**
 * Return a list of allowed widget types.
 */
function linkit_allowed_widget_types() {
  $allowed_widget_types = array(
    'link_field',
    'text_textfield',
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

  // Make a list of all profiles.
  $profiles = array();
  foreach (linkit_profile_load_all() as $profile) {
    $profiles[$profile->name] = $profile->admin_title;
  }

  // Add the linkit settings to the field instance form.
  if (in_array($form['#field']['type'], $allowed_field_types) && in_array($form['instance']['widget']['type']['#value'], $allowed_widget_types)) {
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

    $form['instance']['settings']['linkit']['profile'] = array(
      '#type' => 'select',
      '#title' => t('Profile'),
      '#options' => $profiles,
      '#default_value' => isset($instance['settings']['linkitprofileenable'])
        ? $instance['settings']['linkit']['profile'] : '',
      '#states' => array(
        'invisible' => array(
          'input[name="instance[settings][linkit][enable]"]' => array('checked' => FALSE),
        ),
      ),
    );
  }
}

/**
 * Implements hook_field_widget_form_alter().
 */
function linkit_field_widget_form_alter(&$element, &$form_state, $context) {
  $instance = $context['instance'];
  $field = $context['field'];

  if (isset($instance['settings']['linkit']['enable']) && $instance['settings']['linkit']['enable']) {
    // Add the javascript files needed for the linkit field.
    drupal_add_library('linkit', 'linkit_field');

    switch ($instance['widget']['type']) {
      case 'link_field':
        $settings['fields'] = array(
          $instance['field_name'] . '['  . $context['langcode']  . ']['  . $context['delta'] . '][url]' => 1,
        );
        break;

      case 'text_textfield':
        $settings['fields'] = array(
          $instance['field_name'] . '['  . $context['langcode']  . ']['  . $context['delta'] . '][value]' => 1,
        );
        break;
    }

    if (isset($settings)) {
      $element['#attributes']['class'][] = 'linkit';
      // Add base settings.
      _linkit_add_settings('field');

      drupal_add_js(array('linkit' => $settings), 'setting');
    }
  }
}