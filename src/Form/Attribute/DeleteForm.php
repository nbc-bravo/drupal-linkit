<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Attribute\DeleteForm.
 */

namespace Drupal\linkit\Form\Attribute;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\ProfileInterface;

/**
 * Provides a form to remove an attribute plugin from a profile.
 */
class DeleteForm extends ConfirmFormBase {

  /**
   * The profiles that the attribute plugin is applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The attribute plugin to be removed from the profile.
   *
   * @var \Drupal\linkit\AttributePluginInterface
   */
  protected $linkitAttribute;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the @plugin attribute plugin from the %profile profile?', array('%profile' => $this->linkitProfile->label(), '@plugin' => $this->linkitAttribute->getLabel()));
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
    return $this->linkitProfile->urlInfo('attribute-plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'linkit_attribute_plugin_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL, $plugin_id = NULL) {
    $this->linkitProfile = $linkit_profile;
    $this->linkitAttribute = $this->linkitProfile->getAttributePlugin($plugin_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->linkitProfile->getAttributePlugins()->has($this->linkitAttribute->getPluginId())) {
      $this->linkitProfile->removeAttributePlugin($this->linkitAttribute->getPluginId());
      $this->linkitProfile->save();
    }

    drupal_set_message($this->t('The attribute plugin %label has been removed.', array('%label' => $this->linkitAttribute->getLabel())));
    $form_state->setRedirectUrl($this->linkitProfile->urlInfo('attribute-plugins'));
    // @TODO: Log this?
  }

}
