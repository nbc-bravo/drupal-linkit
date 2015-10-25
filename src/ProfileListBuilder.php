<?php

/**
 * @file
 * Contains \Drupal\linkit\ProfileListBuilder.
 */

namespace Drupal\linkit;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of profile entities.
 *
 * @see \Drupal\linkit\Entity\Profile
 */
class ProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
      $header['title'] = t('Profile');
      $header['description'] = array(
          'data' => t('Description'),
          'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\linkit\ProfileInterface $linkitProfile */
    $linkitProfile = $entity;
    $row['label'] = $linkitProfile->label();
    $row['description']['data'] = ['#markup' => $linkitProfile->getDescription()];
    return $row + parent::buildRow($entity);
  }

}
