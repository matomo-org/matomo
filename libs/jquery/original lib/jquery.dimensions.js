/* Copyright (c) 2007 Paul Bakaus (paul.bakaus@googlemail.com) and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate$
 * $Rev$
 *
 * Version: 1.1.2
 *
 * Requires: jQuery 1.1.3+
 */

(function($){

// store a copy of the core height and width methods
var height = $.fn.height,
    width  = $.fn.width;

$.fn.extend({
	/**
	 * If used on document, returns the document's height (innerHeight).
	 * If used on window, returns the viewport's (window) height.
	 * See core docs on height() to see what happens when used on an element.
	 *
	 * @example $("#testdiv").height()
	 * @result 200
	 *
	 * @example $(document).height()
	 * @result 800
	 *
	 * @example $(window).height()
	 * @result 400
	 *
	 * @name height
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	height: function() {
		if ( !this[0] ) error();
		if ( this[0] == window )
			if ( $.browser.opera || ($.browser.safari && parseInt($.browser.version) > 520) )
				return self.innerHeight - (($(document).height() > self.innerHeight) ? getScrollbarWidth() : 0);
			else if ( $.browser.safari )
				return self.innerHeight;
			else
                return $.boxModel && document.documentElement.clientHeight || document.body.clientHeight;
		
		if ( this[0] == document ) 
			return Math.max( ($.boxModel && document.documentElement.scrollHeight || document.body.scrollHeight), document.body.offsetHeight );
		
		return height.apply(this, arguments);
	},
	
	/**
	 * If used on document, returns the document's width (innerWidth).
	 * If used on window, returns the viewport's (window) width.
	 * See core docs on width() to see what happens when used on an element.
	 *
	 * @example $("#testdiv").width()
	 * @result 200
	 *
	 * @example $(document).width()
	 * @result 800
	 *
	 * @example $(window).width()
	 * @result 400
	 *
	 * @name width
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	width: function() {
		if (!this[0]) error();
		if ( this[0] == window )
			if ( $.browser.opera || ($.browser.safari && parseInt($.browser.version) > 520) )
				return self.innerWidth - (($(document).width() > self.innerWidth) ? getScrollbarWidth() : 0);
			else if ( $.browser.safari )
				return self.innerWidth;
			else
                return $.boxModel && document.documentElement.clientWidth || document.body.clientWidth;

		if ( this[0] == document )
			if ($.browser.mozilla) {
				// mozilla reports scrollWidth and offsetWidth as the same
				var scrollLeft = self.pageXOffset;
				self.scrollTo(99999999, self.pageYOffset);
				var scrollWidth = self.pageXOffset;
				self.scrollTo(scrollLeft, self.pageYOffset);
				return document.body.offsetWidth + scrollWidth;
			}
			else 
				return Math.max( (($.boxModel && !$.browser.safari) && document.documentElement.scrollWidth || document.body.scrollWidth), document.body.offsetWidth );

		return width.apply(this, arguments);
	},
	
	/**
	 * Gets the inner height (excludes the border and includes the padding) for the first matched element.
	 * If used on document, returns the document's height (innerHeight).
	 * If used on window, returns the viewport's (window) height.
	 *
	 * @example $("#testdiv").innerHeight()
	 * @result 210
	 *
	 * @name innerHeight
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	innerHeight: function() {
		if (!this[0]) error();
		return this[0] == window || this[0] == document ?
			this.height() :
			this.is(':visible') ?
				this[0].offsetHeight - num(this, 'borderTopWidth') - num(this, 'borderBottomWidth') :
				this.height() + num(this, 'paddingTop') + num(this, 'paddingBottom');
	},
	
	/**
	 * Gets the inner width (excludes the border and includes the padding) for the first matched element.
	 * If used on document, returns the document's width (innerWidth).
	 * If used on window, returns the viewport's (window) width.
	 *
	 * @example $("#testdiv").innerWidth()
	 * @result 210
	 *
	 * @name innerWidth
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	innerWidth: function() {
		if (!this[0]) error();
		return this[0] == window || this[0] == document ?
			this.width() :
			this.is(':visible') ?
				this[0].offsetWidth - num(this, 'borderLeftWidth') - num(this, 'borderRightWidth') :
				this.width() + num(this, 'paddingLeft') + num(this, 'paddingRight');
	},
	
	/**
	 * Gets the outer height (includes the border and padding) for the first matched element.
	 * If used on document, returns the document's height (innerHeight).
	 * If used on window, returns the viewport's (window) height.
	 *
	 * The margin can be included in the calculation by passing an options map with margin
	 * set to true.
	 *
	 * @example $("#testdiv").outerHeight()
	 * @result 220
	 *
	 * @example $("#testdiv").outerHeight({ margin: true })
	 * @result 240
	 *
	 * @name outerHeight
	 * @type Number
	 * @param Map options Optional settings to configure the way the outer height is calculated.
	 * @cat Plugins/Dimensions
	 */
	outerHeight: function(options) {
		if (!this[0]) error();
		options = $.extend({ margin: false }, options || {});
		return this[0] == window || this[0] == document ?
			this.height() :
			this.is(':visible') ?
				this[0].offsetHeight + (options.margin ? (num(this, 'marginTop') + num(this, 'marginBottom')) : 0) :
				this.height() 
					+ num(this,'borderTopWidth') + num(this, 'borderBottomWidth') 
					+ num(this, 'paddingTop') + num(this, 'paddingBottom')
					+ (options.margin ? (num(this, 'marginTop') + num(this, 'marginBottom')) : 0);
	},
	
	/**
	 * Gets the outer width (including the border and padding) for the first matched element.
	 * If used on document, returns the document's width (innerWidth).
	 * If used on window, returns the viewport's (window) width.
	 *
	 * The margin can be included in the calculation by passing an options map with margin
	 * set to true.
	 *
	 * @example $("#testdiv").outerWidth()
	 * @result 1000
	 *
	 * @example $("#testdiv").outerWidth({ margin: true })
	 * @result 1020
	 * 
	 * @name outerHeight
	 * @type Number
	 * @param Map options Optional settings to configure the way the outer width is calculated.
	 * @cat Plugins/Dimensions
	 */
	outerWidth: function(options) {
		if (!this[0]) error();
		options = $.extend({ margin: false }, options || {});
		return this[0] == window || this[0] == document ?
			this.width() :
			this.is(':visible') ?
				this[0].offsetWidth + (options.margin ? (num(this, 'marginLeft') + num(this, 'marginRight')) : 0) :
				this.width() 
					+ num(this, 'borderLeftWidth') + num(this, 'borderRightWidth') 
					+ num(this, 'paddingLeft') + num(this, 'paddingRight')
					+ (options.margin ? (num(this, 'marginLeft') + num(this, 'marginRight')) : 0);
	},
	
	/**
	 * Gets how many pixels the user has scrolled to the right (scrollLeft).
	 * Works on containers with overflow: auto and window/document.
	 *
	 * @example $(window).scrollLeft()
	 * @result 100
	 *
	 * @example $(document).scrollLeft()
	 * @result 100
	 * 
	 * @example $("#testdiv").scrollLeft()
	 * @result 100
	 *
	 * @name scrollLeft
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	/**
	 * Sets the scrollLeft property for each element and continues the chain.
	 * Works on containers with overflow: auto and window/document.
	 *
	 * @example $(window).scrollLeft(100).scrollLeft()
	 * @result 100
	 * 
	 * @example $(document).scrollLeft(100).scrollLeft()
	 * @result 100
	 *
	 * @example $("#testdiv").scrollLeft(100).scrollLeft()
	 * @result 100
	 *
	 * @name scrollLeft
	 * @param Number value A positive number representing the desired scrollLeft.
	 * @type jQuery
	 * @cat Plugins/Dimensions
	 */
	scrollLeft: function(val) {
		if (!this[0]) error();
		if ( val != undefined )
			// set the scroll left
			return this.each(function() {
				if (this == window || this == document)
					window.scrollTo( val, $(window).scrollTop() );
				else
					this.scrollLeft = val;
			});
		
		// return the scroll left offest in pixels
		if ( this[0] == window || this[0] == document )
			return self.pageXOffset ||
				$.boxModel && document.documentElement.scrollLeft ||
				document.body.scrollLeft;
				
		return this[0].scrollLeft;
	},
	
	/**
	 * Gets how many pixels the user has scrolled to the bottom (scrollTop).
	 * Works on containers with overflow: auto and window/document.
	 *
	 * @example $(window).scrollTop()
	 * @result 100
	 *
	 * @example $(document).scrollTop()
	 * @result 100
	 * 
	 * @example $("#testdiv").scrollTop()
	 * @result 100
	 *
	 * @name scrollTop
	 * @type Number
	 * @cat Plugins/Dimensions
	 */
	/**
	 * Sets the scrollTop property for each element and continues the chain.
	 * Works on containers with overflow: auto and window/document.
	 *
	 * @example $(window).scrollTop(100).scrollTop()
	 * @result 100
	 * 
	 * @example $(document).scrollTop(100).scrollTop()
	 * @result 100
	 *
	 * @example $("#testdiv").scrollTop(100).scrollTop()
	 * @result 100
	 *
	 * @name scrollTop
	 * @param Number value A positive number representing the desired scrollTop.
	 * @type jQuery
	 * @cat Plugins/Dimensions
	 */
	scrollTop: function(val) {
		if (!this[0]) error();
		if ( val != undefined )
			// set the scroll top
			return this.each(function() {
				if (this == window || this == document)
					window.scrollTo( $(window).scrollLeft(), val );
				else
					this.scrollTop = val;
			});
		
		// return the scroll top offset in pixels
		if ( this[0] == window || this[0] == document )
			return self.pageYOffset ||
				$.boxModel && document.documentElement.scrollTop ||
				document.body.scrollTop;

		return this[0].scrollTop;
	},
	
	/** 
	 * Gets the top and left positioned offset in pixels.
	 * The positioned offset is the offset between a positioned
	 * parent and the element itself.
	 *
	 * For accurate calculations make sure to use pixel values for margins, borders and padding.
	 *
	 * @example $("#testdiv").position()
	 * @result { top: 100, left: 100 }
	 *
	 * @example var position = {};
	 * $("#testdiv").position(position)
	 * @result position = { top: 100, left: 100 }
	 * 
	 * @name position
	 * @param Object returnObject Optional An object to store the return value in, so as not to break the chain. If passed in the
	 *                            chain will not be broken and the result will be assigned to this object.
	 * @type Object
	 * @cat Plugins/Dimensions
	 */
	position: function(returnObject) {
		return this.offset({ margin: false, scroll: false, relativeTo: this.offsetParent() }, returnObject);
	},
	
	/**
	 * Gets the location of the element in pixels from the top left corner of the viewport.
	 * The offset method takes an optional map of key value pairs to configure the way
	 * the offset is calculated. Here are the different options.
	 *
	 * (Boolean) margin - Should the margin of the element be included in the calculations? True by default.
	 * (Boolean) border - Should the border of the element be included in the calculations? False by default. 
	 * (Boolean) padding - Should the padding of the element be included in the calculations? False by default. 
	 * (Boolean) scroll - Should the scroll offsets of the parent elements be included in the calculations? True by default.
	 *                    When true it adds the total scroll offsets of all parents to the total offset and also adds two
	 *                    properties to the returned object, scrollTop and scrollLeft.
	 * (Boolean) lite - When true it will use the offsetLite method instead of the full-blown, slower offset method. False by default.
	 *                  Only use this when margins, borders and padding calculations don't matter.
	 * (HTML Element) relativeTo - This should be a parent of the element and should have position (like absolute or relative).
	 *                             It will retreive the offset relative to this parent element. By default it is the body element.
	 *
	 * Also an object can be passed as the second paramater to
	 * catch the value of the return and continue the chain.
	 *
	 * For accurate calculations make sure to use pixel values for margins, borders and padding.
	 * 
	 * Known issues:
	 *  - Issue: A div positioned relative or static without any content before it and its parent will report an offsetTop of 0 in Safari
	 *    Workaround: Place content before the relative div ... and set height and width to 0 and overflow to hidden
	 *
	 * @example $("#testdiv").offset()
	 * @result { top: 100, left: 100, scrollTop: 10, scrollLeft: 10 }
	 *
	 * @example $("#testdiv").offset({ scroll: false })
	 * @result { top: 90, left: 90 }
	 *
	 * @example var offset = {}
	 * $("#testdiv").offset({ scroll: false }, offset)
	 * @result offset = { top: 90, left: 90 }
	 *
	 * @name offset
	 * @param Map options Optional settings to configure the way the offset is calculated.
	 * @param Object returnObject An object to store the return value in, so as not to break the chain. If passed in the
	 *                            chain will not be broken and the result will be assigned to this object.
	 * @type Object
	 * @cat Plugins/Dimensions
	 */
	offset: function(options, returnObject) {
		if (!this[0]) error();
		var x = 0, y = 0, sl = 0, st = 0,
		    elem = this[0], parent = this[0], op, parPos, elemPos = $.css(elem, 'position'),
		    mo = $.browser.mozilla, ie = $.browser.msie, oa = $.browser.opera,
		    sf = $.browser.safari, sf3 = $.browser.safari && parseInt($.browser.version) > 520,
		    absparent = false, relparent = false, 
		    options = $.extend({ margin: true, border: false, padding: false, scroll: true, lite: false, relativeTo: document.body }, options || {});
		
		// Use offsetLite if lite option is true
		if (options.lite) return this.offsetLite(options, returnObject);
		// Get the HTMLElement if relativeTo is a jquery collection
		if (options.relativeTo.jquery) options.relativeTo = options.relativeTo[0];
		
		if (elem.tagName == 'BODY') {
			// Safari 2 is the only one to get offsetLeft and offsetTop properties of the body "correct"
			// Except they all mess up when the body is positioned absolute or relative
			x = elem.offsetLeft;
			y = elem.offsetTop;
			// Mozilla ignores margin and subtracts border from body element
			if (mo) {
				x += num(elem, 'marginLeft') + (num(elem, 'borderLeftWidth')*2);
				y += num(elem, 'marginTop')  + (num(elem, 'borderTopWidth') *2);
			} else
			// Opera ignores margin
			if (oa) {
				x += num(elem, 'marginLeft');
				y += num(elem, 'marginTop');
			} else
			// IE does not add the border in Standards Mode
			if ((ie && jQuery.boxModel)) {
				x += num(elem, 'borderLeftWidth');
				y += num(elem, 'borderTopWidth');
			} else
			// Safari 3 doesn't not include border or margin
			if (sf3) {
				x += num(elem, 'marginLeft') + num(elem, 'borderLeftWidth');
				y += num(elem, 'marginTop')  + num(elem, 'borderTopWidth');
			}
		} else {
			do {
				parPos = $.css(parent, 'position');
			
				x += parent.offsetLeft;
				y += parent.offsetTop;

				// Mozilla and IE do not add the border
				// Mozilla adds the border for table cells
				if ((mo && !parent.tagName.match(/^t[d|h]$/i)) || ie || sf3) {
					// add borders to offset
					x += num(parent, 'borderLeftWidth');
					y += num(parent, 'borderTopWidth');

					// Mozilla does not include the border on body if an element isn't positioned absolute and is without an absolute parent
					if (mo && parPos == 'absolute') absparent = true;
					// IE does not include the border on the body if an element is position static and without an absolute or relative parent
					if (ie && parPos == 'relative') relparent = true;
				}

				op = parent.offsetParent || document.body;
				if (options.scroll || mo) {
					do {
						if (options.scroll) {
							// get scroll offsets
							sl += parent.scrollLeft;
							st += parent.scrollTop;
						}
						
						// Opera sometimes incorrectly reports scroll offset for elements with display set to table-row or inline
						if (oa && ($.css(parent, 'display') || '').match(/table-row|inline/)) {
							sl = sl - ((parent.scrollLeft == parent.offsetLeft) ? parent.scrollLeft : 0);
							st = st - ((parent.scrollTop == parent.offsetTop) ? parent.scrollTop : 0);
						}
				
						// Mozilla does not add the border for a parent that has overflow set to anything but visible
						if (mo && parent != elem && $.css(parent, 'overflow') != 'visible') {
							x += num(parent, 'borderLeftWidth');
							y += num(parent, 'borderTopWidth');
						}
				
						parent = parent.parentNode;
					} while (parent != op);
				}
				parent = op;
				
				// exit the loop if we are at the relativeTo option but not if it is the body or html tag
				if (parent == options.relativeTo && !(parent.tagName == 'BODY' || parent.tagName == 'HTML'))  {
					// Mozilla does not add the border for a parent that has overflow set to anything but visible
					if (mo && parent != elem && $.css(parent, 'overflow') != 'visible') {
						x += num(parent, 'borderLeftWidth');
						y += num(parent, 'borderTopWidth');
					}
					// Safari 2 and opera includes border on positioned parents
					if ( ((sf && !sf3) || oa) && parPos != 'static' ) {
						x -= num(op, 'borderLeftWidth');
						y -= num(op, 'borderTopWidth');
					}
					break;
				}
				if (parent.tagName == 'BODY' || parent.tagName == 'HTML') {
					// Safari 2 and IE Standards Mode doesn't add the body margin for elments positioned with static or relative
					if (((sf && !sf3) || (ie && $.boxModel)) && elemPos != 'absolute' && elemPos != 'fixed') {
						x += num(parent, 'marginLeft');
						y += num(parent, 'marginTop');
					}
					// Safari 3 does not include the border on body
					// Mozilla does not include the border on body if an element isn't positioned absolute and is without an absolute parent
					// IE does not include the border on the body if an element is positioned static and without an absolute or relative parent
					if ( sf3 || (mo && !absparent && elemPos != 'fixed') || 
					     (ie && elemPos == 'static' && !relparent) ) {
						x += num(parent, 'borderLeftWidth');
						y += num(parent, 'borderTopWidth');
					}
					break; // Exit the loop
				}
			} while (parent);
		}

		var returnValue = handleOffsetReturn(elem, options, x, y, sl, st);

		if (returnObject) { $.extend(returnObject, returnValue); return this; }
		else              { return returnValue; }
	},
	
	/**
	 * Gets the location of the element in pixels from the top left corner of the viewport.
	 * This method is much faster than offset but not as accurate when borders and margins are
	 * on the element and/or its parents. This method can be invoked
	 * by setting the lite option to true in the offset method.
	 * The offsetLite method takes an optional map of key value pairs to configure the way
	 * the offset is calculated. Here are the different options.
	 *
	 * (Boolean) margin - Should the margin of the element be included in the calculations? True by default.
	 * (Boolean) border - Should the border of the element be included in the calculations? False by default. 
	 * (Boolean) padding - Should the padding of the element be included in the calcuations? False by default. 
	 * (Boolean) scroll - Sould the scroll offsets of the parent elements be included int he calculations? True by default.
	 *                    When true it adds the total scroll offsets of all parents to the total offset and also adds two
	 *                    properties to the returned object, scrollTop and scrollLeft.
	 * (HTML Element) relativeTo - This should be a parent of the element and should have position (like absolute or relative).
	 *                             It will retreive the offset relative to this parent element. By default it is the body element.
	 *
	 * @name offsetLite
	 * @param Map options Optional settings to configure the way the offset is calculated.
	 * @param Object returnObject An object to store the return value in, so as not to break the chain. If passed in the
	 *                            chain will not be broken and the result will be assigned to this object.
	 * @type Object
	 * @cat Plugins/Dimensions
	 */
	offsetLite: function(options, returnObject) {
		if (!this[0]) error();
		var x = 0, y = 0, sl = 0, st = 0, parent = this[0], offsetParent, 
		    options = $.extend({ margin: true, border: false, padding: false, scroll: true, relativeTo: document.body }, options || {});
				
		// Get the HTMLElement if relativeTo is a jquery collection
		if (options.relativeTo.jquery) options.relativeTo = options.relativeTo[0];
		
		do {
			x += parent.offsetLeft;
			y += parent.offsetTop;

			offsetParent = parent.offsetParent || document.body;
			if (options.scroll) {
				// get scroll offsets
				do {
					sl += parent.scrollLeft;
					st += parent.scrollTop;
					parent = parent.parentNode;
				} while(parent != offsetParent);
			}
			parent = offsetParent;
		} while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML' && parent != options.relativeTo);

		var returnValue = handleOffsetReturn(this[0], options, x, y, sl, st);

		if (returnObject) { $.extend(returnObject, returnValue); return this; }
		else              { return returnValue; }
	},
	
	/**
	 * Returns a jQuery collection with the positioned parent of 
	 * the first matched element. This is the first parent of 
	 * the element that has position (as in relative or absolute).
	 *
	 * @name offsetParent
	 * @type jQuery
	 * @cat Plugins/Dimensions
	 */
	offsetParent: function() {
		if (!this[0]) error();
		var offsetParent = this[0].offsetParent;
		while ( offsetParent && (offsetParent.tagName != 'BODY' && $.css(offsetParent, 'position') == 'static') )
			offsetParent = offsetParent.offsetParent;
		return $(offsetParent);
	}
});

/**
 * Throws an error message when no elements are in the jQuery collection
 * @private
 */
var error = function() {
	throw "Dimensions: jQuery collection is empty";
};

/**
 * Handles converting a CSS Style into an Integer.
 * @private
 */
var num = function(el, prop) {
	return parseInt($.css(el.jquery?el[0]:el,prop))||0;
};

/**
 * Handles the return value of the offset and offsetLite methods.
 * @private
 */
var handleOffsetReturn = function(elem, options, x, y, sl, st) {
	if ( !options.margin ) {
		x -= num(elem, 'marginLeft');
		y -= num(elem, 'marginTop');
	}

	// Safari and Opera do not add the border for the element
	if ( options.border && (($.browser.safari && parseInt($.browser.version) < 520) || $.browser.opera) ) {
		x += num(elem, 'borderLeftWidth');
		y += num(elem, 'borderTopWidth');
	} else if ( !options.border && !(($.browser.safari && parseInt($.browser.version) < 520) || $.browser.opera) ) {
		x -= num(elem, 'borderLeftWidth');
		y -= num(elem, 'borderTopWidth');
	}

	if ( options.padding ) {
		x += num(elem, 'paddingLeft');
		y += num(elem, 'paddingTop');
	}
	
	// do not include scroll offset on the element ... opera sometimes reports scroll offset as actual offset
	if ( options.scroll && (!$.browser.opera || elem.offsetLeft != elem.scrollLeft && elem.offsetTop != elem.scrollLeft) ) {
		sl -= elem.scrollLeft;
		st -= elem.scrollTop;
	}

	return options.scroll ? { top: y - st, left: x - sl, scrollTop:  st, scrollLeft: sl }
	                      : { top: y, left: x };
};

/**
 * Gets the width of the OS scrollbar
 * @private
 */
var scrollbarWidth = 0;
var getScrollbarWidth = function() {
	if (!scrollbarWidth) {
		var testEl = $('<div>')
				.css({
					width: 100,
					height: 100,
					overflow: 'auto',
					position: 'absolute',
					top: -1000,
					left: -1000
				})
				.appendTo('body');
		scrollbarWidth = 100 - testEl
			.append('<div>')
			.find('div')
				.css({
					width: '100%',
					height: 200
				})
				.width();
		testEl.remove();
	}
	return scrollbarWidth;
};

})(jQuery);