<?php

namespace Drupal\linkit;

use Drupal\linkit\Suggestion\SuggestionCollection;

/**
 * Suggestion service to handle autocomplete suggestions.
 */
class SuggestionManager {

  /**
   * Gets the suggestions.
   *
   * @param ProfileInterface $linkitProfile
   *   The linkit profile.
   * @param string $search_string
   *   The string ro use in the matchers.
   *
   * @return \Drupal\linkit\Suggestion\SuggestionCollection
   *   A suggestion collection.
   */
  public function getSuggestions(ProfileInterface $linkitProfile, $search_string) {
    $suggestions = new SuggestionCollection();

    if (empty(trim($search_string))) {
      return $suggestions;
    }

    foreach ($linkitProfile->getMatchers() as $plugin) {
      $suggestions->addSuggestions($plugin->execute($search_string));
    }

    return $suggestions;
  }

}
