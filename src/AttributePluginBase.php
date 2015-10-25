<?php

/**
 * @file
 * Contains \Drupal\linkit\AttributePluginBase.
 */

namespace Drupal\linkit;

use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for attribute plugins.
 *
 * @see \Drupal\linkit\Annotation\AttributePlugin
 * @see \Drupal\linkit\AttributePluginBase
 * @see \Drupal\linkit\AttributePluginManager
 * @see plugin_api
 */
abstract class AttributePluginBase extends PluginBase implements AttributePluginInterface {

  /**
   * The weight of the attribute plugin compared to others in an attribute
   * plugin collection.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'id' => $this->getPluginId(),
      'weight' => $this->weight,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (isset($configuration['weight'])) {
      $this->weight = (int) $configuration['weight'];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'weight' => $this->pluginDefinition['weight'] ?: 0,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

}
