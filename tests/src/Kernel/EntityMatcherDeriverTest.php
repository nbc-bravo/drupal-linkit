<?php

namespace Drupal\Tests\linkit\Kernel;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Routing\Route;

/**
 * Tests the entity matcher deriver.
 *
 * @group linkit
 */
class EntityMatcherDeriverTest extends LinkitKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'block_content', 'node', 'field'];

  /**
   * The matcher manager.
   *
   * @var \Drupal\linkit\MatcherManager
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['block_content']);
    $this->installEntitySchema('block_content');

    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);

    $this->manager = $this->container->get('plugin.manager.linkit.matcher');
  }

  /**
   * Tests the deriver.
   */
  public function testDeriver() {
    $definition = $this->manager->getDefinition('entity:block_content', FALSE);
    $this->assertNull($definition);
    $definition = $this->manager->getDefinition('entity:node', FALSE);
    $this->assertNotNull($definition);
  }

}
