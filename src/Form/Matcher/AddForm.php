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

    $options = [];
    foreach ($this->manager->getDefinitions() as $id => $plugin) {
      $options[$id] = $plugin['label'];
    }

    $form['plugin'] = array(
      '#type' => 'select',
      '#title' => $this->t('Add a new matcher'),
      '#options' => $options,
      '#empty_option' => $this->t('- Select a matcher -'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#submit' => array('::submitForm'),
      '#tableselect' => TRUE,
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance($form_state->getValue('plugin'));

    $plugin_uuid = $this->linkitProfile->addMatcher($plugin->getConfiguration());
    $this->linkitProfile->save();

    $form_state->setRedirect('linkit.matcher.edit', [
      'linkit_profile' => $this->linkitProfile->id(),
      'plugin_instance_id' => $plugin_uuid,
    ]);
  }

}
