<?php

/**
 * @file
 * Contains \Drupal\linkit\Annotation\SelectionPlugin.
 */

namespace Drupal\linkit\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a selection annotation object.
 *
 * Plugin Namespace: Plugin\Linkit\Selection
 *
 * @see \Drupal\linkit\SelectionPluginInterface
 * @see \Drupal\linkit\SelectionPluginBase
 * @see \Drupal\linkit\SelectionPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class SelectionPlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the selection plugin.
   *
   * The string should be wrapped in a @Translation().
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the selection plugin.
   *
   * This will be shown when adding or configuring a profile.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

  /**
   * The entity type that is managed by this plugin.
   *
   * @var string
   */
  public $entity_type;

}
