
/**
 * Better Autocomplete
 * ===================
 * 
 * Provides an object for fetching autocomplete results via XMLHttpRequest
 * from a JSON resource path.
 * 
 * For usage, see below
 * 
 * @author Didrik Nordström, http://betamos.se/
 * 
 * Requirements:
 * 
 * - jQuery 1.4+
 * - A modern web browser
 */

var BetterAutocomplete;

(function ($) {

/**
 * Focus selector, required by BetterAutoComplete
 */
$.expr[':'].focus = function( elem ) {
  return elem === document.activeElement && ( elem.type || elem.href );
};

/**
 * Create an autocomplete object instance from a DOM input element by
 * providing a JSON path
 * 
 * Example usage:
 * 
 * var bac = new BetterAutocomplete($('#find'), '/ajaxcall', function(result) {
 *   $('#title').val(result.title);
 *   $('#myoption').val(result.myOption);
 * }, { getParam: 'keywords', ajaxTimeout: 10000 });
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
 * 
 * @param $input The input element wrapped in jQuery
 * @param path A path which provides JSON objects upon search. This path should
 *        print a JSON array containing result objects. Each result object
 *        should contain at least a title or description key. All other keys
 *        are for the developer to define.
 * @param callback A callback function to execute when an item selection is
 *        confirmed. The callback function recieves an argument which is the
 *        JSON object that was selected.
 * @param options An object with additional options:
 *      - charLimit (default=3) The minimum number of chars to do an AJAX call
 *      - wait (default=100) The time in ms between a key is pressed and AJAX call
 *      - getParam (default="s") The get parameter for AJAX calls: "?param=search"
 *      - ajaxTimeout (default=5000) Timeout on AJAX calls
 * @todo Check Drupal coding standards...
 */
BetterAutocomplete = function($input, path, callback, options) {
  var self = this;

  var lastRenderedSearch = '';

  var options = $.extend({
    charLimit: 3,
    wait: 250,
    getParam: 's',
    ajaxTimeout: 5000
  }, options);

  // Caching of search results
  // A key-value object, key is search string, value is a result object
  var results = {};

  // The user's current string input
  var userString = $input.val();

  var timer;

  // Turn off the browser's autocompletion
  $input
    .attr('autocomplete', 'OFF')
    .attr('aria-autocomplete', 'none');

  var $wrapper = $('<div />')
    .attr('id', 'linkit-autocomplete-wrapper')
    .insertAfter($input);

  var $resultList = $('<ul />')
    .attr('id', 'linkit-autocomplete-results')
    .width($input.innerWidth())
    .appendTo($wrapper);

  // Just toggle visibility of the results on focus/blur
  $input.focus(function() {
    self.parseResults();
    $wrapper.show();
  });
  $input.blur(function() {
    $wrapper.hide();
  });

  $input.keydown(function(event) {
    var index = self.getSelection();
    var newIndex;
    var size = $('.result', $resultList).length;
    switch (event.keyCode) {
      case 38: // Up arrow
        newIndex = Math.max(0, index-1);
        break;

      case 40: // Down arrow
        newIndex = Math.min(size-1, index+1);
        break;

      case 9:
      case 13:
        self.confirmSelection();
        return false;
    }
    // Index have changed so update selection and cancel the event
    if (typeof newIndex === 'number') {
      self.setSelection(newIndex);
      return false;
    }
  });

  $input.keyup(function() {
    clearTimeout(timer);
    // Parse always!
    self.parseResults();
    // If the results can't be displayed we must fetch them, then display
    if (self.needsFetching()) {
      timer = setTimeout(function() {
        self.fetchResults($input.val(), function(data, search) {
          results[search] = data;
          if ($input.is(':focus')) {
            self.parseResults();
          }
        });
      }, options.wait);
    }
  });

  // When the user hovers a result with the mouse, select it
  $('.result', $resultList[0]).live('mouseover', function() {
    self.setSelection($(this).data('index'));
  });

  // A result is inserted
  $('.result', $resultList[0]).live('mousedown', function() {
    self.confirmSelection();
  });

  /**
   * Select a result based on index
   * 
   * @param index The index number of the result, starting on 0
   */
  self.setSelection = function(index) {
    // TODO: Check that it's not out of bounds
    $('.result', $resultList)
      .removeClass('selected')
      .eq(index).addClass('selected');
  };

  /**
   * Get the current selection index
   * 
   * @return The index number of the result, -1 if not found
   */
  self.getSelection = function() {
    return $('.result', $resultList).index($('.result.selected', $resultList));
  };

  /**
   * Confirm a selection and call the defined callback
   */
  self.confirmSelection = function() {
    var $result = $('.result', $resultList).eq(self.getSelection());
    if ($result.length === 0) {
      return false;
    }
    // If a callback is provided, call it now
    if (typeof callback === 'function') {
      callback($result.data('result'));
    }

    // Parse once more, if the callback changed focus or content
    self.parseResults();
  };

  /**
   * Fetch results asynchronously via AJAX
   * Errors are ignored.
   * 
   * @param search The search string
   * @param callback The callback function on success. Takes two
   *        arguments: data (array of results), search string
   */
  self.fetchResults = function(search, callback) {
    $input.addClass('throbbing');
    var xhr = $.ajax({
      url: path,
      dataType: 'json',
      // Self-invoking function needed to create an object with a dynamic key
      data: (function() {
        var o = new Object();
        o[options.getParam] = search;
        return o;
      }()),
      context: search,
      timeout: options.ajaxTimeout,
      success: function(data, textStatus) {
        // TODO: Keep count of how many calls are active, when 0 remove throbber
        $input.removeClass('throbbing');
        callback(data, this);
      }
    });
  };

  /**
   * Does the current user string need fetching?
   * Checks character limit and cache.
   * 
   * @returns {Boolean} true if fetching is required
   */
  self.needsFetching = function() {
    var userString = $input.val();

    if (userString.length < options.charLimit) {
      return false;
    }
    else if (results[userString] instanceof Array) {
      return false;
    }
    else {
      return true;
    }
  };

  /**
   * Checks if needed to re-render etc
   */
  self.parseResults = function() {
    // Check if already rendered
    if (lastRenderedSearch === $input.val()) {
      $wrapper.show();
      return;
    }
    $wrapper.hide();
    if (self.needsFetching()) {
      return;
    }
    lastRenderedSearch = $input.val();

    // Not in cache
    if (self.renderResults() >= 1) {
      self.setSelection(0);
      $wrapper.show();
    }
  };

  /**
   * Generate DOM result items from the current search using the results cache
   * 
   * TODO: Grouping
   */
  self.renderResults = function() {

    // Update user string
    userString = $input.val();

    $resultList.empty();

    // The result is not in cache, so there is nothing to display right now
    if (!(results[userString] instanceof Array)) {
      return -1;
    }
    var index = -1;
    for (index in results[userString]) {

      // Shortname for this result
      var result = results[userString][index];

      // If we don't have title or description, we don't have much to display
      if (typeof result.title === 'undefined' && typeof result.description === 'undefined') {
        continue;
      }
      var $result = $('<li />').addClass('result')
        .append(
            (typeof result.title !== 'undefined' ? '<h4>' + result.title + '</h4>' : '') + 
            (typeof result.description !== 'undefined' ? '<p>' + result.description + '</p>' : '')
        )
        .data('result', result) // Store the result object on this DOM element
        .data('index', index) // For quick determination of index on events
        .appendTo($resultList);
    }
    index++;
    return index;
  };
};

})(jQuery);
