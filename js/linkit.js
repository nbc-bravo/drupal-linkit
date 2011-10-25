/**
 * @file
 * Linkit dialog functions
 */

// Create the linkit namespaces.
Drupal.linkit = Drupal.linkit || {};
Drupal.linkit.editorDialog = Drupal.linkit.editorDialog || {};

(function ($) {

Drupal.behaviors.linkit = {
  attach: function(context, settings) {

  $('#linkit-modal #edit-link', context).keydown(function(ev) {
    if (ev.keyCode == 13) {
      // Prevent browsers from firing the click event on the first submit
      // button when enter is used to select from the autocomplete list.
      return false;
    }
  });

    var $searchInput = $('#linkit-modal #edit-search', context);

    // Create a "Better Autocomplete" object, see betterautocomplete.js
    $searchInput.betterAutocomplete('init', settings.linkit.autocompletePath,
      settings.linkit.autocomplete,
      { // Callbacks
      select: function(result) {
        // Only change the link text if it is empty
        if (typeof result.disabled != 'undefined' && result.disabled) {
          return false;
        }
        Drupal.linkit.dialog.populateLink(result.title, result.path);
      },
      constructURL: function(path, search) {
        return path + encodeURIComponent(search);
      }
    });

    if (context === window.document) {
      //$searchInput.focus();
    }

    // Open IMCE
    $('#linkit-imce').click(function() {
      Drupal.linkit.dialog.openFileBrowser();
      return false;
    });
  }
};

// Create the linkitSelection variable.
Drupal.linkitSelection = {};

/**
 * Set the editor object.
 */
Drupal.linkit.setEditor = function (editor) {
  Drupal.linkitSelection.editor = editor;
};

/**
 * Set the editor name (ckeidor or tinymce).
 */
Drupal.linkit.setEditorName = function (editorname) {
  Drupal.linkitSelection.editorName = editorname;
};

/**
 * Set the name of the field that has triggerd Linkit.
 */
Drupal.linkit.setEditorField = function (editorfield) {
  Drupal.linkitSelection.editorField = editorfield;
};

/**
 * Set the current selection object.
 */
Drupal.linkit.setEditorSelection = function (selection) {
  Drupal.linkitSelection.selection = selection;
};

/**
 * Set the selected element based on the selection.
 */
Drupal.linkit.setEditorSelectedElement = function (element) {
  Drupal.linkitSelection.selectedElement = element;
};

/**
 * Set the selected text based on the selection.
 */
Drupal.linkit.setEditorSelectedText = function (text) {
  Drupal.linkitSelection.selectedText = text;
};

/**
 * Set the ranges based on the selection.
 */
Drupal.linkit.setSelectionRange = function (ranges) {
  Drupal.linkitSelection.selectionRange = ranges;
};

/**
 * Get the linkitSelection object.
 */
Drupal.linkit.getLinkitSelection = function () {
  return Drupal.linkitSelection;
};

})(jQuery);
