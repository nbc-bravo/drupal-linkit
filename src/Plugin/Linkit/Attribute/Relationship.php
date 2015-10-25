<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Relationship.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributePluginBase;

/**
 * Relationship attribute plugin.
 *
 * @AttributePlugin(
 *   id = "relationship",
 *   label = @Translation("Relationship"),
 *   description = @Translation("Basic input field for the relationship attribute."),
 * )
 */
class Relationship extends AttributePluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'textfield',
      '#title' => t('Relationship'),
      '#maxlength' => 255,
      '#size' => 40,
    ];
  }

}
