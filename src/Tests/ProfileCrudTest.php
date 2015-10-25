<?php

/**
 * @file
 * Contains \Drupal\linkit\Tests\ProfileCreationTest.
 */

namespace Drupal\linkit\Tests;
use Drupal\Component\Utility\Unicode;

/**
 * Tests creating, loading and deleting profiles.
 *
 * @group linkit
 */
class ProfileCrudTest extends LinkitTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Creates profile.
   */
  function testProfileCreation() {
    $this->drupalGet('admin/config/content/linkit/add');
    $this->assertResponse(200);
    // Create a profile.
    $edit = [];
    $edit['label'] = Unicode::strtolower($this->randomMachineName());
    $edit['id'] = Unicode::strtolower($this->randomMachineName());
    $edit['description'] = $this->randomMachineName(16);
    $this->drupalPostForm('admin/config/content/linkit/add', $edit, t('Save and manage matchers'));

    $this->assertRaw(t('Created new profile %label.', array('%label' => $edit['label'])));
    $this->drupalGet('admin/config/content/linkit');
    $this->assertText($edit['label'], 'Profile exists in the profile collection.');
  }

  /**
   * Updates a profile.
   */
  function testProfileUpdate() {
    $profile = $this->createProfile();
    $this->drupalGet('admin/config/content/linkit/manage/' . $profile->id());
    $this->assertResponse(200);

    $id_field = $this->xpath('.//input[not(@disabled) and @name="id"]');

    $this->assertTrue(empty($id_field), 'Machine name field is disabled.');
    $this->assertLinkByHref('admin/config/content/linkit/manage/' . $profile->id() . '/delete');

    $edit = [];
    $edit['label'] = $this->randomMachineName();
    $edit['description'] = $this->randomMachineName(16);
    $this->drupalPostForm('admin/config/content/linkit/manage/' . $profile->id(), $edit, t('Update profile'));

    $this->assertRaw(t('Updated profile %label.', array('%label' => $edit['label'])));
    $this->drupalGet('admin/config/content/linkit');
    $this->assertText($edit['label'], 'Updated profile exists in the profile collection.');
  }

  /**
   * Delete a profile.
   */
  function testProfileDelete() {
    /** @var \Drupal\linkit\ProfileInterface $profile */
    $profile = $this->createProfile();

    $this->drupalPostForm('admin/config/content/linkit/manage/' . $profile->id() . '/delete', [], t('Delete'));

    $this->assertRaw(t('The linkit profile %label has been deleted.', array('%label' => $profile->label())));
    $this->drupalGet('admin/config/content/linkit');
    $this->assertNoText($profile->label(), 'Deleted profile does not exists in the profile collection.');
  }

}
