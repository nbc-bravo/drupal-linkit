// $Id$

Drupal.linkit = Drupal.linkit || {};
Drupal.linkit.plugins = Drupal.linkit.plugins || {};

Drupal.behaviors.linkit_tinymce_dialog = function (context) {
  
  var parentWindow = (window.opener) ? window.opener : window.parent;

  if (parentWindow && parentWindow.Drupal) {

    var pta = parentWindow.tinyMCE.activeEditor;
    var content = parentWindow.tinyMCE.activeEditor.selection.getContent();
    var selection = parentWindow.tinyMCE.activeEditor.selection;
    var basePath = parentWindow.Drupal.settings.basePath;
    var href = '';    
    var selection_node = pta.dom.getParent(selection.getNode(), 'A');
    
    // If we have a selected an A tag, get the attriebures
    if(selection_node != null) {
      $('#edit-link').val($(selection_node).attr('href'));
      $('#edit-title').val($(selection_node).attr('title'));
      $('#edit-id').val($(selection_node).attr('id'));
      $('#edit-class').val($(selection_node).attr('class'));
      $('#edit-rel').val($(selection_node).attr('rel'));
      $('#edit-accesskey').val($(selection_node).attr('accesskey'));
    }

    $('#edit-insert').click(function() {

      if($('#edit-link', context).val() == "") {
        alert(Drupal.t('Link to: is empty'));
        return false;
      }
    
      var matches = $('#edit-link', context).val().match(/\[path:(.*)\]/i);
      href = (matches == null) ? $('#edit-link', context).val() : matches[1];

      setLink();
      return false;
    });

    $('#edit-cancel').click(function() {
      parentWindow.Drupal.wysiwyg.instances[parentWindow.Drupal.wysiwyg.activeId].closeDialog(window);
      return false;
    });
  }

  function setLink() {

    var e = pta.dom.getParent(selection.getNode(), 'A');

    // Create new anchor elements
    if (e == null) {
      pta.execCommand("CreateLink", false, href);
      pta.dom.setAttribs($(pta.dom.select("a")), {
        'href'      : href,
        'title'     : $('#edit-title').val(),
        'id'        : $('#edit-id').val(),
        'class'     : $('#edit-class').val(),
        'rel'       : $('#edit-rel').val(),
        'accesskey' : $('#edit-accesskey').val()
      });
    } 
    else {
      pta.dom.setAttribs(e, {
        'href'      : href,
        'title'     : $('#edit-title').val(),
        'id'        : $('#edit-id').val(),
        'class'     : $('#edit-class').val(),
        'rel'       : $('#edit-rel').val(),
        'accesskey' : $('#edit-accesskey').val()
      });
    }

    // Close the popup window
    parentWindow.Drupal.wysiwyg.instances[parentWindow.Drupal.wysiwyg.activeId].closeDialog(window);
  }
};