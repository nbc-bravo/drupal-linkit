<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Matcher\DeleteForm.
 */

namespace Drupal\linkit\Form\Matcher;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\ProfileInterface;

/**
 * Provides a form to remove a matcher from a profile.
 */
class DeleteForm extends ConfirmFormBase {

  /**
   * The profiles that the matcher is applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The matcher to be removed from the profile.
   *
   * @var \Drupal\linkit\MatcherInterface
   */
  protected $linkitMatcher;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the @plugin matcher from the %profile profile?', array('%profile' => $this->linkitProfile->label(), '@plugin' => $this->linkitMatcher->getLabel()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Remove');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->linkitProfile->urlInfo('matchers');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'linkit_matcher_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL, $plugin_id = NULL) {
    $this->linkitProfile = $linkit_profile;
    $this->linkitMatcher = $this->linkitProfile->getMatcher($plugin_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->linkitProfile->getMatchers()->has($this->linkitMatcher->getPluginId())) {
      $this->linkitProfile->removeMatcher($this->linkitMatcher->getPluginId());
      $this->linkitProfile->save();
    }

    drupal_set_message($this->t('The matcher %label has been removed.', array('%label' => $this->linkitMatcher->getLabel())));
    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('matchers'));
    // @TODO: Log this?
  }

}
