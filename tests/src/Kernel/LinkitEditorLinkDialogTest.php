<?php

namespace Drupal\Tests\linkit\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\editor\Entity\Editor;
use Drupal\editor\Form\EditorLinkDialog;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\filter\Entity\FilterFormat;
use Drupal\linkit\Tests\ProfileCreationTrait;

/**
 * Tests EditorLinkDialog validation and conversion functionality.
 *
 * @group linkit
 */
class LinkitEditorLinkDialogTest extends LinkitKernelTestBase {

  use ProfileCreationTrait;

  /**
   * The linkit profile.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * Filter format for testing.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $format;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['editor', 'ckeditor', 'entity_test'];

  /**
   * Sets up the test.
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test');

    // Create a profile.
    $this->linkitProfile = $this->createProfile();

    /** @var \Drupal\linkit\MatcherManager $matcherManager */
    $matcherManager = $this->container->get('plugin.manager.linkit.matcher');

    // Add the entity_test matcher to the profile.
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $matcherManager->createInstance('entity:entity_test');
    $this->linkitProfile->addMatcher($plugin->getConfiguration());
    $this->linkitProfile->save();

    // Add a text format.
    $this->format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
      'filters' => [],
    ]);
    $this->format->save();

    // Set up editor.
    $editor = Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ]);
    $editor->setSettings([
      'plugins' => [
        'drupallink' => [
          'linkit_enabled' => TRUE,
          'linkit_profile' => $this->linkitProfile->id(),
        ],
      ],
    ]);
    $editor->save();
  }

  /**
   * Tests adding a link.
   */
  public function testAdd() {
    $entity_label = $this->randomString();
    $entity = EntityTest::create(['name' => $entity_label]);
    $entity->save();

    $form_object = new EditorLinkDialog();

    $input = [
      'editor_object' => [],
      'dialogOptions' => [
        'title' => 'Add Link',
        'dialogClass' => 'editor-link-dialog',
        'autoResize' => 'true',
      ],
      '_drupal_ajax' => '1',
      'ajax_page_state' => [
        'theme' => 'bartik',
        'theme_token' => 'some-token',
        'libraries' => '',
      ],
    ];
    $form_state = (new FormState())
      ->setRequestMethod('POST')
      ->setUserInput($input)
      ->addBuildInfo('args', [$this->format]);

    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = $this->container->get('form_builder');
    $form_id = $form_builder->getFormId($form_object, $form_state);
    $form = $form_builder->retrieveForm($form_id, $form_state);
    $form_builder->prepareForm($form_id, $form, $form_state);
    $form_builder->processForm($form_id, $form, $form_state);

    $this->assertEquals('linkit.autocomplete', $form['attributes']['href']['#autocomplete_route_name'], 'Linkit is enabled on the href field.');
    $this->assertEquals('', $form['attributes']['href']['#default_value'], 'The href attribute is empty.');
    $this->assertEquals('', $form['link-information']['#context']['link_target'], 'Link information is empty.');

    $form_state->setValue(['attributes', 'href'], 'entity:missing_entity/1');
    $form_builder->submitForm($form_object, $form_state);
    $this->assertNotEmpty($form_state->getErrors(), 'Got validation errors for none existing entity type.');

    $form_state->setValue(['attributes', 'href'], 'url_without_schema');
    $form_builder->submitForm($form_object, $form_state);
    $this->assertEmpty($form_state->getErrors(), 'Got no validation errors for url without schema.');
    $this->assertEquals('', $form_state->getValue(['attributes', 'data-entity-type']));
    $this->assertEquals('', $form_state->getValue(['attributes', 'data-entity-uuid']));

    $form_state->setValue(['attributes', 'href'], 'entity:entity_test/1');
    $form_builder->submitForm($form_object, $form_state);
    $this->assertEmpty($form_state->getErrors(), 'Got no validation errors for correct URI.');
    $this->assertEquals($entity->getEntityTypeId(), $form_state->getValue(['attributes', 'data-entity-type']), 'Attribute "data-entity-type" exists and has the correct value.');
    $this->assertEquals($entity->uuid(), $form_state->getValue(['attributes', 'data-entity-uuid']), 'Attribute "data-entity-uuid" exists and has the correct value.');
  }

  /**
   * Tests editing a link with data attributes.
   */
  public function testEditWithDataAttributes() {
    $entity_label = $this->randomString();
    $entity = EntityTest::create(['name' => $entity_label]);
    $entity->save();

    $entity_no_access = EntityTest::create(['name' => 'forbid_access']);
    $entity_no_access->save();

    $form_object = new EditorLinkDialog();

    $input = [
      'editor_object' => [
        'href' => 'entity:entity_test/1',
        'data-entity-type' => $entity->getEntityTypeId(),
        'data-entity-uuid' => $entity->uuid(),
      ],
      'dialogOptions' => [
        'title' => 'Edit Link',
        'dialogClass' => 'editor-link-dialog',
        'autoResize' => 'true',
      ],
      '_drupal_ajax' => '1',
      'ajax_page_state' => [
        'theme' => 'bartik',
        'theme_token' => 'some-token',
        'libraries' => '',
      ],
    ];
    $form_state = (new FormState())
      ->setRequestMethod('POST')
      ->setUserInput($input)
      ->addBuildInfo('args', [$this->format]);

    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = $this->container->get('form_builder');
    $form_id = $form_builder->getFormId($form_object, $form_state);
    $form = $form_builder->retrieveForm($form_id, $form_state);
    $form_builder->prepareForm($form_id, $form, $form_state);
    $form_builder->processForm($form_id, $form, $form_state);

    $this->assertEquals('linkit.autocomplete', $form['attributes']['href']['#autocomplete_route_name'], 'Linkit is enabled on the href field.');
    $this->assertEquals('entity:entity_test/1', $form['attributes']['href']['#default_value'], 'The href attribute is empty.');
    $this->assertEquals($entity->label(), $form['link-information']['#context']['link_target'], 'Link information is empty.');

    // Make sure the dialog don't display entity labels for inaccessible
    // entities.
    $input = [
      'editor_object' => [
        'href' => 'entity:entity_test/2',
        'data-entity-type' => $entity_no_access->getEntityTypeId(),
        'data-entity-uuid' => $entity_no_access->uuid(),
      ],
      'dialogOptions' => [
        'title' => 'Edit Link',
        'dialogClass' => 'editor-link-dialog',
        'autoResize' => 'true',
      ],
      '_drupal_ajax' => '1',
      'ajax_page_state' => [
        'theme' => 'bartik',
        'theme_token' => 'some-token',
        'libraries' => '',
      ],
    ];
    $form_state = (new FormState())
      ->setRequestMethod('POST')
      ->setUserInput($input)
      ->addBuildInfo('args', [$this->format]);

    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = $this->container->get('form_builder');
    $form_id = $form_builder->getFormId($form_object, $form_state);
    $form = $form_builder->retrieveForm($form_id, $form_state);
    $form_builder->prepareForm($form_id, $form, $form_state);
    $form_builder->processForm($form_id, $form, $form_state);

    $this->assertEquals('linkit.autocomplete', $form['attributes']['href']['#autocomplete_route_name'], 'Linkit is enabled on the href field.');
    $this->assertEquals('entity:entity_test/2', $form['attributes']['href']['#default_value'], 'The href attribute is empty.');
    $this->assertEquals('entity:entity_test/2', $form['link-information']['#context']['link_target'], 'Link information is empty.');
  }

  /**
   * Tests editing a link without data attributes.
   */
  public function testEditWithoutDataAttributes() {
    $form_object = new EditorLinkDialog();

    $input = [
      'editor_object' => [
        'href' => 'http://example.com/',
      ],
      'dialogOptions' => [
        'title' => 'Edit Link',
        'dialogClass' => 'editor-link-dialog',
        'autoResize' => 'true',
      ],
      '_drupal_ajax' => '1',
      'ajax_page_state' => [
        'theme' => 'bartik',
        'theme_token' => 'some-token',
        'libraries' => '',
      ],
    ];
    $form_state = (new FormState())
      ->setRequestMethod('POST')
      ->setUserInput($input)
      ->addBuildInfo('args', [$this->format]);

    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = $this->container->get('form_builder');
    $form_id = $form_builder->getFormId($form_object, $form_state);
    $form = $form_builder->retrieveForm($form_id, $form_state);
    $form_builder->prepareForm($form_id, $form, $form_state);
    $form_builder->processForm($form_id, $form, $form_state);

    $this->assertEquals('linkit.autocomplete', $form['attributes']['href']['#autocomplete_route_name'], 'Linkit is enabled on the href field.');
    $this->assertEquals('http://example.com/', $form['attributes']['href']['#default_value'], 'The href attribute is empty.');
    $this->assertEquals('http://example.com/', $form['link-information']['#context']['link_target'], 'Link information is empty.');
  }

}
