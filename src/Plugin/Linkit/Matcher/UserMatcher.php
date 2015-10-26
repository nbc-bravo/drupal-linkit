<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\UserMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;

/**
 * @Matcher(
 *   id = "entity:user",
 *   target_entity = "user",
 *   label = @Translation("User"),
 *   description = @Translation("Adds support for user entities."),
 *   provider = "user"
 * )
 */
class UserMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + [
      'module' => ['user'],
    ];
  }

  // @TODO: Add role limits?

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match) {
    $query = parent::buildEntityQuery($match);

    $match = $this->database->escapeLike($match);
    $query->condition('name', '%' . $match . '%', 'LIKE');

    return $query;
  }

}
