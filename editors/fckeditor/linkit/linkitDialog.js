
var dialog  = window.parent ;
var oEditor = dialog.InnerDialogLoaded() ;

var FCK        = oEditor.FCK ;
var FCKLang    = oEditor.FCKLang ;
var FCKConfig  = oEditor.FCKConfig ;
var FCKRegexLib  = oEditor.FCKRegexLib ;
var FCKTools  = oEditor.FCKTools ;

dialog.SetAutoSize( true ) ;

// Activate the "OK" button.
dialog.SetOkButton( true ) ;
var oLink = dialog.Selection.GetSelection().MoveToAncestorNode( 'A' ) ;

var selection = "";
if(oEditor.FCK.EditorDocument.selection != null) {
  selection = oEditor.FCK.EditorDocument.selection.createRange().text;
} else {
  selection = oEditor.FCK.EditorWindow.getSelection(); // after this, won't be a string
  selection = "" + selection; // now a string again
}

(function ($) {
  $(document).ready(function() {
    // Hide the form buttons as FCK provide us with thier owns.
    $('#edit-cancel, #edit-insert').hide();

    $('*', document).keydown(function(ev) {
      if (ev.keyCode == 13) {
        // Prevent browsers from firing the click event on the first submit
        // button when enter is used to select.
        return false;
      }
    });

    if ( oLink ) {
      FCK.Selection.SelectNode( oLink ) ;
      $('#edit-text').val($(oLink).html());
      $('#edit-path').val($(oLink).attr('href'));
      $('#edit-title').val($(oLink).attr('title'));
      $('#edit-id').val($(oLink).attr('id'));
      $('#edit-class').val($(oLink).attr('class'));
      $('#edit-rel').val($(oLink).attr('rel'));
      $('#edit-accesskey').val($(oLink).attr('accesskey'));
    } else if(selection == "") {
      // Show help text when there is no selection element
    } 
    else {
      $('#edit-text').val(selection);
    }
  });
})(jQuery);

// The OK button was hit.
function Ok() {
  var sInnerHtml ;

  (function ($) {
    var link_path = $('#edit-path').val();
    var link_text = $('#edit-text').val();

    if ( link_path.length == 0 ) {
      alert(Drupal.t('No URL'));
      return false ;
    }

    oEditor.FCKUndo.SaveUndoStep();

    // If no link is selected, create a new one (it may result in more than one link creation).
    var aLinks = oLink ? [ oLink ] : oEditor.FCK.CreateLink( link_path, true ) ;
    
    // If no selection, no links are created, so use the uri as the link text
    var aHasSelection = ( aLinks.length > 0 ) ;
    if ( !aHasSelection )
    {
      if (link_text)
        sInnerHtml = link_text;  // use matched path

      // Create a new (empty) anchor.
      aLinks = [ oEditor.FCK.InsertElement( 'a' ) ] ;
    }
    
    for ( var i = 0 ; i < aLinks.length ; i++ )
    {
      oLink = aLinks[i] ;

      if ( aHasSelection )
        sInnerHtml = oLink.innerHTML ;    // Save the innerHTML (IE changes it if it is like an URL).

      oLink.href = link_path ;
      SetAttribute( oLink, '_fcksavedurl', link_path ) ;

      oLink.innerHTML = sInnerHtml ;    // Set (or restore) the innerHTML

      // Let's set the "id" only for the first link to avoid duplication.
      if ( i == 0 )
        SetAttribute( oLink, 'id', $('#edit-id').val() ) ;

      // Advances Attributes
      SetAttribute( oLink, 'title', $('#edit-title').val() ) ;
      SetAttribute( oLink, 'rel', $('#edit-rel').val() ) ;
      SetAttribute( oLink, 'accesskey', $('#edit-accesskey').val() ) ;
      SetAttribute( oLink, 'class', $('#edit-class').val() ) ;
    }

    // Select the (first) link.
    oEditor.FCKSelection.SelectNode( aLinks[0] );
  })(jQuery);
  return true ;
}

function SetAttribute( element, attName, attValue )
{
  if ( attValue == null || attValue.length == 0 )
    element.removeAttribute( attName, 0 ) ;      // 0 : Case Insensitive
  else
    element.setAttribute( attName, attValue, 0 ) ;  // 0 : Case Insensitive
}

function GetAttribute( element, attName, valueIfNull )
{
  var oAtt = element.attributes[attName] ;

  if ( oAtt == null || !oAtt.specified )
    return valueIfNull ? valueIfNull : '' ;

  var oValue = element.getAttribute( attName, 2 ) ;

  if ( oValue == null )
    oValue = oAtt.nodeValue ;

  return ( oValue == null ? valueIfNull : oValue ) ;
}