; $Id$

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * Hooks
 * Hooks example


INTRODUCTION
------------

Current Maintainers: 
anon <http://drupal.org/user/464598>
blackdog <http://drupal.org/user/110169>
freakalis <http://drupal.org/user/204187>

Linkit module provides an easy way for users to link to internal nodes, views and files by search with an autocomplete field.


INSTALLATION
------------

Install as usual, see http://drupal.org/node/70151 for further information.

CONFIGURATION
-------------


HOOKS
-------------

The autocomplete field is extendeble, so you can easy extend it with your own plugins.
There is two hooks that MUST be defined if you want to extend the autocomplete field.

- hook_linkit_load_plugins()
- hook_linkit_info_plugins()


HOOK EXAMPLES
-------------

/**
 * hook_linkit_load_plugins()
 *
 * This hook will extend the linkit module autocompele field with your own
 * matches.
 */
function MYMODULENAME_linkit_load_plugins($string) {
  // Add my JS file that will handle the TinyMCE insert/update
  drupal_add_js(drupal_get_path('modules', 'MYMODULENAME').'/MYMODULE_JSFILE.js');
  $matches = array();
  
  // Get foo´s
  $result = db_query_range("SELECT foo, bar FROM {foo_table} WHERE LOWER(foo) LIKE LOWER('%%%s%%')", $string, 0, 10);
  while ($foo = db_fetch_object($result)) {
    $matches['MYMODULENAME'][_linkit_autolist_val($foo->foo, 'Foos', 'path:'.$node->filepath)] = _linkit_autolist_list($foo->foo, 'Foos');
  }
  return $matches;
}

/**
 * Implementation of hook_linkit_info_plugins().
 */
function MYMODULENAME_linkit_info_plugins() {
  $return['MYMODULENAME'] = array(
    'type' => 'foo',
  );
  return $return;
}