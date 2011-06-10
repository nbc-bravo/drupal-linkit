<?php 

/**
 * @file
 * Extend Linkit with taxonomy links.
 * @todo Rename to just term?
 */

/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_taxonomy_autocomplete($string, $settings) {
  $matches = array();

  $result_description = check_plain($settings['taxonomy']['result_description']);

  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);

  // Build default query
  $query = db_select('taxonomy_term_data', 't')
    ->fields('t', array('tid', 'name'))
    ->condition('t.name' , '%' . db_like($string) . '%' , 'LIKE')
    ->addTag('term_access');

  // Not all tokens are supported by Linkit.
  // Here is a list of valid tokens
  // [term:tid]
  // [term:name]
  // [term:vocabulary] (vid, name, [default : name])
  // [term:parent]
  if (isset($result_tokens['term'])) {
    foreach ($result_tokens['term'] AS $token => $token_value) {
      switch ($token) {
        case 'vocabulary':
          $query->addField('t', 'vid');
          break;
      }
    }

    // There can be tokens that is chained so we will not find them in the switch statement above.
    if (token_find_with_prefix($result_tokens['term'], 'vocabulary')) {
      $query->addField('t', 'vid');
    }
  }

  // Get terms
  $result = $query->execute();
  foreach ($result as $term) {
    $uri = entity_uri('taxonomy_term', taxonomy_term_load($term->tid));
    $matches[] = array(
      'title' => $term->name,
      'description' => token_replace($result_description, array(
        'term' => $term,
      )),
      'path' => $uri['path'],
      'group' => t('Terms'),
    );
  }
  return  $matches;
}

/**
 * Implements hook_linkit_get_search_styled_link().
 */
function linkit_taxonomy_linkit_get_search_styled_link($string) {
  // Check to see that the link really is a term link
  // Backwards compatible with internal: links
  $escaped_string = str_replace('internal:', '', $string);
  $splitted_string = explode('/', $escaped_string);
  if ($splitted_string[0] != 'taxonomy' && $splitted_string[0] != 'forum') {
    return;
  }
  
  // This is a term link created with Linkit, try to grab the title and path now.
  $result = db_select('taxonomy_term_data', 't')
    ->fields('t', array('name'))
    ->condition('t.tid', $splitted_string[count($splitted_string)-1])
    ->addTag('term_access')
    ->execute()
    ->fetchObject();
  
  // No reault was found
  if (!$result) {
    return;
  }
  return check_plain($result->name) . ' [path:' . $escaped_string . ']';
}