<?php

/**
 * @file
 * Extend Linkit with taxonomy links.
 */

/**
 * Build the default node query.
 */
function _linkit_taxonomy_create_base_query($profile) {
  // Get terms
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'taxonomy_term')
        ->addTag('term_access');

  if ($profile->data['taxonomy']['group_by_vocabulary']) {
    $query->propertyOrderBy('vid', 'ASC');
  }

  // Add the default sort.
  $query->propertyOrderBy('name', 'ASC');

  return $query;
}

/**
 * Build the group string for the term.
 */
function _linkit_taxonomy_build_group($profile, $term) {
  if ($profile->data['taxonomy']['group_by_vocabulary']) {
    $vocabulary = taxonomy_vocabulary_load($term->vid);
    return t('Terms (%vocabulary_name)', array('%vocabulary_name' => check_plain($vocabulary->name)));
  }
  else {
    return t('Terms');
  }
}

/**
 * The autocomplete callback function for the Linkit taxonomy plugin.
 */
function _linkit_taxonomy_autocomplete($string, $profile) {
  $matches = array();

  $query = _linkit_taxonomy_create_base_query($profile);

  // Add the name condition to the query object.
  $query->propertyCondition('name', '%' . db_like($string) . '%', 'LIKE')
        ->addTag('linkit_taxonomy_autocomplete');

  // Execute the query.
  $result = $query->execute();
  $term_tids = array_keys($result['taxonomy_term']);

  // Load all the terms with all the tids we got.
  $terms = entity_load('taxonomy_term', $term_tids);

  foreach ($terms as $term) {
    // Create the URI for the entity.
    $uri = entity_uri('taxonomy_term', $term);

    $matches[] = array(
      'title' => $term->name,
      'description' => token_replace(check_plain($profile->data['taxonomy']['result_description']), array(
        'term' => $term,
      )),
      'path' => url($uri['path'], array('alias' => TRUE)),
      'group' => _linkit_taxonomy_build_group($profile, $term),
    );
  }
  return  $matches;
}

/**
 * The path info callback function for the Linkit taxonomy plugin.
 *
 * If the path given is a taxonomy term, then return information about that term.
 *
 * @see linkit.api.php
 */
function _linkit_taxonomy_path_info($path_info, $profile) {
  // Define the default taxonomy URI's first argument (arg(0)).
  $taxonomy_uri_array = array('taxonomy', 'forum');

  if (!isset($path_info['system_path'])) {
    return FALSE;
  }

  // Check if path is referring to a taxonomy
  if (in_array(arg(0, $path_info['system_path']), $taxonomy_uri_array)) {
    // We need to know where the tid is.
    switch (arg(0, $path_info['system_path'])) {
      case 'taxonomy':
        // URI = taxonomy/term/[tid]
        $arg_i = 2;
        break;
      case 'forum':
        // URI = forum/[tid]
        $arg_i = 1;
        break;
    }

    $tid = arg($arg_i, $path_info['system_path']);

    // Load the term with the corresponding tid.
    $terms = entity_load('taxonomy_term', array($tid));
    $term = $terms[$tid];

    if ($term) {
      $result = array(
        'title' => check_plain($term->name),
        'description' => token_replace(check_plain($profile->data['taxonomy']['result_description']), array(
          'term' => $term,
        )),
      );
      return $result;
    }

  }
  return FALSE;
}