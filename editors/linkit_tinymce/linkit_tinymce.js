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
    console.log(settings);
    Linkit.openDialog(settings.dialog, options);
  },

  attach: function(content, settings, instanceId) {
    $('#edit-body_linkit_tinymce').removeClass('mceButtonEnabled').addClass('mceButtonDisabled');
    function addEvents(win) {
      if (win.contentWindow) {
        win = win.contentWindow;
      }
      $(win.document).bind("mouseup", function() {
      var selection = this.getSelection();
        if(selection == "") {
          $('#edit-body_linkit_tinymce').removeClass('mceButtonEnabled').addClass('mceButtonDisabled');
        } else {
          $('#edit-body_linkit_tinymce').removeClass('mceButtonDisabled').addClass('mceButtonEnabled');
        }
      });
      
    }

    var frames = document.getElementsByTagName("iframe");
    for (var i=0; i < frames.length; i++) {
      addEvents(frames[i]);
    }

    return content;
  }

};