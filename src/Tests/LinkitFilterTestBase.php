<?php

namespace Drupal\linkit\Tests;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\filter\FilterPluginCollection;

/**
 * Provides a base class for testing the Linkit filter.
 */
abstract class LinkitFilterTestBase extends LinkitTestBase {

  /**
   * The linkit filter.
   *
   * @var \Drupal\filter\Plugin\FilterInterface
   */
  protected $filter;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filter = $bag->get('linkit');
  }

  /**
   * Asserts that Linkit filter correctly processes the content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to check.
   */
  protected function assertLinkitFilter(EntityInterface $entity, $langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    $input = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '" href="' . $entity->toUrl()->toString() . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input, $langcode)->getProcessedText());
  }

  /**
   * Asserts that Linkit filter correctly processes the content titles.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to check.
   */
  protected function assertLinkitFilterWithTitle(EntityInterface $entity, $langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    $input = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '" href="' . $entity->toUrl()->toString() . '" title="' . Html::decodeEntities($entity->label()) . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input, $langcode)->getProcessedText());
  }

  /**
   * Test helper method that wraps the filter process method.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::process
   */
  protected function process($input, $langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    return $this->filter->process($input, $langcode);
  }

}
