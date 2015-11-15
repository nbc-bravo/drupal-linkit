<?php

/**
 * @file
 * Contains \Drupal\linkit\MatcherTokensTrait.
 */

namespace Drupal\linkit;

/**
 * Provides friendly methods for matchers using tokens.
 */
trait MatcherTokensTrait {

  /**
   * Inserts a form element with a list of available tokens.
   *
   * @param $form
   *   The form array to append the token list to.
   * @param array $types
   *   An array of token types to use.
   */
  public function insertTokenList(&$form, array $types = array()) {
    $token_items = array();
    foreach ($this->getAvailableTokens($types) as $type => $tokens) {
      foreach ($tokens as $name => $info) {
        $token_items[$type . ':' . $name] = "[$type:$name]" . ' - ' . $info['name'] . ': ' . $info['description'];
      }
    }

    if (count($token_items)) {
      $form['tokens'] = array(
        '#type' => 'details',
        '#title' => t('Available tokens'),
      );

      $form['tokens']['list'] = array(
        '#theme' => 'item_list',
        '#items' => $token_items,
      );
    }
  }

  /**
   * Gets all available tokens.
   *
   * @param array $types
   *   An array of token types to use.
   * @return array
   *   An array with available tokens
   */
  public function getAvailableTokens(array $types = array()) {
    $info = \Drupal::token()->getInfo();
    $available = array_intersect_key($info['tokens'], array_flip($types));
    return $available;
  }

}
