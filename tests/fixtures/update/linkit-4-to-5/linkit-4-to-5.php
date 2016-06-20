<?php

/**
 * @file
 * Database fixture for testing the upgrade path for Linkit 4 to 5.
 *
 * Contains database additions to drupal-8.bare.standard.php.gz for testing the
 * upgrade path for Linkit 4 to 5.
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Configuration for linkit profiles.
$config = Yaml::decode(file_get_contents(__DIR__ . '/linkit.linkit_profile.test_profile.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'linkit.linkit_profile.' . $config['id'],
    'data' => serialize($config),
  ])
  ->execute();


// Configuration for text formats.
$configs = [];
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/filter.format.format_1.yml'));
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/filter.format.format_2.yml'));
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/filter.format.format_3.yml'));
foreach ($configs as $config) {
  $connection->insert('config')
    ->fields([
      'collection',
      'name',
      'data',
    ])
    ->values([
      'collection' => '',
      'name' => 'filter.format.' . $config['format'],
      'data' => serialize($config),
    ])
    ->execute();
}

// Configuration for editors.
$configs = [];
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/editor.editor.format_1.yml'));
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/editor.editor.format_2.yml'));
$configs[] = Yaml::decode(file_get_contents(__DIR__ . '/editor.editor.format_3.yml'));
foreach ($configs as $config) {
  $connection->insert('config')
    ->fields([
      'collection',
      'name',
      'data',
    ])
    ->values([
      'collection' => '',
      'name' => 'editor.editor.' . $config['format'],
      'data' => serialize($config),
    ])
    ->execute();
}
