<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Selection\OverviewForm.
 */

namespace Drupal\linkit\Form\Selection;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\linkit\ProfileInterface;
use Drupal\linkit\SelectionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an overview form for selection plugins on a profile.
 */
class OverviewForm extends FormBase {

  /**
   * The profiles to which the selection plugins are applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  private $linkitProfile;

  /**
   * The selection plugin manager.
   *
   * @var \Drupal\linkit\SelectionPluginManager
   */
  protected $manager;

  /**
   * Constructs a new OverviewForm.
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
    return "linkit_selection_plugin_overview_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $form['plugins'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Selection plugin'),
        $this->t('Description'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No selection plugins added.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'plugin-order-weight',
        ],
      ],
    ];

    foreach ($this->linkitProfile->getSelectionPlugins() as $id => $plugin) {
      $form['plugins'][$id]['#attributes']['class'][] = 'draggable';
      $form['plugins'][$id]['#weight'] = $plugin->getWeight();

      $form['plugins'][$id]['label'] = [
        '#plain_text' => (string) $plugin->getLabel(),
      ];

      $form['plugins'][$id]['description'] = [
        '#plain_text' => (string) $plugin->getDescription(),
      ];

      $form['plugins'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => (string) $plugin->getLabel()]),
        '#title_display' => 'invisible',
        '#default_value' => $plugin->getWeight(),
        '#attributes' => ['class' => ['plugin-order-weight']],
      ];

      $form['plugins'][$id]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      $form['plugins'][$id]['operations']['#links']['edit'] = [
        'title' => t('Edit'),
        'url' => Url::fromRoute('linkit.selection_plugin.edit', [
          'linkit_profile' =>  $this->linkitProfile->id(),
          'plugin_id' => $id,
        ]),
      ];

      $form['plugins'][$id]['operations']['#links']['delete'] = [
        'title' => t('Remove'),
        'url' => Url::fromRoute('linkit.selection_plugin.remove', [
          'linkit_profile' =>  $this->linkitProfile->id(),
          'plugin_id' => $id,
        ]),
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('plugins') as $id => $plugin_data) {
      if ($this->linkitProfile->getSelectionPlugins()->has($id)) {
        $this->linkitProfile->getSelectionPlugin($id)->setWeight($plugin_data['weight']);
      }
    }
    $this->linkitProfile->save();
  }

}
