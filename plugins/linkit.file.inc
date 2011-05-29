<?php

/**
 * @file
 * Extend Linkit with files.
 */

/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_file_autocomplete($string, $settings) {
  $matches = array();

  $result_description = check_plain($settings['file']['result_description']);

  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);
  
  // Get files.
  $query = db_select('file_managed', 'f')
    ->fields('f', array('uri', 'filename'))
    ->condition('f.filename', '%' . db_like($string) . '%', 'LIKE')
    ->condition('f.status' , '1')
    ->condition('f.uri', 'public://%', 'LIKE');

  // @todo Kolla rättigheter här??

  // Not all tokens are supported by Linkit.
  // Here is a list of valid tokens
  // [file:fid]
  // [file:name]
  // [file:mime]
  // [file:size]
  // [file:timestamp] (short, medium, long, since, raw, custom, [default : medium])
  // [file:owner] (Can use the user tokens, [default : name])
  
  foreach ($result_tokens['file'] AS $token => $token_value) {
    switch ($token) {
      case 'fid':
        $query->addField('f', 'fid');
        break;

      case 'mime':
        $query->addField('f', 'filemime');
        break;

      case 'size':
        $query->addField('f', 'filesize');
        break;

      case 'timestamp':
        $query->addField('f', 'timestamp');
        break;

      case 'owner':
        $query->addField('f', 'uid');
        break;
    }
  }

  // There can be tokens that is chained so we will not find them in the switch statement above.
  if(count($result_tokens['file'])) {
    if (token_find_with_prefix($result_tokens['file'], 'timestamp')) {
      $query->addField('f', 'timestamp');
    }

    if (token_find_with_prefix($result_tokens['file'], 'owner')) {
      $query->addField('f', 'uid');
    }
  }

  $result = $query->execute();

  $public_path = variable_get('file_public_path', conf_path() . '/files');
  foreach ($result AS $file) {
  
    $imageinfo = image_get_info($file->uri);
    $image = $imageinfo ? theme_image_style(array(
      'style_name' => 'linkit_thumb',
      'path' => $file->uri,
    )) : '';
    

// 'description' => $image . $file->filemime . ' · ' . format_size($file->filesize) . ($imageinfo ? ' · ' . $imageinfo['width'] . 'x' . $imageinfo['height'] . 'px' : '') .
// 								 '<br />' . $public_path . '/' . file_uri_target($file->uri),
    $matches[] = array(
      'title' => $file->filename,
      'description' => token_replace($result_description, array(
        'file' => $file,
      )),
      'path' => $public_path . '/' . file_uri_target($file->uri),
      'group' => t('Files'),
    );
  }

  return $matches;
}

/**
 * Implements hook_linkit_get_search_styled_link().
 */
function linkit_file_linkit_get_search_styled_link($string) {
  return;
}

/**
 * Implements hook_linkit_info_plugins().
 * 
 * This is used by linkit_permissions
 */
function linkit_file_linkit_info_plugins() {
  $return['linkit_file'] = array(
    'type' => 'file',
  );
  return $return;
}