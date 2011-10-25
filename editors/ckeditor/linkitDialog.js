/**
 * @file
 * Linkit ckeditor dialog helper.
 */

Drupal.linkit.editorDialog.ckeditor = {};

(function ($) {

Drupal.linkit.editorDialog.ckeditor = {
  init : function() {
    var linkitSelection = Drupal.linkit.getLinkitSelection();
    var plugin = CKEDITOR.plugins.linkit;

    var selectedElement = null;
    var selectedText = '';

    // Get the selected text based on the selection.
    if (linkitSelection.selection.getType() == CKEDITOR.SELECTION_TEXT) {
      if (CKEDITOR.env.ie) {
        linkitSelection.selection.unlock(true);
        selectedText = linkitSelection.selection.getNative().createRange().text;
      }
      else {
        selectedText = linkitSelection.selection.getNative();
      }
    }
    // Save the selected text.
    Drupal.linkit.setEditorSelectedText(selectedText.toString());

    // Get the selected element based on the selection.
    if ((selectedElement = plugin.getSelectedLink()) && selectedElement.hasAttribute('href')) {
      linkitSelection.selection.selectElement(selectedElement);
    }
    else {
      selectedElement = null;
    }

    // Save the selected element.
    Drupal.linkit.setEditorSelectedElement(selectedElement);
  },

  /**
   * Prepare the dialog after init.
   */
  afterInit : function () {
    var linkitSelection = Drupal.linkit.getLinkitSelection();

    // If we have selected an element, grab the elements attributes.
    if(linkitSelection.selectedElement) {
      $('#linkit-modal #edit-path').val(linkitSelection.selectedElement.getAttribute('href'));
      // Set values from selection.
      $('#linkit-modal #edit-attributes .linkit-attribute').each(function() {
        $(this).val(linkitSelection.selectedElement.getAttribute($(this).attr('name')));
      });
    }
    else if(linkitSelection.selection.getNative().isCollapsed) {
      // No text and no element is selected.
      Drupal.linkit.dialog.noselection();
    }
    else {
     // Text is selected.
    }
  },

  /**
   * Prepare the insert.
   */
  insertLink : function() {
    var linkitSelection = Drupal.linkit.getLinkitSelection();
    // Get the data from the form.
    this.getData();

    //If no path, just close this window.
    if(this.data.attributes.href == "") {
      alert(Drupal.t('There is no path.'));
      $('#linkit-modal #edit-search').focus();
      return false;
    }
    // Ok, we have a path, lets make a link of it and insert.
    else {
      CKEDITOR.tools.callFunction(linkitSelection.editor._.linkitFnNum, this.data, linkitSelection.editor);
      Drupal.linkit.dialog.close();
      return false;
    }
  },

  /**
   * Get data from the form.
   */
  getData : function () {
    var linkitSelection = Drupal.linkit.getLinkitSelection();
    var data = {};
    data.attributes = {};

    $("#linkit-modal #edit-attributes .linkit-attribute").each(function() {
      if($(this).val() != "") {
        data.attributes[$(this).attr('name')] = $(this).val();
      }
    });

    data.attributes['href'] = $("#linkit-modal #edit-path").val();
    data.text = linkitSelection.selection.getNative().isCollapsed ? $('#linkit-modal').data('text') : linkitSelection.selectedText;

    this.data = data;
  }
};

})(jQuery);