; $Id$

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * Editor support
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

EDITOR SUPPORT
-------------
With WYSIWYG module
  - TinyMCE
  - CKEditor

"Pure" editors (not using WYSIWYG module)
  - CKEditor

HOOKS
-------------

The autocomplete field is extendeble, so you can easy extend it with your own plugins.
There is two hooks that MUST be defined if you want to extend the autocomplete field.

- hook_linkit_load_plugins()
- hook_linkit_info_plugins()

HOOK EXAMPLE
-------------

/**
 * hook_linkit_load_plugins()
 *
 * This hook will extend the linkit module autocompele field with your own
 * matches.
 */
function MYMODULENAME_linkit_load_plugins($string) {
  $matches = array();
  
  // Get foo´s
  $result = db_query_range("SELECT foo, bar FROM {foo_table} WHERE LOWER(foo) LIKE LOWER('%%%s%%')", $string, 0, 10);
  while ($foo = db_fetch_object($result)) {
    $matches['MYMODULETYPE'][linkit_autolist_val($foo->foo, 'internal:' . $foo->path)] = linkit_autolist_list($foo->foo, 'Foos');
  }
  return $matches;
}

For alias link, use "base_path().$foo->path" instead of "'internal:' . $foo->path"


/**
 * Implementation of hook_linkit_info_plugins().
 */
function MYMODULENAME_linkit_info_plugins() {
  $return['MYMODULENAME'] = array(
    'type' => 'MYMODULETYPE',
  );
  return $return;
}