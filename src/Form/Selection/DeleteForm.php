<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Selection\DeleteForm.
 */

namespace Drupal\linkit\Form\Selection;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\ProfileInterface;

/**
 * Provides a form to remove a selection plugin from a profile.
 */
class DeleteForm extends ConfirmFormBase {

  /**
   * The profiles that the selection plugin is applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The selection plugin to be removed from the profile.
   *
   * @var \Drupal\linkit\SelectionPluginInterface
   */
  protected $linkitSelectionPlugin;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the @plugin selection plugin from the %profile profile?', array('%profile' => $this->linkitProfile->label(), '@plugin' => $this->linkitSelectionPlugin->getLabel()));
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
    return $this->linkitProfile->urlInfo('selection-plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'linkit_selection_plugin_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL, $plugin_id = NULL) {
    $this->linkitProfile = $linkit_profile;
    $this->linkitSelectionPlugin = $this->linkitProfile->getSelectionPlugin($plugin_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->linkitProfile->getSelectionPlugins()->has($this->linkitSelectionPlugin->getPluginId())) {
      $this->linkitProfile->removeSelectionPlugin($this->linkitSelectionPlugin->getPluginId());
      $this->linkitProfile->save();
    }

    drupal_set_message($this->t('The selection plugin %label has been removed.', array('%label' => $this->linkitSelectionPlugin->getLabel())));
    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('selection-plugins'));
    // @TODO: Log this?
  }

}
