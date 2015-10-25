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
   * Returns a specific attribute.
   *
   * @param string $attribute_id
   *   The attribute ID.
   *
   * @return \Drupal\linkit\AttributeInterface
   *   The attribute object.
   */
  public function getAttribute($attribute_id);

  /**
   * Returns the attributes for this profile.
   *
   * @return \Drupal\linkit\AttributeCollection|\Drupal\linkit\AttributeInterface[]
   *   The attribute collection.
   */
  public function getAttributes();

  /**
   * Adds an attribute to this profile.
   *
   * @param array $configuration
   *   An array of attribute configuration.
   *
   * @return $this
   */
  public function addAttribute(array $configuration);

  /**
   * Removes an attribute from this profile.
   *
   * @param string $attribute_id
   *  The attribute ID.
   *
   * @return $this
   */
  public function removeAttribute($attribute_id);

  /**
   * Sets the configuration for an attribute instance.
   *
   * @param string $attribute_id
   *   The ID of the attribute to set the configuration for.
   * @param array $configuration
   *   The attribute configuration to set.
   *
   * @return $this
   */
  public function setAttributeConfig($attribute_id, array $configuration);

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
