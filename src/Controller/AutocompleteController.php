<?php

/**
 * @file
 * Contains \Drupal\linkit\Controller\AutocompleteController.
 */

namespace Drupal\linkit\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController implements ContainerInjectionInterface {

  /**
   * The linkit profile storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $linkitProfileStorage;

  /**
   * The linkit profile.
   *
   * @var \Drupal\linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * Constructs a EntityAutocompleteController object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $linkit_profile_storage
   *   The linkit profile storage service.
   */
  public function __construct(EntityStorageInterface $linkit_profile_storage) {
    $this->linkitProfileStorage = $linkit_profile_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('linkit_profile')
    );
  }

  /**
   * Menu callback for linkit search autocompletion.
   *
   * Like other autocomplete functions, this function inspects the 'q' query
   * parameter for the string to use to search for suggestions.
   *
   * @param Request $request
   * @param $linkit_profile_id
   *
   * @return JsonResponse A JSON response containing the autocomplete suggestions.
   * A JSON response containing the autocomplete suggestions.
   */
  public function autocomplete(Request $request, $linkit_profile_id) {
    $this->linkitProfile = $this->linkitProfileStorage->load($linkit_profile_id);
    $matchers = $this->linkitProfile->getMatchers();

    $matches = array();

    $string = Unicode::strtolower($request->query->get('q'));

    // Special for link to frontpage.
    if (strpos($string, 'front') !== FALSE) {
      $matches[] = array(
        'title' => t('Frontpage'),
        'description' => 'The frontpage for this site.',
        'path' => Url::fromRoute('<front>')->toString(),
        'group' => t('System'),
      );
    }

    foreach ($matchers as $plugin) {
      $matches = array_merge($matches, $plugin->getMatches($string));
    }

    return new JsonResponse($matches);
  }

}
