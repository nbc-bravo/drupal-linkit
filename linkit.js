
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
 * $element is the input element wrapped in jQuery
 * selectCallback A callback function to execute when an item gets selected. Takes linkObject as argument
 */
var AutoCompleteObject = function($element, selectCallback) {
  var self = this;
  self.activeCall = null;
  self.$element = $element;
  self.selectCallback = selectCallback;
  
  self.currentResults = [];
  console.log('call');
  self.$element.attr('autocomplete', 'OFF').attr('aria-autocomplete', 'none');
  
  // TODO: Wrapper div that has no dimensions instead of the ul
  self.resultsDOM = $('<ul id="linkit-autocomplete-results" />').width(self.$element.innerWidth()).insertAfter(self.$element);
  
  self.$element.focus(function() {
    self.resultsDOM.show();
    console.log('focus');
  });
  self.$element.blur(function() {
    self.resultsDOM.hide();
    return false;
  });
  self.$element.keyup(function() {
    if (self.$element.val().length >= 3) {
      self.fetchResults(self.$element.val());
    }
  });
  
  self.setStatus = function(string) {
    self.status = string;
    switch (string) {
    case 'loading':
      self.$element.addClass('throbbing');
      break;
    }
  };
  
  self.fetchResults = function(str) {
    self.$element.addClass('throbbing');
    if (self.activeCall !== null) {
      self.activeCall.abort();
      
      console.log(self.activeCall);
    }
    var xhr = $.ajax({
      url: 'http://d7.dev/linkit/autocomplete',
      dataType: 'json',
      success: function(results, textStatus) {
        self.currentResults = results;
        self.activeCall = null;
        self.displayResults();
        self.$element.removeClass('throbbing');
      }
    });
    self.activeCall = xhr;
  };
  
  /**
   * Put the mouse cursor in this autocomplete field
   */
  self.focus = function() {
    self.$element.focus();
  };
  
  self.displayResults = function() {
    self.resultsDOM.empty();
    for (i in self.currentResults) {
      var res = self.currentResults[i];
      var DOMResult = $('<li />').addClass('result').append('<h4>' + res.title + '</h4><p>' + res.description + '</p>');
      if (i == 0) {
        console.log(i);
        DOMResult.addClass('selected');
      }
      DOMResult.data('linkObject', res);
      self.resultsDOM.append(DOMResult);
      DOMResult.mouseover(function() {
        // Some dude hovered in
        $('.result.selected', self.resultsDOM).removeClass('selected');
        $(this).addClass('selected');
      });
      
      // A result is inserted
      // TODO: Move most code to new method self.selectResult(id) so it can also be keyboard navigated
      DOMResult.mousedown(function() {
        // Some dude clicked
        console.log(27);
        if (typeof self.selectCallback === 'function')
          self.selectCallback($(this).data('linkObject'));
        //self.$element.blur();
        self.$element.val('');
        self.resultsDOM.empty();
      });
    }
    
    // self.results.push();
  };
};

Drupal.behaviors.linkitAutocomplete = {
  attach: function(context, settings) {
    var aco = new AutoCompleteObject($('#linkit #edit-search', context), function(linkObject) {
      // Select callback is executed when an object is chosen
      // Only change the link text if it is empty
      $('#linkit #edit-text:text[value=""]').val(linkObject.title);
      $('#linkit #edit-path').val(linkObject.path);
    });
    if (context === window.document) {
      aco.focus();
    }
  }
};

})(jQuery);