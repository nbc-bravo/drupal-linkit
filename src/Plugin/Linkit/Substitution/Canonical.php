<?php

namespace Drupal\linkit\Plugin\Linkit\Substitution;

use Drupal\Core\Entity\EntityInterface;
use Drupal\linkit\SubstitutionInterface;
use Drupal\views\Plugin\views\PluginBase;

/**
 * A substitution plugin for the canonical URL of an entity.
 *
 * @Substitution(
 *   id = "canonical",
 *   label = @Translation("Canonical URL"),
 * )
 */
class Canonical extends PluginBase implements SubstitutionInterface {

  /**
   * {@inheritdoc}
   */
  public function getUrl(EntityInterface $entity) {
    return $entity->toUrl('canonical')->toString(TRUE);
  }

}
