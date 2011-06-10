<?php 

/**
 * @file
 * Extend Linkit with node links.
 */

/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_node_autocomplete($string, $settings) {
  $matches = array();

  $result_description = check_plain($settings['node']['result_description']);
  
  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);
  
  // Build default query
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title'))
    ->condition('n.title', '%' . db_like($string) . '%', 'LIKE')
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

  /*
  // Content type check
  if ($allowed_content_types = array_filter($settings['content_types'])) {
    $query->condition('n.type', $allowed_content_types, 'IN');
  }
  */
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
 * @todo the query will almost be equal to autocomplete search.
 * Perhaps create a new function that constructs the first part of the query
 * to avoid duplicate code?
 *
 * @todo Perhaps non core plugins should be able to alter the appearance of
 * title and description by for example adding tokens and having a way to
 * alter the query so their particular field can be loaded. For example if
 * they want a new token "node image" which fetches an image field file ref
 * that inserts a thumbnail for that image. Maybe it's a little too much :)
 */
function _linkit_node_path_info($path_info, $settings) {
  // TODO: Move to linkit.module:
  // Backwards compatible with internal: links
  // $escaped_string = str_replace('internal:', '', $string)

  // Check if path is referring to a node
  if (arg(0, $path_info['system_path']) == 'node' && is_numeric(arg(1, $path_info['system_path']))) {
    $db_result = db_select('node', 'n')
      ->fields('n', array('title', 'type', 'nid'))
      ->condition('n.nid', arg(1, $path_info['system_path']))
      ->addTag('node_access')
      ->execute()
      ->fetchObject();

    if ($db_result) {
      // TODO: Use tokens instead
      $result = array(
        'title' => check_plain($db_result->title),
        'description' => check_plain('id: ' . $db_result->nid . ' · type: ' . $db_result->type),
      );
      return $result;
    }
  }
  return FALSE;
}
