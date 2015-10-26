<?php

/**
 * @file
 * Contains \Drupal\linkit\Tests\MatcherCrudTest.
 */

namespace Drupal\linkit\Tests;

/**
 * Tests adding, listing, updating and deleting matchers on a profile.
 *
 * @group linkit
 */
class MatcherCrudTest extends LinkitTestBase {

  /**
   * Modules to enable.
   *
   * Enable block module to get the local_actions_block to work.
   *
   * @var array
   */
  public static $modules = array('user');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the overview page.
   */
  function testMatcherOverview() {
    $profile = $this->createProfile();

    $this->drupalGet(\Drupal::url('entity.linkit_profile.matchers', [
      'linkit_profile' => $profile->id(),
    ]));
    $this->assertText('No matchers added.');

    $this->assertLinkByHref(\Drupal::url('linkit.matcher.add', [
      'linkit_profile' => $profile->id(),
    ]));
  }

  /**
   * Test adding a matcher to a profile.
   */
  function testAddMatcher() {
    $profile = $this->createProfile();
    $this->drupalGet(\Drupal::url('linkit.matcher.add', [
      'linkit_profile' => $profile->id(),
    ]));

    // User matcher is the only matcher that will be able as the user module is
    // required by "Drupal" and will always be enabled.
    $this->assertEqual(1, count($this->xpath('//table/tbody/tr')), 'User matcher is available.');

    $edit = array();
    $edit['plugins[entity:user]'] = 'entity:user';
    $this->drupalPostForm(NULL, $edit, t('Add matchers'));
//    $this->assertUrl(\Drupal::url('entity.linkit_profile.matchers', ['linkit_profile' => $profile->id()]));
//
//    $this->clickLink('Add matcher');
//    $this->assertText('No matchers available.');
//    $this->clickLink('Cancel');
//    $this->assertUrl(\Drupal::url('entity.linkit_profile.matchers', ['linkit_profile' => $profile->id()]));
  }

}
