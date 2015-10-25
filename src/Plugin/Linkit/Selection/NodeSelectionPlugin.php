<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Selection\NodeSelectionPlugin.
 */

namespace Drupal\linkit\Plugin\Linkit\Selection;

use Drupal\Core\Form\FormStateInterface;

/**
 * @SelectionPlugin(
 *   id = "entity:node",
 *   target_entity = "node",
 *   label = @Translation("Content"),
 *   description = @Translation("Adds support for node entities.")
 * )
 */
class NodeSelectionPlugin extends EntitySelectionPlugin {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'include_unpublished' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['include_unpublished'] = array(
      '#title' => t('Include unpublished nodes'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['include_unpublished'],
      '#description' => t('In order to see unpublished nodes, the user must also have permissions to do so.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['include_unpublished'] = $form_state->getValue('include_unpublished');
  }

}
