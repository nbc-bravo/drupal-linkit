<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\linkit\MatcherBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Matcher(
 *   id = "entity",
 *   label = @Translation("Entity"),
 *   deriver = "\Drupal\linkit\Plugin\Derivative\EntityMatcherDeriver"
 * )
 */
class EntityMatcher extends MatcherBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The target entity type id
   *
   * @var string
   */
  protected $target_type;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler,   AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (empty($plugin_definition['target_entity'])) {
      throw new \InvalidArgumentException("Missing required 'target_entity' property for a matcher.");
    }
    $this->database = $database;
    $this->entityManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->target_type = $plugin_definition['target_entity'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'result_description' => '',
      'bundles' => [],
      'group_by_bundle' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $entity_type = $this->entityManager->getDefinition($this->target_type);
    $bundles = $this->entityManager->getBundleInfo($this->target_type);

    $form['result_description'] = [
      '#title' => $this->t('Result description'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['result_description'],
      '#size' => 120,
      '#maxlength' => 255,
    ];

    // @TODO: Add support for tokens in the result_description.

    // Filter the possible bundles to use if the entity has bundles.
    if ($entity_type->hasKey('bundle')) {
      $bundle_options = [];
      foreach ($bundles as $bundle_name => $bundle_info) {
        $bundle_options[$bundle_name] = $bundle_info['label'];
      }

      $form['bundles'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Bundle filter'),
        '#options' => $bundle_options,
        '#default_value' => $this->configuration['bundles'],
        '#description' => $this->t('If none of the checkboxes is checked, allow all bundles.'),
        '#element_validate' => [[get_class($this), 'elementValidateFilter']],
      ];

      // Group the results within this bundle.
      $form['group_by_bundle'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Group by bundle'),
        '#default_value' => $this->configuration['group_by_bundle'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['result_description'] = $form_state->getValue('result_description');
    $this->configuration['bundles'] = $form_state->getValue('bundles');
    $this->configuration['group_by_bundle'] = $form_state->getValue('group_by_bundle');
  }

  /**
   * Form element validation handler; Filters the #value property of an element.
   */
  public static function elementValidateFilter(&$element, FormStateInterface $form_state) {
    $element['#value'] = array_filter($element['#value']);
    $form_state->setValueForElement($element, $element['#value']);
  }

  /**
   * {@inheritdoc}
   */
  public function getMatches($string) {
    $query = $this->buildEntityQuery($string);
    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $matches = [];
    $entities = $this->entityManager->getStorage($this->target_type)->loadMultiple($result);
    foreach ($entities as $entity_id => $entity) {
      $matches[] = [
        'title' => $this->buildLabel($entity),
        'description' => $this->buildDescription($entity),
        'path' => $this->buildPath($entity),
        'group' => $this->buildGroup($entity),
      ];
    }

    return $matches;
  }

  /**
   * Builds an EntityQuery to get entities.
   *
   * @param $match
   *   Text to match the label against.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The EntityQuery object with the basic conditions and sorting applied to
   *   it.
   */
  protected function buildEntityQuery($match) {
    $match = $this->database->escapeLike($match);

    $entity_type = $this->entityManager->getDefinition($this->target_type);
    $query = $this->entityManager->getStorage($this->target_type)->getQuery();
    $label_key = $entity_type->getKey('label');

    if ($label_key) {
      $query->condition($label_key, '%' . $match . '%', 'LIKE');
      $query->sort($label_key, 'ASC');
    }

    // Add entity-access tag.
    $query->addTag($this->target_type . '_access');

    return $query;
  }

  /**
   * Builds the label string used in the match array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return string
   *   The label for this entity.
   */
  protected function buildLabel($entity) {
    return Html::escape($entity->label());
  }

  /**
   * Builds the description string used in the match array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return string
   *    The description for this entity.
   */
  protected function buildDescription($entity) {
    $description = $this->configuration['result_description'];
    return Html::escape($description);
  }

  /**
   * Builds the path string used in the match array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *    The matched entity.
   *
   * @return string
   *   The URL for this entity.
   */
  protected function buildPath($entity) {
    return $entity->url();
  }

  /**
   * Builds the group string used in the match array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return string
   *   The match group for this entity.
   */
  protected function buildGroup($entity) {
    // @TODO: Use the "group_by_bundle" setting.

    return Html::escape($entity->getEntityTypeId());
  }

}
