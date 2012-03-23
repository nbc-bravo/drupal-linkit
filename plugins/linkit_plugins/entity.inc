<?php
/**
 * @file
 * Linkit Entity Plugin.
 */

/**
 * Plugin structure is an associative array containing:
 * - ui_title: The title of the plugin used in the admin UI.
 * - ui_description: A description used in the admin UI.
 * - entity_type: Only required if the plugin extends LinkitPluginEntity. The
 *     entity type this plugin will be used for.
 * - handler: Describes the class to be used for this plugin.
 * - entity fallback handler: (optional) An array describing a class to use if
 *     the regular handler doesn't exists.
 */

$plugin = array(
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

    $plugin['ui_title'] = $entity['label'];
    $plugin['ui_description'] = t('Extend Linkit with @entity support.', array('@entity' => $entity_type));
    $plugin['name'] = $parent . ':' . $entity_type;
    $plugin['entity_type'] = $entity_type;

    // The handler class must exists.
    if (linkit_entity_ctools_linkit_get_handler_file($entity_type, $plugin)) {
      $plugin['handler'] = array(
        'class' => 'LinkitPlugin' . drupal_ucfirst($entity_type),
        'file' => 'linkit-plugin-' . $entity_type . '.class.php',
      );
    }
    drupal_alter('linkit_plugin_entity', $plugin, $entity);
    $plugins[$parent . ':' . $entity_type] = $plugin;
  }

  drupal_alter('linkit_plugin_entitys', $plugins);
  return $plugins;
}

/**
 * Find the handle class file.
 */
function linkit_entity_ctools_linkit_get_handler_file($entity_type, $plugin) {
  $classname = 'LinkitPlugin' . drupal_ucfirst($entity_type);
  $filename = 'linkit-plugin-' . $entity_type . '.class.php';

  $path = $plugin['path'] . '/' . $filename;
}