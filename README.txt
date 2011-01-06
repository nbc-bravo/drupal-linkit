; $Id$

-- INTRODUCTION --

Linkit provides an easy interface for internal linking. Linkit links to
nodes, users, files and terms by default, using an autocomplete field.
Linkit has two major advantages over traditional linking

 1. The user does not have to copy or remember a URL
 2. If the target node changes it's alias (e.g. if the node's menu item
    title is changed) the link will remain functional

See http://drupal.org/project/linkit for more information

-- INSTALLATION --

 1. Install and enable linkit and at least one of linkit_node,
    linkit_views, linkit_taxonomy or linkit_user
 2. Enable the Linkit button in your WYSIWYG editor's settings
 3. If you are using stand alone CKEditor, there is 
    some more installation information in the editor dir. 
      * CKEditor (/sites/all/modules/linkit/editors/ckeditor/README.txt)


-- DEPENDENCIES --

Linkit dont have any "real" dependencies, but without these modules you dont 
get the fully functionality.

To begin, we need an editor. Linkit supports all of these editors:
 * WYSIWYG <http://drupal.org/project/wysiwyg> with TinyMCE or CKEditor
   or FCKeditor
 * CKEditor <http://drupal.org/project/ckeditor>

Linkit creates internal links with the "internal:" prefix. To make your site 
understand these links, you have to install Pathologic.
* Pathologic <http://drupal.org/project/pathologic> 

Make sure Pathologic is enabled on the input formats you intend to use with linkit

-- CONFIGURATION --

No additional configuration is necessary though you may fine-tune settings at
Configuration -> Linkit settings (/admin/config/content/linkit).

To administrate this settings, you need "administer linkit" premission (/admin/people/permissions) or be user 1.

-- MAINTAINERS --

 * anon <http://drupal.org/user/464598>