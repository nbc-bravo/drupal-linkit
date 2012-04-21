<?php
/**
 * @file
 */

class LinkitInsertPatternPlugin extends LinkitInsertPlugin {
  function settingsForm(&$form, &$form_state, $instance) {
    parent::settingsForm($form, $form_state, $instance);

    $form['pattern_type'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => t('Pattern type'),
      '#empty_option' => t('- Select a pattern type -'),
      '#default_value' => isset($instance['settings']['linkit']['insert_plugin_settings']['pattern_type'])
        ? $instance['settings']['linkit']['insert_plugin_settings']['pattern_type'] : '',
      '#options' => array(
        '[%link](%title)' => 'markdown',
        '<a href="%link">%title</a>' => 'html',
      )
    );
  }
}