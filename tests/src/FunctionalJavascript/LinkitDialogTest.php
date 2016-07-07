<?php

namespace Drupal\Tests\linkit\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\editor\Entity\Editor;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\linkit\Tests\ProfileCreationTrait;
use Drupal\node\Entity\NodeType;

/**
 * Tests the linkit alterations on the drupallink plugin.
 *
 * @group linkit
 */
class LinkitDialogTest extends JavascriptTestBase {

  use ProfileCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'ckeditor', 'filter', 'linkit'];

  /**
   * An instance of the "CKEditor" text editor plugin.
   *
   * @var \Drupal\ckeditor\Plugin\Editor\CKEditor;
   */
  protected $ckeditor;

  /**
   * A demo entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $demoEntity;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $matcherManager = $this->container->get('plugin.manager.linkit.matcher');
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $matcherManager->createInstance('entity:node', []);
    $profile = $this->createProfile();
    $profile->addMatcher($plugin->getConfiguration());
    $profile->save();

    // Create text format, associate CKEditor.
    $llama_format = FilterFormat::create([
      'format' => 'llama',
      'name' => 'Llama',
      'weight' => 0,
      'filters' => [],
    ]);
    $llama_format->save();
    $editor = Editor::create([
      'format' => 'llama',
      'editor' => 'ckeditor',
    ]);
    $editor->save();

    // Create "CKEditor" text editor plugin instance.
    $this->ckeditor = $this->container->get('plugin.manager.editor')->createInstance('ckeditor');

    // Create a node type for testing.
    NodeType::create(['type' => 'page', 'name' => 'page'])->save();

    // Create a body field instance for the 'page' node type.
    FieldConfig::create([
      'field_storage' => FieldStorageConfig::loadByName('node', 'body'),
      'bundle' => 'page',
      'label' => 'Body',
      'settings' => ['display_summary' => TRUE],
      'required' => TRUE,
    ])->save();

    // Assign widget settings for the 'default' form mode.
    EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent('body', ['type' => 'text_textarea_with_summary'])->save();

    // Customize the configuration.
    $this->container->get('plugin.manager.editor')->clearCachedDefinitions();

    $this->ckeditor = $this->container->get('plugin.manager.editor')->createInstance('ckeditor');
    $this->container->get('plugin.manager.ckeditor.plugin')->clearCachedDefinitions();
    $settings = $editor->getSettings();
    $settings['plugins']['drupallink']['linkit_enabled'] = TRUE;
    $settings['plugins']['drupallink']['linkit_profile'] = $profile->id();
    $editor->setSettings($settings);
    $editor->save();

    $account = $this->drupalCreateUser([
      'administer nodes',
      'create page content',
      'edit own page content',
      'use text format llama',
    ]);

    $this->drupalLogin($account);
  }

  /**
   * Test the link dialog.
   */
  public function testLinkDialog() {
    // Create test nodes.
    $this->demoEntity = $this->createNode(['title' => 'Foo']);

    // Go to node creation page.
    $this->drupalGet('node/add/page');
    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();

    // Wait until the editor has been loaded.
    $ckeditor_loaded = $this->getSession()->wait(5000, "jQuery('.cke_contents').length > 0");
    $this->assertTrue($ckeditor_loaded, 'The editor has been loaded.');

    // Click on the drupallink plugin.
    $link_button = $page->find('css', 'a.cke_button__drupallink');
    $link_button->click();

    // Wait for the form to load.
    $web_assert->assertWaitOnAjaxRequest();

    // Find the href field.
    $input_field = $page->findField('attributes[href]');

    // Make sure linkit has altered the href field.
    $input_field->hasAttribute('data-autocomplete-path');
    $input_field->hasClass('form-linkit-autocomplete');
    $input_field->hasClass('ui-autocomplete-input');

    // Make sure the autocomplete result container is hidden.
    $autocomplete_container = $page->find('css', 'ul.linkit-ui-autocomplete');
    $this->assertFalse($autocomplete_container->isVisible());

    // Make sure the link information is empty.
    $javascript = "(function (){ return jQuery('.linkit-link-information > span').text(); })()";
    $link_information = $session->evaluateScript($javascript);
    $this->assertEquals('', $link_information, 'Link information is empty');

    // Trigger a keydown event to active a autocomplete search.
    $input_field->keyDown('f');

    // Wait for the results to load.
    $this->getSession()->wait(5000, "jQuery('.linkit-result.ui-menu-item').length > 0");

    // Make sure the autocomplete result container is visible.
    $this->assertTrue($autocomplete_container->isVisible());

    // Find all the autocomplete results.
    $results = $page->findAll('css', '.linkit-result.ui-menu-item');
    $this->assertEquals(1, count($results), 'Found autocomplete result');

    // Find the first result and click it.
    $result_selection = $page->find('xpath', '(//li[contains(@class, "linkit-result") and contains(@class, "ui-menu-item")])[1]');
    $result_selection->click();

    // Make sure the href field is populated with the node uri.
    $this->assertEquals('entity:' . $this->demoEntity->getEntityTypeId() . '/' . $this->demoEntity->id(), $input_field->getValue(), 'The href field is populated with the node uri');

    // Make sure the link information is populated.
    $javascript = "(function (){ return jQuery('.linkit-link-information > span').text(); })()";
    $link_information = $session->evaluateScript($javascript);
    $this->assertEquals($this->demoEntity->label(), $link_information, 'Link information is populated');

    // Save the dialog input.
    $button = $page->find('css', '.editor-link-dialog')->find('css', '.button.form-submit span');
    $button->click();

    // Wait for the dialog to close.
    $web_assert->assertWaitOnAjaxRequest();

    // We can't use $session->switchToIFrame() here, because the iframe does not
    // have a name.
    foreach (['data-entity-type' => $this->demoEntity->getEntityTypeId(), 'data-entity-uuid' => $this->demoEntity->uuid()] as $attr => $value) {
      $javascript = <<<JS
        (function(){
          var iframes = document.getElementsByClassName('cke_wysiwyg_frame');
          if (iframes.length) {
            var doc = iframes[0].contentDocument || iframes[0].contentWindow.document;
            var link = doc.getElementsByTagName('a')[0];
            return link.getAttribute("$attr");
          }
        })()
JS;
      $link_attr = $session->evaluateScript($javascript);
      $this->assertNotNull($link_attr);
      $this->assertEquals($value, $link_attr);
    }
  }

}
