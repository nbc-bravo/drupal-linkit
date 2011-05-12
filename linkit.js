
/*
 * Linkit javascript lib 
 */
 var linkit_helper = {};

(function ($) {
linkitHelper = {
  /*
   * Makes an AJAX request when a link is about to be edited with Linkit
   */
  search_styled_link : function(string) {
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
  }, 

  /*
   * Show helper text
   * If there is no selection, the link text will be the result title.
   */
  populateTitle : function(title) {
    $('#linkit #edit-text').val(title);
  },
  /*
   * IMCE integration
   */
  openFileBrowser : function () {
    window.open(decodeURIComponent(Drupal.settings.linkit.IMCEurl), '', 'width=760,height=560,resizable=1');
  },
  
  /*
   * See if the link contains a #anchor
   */
  findAnchor : function(href) {
    var matches = href.match(/.*(#.*)/i);
    anchor = (matches == null) ? '' : matches[1].substring(1);
    return anchor;
  }
};

Drupal.behaviors.linkitImce =  {
  attach: function(context, settings) {
    $('#linkit-imce').click(function() {
      linkit_helper.openFileBrowser();
      return false;
    });
  }
};

/**
 * Create an autocomplete instance from a DOM input element by providing a JSON
 * path 
 * 
 * $input The input element wrapped in jQuery
 * path A path which provides JSON objects upon search
 * callback A callback function to execute when an item selection is confirmed.
 *          The callback function recieves an argument which is the JSON object
 *          that was selected.
 * options An object with additional options, which can contain these keys:
 *   - characterLimit (default=3) The minimum number of chars for an AJAX call
 *   - wait (default=100) The time in ms between a key is pressed and AJAX call
 *   - ajaxTimeout (default=5000) Timeout on AJAX calls
 * 
 * The DOM tree will look like this:
 * 
 * input (text input field, provided as the $input argument)
 * div#linkit-autocomplete-wrapper (no width/height, position relative)
 *   ul#linkit-autocomplete-results (fixed width, variable height)
 *     li.result (variable height)
 *       h4.title (contains the title)
 *       p.description (contains the description)
 *     li.result (more results...)
 */
var AutoCompleteObject = function($input, path, callback, options) {
  var self = this;

  var options = $.extend({
    characterLimit: 3,
    wait: 100,
    ajaxTimeout: 5000
  }, options);

  // A key-value object with keys as the string and value as the JSON result
  // object
  var results = {};

  // The user's current string input
  var userString = $input.val();

  // Turn off the browser's autocompletion
  $input.attr('autocomplete', 'OFF').attr('aria-autocomplete', 'none');

  var $resultList = $('<ul />').attr('id', 'linkit-autocomplete-results').width($input.innerWidth());
  var $wrapper = $('<div />').append($resultList).attr('id', 'linkit-autocomplete-wrapper').insertAfter($input);
  
  $input.focus(function() {
    $wrapper.show();
  });
  $input.blur(function() {
    $wrapper.hide();
    return false;
  });
  $input.keyup(function() {
    if (!self.displayResults())
      self.fetchResults($input.val());
  });
  
  self.setStatus = function(string) {
    self.status = string;
    switch (string) {
    case 'loading':
      $input.addClass('throbbing');
      break;
    }
  };

  /**
   * Fetch results asynchronously via AJAX
   * 
   * @param search The search string
   * @param callback The callback function to
   */
  self.fetchResults = function(search, callback) {
    $input.addClass('throbbing');
    var xhr = $.ajax({
      // TODO: move uri
      url: path,
      dataType: 'json',
      data: {s: search},
      context: search,
      timeout: options.ajaxTimeout,
      success: function(res, textStatus) {
        results[this] = res;
        self.activeCall = null;
        // TODO: Make a callback method instead
        self.displayResults();
        $input.removeClass('throbbing');
      }
    });
  };
  
  /**
   * Put the mouse cursor in this autocomplete field
   */
  self.focus = function() {
    $input.focus();
  };

  /**
   * Display results from a certain string
   * Returns true if displayed properly
   */
  self.displayResults = function() {

    // Update user string
    userString = $input.val();
    
    $resultList.empty();

    if (userString.length < options.characterLimit) {
      $wrapper.hide();
      return true;
    }

    // The result is not in cache, so there is nothing to display right now
    if (typeof results[userString] !== 'object') {
      $wrapper.hide();
      return false;
    }
    for (i in results[userString]) {

      // Shortname for this result
      var result = results[userString][i];
      var $result = $('<li />').addClass('result')
        .append('<h4>' + result.title + '</h4><p>' + result.description + '</p>')
        .data('linkObject', result)
        .appendTo($resultList);
      $wrapper.show();
      // Select the first result
      if (i == 0) {
        $result.addClass('selected');
      }

      // When the user hovers the result with the mouse, select it
      // TODO: perhaps use live() instead?
      $result.mouseover(function() {
        $('.result.selected', $resultList).removeClass('selected');
        $(this).addClass('selected');
      });
      
      // A result is inserted
      // TODO: Move most code to new method self.selectResult(id) so it can also be keyboard navigated
      $result.mousedown(function() {
        // If a callback is provided, call it now
        if (typeof callback === 'function')
          callback($(this).data('linkObject'));
        // Clear the input field
        $input.val('');
        // And remove results TODO: maybe call this method recursively instead?
        $resultList.empty();
      });
    }
    return true;
  };
};

Drupal.behaviors.linkitAutocomplete = {
  attach: function(context, settings) {
    var aco = new AutoCompleteObject($('#linkit #edit-search', context), 'http://d7.dev/linkit/autocomplete', function(linkObject) {
      // Select callback is executed when an object is chosen
      // Only change the link text if it is empty
      $('#linkit #edit-text:text[value=""]').val(linkObject.title);
      $('#linkit #edit-path').val(linkObject.path);
    });
    if (context === window.document) {
      // TODO: Make autofocus with html5?
      aco.focus();
    }
  }
};

})(jQuery);