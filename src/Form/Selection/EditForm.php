<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Selection\EditForm.
 */

namespace Drupal\linkit\Form\Selection;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\ProfileInterface;

/**
 *  Provides an edit form for selection plugins.
 */
class EditForm extends FormBase {

  /**
   * The profiles to which the selection plugins will be applied.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The profiles to which the selection plugins will be applied.
   *
   * @var \Drupal\linkit\SelectionPluginInterface
   */
  protected $selectionPlugin;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'linkit_selection_plugin_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL, $plugin_id = NULL) {
    $this->linkitProfile = $linkit_profile;
    $this->selectionPlugin = $this->linkitProfile->getSelectionPlugin($plugin_id);
    $form['data'] = [
      '#tree' => true,
    ];

    $form['data'] += $this->selectionPlugin->buildConfigurationForm($form, $form_state);

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save changes'),
      '#submit' => array('::submitForm'),
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
    $value = $form_state->getValue('data');
    $effect_data = (new FormState())->setValues($form_state->getValue('data'));
    $this->selectionPlugin->submitConfigurationForm($form, $effect_data);

    $this->linkitProfile->save();
  }
}
