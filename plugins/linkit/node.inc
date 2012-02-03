<?php
/**
 * @file
 * Linkit Node Plugin.
 */

/**
 * Define Linkit node plugin class.
 */
class LinkitPluginNode extends LinkitPluginEntity {

  /**
   * If we are inclunding unpublished nodes in the search results, and this
   * entity is unpublished, we need to tell the user this.
   *
   * @return a string which will be used as the search result label for this
   *   item.
   */
  function buildLabel($entity) {
    $label = parent::buildLabel($entity);
    if (isset($this->profile->data['node']['include_unpublished']) && $entity->status == NODE_NOT_PUBLISHED) {
      $label .= '<span class="status"> - ' . t('unpublished') . '</span>';
    }
    return $label;
  }

  /**
   * Returns a string with CSS classes that will be added to the search result
   * row for this item.
   *
   * @return a string with CSS classes
   */
  function buildRowClass($entity) {
     if (isset($this->profile->data['node']['include_unpublished']) && $entity->status == NODE_NOT_PUBLISHED) {
      return 'unpublished-node';
    }
  }
}