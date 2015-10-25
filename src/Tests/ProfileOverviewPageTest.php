<?php

/**
 * @file
 * Contains \Drupal\linkit\Tests\LinkitAdminTest.
 */

namespace Drupal\linkit\Tests;

/**
 * Tests profile overview page functionality.
 *
 * @group linkit
 */
class ProfileOverviewPageTest extends LinkitTestBase {

  /**
   * A user with the 'administer linkit profiles' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A user without the 'administer linkit profiles' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $baseUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests profile collection page.
   */
  function testProfileOverviewPage() {
    // Verify that the profile collection page is not accessible for regular
    // users.
    $this->drupalLogin($this->baseUser);
    $this->drupalGet('admin/config/content/linkit');
    $this->assertResponse(403);
    $this->drupalLogout();

    // Verify that the profile collection page is accessible for regular users.
    $this->drupalLogin($this->adminUser);

    $profiles = [];
    $profiles[] = $this->createProfile();
    $profiles[] = $this->createProfile();

    $this->drupalGet('admin/config/content/linkit');
    $this->assertResponse(200);

    // Assert that the 'Add profile' action exists.
    $this->assertLinkByHref('admin/config/content/linkit/add');

    foreach ($profiles as $profile) {
      $this->assertLinkByHref('admin/config/content/linkit/manage/' . $profile->id());
      $this->assertLinkByHref('admin/config/content/linkit/manage/' . $profile->id() . '/delete');
    }
  }

}
