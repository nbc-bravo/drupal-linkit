<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Matcher\OverviewForm.
 */

namespace Drupal\linkit\Form\Matcher;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\linkit\MatcherManager;
use Drupal\linkit\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an overview form for matchers on a profile.
 */
class OverviewForm extends FormBase {

  /**
   * The profiles to which the matchers are applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  private $linkitProfile;

  /**
   * The matcher manager.
   *
   * @var \Drupal\linkit\MatcherManager
   */
  protected $manager;

  /**
   * Constructs a new OverviewForm.
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
    return "linkit_matcher_overview_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $form['plugins'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Matcher'),
        $this->t('Description'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No matchers added.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'plugin-order-weight',
        ],
      ],
    ];

    foreach ($this->linkitProfile->getMatchers() as $id => $plugin) {
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
        'url' => Url::fromRoute('linkit.matcher.edit', [
          'linkit_profile' =>  $this->linkitProfile->id(),
          'plugin_id' => $id,
        ]),
      ];

      $form['plugins'][$id]['operations']['#links']['delete'] = [
        'title' => t('Remove'),
        'url' => Url::fromRoute('linkit.matcher.remove', [
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
      if ($this->linkitProfile->getMatchers()->has($id)) {
        $this->linkitProfile->getMatcher($id)->setWeight($plugin_data['weight']);
      }
    }
    $this->linkitProfile->save();
  }

}
