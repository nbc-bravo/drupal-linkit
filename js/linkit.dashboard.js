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
    Drupal.linkit.requiredFieldsValidation(context);

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

        };
      }
    });
}
})(jQuery);