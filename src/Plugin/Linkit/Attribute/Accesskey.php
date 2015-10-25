<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Accesskey.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributePluginBase;

/**
 * Accesskey attribute plugin.
 *
 * @AttributePlugin(
 *   id = "accesskey",
 *   label = @Translation("Accesskey"),
 *   description = @Translation("Basic input field for the accesskey attribute.")
 * )
 */
class Accesskey extends AttributePluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'textfield',
      '#title' => t('Accesskey'),
      '#maxlength' => 255,
      '#size' => 40,
    ];
  }

}
