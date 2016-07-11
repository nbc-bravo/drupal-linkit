<?php

namespace Drupal\linkit\Suggestion;

/**
 * Defines a simple suggestion.
 */
class SimpleSuggestion implements SuggestionInterface {

  /**
   * The suggestion label.
   *
   * @var string
   *   The suggestion label.
   */
  protected $label;

  /**
   * The suggestion path.
   *
   * @var string
   *   The suggestion path.
   */
  protected $path;

  /**
   * The suggestion group.
   *
   * @var string
   *   The suggestion group.
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup($group) {
    $this->group = $group;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'label' => $this->getLabel(),
      'path' => $this->getPath(),
      'group' => $this->getGroup(),
    ];
  }

}
