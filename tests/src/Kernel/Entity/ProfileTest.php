<?php

namespace Drupal\Tests\linkit\Kernel\Entity;

use Drupal\Tests\linkit\Kernel\LinkitKernelTestBase;
use Drupal\Tests\linkit\ProfileCreationTrait;

/**
 * Tests the Profile entity.
 *
 * @coversDefaultClass \Drupal\linkit\Entity\Profile
 *
 * @group linkit
 */
class ProfileTest extends LinkitKernelTestBase {

  use ProfileCreationTrait;

  /**
   * Test the profile description.
   *
   * @covers ::getDescription
   * @covers ::setDescription
   */
  public function testDescription() {
    $profile = $this->createProfile(['description' => 'foo']);
    $this->assertEquals('foo', $profile->getDescription());
    $profile->setDescription('bar');
    $this->assertEquals('bar', $profile->getDescription());
  }

}
