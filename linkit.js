(function ($) {

Drupal.behaviors.linkit = {

  attach: function(context, settings) {

    var $searchInput = $('#linkit #edit-search', context);

    // Create a "Better Autocomplete" object, see betterautocomplete.js
    $searchInput.betterAutocomplete('init', Drupal.settings.linkit.autocompletePath,
      {}, // Options
      { // Callbacks
      select: function(result) {
        // Only change the link text if it is empty
        if (typeof result.disabled != 'undefined' && result.disabled) {
          return false;
        }
        Drupal.linkit.populateLink(result.title, result.path);
      },
      beginFetching: function() {
        $searchInput.addClass('throbbing');
      },
      finishFetching: function() {
        $searchInput.removeClass('throbbing');
      }
    });

    if (context === window.document) {
      // TODO: Make autofocus with html5?
      $searchInput.focus();
    }

    // Open IMCE
    $('#linkit-imce').click(function() {
      Drupal.linkit.openFileBrowser();
      return false;
    });
  }
};

Drupal.linkit = Drupal.linkit || {};

/**
 * Populate the title and path fields when a linkable object is selected
 * 
 * @param title
 *   The title of the link, only populated if title is empty
 * @param path
 *   The target path of the link
 * @param {Boolean} silent
 *   Only populate fields, do not focus and select the title field
 * 
 * @todo Create another function which populates by field name since
 * other fields may be populated by the editor.
 */
Drupal.linkit.populateLink = function(text, path, silent) {
  var silent = silent || false;
  // Only change the link text if it is empty
  $('#linkit #edit-text:text[value=""]').val(text);
  $('#linkit #edit-path').val(path);
  if (!silent) {
    $('#linkit #edit-text').focus().select();
  }
};

/**
 * Open the IMCE file browser
 */
Drupal.linkit.openFileBrowser = function () {
  window.open(decodeURIComponent(Drupal.settings.linkit.IMCEurl), '', 'width=760,height=560,resizable=1');
};

/**
 * When a file is inserted through IMCE, this function is called
 * See IMCE api for details
 * 
 * @param file
 *   The file object that was selected inside IMCE
 * @param win
 *   The IMCE window object
 */
Drupal.linkit.IMCECallback = function(file, win) {
  // TODO: Retrieve public files path by adding it to Drupal.settings
  Drupal.linkit.populateLink(file.name, win.imce.decode(Drupal.settings.linkit.publicFilesDirectory + '/' + file.relpath));
  win.close();
};

})(jQuery);
