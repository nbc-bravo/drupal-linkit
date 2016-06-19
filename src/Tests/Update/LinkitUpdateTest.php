<?php

namespace Drupal\linkit\Tests\Update;

use Drupal\filter\Entity\FilterFormat;
use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Tests Linkit upgrade paths.
 *
 * @group Update
 */
class LinkitUpdateTest extends UpdatePathTestBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * Set database dump files to be used.
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../tests/fixtures/update/linkit-4-to-5/drupal-8.linkit-enabled.standard.php.gz',
      __DIR__ . '/../../../tests/fixtures/update/linkit-4-to-5/linkit-4-to-5.php',
    ];
  }

  /**
   * Tests linkit_update_8500().
   *
   * @see linkit_update_8500()
   */
  public function testLinkitUpdate8500() {
    $editor = $this->configFactory->get('editor.editor.format_1');
    $this->assertTrue($editor->get('settings.plugins.linkit'), 'We got old linkit settings in the editor configuration.');
    $format_1_linkit_profile = $editor->get('settings.plugins.linkit.linkit_profile');

    $editor = $this->configFactory->get('editor.editor.format_2');
    $this->assertTrue($editor->get('settings.plugins.linkit'), 'We got old linkit settings in the editor configuration.');
    $format_2_linkit_profile = $editor->get('settings.plugins.linkit.linkit_profile');

    $editor = $this->configFactory->get('editor.editor.format_3');
    $this->assertTrue($editor->get('settings.plugins.linkit'), 'We got old linkit settings in the editor configuration.');
    $format_3_linkit_profile = $editor->get('settings.plugins.linkit.linkit_profile');

    $this->runUpdates();

    $test_profile = $this->configFactory->get('linkit.linkit_profile.test_profile');
    $this->assertEqual(NULL, $test_profile->get('attributes'), 'Attributes are deleted from the profile.');

    $editor = $this->configFactory->get('editor.editor.format_1');
    $this->assertNull($editor->get('settings.plugins.linkit'), 'Old linkit settings in the editor configuration is removed.');
    $this->assertEqual($editor->get('settings.toolbar.rows.0.1.items.0'), 'DrupalLink', 'Drupal link plugin is in the toolbar.');
    $this->assertNotEqual($editor->get('settings.toolbar.rows.0.1.items.1'), 'Linkit', 'Linkit plugin is removed from the toolbar.');
    $this->assertTrue($editor->get('settings.plugins.drupallink.linkit_enabled'), 'Drupal link plugin has linkit enabled.');
    $this->assertEqual($editor->get('settings.plugins.drupallink.linkit_profile'), $format_1_linkit_profile, 'Drupal link plugin uses the same profile as the old linkit plugin.');

    $editor = $this->configFactory->get('editor.editor.format_2');
    $this->assertNull($editor->get('settings.plugins.linkit'), 'Old linkit settings in the editor configuration is removed.');
    $this->assertEqual($editor->get('settings.toolbar.rows.0.1.items.0'), 'DrupalLink', 'Drupal link plugin is in the toolbar.');
    $this->assertTrue($editor->get('settings.plugins.drupallink.linkit_enabled'), 'Drupal link plugin has linkit enabled.');
    $this->assertEqual($editor->get('settings.plugins.drupallink.linkit_profile'), $format_2_linkit_profile, 'Drupal link plugin uses the same profile as the old linkit plugin.');

    $editor = $this->configFactory->get('editor.editor.format_3');
    $this->assertNull($editor->get('settings.plugins.linkit'), 'Old linkit settings in the editor configuration is removed.');
    $this->assertEqual($editor->get('settings.toolbar.rows.0.0.items.0'), 'DrupalLink', 'Drupal link plugin is in the toolbar.');
    $this->assertTrue($editor->get('settings.plugins.drupallink.linkit_enabled'), 'Drupal link plugin has linkit enabled.');
    $this->assertEqual($editor->get('settings.plugins.drupallink.linkit_profile'), $format_3_linkit_profile, 'Drupal link plugin uses the same profile as the old linkit plugin.');

    $format = $this->configFactory->get('filter.format.format_1');
    $this->assertNotNull($format->get('filters.linkit'), 'Linkit filter is enabled.');
    $this->assertTrue($format->get('filters.linkit.weight') < $format->get('filters.filter_html.weight'), 'Linkit filter is running before filter_html.');

    $format = $this->configFactory->get('filter.format.format_2');
    $this->assertNotNull($format->get('filters.linkit'), 'Linkit filter is enabled.');

    $format = $this->configFactory->get('filter.format.format_3');
    $this->assertNotNull($format->get('filters.linkit'), 'Linkit filter is enabled.');

    $htmlRestrictions = FilterFormat::load('format_1')->getHtmlRestrictions();
    $this->assertTrue(array_key_exists("data-entity-type", $htmlRestrictions['allowed']['a']));
    $this->assertTrue(array_key_exists("data-entity-uuid", $htmlRestrictions['allowed']['a']));

    $htmlRestrictions = FilterFormat::load('format_3')->getHtmlRestrictions();
    $this->assertTrue(array_key_exists("data-entity-type", $htmlRestrictions['allowed']['a']));
    $this->assertTrue(array_key_exists("data-entity-uuid", $htmlRestrictions['allowed']['a']));
  }

}
