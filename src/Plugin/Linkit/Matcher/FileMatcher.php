<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\FileMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;

/**
 * @Matcher(
 *   id = "entity:file",
 *   target_entity = "file",
 *   label = @Translation("File"),
 *   provider = "file"
 * )
 */
class FileMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + [
      'module' => ['file'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match) {
    $query = parent::buildEntityQuery($match);
    $query->condition('status', FILE_STATUS_PERMANENT);

    return $query;
  }

}
