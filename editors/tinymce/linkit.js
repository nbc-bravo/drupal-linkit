
// TODO: rename this file, for easier debugging

var linkitDialog = {};

(function ($) {

linkitDialog = {
  init : function() {
    var ed = tinyMCEPopup.editor;

    if (e = ed.dom.getParent(ed.selection.getNode(), 'A')) {
      
      // Set values from selection.
      $('#edit-attributes .linkit-attribute').each(function() {
        $(this).val($(e).attr($(this).attr('name')));
      });

      $('#edit-path').val($(e).attr('href'));
    } else if(ed.selection.isCollapsed()) {
      // No text and no element is selected.
      Drupal.linkit.noselection();
    }
    else {
      // Text is selected.
    }
  },

  insertLink : function() {
    var ed = tinyMCEPopup.editor, e;

    tinyMCEPopup.restoreSelection();
    e = ed.dom.getParent(ed.selection.getNode(), 'A');

    if ($('#edit-path').val() == "") {
      // Remove element if there is no href
      if (e) {
        tinyMCEPopup.execCommand("mceBeginUndoLevel");
        ed.dom.remove(e, 1);
        tinyMCEPopup.execCommand("mceEndUndoLevel");
        tinyMCEPopup.close();
        return;
      }
      alert(Drupal.t('There is no path.'));
      $('#edit-search').focus();
      //tinyMCEPopup.close();
      return;
    }
    
    tinyMCEPopup.execCommand("mceBeginUndoLevel");

    var link_path = $('#edit-path').val();

    // Create new anchor elements
    if (e == null) {

      if (ed.selection.isCollapsed()) {
        tinyMCEPopup.execCommand("mceInsertContent", false, '<a href="#linkit-href#">' + $('#linkit').data('text') + '</a>');
      } else {
        tinyMCEPopup.execCommand("createlink", false, '#linkit-href#', {skip_undo : 1});
      }

      tinymce.each(ed.dom.select("a"), function(n) {
        if (ed.dom.getAttrib(n, 'href') == '#linkit-href#') {
          e = n;
          // Remove all attributes before we insert new ones.
          ed.dom.removeAllAttribs(e);
          $("#edit-attributes .linkit-attribute").each(function() {
            if($(this).val() != "") {
              ed.dom.setAttrib(e, $(this).attr('name'), $(this).val());
            }
          });

          // Set href explicit.
          ed.dom.setAttrib(e, 'href', link_path);
        }
      });
    } else {

      $("#edit-attributes .linkit-attribute").each(function() {
        if($(this).val() != "") {
          ed.dom.setAttrib(e, $(this).attr('name'), $(this).val());
        }
      });

      // Set href explicit.
      ed.dom.setAttrib(e, 'href', link_path);
    }
    // Don't move caret if selection was image
    if(e != null) {
      if (e.childNodes.length != 1 || e.firstChild.nodeName != 'IMG') {
        ed.focus();
        ed.selection.select(e);
        ed.selection.collapse(0);
        tinyMCEPopup.storeSelection();
      }
    }

    tinyMCEPopup.execCommand("mceEndUndoLevel");
    tinyMCEPopup.close();

  }
};


tinyMCEPopup.onInit.add(linkitDialog.init, linkitDialog);

/*
 * TODO: Shouldn't it be the other way around, i.e that these editor specific
 * scripts attaches callbacks to the Drupal.linkit object instead so that
 * these files does not need to do any DOM manipulation etc.?
 */
Drupal.behaviors.linkitInitTinyMCE =  {
  attach: function(context, settings) {
    $('#edit-link', context).keydown(function(ev) {
      if (ev.keyCode == 13) {
        // Prevent browsers from firing the click event on the first submit
        // button when enter is used to select from the autocomplete list.
        return false;
      }
    });
    $('#edit-insert', context).click(function() {
      linkitDialog.insertLink();
      return false;
    });

    $('#linkit #cancel', context).click(function() {
      tinyMCEPopup.close();
    });
  }
};

})(jQuery);