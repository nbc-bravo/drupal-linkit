<?php

/**
 * @file
 * Extend Linkit with files.
 */

/**
 * Build the default node query.
 */
function _linkit_file_create_base_query($profile, $result_tokens) {
  // Get files.
  $query = db_select('file_managed', 'f')
    ->fields('f', array('uri', 'filename'))
    ->condition('f.status' , FILE_STATUS_PERMANENT)
    ->addTag('linkit_file');
    // @todo: Make sure we dont include files that are temporary://.

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

  if ($profile['file']['group_by_scheme']) {
    $query->orderBy('f.uri', 'ASC');
  }

  // Add the default sort.
  $query->orderBy('f.filename', 'ASC');

  return $query;
}

/**
 * Build the description string for the file.
 */
function _linkit_file_build_description($profile, $result_description, $file) {
  $description_array = array();

  if ($profile['file']['image_extra_info']['thumbnail'] || $profile['file']['image_extra_info']['dimensions']) {
    $imageinfo = image_get_info($file->uri);
  }

  if ($profile['file']['image_extra_info']['thumbnail']) {
    $image = $imageinfo ? theme_image_style(array(
      'style_name' => 'linkit_thumb',
      'path' => $file->uri,
    )) : '';
  }

  if ($profile['file']['image_extra_info']['dimensions']) {
    $description_array[] = ($imageinfo ? ' ' . $imageinfo['width'] . 'x' . $imageinfo['height'] . 'px ' : '');
  }

  $description_array[] = token_replace($result_description, array(
    'file' => $file,
  ));

  if ($profile['file']['show_scheme']) {
    $description_array[] = file_uri_scheme($file->uri) . '://';
  }

  $description = (isset($image) ? $image : '') . implode(' - ' , $description_array);

  return $description;
}

/**
 * Build the group string for the file.
 */
function _linkit_file_build_group($profile, $file) {
  if ($profile['file']['group_by_scheme']) {
    // Get all stream wrappers.
    $stream_wrapper = file_get_stream_wrappers();
    return t('Files (%stream_wrapper)', array('%stream_wrapper' => t($stream_wrapper[file_uri_scheme($file->uri)]['name'])));
  }
  else {
    return t('Files');
  }
}

/**
 * The autocomplete callback function for the Linkit file plugin.
 */
function _linkit_file_autocomplete($string, $profile) {
  $matches = array();

  $result_description = check_plain($profile['file']['result_description']);

  // Build a list of all token-like patterns that appear in the text.
  $result_tokens = token_scan($result_description);

  // Build the base query.
  $query = _linkit_file_create_base_query($profile, $result_tokens);
  $query->condition('f.filename', '%' . db_like($string) . '%', 'LIKE');
  $result = $query->execute();

  foreach ($result as $file) {
    if (_linkit_file_access($file->uri)) {
      $matches[] = array(
        'title' => $file->filename,
        'description' => _linkit_file_build_description($profile, $result_description, $file),
        'path' => _linkit_file_get_url($file->uri),
        'group' => _linkit_file_build_group($profile, $file),
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
function _linkit_file_path_info($path_info, $profile) {
  $public_path = variable_get('file_public_path', conf_path() . '/files');

  // TODO: In path info, this variable should already exist, to avoid duplication
  preg_match('~^' . preg_quote($public_path, '~') . '/(.+)$~', $path_info['requested_path'], $matches);
  $filepath = $matches[1];
  if (!empty($filepath)) {
    $result_description = check_plain($profile['file']['result_description']);

    // Build a list of all token-like patterns that appear in the text.
    $result_tokens = token_scan($result_description);

    // Build the base query.
    $query = _linkit_file_create_base_query($profile, $result_tokens);
    // @TODO: Make this work with other schemas.
    $query->condition('f.uri', 'public://' . $filepath);
    $query_result = $query->execute()->fetch();

    if ($query_result) {
      if (_linkit_file_access($query_result->uri)) {
        $result = array(
          'title' => check_plain($query_result->filename),
          'description' => _linkit_file_build_description($profile, $result_description, $query_result),
          // @TODO: Make this work with other schemas.
          'path' => $public_path . '/' . file_uri_target($query_result->uri),
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


/**
 * Get an relative url to the file.
 *
 * We don't want to use the "getExternalUrl()" provided by the stream wrappers
 * as this functions return an absolute paths.
 *
 * @param $uri
 *   The URI of the file.
 * @return
 *   A relative URL to the file.
 */
function _linkit_file_get_url($uri) {
  $scheme = file_uri_scheme($uri);
  $target = file_uri_target($uri);
  $url = module_invoke_all('linkit_get_url', $scheme, $target);
  return $url;
}

/**
 * Implements hook_linkit_get_url().
 */
function linkit_linkit_get_url($scheme, $target) {
  switch ($scheme) {
    case 'public':
      $directory = variable_get('file_public_path', conf_path() . '/files');
      $path = str_replace('\\', '/', $target);
      return base_path() . $directory . '/asdasdasd' . drupal_encode_path($path);
      break;

    case 'private':
      $directory = variable_get('file_private_path', '');
      $path = str_replace('\\', '/', $target);
      return url('system/files/' . $path);
      break;
  }
}