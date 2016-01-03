<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\UserMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Matcher(
 *   id = "entity:user",
 *   target_entity = "user",
 *   label = @Translation("User"),
 *   provider = "user"
 * )
 */
class UserMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summery = parent::getSummary();

    $summery[] = $this->t('Include blocked users: @include_blocked', [
      '@include_blocked' => $this->configuration['include_blocked'] ? $this->t('Yes') : $this->t('No'),
    ]);

    return $summery;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'include_blocked' => FALSE,
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + [
      'module' => ['user'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // @TODO: Add role limits?

    $form['include_blocked'] = [
      '#title' => t('Include blocked user'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['include_blocked'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['include_blocked'] = $form_state->getValue('include_blocked');
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match) {
    $query = parent::buildEntityQuery($match);

    $match = $this->database->escapeLike($match);
    // The user entity don't specify a label key so we have to do it instead.
    $query->condition('name', '%' . $match . '%', 'LIKE');

    if ($this->configuration['include_blocked'] !== TRUE) {
      $query->condition('status', 1);
    }

    return $query;
  }

}
