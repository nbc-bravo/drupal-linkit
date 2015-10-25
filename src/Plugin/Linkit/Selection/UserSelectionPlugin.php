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

  protected function buildEntityQuery($search_string) {
    $query = parent::buildEntityQuery($search_string);

    $search_string = $this->database->escapeLike($search_string);
    $query->condition('name', '%' . $search_string . '%', 'LIKE');

    return $query;
  }

}
