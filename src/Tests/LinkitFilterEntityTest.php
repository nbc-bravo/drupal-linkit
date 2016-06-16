<?php

namespace Drupal\linkit\Tests;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the Linkit filter for entities.
 *
 * @group linkit
 */
class LinkitFilterEntityTest extends LinkitFilterTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_test', 'path', 'language'];

  /**
   * An user with permissions to administer content types.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add Swedish, Danish and Finnish.
    ConfigurableLanguage::createFromLangcode('sv')->save();
    ConfigurableLanguage::createFromLangcode('da')->save();
    ConfigurableLanguage::createFromLangcode('fi')->save();
  }

  /**
   * Tests the linkit filter for entities with different access.
   */
  public function testFilterEntityAccess() {
    $entity_no_access = EntityTest::create(['name' => 'forbid_access']);
    $entity_no_access->save();
    $entity_with_access = EntityTest::create(['name' => $this->randomMachineName()]);
    $entity_with_access->save();

    // Automatically set the title.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);
    // The title should not be included.
    $input = '<a data-entity-type="' . $entity_no_access->getEntityTypeId() . '" data-entity-uuid="' . $entity_no_access->uuid() . '">Link text</a>';
    $this->assertFalse(strpos($this->process($input)->getProcessedText(), 'title'), 'The link does not contain a title attribute.');
    $this->assertLinkitFilterWithTitle($entity_with_access);
  }

  /**
   * Tests the linkit filter for entities with translations.
   */
  public function testFilterEntityTranslations() {
    // Create an entity and add translations to that.
    /** @var EntityTestMul $entity */
    $entity = EntityTestMul::create(['name' => $this->randomMachineName()]);
    $entity->addTranslation('sv', ['name' => $this->randomMachineName(), 'langcode' => 'sv']);
    $entity->addTranslation('da', ['name' => $this->randomMachineName(), 'langcode' => 'da']);
    $entity->addTranslation('fi', ['name' => $this->randomMachineName(), 'langcode' => 'fi']);
    $entity->save();

    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = $this->container->get('path.alias_storage');

    $url = $entity->toUrl()->toString();

    // Add url aliases.
    $path_alias_storage->save($url, '/' . $this->randomMachineName(), 'en');
    $path_alias_storage->save($url, '/' . $this->randomMachineName(), 'sv');
    $path_alias_storage->save($url, '/' . $this->randomMachineName(), 'da');
    $path_alias_storage->save($url, '/' . $this->randomMachineName(), 'fi');

    // Disable the automatic title attribute.
    $this->filter->setConfiguration(['settings' => ['title' => 0]]);
    /** @var \Drupal\Core\Language\Language $language */
    foreach ($entity->getTranslationLanguages() as $language) {
      $this->assertLinkitFilter($entity->getTranslation($language->getId()), $language->getId());
    }

    // Enable the automatic title attribute.
    $this->filter->setConfiguration(['settings' => ['title' => 1]]);
    /** @var \Drupal\Core\Language\Language $language */
    foreach ($entity->getTranslationLanguages() as $language) {
      $this->assertLinkitFilterWithTitle($entity->getTranslation($language->getId()), $language->getId());
    }
  }

}
