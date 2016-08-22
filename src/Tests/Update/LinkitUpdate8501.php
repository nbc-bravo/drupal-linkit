<?php

namespace Drupal\linkit\Tests\Update;

use Drupal\filter\Entity\FilterFormat;
use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Tests Linkit upgrade path for update 8501.
 *
 * @group Update
 */
class LinkitUpdate8501 extends UpdatePathTestBase {

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
      __DIR__ . '/../../../tests/fixtures/update/8501/linkit-update-8501.php',
    ];
  }

  /**
   * Tests linkit_update_8501().
   *
   * @see linkit_update_8501()
   */
  public function testLinkitUpdate8501() {
    $this->runUpdates();

    $test_profile = $this->configFactory->get('linkit.linkit_profile.test_profile');
    $this->assertEqual('canonical', $test_profile->get('matchers.fc48c807-2a9c-44eb-b86b-7e134c1aa252.settings.substitution_type'), 'Content matcher has a substitution type of canonical.');
    $this->assertEqual('file', $test_profile->get('matchers.b8d6d672-6377-493f-b492-3cc69511cf17.settings.substitution_type'), 'File matcher has a substitution type of file.');

    $htmlRestrictions = FilterFormat::load('format_1')->getHtmlRestrictions();
    $this->assertTrue(array_key_exists("data-entity-type", $htmlRestrictions['allowed']['a']));
    $this->assertTrue(array_key_exists("data-entity-uuid", $htmlRestrictions['allowed']['a']));
    $this->assertTrue(array_key_exists("data-entity-substitution", $htmlRestrictions['allowed']['a']));
  }

}
