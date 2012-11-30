/**
 * @file
 * Linkit dashboard functions
 */

(function ($) {

Drupal.behaviors.linkitDashboard = {
  attach: function (context, settings) {
    // Bind the insert link button.
    $('.linkit-insert', context).click(function() {
      var linkitCache = Drupal.linkit.getLinkitCache();
      // Call the insertLink() function.
      //Drupal.linkit.getDialogHelper(linkitCache.helper).insertLink(Drupal.linkit.dialog.getLink());
      // Close the dialog.
      Drupal.linkit.modalClose;
      return false;
    });

    // Bind the close link.
    $('#linkit-cancel', context).bind('click', Drupal.linkit.modalClose);

    // Run required field validation.
    Drupal.linkit.requiredFieldsValidation();

    // Make the profile changer
    Drupal.linkit.profileChanger(context);
  }
};

/**
 * Check for mandatory fields in the form and disable for submissions
 * if any of the fields are empty.
 */
Drupal.linkit.requiredFieldsValidation = function() {
  var allowed = true;
  $('#linkit-modal .required').each(function() {
    if (!$(this).val()) {
      allowed = false;
      return false;
    }
  });
  if (allowed) {
    $('#linkit-modal .linkit-insert')
      .removeAttr('disabled')
      .removeClass('form-button-disabled');
  }
  else {
    $('#linkit-modal .linkit-insert')
      .attr('disabled', 'disabled')
      .addClass('form-button-disabled');
  }
};

/**
 * Populate fields on the dashboard.
 *
 * @param link
 *   An object with the following properties (all are optional):
 *   - path: The anchor's href.
 *   - attributes: An object with additional attributes for the anchor element.
 */
Drupal.linkit.populateFields = function(link) {
  link = link || {};
  link.attributes = link.attributes || {};

  $('#linkit-modal .linkit-path-element').val(link.path);

  $.each(link.attributes, function(name, value) {
    $('#linkit-modal .linkit-attributes .linkit-attribute-' + name).val(value);
  });

  // Run required field validation.
  Drupal.linkit.requiredFieldsValidation();
};

Drupal.linkit.profileChanger = function(context) {
  $('#linkit-profile-changer > div.form-item', context).once('linkit-change-profile', function() {
    var target = $(this);
    var toggler = $('<div id="linkit-profile-changer-toggler"></div>')
    .html(Drupal.t('Change profile'))
    .click(function() {
      target.slideToggle();
    });
    $(this).after(toggler);
  });

  $('#linkit-profile-changer .form-radio', context).each(function() {
    var id = $(this).attr('id');
    var profile = $(this).val();
    if (typeof Drupal.ajax[id] != 'undefined') {
      // @TODO: Jquery 1.5 accept success setting to be an array of functions.
      // But we have to wait for jquery to get updated in Drupal core.
      // In the meantime we have to override it.
      Drupal.ajax[id].options.success = function (response, status) {
        if (typeof response == 'string') {
          response = $.parseJSON(response);
        }

        // Call the ajax success method.
        Drupal.ajax[id].success(response, status);
        $('#linkit-profile-changer > div.form-item').slideToggle();

        //Drupal.linkitCacheAdd('profile', profile);

        // Update the autocomplete url.
        Drupal.settings.linkit.autocompletePathParsed = Drupal.settings.linkit.autocompletePath.replace('___profile___', profile);
      };
    }
  });
};

Drupal.behaviors.linkitSearch = {
  attach: function(context, settings) {
    $('.linkit-search-element', context).once('linkit-search', function() {
      // Create a synonym for this to reduce code confusion.
      var searchElement = $(this);

      var callbacks = {
        constructURL: function(path, search) {
          return path + encodeURIComponent(search);
        },

        insertSuggestionList: function($results, $input) {
          var top = $input.position().top + $input.outerHeight() - 5;
          $results.width($input.outerWidth()).css({
            position: 'absolute',
            left: $input.position().left,
            top: top,
            // High value because of other overlays like
            // wysiwyg fullscreen (TinyMCE) mode.
            zIndex: 211000,
            maxHeight: $(window).height() - (top + 20)
          })
          .hide()
          .insertAfter($input);
        },

        select: function(result) {
          if(typeof result == 'undefined') {
            return false;
          }
          // Only change the link text if it is empty
          if (typeof result.disabled != 'undefined' && result.disabled) {
            return false;
          }

          Drupal.linkit.populateFields({
            path: result.path
          });

          // Store the result title (Used when no selection is made bythe user).
          //Drupal.linkitCacheAdd('link_tmp_title', result.title);

          $('.linkit-path-element', context).focus();
        }
      }

      searchElement.betterAutocomplete('init', settings.linkit.autocompletePathParsed, settings.linkit.autocomplete, callbacks);
    });
  }
};


})(jQuery);