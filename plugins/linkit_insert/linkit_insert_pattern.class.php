<?php
/**
 * @file
 */

class LinkitInsertPatternPlugin extends LinkitInsertPlugin {
  function settingsForm(&$form, &$form_state) {
    parent::settingsForm($form, $form_state);
    $form['pattern_type'] = array(
      '#type' => 'select',
      '#options' => array(
        '[%link](%title)' => 'markdown',
        '<a href="%link">%title</a>' => 'html',
      )
    );
  }
}