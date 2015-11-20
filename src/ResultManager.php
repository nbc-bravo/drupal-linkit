<?php

/**
 * @file
 * Contains \Drupal\linkit\ResultManager.
 */

namespace Drupal\linkit;


use Drupal\Core\Url;

/**
 * Result service to handle autocomplete matcher results.
 */
class ResultManager {

  /**
   * Gets the results.
   *
   * @param ProfileInterface $linkitProfile
   *
   * @param $search_string
   *
   * @return array
   *
   */
  public function getResults(ProfileInterface $linkitProfile, $search_string) {
    $matches = array();
    // Special for link to front page.
    if (strpos($search_string, 'front') !== FALSE) {
      $matches[] = array(
        'title' => t('Frontpage'),
        'description' => 'The frontpage for this site.',
        'path' => Url::fromRoute('<front>')->toString(),
        'group' => t('System'),
      );
    }

    foreach ($linkitProfile->getMatchers() as $plugin) {
      $matches = array_merge($matches, $plugin->getMatches($search_string));
    }

    // If there is still no matches, return a "no results" array.
    if (empty($matches)) {
      return [
        [
          'title' => t('No results'),
        ]
      ];
    }

    return $matches;
  }
}
