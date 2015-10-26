<?php

/**
 * @file
 * Contains \Drupal\linkit\Tests\AttributeCrudTest.
 */

namespace Drupal\linkit\Tests;

/**
 * Tests adding, listing and deleting attributes on a profile.
 *
 * @group linkit
 */
class AttributeCrudTest extends LinkitTestBase {

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
    $this->linkitProfile = $this->createProfile();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test list, add, and remove attributes.
   */
  function testAttributes() {
    $this->drupalGet('admin/config/content/linkit/manage/' . $this->linkitProfile->id() . '/attributes');
    $this->assertText('No attributes added.');

    $this->clickLink('Add attribute');
    $this->assertEqual(6, count($this->xpath('//table/tbody/tr')), 'All attributes are available.');

    $edit = array();
    $edit['plugins[target]'] = 'target';
    $edit['plugins[relationship]'] = 'relationship';
    $edit['plugins[class]'] = 'class';
    $edit['plugins[title]'] = 'title';
    $edit['plugins[accesskey]'] = 'accesskey';
    $edit['plugins[id]'] = 'id';
    $this->drupalPostForm(NULL, $edit, t('Add attributes'));
    $this->assertUrl(\Drupal::url('entity.linkit_profile.attributes', ['linkit_profile' => $this->linkitProfile->id()]));
    $this->assertEqual(6, count($this->xpath('//table/tbody/tr')), 'All attributes are added.');

    $this->clickLink('Add attribute');
    $this->assertText('No attribute available.');

    $this->clickLink('Cancel');
    $this->assertUrl(\Drupal::url('entity.linkit_profile.attributes', ['linkit_profile' => $this->linkitProfile->id()]));

    $this->drupalGet(\Drupal::url('linkit.attribute.remove', [
      'linkit_profile' => $this->linkitProfile->id(),
      'plugin_id' => 'accesskey',
    ]));
    $this->clickLink('Cancel');
    $this->assertUrl(\Drupal::url('entity.linkit_profile.attributes', ['linkit_profile' => $this->linkitProfile->id()]));

    $this->drupalGet(\Drupal::url('linkit.attribute.remove', [
      'linkit_profile' => $this->linkitProfile->id(),
      'plugin_id' => 'accesskey',
    ]));
    $this->drupalPostForm(NULL, [], t('Remove'));
    $this->assertRaw(t('The attribute %plugin has been removed.', array('%plugin' => 'Accesskey')));
    $this->assertUrl(\Drupal::url('entity.linkit_profile.attributes', ['linkit_profile' => $this->linkitProfile->id()]));
    $this->assertEqual(5, count($this->xpath('//table/tbody/tr')), 'One attributes are removed.');

    $this->assertFalse($this->linkitProfile->getAttributes()->has('accesskey'), 'The atteibute is removed from the profile');
  }

}
