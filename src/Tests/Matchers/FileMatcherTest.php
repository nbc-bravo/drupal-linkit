<?php

namespace Drupal\linkit\Tests\Matchers;

use Drupal\file\Entity\File;
use Drupal\linkit\Tests\LinkitTestBase;

/**
 * Tests file matcher.
 *
 * @group linkit
 */
class FileMatcherTest extends LinkitTestBase {

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
   * List of images uploaded to test the matcher.
   *
   * @var array
   */
  protected $images = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
    $this->manager = $this->container->get('plugin.manager.linkit.matcher');

    $image_files = $this->drupalGetTestFiles('image');

    foreach ($image_files as $file) {
      $image = File::create((array) $file);
      $this->assertTrue(is_file($image->getFileUri()), "The image file we're going to upload exists.");

      // Upload with replace to guarantee there's something there.
      $edit = array(
        'file_test_replace' => FILE_EXISTS_REPLACE,
        'files[file_test_upload]' => drupal_realpath($image->getFileUri()),
      );
      $this->drupalPostForm('file-test/upload', $edit, t('Submit'));
      $this->assertResponse(200, 'Received a 200 response for posted test file.');

      $this->images[$file->name] = $image;
    }
  }

  /**
   * Tests file matcher.
   */
  public function testFileMatcherWithDefaultConfiguration() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', [
      'settings' => [
        'status' => 0,
      ],
    ]);
    $matches = $plugin->getMatches('image-test');
    $this->assertEqual(6, count($matches), 'Correct number of matches');
  }

  /**
   * Tests file matcher with extension filer.
   */
  public function testFileMatcherWithExtensionFiler() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:file', [
      'settings' => [
        'extensions' => 'png',
        'status' => 0,
      ],
    ]);

    $matches = $plugin->getMatches('image-test');
    $this->assertEqual(1, count($matches), 'Correct number of matches with extension filter');
  }

}
