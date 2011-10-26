/**
 * @file
 * Plugin for inserting links with Linkit.
 */

(function ($) {
  CKEDITOR.plugins.add( 'Linkit', {

    requires : [ 'fakeobjects', 'htmlwriter' ],

    init: function( editor ) {

      // Add Button.
      editor.ui.addButton( 'Linkit', {
        label: 'Linkit',
        command: 'Linkit',
        icon: this.path + 'linkit.png'
      });

      // Add Command.
      editor.addCommand( 'Linkit', {
        exec : function () {
          // Set the editor object.
          Drupal.linkit.setEditor(editor);
          // Set which editor is calling the dialog script.
          Drupal.linkit.setEditorName('ckeditor');
          // Set the name of the editor field, this is just for CKeditor.

          Drupal.linkit.setEditorField(editor.name);

          Drupal.linkit.setEditorSelection(editor.getSelection());

          var path = (Drupal.settings.linkit.url.wysiwyg_ckeditor) ? Drupal.settings.linkit.url.wysiwyg_ckeditor : Drupal.settings.linkit.url.ckeditor;
          Drupal.linkit.dialog.buildDialog(path);
        }
      });

      // Register an extra fucntion, this will be used in the popup.
      editor._.linkitFnNum = CKEDITOR.tools.addFunction( insertLink, editor );
    }

  });

  CKEDITOR.plugins.linkit = {
    getSelectedLink : function( )
    {
      try
      {
        var linkitSelection = Drupal.linkit.getLinkitSelection();
        if ( linkitSelection.selection.getType() == CKEDITOR.SELECTION_ELEMENT )
        {
          var selectedElement = linkitSelection.selection.getSelectedElement();
          if ( selectedElement.is( 'a' ) )
            return selectedElement;
        }

        // Save the range
        Drupal.linkit.setSelectionRange(linkitSelection.selection.getRanges( true ));

        var range = linkitSelection.selection.getRanges( true )[ 0 ];
        range.shrink( CKEDITOR.SHRINK_TEXT );
        var root = range.getCommonAncestor();
        return root.getAscendant( 'a', true );
      }
      catch( e ) { return null; }
    }
  };

  function insertLink(data, editor) {
    this.fakeObj = false;
    var linkitSelection = Drupal.linkit.getLinkitSelection();
    var plugin = CKEDITOR.plugins.linkit;
    var selectedElement = null;

    this._.selectedElement = linkitSelection.selectedElement;

    if ( !this._.selectedElement ) {
      // Create element if current selection is collapsed.
      var ranges = linkitSelection.selectionRange;

      if ( ranges.length == 1 ) {
        var text = new CKEDITOR.dom.text( data.text, linkitSelection.editor.document );
        ranges[0].insertNode( text );
        ranges[0].selectNodeContents( text );
        linkitSelection.selection.selectRanges( ranges );
      }

      // Insert into editor.
      var style = new CKEDITOR.style( { element : 'a', attributes : data.attributes } );
      style.type = CKEDITOR.STYLE_INLINE;
      style.apply( linkitSelection.editor.document );
    }
    else {
      // We're only editing an existing link, so just overwrite the attributes.
      var element = linkitSelection.selectedElement;

      var removeAttributes = [];

      for ( var i = 0 ; i < element.$.attributes.length ; i++ ) {
        // Remove the 'linkit_' prefix.
        var attr = element.$.attributes[i].localName.substr(7);
        removeAttributes.push(attr);
      }

      // Remove all attributes so we can update them.
      element.removeAttributes(removeAttributes);

      // Set params from .
      element.setAttributes(data.attributes);

      if (this.fakeObj) {
        editor.createFakeElement(element, 'cke_anchor', 'anchor').replace(this.fakeObj);
      }
    }

    delete this._.selectedElement;
  }

})(jQuery);
