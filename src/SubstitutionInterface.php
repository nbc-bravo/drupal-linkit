<?php

namespace Drupal\linkit;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for substitution plugins.
 */
interface SubstitutionInterface extends PluginInspectionInterface {

  /**
   * Get the URL associated with a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get a URL for.
   *
   * @return \Drupal\Core\GeneratedUrl
   *   A url to replace.
   */
  public function getUrl(EntityInterface $entity);

}
