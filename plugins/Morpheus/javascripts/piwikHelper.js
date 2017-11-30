/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function _pk_translate(translationStringId, values) {

    if( typeof(piwik_translations[translationStringId]) != 'undefined' ){
        var translation = piwik_translations[translationStringId];
        if (typeof values != 'undefined' && values && values.length) {
            values.unshift(translation);
            return sprintf.apply(null, values);
        }

        return translation;
    }

    return "The string "+translationStringId+" was not loaded in javascript. Make sure it is added in the Translate.getClientSideTranslationKeys hook.";
}

var piwikHelper = {

    htmlDecode: function(value)
    {
        return $('<div/>').html(value).text();
    },

    /**
     * a nice cross-browser logging function
     */
    log: function() {
        try {
            console.log.apply(console, arguments); // Firefox, Chrome
        } catch (e) {
            try {
                opera.postError.apply(opera, arguments);  // Opera
            } catch (f) {
                // don't alert as log is not considered to be important enough
                // (as opposed to piwikHelper.error)
                //alert(Array.prototype.join.call(arguments, ' ')); // MSIE
            }
        }
    },

    error: function() {
        try {
            console.error.apply(console, arguments); // Firefox, Chrome
        } catch (e) {
            try {
                opera.postError.apply(opera, arguments);  // Opera
            } catch (f) {
                alert(Array.prototype.join.call(arguments, ' ')); // MSIE
            }
        }
    },

    htmlEntities: function(value)
    {
        var findReplace = [[/&/g, "&amp;"], [/</g, "&lt;"], [/>/g, "&gt;"], [/"/g, "&quot;"]];
        for(var item in findReplace) {
            value = value.replace(findReplace[item][0], findReplace[item][1]);
        }
        return value;
    },

    escape: function (value)
    {
        var escape = angular.element(document).injector().get('$sanitize');

        return escape(value);
    },

	/**
	 * Add break points to a string so that it can be displayed more compactly
	 */
	addBreakpoints: function(text, breakpointMarkup)
	{
		return text.replace(/([\/&=?\.%#:_-])/g, '$1' +
			(typeof breakpointMarkup == 'undefined' ? '<wbr>&#8203;' : breakpointMarkup));
			 // &#8203; is for internet explorer
	},

	/**
	 * Add breakpoints to a URL
	 * urldecodes and encodes htmlentities to display utf8 urls without XSS vulnerabilities
	 */
	addBreakpointsToUrl: function(url)
	{
		try {
			url = decodeURIComponent(url);
		} catch (e) {
			// might throw "URI malformed"
		}
		url = piwikHelper.addBreakpoints(url, '|||');
		url = $(document.createElement('p')).text(url).html();
		url = url.replace(/\|\|\|/g, '<wbr />&#8203;'); // &#8203; is for internet explorer
		return url;
	},

    getAngularDependency: function (dependency) {
        return angular.element(document).injector().get(dependency);
    },

    /**
     * As we still have a lot of old jQuery code and copy html from node to node we sometimes have to trigger the
     * compiling of angular components manually.
     *
     * @param selector
     * @param {object} options
     * @param {object} options.scope if supplied, a new isolate scope is created as the parent scope of the
     *                               compiled angular components. The properties in this object are
     *                               added to the new scope.
     */
    compileAngularComponents: function (selector, options) {
        options = options || {};

        var $element = $(selector);

        if (!$element.length) {
            return;
        }

        angular.element(document).injector().invoke(function($compile) {
            var scope = angular.element($element).scope();
            if (!scope) {
                return;
            }

            if (options.scope) {
                scope = scope.$new(true);
                $.extend(scope, options.scope);
            }

            $compile($element)(scope);
        });
    },

    /**
     * Detection works currently only for directives defining an isolated scope. Functionality might need to be
     * extended if needed. Under circumstances you might call this method before calling compileAngularComponents()
     * to avoid compiling the same element twice.
     * @param selector
     */
    isAlreadyCompiledAngularComponent: function (selector) {
        var $element = $(selector);

        return ($element.length && $element.hasClass('ng-isolate-scope'));
    },

    /**
     * Detects whether angular is rendering the page. If so, the page will be reloaded automatically
     * via angular as soon as it detects a $locationChange
     *
     * @returns {number|jQuery}
     */
    isAngularRenderingThePage: function ()
    {
        return $('[piwik-reporting-page]').length;
    },

    /**
     * Moves an element further to the left by changing the left margin to make sure as much as possible of an element
     * is visible in the current viewport. The top position keeps unchanged.
     * @param elementToPosition
     */
    setMarginLeftToBeInViewport: function (elementToPosition) {
        var availableWidth = $(window).width();
        $(elementToPosition).css('marginLeft', '0px');
        var offset = $(elementToPosition).offset();
        if (!offset) {
            return;
        }
        var leftPos = offset.left;
        if (leftPos < 0) {
            leftPos = 0;
        }
        var widthSegmentForm = $(elementToPosition).outerWidth();
        if (leftPos + widthSegmentForm > availableWidth) {
            var extraSpaceForMoreBeauty = 16;
            var newLeft = availableWidth - widthSegmentForm - extraSpaceForMoreBeauty;
            if (newLeft < extraSpaceForMoreBeauty) {
                newLeft = extraSpaceForMoreBeauty;
            }
            var marginLeft = Math.abs(leftPos - newLeft);
            if (marginLeft > extraSpaceForMoreBeauty) {
                // we only move it further to the left if it is actually more than 16px to the left.
                // otherwise it is not really worth it and doesn't look as good.
                $(elementToPosition).css('marginLeft', (parseInt(marginLeft, 10) * -1) + 'px');
            }
        }
    },

    /**
     * Displays a Modal dialog. Text will be taken from the DOM node domSelector.
     * Given callback handles will be mapped to the buttons having a role attriute
     *
     * Dialog will be closed when a button is clicked and callback handle will be
     * called, if one was given for the clicked role
     *
     * @param {string} domSelector   domSelector for modal window
     * @param {object} handles       callback functions for available roles
     * @return {void}
     */
    modalConfirm: function(domSelector, handles, options)
    {
        if (!options) {
            options = {};
        }

        var domElem = $(domSelector);
        var buttons = [];

        var content = '<div class="modal"><div class="modal-content"></div>';
        content += '<div class="modal-footer"></div></div>';

        var $content = $(content).hide();
        var $footer = $content.find('.modal-footer');

        $('[role]', domElem).each(function(){
            var $button = $(this);
            var role  = $button.attr('role');
            var title = $button.attr('title');
            var text  = $button.val();
            $button.hide();

            var button = $('<a href="javascript:;" class="modal-action modal-close waves-effect waves-light btn-flat "></a>');
            button.text(text);
            if (title) {
                button.attr('title', title);
            }

            if(typeof handles[role] == 'function') {
                button.on('click', function(){
                    handles[role].apply()
                });
            }

            $footer.append(button);
        });

        $('body').append($content);
        $content.find('.modal-content').append(domElem);

        if (options && options.fixedFooter) {
            $content.addClass('modal-fixed-footer');
            delete options.fixedFooter;
        }

        domElem.show();
        $content.openModal(options);
    },

    getQueryStringWithParametersModified: function (queryString, newParameters) {
        if (queryString != '') {
            var r, i, keyvalue, keysvalues = newParameters.split('&');
            var appendUrl = '';
            for (i = 0; i < keysvalues.length; i++) {
                keyvalue = keysvalues[i].split('=');
                r = new RegExp('(^|[?&])' + keyvalue[0] + '=[^&]*');
                queryString = queryString.replace(r, '');

                // empty value, eg. &segment=, we remove the parameter from URL entirely
                if (keyvalue[1].length == 0) {
                    continue;
                }
                appendUrl += '&' + keyvalue[0] + '=' + keyvalue[1];
            }
            queryString += appendUrl;
            if (queryString[0] == '&') {
                queryString = '?' + queryString.substring(1);
            }
        } else {
            queryString = '?' + newParameters;
        }

        return queryString;
    },

    /**
     * Returns the current query string with the given parameters modified
     * @param {String} newparams parameters to be modified
     * @return {String}
     */
    getCurrentQueryStringWithParametersModified: function(newparams)
    {
        var queryString = String(window.location.search);
        if (newparams) {
            queryString = this.getQueryStringWithParametersModified(queryString, newparams);
        }
        return String(window.location.pathname) + queryString;
    },

  /**
   * Given param1=v1&param2=k2
   * returns: { "param1": "v1", "param2": "v2" }
   *
   * @param query string
   * @return {Object}
   */
    getArrayFromQueryString: function (query) {
      var params = {};
      var vars = query.split("&");
      for (var i=0;i<vars.length;i++) {
        var keyValue = vars[i].split("=");
        // Jquery will urlencode these, but we wish to keep the current raw value
        // use case: &segment=visitorId%3D%3Dabc...
        params[keyValue[0]] = decodeURIComponent(keyValue[1]);
      }
      return params;
    },

    /**
     *  Returns query string for an object of key,values
     *  Note: we don't use $.param from jquery as it doesn't return array values the PHP way (returns a=v1&a=v2 instead of a[]=v1&a[]=v2)
     *  Example:
     *      piwikHelper.getQueryStringFromParameters({"a":"va","b":["vb","vc"],"c":1})
     *  Returns:
     *      a=va&b[]=vb&b[]=vc&c=1
     *  @param {object} parameters
     *  @return {string}
     */
    getQueryStringFromParameters: function(parameters)
    {
        var queryString = '';
        if(!parameters || parameters.length==0) {
            return queryString;
        }
        for(var name in parameters) {
            var value = parameters[name];
            if(typeof value == 'object') {
                for(var i in value) {
                    queryString += name + '[]=' + value[i] + '&';
                }
            } else {
                queryString += name + '=' + value + '&';
            }
        }
        return queryString.substring(0, queryString.length-1);
    },

    /**
     * Displays the given ajax error message within the given id element
     * @param {string} message       error message
     * @param {string} errorDivID    id of the domNode (defaults to ajaxError)
     * @return {void}
     */
    showAjaxError: function( message, errorDivID )
    {
        errorDivID = errorDivID || 'ajaxError';
        $('#'+errorDivID).html(message).show();
    },

    /**
     * Hides the error message with the given id
     * @param {string} [errorDivID]   id of the domNode (defaults to ajaxError)
     * @return {void}
     */
    hideAjaxError: function(errorDivID)
    {
        errorDivID = errorDivID || 'ajaxError';
        $('#'+errorDivID).hide();
    },

    /**
     * Shows the loading message with the given Id
     * @param {string} [loadingDivID]   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    showAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoadingDiv';
        $('#'+loadingDivID).show();
    },

    /**
     * Hides the loading message with the given id
     * @param {string} [loadingDivID]   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    hideAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoadingDiv';
        $('#'+loadingDivID).hide();
    },

    /**
     * Reloads the page after the given period
     * @param {int} timeoutPeriod
     * @return void
     */
    refreshAfter: function(timeoutPeriod)
    {
        if(timeoutPeriod == 0) {
            location.reload();
        } else {
            setTimeout("location.reload();",timeoutPeriod);
        }
    },

    redirect: function (params) {
        // add updated=X to the URL so that a "Your changes have been saved" message is displayed
        if (typeof params == 'object') {
            params = this.getQueryStringFromParameters(params);
        }
        var urlToRedirect = this.getCurrentQueryStringWithParametersModified(params);
        var updatedUrl = new RegExp('&updated=([0-9]+)');
        var updatedCounter = updatedUrl.exec(urlToRedirect);
        if (!updatedCounter) {
            urlToRedirect += '&updated=1';
        } else {
            updatedCounter = 1 + parseInt(updatedCounter[1]);
            urlToRedirect = urlToRedirect.replace(new RegExp('(&updated=[0-9]+)'), '&updated=' + updatedCounter);
        }
        var currentHashStr = window.location.hash;
        if(currentHashStr.length > 0) {
            urlToRedirect += currentHashStr;
        }
        this.redirectToUrl(urlToRedirect);
    },

    /**
     * Redirect to the given url
     * @param {string} url
     */
    redirectToUrl: function(url)
    {
        window.location = url;
    },

    lazyScrollToContent: function () {
        this.lazyScrollTo('#content', 250);
    },

    /**
     * Scrolls the window to the jquery element 'elem'
     * if the top of the element is not currently visible on screen
     * @param {string} elem Selector for the DOM node to scroll to, eg. '#myDiv'
     * @param {int} [time] Specifies the duration of the animation in ms
     * @param {boolean} [forceScroll] Whether to force scroll to an element.
     * @return {void}
     */
    lazyScrollTo: function(elem, time, forceScroll)
    {
        var $elem = $(elem);
        if (!$elem.length) {
            return;
        }

        var elemTop = $elem.offset().top;
        // only scroll the page if the graph is not visible
        if (elemTop < $(window).scrollTop()
            || elemTop > $(window).scrollTop()+$(window).height()
            || forceScroll
        ) {
            // scroll the page smoothly to the graph
            $.scrollTo(elem, time);
        }
    },

    /**
     * Returns the filtered/converted content of a textarea to be used for api requests
     * @param {string} textareaContent
     * @return {string}
     */
    getApiFormatTextarea: function (textareaContent) {
        if (typeof textareaContent == 'undefined') {
            return '';
        }
        return textareaContent.trim().split("\n").join(',');
    },

    shortcuts: {},

    /**
     * Register a shortcut
     *
     * @param {string} key key-stroke to be registered for this shortcut
     * @param {string } description  description to be shown in summary
     * @param callback method called when pressing the key
     */
    registerShortcut: function(key, description, callback) {

        piwikHelper.shortcuts[key] = description;

        Mousetrap.bind(key, callback);
    }
};

String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g,"");
};

/**
 * Returns true if the event keypress passed in parameter is the ENTER key
 * @param {Event} e   current window event
 * @return {boolean}
 */
function isEnterKey(e)
{
    return (window.event?window.event.keyCode:e.which)==13;
}

/**
 * Returns true if the event keypress passed in parameter is the ESCAPE key
 * @param {Event} e   current window event
 * @return {boolean}
 */
function isEscapeKey(e)
{
    return (window.event?window.event.keyCode:e.which)==27;
}

// workarounds
(function($){
try {
    // this code is not vital, so we make sure any errors are ignored

    //--------------------------------------
    //
    // monkey patch that works around bug in arc function of some browsers where
    // nothing gets drawn if angles are 2 * PI apart and in counter-clockwise direction.
    // affects some versions of chrome & IE 8
    //
    //--------------------------------------
    var oldArc = CanvasRenderingContext2D.prototype.arc;
    CanvasRenderingContext2D.prototype.arc = function(x, y, r, sAngle, eAngle, clockwise) {
        if (Math.abs(eAngle - sAngle - Math.PI * 2) < 0.000001 && !clockwise)
            eAngle -= 0.000001;
        oldArc.call(this, x, y, r, sAngle, eAngle, clockwise);
    };

    // Fix jQuery UI dialogs scrolling when click on links with tooltips
    jQuery.ui.dialog.prototype._focusTabbable = $.noop;

    // Fix jQuery UI tooltip displaying when dialog is closed by Esc key
    jQuery(document).keyup(function(e) {
      if (e.keyCode == 27) {
          $('.ui-tooltip').hide();
      }
    });

} catch (e) {}
}(jQuery));
