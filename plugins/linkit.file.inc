<?php 

/**
 * @file
 * Extend Linkit with files.
 */


/**
 * Implements hook_linkit_load_plugins().
 */
function _linkit_file_autocomplete($string) {
  $matches = array();
  
  // Get files.
  $result = db_select('file_managed', 'f')
    ->fields('f', array('filename', 'uri', 'filemime', 'filesize'))
    ->condition('f.filename', '%' . db_like($string) . '%', 'LIKE')
    ->condition('f.status' , '1')
    ->condition('f.uri', 'public://%', 'LIKE')
    ->execute();

  $public_path = variable_get('file_public_path', conf_path() . '/files');
  foreach ($result AS $file) {
  
    if (in_array($file->filemime, array('image/jpg', 'image/png', 'image/gif'))) {
      $image = theme_image_style(array(
        'style_name' => 'linkit_thumb',
        'path' => $file->uri,
      ));
      $imageinfo = image_get_info($file->uri);
    }
    
    $matches[] = array(
      'title' => $file->filename,
      'description' => $image . $file->filemime . ' · ' . format_size($file->filesize) . ($imageinfo ? ' · ' . $imageinfo['width'] . 'x' . $imageinfo['height'] . 'px' : '') .
      								 '<br />' . $public_path . '/' . file_uri_target($file->uri),
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