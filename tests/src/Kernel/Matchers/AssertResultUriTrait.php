<?php

namespace Drupal\Tests\linkit\Kernel\Matchers;

use Drupal\linkit\Suggestion\SuggestionCollection;

/**
 * Provides helper methods for assertions.
 */
trait AssertResultUriTrait {

  /**
   * Assert that paths are formatted as an URI with the entity: scheme.
   *
   * @param string $entity_type
   *   The entity_type.
   * @param \Drupal\linkit\Suggestion\SuggestionCollection $suggestions
   *   A collection of suggestions.
   */
  public function assertResultUri($entity_type, SuggestionCollection $suggestions) {
    foreach ($suggestions->getSuggestions() as $suggestion) {
      $this->assertTrue(preg_match("/^entity:canonical\/" . $entity_type . "\\/\\w+$/i", $suggestion->getPath()), 'Result URI correct formatted.');
    }
  }

}
