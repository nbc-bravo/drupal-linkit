<?php
/**
 * @file
 */

interface LinkitInsertPluginInterface {
 /**
  * Process a FAPI element add whatever extra data needed.
  */
 public function process($instance, $element, $settings);

 public function settingsForm(&$form, &$form_state, $instance);

 public function settingsFormSubmit(&$form, $form_state);
}

abstract class LinkitInsertPlugin implements LinkitInsertPluginInterface {

  public function process($instance, $element, $settings) {
    $element['linkit'] = array(
      '#type' => 'button',
      '#value' => 'linkit',
      '#attached' => array(
        $this->attachJs(),
        $this->fieldSettings(),
      ),
    );
  }

  protected function attachJS() {
    return array(
      array($this->defintion['javascript']),
    );
  }

  protected function fieldSettings($field_name, $settings) {
    return array(
      'data' => array(
        'linkit' => array(
          'fields' => array(
            $field_name => array(
              'plugin' => $this->plugin,
              'settings' => $settings,
            ),
          ),
        ),
        'type' => 'setting',
      ),
    );
  }

 public function settingsForm(&$form, &$form_state, $instance) {}

 public function settingsFormSubmit(&$form, $form_state) {}

}
