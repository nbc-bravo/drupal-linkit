<?php

/**
 * @file
 * Contains \Drupal\linkit\SelectionPluginManager.
 */

namespace Drupal\linkit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages selection plugins.
 */
class SelectionPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Linkit/Selection', $namespaces, $module_handler, 'Drupal\linkit\SelectionPluginInterface', 'Drupal\linkit\Annotation\SelectionPlugin');

    $this->alterInfo('linkit_selection');
    $this->setCacheBackend($cache_backend, 'linkit_selection_plugins');
  }

}
