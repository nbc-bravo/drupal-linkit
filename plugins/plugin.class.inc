<?php
/**
 * @file
 * Linkit Plugin interface.
 *
 * Provides an interface and classes to implement Linkit plugins.
 */

/**
 * Linkit plugin interface.
 */
interface LinkitPluginInterface {
  public function autocomplete_callback();
  //public function path_info_callback();
}

/**
 *
 * LinkitPlugin.
 */
abstract class LinkitPlugin implements LinkitPluginInterface {

  function init($profile, $plugin_name) {
    $this->profile = $profile;
    $this->plugin_name = $plugin_name;
  }

  function setSearchString($serachString) {
    $this->searchString = $serachString;
  }

  /**
   * Returns a string to use as the search result label.
   */
  function buildLabel($label) {
    return check_plain($label);
  }

  /**
   * Returns an URL based in the path and the options.
   */
  function buildPath($path, $options = array()) {
    return url($path, $options);
  }

  /**
   * If there is a "result_description", run it thro token_replace.
   */
  function buildDescription($item) {
    if (isset($this->profile->data[$this->plugin_name]['result_description'])) {
      return token_replace(check_plain($this->profile->data[$this->plugin_name]['result_description']), array(
       $this->plugin_name => $item,
      ));
    }
  }

  /**
   * Returns a string to use as the search result label.
   */
  function buildGroup($groupname) {
    return check_plain($groupname);
  }

  /**
   * Returns a string with CSS classes that will be added to the search result
   * row for this item.
   *
   * @return a string with CSS classes
   */
  function buildRowClass() {}

  /**
   * Add plugin settings to the profile settings page.
   * Use regular Drupal FAPI elements.
   */
  function buildSettingsForm() {}
}

/**
 * Define Linkit entity plugin.
 */
class LinkitPluginEntity extends LinkitPlugin {
  /**
   * Entity field query instance.
   *
   * @var Resource
   */
  var $query;

  /**
   * The entity info array of an entity type.
   *
   * @var array
   */
  var $entity_info;

  /**
   * The name of the property that contains the entity label.
   *
   * @var string
   */
  var $entity_field_label;

  /**
   * The name of the property of the bundle object that contains the name of
   * the bundle object.
   *
   * @var string
   */
  var $entity_key_bundle;

  /**
   *
   * @param object $profile
   *   The Linkit profile to use.
   * @param string $plugin_name
   *   The name of the plugin calling this method.
   */
  function init($profile, $plugin_name) {
    parent::init($profile, $plugin_name);

    // Load the corresponding entity.
    $this->entity_info = entity_get_info($this->plugin_name);

    // Set bundle key.
    if (isset($this->entity_info['entity keys']['bundle'])) {
      $this->entity_key_bundle = $this->entity_info['entity keys']['bundle'];
    }

    // Set the label field.
    if(!isset($this->entity_field_label)) {
      $this->entity_field_label = $this->entity_info['entity keys']['label'];
    }
  }

  /**
   * Returns a string to use as the search result label.
   */
  function buildLabel($entity) {
    return entity_label($this->plugin_name, $entity);
  }

  /**
   * Returns an URL based in the path and the options.
   */
  function buildPath($entity, $options = array()) {
    // Create the URI for the entity.
    $uri = entity_uri($this->plugin_name, $entity);

    // We have to set alias to TRUE as we don't want an alias back.
    $options += array('alias' => TRUE);

    return parent::buildPath($uri['path'], $options);
  }

  /**
   * When "group_by_bundle" is active, we need to add the bundle name to the
   * group, else just return the entity label.
   *
   * @return a string with the group name.
   */
  function buildGroup($entity) {
    // Get the entity label.
    $group = $this->entity_info['label'];

    // If the entities by this entity should be grouped by bundle, get the
    // name and append it to the group.
    if (isset($this->profile->data[$this->plugin_name]['group_by_bundle']) &&
            $this->profile->data[$this->plugin_name]['group_by_bundle']) {
      $bundles = $this->entity_info['bundles'];
      $bundle_name = $bundles[$entity->{$this->entity_key_bundle}]['label'];
      $group .= ' · ' . check_plain($bundle_name);
    }
    return $group;
  }

  /**
   * Start a new EntityFieldQuery instance.
   */
  function getQueryInstance() {
    $this->query = new EntityFieldQuery();
    $this->query->entityCondition('entity_type', $this->plugin_name);

    // Add the default sort on the enity label.
    $this->query->propertyOrderBy($this->entity_field_label, 'ASC');
  }

  /**
   * The autocomplete callback function for the Linkit Entity plugin.
   *
   * @return
   *   An associative array whose values are an
   *   associative array containing:
   *   - title: A string to use as the search result label.
   *   - description: (optional) A string with additional information about the
   *     result item.
   *   - path: The URL to the item.
   *   - group: (optional) A string with the group name for the result item.
   *     Best practice is to use the plugin name as group name.
   *   - addClass: (optional) A string with classes to add to the result row.
   */
  function autocomplete_callback() {
    $matches = array();
    // Get the EntityFieldQuery instance.
    $this->getQueryInstance();

    // Add the search condition to the query object.
    $this->query->propertyCondition($this->entity_field_label,
            '%' . db_like($this->searchString) . '%', 'LIKE')
        ->addTag('linkit_entity_autocomplete')
        ->addTag('linkit_' . $this->plugin_name . '_autocomplete');

    // Execute the query.
    $result = $this->query->execute();

    $ids = array_keys($result[$this->plugin_name]);

    // Load all the entities with all the ids we got.
    $entities = entity_load($this->plugin_name, $ids);

    foreach ($entities AS $entity) {
      // If we have the entity module enabled, we check the access againt the
      // definded entity access callback.
      if (module_exists('entity') && !entity_access('view', $this->plugin_name, $entity)) {
        continue;
      }

      $matches[] = array(
        'title' => $this->buildLabel($entity),
        'description' => $this->buildDescription($entity),
        'path' => $this->buildPath($entity),
        'group' => $this->buildGroup($entity),
        'addClass' => $this->buildRowClass($entity),
      );

    }
    return $matches;
  }

}

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

/**
 * Define Linkit user plugin class.
 */
class LinkitPluginUser extends LinkitPluginEntity {
  /**
   * The user entity doesn't add any label in their entity keys as they define a
   * "label callback" instead. Therefor we have to tell which field the user
   * entity have as label.
   */
  var $entity_field_label = 'name';
}


/**
 * Define Linkit term plugin class.
 */
class LinkitPluginTaxonomy_Term extends LinkitPluginEntity {

}