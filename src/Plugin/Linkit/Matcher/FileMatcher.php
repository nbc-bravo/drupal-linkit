<?php

/**
 * @file
 * Contains \Drupal\linkit\Plugin\Linkit\Matcher\FileMatcher.
 */

namespace Drupal\linkit\Plugin\Linkit\Matcher;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\Utility\LinkitXss;

/**
 * @Matcher(
 *   id = "entity:file",
 *   target_entity = "file",
 *   label = @Translation("File"),
 *   provider = "file"
 * )
 */
class FileMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summery = parent::getSummary();

    $summery[] = $this->t('Show image dimensions: @show_image_dimensions', [
      '@show_image_dimensions' => $this->configuration['images']['show_dimensions'] ? $this->t('Yes') : $this->t('No'),
    ]);

    return $summery;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'images' => [
        'show_dimensions' => FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + [
      'module' => ['file'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['images'] = array(
      '#type' => 'details',
      '#title' => t('Image file settings'),
      '#description' => t('Extra settings for image files in the result.'),
      '#tree' => TRUE,
    );

    $form['images']['show_dimensions'] = [
      '#title' => t('Show pixel dimensions'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['images']['show_dimensions'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['images'] = $form_state->getValue('images');
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match) {
    $query = parent::buildEntityQuery($match);
    $query->condition('status', FILE_STATUS_PERMANENT);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildDescription($entity) {
    $description_array = array();

    $description_array[] = parent::buildDescription($entity);

    /** @var \Drupal\file\FileInterface $entity */
    $file = $entity->getFileUri();

    /** @var \Drupal\Core\Image\ImageInterface $image */
    $image = \Drupal::service('image.factory')->get($file);
    if ($image->isValid() && $this->configuration['images']['show_dimensions']) {
        $description_array[] = $image->getWidth() . 'x' . $image->getHeight() . 'px';
    }

    $description = implode('<br />' , $description_array);

    return LinkitXss::descriptionFilter($description);
  }

}
