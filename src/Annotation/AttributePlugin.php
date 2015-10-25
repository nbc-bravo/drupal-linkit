<?php

/**
 * @file
 * Contains \Drupal\linkit\Annotation\AttributePlugin.
 */

namespace Drupal\linkit\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an attribute annotation object.
 *
 * Plugin Namespace: Plugin\Linkit\Attribute
 *
 * For a working example, see \Drupal\linkit\Plugin\Linkit\Attribute\Title
 *
 * @see \Drupal\linkit\AttributePluginInterface
 * @see \Drupal\linkit\AttributePluginBase
 * @see \Drupal\linkit\AttributePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class AttributePlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the attribute plugin.
   *
   * The string should be wrapped in a @Translation().
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The real HTML attribute name for this attribute plugin.
   *
   * @var string
   */
  public $html_attribute;
  // @TODO: Fix this!

  /**
   * A brief description of the attribute plugin.
   *
   * This will be shown when adding or configuring a profile.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

  /**
   * A default weight for the attribute plugin.
   *
   * @var int (optional)
   */
  public $weight = 0;

}
