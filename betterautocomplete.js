
/**
 * Better Autocomplete
 * ===================
 *
 * Provides a jQuery plugin for fetching autocomplete results via
 * XMLHttpRequest from a JSON resource path.
 *
 * For usage, see below
 *
 * @author Didrik Nordström, http://betamos.se/
 *
 * Requirements:
 * - jQuery 1.4+
 * - A modern web browser
 */

(function ($) {

/**
 * Create an autocomplete object instance from a DOM input element by
 * providing a JSON path
 *
 * Example usage:
 * @code
 *   var bac = new BetterAutocomplete($('#find'), '/ajaxcall', {
 *     // Options
 *     getParam: 'keywords',
 *     ajaxTimeout: 10000
 *   }, {
 *     // Callbacks
 *     select: function(result) {
 *       $('#title').val(result.title);
 *       $('#myoption').val(result.myOption);
 *     }
 *   });
 * @endcode
 *
 * The DOM tree will look like this:
 *
 * - input (text input field, provided as the $input argument)
 * - div#linkit-autocomplete-wrapper (no width/height, position relative)
 *   - ul#linkit-autocomplete-results (fixed width, variable height)
 *     - li.result (variable height)
 *       - Customizable output.
 *     - li.result (more results...)
 *
 * Note that everything within li.result can be altered by the user,
 * @see callbacks.renderResult(). The default rendering function outputs:
 * - h4.title (contains the title)
 * - p.description (contains the description)
 * Note that no sanitization of title/description occurs client side.
 *
 * @param inputElement
 *   The text input element.
 *   // TODO: If it's made a jQuery plugin, it should be multiple elements?
 *
 * @param path
 *   A path which provides JSON objects upon an HTTP request. This path should
 *   print a JSON-encoded array containing result objects. Each result object
 *   should contain these properties:
 *   - title: (optional) Per default, this will be rendered as an h4 tag in the
 *     list item. To alter, @see callbacks.renderResult().
 *   - description: (optional) Per default, this will be rendered as a p tag
 *     in the list item.
 *   - group: (optional) Add groups to the results. Will render nice group
 *     headings. Remember to put the results grouped together in the JSON
 *     array, otherwise they will be rendered as multiple groups.
 *   - addClass: (optional) Add CSS classes to the result object separated by
 *     spaces.
 *
 *   Feel free to add more properties. They will be returned with the callbacks
 *   just like the other properties.
 *
 * @param options
 *   An object with configurable options:
 *   - charLimit: (default=3) The minimum number of chars to do an AJAX call.
 *     A typical use case for this limit is to reduce server load.
 *   - wait: (default=250) The time in ms between last keypress and AJAX call.
 *   - getParam: (default="s") The get parameter for AJAX calls: "?param=".
 *     TODO: Make a generic "construct URL callback" instead.
 *   - ajaxTimeout: (default=5000) Timeout on AJAX calls.
 *
 * @param callbacks
 *   An object containing optional callback functions on certain events:
 *   - select: Gets executed when a result gets selected (clicked) by the user.
 *     Arguments:
 *     - result: The result object that was selected.
 *   - renderResult: Gets executed when results has been fetched and needs to
 *     be rendered. It should return a DOM element, an HTML string, or a jQuery
 *     object which will be inserted into the list item. Arguments:
 *     - result: The result object that should be rendered.
 */
$.fn.betterAutocomplete = function(method) {

  var methods = {
      init: function(path, options, callbacks) {
        this.filter(':input[type=text]').each(function() {
          $(this).data('betterAutocomplete', new BetterAutocomplete($(this), path, options, callbacks));
        });
      },
      destroy: function() {
        this.filter(':input[type=text]').each(function() {
          var bac = $(this).data('betterAutocomplete');
          if (bac instanceof BetterAutocomplete) {
            bac.destroy();
          }
        });
      }
  };

  //Method calling logic
  if (methods[method]) {
    return methods[method].apply( this, Array.prototype.slice.call(arguments, 1));
  }
  else if (typeof method === 'object' || ! method) {
    return methods.init.apply(this, arguments);
  }
  else {
    $.error( 'Method ' +  method + ' does not exist on jQuery.betterAutocomplete' );
  }

  return this;
};

/**
 * The BetterAutocomplete constructor function
 */
var BetterAutocomplete = function($input, path, options, callbacks) {

  options = $.extend({
    charLimit: 3,
    wait: 250,
    getParam: 's',
    ajaxTimeout: 5000
  }, options);

  callbacks = $.extend({
    select: function(result) {
      $input.blur();
    },
    renderResult: function(result) {
      var output = '';
      if (typeof result.title != 'undefined') {
        output += '<h4>' + result.title + '</h4>';
      }
      if (typeof result.description != 'undefined') {
        output += '<p>' + result.description + '</p>';
      }
      return output;
    }
  }, callbacks);

  var self = this;

  self.destroy = function() {
    // TODO: Unbind only the specific functions
    $input.unbind(inputEvents);
    $wrapper.remove();
    $input.removeData('betterAutocomplete');
  };

  var lastRenderedSearch = '';

  // Caching of search results
  // A key-value object, key is search string, value is a result object
  var results = {};

  // The user's current string input
  var userString = $input.val();

  var timer;
  
  var disableMouseHighlight = false;

  // Turn off the browser's autocompletion
  $input
    .attr('autocomplete', 'OFF')
    .attr('aria-autocomplete', 'none');

  // TODO: Change specific id:s to generic classnames
  var $wrapper = $('<div />')
    .attr('id', 'linkit-autocomplete-wrapper')
    .insertAfter($input);

  var $resultsList = $('<ul />')
    .attr('id', 'linkit-autocomplete-results')
    .width($input.innerWidth())
    .appendTo($wrapper);

  var inputEvents = {
    focus: function() {
      parseResults();
      $wrapper.show();
    },
    blur: function() {
      $wrapper.hide();
    },
    keydown: function(event) {
      var index = getHighlighted();
      var newIndex;
      var size = $('.result', $resultsList).length;
      switch (event.keyCode) {
        case 38: // Up arrow
          newIndex = Math.max(0, index-1);
          break;
        case 40: // Down arrow
          newIndex = Math.min(size-1, index+1);
          break;
        case 9: // Tab
        case 13: // Enter
          select();
          return false;
      }
      // Index have changed so update highlighted element, then cancel the event.
      if (typeof newIndex == 'number') {

        // Disable the auto-triggered mouseover event
        disableMouseHighlight = true;

        setHighlighted(newIndex);

        // Automatic scrolling to the highlighted result
        var $scrollTo = $('.result', $resultsList).eq(getHighlighted());

        // Scrolling up, then show the group title
        if ($scrollTo.prev().is('.group') && event.keyCode == 38) {
          $scrollTo = $scrollTo.prev();
        }
        // Is the result above the visible region?
        if ($scrollTo.position().top < 0) {
          $resultsList.scrollTop($scrollTo.position().top + $resultsList.scrollTop());
        }
        // Or is it below the visible region?
        else if (($scrollTo.position().top + $scrollTo.outerHeight()) > $resultsList.height()) {
          $resultsList.scrollTop($scrollTo.position().top + $resultsList.scrollTop() + $scrollTo.outerHeight() - $resultsList.height());
        }
        return false;
      }
    },
    keyup: function() {
      clearTimeout(timer);
      // Parse always!
      parseResults();
      // If the results can't be displayed we must fetch them, then display
      if (needsFetching()) {
        timer = setTimeout(function() {
          // TODO: For ultimate portability, provide callback for storing result objects so that even non JSON sources can be used?
          fetchResults($input.val(), function(data, search) {
            results[search] = data;
            parseResults();
          });
        }, options.wait);
      }
    }
  };

  // Just toggle visibility of the results on focus/blur
  $input.bind(inputEvents);

  $('.result', $resultsList[0]).live({
    // When the user hovers a result with the mouse, highlight it.
    mouseover: function() {
      if (disableMouseHighlight) {
        return;
      }
      setHighlighted($(this).data('index'));
    },
    mousemove: function() {
      // Enable mouseover again.
      disableMouseHighlight = false;
    },
    mousedown: function() {
      select();
      return false;
    }
  });

  // Prevent blur when clicking on group titles, scrollbars etc.,
  // This event is triggered after the others' because of bubbling order.
  $resultsList.mousedown(function() {
    return false;
  });

  /**
   * Set highlight to a specific result item
   *
   * @param index
   *   The result's index, starting on 0
   */
  var setHighlighted = function(index) {
    $('.result', $resultsList)
      .removeClass('highlight')
      .eq(index).addClass('highlight');
  };

  /**
   * Retrieve the index of the currently highlighted result item
   *
   * @return
   *   The result's index or -1 if no result is highlighted
   */
  var getHighlighted = function() {
    return $('.result', $resultsList).index($('.result.highlight', $resultsList));
  };

  /**
   * Select the current highlighted element and call the selection cak
   */
  var select = function() {
    var $result = $('.result', $resultsList).eq(getHighlighted());
    if ($result.length == 0) {
      return;
    }
    var result = $result.data('result');

    callbacks.select(result);

    // Parse once more, if the callback changed focus or content
    parseResults();
  };

  /**
   * Fetch results asynchronously via AJAX.
   * Errors are ignored. TODO: Disable enter and tab while fetching
   *
   * @param search
   *   The search string
   *
   * @param callback
   *   The callback function on success. Takes two arguments:
   *   TODO: Naming "data" and "callback"?
   *   - data (array of results)
   *   - search string
   */
  var fetchResults = function(search, callback) {
    // TODO: That class name is not generic?
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
      },
      error: function(jqXHR, textStatus, errorThrown) {
        // TODO: A callback for when an error occurs?
        $input.removeClass('throbbing');
      }
    });
  };

  /**
   * Does the current user string need fetching?
   * Checks character limit and cache.
   * 
   * @returns {Boolean} true if fetching is required
   */
  var needsFetching = function() {
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
  var parseResults = function() {
    // TODO: Logical statements here, cleanup?
    if (!$input.is(':focus')) {
      $wrapper.hide();
      return;
    }
    // Check if already rendered
    if (lastRenderedSearch == $input.val()) {
      $wrapper.show();
      return;
    }
    $wrapper.hide();
    if (needsFetching()) {
      return;
    }
    lastRenderedSearch = $input.val();

    // Not in cache
    if (renderResults() >= 1) {
      setHighlighted(0);
      $wrapper.show();
    }
  };

  /**
   * Generate DOM result items from the current search using the results cache
   * 
   * @todo Grouping of items even if they are recieved in an arbitrary order?
   *
   * @todo Sanitization of title/description? Something that just filters XSS
   * would be necessary, I think. Maybe a list of allowed HTML tags.
   * Another option is to inform the developers that they should sanitize
   * server-side.
   */
  var renderResults = function() {

    // Update user string
    userString = $input.val();

    $resultsList.empty();

    // The result is not in cache, so there is nothing to display right now
    if (!(results[userString] instanceof Array)) {
      return -1;
    }
    var index = -1;
    var lastGroup;
    for (index in results[userString]) {
      // Shortname for this result
      var result = results[userString][index];
      if (!(result instanceof Object)) {
        continue;
      }

      // If we don't have title or description, we don't have much to display
      if (typeof result.title == 'undefined' && typeof result.description == 'undefined') {
        continue;
      }

      // Grouping
      if (typeof result.group != 'undefined' && result.group !== lastGroup) {
        var $groupHeading = $('<li />').addClass('group')
          .append('<h3>' + result.group + '</h3>')
          .appendTo($resultsList);
      }
      lastGroup = result.group;

      var $result = $('<li />').addClass('result')
        .append(callbacks.renderResult(result))
        .data('result', result) // Store the result object on this DOM element
        .data('index', index) // For quick determination of index on events
        .addClass(result.addClass)
        .appendTo($resultsList);
    }
    index++;
    return index;
  };
};

/**
 * Focus selector, required by BetterAutocomplete
 *
 * @see http://stackoverflow.com/questions/967096/using-jquery-to-test-if-an-input-has-focus
 *
 * @todo Check if focus selector already exists? jQuery 1.6 has it built-in.
 */
$.expr[':'].focus = function( elem ) {
  return elem === document.activeElement && ( elem.type || elem.href );
};

})(jQuery);
