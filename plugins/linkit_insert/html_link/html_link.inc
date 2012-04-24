<?php
/**
 * @file
 */
$plugin = array(
  'name' => t('HTML Link'),
  'javascript' => drupal_get_path('module', 'linkit') . '/plugins/linkit_insert/html_link/html_link.js',
  'handler' => array(
    'class' => 'LinkitInsertPluginHtmlLink',
    'file' => 'html_link.class.php',
  )
);