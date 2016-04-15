<?php

namespace Drupal\linkit\Tests\Controllers;

use Drupal\Core\Url;
use Drupal\linkit\Tests\LinkitTestBase;

/**
 * Tests Linkit controller.
 *
 * @group linkit
 */
class LinkitControllerTest extends LinkitTestBase {

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
   * Tests the profile route title callback.
   */
  public function testProfileTitle() {
    $this->drupalGet(Url::fromRoute('entity.linkit_profile.edit_form', [
      'linkit_profile' => $this->linkitProfile->id(),
    ]));

    $this->assertText('Edit ' . $this->linkitProfile->label() . ' profile');
  }

  /**
   * Tests the matcher route title callback.
   */
  public function testMatcherTitle() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->container->get('plugin.manager.linkit.matcher')->createInstance('configurable_dummy_matcher');
    $matcher_uuid = $this->linkitProfile->addMatcher($plugin->getConfiguration());
    $this->linkitProfile->save();

    $this->drupalGet(Url::fromRoute('linkit.matcher.edit', ['linkit_profile' => $this->linkitProfile->id(), 'plugin_instance_id' => $matcher_uuid]));

    $this->assertText('Edit ' . $plugin->getLabel() . ' matcher');
  }

}
