<?php

/**
 * Implements hook_linkit_plugins "path info callback".
 *
 * If the path given is an absolute URL, then return information about it.
 *
 * @see linkit.api.php
 */
function _linkit_external_path_info($path_info, $settings) {
  $settings['timeout'] = 5;
  // TODO: Scraping should perhaps respect robots.txt?
  // TODO: Should not default to true, since scraping can be sensitive
  $settings['fetch_document'] = TRUE;
  // Check if path is referring to an http resource
  if (!function_exists('curl_init') || !$path_info['safe_url']) {
    return FALSE;
  }
  $ch = curl_init($path_info['safe_url']);
  // TODO: More help on what options should be set
  // E.g. is there a gurantee for that redirects won't add unsafe data, like passwords?
  // And what happens when it's a huge file? A binary file? Maybe it's the best without body...
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_FOLLOWLOCATION => TRUE,
    CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    CURLOPT_TIMEOUT => $settings['timeout'],
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_USERAGENT => 'Linkit/2.x Drupal/7.x',
  ));
  if (!$settings['fetch_document']) {
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
  }
  $document = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curl_errno = curl_errno($ch);
  // CURL error
  if ($curl_errno) {
    switch ($curl_errno) {
      case CURLE_COULDNT_RESOLVE_HOST:
        $status = 'Can not resolve host name ' . $path_info['host'];
        break;
      case CURLE_COULDNT_CONNECT:
        $status = 'Can not connect to host ' . $path_info['host'];
        break;
      case CURLE_OPERATION_TIMEDOUT:
        $status = 'Operation timed out after ' . $settings['timeout'] . ' seconds';
        break;
      default:
        $status = 'An internal error occured.';
    }
    $class = 'status-warning';
  }
  else {
    $status = _linkit_external_http_status($http_code);
    if ($http_code >= 200 && $http_code < 300) {
      $class = 'status-ok';
    }
    else {
      $class = 'status-warning';
    }
    if ($settings['fetch_document']) {
      // TODO: Maybe document should be shortened, for performance reasons
      // TODO: Title should definitely be shortened if too long
      // Try retrieve title attribute
      preg_match('~<title>(.+)</title>~', $document, $matches);
      $title = $matches[1];
    }
  }
  $result = array(
    'path' => $path_info['url'],
    'title' => check_plain($title ? $title : $path_info['url']),
    'description' => $status,
    'addClass' => $class,
  );
  curl_close($ch);
  return $result;
}

/**
 * Retrieve text status about a certain http status codes.
 * The most common HTTP status codes have a human readable
 * text string
 *
 * @param $http_code
 *   The HTTP status code
 *
 * @return
 *   A simple text string with information about the status
 */
function _linkit_external_http_status($http_code) {
  if ($http_code >= 200 && $http_code < 300) {
    // Success
    $status = 'Connection successful. HTTP status: ' . $http_code . ($http_code == 200 ? ' (OK)' : '');
  }
  elseif ($http_code >= 300 && $http_code < 400) {
    // Redirect
    $status = 'Too many redirects. HTTP status: ' . $http_code;
  }
  elseif ($http_code >= 400 && $http_code < 500) {
    // Client error
    switch ($http_code) {
      case 403:
        $human = 'Forbidden';
        break;
      case 404:
        $human = 'Page not found';
        break;
    }
    $status = 'Client error. HTTP status: ' . $http_code . ($human ? ' (' . $human . ')' : '');
  }
  elseif ($http_code >= 500 && $http_code < 600) {
    // Server error
    $status = 'Server error. HTTP status: ' . $http_code . ($http_code == 500 ? ' (Internal server error)' : '');
  }
  else {
    $status = 'An error occured. HTTP status: ' . $http_code;
  }
  return $status;
}
