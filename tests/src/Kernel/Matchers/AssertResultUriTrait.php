<?php

namespace Drupal\Tests\linkit\Kernel\Matchers;

/**
 * Provides helper methods for assertions.
 */
trait AssertResultUriTrait {

  /**
   * Assert that paths are formatted as an URI with the entity: scheme.
   *
   * @param string $entity_type
   *   The entity_type.
   * @param array $matches
   *   An array of matches.
   */
  public function assertResultUri($entity_type, $matches) {
    foreach ($matches as $match) {
      $this->assertTrue(preg_match("/^entity:" . $entity_type . "\\/\\w+$/i", $match['path']), 'Result URI correct formatted.');
    }
  }

}
