<?php

namespace Drupal\linkit\Tests;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterPluginCollection;

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
  public static $modules = ['node', 'user'];

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
  }

  /**
   * Tests the linkit filter.
   */
  public function testFilter() {
    // Create a content type.
    $this->drupalCreateContentType(['type' => 'test', 'name' => 'Test']);

    $node = $this->drupalCreateNode([
      'type' => 'test',
      'title' => $this->randomGenerator->string(),
    ]);

    $account = $this->drupalCreateUser();

    // Don't automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 0]]);

    // Test with a node.
    $input = '<a data-entity-type="node" data-entity-uuid="' . $node->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="node" data-entity-uuid="' . $node->uuid() . '" href="' . $node->toUrl()->toString() . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input)->getProcessedText());

    // Test with a user.
    $input = '<a data-entity-type="user" data-entity-uuid="' . $account->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="user" data-entity-uuid="' . $account->uuid() . '" href="' . $account->toUrl()->toString() . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input)->getProcessedText());

    // Automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);

    // Test with a node.
    $input = '<a data-entity-type="node" data-entity-uuid="' . $node->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="node" data-entity-uuid="' . $node->uuid() . '" href="' . $node->toUrl()->toString() . '" title="' . Html::decodeEntities($node->label()) . '">Link text</a>';
    $this->assertIdentical($expected, $this->process($input)->getProcessedText());

    // Test with a user.
    $input = '<a data-entity-type="user" data-entity-uuid="' . $account->uuid() . '">Link text</a>';
    $expected = '<a data-entity-type="user" data-entity-uuid="' . $account->uuid() . '" href="' . $account->toUrl()->toString() . '" title="' . Html::decodeEntities($account->label()) . '">Link text</a>';
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
