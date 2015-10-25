<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Attribute\AddForm.
 */

namespace Drupal\linkit\Form\Attribute;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\AttributePluginManager;
use Drupal\linkit\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to apply attributes plugins to a profile.
 */
class AddForm extends FormBase {

  /**
   * The profiles to which the attribute plugins will be applied.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The attribute plugin manager.
   *
   * @var \Drupal\linkit\AttributePluginManager
   */
  protected $manager;

  /**
   * Constructs a new AddForm.
   *
   * @param \Drupal\linkit\AttributePluginManager $manager
   *   The attribute plugin manager.
   */
  public function __construct(AttributePluginManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.linkit.attribute_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "linkit_attribute_plugin_add_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $header = array(
      'label' => $this->t('Attribute plugin'),
      'description' => $this->t('Description'),
    );

    $form['plugins'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $this->buildRows(),
      '#empty' => $this->t('No attribute plugins available.'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add attributes'),
      '#submit' => array('::submitForm'),
      '#tableselect' => TRUE,
      '#button_type' => 'primary',
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->linkitProfile->urlInfo('attribute-plugins'),
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
      $plugin = array(
        'id' => $plugin_id,
      );
      $this->linkitProfile->addAttributePlugin($plugin);
    }

    $this->linkitProfile->save();

    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('attribute-plugins'));
  }

  /**
   * Builds the table rows.
   *
   * Only attributes plugins that is not already applied to the profile are
   * shown.
   *
   * @return array
   *   An array of table rows.
   */
  private function buildRows() {
    $rows = array();

    $applied_plugins = $this->linkitProfile->getAttributePlugins()->getConfiguration();
    $all_plugins = $this->manager->getDefinitions();

    foreach (array_diff_key($all_plugins, $applied_plugins) as $definition) {
      /** @var \Drupal\linkit\AttributePluginInterface $plugin */
      $plugin = $this->manager->createInstance($definition['id']);

      $row = array(
        'label' => (string) $plugin->getLabel(),
        'description' => (string) $plugin->getDescription(),
      );

      $rows[$plugin->getPluginId()] = $row;
    }

    return $rows;
  }

}
