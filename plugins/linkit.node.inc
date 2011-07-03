<?php

/**
 * @file
 * Extend Linkit with node links.
 */

/**
 * Build the default node query.
 */
function _linkit_node_create_base_query($profile, $result_tokens) {
  // Build default query
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title'))
    ->addTag('node_access');

  // Not all tokens are supported by Linkit.
  // Here is a list of valid tokens
  // [node:nid]
  // [node:vid]
  // [node:tnid]
  // [node:type]
  // [node:type-name]
  // [node:title]
  // [node:language]
  // [node:created] (short, medium, long, since, raw, custom, [default : medium])
  // [node:changed] (short, medium, long, since, raw, custom, [default : medium])
  // [node:author]  (Can use the user tokens, [default : name])

  if (isset($result_tokens['node'])) {
    foreach ($result_tokens['node'] as $token => $token_value) {
      switch ($token) {
        case 'vid':
          $query->addField('n', 'vid');
          break;
        case 'tnid':
          $query->addField('n', 'tnid');
          break;
        case 'type':
        case 'type-name';
          $query->addField('n', 'type');
          break;
        case 'language':
          $query->addField('n', 'language');
          break;
        case 'author':
          $query->addField('n', 'uid'); // @todo: kolla på detta!
          $query->addField('u', 'name', 'name');
          $query->leftJoin('users', 'u', 'u.uid = n.uid');
          break;
        case 'created':
          $query->addField('n', 'created');
          break;
        case 'changed':
          $query->addField('n', 'changed');
          break;
      }
    }

    // This tokens can be chained so we cant use them in the foreach loop above.
    if (token_find_with_prefix($result_tokens['node'], 'author')) {
      $query->addField('n', 'uid');
    }

    if (token_find_with_prefix($result_tokens['node'], 'created')) {
      $query->addField('n', 'created');
    }

    if (token_find_with_prefix($result_tokens['node'], 'changed')) {
      $query->addField('n', 'changed');
    }
  }

  // Content type check
  if ($allowed_content_types = array_filter($profile['node']['content_types'])) {
    $query->condition('n.type', $allowed_content_types, 'IN');
  }

  // Status condition.
  if (!isset($profile['node']['include_unpublished'])) {
    $query->condition('n.status', 1);
  }

  return $query;
}


/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_node_autocomplete($string, $profile) {
  $matches = array();

  $result_description = check_plain($profile['node']['result_description']);

  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);

  // Build the base query.
  $query = _linkit_node_create_base_query($profile, $result_tokens);
  $query->condition('n.title', '%' . db_like($string) . '%', 'LIKE');
  $result = $query->execute();

  foreach ($result as $node) {
    $matches[] = array(
      'title' => $node->title,
      'description' => token_replace($result_description, array(
        'node' => $node,
      )),
      'path' => 'node/' . $node->nid,
      'group' => t('Content'),
    );
  }

  return $matches;
}

/**
 * Implements hook_linkit_plugins "path info callback".
 *
 * If the path given is a node item, then return information about that node.
 *
 * @see linkit.api.php
 *
 * @todo Perhaps non core plugins should be able to alter the appearance of
 * title and description by for example adding tokens and having a way to
 * alter the query so their particular field can be loaded. For example if
 * they want a new token "node image" which fetches an image field file ref
 * that inserts a thumbnail for that image. Maybe it's a little too much :)
 */
function _linkit_node_path_info($path_info, $profile) {
  // TODO: Move to linkit.module:
  // Backwards compatible with internal: links
  // $escaped_string = str_replace('internal:', '', $string)

  // Check if path is referring to a node
  if (arg(0, $path_info['system_path']) == 'node' && is_numeric(arg(1, $path_info['system_path']))) {
    $result_description = check_plain($profile['node']['result_description']);

    // Build a list of all token-like patterns that appear in the text.
    $result_tokens = token_scan($result_description);

    // Build the base query.
    $query = _linkit_node_create_base_query($profile, $result_tokens);
    $query->condition('n.nid', arg(1, $path_info['system_path']));
    $query_result = $query->execute()->fetch();

    if ($query_result) {
      $result = array(
        'title' => check_plain($query_result->title),
        'description' => token_replace($result_description, array(
          'node' => $query_result,
        )),
      );
      return $result;
    }
  }
  return FALSE;
}
