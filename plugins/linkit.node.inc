<?php

/**
 * @file
 * Extend Linkit with node links.
 */

/**
 * Build the default node query.
 */
function _linkit_node_create_base_query($profile) {
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
        ->addTag('node_access');

  // Content type check.
  if ($allowed_content_types = array_filter($profile->data['node']['content_types'])) {
    $query->entityCondition('bundle', $allowed_content_types);
  }

  // Status condition.
  if (!isset($profile->data['node']['include_unpublished'])) {
    $query->propertyCondition('status', 1);
  }

  // Sort the results by the entity bundle if we are grouping by bundle.
  if ($profile->data['node']['group_by_content_type']) {
    $query->propertyOrderBy('type', 'ASC');
  }

  // Add the default sort.
  $query->propertyOrderBy('title', 'ASC');

  return $query;
}

/**
 * Build the group string for the node.
 */
function _linkit_node_build_group($profile, $node) {
  $group = t('Content');
  if ($profile->data['node']['group_by_content_type']) {
    $type_name = node_type_get_name($node);
    $group .= ' · ' . check_plain($type_name);
  }
  return $group;
}

/**
 * The autocomplete callback function for the Linkit node plugin.
 */
function _linkit_node_autocomplete($string, $profile) {
  $matches = array();

  // Build the base query.
  $query = _linkit_node_create_base_query($profile);

  // Add the title condition to the query object.
  $query->propertyCondition('title', '%' . db_like($string) . '%', 'LIKE')
        ->addTag('linkit_node_autocomplete');

  // Execute the query.
  $result = $query->execute();
  $node_nids = array_keys($result['node']);

  // Load all the nodes with all the nids we got.
  $nodes = entity_load('node', $node_nids);

  foreach ($nodes as $node) {
    $title_suffix = '';
    $row_class = '';
    if (isset($profile->data['node']['include_unpublished']) && $node->status == NODE_NOT_PUBLISHED) {
      $title_suffix = '<span class="status"> - ' . t('unpublished') . '</span>';
      $row_class = 'unpublished-node';
    }

    // Create the URI for the entuty.
    $uri = entity_uri('node', $node);

    $matches[] = array(
      'title' => $node->title . $title_suffix,
      'description' => token_replace(check_plain($profile->data['node']['result_description']), array(
        'node' => $node,
      )),
      // We have to set alias to TRUE as we don't want an alias back.
      'path' => url($uri['path'], array('alias' => TRUE)),
      'group' => _linkit_node_build_group($profile, $node),
      'addClass' => $row_class,
    );
  }
  return $matches;
}

/**
 * The path info callback function for the Linkit node plugin.
 *
 * If the path given is a node item, then return information about that node.
 *
 * @see linkit.api.php
 */
function _linkit_node_path_info($path_info, $profile) {
  // Check if path is referring to a node
  if (isset($path_info['system_path']) && arg(0, $path_info['system_path']) == 'node' && is_numeric(arg(1, $path_info['system_path']))) {

    $nid = arg(1, $path_info['system_path']);

    // Build the base query.
    // We can't just load the node here as we also need to attach the profile
    // settings to the query.
    $query = _linkit_node_create_base_query($profile);

    // Add the nid condition to the query object.
    $query->propertyCondition('nid', $nid);

    // Execute the query.
    $result = $query->execute();

    // If we for some reason don't get our node nid, return false.
    if (!isset($result['node'][$nid]->nid)) {
      return FALSE;
    }

    // Load the node with the corresponding nid.
    $nodes = entity_load('node', array($result['node'][$nid]->nid));
    $node = $nodes[$nid];
    if ($node) {
      $result = array(
        'title' => check_plain($node->title),
        'description' => token_replace(check_plain($profile->data['node']['result_description']), array(
          'node' => $node,
        )),
      );
      return $result;
    }
  }
  return FALSE;
}
