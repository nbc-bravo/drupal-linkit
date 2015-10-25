<?php

/**
 * @file
 * Contains \Drupal\linkit\SelectionPluginBase.
 */

namespace Drupal\linkit;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for selection plugins.
 *
 * @see \Drupal\linkit\Annotation\SelectionPlugin
 * @see \Drupal\linkit\SelectionPluginBase
 * @see \Drupal\linkit\SelectionPluginManager
 * @see plugin_api
 */
abstract class SelectionPluginBase extends PluginBase implements SelectionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The weight of the selection plugin compared to others in a selection
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
    return $this->weight = $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
      'weight' => $this->getWeight(),
      'data' => $this->configuration,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration += [
      'data' => [],
      'weight' => '0',
    ];
    $this->configuration = $configuration['data'] + $this->defaultConfiguration();
    $this->weight = $configuration['weight'];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
