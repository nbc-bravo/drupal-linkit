<?php

namespace Drupal\linkit\Tests;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\filter\FilterPluginCollection;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the Linkit filter.
 *
 * @group linkit
 */
class LinkitFilterTest extends LinkitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'user', 'language'];

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
    $bag = new FilterPluginCollection($manager, array());
    $this->filter = $bag->get('linkit');

    // Add Swedish, Danish and Finnish.
    ConfigurableLanguage::createFromLangcode('sv')->save();
    ConfigurableLanguage::createFromLangcode('da')->save();
    ConfigurableLanguage::createFromLangcode('fi')->save();
  }

  /**
   * Tests the linkit filter for nodes.
   */
  public function testFilterNode() {
    // Create a content type.
    $this->drupalCreateContentType(['type' => 'test']);

    $node = $this->drupalCreateNode(['type' => 'test']);
    $node_sv = $this->drupalCreateNode(['type' => 'test', 'langcode' => 'sv']);
    $this->assertTrue($node_sv->language()->getId() == 'sv', 'Node created as Swedish.');
    $node_da = $this->drupalCreateNode(['type' => 'test', 'langcode' => 'da']);
    $this->assertTrue($node_da->language()->getId() == 'da', 'Node created as Danish.');
    $node_fi = $this->drupalCreateNode(['type' => 'test', 'langcode' => 'fi']);
    $this->assertTrue($node_fi->language()->getId() == 'fi', 'Node created as Finnish.');

    // Don't automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 0]]);
    $this->assertLinkitFilter($node);
    $this->assertLinkitFilter($node_sv);
    $this->assertLinkitFilter($node_da);
    $this->assertLinkitFilter($node_fi);

    // Automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);
    $this->assertLinkitFilterWithTitle($node);
    $this->assertLinkitFilterWithTitle($node_sv);
    $this->assertLinkitFilterWithTitle($node_da);
    $this->assertLinkitFilterWithTitle($node_fi);
  }

  /**
   * Tests the linkit filter for users.
   */
  public function testFilterUser() {
    $account = $this->drupalCreateUser();

    // Don't automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 0]]);
    $this->assertLinkitFilter($account);

    // Automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);
    $this->assertLinkitFilterWithTitle($account);
  }

  /**
   * Asserts that Linkit filter correctly processes the content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to check.
   */
  private function assertLinkitFilter(EntityInterface $entity) {
    $input = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '" href="' . $entity->toUrl()->toString() . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input)->getProcessedText());
  }

  /**
   * Asserts that Linkit filter correctly processes the content titles.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to check.
   */
  private function assertLinkitFilterWithTitle(EntityInterface $entity) {
    $input = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="' . $entity->getEntityTypeId() . '" data-entity-uuid="' . $entity->uuid() . '" href="' . $entity->toUrl()->toString() . '" title="' . Html::decodeEntities($entity->label()) . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input)->getProcessedText());
  }

  /**
   * Test helper method that wraps the filter process method.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::process
   */
  private function process($input, $langcode = 'und') {
    return $this->filter->process($input, $langcode);
  }

}
