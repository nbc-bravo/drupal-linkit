<?php
/**
 * @file
 */
$plugin = array(
  'name' => t('Pattern'),
  'javascript' => drupal_get_path('module', 'linkit') . '/plugins/linkit_insert/linkit_insert_patter/linkit_inster_pattern.js',
  'handler' => array(
    'class' => 'LinkitInsertPatternPlugin',
    'file' => 'linkit_insert_pattern.class.php',
  )
);