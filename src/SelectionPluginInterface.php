<?php

/**
 * @file
 * Contains \Drupal\linkit\SelectionPluginInterface.
 */

namespace Drupal\linkit;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for selection plugins.
 *
 * @see \Drupal\linkit\Annotation\SelectionPlugin
 * @see \Drupal\linkit\SelectionPluginBase
 * @see \Drupal\linkit\SelectionPluginManager
 * @see plugin_api
 */
interface SelectionPluginInterface extends PluginFormInterface, PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the selection plugin label.
   *
   * @return string
   *   The selection plugin label.
   */
  public function getLabel();

  /**
   * Returns the selection plugin description.
   *
   * @return string
   *   The selection plugin description.
   */
  public function getDescription();

  /**
   * Returns the weight of the selection plugin.
   *
   * @return int|string
   *   Either the integer weight of the selection plugin, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this selection plugin.
   *
   * @param int $weight
   *   The weight for this selection plugin.
   *
   * @return $this
   */
  public function setWeight($weight);

  public function getMatches($string);
}
