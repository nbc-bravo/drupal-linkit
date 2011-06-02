(function ($) {

Drupal.behaviors.linkit = {

  attach: function(context, settings) {

    var $searchInput = $('#linkit #edit-search', context);

    // Create a better autocomplete objects, see betterautocomplete.js
    // TODO: Retrieve an absolute path through the Drupal.settings
    var bac = new BetterAutocomplete($searchInput, Drupal.settings.linkit.autocompletePath, function(linkObject) {
      // Select callback is executed when an object is chosen
      // Only change the link text if it is empty
      Drupal.linkit.populateLink(linkObject.title, linkObject.path);
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
 * Makes an AJAX request when a link is about to be edited with Linkit
 * 
 * @todo Major rewrite!
 */
Drupal.linkit.fetchPathInfo = function(string) {
  $('#linkit .form-item-link input').hide();
  $('#linkit .form-item-link label').after($('<span></span>').addClass('throbber').html('<strong>' + Drupal.t('Loading path...') + '</strong>'));
  // DO AJAX!
  var result = $.get(Drupal.settings.linkit.ajaxcall, { string: string } , function(data) {
    if(data) {
      $('#linkit #edit-link--2').val(data);
      $('#linkit .form-item-link .throbber').remove();
      $('#linkit .form-item-link input').show();
    } else {
      $('#linkit #edit-link--2').val(string);
      $('#linkit .form-item-link .throbber').remove();
      $('#linkit .form-item-link input').show();
    }
  });
};

/**
 * Open the IMCE file browser
 */
Drupal.linkit.openFileBrowser = function () {
  window.open(decodeURIComponent(Drupal.settings.linkit.IMCEurl), '', 'width=760,height=560,resizable=1');
};

/**
 * Find and return the #anchor from the path field
 * 
 * @return
 *   The anchor name, without the # or null if no anchor
 */
Drupal.linkit.getAnchor = function(href) {
  var matches = href.match(/#(.*)$/i);
  return (matches == null) ? null : matches[1];
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
