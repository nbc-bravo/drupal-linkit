(function ($) {

Drupal.behaviors.linkit = {

  attach: function(context, settings) {

    var $searchInput = $('#linkit #edit-search', context);

    // Create a "Better Autocomplete" object, see betterautocomplete.js
    $searchInput.betterAutocomplete('init', settings.linkit.autocompletePath,
      { // Options
        charLimit : settings.linkit.advanced.charlimit,
        wait : settings.linkit.advanced.wait,
        ajaxTimeout : settings.linkit.advanced.ajaxtimeout
      },
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
      },
      constructURL: function(path, search) {
        return path + encodeURIComponent(search);
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
Drupal.linkit.populateLink = function(text, path) {
  $('#linkit').data('text', text);
  $('#linkit #edit-path').val(path);
  $('#linkit #edit-search').blur();
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

/**
 * Show a message if there is no selection.
 */
Drupal.linkit.noselection = function() {
  var info_text = Drupal.t('<em class="notice">Notice: No selection was found, your link text will appear as the item title you are linking to.</em>');
  $('#linkit-dashboard-form').prepend(info_text);
};

})(jQuery);
