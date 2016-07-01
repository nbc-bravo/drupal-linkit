<?php

namespace Drupal\Tests\linkit\Kernel\Matchers;

use Drupal\file\Entity\File;
use Drupal\Tests\linkit\Kernel\LinkitKernelTestBase;

/**
 * Tests file matcher.
 *
 * @group linkit
 */
class FileMatcherTest extends LinkitKernelTestBase {

  use AssertResultUriTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['file_test', 'file'];

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

    $this->installEntitySchema('file');
    $this->installSchema('system', ['key_value_expire']);
    $this->installSchema('file', array('file_usage'));

    $this->manager = $this->container->get('plugin.manager.linkit.matcher');

    // Linkit doesn't case about the actual resource, only the entity.
    foreach (['gif', 'jpg', 'png'] as $ext) {
      $file = File::create([
        'uid' => 1,
        'filename' => 'image-test.' . $ext,
        'uri' => 'public://image-test.' . $ext,
        'filemime' => 'text/plain',
        'status' => FILE_STATUS_PERMANENT,
      ]);
      $file->save();
    }
  }

  /**
   * Tests the paths for results on a file matcher.
   */
  public function testMatcherResultsPath() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', []);
    $matches = $plugin->getMatches('image-test');
    $this->assertTrue(count($matches), 'Got matches');
    $this->assertResultUri('file', $matches);
  }

  /**
   * Tests file matcher.
   */
  public function testFileMatcherWithDefaultConfiguration() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', []);
    $matches = $plugin->getMatches('image-test');
    $this->assertEquals(3, count($matches), 'Correct number of matches.');
  }

  /**
   * Tests file matcher with extension filer.
   */
  public function testFileMatcherWithExtensionFiler() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', [
      'settings' => [
        'file_extensions' => 'png',
      ],
    ]);

    $matches = $plugin->getMatches('image-test');
    $this->assertEquals(1, count($matches), 'Correct number of matches with single file extension filter.');

    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', [
      'settings' => [
        'file_extensions' => 'png jpg',
      ],
    ]);

    $matches = $plugin->getMatches('image-test');
    $this->assertEquals(2, count($matches), 'Correct number of matches with multiple file extension filter.');
  }

}