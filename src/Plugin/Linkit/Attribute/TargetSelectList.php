<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\TargetSelectList.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributeBase;

/**
 * Target attribute.
 *
 * @Attribute(
 *   id = "target_select",
 *   label = @Translation("Target (select list)"),
 *   html_name = "target",
 *   description = @Translation("Select list with predefined targets."),
 * )
 */
class TargetSelectList extends AttributeBase {

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
