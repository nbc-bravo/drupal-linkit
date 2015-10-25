<?php

/**
 * @file
 * Contains \Drupal\linkit\AttributePluginInterface.
 */

namespace Drupal\linkit;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for attributes plugins.
 *
 * @see \Drupal\linkit\Annotation\AttributePlugin
 * @see \Drupal\linkit\AttributePluginBase
 * @see \Drupal\linkit\AttributePluginManager
 * @see plugin_api
 */
interface AttributePluginInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the attribute plugin label.
   *
   * @return string
   *   The attribute plugin label.
   */
  public function getLabel();

  /**
   * Returns the attribute plugin description.
   *
   * @return string
   *   The attribute plugin description.
   */
  public function getDescription();

  /**
   * Returns the weight of the attribute plugin.
   *
   * @return int|string
   *   Either the integer weight of the attribute plugin, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this attribute plugin.
   *
   * @param int $weight
   *   The weight for this attribute plugin.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * The form element structure for this attribute plugin to be used in the
   * dialog.
   *
   * @param mixed $default_value
   *  The default value for the element. Used when editing an attribute n the
   *  dialog.
   *
   * @return array
   *  The form element.
   */
  public function buildFormElement($default_value);

}
