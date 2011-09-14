<?php

/**
 * @file
 */

 /**
 * Template preprocess function for theme_linkit_dashboard().
 */
function template_preprocess_linkit_dashboard(&$variables) {
  // Construct page title
  $variables['head_title']        = t('Linkit dashboard');
  $variables['head']              = drupal_get_html_head();
  $variables['help']              = theme('help');
  $variables['language']          = $GLOBALS['language'];
  $variables['language']->dir     = $GLOBALS['language']->direction ? 'rtl' : 'ltr';
  $variables['messages']          = theme('status_messages');
  $variables['css']               = drupal_add_css();
  $variables['styles']            = drupal_get_css();
  $variables['scripts']           = drupal_get_js();
}

/**
 * Processes variables
 *
 * The $variables array contains the following arguments:
 * - $form
 */
function template_preprocess_linkit_profiles_export_ui_form(&$variables) {
  _linkit_preprocess_tabledrag_variables($variables, 'plugins');
  _linkit_preprocess_tabledrag_variables($variables, 'attributes');
}

/**
 * Returns HTML for the profile form.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 */
function theme_linkit_profiles_export_ui_form($variables) {
  $form = &$variables['form'];

  $plugin_table = _linkit_theme_profile_form_table($variables, 'plugins');
  $form['data']['plugins_fieldset']['plugins']['#markup'] = $plugin_table;

  $attribute_table = _linkit_theme_profile_form_table($variables, 'attributes');
  $form['data']['attributes_fieldset']['attributes']['#markup'] = $attribute_table;

  $output = drupal_render_children($form);
  return $output;
}

/**
 * Preprocess tabledrag variables
 *
 * @param $variables
 * @param $type
 *   "plugin" or "attribute"
 */
function _linkit_preprocess_tabledrag_variables(&$variables, $type) {
  // Add each attribute in the form to the appropriate place in the attribute listing.
  foreach (element_children($variables['form']['data'][$type . '_fieldset'][$type]) as $i) {
    $element = &$variables['form']['data'][$type . '_fieldset'][$type][$i];

    // Set special classes needed for table drag and drop.
    $element['weight']['#attributes']['class'] = array('weight');

    $variables['linkit_' . $type . '_listing'][$i] = new stdClass();
    $variables['linkit_' . $type . '_listing'][$i]->title = drupal_render($element['name']);
    $variables['linkit_' . $type . '_listing'][$i]->enabled = drupal_render($element['enabled']);
    $variables['linkit_' . $type . '_listing'][$i]->weight_select = drupal_render($element['weight']);
    $variables['linkit_' . $type . '_listing'][$i]->printed = FALSE;

    // Add description if it extists.
    if (isset($element['description'])) {
      $variables['linkit_' . $type . '_listing'][$i]->description = drupal_render($element['description']);
    }
  }
}