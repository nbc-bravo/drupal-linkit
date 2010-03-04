// $Id$

Linkit = {
  openDialog: function(dialog, params) {
    var editor = tinyMCE.get(tinyMCE.activeEditor.editorId);
    editor.windowManager.open({
      file: dialog.url + '/' + this.field,
      width: dialog.width,
      height: dialog.height,
      inline: 1,
      scrollbars: dialog.scrollbars
    }, params);
  }
};

Drupal.wysiwyg.plugins["linkit_tinymce"] = {    
  /**
   * Execute the button.
   */
  invoke: function(data, settings, instanceId) {  	
  	// Options to pass to the dialog.
		var options = { id: instanceId, content: data.content, inline : true, scrollbars : true };
    // Open dialogue.
    Linkit.openDialog(settings.dialog, options);
  },
  
  isNode: function(node) {
    return ($(node).is('a'));
  }
};

