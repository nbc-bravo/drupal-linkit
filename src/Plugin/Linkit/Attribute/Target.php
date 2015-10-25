<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\Target.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributePluginBase;

/**
 * Target attribute plugin.
 *
 * @AttributePlugin(
 *   id = "target",
 *   label = @Translation("Target"),
 *   description = @Translation("Basic input field for the target attribute."),
 * )
 */
class Target extends AttributePluginBase {

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
    ];
  }

}
