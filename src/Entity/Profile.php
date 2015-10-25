<?php

/**
 * @file
 * Contains \Drupal\linkit\Entity\Profile.
 */

namespace Drupal\linkit\Entity;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\linkit\AttributePluginCollection;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\SelectionPluginCollection;

/**
 * Defines the linkit profile entity.
 *
 * @ConfigEntityType(
 *   id = "linkit_profile",
 *   label = @Translation("Linkit profile"),
 *   handlers = {
 *     "list_builder" = "Drupal\linkit\ProfileListBuilder",
 *     "form" = {
 *       "add" = "Drupal\linkit\Form\Profile\AddForm",
 *       "edit" = "Drupal\linkit\Form\Profile\EditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer linkit profiles",
 *   config_prefix = "linkit_profile",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "collection" = "/admin/config/content/linkit",
 *     "edit-form" = "/admin/config/content/linkit/manage/{linkit_profile}",
 *     "delete-form" = "/admin/config/content/linkit/manage/{linkit_profile}/delete",
 *     "attribute-plugins" = "/admin/config/content/linkit/manage/{linkit_profile}/attribute-plugins",
 *     "selection-plugins" = "/admin/config/content/linkit/manage/{linkit_profile}/selection-plugins",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "autocomplete_configuration",
 *     "attributePlugins",
 *     "selectionPlugins"
 *   }
 * )
 */
class Profile extends ConfigEntityBase implements ProfileInterface, EntityWithPluginCollectionInterface {

  /**
   * The ID of this profile.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of this profile.
   *
   * @var string
   */
  protected $label;

  /**
   * Description of this profile.
   *
   * @var string
   */
  protected $description;

  /**
   * Autocomplete settings for this profile.
   *
   * An associative array using the properties:
   * - character_limit: The minimum number of chars to trigger the first search.
   * - key_press_delay: The time in milliseconds between last keypress and a
   *   new search.
   * - timeout: The timeout in milliseconds for searches.
   *
   * @var array
   */
  protected $autocomplete_configuration = [
    'character_limit' => 3,
    'key_press_delay' => 350,
    'timeout' => 10000,
  ];

  /**
   * Configured attribute plugins for this profile.
   *
   * An associative array of attribute plugins assigned to the profile, keyed by
   * the instance ID of each attribute plugin and using the properties:
   * - id: The plugin ID of the attribute plugin instance.
   * - status: (optional) A Boolean indicating whether the attribute plugin is
   *   enabled in the profile. Defaults to FALSE.
   * - weight: (optional) The weight of the attribute plugin in the profile.
   *   Defaults to 0.
   *
   * @var array
   */
  protected $attributePlugins = [];

  /**
   * Holds the collection of attribute plugins that are attached to this
   * profile.
   *
   * @var \Drupal\linkit\AttributePluginCollection
   */
  protected $attributePluginCollection;

  /**
   * Configured selection plugins for this profile.
   *
   * An associative array of selection plugins assigned to the profile, keyed by
   * the instance ID of each selection plugin and using the properties:
   * - id: The plugin ID of the selection plugin instance.
   * - status: (optional) A Boolean indicating whether the selection plugin is
   *   enabled in the profile. Defaults to FALSE.
   * - weight: (optional) The weight of the selection plugin in the profile.
   *   Defaults to 0.
   *
   * @var array
   */
  protected $selectionPlugins = [];

  /**
   * Holds the collection of selection plugins that are attached to this
   * profile.
   *
   * @var \Drupal\linkit\SelectionPluginCollection
   */
  protected $selectionPluginCollection;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description');
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', trim($description));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutocompleteConfiguration() {
    return $this->get('autocomplete_configuration');
  }

  /**
   * {@inheritdoc}
   */
  public function setAutocompleteConfiguration(array $autocomplete_configuration) {
    $this->set('autocomplete_configuration', $autocomplete_configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributePlugin($attribute_plugin_id) {
    return $this->getAttributePlugins()->get($attribute_plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributePlugins() {
    if (!$this->attributePluginCollection) {
      $this->attributePluginCollection = new AttributePluginCollection($this->getAttributePluginManager(), $this->attributePlugins);
      $this->attributePluginCollection->sort();
    }
    return $this->attributePluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addAttributePlugin(array $configuration) {
    $this->getAttributePlugins()->addInstanceId($configuration['id'], $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAttributePlugin($instance_id) {
    unset($this->attributePlugins[$instance_id]);
    $this->getAttributePlugins()->removeInstanceId($instance_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributePluginConfig($instance_id, array $configuration) {
    $this->attributePlugins[$instance_id] = $configuration;
    $this->getAttributePlugins()->setInstanceConfiguration($instance_id, $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionPlugin($selection_plugin_id) {
    return $this->getSelectionPlugins()->get($selection_plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionPlugins() {
    if (!$this->selectionPluginCollection) {
      $this->selectionPluginCollection = new SelectionPluginCollection($this->getSelectionPluginManager(), $this->selectionPlugins);
      $this->selectionPluginCollection->sort();
    }
    return $this->selectionPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectionPlugin(array $configuration) {
    $this->getSelectionPlugins()->addInstanceId($configuration['id'], $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelectionPlugin($instance_id) {
    unset($this->selectionPlugins[$instance_id]);
    $this->getSelectionPlugins()->removeInstanceId($instance_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSelectionPluginConfig($instance_id, array $configuration) {
    $this->selectionPlugins[$instance_id] = $configuration;
    $this->getSelectionPlugins()->setInstanceConfiguration($instance_id, $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array(
      'attributePlugins' => $this->getAttributePlugins(),
      'selectionPlugins' => $this->getSelectionPlugins(),
    );
  }

  /**
   * Returns the attribute plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The attribute plugin manager.
   */
  protected function getAttributePluginManager() {
    return \Drupal::service('plugin.manager.linkit.attribute_plugin');
  }

  /**
   * Returns the selection plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The attribute plugin manager.
   */
  protected function getSelectionPluginManager() {
    return \Drupal::service('plugin.manager.linkit.selection_plugin');
  }

}
