<?php

/**
 * @file
 * Contains \Drupal\linkit\ProfileInterface.
 */

namespace Drupal\linkit;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a profile entity.
 */
interface ProfileInterface extends ConfigEntityInterface {

  /**
   * Gets the profile description.
   *
   * @return string
   *   The profile description.
   */
  public function getDescription();

  /**
   * Sets the profile description.
   *
   * @param string $description
   *   The profile description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the profile autocomplete configuration.
   *
   * @return array
   *   The profile autocomplete configuration.
   */
  public function getAutocompleteConfiguration();

  /**
   * Sets the profile autocomplete configuration.
   *
   * @param array $autocomplete_configuration
   *  The profile autocomplete configuration.
   *
   * @return $this
   */
  public function setAutocompleteConfiguration(array $autocomplete_configuration);

  /**
   * Returns a specific attribute plugin.
   *
   * @param string $attribute_plugin_id
   *   The attribute plugin ID.
   *
   * @return \Drupal\linkit\AttributePluginInterface
   *   The attribute plugin object.
   */
  public function getAttributePlugin($attribute_plugin_id);

  /**
   * Returns the attribute plugins for this profile.
   *
   * @return \Drupal\linkit\AttributePluginCollection|\Drupal\linkit\AttributePluginInterface[]
   *   The attribute plugin collection.
   */
  public function getAttributePlugins();

  /**
   * Adds an attribute plugin to this profile.
   *
   * @param array $configuration
   *   An array of attribute plugin configuration.
   *
   * @return $this
   */
  public function addAttributePlugin(array $configuration);

  /**
   * Removes an attribute plugin from this profile.
   *
   * @param string $instance_id
   *  The ID of the attribute plugin to remove.
   *
   * @return $this
   */
  public function removeAttributePlugin($instance_id);

  /**
   * Sets the configuration for an attribute plugin instance.
   *
   * @param string $instance_id
   *   The ID of the attribute plugin to set the configuration for.
   * @param array $configuration
   *   The attribute plugin configuration to set.
   *
   * @return $this
   */
  public function setAttributePluginConfig($instance_id, array $configuration);




















  /**
   * Returns a specific selection plugin.
   *
   * @param string $selection_plugin_id
   *   The selection plugin ID.
   *
   * @return \Drupal\linkit\SelectionPluginInterface
   *   The selection plugin object.
   */
  public function getSelectionPlugin($selection_plugin_id);

  /**
   * Returns the selection plugins for this profile.
   *
   * @return \Drupal\linkit\SelectionPluginCollection|\Drupal\linkit\SelectionPluginInterface[]
   *   The selection plugin collection.
   */
  public function getSelectionPlugins();

  /**
   * Adds a selection plugin to this profile.
   *
   * @param array $configuration
   *   An array of selection plugin configuration.
   *
   * @return $this
   */
  public function addSelectionPlugin(array $configuration);

  /**
   * Removes a selection plugin from this profile.
   *
   * @param string $instance_id
   *  The ID of the selection plugin to remove.
   *
   * @return $this
   */
  public function removeSelectionPlugin($instance_id);

  /**
   * Sets the configuration for a selection plugin instance.
   *
   * @param string $instance_id
   *   The ID of the selection plugin to set the configuration for.
   * @param array $configuration
   *   The selection plugin configuration to set.
   *
   * @return $this
   */
  public function setSelectionPluginConfig($instance_id, array $configuration);

}
