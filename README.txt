; $Id$

-- INTRODUCTION --

Linkit provides an easy interface for internal linking. Linkit links to
nodes, users, views and terms by default, using an autocomplete field.
Linkit has two major advantages over traditional linking

 1. The user does not have to copy or remember a URL
 2. If the target node changes it's alias (e.g. if the node's menu item
    title is changed) the link will remain functional

See http://drupal.org/project/linkit for more information

-- INSTALLATION & CONFIGURATION --

 1. Install and enable Linkit's dependencies (see below). Make sure
    Path Filter is enabled on the input formats you intend to use with linkit
 2. Install and enable linkit (required) and at least one of linkit_node,
    linkit_views and linkit_taxonomy
 3. Enable the Linkit button in your WYSIWYG editor's settings

-- DEPENDENCIES --

Path Filter <http://drupal.org/project/pathfilter>
One of these editors:
 * WYSIWYG <http://drupal.org/project/wysiwyg> with TinyMCE or CKEditor (recommended)
 * CKEditor <http://drupal.org/project/ckeditor>

-- HOOKS --

The autocomplete field is extendeble, so you can easy extend it with your own plugins.
For example you may wan't to integrate a third party web service.

There are two hooks that MUST be defined if you want to extend the autocomplete field.

- hook_linkit_load_plugins()
- hook_linkit_info_plugins()

-- HOOK EXAMPLE --

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

-- MAINTAINERS --

 * anon <http://drupal.org/user/464598>
 * betamos <http://drupal.org/user/442208>
 * blackdog <http://drupal.org/user/110169>
 * freakalis <http://drupal.org/user/204187>
