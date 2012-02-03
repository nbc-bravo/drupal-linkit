<?php
/**
 * @file
 * Linkit Entity Plugin.
 *
 */
$plugin = array(
  'title' => t("Entity : bundle"),
  'description' => t('Enables.'),
  'get child' => 'linkit_entity_ctools_linkit_get_child',
  'get children' => 'linkit_entity_ctools_linkit_get_children',
  'class' => 'LinkitPluginEntity',
);

function linkit_entity_ctools_linkit_get_child($plugin, $parent, $child) {
  $plugins = linkit_entity_ctools_linkit_get_children($plugin, $parent);
  return $plugins[$parent . ':' . $child];
}

function linkit_entity_ctools_linkit_get_children($plugin, $parent) {
  $entities = entity_get_info();
  $plugins = array();

  foreach ($entities as $entity_type => $entity) {
    // We can and will only use entities that have an URI CALLBACK defined.
    if (!isset($entity['uri callback'])) {
      continue;
    }
    $plugin['title'] = $entity['label'];
    $plugin['description'] = t('Enable Linkit to search for the @entity entity bundle.', array('@entity' => $entity_type));
    $plugin['name'] = $parent . ':' . $entity_type;
    $plugin['entity_type'] = $entity_type;
    $plugins[$parent . ':' . $entity_type] = $plugin;
  }

  return $plugins;
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
   * @param array $plugin
   *   The plugin array.
   */
  function __construct($plugin, $profile) {
    parent::init($plugin, $profile);

    // Load the corresponding entity.
    $this->entity_info = entity_get_info($this->plugin['entity_type']);

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
    return entity_label($this->plugin['entity_type'], $entity);
  }

  /**
   * Returns an URL based in the path and the options.
   */
  function buildPath($entity, $options = array()) {
    // Create the URI for the entity.
    $uri = entity_uri($this->plugin['entity_type'], $entity);

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
    if (isset($this->profile->data[$this->plugin['name']]['group_by_bundle']) &&
            $this->profile->data[$this->plugin['name']]['group_by_bundle']) {
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
    $this->query->entityCondition('entity_type', $this->plugin['entity_type']);

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
        ->addTag('linkit_' . $this->plugin['name'] . '_autocomplete');

    // Execute the query.
    $result = $this->query->execute();

    $ids = array_keys($result[$this->plugin['entity_type']]);

    // Load all the entities with all the ids we got.
    $entities = entity_load($this->plugin['entity_type'], $ids);

    foreach ($entities AS $entity) {
      // If we have the entity module enabled, we check the access againt the
      // definded entity access callback.
      if (module_exists('entity') && !entity_access('view', $this->plugin['entity_type'], $entity)) {
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