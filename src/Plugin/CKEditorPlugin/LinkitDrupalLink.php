<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\CKEditorPlugin\LinkitDrupalLink.
 */

namespace Drupal\linkit\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\Plugin\CKEditorPlugin\DrupalLink;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends the default Drupal link plugin and adds a settings form to select a
 * Linkit profile.
 */
class LinkitDrupalLink extends DrupalLink implements CKEditorPluginConfigurableInterface, ContainerFactoryPluginInterface {

  /**
   * The Linkit profile storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $linkitProfileStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $linkit_profile_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->linkitProfileStorage = $linkit_profile_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('linkit_profile')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    $all_profiles = $this->linkitProfileStorage->loadMultiple();

    $options = [];
    foreach ($all_profiles as $profile) {
      $options[$profile->id()] = $profile->label();
    }

    $form['linkit_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Linkit enabled'),
      '#default_value' => isset($settings['plugins']['drupallink']['linkit_enabled']) ? $settings['plugins']['drupallink']['linkit_enabled'] : '',
      '#description' => $this->t('Enable Linkit for this text format.'),
    ];

    $form['linkit_profile'] = [
      '#type' => 'select',
      '#title' => t('Linkit profile'),
      '#options' => $options,
      '#default_value' => isset($settings['plugins']['drupallink']['linkit_profile']) ? $settings['plugins']['drupallink']['linkit_profile'] : '',
      '#empty_option' => $this->t('- Select -'),
      '#description' => $this->t('Select the Linkit profile you wish to use with this text format.'),
      '#states' => [
        'invisible' => [
          'input[data-drupal-selector="edit-editor-settings-plugins-drupallink-linkit-enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#element_validate' => [
        [$this, 'validateLinkitProfileSelection'],
      ],
    ];

    return $form;
  }

  /**
   * #element_validate handler for the "linkit_profile" element in
   * settingsForm().
   */
  public function validateLinkitProfileSelection(array $element, FormStateInterface $form_state) {
    $settings = $form_state->getValue(array('editor', 'settings', 'plugins', 'drupallink'));
    $enabled = isset($settings['linkit_enabled']) && $settings['linkit_enabled'] === 1;
    if ($enabled && empty(trim($settings['linkit_profile']))) {
      $form_state->setError($element, t('Please select the Linkit profile you wish to use.'));
    }
  }

}
