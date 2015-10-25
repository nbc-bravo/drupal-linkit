<?php

/**
 * @file
 * Contains \Drupal\linkit\Entity\Profile.
 */

namespace Drupal\linkit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\linkit\AttributeCollection;
use Drupal\linkit\MatcherCollection;
use Drupal\linkit\ProfileInterface;

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
 *     "matchers" = "/admin/config/content/linkit/manage/{linkit_profile}/matchers",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "autocomplete_configuration",
 *     "attributes",
 *     "matchers"
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
   * Configured matchers for this profile.
   *
   * An associative array of matchers assigned to the profile, keyed by the
   * matcher ID of each matcher and using the properties:
   * - id: The plugin ID of the matchers instance.
   * - status: (optional) A Boolean indicating whether the matchers is enabled
   *   in the profile. Defaults to FALSE.
   * - weight: (optional) The weight of the matchers in the profile.
   *   Defaults to 0.
   *
   * @var array
   */
  protected $matchers = [];

  /**
   * Holds the collection of matchers that are attached to this profile.
   *
   * @var \Drupal\linkit\MatcherCollection
   */
  protected $matcherCollection;

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
  public function getMatcher($matcher_id) {
    return $this->getMatchers()->get($matcher_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getMatchers() {
    if (!$this->matcherCollection) {
      $this->matcherCollection = new MatcherCollection($this->getMatcherManager(), $this->matchers);
      $this->matcherCollection->sort();
    }
    return $this->matcherCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addMatcher(array $configuration) {
    $this->getMatchers()->addInstanceId($configuration['id'], $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeMatcher($matcher_id) {
    unset($this->matchers[$matcher_id]);
    $this->getMatchers()->removeInstanceId($matcher_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMatcherConfig($matcher_id, array $configuration) {
    $this->matchers[$matcher_id] = $configuration;
    $this->getMatchers()->setInstanceConfiguration($matcher_id, $configuration);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array(
      'attributes' => $this->getAttributes(),
      'matchers' => $this->getMatchers(),
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
   * Returns the matcher manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The matcher manager.
   */
  protected function getMatcherManager() {
    return \Drupal::service('plugin.manager.linkit.matcher');
  }

}
