<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Matcher\AddForm.
 */

namespace Drupal\linkit\Form\Matcher;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\MatcherManager;
use Drupal\linkit\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to apply matchers to a profile.
 */
class AddForm extends FormBase {

  /**
   * The profiles to which the matchers will be applied.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The matcher manager.
   *
   * @var \Drupal\linkit\MatcherManager
   */
  protected $manager;

  /**
   * Constructs a new AddForm.
   *
   * @param \Drupal\linkit\MatcherManager $manager
   *   The matcher manager.
   */
  public function __construct(MatcherManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.linkit.matcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "linkit_matcher_add_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $header = array(
      'label' => $this->t('Matcher'),
      'description' => $this->t('Description'),
    );

    $form['plugins'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $this->buildRows(),
      '#empty' => $this->t('No matchers available.'),
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
      '#url' => $this->linkitProfile->urlInfo('matchers'),
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
      $this->linkitProfile->addMatcher($plugin_configuration);
    }

    $this->linkitProfile->save();

    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('matchers'));
  }

  /**
   * Builds the table rows.
   *
   * Only matchers that is not already applied to the profile are
   * shown.
   *
   * @return array
   *   An array of table rows.
   */
  private function buildRows() {
    $rows = array();

    $applied_plugins = $this->linkitProfile->getMatchers()->getConfiguration();
    $all_plugins = $this->manager->getDefinitions();

    foreach ($all_plugins as $key => $definition) {
      /** @var \Drupal\linkit\MatcherInterface $plugin */
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
