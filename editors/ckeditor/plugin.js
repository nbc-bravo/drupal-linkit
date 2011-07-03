/**
 * @file Plugin for inserting links with Linkit
 */
(function ($) {
  CKEDITOR.plugins.add( 'Linkit', {

    requires : [ 'fakeobjects', 'htmlwriter' ],
      
    init: function( editor ) {

      // Add Button
      editor.ui.addButton( 'Linkit', {
        label: 'Linkit',
        command: 'Linkit',
        icon: this.path + 'linkit.png'
      });
      // Add Command
      editor.addCommand( 'Linkit', {
        exec : function () {
          var path = (Drupal.settings.linkit.url.wysiwyg_ckeditor) ? Drupal.settings.linkit.url.wysiwyg_ckeditor : Drupal.settings.linkit.url.ckeditor
          if (window.showModalDialog) {
            var media = window.showModalDialog(path, { 'opener' : window, 'editorname' : editor.name }, "dialogWidth:750px; dialogHeight:320px; center:yes; resizable:yes; help:no;");
          } else {
            var media = window.open(path + (path.indexOf('?') == -1 ? '?' : '&') + 'editorname='+encodeURI(editor.name), null, "width=750,height=320,resizable,alwaysRaised,dependent,toolbar=no,location=no,menubar=no");
          }
        }
      });
      
      // Register an extra fucntion, this will be used in the popup.
      editor._.linkitFnNum = CKEDITOR.tools.addFunction( insertLink, editor );
    }
    
  });

  function insertLink(data, editor) {
    this.fakeObj = false;

    var selection = editor.getSelection(),
    ranges = selection.getRanges(),
    element = null;

    // Fill in all the relevant fields if there's already one link selected.
    if (ranges.length == 1) {
      var rangeRoot = ranges[0].getCommonAncestor(true);
      element = rangeRoot.getAscendant('a', true);
      
      if(element && element.getAttribute('href')) {
        selection.selectElement(element);
      }
      else if((element = rangeRoot.getAscendant('img', true)) && element.getAttribute('_cke_real_element_type') && element.getAttribute('_cke_real_element_type') == 'anchor') {
        this.fakeObj = element;
        element = editor.restoreRealElement(this.fakeObj);
        selection.selectElement(this.fakeObj);
      }
      else
        element = null;
    }

    // Record down the selected element in the dialog.
    this._.selectedElement = element;
    
    if ( !this._.selectedElement ) {
      // Create element if current selection is collapsed.
      var selection = editor.getSelection(), ranges = selection.getRanges();
      
      if ( ranges.length == 1 && ranges[0].collapsed ) {
        var text = new CKEDITOR.dom.text( data.text, editor.document );
        ranges[0].insertNode( text );
        ranges[0].selectNodeContents( text );
        selection.selectRanges( ranges );
      }

      // Insert into editor
      var style = new CKEDITOR.style( { element : 'a', attributes : data.attributes } );
      style.type = CKEDITOR.STYLE_INLINE;
      style.apply( editor.document );
    }
    else {
      // We're only editing an existing link, so just overwrite the attributes.
      var element = this._.selectedElement;

      var removeAttributes = [];

      for ( var i = 0 ; i < element.$.attributes.length ; i++ ) {
        removeAttributes.push(element.$.attributes[i].localName);
      }

      // Remove all attributes so we can update them
      element.removeAttributes(removeAttributes);

      // Set params from form
      element.setAttributes(data.attributes);
     
      if (this.fakeObj) {
        editor.createFakeElement(element, 'cke_anchor', 'anchor').replace(this.fakeObj);
      }
      delete this._.selectedElement;
    }
  }

})(jQuery);
