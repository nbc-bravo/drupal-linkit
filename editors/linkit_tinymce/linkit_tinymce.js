// $Id$

Drupal.wysiwyg.plugins["linkit_tinymce"] = {    
  /**
   * Execute the button.
   */
  invoke: function(data, settings, instanceId) {  	
  	// Options to pass to the dialog.
		var options = { id: instanceId, content: data.content };
    // Open dialogue.
    
    Drupal.wysiwyg.instances[instanceId].openDialog(settings.dialog, options);
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