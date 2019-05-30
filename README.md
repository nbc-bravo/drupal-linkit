INTRODUCTION
------------
Linkit provides an **enriched linking experience for internal and 
external linking** with editors by using an autocomplete field. Linkit 
has by default support for nodes, users, taxonomy terms, files, 
comments and **basic support for all types of entities** that defines a 
canonical link template.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/linkit

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/linkit

REQUIREMENTS
------------

No special requirements


INSTALLATION
------------

* Normal module installation procedure. See
https://www.drupal.org/documentation/install/modules-themes/modules-8

* **Enable Linkit**
To enable Linkit, go to `/admin/config/content/formats` and edit the 
desired text format you want to enable Linkit for. Linkit will alter 
the default link plugin, so make sure that it is enabled. When the 
default link plugin is enabled, you will have to select a Linkit 
profile to use in the "Drupal link" tab under the toolbar configuration.

* **Enable Linkit filter**
Linkit will insert URLs in a format like "entity:node/1". The Linkit 
filter will then transform that URL into a "real" URL when rendering 
the text. **Note: The Linkit filter must run before "Limit allowed HTML 
tags and correct faulty HTML"**.

* If the **Limit allowed HTML tags and correct faulty HTML** filter is
enabled, add *data-entity-type* and *data-entity-uuid* to the `<a>` tag
in the *Allowed HTML* tags (i.e. `<a href data-entity-type data-entity-uuid>`).

* (optional) **Use automatic titles with the Linkit filter**
If automatic titles is enabled in the Linkit filter settings, and
**Limit allowed HTML tags and correct faulty HTML** is enabled, add
*title*  to the `<a>` tag in the *Allowed HTML* tags (i.e.
`<a href data-entity-type data-entity-uuid title>`).

* (optional) **Automatically set the "download" attribute for Media entities**
_Only for Media entity links that has the substitution type "Direct URL to 
media file entity"._ If this option is enabled in the Linkit filter settings, 
and **Limit allowed HTML tags and correct faulty HTML** is enabled, add
*download* to the `<a>` tag in the *Allowed HTML* tags (i.e.
`<a href data-entity-type data-entity-uuid download>`).


CONFIGURATION
-------------

A default Linkit profile will have been installed as a step in the 
module installation process. The profile will contain information about 
which plugins to use.

You can create additional profiles at `/admin/config/content/linkit`.


PLUGIN EXAMPLES
---------------

There are plugin implementation examples in the linkit_test module 
bundled with Linkit core.


MAINTAINERS
-----------

Current maintainers:
 * Emil Stjerneman (anon) - https://www.drupal.org/user/464598
