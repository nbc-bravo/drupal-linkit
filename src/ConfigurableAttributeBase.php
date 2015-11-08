<?php

/**
 * @file
 * Contains \Drupal\linkit\ConfigurableAttributeBase.
 */

namespace Drupal\linkit;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base class for configurable attributes.
 *
 * @see plugin_api
 */
abstract class ConfigurableAttributeBase extends AttributeBase implements ConfigurableAttributeInterface {

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
