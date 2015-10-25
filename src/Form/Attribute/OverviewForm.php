<?php

/**
 * @file
 * Contains \Drupal\linkit\Form\Attribute\OverviewForm.
 */

namespace Drupal\linkit\Form\Attribute;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\linkit\AttributeManager;
use Drupal\linkit\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an overview form for attribute on a profile.
 */
class OverviewForm extends FormBase {

  /**
   * The profiles to which the attributes are applied to.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  private $linkitProfile;

  /**
   * The attribute manager.
   *
   * @var \Drupal\linkit\AttributeManager
   */
  protected $manager;

  /**
   * Constructs a new OverviewForm.
   *
   * @param \Drupal\linkit\AttributeManager $manager
   *   The attribute manager.
   */
  public function __construct(AttributeManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.linkit.attribute')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "linkit_attribute_overview_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ProfileInterface $linkit_profile = NULL) {
    $this->linkitProfile = $linkit_profile;

    $form['plugins'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Attribute'),
        $this->t('Description'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No attributes added.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'plugin-order-weight',
        ],
      ],
    ];

    foreach ($this->linkitProfile->getAttributes() as $id => $attribute) {
      $form['plugins'][$id]['#attributes']['class'][] = 'draggable';
      $form['plugins'][$id]['#weight'] = $attribute->getWeight();

      $form['plugins'][$id]['label'] = [
        '#plain_text' => (string) $attribute->getLabel(),
      ];

      $form['plugins'][$id]['description'] = [
        '#plain_text' => (string) $attribute->getDescription(),
      ];

      $form['plugins'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => (string) $attribute->getLabel()]),
        '#title_display' => 'invisible',
        '#default_value' => $attribute->getWeight(),
        '#attributes' => ['class' => ['plugin-order-weight']],
      ];

      $form['plugins'][$id]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      $form['plugins'][$id]['operations']['#links']['delete'] = [
        'title' => t('Remove'),
        'url' => Url::fromRoute('linkit.attributes.remove', [
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
      if ($this->linkitProfile->getAttributes()->has($id)) {
        $this->linkitProfile->getAttribute($id)->setWeight($plugin_data['weight']);
      }
    }
    $this->linkitProfile->save();
  }

}
