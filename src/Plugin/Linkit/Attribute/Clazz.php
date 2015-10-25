<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Clazz.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributePluginBase;

/**
 * Class attribute plugin.
 *
 * @AttributePlugin(
 *   id = "class",
 *   label = @Translation("Class"),
 *   description = @Translation("Basic input field for the class attribute."),
 * )
 */
class Clazz extends AttributePluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'textfield',
      '#title' => t('Class'),
      '#maxlength' => 255,
      '#size' => 40,
    ];
  }

}
