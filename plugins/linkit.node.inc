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
  $matches = array();
  
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

  $i = 0;
  foreach ($result AS $node) {
    $matches[$i] = array(
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
    $i++;
  }

  return $matches;
}

/**
 * Implements hook_linkit_get_search_styled_link().
 */
function linkit_node_linkit_get_search_styled_link($string) {
  // Check to see that the link really is a node link
  // Backwards compatible with internal: links
  $escaped_string = str_replace('internal:', '', $string);
  $splitted_string = explode('/', $escaped_string);
  if ($splitted_string[0] != 'node') {
    return;
  }

  // This is a node link created with Linkit, try to grab the title and path now. 
  $result = db_select('node', 'n')
    ->fields('n', array('title'))
    ->condition('n.nid', $splitted_string[1])
    ->addTag('node_access')
    ->execute()
    ->fetchObject();

  // No reault was found
  if (!$result) {
    return;
  }
  return check_plain($result->title) . ' [path:' . $escaped_string . ']';
}

/**
 * Implements hook_linkit_info_plugins().
 * 
 * This is used by linkit_permissions
 */
function linkit_node_linkit_info_plugins() {
  $return['linkit_node'] = array(
    'type' => 'node',
  );
  return $return;
}
