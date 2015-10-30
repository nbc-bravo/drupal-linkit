<?php

/**
 * @file
 * Contains \Drupal\linkit\AttributeBase.
 */

namespace Drupal\linkit;

use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for attribute plugins.
 *
 * @see \Drupal\linkit\Annotation\Attribute
 * @see \Drupal\linkit\AttributeBase
 * @see \Drupal\linkit\AttributeManager
 * @see plugin_api
 */
abstract class AttributeBase extends PluginBase implements AttributeInterface {

  /**
   * The weight of the attribute compared to others in an attribute collection.
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
  public function getHtmlName() {
    return $this->pluginDefinition['html_name'];
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
