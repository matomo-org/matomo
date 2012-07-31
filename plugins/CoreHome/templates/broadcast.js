/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *  broadcast object is to help maintain a hash for link clicks and ajax calls
 *  so we can have back button and refresh button working.
 *
 *   Other file that currently depending on this are:
 *     calendar.js
 *     period_select.tpl
 *     sites_selections.tpl
 *     menu.js, ...etc
 *
 * !! Load this once and only once. !!
 *
 * @type {object}
 */
var broadcast = {

    /**
     * Initialisation state
     * @type {Boolean}
     */
    _isInit: false,

    /**
     * Initializes broadcast object
     * @return {void}
     */
    init: function() {
        if(broadcast._isInit) {
            return;
        }
        broadcast._isInit = true;

        // Initialize history plugin.
        // The callback is called at once by present location.hash
        $.history.init(broadcast.pageload, {unescape: true});

        piwikHelper.showAjaxLoading();
    },

    /**
     * ========== PageLoad function =================
     * This function is called when:
     * 1. after calling $.history.init();
     * 2. after calling $.history.load();  //look at broadcast.changeParameter();
     * 3. after pushing "Go Back" button of a browser
     *
     * @param {string}  hash to load page with
     * @return {void}
     */
    pageload: function( hash )
    {
        broadcast.init();

        // Unbind any previously attached resize handlers
        $(window).off('resize');

        // hash doesn't contain the first # character.
        if( hash ) {
            // restore ajax loaded state
            broadcast.loadAjaxContent(hash);

            // Hack: make sure the "Widgets & Dashboard" is deleted on reload
            $('#dashboardSettings').remove();
            $('#dashboardWidgetsArea').dashboard('destroy');
        } else {
            // start page
            $('#content').empty();
        }
    },

    /**
     * propagateAjax -- update hash values then make ajax calls.
     *    example :
     *       1) <a href="javascript:broadcast.propagateAjax('module=Referers&action=getKeywords')">View keywords report</a>
     *       2) Main menu li also goes through this function.
     *
     * Will propagate your new value into the current hash string and make ajax calls.
     *
     * NOTE: this method will only make ajax call and replacing main content.
     *
     * @param {string} ajaxUrl  querystring with parameters to be updated
     * @return {void}
     */
    propagateAjax: function (ajaxUrl)
    {
        broadcast.init();

        // abort all existing ajax requests
        piwikHelper.abortQueueAjax();

        // available in global scope
        var currentHashStr = broadcast.getHash();

        ajaxUrl = ajaxUrl.replace(/^\?|&#/,'');

        var params_vals = ajaxUrl.split("&");
        for( var i=0; i<params_vals.length; i++ )
        {
            currentHashStr = broadcast.updateParamValue(params_vals[i],currentHashStr);
        }

        // if the module is not 'Goals', we specifically unset the 'idGoal' parameter
        // this is to ensure that the URLs are clean (and that clicks on graphs work as expected - they are broken with the extra parameter)
        var action = broadcast.getParamValue('action', currentHashStr);
        if( action != 'goalReport' && action != 'ecommerceReport')
        {
            currentHashStr = broadcast.updateParamValue('idGoal=', currentHashStr);
        }
        // unset idDashboard if use doesn't display a dashboard
        var module = broadcast.getParamValue('module', currentHashStr);
        if( module != 'Dashboard')
        {
            currentHashStr = broadcast.updateParamValue('idDashboard=', currentHashStr);
        }
        // Let history know about this new Hash and load it.
        $.history.load(currentHashStr);
    },

    /**
     * propagateNewPage() -- update url value and load new page,
     * Example:
     *         1) We want to update idSite to both search query and hash then reload the page,
     *         2) update period to both search query and hash then reload page.
     *
     * ** If you'd like to make ajax call with new values then use propagateAjax ** *
     *
     * Expecting:
     *         str = "param1=newVal1&param2=newVal2";
     *
     * Currently being use by:
     *
     *  handlePeriodClick,
     *  calendar.js,
     *  sites_selection.tpl
     *
     * NOTE: This method will refresh the page with new values.
     *
     * @param {string} str  url with parameters to be updated
     * @param {bool} showAjaxLoading whether to show the ajax loading gif or not.
     * @return {void}
     */
    propagateNewPage: function (str, showAjaxLoading)
    {
        // abort all existing ajax requests
        piwikHelper.abortQueueAjax();
		
		if (typeof showAjaxLoading === 'undefined' || showAjaxLoading)
		{
	        piwikHelper.showAjaxLoading();
        }

        var params_vals = str.split("&");

        // available in global scope
        var currentSearchStr = window.location.search;
        var currentHashStr = broadcast.getHashFromUrl();
        var oldUrl = currentSearchStr + currentHashStr;

        for( var i=0; i<params_vals.length; i++ ) {
            // update both the current search query and hash string
            currentSearchStr = broadcast.updateParamValue(params_vals[i],currentSearchStr);

            if(currentHashStr.length != 0 ) {
                currentHashStr   = broadcast.updateParamValue(params_vals[i],currentHashStr);
            }
        }

        // Now load the new page.
        var newUrl = currentSearchStr + currentHashStr;

        if(oldUrl == newUrl) {
            window.location.reload();
        } else {
            window.location.href = newUrl;
        }
        return false;
    },

    /*************************************************
     *
     *      Broadcast Supporter Methods:
     *
     *************************************************/

    /**
     * updateParamValue(newParamValue,urlStr) -- Helping propagate functions to update value to url string.
     * eg. I want to update date value to search query or hash query
     *
     * Expecting:
     *        urlStr : A Hash or search query string. e.g: module=whatever&action=index=date=yesterday
     *        newParamValue : A param value pair: e.g: date=2009-05-02
     *
     * Return module=whatever&action=index&date=2009-05-02
     *
     * @param {string} newParamValue   param to be updated
     * @param {string} urlStr          url to be updated
     * @return {string}  urlStr with updated param
     */
    updateParamValue: function(newParamValue, urlStr)
    {
        var p_v = newParamValue.split("=");

        var paramName = p_v[0];
        var valFromUrl = broadcast.getParamValue(paramName,urlStr);
        // if set 'idGoal=' then we remove the parameter from the URL automatically (rather than passing an empty value)
        var paramValue = p_v[1];
        if(paramValue == '')
        {
            newParamValue = '';
        }
        if( valFromUrl != '') {
            // replacing current param=value to newParamValue;
            var regToBeReplace = new RegExp(paramName + '=' + valFromUrl, 'ig');
            if(newParamValue == '') {
                // if new value is empty remove leading &, aswell
                regToBeReplace = new RegExp('[\&]?' + paramName + '=' + valFromUrl, 'ig');
            }
            urlStr = urlStr.replace( regToBeReplace, newParamValue );
        } else if(newParamValue != '') {
            urlStr += (urlStr == '') ? newParamValue : '&' + newParamValue;
        }

        return urlStr;
    },

    /**
     * Loads the given url with ajax and replaces the content
     *
     * @param {string} urlAjax  url to load
     * @return {Boolean}
     */
    loadAjaxContent: function(urlAjax)
    {
        piwikMenu.activateMenu(
                broadcast.getParamValue('module', urlAjax),
                broadcast.getParamValue('action', urlAjax),
                broadcast.getParamValue('idGoal', urlAjax) || broadcast.getParamValue('idDashboard', urlAjax)
        );

        piwikHelper.hideAjaxError('loadingError');
        piwikHelper.showAjaxLoading();
        $('#content').hide();
        $("object").remove();

        urlAjax = urlAjax.match(/^\?/) ? urlAjax : "?" + urlAjax;
        broadcast.lastUrlRequested = urlAjax;
        function sectionLoaded(content)
        {
            if(urlAjax == broadcast.lastUrlRequested) {
                $('#content').html( content ).show();
                piwikHelper.hideAjaxLoading();
                broadcast.lastUrlRequested = null;
            }
        }
        var ajaxRequest = {
            type: 'GET',
            url: urlAjax,
            dataType: 'html',
            async: true,
            error: broadcast.customAjaxHandleError,	// Callback when the request fails
            success: sectionLoaded, // Callback when the request succeeds
            data: new Object
        };
        piwikHelper.queueAjaxRequest( $.ajax(ajaxRequest) );
        return false;
    },

    /**
     * Method to handle ajax errors
     * @param {XMLHttpRequest} deferred
     * @param {string} status
     * @return {void}
     */
    customAjaxHandleError: function (deferred, status)
    {
        broadcast.lastUrlRequested = null;
        piwikHelper.ajaxHandleError(deferred, status);
    },

    /**
     * Return hash string if hash exists on address bar.
     * else return false;
     *
     * @return {string|false}
     */
    isHashExists: function()
    {
        var hashStr = broadcast.getHashFromUrl();

        if ( hashStr != "" ) {
            return hashStr;
        } else {
            return false;
        }
    },

    /**
     * Get Hash from given url or from current location.
     * return empty string if no hash present.
     *
     * @param {string}  url
     * @return {string} the hash part of the given url
     */
    getHashFromUrl: function(url)
    {
        var hashStr = "";
        // If url provided, give back the hash from url, else get hash from current address.
        if( url && url.match('#') ) {
            hashStr = url.substring(url.indexOf("#"),url.length);
        }
        else {
            hashStr = decodeURIComponent(location.hash);
        }

        return hashStr;
    },

    /**
     * Get search query from given url or from current location.
     * return empty string if no search query present.
     *
     * @param {string} url
     * @return {string}  the query part of the given url
     */
    getSearchFromUrl: function(url)
    {
        var searchStr = "";
        // If url provided, give back the query string from url, else get query string from current address.
        if( url && url.match(/\?/) ) {
            searchStr = url.substring(url.indexOf("?"),url.length);
        } else {
            searchStr = location.search;
        }

        return searchStr;
    },
    
    /**
     * Returns all key-value pairs in query string of url.
     * 
     * @param {string} url url to check. if undefined, null or empty, current url is used.
     * @return {object} key value pair describing query string parameters
     */
    getValuesFromUrl: function(url)
    {
    	var searchString = this._removeHashFromUrl(url).split('?')[1] || '',
    		pairs = searchString.split('&');
    	
    	var result = {};
    	for (var i = 0; i != pairs.length; ++i)
    	{
    		var pair = pairs[i].split('=');
    		result[pair[0]] = pair[1];
    	}
    	return result;
    },

    /**
     * help to get param value for any given url string with provided param name
     * if no url is provided, it will get param from current address.
     * return:
     *   Empty String if param is not found.
     *
     * @param {string} param   parameter to search for
     * @param {string} url     url to check
     * @return {string} value of the given param within the given url
     */
    getValueFromUrl: function (param, url)
    {
        var searchString = this._removeHashFromUrl(url);
        return broadcast.getParamValue(param,searchString);
    },

    /**
     * NOTE: you should probably be using broadcast.getValueFromUrl instead!
     *
     * @param {string} param   parameter to search for
     * @param {string} url     url to check
     * @return {string} value of the given param within the hash part of the given url
     */
    getValueFromHash: function(param, url)
    {
        var hashStr = broadcast.getHashFromUrl(url);
        return broadcast.getParamValue(param,hashStr);
    },


    /**
     * return value for the requested param, will return the first match.
     * out side of this class should use getValueFromHash() or getValueFromUrl() instead.
     * return:
     *   Empty String if param is not found.
     *
     * @param {string} param   parameter to search for
     * @param {string} url     url to check
     * @return {string} value of the given param within the given url
     */
    getParamValue: function (param, url)
    {
        var startStr = url.indexOf(param);

        if( startStr  >= 0 ) {
            var endStr = url.indexOf("&", startStr);
            if( endStr == -1 ) {
                endStr = url.length;
            }
            var value = url.substring(startStr + param.length +1,endStr);
            // sanitize values
            value = value.replace(/[^_%\-\<\>!@=,0-9a-zA-Z]/gi, '');

            return value;
        } else {
            return '';
        }
    },

    /**
     * Returns the hash without the starting #
     * @return {string} hash part of the current url
     */
    getHash: function ()
    {
        return broadcast.getHashFromUrl().replace(/^#/, '');
    },
	
	/**
	 * Removes the hash portion of a URL and returns the rest.
	 * 
	 * @param {string} url
	 * @return {string} url w/o hash
	 */
	_removeHashFromUrl: function(url)
	{
        var searchString = '';
        if( url ) {
            var urlParts = url.split('#');
            searchString = urlParts[0];
        } else {
            searchString = location.search;
        }
        return searchString;
	}
};
