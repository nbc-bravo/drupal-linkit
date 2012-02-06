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
  'entity fallback handler' => array(
    'class' => 'LinkitPluginEntity',
    'file' => 'linkit-plugin-entity.class.php',
  ),
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
    $plugin['handler'] = array(
      'class' => 'LinkitPlugin' . drupal_ucfirst($entity_type),
      'file' => 'linkit-plugin-' . $entity_type . '.class.php',
    );
    $plugins[$parent . ':' . $entity_type] = $plugin;
  }

  return $plugins;
}