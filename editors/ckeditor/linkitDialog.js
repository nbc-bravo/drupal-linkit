
/**
 * @file Linkit ckeditor dialog helper.
 */

(function ($) {

var LinkitDialog = {};

LinkitDialog = {
  init : function() {
    this.getEditor();
    this.getSelection();
    this.afterInit();
  },

  /**
   * Get CKEDITOR and current instance name.
   */
  getEditor : function () {
    if (typeof(dialogArguments) != 'undefined') {
      var CKEDITOR = dialogArguments.opener.CKEDITOR;
      var name = dialogArguments.editorname;
    } else {
      var CKEDITOR = window.opener.CKEDITOR;
      var name = decodeURI((RegExp('editorname=(.+?)(&|$)').exec(location.search)||[,null])[1]);
    }

    this.CKEDITOR = CKEDITOR;
    this.instance_name = name;

    //Get the editor instance.
    this.editor = this.CKEDITOR.instances[this.instance_name];
  },

  /**
   * Get CKEDITOR and current instance name.
   */
  getSelection : function () {
    var selectedText = '';
    var selection = this.editor.getSelection();
    if (selection.getType() == this.CKEDITOR.SELECTION_TEXT) {
      if (this.CKEDITOR.env.ie) {
        selection.unlock(true);
        selectedText = selection.getNative().createRange().text;
      }
      else {
        selectedText = selection.getNative();
      }
    }

    this.selectedText = selectedText.toString();
    this.selection = selection;
    
    //Get the selected element.
    var ranges = this.selection.getRanges();
    var element;

    if (ranges.length == 1) {
      var rangeRoot = ranges[0].getCommonAncestor(true);
      element = rangeRoot.getAscendant('a', true);
    }

    this.selectedElement = element;
  },

  /**
   * Prepare the dialog after init.
   */
  afterInit : function () {
    // If we have selected an element, grab the elements attributes.
    if(this.selectedElement) {
      $('#edit-path').val(this.selectedElement.getAttribute('href'));
      var selectedElement = this.selectedElement;
      // Set values from selection.
      $('#edit-attributes input').each(function() {
        $(this).val(selectedElement.getAttribute($(this).attr('name')));
      });
    }
    else if(this.selection.getNative().isCollapsed) {
      // No text and no element is selected.
      // @TODO: Insert title if no text or element is selected?
    }
    else {
     // Text is selected.
    }
  },

  /**
   * Prepare the insert.
   */
  insertLink : function() {
    // Get the data from the form.
    this.getData();

    //If no path, just close this window.
    if(this.data.attributes.href == "") {
      alert(Drupal.t('There is no path.'));
      $('#edit-search').focus();
      return false;
    }
    // Ok, we have a path, lets make a link of it and insert.
    else {
      this.CKEDITOR.tools.callFunction(this.editor._.linkitFnNum, this.data, this.editor);
      window.close();
    }
  },

  /**
   * Get data from the form.
   */
  getData : function () {
    var data = {};
    data.attributes = {};

    $("#edit-attributes input").each(function() {
      if($(this).val() != "") {
        data.attributes[$(this).attr('name')] = $(this).val();
      }
    });
    
    data.attributes['href'] = $("#edit-path").val();
    data.text = this.selectedText;

    this.data = data;
  }
};

$(document).ready(function() {
  LinkitDialog.init();
  $('#edit-link').keydown(function(ev) {
    if (ev.keyCode == 13) {
      // Prevent browsers from firing the click event on the first submit
      // button when enter is used to select from the autocomplete list.
      return false;
    }
  });
  
  $('#edit-insert').click(function() {
    LinkitDialog.insertLink();
    return false;
  });

  $('#cancel').click(function() {
    window.close();
  });
});

})(jQuery);