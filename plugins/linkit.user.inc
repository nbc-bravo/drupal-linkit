<?php

/**
 * @file
 * Extend Linkit with user links.
 */

/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_user_autocomplete($string, $settings) {
  $matches = array();

  $result_description = check_plain($settings['user']['result_description']);
  
  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);

  // Get users
  $query = db_select('users', 'u')
    ->fields('u', array('uid', 'name'))
    ->condition('u.name', '%' . db_like($string) . '%', 'LIKE')
    ->condition('u.status' , '1');

  // Not all tokens are supported by Linkit.
  // Here is a list of valid tokens
  // [user:uid]
  // [user:name]
  // [user:mail]
  // [user:last-login] (short, medium, long, since, raw, custom, [default : medium])
  // [user:created] (short, medium, long, since, raw, custom, [default : medium])
  if (isset($result_tokens['user'])) {
    foreach ($result_tokens['user'] AS $token => $token_value) {
      switch ($token) {
        case 'mail':
          $query->addField('u', 'mail');
          break;

        case 'last-login':
          $query->addField('u', 'login');
          break;

        case 'created':
          $query->addField('u', 'created');
          break;
      }
    }

    // There can be tokens that is chained so we will not find them in the switch statement above.
    if (token_find_with_prefix($result_tokens['user'], 'last-login')) {
      $query->addField('u', 'login');
    }

    if (token_find_with_prefix($result_tokens['user'], 'created')) {
      $query->addField('u', 'created');
    }
  }

  $result = $query->execute();

  foreach ($result AS $user) {
    $matches[] = array(
      'title' => $user->name,
      'description' => token_replace($result_description, array(
        'user' => $user,
      )),
      'path' => 'user/' . $user->uid,
      'group' => t('Users'),
    );

  }

  return $matches;
}

/**
 * Implements hook_linkit_get_search_styled_link().
 */
function linkit_user_linkit_get_search_styled_link($string) {
  // Check to see that the link really is a user link
  // Backwards compatible with internal: links
  $escaped_string = str_replace('internal:', '', $string);
  $splitted_string = explode('/', $escaped_string);
  if ($splitted_string[0] != 'user') {
    return;
  }

  // This is a node link created with Linkit, try to grab the title and path now.
  $result = db_select('users', 'u')
    ->fields('u', array('name'))
    ->condition('u.uid', $splitted_string[1])
    ->execute()
    ->fetchObject();

  // No reault was found
  if (!$result) {
    return;
  }

  return check_plain($result->name) . ' [path:' . $escaped_string . ']';
}

/**
 * Implements hook_linkit_info_plugins().
 * 
 * This is used by linkit_permissions
 */
function linkit_user_linkit_info_plugins() {
  $return['linkit_user'] = array(
    'type' => 'user',
  );
  return $return;
}