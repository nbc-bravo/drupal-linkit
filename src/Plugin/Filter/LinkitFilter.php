<?php

namespace Drupal\linkit\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Linkit filter.
 *
 * Note this must run before any Xss::filter() calls, because that strips
 * disallowed protocols. That effectively means this must run before the
 * \Drupal\filter\Plugin\Filter\FilterHtml filter. Hence the very low weight.
 *
 * @Filter(
 *   id = "linkit",
 *   title = @Translation("Linkit filter"),
 *   description = @Translation("Updates content links inserted by Linkit to point to the current URL alias, and have the current title."),
 *   settings = {
 *     "title" = TRUE,
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = -15,
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository')
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
          $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
          if ($entity) {
            $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);

            // Set the appropriate href attribute.
            // The file entity has not declared any "links" in its entity
            // definition. We therefor have to use the file entity specific
            // getFileUri() instead.
            if ($entity_type === 'file') {
              /** @var \Drupal\file\Entity\File $entity */
              $url = file_create_url($entity->getFileUri());
              $element->setAttribute('href', $url);
            }
            else {
              $url = $entity->toUrl()->toString(TRUE);
              $element->setAttribute('href', $url->getGeneratedUrl());
            }

            $access = $entity->access('view', NULL, TRUE);

            // Set the appropriate title attribute.
            if ($this->settings['title'] && !$access->isForbidden()) {
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
      '#attached' => [
        'library' => ['linkit/linkit.filter_html.admin'],
      ],
    ];
    return $form;
  }

}
