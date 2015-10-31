<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Target.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributeBase;

/**
 * Target attribute.
 *
 * @Attribute(
 *   id = "target",
 *   label = @Translation("Target"),
 *   html_name = "target",
 *   description = @Translation("Basic input field for the target attribute."),
 * )
 */
class Target extends AttributeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'select',
      '#title' => t('Target'),
      '#options' => [
        '' => '',
        '_blank' => t('New window (_blank)'),
        '_top' => t('Top window (_top)'),
        '_self' => t('Same window (_self)'),
        '_parent' => t('Parent window (_parent)')
      ],
      '#default_value' => $default_value,
    ];
  }

}
