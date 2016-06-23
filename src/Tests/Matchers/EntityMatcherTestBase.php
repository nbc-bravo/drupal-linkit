<?php

namespace Drupal\linkit\Tests\Matchers;

use Drupal\linkit\Tests\LinkitTestBase;

/**
 * Base class for matcher tests.
 *
 * Adds some additional matcher specific assertions and helper functions.
 */
abstract class EntityMatcherTestBase extends LinkitTestBase {

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
    $this->drupalLogin($this->adminUser);
    $this->manager = $this->container->get('plugin.manager.linkit.matcher');
  }

  /**
   * Assert that paths are formatted as an URI with the entity: scheme.
   *
   * @param string $entity_type
   *   The entity_type.
   * @param array $matches
   *   An array of matches.
   */
  public function assertResultUri($entity_type, $matches) {
    foreach ($matches as $match) {
      $this->assertTrue(preg_match("/^entity:" . $entity_type . "\\/\\w+$/i", $match['path']), 'Result URI correct formatted.');
    }
  }

}
