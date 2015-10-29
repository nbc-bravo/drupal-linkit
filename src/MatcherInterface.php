<?php

/**
 * @file
 * Contains \Drupal\linkit\MatcherInterface.
 */

namespace Drupal\linkit;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for matchers.
 *
 * @see \Drupal\linkit\Annotation\Matcher
 * @see \Drupal\linkit\MatcherBase
 * @see \Drupal\linkit\MatcherManager
 * @see plugin_api
 */
interface MatcherInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the unique ID representing the matcher.
   *
   * @return string
   *   The matcher ID.
   */
  public function getUuid();

  /**
   * Returns the matcher label.
   *
   * @return string
   *   The matcher label.
   */
  public function getLabel();

  /**
   * Returns the summarized configuration of the matcher.
   *
   * @return string
   *   The summarized configuration of the matcher.
   */
  public function getSummary();

  /**
   * Returns the weight of the matcher.
   *
   * @return int|string
   *   Either the integer weight of the matcher, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for the matcher.
   *
   * @param int $weight
   *   The weight for this matcher.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * @TODO: Doc this.
   * @param $string
   * @return mixed
   */
  public function getMatches($string);
}
