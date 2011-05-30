<?php 

/**
 * @file
 * Extend Linkit with node links.
 */


/**
 * Implements hook_linkit_load_plugins().
 * TODO: Add second parameter $settings with this plugins settings for the current profile
 */
function _linkit_node_autocomplete($string, $settings) {
  
  $result_description = check_plain($settings['node']['result_description']);
  
  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);
  
  // Build default query
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title'))
    ->condition('n.title', '%' . db_like($string) . '%', 'LIKE')
    ->addTag('node_access');
  

  foreach ($result_tokens['node'] AS $token => $token_value) {
    switch ($token) {
      case 'content-type':
        $query->addField('n', 'type');
        $query->addField('nt', 'name', 'content_type');
        $query->innerJoin('node_type', 'nt', 'nt.type = n.type');
        break;
      case 'language':
        $query->addField('n', 'language');
        break;
    }
  }
  
  if(count($result_tokens['node'])) {
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

  //$query->addField('n', 'status');

  /*
  if ($settings['display_settings']['show_books'] && module_exists('book')) {
    $query->leftJoin('book', 'b', 'b.nid = n.nid');
    $query->addField('b', 'bid');
  }
  if ($settings['display_settings']['show_unpublished']) {
    $query->condition('n.status', '1', '=');
  }
  
  // Content type check
  if ($allowed_content_types = array_filter($settings['content_types'])) {
    $query->condition('n.type', $allowed_content_types, 'IN');
  }
  */
  $result = $query->execute();

  foreach ($result AS $node) {
    $matches[] = array(
      'title' => $node->title,
      'description' => token_replace($result_description, array(
        'node' => $node,
      )),
      'path' => 'node/' . $node->nid,
      'group' => t('Content'),
    );
    /*
    // Add the node nid
    if ($settings['display_settings']['nid']) {
      $matches['node'][$i]['information']['nid'] = $node->nid;
    } 
    // Add the node content type
    if ($settings['display_settings']['content_type']) {
      $matches['node'][$i]['information']['content type'] = $node->content_type;
    } 
    // Add the node status
    if ($settings['display_settings']['status']) {
      $matches['node'][$i]['information']['status'] = $node->status ? t('published') : t('unpublished');
    }
    // Add the node language
    if ($settings['display_settings']['language']) {
      $matches['node'][$i]['information']['language'] = $node->language ? $node->language : '-';
    } 
    // Add the node created time
    if ($settings['display_settings']['created']) {
      $matches['node'][$i]['information']['created'] = $node->created ? format_date($node->created, 'small') : '-';
    } 
    // Add the node changed time
    if ($settings['display_settings']['changed']) {
      $matches['node'][$i]['information']['changed'] = $node->changed ? format_date($node->changed, 'small') : '-';
    } 
    
    // Add Title of the book a node belong to
    if ($settings['display_settings']['show_books'] && $node->bid) {
      $book_node = node_load($node->bid);
      $matches['node'][$i]['information']['book'] = $book_node->title;
    } 
    */
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
function _linkit_node_path_info($path, $settings) {
  // TODO: Move to linkit.module:
  // Backwards compatible with internal: links
  // $escaped_string = str_replace('internal:', '', $string)

  // Check if path is referring to a node
  if (arg(0, $path) == 'node' && is_numeric(arg(1, $path))) {
    $result_description = check_plain($settings['node']['result_description']);
    $result = db_select('node', 'n')
      ->fields('n', array('title', 'type', 'nid'))
      ->condition('n.nid', arg(1, $path))
      ->addTag('node_access')
      ->execute()
      ->fetchObject();

    if ($result) {
      // TODO: Use tokens instead
      $path_info = array(
        'title' => check_plain($result->title),
        'description' => check_plain('id: ' . $result->nid . ' · type: ' . $result->type),
      );
      return $path_info;
    }
  }
  return FALSE;
}
