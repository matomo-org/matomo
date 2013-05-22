/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var piwikHelper = {

    htmlDecode: function(value)
    {
        return $('<div/>').html(value).text();
    },

    /*
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
    modalConfirm: function( domSelector, handles )
    {
        var domElem = $(domSelector);
        var buttons = {};

        $('[role]', domElem).each(function(){
            var role = $(this).attr('role');
            var text = $(this).val();
            if(typeof handles[role] == 'function') {
                buttons[text] = function(){$(this).dialog("close"); handles[role].apply()};
            } else {
                buttons[text] = function(){$(this).dialog("close");};
            }
            $(this).hide();
        });

        domElem.dialog({
            resizable: false,
            modal: true,
            buttons: buttons,
            width: 650,
            position: ['center', 90]
        });
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
     * @param {object} newparams parameters to be modified
     * @return {String}
     */
    getCurrentQueryStringWithParametersModified: function(newparams)
    {
        var queryString = String(window.location.search);
        if (newparams) {
            queryString = this.getQueryStringWithParametersModified(queryString, newparams);
        }
        var value = String(window.location.pathname) + queryString;

        return value;
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
        var rawValue = decodeURIComponent(keyValue[1]);
        params[keyValue[0]] = rawValue;
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
     * @param {string} errorDivID   id of the domNode (defaults to ajaxError)
     * @return {void}
     */
    hideAjaxError: function(errorDivID)
    {
        errorDivID = errorDivID || 'ajaxError';
        $('#'+errorDivID).hide();
    },

    /**
     * Shows the loading message with the given Id
     * @param {string} loadingDivID   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    showAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoading';
        $('#'+loadingDivID).show();
    },

    /**
     * Hides the loading message with the given id
     * @param {string} loadingDivID   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    hideAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoading';
        $('#'+loadingDivID).hide();
    },

    /**
     * Returns default configuration for ajax requests
     * @param {string} loadingDivID   id of domNode used for loading message
     * @param {string} errorDivID     id of domNode used for error messages
     * @param {object} params         params used for handling response
     * @return {object}
     * @deprecated sine 1.9.3 - will be removed in 2.0
     * @see use ajaxHelper for ajax requests
     */
    getStandardAjaxConf: function(loadingDivID, errorDivID, params)
    {
        piwikHelper.showAjaxLoading(loadingDivID);
        piwikHelper.hideAjaxError(errorDivID);
        var ajaxRequest = {};
        ajaxRequest.type = 'GET';
        ajaxRequest.url = 'index.php';
        ajaxRequest.dataType = 'json';
        ajaxRequest.error = piwikHelper.ajaxHandleError;
        ajaxRequest.success = function(response) { piwikHelper.ajaxHandleResponse(response, loadingDivID, errorDivID, params); };
        return ajaxRequest;
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

    /**
     * Redirect to the given url
     * @param {string} url
     */
    redirectToUrl: function(url)
    {
        window.location = url;
    },

    /**
     * Method to handle ajax errors
     * @param {XMLHttpRequest} deferred
     * @param {string} status
     * @return {void}
     * @deprecated sine 1.9.3 - will be removed in 2.0
     * @see use ajaxHelper for ajax requests
     */
    ajaxHandleError: function(deferred, status)
    {
        // do not display error message if request was aborted
        if(status == 'abort') {
            return;
        }
        $('#loadingError').show();
        setTimeout( function(){
            $('#loadingError').fadeOut('slow');
            }, 2000);
    },

    /**
     * Method to handle ajax response
     * @param {object} response
     * @param {string} loadingDivID
     * @param {string} errorDivID
     * @param {object} params
     * @return {void}
     * @deprecated since 1.9.3 - will be removed in 2.0
     * @see use ajaxHelper for ajax requests
     */
    ajaxHandleResponse: function(response, loadingDivID, errorDivID, params)
    {
        if(response.result == "error")
        {
            piwikHelper.hideAjaxLoading(loadingDivID);
            piwikHelper.showAjaxError(response.message, errorDivID);
        }
        else
        {
            // add updated=1 to the URL so that a "Your changes have been saved" message is displayed
            var urlToRedirect = piwikHelper.getCurrentQueryStringWithParametersModified(params);
            var updatedUrl = new RegExp('&updated=([0-9]+)');
            var updatedCounter = updatedUrl.exec(urlToRedirect);
            if(!updatedCounter)
            {
                urlToRedirect += '&updated=1';
            }
            else
            {
                updatedCounter = 1 + parseInt(updatedCounter[1]);
                urlToRedirect = urlToRedirect.replace(new RegExp('(&updated=[0-9]+)'), '&updated=' + updatedCounter);
            }
            var currentHashStr = window.location.hash;
            if(currentHashStr.length > 0) {
                urlToRedirect += currentHashStr;
            }
            piwikHelper.redirectToUrl(urlToRedirect);
        }
    },

    /**
     * Scrolls the window to the jquery element 'elem'
     * if the top of the element is not currently visible on screen
     * @param {string} elem Selector for the DOM node to scroll to, eg. '#myDiv'
     * @param {int} time Specifies the duration of the animation in ms
     * @return {void}
     */
    lazyScrollTo: function(elem, time)
    {
        var elemTop = $(elem).offset().top;
        // only scroll the page if the graph is not visible
        if(elemTop < $(window).scrollTop()
        || elemTop > $(window).scrollTop()+$(window).height())
        {
            // scroll the page smoothly to the graph
            $.scrollTo(elem, time);
        }
    },

    /**
     * Returns the filtered/converted content of a textarea to be used for api requests
     * @param {string} textareaContent
     * @return {string}
     */
    getApiFormatTextarea: function (textareaContent)
    {
        if(typeof textareaContent == 'undefined') {
            return '';
        }
        return textareaContent.trim().split("\n").join(',');
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

// workarounds
(function($){
try { // this code is not vital, so we make sure any errors are ignored

// monkey patch that works around bug in arc function of some browsers where
// nothing gets drawn if angles are 2 * PI apart and in counter-clockwise direction.
// affects some versions of chrome & IE 8
var oldArc = CanvasRenderingContext2D.prototype.arc;
CanvasRenderingContext2D.prototype.arc = function(x, y, r, sAngle, eAngle, clockwise) {
	if (Math.abs(eAngle - sAngle - Math.PI * 2) < 0.000001 && !clockwise)
		eAngle -= 0.000001;
	oldArc.call(this, x, y, r, sAngle, eAngle, clockwise);
};

} catch (e) {}
}(jQuery));
