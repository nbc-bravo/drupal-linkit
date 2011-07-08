<?php

/**
 * @file
 * Extend Linkit with files.
 */

/**
 * The autocomplete callback function for the Linkit file plugin.
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
    ->condition('f.status' , FILE_STATUS_PERMANENT);

  // Not all tokens are supported by Linkit.
  // Here is a list of valid tokens
  // [file:fid]
  // [file:name]
  // [file:mime]
  // [file:size]
  // [file:timestamp] (short, medium, long, since, raw, custom, [default : medium])
  // [file:owner] (Can use the user tokens, [default : name])

  if (isset($result_tokens['file'])) {
    foreach ($result_tokens['file'] as $token => $token_value) {
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
    if (token_find_with_prefix($result_tokens['file'], 'timestamp')) {
      $query->addField('f', 'timestamp');
    }

    if (token_find_with_prefix($result_tokens['file'], 'owner')) {
      $query->addField('f', 'uid');
    }
  }

  $result = $query->execute();

  $public_path = variable_get('file_public_path', conf_path() . '/files');
  foreach ($result as $file) {
    if (_linkit_file_access($file->uri)) {
      $imageinfo = image_get_info($file->uri);
      $image = $imageinfo ? theme_image_style(array(
        'style_name' => 'linkit_thumb',
        'path' => $file->uri,
      )) : '';
/*
// 'description' => $image . $file->filemime . ' · ' . format_size($file->filesize) . ($imageinfo ? ' · ' . $imageinfo['width'] . 'x' . $imageinfo['height'] . 'px' : '') .
// '<br />' . $public_path . '/' . file_uri_target($file->uri),
*/
      $matches[] = array(
        'title' => $file->filename,
        'description' => token_replace($result_description, array(
          'file' => $file,
        )),
        'path' => $public_path . '/' . file_uri_target($file->uri),
        'group' => t('Files'),
      );
    }
  }

  return $matches;
}

/**
 * The path info callback function for the Linkit file plugin.
 *
 * If the path given is a public file, then return information about that file.
 *
 * @see linkit.api.php
 */
function _linkit_file_path_info($path_info, $settings) {
  $public_path = variable_get('file_public_path', conf_path() . '/files');

  // TODO: In path info, this variable should already exist, to avoid duplication
  preg_match('~^' . preg_quote($public_path, '~') . '/(.+)$~', $path_info['requested_path'], $matches);
  $filepath = $matches[1];
  if (!empty($filepath)) {

    $query = db_select('file_managed', 'f')
      ->fields('f', array('uri', 'filename'))
      ->condition('f.status' , '1')
      ->condition('f.uri', 'public://' . $filepath);

    $db_result = $query->execute();

    if ($file = $db_result->fetch()) {
      if (_linkit_file_access($file->uri)) {
        // TODO: Use tokens instead, by sharing functionalty with _linkit_file_autocomplete
        $result = array(
          'title' => check_plain($file->filename),
          'description' => check_plain('herro'),
          'path' => $public_path . '/' . file_uri_target($file->uri),
        );
        return $result;
      }
    }
  }
  return FALSE;
}

/**
 * Ensure that the user have download access to the file, If the user does not
 * have access to download the file, then the user should not be able to link
 * to it.
 *
 * @param $uri
 *   The URI of the file.
 * @return
 *   If the user does not have permission to access the file, return FALSE.
 *   If the user has permission, return TRUE.
 */
function _linkit_file_access($uri) {
  $scheme = file_uri_scheme($uri);
  if (file_stream_wrapper_valid_scheme($scheme) && file_exists($uri)) {
    // Headers is not used for file transport here, it is just so we can see if
    // the user have access to the file.
    $headers = array();
    foreach (module_implements('file_download') as $module) {
      $function = $module . '_file_download';
      $result = $function($uri);
      if ($result == -1) {
        return FALSE;
      }
      if (isset($result) && is_array($result)) {
        $headers = array_merge($headers, $result);
      }
    }
    if (count($headers)) {
      return TRUE;
    }
  }
  return FALSE;
}