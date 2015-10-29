<?php

/**
 * @file
 * Contains \Drupal\linkit\Tests\AttributeCrudTest.
 */

namespace Drupal\linkit\Tests;
use Drupal\linkit\Entity\Profile;


/**
 * Tests adding, listing and deleting attributes on a profile.
 *
 * @group linkit
 */
class AttributeCrudTest extends LinkitTestBase {

  /**
   * The attribute manager.
   *
   * @var \Drupal\linkit\AttributeManager
   */
  protected $manager;

  /**
   * The linkit profile.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.linkit.attribute');

    $this->linkitProfile = $this->createProfile();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the overview page.
   */
  function testOverview() {
    $profile = $this->createProfile();

    $this->drupalGet(\Drupal::url('linkit.attributes', [
      'linkit_profile' => $profile->id(),
    ]));
    $this->assertText(t('No attributes added.'));

    $this->assertLinkByHref(\Drupal::url('linkit.attribute.add', [
      'linkit_profile' => $profile->id(),
    ]));
  }

  /**
   * Test adding an attribute to a profile.
   */
  function testAdd() {
    $profile = $this->createProfile();
    $this->drupalGet(\Drupal::url('linkit.attribute.add', [
      'linkit_profile' => $profile->id(),
    ]));

    $this->assertEqual(count($this->manager->getDefinitions()), count($this->xpath('//table/tbody/tr')), 'All attributes are available.');

    $edit = array();
    $edit['plugins[class]'] = 'class';
    $edit['plugins[accesskey]'] = 'accesskey';
    $this->drupalPostForm(NULL, $edit, t('Add attributes'));

    $this->assertUrl(\Drupal::url('linkit.attributes', [
      'linkit_profile' => $profile->id(),
    ]));

    $this->assertEqual(2, count($this->xpath('//table/tbody/tr')), 'Two attributes were added.');
    $this->assertNoText(t('No attributes added.'));
  }

  /**
   * Test delete an attribute from a profile.
   */
  function testDelete() {
    $profile = $this->createProfile();
    $profile->addAttribute(['id' => 'accesskey']);
    $profile->save();

    // Try delete an attribute that is not attached to the profile.
    $this->drupalGet(\Drupal::url('linkit.attribute.delete', [
      'linkit_profile' => $profile->id(),
      'plugin_id' => 'doesntexists'
    ]));
    $this->assertResponse('404');

    // Go to the delete page, but press cancel.
    $this->drupalGet(\Drupal::url('linkit.attribute.delete', [
      'linkit_profile' => $profile->id(),
      'plugin_id' => 'accesskey',
    ]));
    $this->clickLink(t('Cancel'));
    $this->assertUrl(\Drupal::url('linkit.attributes', [
      'linkit_profile' => $profile->id(),
    ]));

    // Delete the attribute from the profile.
    $this->drupalGet(\Drupal::url('linkit.attribute.delete', [
      'linkit_profile' => $profile->id(),
      'plugin_id' => 'accesskey',
    ]));

    $this->drupalPostForm(NULL, [], t('Confirm'));
    $this->assertRaw(t('The attribute %plugin has been deleted.', ['%plugin' => 'Accesskey']));
    $this->assertUrl(\Drupal::url('linkit.attributes', [
      'linkit_profile' => $profile->id(),
    ]));
    $this->assertText(t('No attributes added.'));

    /** @var \Drupal\linkit\Entity\Profile $updated_profile */
    $updated_profile = Profile::load($profile->id());
    $this->assertFalse($updated_profile->getAttributes()->has('accesskey'), 'The attribute is deleted from the profile');
  }

}
