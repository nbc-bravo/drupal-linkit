<?php

/**
 * @file
 * Contains \Drupal\linkit\Entity\Profile.
 */

namespace Drupal\linkit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\linkit\AttributeCollection;
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
 *     "attributes" = "/admin/config/content/linkit/manage/{linkit_profile}/attributes",
 *     "selection-plugins" = "/admin/config/content/linkit/manage/{linkit_profile}/selection-plugins",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "autocomplete_configuration",
 *     "attributes",
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
   * Configured attribute for this profile.
   *
   * An associative array of attribute assigned to the profile, keyed by the
   * attribute id of each attribute and using the properties:
   * - id: The plugin ID of the attribute instance.
   * - status: (optional) A Boolean indicating whether the attribute is enabled
   *   in the profile. Defaults to FALSE.
   * - weight: (optional) The weight of the attribute in the profile.
   *   Defaults to 0.
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Holds the collection of attributes that are attached to this profile.
   *
   * @var \Drupal\linkit\AttributeCollection
   */
  protected $attributeCollection;

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
  public function getAttribute($attribute_id) {
    return $this->getAttributes()->get($attribute_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributes() {
    if (!$this->attributeCollection) {
      $this->attributeCollection = new AttributeCollection($this->getAttributeManager(), $this->attributes);
      $this->attributeCollection->sort();
    }
    return $this->attributeCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addAttribute(array $configuration) {
    $this->getAttributes()->addInstanceId($configuration['id'], $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAttribute($attribute_id) {
    unset($this->attributes[$attribute_id]);
    $this->getAttributes()->removeInstanceId($attribute_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributeConfig($attribute_id, array $configuration) {
    $this->attributes[$attribute_id] = $configuration;
    $this->getAttributes()->setInstanceConfiguration($attribute_id, $configuration);
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
      'attributes' => $this->getAttributes(),
      'selectionPlugins' => $this->getSelectionPlugins(),
    );
  }

  /**
   * Returns the attribute manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The attribute manager.
   */
  protected function getAttributeManager() {
    return \Drupal::service('plugin.manager.linkit.attribute');
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
