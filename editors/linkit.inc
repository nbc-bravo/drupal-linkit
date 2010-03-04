<?php
// $Id$

/**
 * @file
 * Wysiwyg API integration.
 */

/**
 * Implementation of hook_wysiwyg_plugin().
 */
function linkit_linkit_plugin() {
  $plugins['linkit_tinymce'] = array(
    'title' => t('Linkit (TinyMCE)'),
    'vendor url' => 'http://drupal.org/project/linkit',
    'icon file' => 'linkit.png',
    'icon title' => t('Insert a link with the linkitmodule.'),
    'settings' => array(
    	'dialog' => array(
    		'title' => '',
				'url' => url('admin/linkit/dashboard'),
				'width' => 640,
				'height' => 480,
        'scrollbars' => TRUE,
			),
    ),
  );
  return $plugins;
}