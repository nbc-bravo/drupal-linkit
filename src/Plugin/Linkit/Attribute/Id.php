<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Id.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributePluginBase;

/**
 * Id attribute plugin.
 *
 * @AttributePlugin(
 *   id = "id",
 *   label = @Translation("Id"),
 *   description = @Translation("Basic input field for the id attribute."),
 * )
 */
class Id extends AttributePluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'textfield',
      '#title' => t('Id'),
      '#maxlength' => 255,
      '#size' => 40,
    ];
  }

}
