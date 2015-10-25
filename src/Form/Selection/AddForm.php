<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Selection\AddForm.
 */

namespace Drupal\linkit\Form\Selection;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\SelectionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to apply selection plugins to a profile.
 */
class AddForm extends FormBase {

  /**
   * The profiles to which the selection plugins will be applied.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The selection plugin manager.
   *
   * @var \Drupal\linkit\SelectionPluginManager
   */
  protected $manager;

  /**
   * Constructs a new AddForm.
   *
   * @param \Drupal\linkit\SelectionPluginManager $manager
   *   The selection plugin manager.
   */
  public function __construct(SelectionPluginManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.linkit.selection_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "linkit_selection_plugin_add_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $header = array(
      'label' => $this->t('Selection plugin'),
      'description' => $this->t('Description'),
    );

    $form['plugins'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $this->buildRows(),
      '#empty' => $this->t('No selection plugins available.'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add plugins'),
      '#submit' => array('::submitForm'),
      '#tableselect' => TRUE,
      '#button_type' => 'primary',
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->linkitProfile->urlInfo('selection-plugins'),
      '#attributes' => ['class' => ['button']],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    foreach (array_filter($form_state->getValue('plugins')) as $plugin_id) {
      $plugin_configuration = array(
        'id' => $plugin_id,
      );
      $this->linkitProfile->addSelectionPlugin($plugin_configuration);
    }

    $this->linkitProfile->save();

    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('selection-plugins'));
  }

  /**
   * Builds the table rows.
   *
   * Only selection plugins that is not already applied to the profile are
   * shown.
   *
   * @return array
   *   An array of table rows.
   */
  private function buildRows() {
    $rows = array();

    $applied_plugins = $this->linkitProfile->getSelectionPlugins()->getConfiguration();
    $all_plugins = $this->manager->getDefinitions();

    foreach ($all_plugins as $key => $definition) {
      /** @var \Drupal\linkit\SelectionPluginInterface $plugin */
      $plugin = $this->manager->createInstance($key, $definition);

      $row = array(
        'label' => (string) $plugin->getLabel(),
        'description' => (string) $plugin->getDescription(),
      );

      $rows[$plugin->getPluginId()] = $row;
    }

    return $rows;
  }

}
