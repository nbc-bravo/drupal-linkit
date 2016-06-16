<?php

namespace Drupal\linkit\Tests;

use Drupal\Core\Url;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests the IMCE module integration.
 *
 * @group linkit
 */
class ImceIntegrationTest extends LinkitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['editor', 'ckeditor', 'imce'];

  /**
   * The linkit profile.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The text format to use when opening the link dialog.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $filterFormat;

  /**
   * The editor to bind the text format to and enable linkit on.
   *
   * @var \Drupal\editor\EditorInterface
   */
  protected $editor;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->linkitProfile = $this->createProfile();
    $this->drupalLogin($this->adminUser);

    $this->filterFormat = FilterFormat::create([
      'format' => 'linkit_test_format',
      'name' => 'Linkit test format',
      'weight' => 1,
      'filters' => [],
    ]);
    $this->filterFormat->save();

    // Set up text editor.
    $this->editor = Editor::create([
      'format' => $this->filterFormat->id(),
      'editor' => 'ckeditor',
    ]);
    $this->editor->setSettings([
      'plugins' => [
        'drupallink' => [
          'linkit_enabled' => TRUE,
          'linkit_profile' => $this->linkitProfile->id(),
        ],
      ],
    ]);
    $this->editor->save();

    // Create a regular user with access to the format.
    $this->baseUser = $this->drupalCreateUser([
      $this->filterFormat->getPermissionName(),
    ]);
  }

  /**
   * Test that the IMCE link does not shows up.
   */
  public function testImceIntegationDisabled() {
    $this->drupalLogin($this->baseUser);

    $this->drupalGet(Url::fromRoute('editor.link_dialog', [
      'filter_format' => $this->filterFormat->id(),
    ]));

    $this->assertNoLink('Open IMCE file browser');
  }

  /**
   * Test that the IMCE link shows up.
   */
  public function testImceIntegationEnabled() {
    $this->drupalGet(Url::fromRoute('entity.linkit_profile.edit_form', [
      'linkit_profile' => $this->linkitProfile->id(),
    ]));
    $this->assertResponse(200);

    $this->assertText('IMCE integration');
    $this->assertFieldByName('imce_use');

    $edit = [];
    $edit['imce_use'] = TRUE;
    $this->drupalPostForm(NULL, $edit, t('Update profile'));

    $this->drupalGet(Url::fromRoute('entity.linkit_profile.edit_form', [
      'linkit_profile' => $this->linkitProfile->id(),
    ]));

    $this->assertFieldChecked('edit-imce-use');

    $this->drupalLogin($this->baseUser);

    $this->drupalGet(Url::fromRoute('editor.link_dialog', [
      'filter_format' => $this->filterFormat->id(),
    ]));
    $this->assertLink('Open IMCE file browser');
  }

}
