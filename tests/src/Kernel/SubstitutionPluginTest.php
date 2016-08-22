<?php

namespace Drupal\Tests\linkit\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\file\Entity\File;

/**
 * Tests the substitution plugins.
 *
 * @group linkit
 */
class SubstitutionPluginTest extends LinkitKernelTestBase {

  /**
   * The substitution manager.
   *
   * @var \Drupal\linkit\SubstitutionManagerInterface
   */
  protected $substitutionManager;

  /**
   * The file substitution plugin.
   *
   * @var \Drupal\linkit\SubstitutionInterface
   */
  protected $fileSubstitution;

  /**
   * The canonical substitution plugin.
   *
   * @var \Drupal\linkit\SubstitutionInterface
   */
  protected $canonicalSubstitution;

  /**
   * Additional modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'file',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->substitutionManager = $this->container->get('plugin.manager.linkit.substitution');
    $this->fileSubstitution = $this->substitutionManager->createInstance('file');
    $this->canonicalSubstitution = $this->substitutionManager->createInstance('canonical');

    $this->installEntitySchema('file');
    $this->installEntitySchema('entity_test');
  }

  /**
   * Test the file substitution.
   */
  public function testFileSubstitutions() {
    $file = File::create([
      'uid' => 1,
      'filename' => 'druplicon.txt',
      'uri' => 'public://druplicon.txt',
      'filemime' => 'text/plain',
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();
    $this->assertEquals($GLOBALS['base_url'] . '/' . $this->siteDirectory . '/files/druplicon.txt', $this->fileSubstitution->getUrl($file)->getGeneratedUrl());
  }

  /**
   * Test the canonical URL substitution.
   */
  public function testCanonicalSubstutition() {
    $entity = EntityTest::create([]);
    $entity->save();
    $this->assertEquals('/entity_test/1', $this->canonicalSubstitution->getUrl($entity)->getGeneratedUrl());
  }

}
