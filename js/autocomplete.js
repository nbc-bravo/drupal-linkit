/**
 * @file
 * Linkit Autocomplete based on jQuery UI.
 */

(function ($, Drupal) {

  "use strict";

  var autocomplete;

  /**
   * JQuery UI autocomplete source callback.
   *
   * @param {object} request
   * @param {function} response
   */
  function sourceData(request, response) {
    var elementId = this.element.attr('id');

    if (!(elementId in autocomplete.cache)) {
      autocomplete.cache[elementId] = {};
    }

    /**
     * @param {object} suggestions
     */
    function showSuggestions(suggestions) {
      response(suggestions);
    }

    /**
     * Transforms the data object into an array and update autocomplete results.
     *
     * @param {object} data
     */
    function sourceCallbackHandler(data) {
      console.log(data);
      autocomplete.cache[elementId][term] = data;
      showSuggestions(data);
    }

    // Get the desired term and construct the autocomplete URL for it.
    var term = request.term;

    // Check if the term is already cached.
    if (autocomplete.cache[elementId].hasOwnProperty(term)) {
      showSuggestions(autocomplete.cache[elementId][term]);
    }
    else {
      var options = $.extend({success: sourceCallbackHandler, data: {q: term}}, autocomplete.ajax);
      $.ajax(this.element.attr('data-autocomplete-path'), options);
    }
  }

  /**
    * Handles an autocompleteselect event.
    *
    * @param {jQuery.Event} event
    * @param {object} ui
    *
    * @return {bool}
    */
  function selectHandler(event, ui) {
    console.log(ui.item);

    event.target.value = ui.item.path;
    return false;
  }

  /**
   * Override jQuery UI _renderItem function to output HTML by default.
   *
   * @param {object} ul
   * @param {object} item
   *
   * @return {object}
   */
  function renderItem(ul, item) {
    return $("<li>")
      .append($("<span>").html(item.title))
      .append($("<span>").html(item.description))
      .appendTo(ul);
  }

  /**
   * Override jQuery UI _renderMenu function to group items.
   *
   * @param {object} ul
   * @param {object} item
   *
   */
  //function renderMenu(ul, item) {
  //  var that = this;
  //  $.each(items, function(index, item) {
  //    autocomplete.options._renderItemData(ul, item);
  //  });
  //  $(ul).find("li:odd").addClass("odd");
  //}

  /**
   * Attaches the autocomplete behavior to all required fields.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.linkit_autocomplete = {
    attach: function (context) {
      // Act on textfields with the "form-autocomplete" class.
      var $autocomplete = $(context).find('input.form-linkit-autocomplete').once('linkit-autocomplete');
      if ($autocomplete.length) {
        // Use jQuery UI Autocomplete on the textfield.
        $autocomplete.autocomplete(autocomplete.options)
          .data("ui-autocomplete")
          ._renderItem = autocomplete.options.renderItem;
      }
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        $(context).find('input.form-linkit-autocomplete')
          .removeOnce('linkit-autocomplete')
          .autocomplete('destroy');
      }
    }
  };

  /**
   * Autocomplete object implementation.
   */
  autocomplete = {
    cache: {},
    options: {
      source: sourceData,
      renderItem: renderItem,
      select: selectHandler,
      minLength: 1
    },
    ajax: {
      dataType: 'json'
    }
  };

})(jQuery, Drupal);
