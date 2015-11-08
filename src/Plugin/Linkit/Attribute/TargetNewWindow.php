<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Attribute\TargetNewWindow.
 */

namespace Drupal\linkit\Plugin\Linkit\Attribute;

use Drupal\linkit\AttributeBase;

/**
 * Target attribute.
 *
 * @Attribute(
 *   id = "target_new_window",
 *   label = @Translation("Target (only new window)"),
 *   html_name = "target",
 *   description = @Translation("Simple checkbox to allow links to be opened in a new browser window or tab."),
 * )
 */
class TargetNewWindow extends AttributeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFormElement($default_value) {
    return [
      '#type' => 'checkbox',
      '#title' => t('Open in new window'),
      '#default_value' => $default_value,
      '#return_value' => '_blank',
    ];
  }

}
