/*
Version: 1.0.5

Documentation: http://baymard.com/labs/country-selector#documentation

Copyright (C) 2011 by Jamie Appleseed, Baymard Institute (baymard.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
(function($){
  var settings = {
    'sort': false,
    'sort-attr': 'data-priority',
    'sort-desc': false,
    'autoselect': true,
    'alternative-spellings': true,
    'alternative-spellings-attr': 'data-alternative-spellings',
    'remove-valueless-options': true,
    'copy-attributes-to-text-field': true,
    'autocomplete-plugin': 'jquery_ui',
    'relevancy-sorting': true,
    'relevancy-sorting-partial-match-value': 1,
    'relevancy-sorting-strict-match-value': 5,
    'relevancy-sorting-booster-attr': 'data-relevancy-booster',
    handle_invalid_input: function( context ) {
      context.$text_field.val( context.$select_field.find('option:selected:first').text() );
    },
    handle_select_field: function( $select_field ) {
      return $select_field.hide();
    },
    insert_text_field: function( context ) {
      var $text_field = $( "<input></input>" );
      if ( settings['copy-attributes-to-text-field'] ) {
        var attrs = {};
        var raw_attrs = context.$select_field[0].attributes;
        for (var i=0; i < raw_attrs.length; i++) {
          var key = raw_attrs[i].nodeName;
          var value = raw_attrs[i].nodeValue;
          if ( key !== 'name' && key !== 'id' ) {
            attrs[key] = raw_attrs[i].nodeValue;
          }
        };
        $text_field.attr( attrs );
      }
      $text_field.blur(function() {
        var valid_values = context.$select_field.find('option').map(function(i, option) { return $(option).text(); });
        if ( !($text_field.val() in valid_values) && typeof settings['handle_invalid_input'] === 'function' ) {
          settings['handle_invalid_input'](context);
        }
      });
      // give the input box the ability to select all text on mouse click
      if ( context.settings['autoselect'] ) {
         $text_field.click( function() {
             this.select();
            });
      }
      return $text_field.val( context.$select_field.find('option:selected:first').text() )
        .insertAfter( context.$select_field );
    },
    extract_options: function( $select_field ) {
      var options = [];
      var $options = $select_field.find('option');
      var number_of_options = $options.length;
      
      // go over each option in the select tag
      $options.each(function(){
        var $option = $(this);
        var option = {
          'real-value': $option.attr('value'),
          'label': $option.text()
        }
        if ( settings['remove-valueless-options'] && option['real-value'] === '') {
          // skip options without a value
        } else {
          // prepare the 'matches' string which must be filtered on later
          option['matches'] = option['label'];
          var alternative_spellings = $option.attr( settings['alternative-spellings-attr'] );
          if ( alternative_spellings ) {
            option['matches'] += ' ' + alternative_spellings;
          }
          // give each option a weight paramter for sorting
          if ( settings['sort'] ) {
            var weight = parseInt( $option.attr( settings['sort-attr'] ), 10 );
            if ( weight ) {
              option['weight'] = weight;
            } else {
              option['weight'] = number_of_options;
            }
          }
          // add relevancy score
          if ( settings['relevancy-sorting'] ) {
            option['relevancy-score'] = 0;
            option['relevancy-score-booster'] = 1;
            var boost_by = parseFloat( $option.attr( settings['relevancy-sorting-booster-attr'] ) );
            if ( boost_by ) {
              option['relevancy-score-booster'] = boost_by;
            }
          }
          // add option to combined array
          options.push( option );
        }
      });
      // sort the options based on weight
      if ( settings['sort'] ) {
        if ( settings['sort-desc'] ) {
          options.sort( function( a, b ) { return b['weight'] - a['weight']; } );
        } else {
          options.sort( function( a, b ) { return a['weight'] - b['weight']; } );
        }
      }
      
      // return the set of options, each with the following attributes: real-value, label, matches, weight (optional)
      return options;
    }
  };
  
  var public_methods = {
    init: function( customizations ) {
      
      if ( $.browser.msie && parseInt($.browser.version, 10) <= 7) {
        
        return this;
        
      } else {
        
        settings = $.extend( settings, customizations );

        return this.each(function(){
          var $select_field = $(this);
          
          var context = {
            '$select_field': $select_field,
            'options': settings['extract_options']( $select_field ),
            'settings': settings
          };

          context['$text_field'] = settings['insert_text_field']( context );
          
          settings['handle_select_field']( $select_field );
          
          if ( typeof settings['autocomplete-plugin'] === 'string' ) {
            adapters[settings['autocomplete-plugin']]( context );
          } else {
            settings['autocomplete-plugin']( context );
          }
        });
        
      }
      
    }
  };
  
  var adapters = {
    jquery_ui: function( context ) {
      // loose matching of search terms
      var filter_options = function( term ) {
        var split_term = term.split(' ');
        var matchers = [];
        for (var i=0; i < split_term.length; i++) {
          if ( split_term[i].length > 0 ) {
            var matcher = {};
            matcher['partial'] = new RegExp( $.ui.autocomplete.escapeRegex( split_term[i] ), "i" );
            if ( context.settings['relevancy-sorting'] ) {
              matcher['strict'] = new RegExp( "^" + $.ui.autocomplete.escapeRegex( split_term[i] ), "i" );
            }
            matchers.push( matcher );
          }
        };
        
        return $.grep( context.options, function( option ) {
          var partial_matches = 0;
          if ( context.settings['relevancy-sorting'] ) {
            var strict_match = false;
            var split_option_matches = option.matches.split(' ');
          }
          for ( var i=0; i < matchers.length; i++ ) {
            if ( matchers[i]['partial'].test( option.matches ) ) {
              partial_matches++;
            }
            if ( context.settings['relevancy-sorting'] ) {
              for (var q=0; q < split_option_matches.length; q++) {
                if ( matchers[i]['strict'].test( split_option_matches[q] ) ) {
                  strict_match = true;
                  break;
                }
              };
            }
          };
          if ( context.settings['relevancy-sorting'] ) {
            var option_score = 0;
            option_score += partial_matches * context.settings['relevancy-sorting-partial-match-value'];
            if ( strict_match ) {
              option_score += context.settings['relevancy-sorting-strict-match-value'];
            }
            option_score = option_score * option['relevancy-score-booster'];
            option['relevancy-score'] = option_score;
          }
          return (!term || matchers.length === partial_matches );
        });
      }
      // update the select field value using either selected option or current input in the text field
      var update_select_value = function( option ) {
        if ( option ) {
          if ( context.$select_field.val() !== option['real-value'] ) {
            context.$select_field.val( option['real-value'] );
            context.$select_field.change();
          }
        } else {
          var option_name = context.$text_field.val().toLowerCase();
          var matching_option = { 'real-value': false };
          for (var i=0; i < context.options.length; i++) {
            if ( option_name === context.options[i]['label'].toLowerCase() ) {
              matching_option = context.options[i];
              break;
            }
          };
          if ( context.$select_field.val() !== matching_option['real-value'] ) {
            context.$select_field.val( matching_option['real-value'] || '' );
            context.$select_field.change();
          }
          if ( matching_option['real-value'] ) {
            context.$text_field.val( matching_option['label'] );
          }
          if ( typeof context.settings['handle_invalid_input'] === 'function' && context.$select_field.val() === '' ) {
            context.settings['handle_invalid_input']( context );
          }
        }
      }
      // jQuery UI autocomplete settings & behavior
      context.$text_field.autocomplete({
        'minLength': 0,
        'delay': 0,
        'autoFocus': true,
        source: function( request, response ) {
          var filtered_options = filter_options( request.term );
          if ( context.settings['relevancy-sorting'] ) {
            filtered_options = filtered_options.sort( function( a, b ) { return b['relevancy-score'] - a['relevancy-score']; } );
          }
          response( filtered_options );
        },
        select: function( event, ui ) {
          update_select_value( ui.item );
        },
        change: function( event, ui ) {
          update_select_value( ui.item );
        }
      });
      // force refresh value of select field when form is submitted
      context.$text_field.parents('form:first').submit(function(){
        update_select_value();
      });
      // select current value
      update_select_value();
    }
  };

  $.fn.selectToAutocomplete = function( method ) {
    if ( public_methods[method] ) {
      return public_methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return public_methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.fn.selectToAutocomplete' );
    }    
  };
  
})(jQuery); 
