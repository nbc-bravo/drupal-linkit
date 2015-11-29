Linkit
===========

Linkit provides an **enriched linking experience for internal and external
linking** with editors by using an autocomplete field. Linkit links to all
entities by default.


Key features
------------

* Search content with an autocomplete field.
* Basic support for all entities.
* Token support for result descriptions.
* Settings are handled by profiles, similar to the profiles of the WYSIWYG
module. Thus, it is possible to customize the behavior of Linkit in detail.


Installation
------------

* Normal module installation procedure. See
  https://www.drupal.org/documentation/install/modules-themes/modules-8
* Create a linkit profile. (See Configuration)
* Enable the Linkit plugin on the text format you want to use. Formats are found
  at `admin/config/content/formats`.


Configuration
------------

After the installation, you have to create a Linkit profile. The profile will
contain information about which plugins to use.
Profiles can be created at `/admin/config/content/linkit`


Plugins
------------

There are plugin implementation examples in the linkit_test module bundled with
Linkit core.
