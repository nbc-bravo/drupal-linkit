<?php

namespace Drupal\linkit\Tests;

use Drupal\file\Entity\File;

/**
 * Tests the Linkit filter for file entities.
 *
 * @group linkit
 */
class LinkitFilterFileEntityTest extends LinkitFilterTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['file'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests the linkit filter for file entities.
   */
  public function testFilterFileEntity() {
    $file = File::create(array(
      'uid' => 1,
      'filename' => 'druplicon.txt',
      'uri' => 'public://druplicon.txt',
      'filemime' => 'text/plain',
      'status' => FILE_STATUS_PERMANENT,
    ));
    $file->save();

    // Disable the automatic title attribute.
    $this->filter->setConfiguration(['settings' => ['title' => 0]]);
    $this->assertLinkitFilter($file);

    // Automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);
    $this->assertLinkitFilterWithTitle($file);
  }

}
