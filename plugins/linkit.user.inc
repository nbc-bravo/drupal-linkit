<?php

/**
 * @file
 * Extend Linkit with user links.
 */

/**
 * Build the default node query.
 */
function _linkit_user_create_base_query($profile) {
  // Get users
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'user')
        ->propertyCondition('status', 1);

  // Add the default sort.
  $query->propertyOrderBy('name', 'ASC');

  return $query;
}

/**
 * The autocomplete callback function for the Linkit user plugin.
 */
function _linkit_user_autocomplete($string, $profile) {
  $matches = array();

  $query = _linkit_user_create_base_query($profile);

  // Add the title condition to the query object.
  $query->propertyCondition('name', '%' . db_like($string) . '%', 'LIKE')
        ->addTag('linkit_user_autocomplete');

  // Execute the query.
  $result = $query->execute();
  $user_uids = array_keys($result['user']);

  // Load all the users with all the uids we got.
  $users = entity_load('user', $user_uids);

  foreach ($users AS $user) {
    // Create the URI for the entuty.
    $uri = entity_uri('user', $user);

    $matches[] = array(
      'title' => $user->name,
      'description' => token_replace(check_plain($profile->data['user']['result_description']), array(
        'user' => $user,
      )),
      'path' => url($uri['path'], array('alias' => TRUE)),
      'group' => t('Users'),
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