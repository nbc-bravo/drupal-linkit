<?php

/**
 * @file
 * Extend Linkit with entity support.
 */

/**
 * Build the default query.
 */
function _linkit_entity_create_base_query($profile, $name, $plugin) {
  // Load all defined entities.
  $entity_info = entity_get_info($name);

  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', $name);

  // Add the default sort on the enity label.
  $query->propertyOrderBy($entity_info['entity keys']['label'], 'ASC');
  return $query;
}

/**
 * The autocomplete callback function for the Linkit user plugin.
 */
function linkit_entity_autocomplete($string, $profile, $name, $plugin) {
  $matches = array();
  $entity_info = entity_get_info($name);
  $query = _linkit_entity_create_base_query($profile, $name, $plugin);

  // Add the title condition to the query object.
  $query->propertyCondition($entity_info['entity keys']['label'], '%' . db_like($string) . '%', 'LIKE')
        ->addTag('linkit_entity_autocomplete');

  drupal_alter('linkit_entity_query', $query, $profile, $name, $plugin);

  // Execute the query.
  $result = $query->execute();
  $ids = array_keys($result[$name]);

  // Load all the users with all the uids we got.
  $entities = entity_load($name, $ids);

  foreach ($entities AS $entity) {
    // If we have the entity module enabled, we check the access againt the
    // definded entity access callback.
    if (module_exists('entity') && !entity_access('view', $name, $entity)) {
      continue;
    }

    // Create the URI for the entity.
    $uri = entity_uri($name, $entity);

    $matches[] = array(
      'title' => entity_label($name, $entity),
      'description' => token_replace(check_plain($profile->data['entity']['result_description']), array(
        'entity' => $entity,
      )),
      'path' => url($uri['path'], array('alias' => TRUE)),
      'group' => t($name),
    );
  }
  return $matches;
}

/**
 * The path info callback function for the Linkit user plugin.
 *
 * If the path given is a user, then return information about that user.
 *
 * @see linkit.api.php
 */
function _linkit_user_path_info($path_info, $profile) {
  // Check if path is referring to a user
  if (isset($path_info['system_path']) && arg(0, $path_info['system_path']) == 'user' && is_numeric(arg(1, $path_info['system_path']))) {

    $uid = arg(1, $path_info['system_path']);

    // Load the user with the corresponding uid.
    $users= entity_load('user', array($uid));
    $user = $users[$uid];

    if ($user) {
      $result = array(
        'title' => check_plain($user->name),
        'description' => token_replace(check_plain($profile->data['user']['result_description']), array(
          'user' => $user,
        )),
      );
      return $result;
    }
  }
  return FALSE;
}