<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Selection\UserSelectionPlugin.
 */

namespace Drupal\linkit\Plugin\Linkit\Selection;

/**
 * @SelectionPlugin(
 *   id = "entity:user",
 *   target_entity = "user",
 *   label = @Translation("User"),
 *   description = @Translation("Adds support for user entities.")
 * )
 */
class UserSelectionPlugin extends EntitySelectionPlugin {

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
