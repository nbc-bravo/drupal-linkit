<?php

namespace Drupal\linkit\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\linkit\Plugin\Linkit\Substitution\Media;
use Drupal\linkit\SubstitutionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Linkit filter.
 *
 * @Filter(
 *   id = "linkit",
 *   title = @Translation("Linkit URL converter"),
 *   description = @Translation("Updates links inserted by Linkit to point to entity URL aliases."),
 *   settings = {
 *     "title" = TRUE,
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class LinkitFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The substitution manager.
   *
   * @var \Drupal\linkit\SubstitutionManagerInterface
   */
  protected $substitutionManager;

  /**
   * Constructs a LinkitFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\linkit\SubstitutionManagerInterface $substitution_manager
   *   The substitution manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entity_repository, SubstitutionManagerInterface $substitution_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityRepository = $entity_repository;
    $this->substitutionManager = $substitution_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('plugin.manager.linkit.substitution')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && strpos($text, 'data-entity-uuid') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//a[@data-entity-type and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $entity_type = $element->getAttribute('data-entity-type');
          $uuid = $element->getAttribute('data-entity-uuid');

          // Make the substitution optional, for backwards compatibility,
          // maintaining the previous hard-coded direct file link assumptions,
          // for content created before the substitution feature.
          if (!$substitution_type = $element->getAttribute('data-entity-substitution')) {
            $substitution_type = $entity_type === 'file' ? 'file' : SubstitutionManagerInterface::DEFAULT_SUBSTITUTION;
          }

          $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
          if ($entity) {
            $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);

            /** @var \Drupal\Core\GeneratedUrl $url */
            $substitution = $this->substitutionManager->createInstance($substitution_type);
            $url = $substitution->getUrl($entity);

            // Check if we need to add the download attribute to Media files.
            if ($this->settings['download'] && $substitution instanceof Media) {
              $media_type = $entity->get('bundle')->entity;
              if (!empty($media_type->getSource()->getConfiguration()['source_field'])) {
                $element->setAttribute('download', $entity->label());
              }
            }

            $element->setAttribute('href', $url->getGeneratedUrl());
            $access = $entity->access('view', NULL, TRUE);

            // Set the appropriate title attribute.
            if ($this->settings['title'] && !$access->isForbidden() && !$element->getAttribute('title')) {
              $element->setAttribute('title', $entity->label());
            }

            // The processed text now depends on:
            $result
              // - the linked entity access for the current user.
              ->addCacheableDependency($access)
              // - the generated URL (which has undergone path & route
              // processing)
              ->addCacheableDependency($url)
              // - the linked entity (whose URL and title may change)
              ->addCacheableDependency($entity);
          }
        }
        catch (\Exception $e) {
          watchdog_exception('linkit_filter', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically set the <code>title</code> attribute to that of the (translated) referenced content'),
      '#default_value' => $this->settings['title'],
    ];
    $form['download'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically set the <code>download</code> attribute to Media entities'),
      '#default_value' => (!empty($this->settings['download'])) ? 1 : 0,
    ];
    $form['#attached']['library'][] = 'linkit/linkit.filter_html.admin';
    return $form;
  }

}
