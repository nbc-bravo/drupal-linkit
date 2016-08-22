<?php

namespace Drupal\linkit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * A plugin manager for the substitution plugins.
 */
class SubstitutionManager extends DefaultPluginManager implements SubstitutionManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Linkit/Substitution', $namespaces, $module_handler, 'Drupal\linkit\SubstitutionInterface', 'Drupal\linkit\Annotation\Substitution');
    $this->alterInfo('linkit_substitution');
    $this->setCacheBackend($cache_backend, 'linkit_substitution');
  }

  /**
   * {@inheritdoc}
   */
  public function filterPluginDefinitions($definitions, $entity_type_id) {
    return array_filter($definitions, function($definition) use ($entity_type_id) {
      return empty($definition['entity_types']) || in_array($entity_type_id, $definition['entity_types']);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicablePluginsOptionList($entity_type_id) {
    $options = [];
    foreach ($this->filterPluginDefinitions($this->getDefinitions(), $entity_type_id) as $id => $definition) {
      $options[$id] = $definition['label'];
    }
    return $options;
  }

}
