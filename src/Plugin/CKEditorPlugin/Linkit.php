<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\CKEditorPlugin\Linkit.
 */

namespace Drupal\linkit\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "linkit" plugin.
 *
 * @CKEditorPlugin(
 *   id = "linkit",
 *   label = @Translation("Linkit"),
 *   module = "linkit"
 * )
 */
class Linkit extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface, ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'linkit') . '/js/plugins/linkit/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'linkit_dialogTitleAdd' => t('Add link'),
      'linkit_dialogTitleEdit' => t('Edit link'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'Linkit' => array(
        'label' => t('Linkit'),
        'image' => drupal_get_path('module', 'linkit') . '/js/plugins/linkit/linkit.png',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    $all_profiles = $this->entityManager->getStorage('linkit_profile')->loadMultiple();

    // @TODO: Can this be "nicer"?
    $options = array();
    foreach ($all_profiles as $profile) {
      /** @var \Drupal\linkit\ProfileInterface $profile */
      $options[$profile->id()] = $profile->label();
    }

    // @TODO: Add information text about the selection. A link to the profile
    // collection page?

    $form['linkit_profile'] = array(
      '#type' => 'select',
      '#title' => t('Select a profile'),
      '#options' => $options,
      '#default_value' => isset($settings['plugins']['linkit']) ? $settings['plugins']['linkit'] : '',
      '#empty_option' => $this->t('- Select -'),
      '#element_validate' => array(
        array($this, 'validateLinkitProfileValue'),
      ),
    );

    return $form;
  }

  /**
   * #element_validate handler for the "profile" element in settingsForm().
   */
  public function validateLinkitProfileValue(array $element, FormStateInterface $form_state) {
    if (empty($element['#value'])) {
      $form_state->setError($element, $this->t('You must select a profile in order to use Linkit on this format.'));
    }
  }

}
