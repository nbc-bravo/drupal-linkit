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
  ctools_include('plugins');
  $instance = $form['#instance'];

  $allowed_field_types = linkit_allowed_field_types();
  $allowed_widget_types = linkit_allowed_widget_types();

  // Make a list of all profiles.
  $profiles = array();
  foreach (linkit_profile_load_all() as $profile) {
    $profiles[$profile->name] = $profile->admin_title;
  }

  // Add the linkit settings to the field instance form.
  if (in_array($form['#field']['type'], $allowed_field_types) &&
    in_array($form['instance']['widget']['type']['#value'], $allowed_widget_types)) {

    // In order to know what insert plugin we are going to render, we need to
    // check if we have changed the selection or if we have a default.
    if (isset($form_state['values']['instance']['settings']['linkit']['insert_plugin'])) {
      $selected_insert_plugin = $form_state['values']['instance']['settings']['linkit']['insert_plugin'];
    }
    elseif (isset($instance['settings']['linkit']['insert_plugin'])) {
      $selected_insert_plugin = $instance['settings']['linkit']['insert_plugin'];
    }

    // Fieldset for Linkit settings on this field instance.
    $form['instance']['settings']['linkit'] = array(
      '#type' => 'fieldset',
      '#title' => t('Linkit field settings'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    // Enable Linkit on this field instance.
    $form['instance']['settings']['linkit']['enable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable Linkit support.'),
      '#default_value' => isset($instance['settings']['linkit']['enable'])
        ? $instance['settings']['linkit']['enable'] : 0,
    );

    // If Linkit is enabled on this field instance, there must be an assign
    // profile.
    $form['instance']['settings']['linkit']['profile'] = array(
      '#type' => 'select',
      '#title' => t('Profile'),
      '#options' => $profiles,
      '#empty_option' => t('- Select a profile -'),
      '#default_value' => isset($instance['settings']['linkit']['profile'])
        ? $instance['settings']['linkit']['profile'] : '',
      '#states' => array(
        'invisible' => array(
          'input[name="instance[settings][linkit][enable]"]' => array('checked' => FALSE),
        ),
      ),
      //'#required' => TRUE,
      // @TODO: This needs to be requeired then linkit is enabled on this field.
    );

    // Load all insert plugins.
    $plugins = ctools_get_plugins('linkit', 'linkit_insert');
    foreach ($plugins as $name => $plugin) {
      $options[$name] = $plugin['name'];
    }

    // Settings for input plugins.
    $form['instance']['settings']['linkit']['insert_plugin'] = array(
      '#type' => 'select',
      '#title' => t('Insert plugin'),
      '#options' => $options,
      '#empty_option' => t('- Select an insert plugin -'),
      '#default_value' => isset($instance['settings']['linkit']['insert_plugin'])
        ? $instance['settings']['linkit']['insert_plugin'] : '',
      '#ajax' => array(
        'callback' => 'linkit_insert_plugin_settings_callback',
        'wrapper' => 'linkit_insert_plugin_settings',
      ),
      '#required' => TRUE,
    );

    // Placeholder for the #ajax stuff.
    $form['instance']['settings']['linkit']['insert_plugin_settings'] = array(
      '#type' => 'item',
      '#tree' => TRUE,
      '#prefix' => '<div id="linkit_insert_plugin_settings">',
      '#suffix' => '</div>',
    );

    if (isset($selected_insert_plugin) && !empty($selected_insert_plugin)) {
      // Try to load the insert plugin we have chosen tio use.
      $insert_plugin = ctools_plugin_load_class('linkit', 'linkit_insert', $selected_insert_plugin, 'handler');
      if ($insert_plugin) {
        // Load the input plugin settings form.
        $insert_plugin::settingsForm($form['instance']['settings']['linkit']['insert_plugin_settings'], $form_state, $instance);
      }
    }
  }
}

/**
 * Callback;
 */
function linkit_insert_plugin_settings_callback($form, $form_state) {
  return $form['instance']['settings']['linkit']['insert_plugin_settings'];
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
        //$plugin = $instance['settings']['linkit']['insert_plugin'];
        //$plugin_settings = $instance['settings']['linkit']['plugin_settings'];
        //ctools_include('plugins');
        //$plugin = ctools_get_plugins('linkit', 'linkit_insert', $plugin);
        //$plugin->process($instance, $element, $element['linkit']);
        break;
    }
    _linkit_add_settings('field');
/*
    if (isset($settings)) {
      $element['#attributes']['class'][] = 'linkit';
      // Add base settings.


      drupal_add_js(array('linkit' => $settings), 'setting');
    }
 */
  }
}