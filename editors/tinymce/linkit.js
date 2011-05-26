
// TODO: rename this file, for easier debugging

var linkitDialog = {};

(function ($) {
linkitDialog = {
	init : function() {
  	var ed = tinyMCEPopup.editor;
  	// Setup browse button
  	if (e = ed.dom.getParent(ed.selection.getNode(), 'A')) {
  	
  	  // Delete the anchor from the URL, this will be added later on anyway
  	  var href = $(e).attr('href');
  	
  	  if(href.length > 0) {
  		  // linkitHelper.search_styled_link(href);
  			} 
  	  $('#edit-title').val($(e).attr('title'));
  	  return false;
  	}
  	// TODO: Change all linkitHelper references to
  	// Drupal.linkit.<new_function_name>
  	// linkitHelper.populateTitle(ed.selection.getContent());
  	Drupal.linkit.populateLink(ed.selection.getContent(), '', true);
	},
  
  insertLink : function() {
    var ed = tinyMCEPopup.editor, e;

    tinyMCEPopup.restoreSelection();
		e = ed.dom.getParent(ed.selection.getNode(), 'A');
    
    // Remove element if there is no href
		if ($('#edit-link--2').val() == "") {
			if (e) {
				tinyMCEPopup.execCommand("mceBeginUndoLevel");
				ed.dom.remove(e, 1);
				tinyMCEPopup.execCommand("mceEndUndoLevel");
				tinyMCEPopup.close();
				return;
			}
      tinyMCEPopup.close();
		  return;
		}
    
    tinyMCEPopup.execCommand("mceBeginUndoLevel");
		
    var matches = $('#edit-link--2').val().match(/\[path:(.*)\]/i);
    href = (matches == null) ? $('#edit-link--2').val() : matches[1];
    
    // Add anchor if we have any and make sure there is no "#" before adding the anchor
    // But do not add if there is an anchor in the URL
    var anchor = $('#edit-anchor').val().replace(/#/g,'');
    var hasAnchor = $('#edit-link--2').val().match(/\#/i);
    
    if(anchor.length > 0 && hasAnchor == null ) {
      href = href.concat('#' + anchor);
    }

    var link_text_matches = $('#edit-link--2').val().match(/(.*)\[path:.*\]/i);
    link_text = (link_text_matches == null) ? $('#edit-link--2').val() : link_text_matches[1].replace(/^\s+|\s+$/g, '');

    // Create new anchor elements
		if (e == null) {
      
      if (ed.selection.isCollapsed()) {
        tinyMCEPopup.execCommand("mceInsertContent", false, '<a href="#linkit-href#">' + link_text + '</a>');
      } else {
        tinyMCEPopup.execCommand("createlink", false, '#linkit-href#', {skip_undo : 1});
      }
        
			tinymce.each(ed.dom.select("a"), function(n) {
				if (ed.dom.getAttrib(n, 'href') == '#linkit-href#') {
					e = n;

					ed.dom.setAttribs(e, {
						'href'      : href,
            'title'     : $('#edit-title').val(),
            'id'        : $('#edit-id').val(),
            'class'     : $('#edit-class').val(),
            'rel'       : $('#edit-rel').val(),
            'accesskey' : $('#edit-accesskey').val()
					});
				}
			});
		} else {
			ed.dom.setAttribs(e, {
				'href'      : href,
        'title'     : $('#edit-title').val(),
        'id'        : $('#edit-id').val(),
        'class'     : $('#edit-class').val(),
        'rel'       : $('#edit-rel').val(),
        'accesskey' : $('#edit-accesskey').val()
			});
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