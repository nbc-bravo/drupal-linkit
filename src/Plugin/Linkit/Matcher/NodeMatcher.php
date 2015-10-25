<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;

use Drupal\Core\Form\FormStateInterface;

/**
 * @Matcher(
 *   id = "entity:node",
 *   target_entity = "node",
 *   label = @Translation("Content"),
 *   description = @Translation("Adds support for node entities.")
 * )
 */
class NodeMatcher extends EntityMatcher {

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
    $form['bundles']['#title'] = $this->t('Content types filter');
    $form['bundles']['#description'] = $this->t('If none of the checkboxes is checked, allow all content types.');
    $form['group_by_bundle']['#title'] = $this->t('Group by content type');

    $form['include_unpublished'] = [
      '#title' => t('Include unpublished nodes'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['include_unpublished'],
      '#description' => t('In order to see unpublished nodes, the user must also have permissions to do so.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['include_unpublished'] = $form_state->getValue('include_unpublished');
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match) {
    $query = parent::buildEntityQuery($match);

    $access = $this->currentUser->hasPermission('bypass node access') && !count($this->moduleHandler->getImplementations('node_grants'));

    if (!$access || $this->configuration['include_unpublished'] === FALSE) {
      $query->condition('status', NODE_PUBLISHED);
    }

    return $query;
  }

}
