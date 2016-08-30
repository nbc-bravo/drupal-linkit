<?php

namespace Drupal\Tests\linkit\Kernel\Matchers;

use Drupal\linkit\MatcherInterface;
use Drupal\linkit\Suggestion\SuggestionCollection;

/**
 * Provides helper methods for assertions.
 */
trait AssertResultUriTrait {

  /**
   * Assert that paths are formatted as an URI with the entity: scheme.
   *
   * @param \Drupal\linkit\MatcherInterface $plugin
   *   A matcher plugin.
   * @param \Drupal\linkit\Suggestion\SuggestionCollection $suggestions
   *   A collection of suggestions.
   */
  public function assertResultUri(MatcherInterface $plugin, SuggestionCollection $suggestions) {
    $entity_type = $plugin->getPluginDefinition()['target_entity'];
    $substitution_id = $plugin->getConfiguration()['settings']['substitution_type'];
    foreach ($suggestions->getSuggestions() as $suggestion) {
      $this->assertTrue(preg_match("/^entity:" . $substitution_id . "\\/" . $entity_type . "\\/\\w+$/i", $suggestion->getPath()), 'Result URI correct formatted.');
    }
  }

}
