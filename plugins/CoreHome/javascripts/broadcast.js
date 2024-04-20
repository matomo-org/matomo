/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *  broadcast object is to help maintain a hash for link clicks and ajax calls
 *  so we can have back button and refresh button working.
 *
 * @type {object}
 */
var broadcast = {

    /**
     * Last known hash url without popover parameter
     */
    currentHashUrl: false,

    /**
     * Last known popover parameter
     */
    currentPopoverParameter: false,

    /**
     * Callbacks for popover parameter change
     */
    popoverHandlers: [],

    /**
     * Holds the stack of popovers opened in sequence. When closing a popover, the last popover in the stack
     * is opened (if any).
     */
    popoverParamStack: [],

    /**
     * Force reload once
     */
    forceReload: false,

    /**
     * Suppress content update on hash changing
     */
    updateHashOnly: false,

    isWidgetizedDashboard: function() {
        return broadcast.getValueFromUrl('module') == 'Widgetize' && broadcast.getValueFromUrl('moduleToWidgetize') == 'Dashboard';
    },

    isWidgetizeRequestWithoutSession: function() {
        // whenever a token_auth is set in the URL, we assume a widget or page is tried to be shown widgetized.
        return broadcast.getValueFromUrl('token_auth') != '' && broadcast.getValueFromUrl('force_api_session') != '1';
    },

    /**
     * Returns if the current page is the login page
     * @return {boolean}
     */
    isLoginPage: function() {
        return !!$('body#loginPage').length;
    },

    /**
     * Returns if the current page is the no data page
     * @return {boolean}
     */
    isNoDataPage: function() {
        return !!$('body#site-without-data').length;
    },

    /**
     * Returns the current hash with updated parameters that were provided in ajaxUrl
     *
     * Parameters like idGoal and idDashboard will be automatically reset if the won't be relevant anymore
     *
     * NOTE: this method does not issue any ajax call, but returns the hash instead
     *
     * @param {string} ajaxUrl  querystring with parameters to be updated
     * @return {string} current hash with updated parameters
     */
    buildReportingUrl: function (ajaxUrl) {

        // available in global scope
        var currentHashStr = broadcast.getHash();

        ajaxUrl = ajaxUrl.replace(/^\?|&#/, '');

        var params_vals = ajaxUrl.split("&");
        for (var i = 0; i < params_vals.length; i++) {
            currentHashStr = broadcast.updateParamValue(params_vals[i], currentHashStr);
        }

        // if the module is not 'Goals', we specifically unset the 'idGoal' parameter
        // this is to ensure that the URLs are clean (and that clicks on graphs work as expected - they are broken with the extra parameter)
        var action = broadcast.getParamValue('action', currentHashStr);
        if (action != 'goalReport' && action != 'ecommerceReport' && action != 'products' && action != 'sales') {
            currentHashStr = broadcast.updateParamValue('idGoal=', currentHashStr);
        }
        // unset idDashboard if use doesn't display a dashboard
        var module = broadcast.getParamValue('module', currentHashStr);
        if (module != 'Dashboard') {
            currentHashStr = broadcast.updateParamValue('idDashboard=', currentHashStr);
        }

        return '#' + currentHashStr;
    },

    /**
     * propagateNewPage() -- update url value and load new page,
     * Example:
     *         1) We want to update idSite to both search query and hash then reload the page,
     *         2) update period to both search query and hash then reload page.
     *
     * Expecting:
     *         str = "param1=newVal1&param2=newVal2";
     *
     * NOTE: This method will refresh the page with new values.
     *
     * @param {string} str  url with parameters to be updated
     * @param {boolean} [showAjaxLoading] whether to show the ajax loading gif or not.
     * @param {string} strHash additional parameters that should be updated on the hash
     * @param {array} paramsToRemove Optional parameters to remove from the URL.
     * @return {void}
     */
    propagateNewPage: function (str, showAjaxLoading, strHash, paramsToRemove, wholeNewUrl) {
        // abort all existing ajax requests
        globalAjaxQueue.abort();

        paramsToRemove = paramsToRemove || [];

        if (typeof showAjaxLoading === 'undefined' || showAjaxLoading) {
            piwikHelper.showAjaxLoading();
        }

        var params_vals = str.split("&");

        // available in global scope
        var currentSearchStr = window.location.search;
        var currentHashStr = broadcast.getHashFromUrl();

        if (!currentSearchStr) {
            currentSearchStr = '?';
        }

        var oldUrl = currentSearchStr + currentHashStr;
        var newUrl;

        if (!wholeNewUrl) {
          // remove all array query params that are currently set. if we don't do this the array parameters we add
          // just get added to the existing parameters.
          params_vals.forEach(function (param) {
            if (/\[]=/.test(decodeURIComponent(param))) {
              var paramName = decodeURIComponent(param).split('[]=')[0];
              removeParam(paramName);
            }
          });

          // remove parameters if needed
          paramsToRemove.forEach(function (paramName) {
            removeParam(paramName);
          });

          // update/add parameters based on whether the parameter is an array param or not
          params_vals.forEach(function (param) {
            if (!param.length) {
              return; // updating with empty string would destroy some values
            }

            if (/\[]=/.test(decodeURIComponent(param))) { // array param value
              currentSearchStr = broadcast.addArrayParamValue(param, currentSearchStr);

              if (currentHashStr.length !== 0) {
                currentHashStr = broadcast.addArrayParamValue(param, currentHashStr);
              }
            } else {
              // update both the current search query and hash string
              currentSearchStr = broadcast.updateParamValue(param, currentSearchStr);

              if (currentHashStr.length !== 0) {
                currentHashStr = broadcast.updateParamValue(param, currentHashStr);
              }
            }
          });

          var updatedUrl = new RegExp('&updated=([0-9]+)');
          var updatedCounter = updatedUrl.exec(currentSearchStr);
          if (!updatedCounter) {
            currentSearchStr += '&updated=1';
          } else {
            updatedCounter = 1 + parseInt(updatedCounter[1]);
            currentSearchStr = currentSearchStr.replace(new RegExp('(&updated=[0-9]+)'), '&updated=' + updatedCounter);
          }

          if (strHash && currentHashStr.length != 0) {
            var params_hash_vals = strHash.split("&");
            for (var i = 0; i < params_hash_vals.length; i++) {
              currentHashStr = broadcast.updateParamValue(params_hash_vals[i], currentHashStr);
            }
          }

          // Now load the new page.
          newUrl = currentSearchStr + currentHashStr;
        } else {
          newUrl = wholeNewUrl;
        }

        if (oldUrl == newUrl) {
            window.location.reload();
        } else {
            this.forceReload = true;
            window.location.href = newUrl;
        }
        return false;

        function removeParam(paramName) {
            var paramRegex = new RegExp(paramName + '(\\[]|%5B%5D)?=[^&?#]*&?', 'gi');
            currentSearchStr = currentSearchStr.replace(paramRegex, '');
            currentHashStr = currentHashStr.replace(paramRegex, '');
        }
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
    updateParamValue: function (newParamValue, urlStr) {
        var p_v = newParamValue.split("=");

        var paramName = p_v[0];
        var valFromUrl = broadcast.getParamValue(paramName, urlStr) || broadcast.getParamValue(encodeURIComponent(paramName), urlStr);
        // if set 'idGoal=' then we remove the parameter from the URL automatically (rather than passing an empty value)
        var paramValue = p_v[1];
        if (paramValue == '') {
            newParamValue = '';
        }
        var getQuotedRegex = function(str) {
            return (str+'').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
        };

        if (valFromUrl != '' || urlStr.indexOf(paramName + '=') !== -1) {
            // replacing current param=value to newParamValue;
            valFromUrl = getQuotedRegex(valFromUrl);
            var regToBeReplace = new RegExp(paramName + '=' + valFromUrl, 'ig');
            if (newParamValue == '') {
                // if new value is empty remove leading &, as well
                regToBeReplace = new RegExp('[\&]?(' + paramName + '|' + encodeURIComponent(paramName) + ')=' + valFromUrl, 'ig');
            }
            urlStr = urlStr.replace(regToBeReplace, newParamValue);
        } else if (newParamValue != '') {
            urlStr += (urlStr == '') ? newParamValue : '&' + newParamValue;
        }

        return urlStr;
    },

    /**
     * Adds a query param value. Use it to add an array parameter value where you don't want to remove an existing value first.
     *
     * @param newParamValue
     * @param urlStr
     */
    addArrayParamValue: function (newParamValue, urlStr) {
        if (urlStr.indexOf('?') === -1) {
            urlStr += '?';
        } else {
            urlStr += '&';
        }
        return urlStr + newParamValue;
    },

    /**
     * Loads a popover by adding a 'popover' query parameter to the current URL and
     * indirectly executing the popover handler.
     *
     * This function should be called to open popovers that can be opened by URL alone.
     * That is, if you want users to be able to copy-paste the URL displayed when a popover
     * is open into a new browser window/tab and have the same popover open, you should
     * call this function.
     *
     * In order for this function to open a popover, there must be a popover handler
     * associated with handlerName. To associate one, call broadcast.addPopoverHandler.
     *
     * @param {String} handlerName The name of the popover handler.
     * @param {String} value The String value that should be passed to the popover
     *                       handler.
     */
    propagateNewPopoverParameter: function (handlerName, value) {
        var popover = '';
        if (handlerName && '' != value && 'undefined' != typeof value) {
            popover = handlerName + ':' + value;

            // between jquery.history and different browser bugs, it's impossible to ensure
            // that the parameter is en- and decoded the same number of times. in order to
            // make sure it doesn't change, we have to manipulate the url encoding a bit.
            popover = encodeURIComponent(popover);
            popover = popover.replace(/%/g, '\$');

            broadcast.popoverParamStack.push(popover);
        } else {
            broadcast.popoverParamStack.pop();
            if (broadcast.popoverParamStack.length) {
                popover = broadcast.popoverParamStack[broadcast.popoverParamStack.length - 1];
            }
        }

        var MatomoUrl = window.CoreHome.MatomoUrl;
        MatomoUrl.updateHash(
          Object.assign({}, MatomoUrl.hashParsed.value, { popover }),
        );
    },

    /**
     * Resets the popover param stack ensuring when a popover is closed, no new popover will
     * be loaded.
     */
    resetPopoverStack: function () {
        broadcast.popoverParamStack = [];
    },

    /**
     * Adds a handler for the 'popover' query parameter.
     *
     * @see broadcast#propagateNewPopoverParameter
     *
     * @param {String} handlerName The handler name, eg, 'visitorProfile'. Should identify
     *                             the popover that the callback will open up.
     * @param {Function} callback This function should open the popover. It should take
     *                            one string parameter.
     */
    addPopoverHandler: function (handlerName, callback) {
        broadcast.popoverHandlers[handlerName] = callback;
    },

    /**
     * Loads the given url with ajax and replaces the content
     *
     * Note: the method is replaced in Overlay/javascripts/Piwik_Overlay.js - keep this in mind when making changes.
     *
     * @param {string} urlAjax  url to load
     * @return {Boolean}
     */
    loadAjaxContent: function (urlAjax) {
        if(broadcast.getParamValue('module', urlAjax) == 'API') {
            broadcast.lastUrlRequested = null;
            $('#content').html("Loading content from the API and displaying it within Piwik is not allowed.");
            piwikHelper.hideAjaxLoading();
            return false;
        }

        piwikHelper.hideAjaxError('loadingError');
        piwikHelper.showAjaxLoading();
        $('#content').empty();
        $("object").remove();

        urlAjax = urlAjax.match(/^\?/) ? urlAjax : "?" + urlAjax;
        broadcast.lastUrlRequested = urlAjax;

        function sectionLoaded(content, status, request) {
            if (request) {
                var responseHeader = request.getResponseHeader('Content-Type');
                if (responseHeader && 0 <= responseHeader.toLowerCase().indexOf('json')) {
                    var message = 'JSON cannot be displayed for';
                    if (this.getParams && this.getParams['module']) {
                        message += ' module=' +  this.getParams['module'];
                    }
                    if (this.getParams && this.getParams['action']) {
                        message += ' action=' +  this.getParams['action'];
                    }
                    $('#content').text(message);
                    piwikHelper.hideAjaxLoading();
                    return;
                }
            }

            // if content is whole HTML document, do not show it, otherwise recursive page load could occur
            var htmlDocType = '<!DOCTYPE';
            if (content.substring(0, htmlDocType.length) == htmlDocType) {
                // if the content has an error message, display it
                if ($(content).filter('title').text() == 'Piwik › Error') {
                    content = $(content).filter('#contentsimple');
                } else {
                    return;
                }
            }

            if (urlAjax == broadcast.lastUrlRequested) {
                $('#content').html(content).show();
                $(broadcast).trigger('locationChangeSuccess', {element: $('#content'), content: content});
                piwikHelper.hideAjaxLoading();
                broadcast.lastUrlRequested = null;

                piwikHelper.compileVueDirectives('#content');
            }

            initTopControls();
        }

        var ajax = new ajaxHelper();
        ajax.setUrl(urlAjax);
        ajax._getDefaultPostParams = function () {
            return {};
        };
        ajax.setErrorCallback(broadcast.customAjaxHandleError);
        ajax.setCallback(sectionLoaded);
        ajax.setFormat('html');
        ajax.send();

        return false;
    },

    /**
     * Method to handle ajax errors
     * @param {XMLHttpRequest} deferred
     * @param {string} status
     * @return {void}
     */
    customAjaxHandleError: function (deferred, status) {
        broadcast.lastUrlRequested = null;

        piwikHelper.hideAjaxLoading();

        // do not display error message if request was aborted
        if(status == 'abort') {
            return;
        }

        $('#loadingError').show();
    },

    /**
     * Return hash string if hash exists on address bar.
     * else return false;
     *
     * @return {string|boolean}  current hash or false if it is empty
     */
    isHashExists: function () {
        var hashStr = broadcast.getHashFromUrl();

        if (hashStr != "") {
            return hashStr;
        } else {
            return false;
        }
    },

    /**
     * Get Hash from given url or from current location.
     * return empty string if no hash present.
     *
     * @param {string}  [url]  url to get hash from (defaults to current location)
     * @return {string} the hash part of the given url
     */
    getHashFromUrl: function (url) {
        var hashStr = "";
        // If url provided, give back the hash from url, else get hash from current address.
        if (url && url.match('#')) {
            hashStr = url.substring(url.indexOf("#"), url.length);
        }
        else {
            locationSplit = location.href.split('#');
            if(typeof locationSplit[1] != 'undefined') {
                hashStr = '#' + locationSplit[1];
            }
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
    getSearchFromUrl: function (url) {
        var searchStr = "";
        // If url provided, give back the query string from url, else get query string from current address.
        if (url && url.match(/\?/)) {
            searchStr = url.substring(url.indexOf("?"), url.length);
        } else {
            searchStr = location.search;
        }

        return searchStr;
    },

    /**
     * Extracts from a query strings, the request array
     * @param queryString
     * @returns {object}
     */
    extractKeyValuePairsFromQueryString: function (queryString, decode) {
        var pairs = queryString.replace(/%5B%5D/g, '[]').split('&');
        var result = {};
        for (var i = 0; i != pairs.length; ++i) {
            if (pairs[i] === '') {
              continue;
            }

            // attn: split with regex has bugs in several browsers such as IE 8
            // so we need to split, use the first part as key and rejoin the rest
            var pair = pairs[i].split('=');
            var key = pair.shift();
            var value = pair.join('=');
            if (decode) {
              value = decodeURIComponent(value);
            }
            if (/\[.*?]$/.test(key)) {
              key = key.replace(/\[.*?]$/, '');
              result[key] = result[key] || [];
              result[key].push(value);
            } else {
              result[key] = value;
            }
        }
        return result;
    },

    /**
     * Returns all key-value pairs in query string of url.
     *
     * @param {string} url url to check. if undefined, null or empty, current url is used.
     * @param {boolean} decodeValues if true, also applies decodeURIComponent to values. (Not
     *                               true by default for BC.)
     * @return {object} key value pair describing query string parameters
     */
    getValuesFromUrl: function (url, decode) {
        var searchString = this._removeHashFromUrl(url).split('?')[1] || '';
        return this.extractKeyValuePairsFromQueryString(searchString, decode);
    },

    /**
     * help to get param value for any given url string with provided param name
     * if no url is provided, it will get param from current address.
     * return:
     *   Empty String if param is not found.
     *
     * @param {string} param   parameter to search for
     * @param {string} [url]     url to check, defaults to current location
     * @return {string} value of the given param within the given url
     */
    getValueFromUrl: function (param, url) {
        var searchString = this._removeHashFromUrl(url);
        return broadcast.getParamValue(param, searchString);
    },

    /**
     * NOTE: you should probably be using broadcast.getValueFromUrl instead!
     *
     * @param {string} param   parameter to search for
     * @param {string} [url]   url to check
     * @return {string} value of the given param within the hash part of the given url
     */
    getValueFromHash: function (param, url) {
        var hashStr = broadcast.getHashFromUrl(url);
        if (hashStr.slice(0, 1) == '#') {
            hashStr = hashStr.slice(1);
        }
        hashStr = hashStr.split('#')[0];

        return broadcast.getParamValue(param, hashStr);
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
    getParamValue: function (param, url) {
        var lookFor = param + '=';

        if (url.indexOf('?') >= 0) {
            url = url.slice(url.indexOf('?')+1);
        }

        var urlPieces = url.split('&');

        // look for the latest occurrence of the parameter if available
        for (var i=urlPieces.length-1; i>=0; i--) {
            if (urlPieces[i].indexOf(lookFor) === 0) {
                return getSingleValue(urlPieces[i]);
            }
        }

        // gather parameter array if available
        lookFor = param + '[]=';
        var result = [];
        for (var j=0; j<urlPieces.length; j++) {
            if (urlPieces[j].indexOf(lookFor) === 0) {
                result.push(getSingleValue(urlPieces[j]));
            } else if (decodeURIComponent(urlPieces[j]).indexOf(lookFor) === 0) {
                result.push(getSingleValue(decodeURIComponent(urlPieces[j])));
            }
        }
        return result.length ? result : '';

        function getSingleValue(urlPart) {
            var startPos = urlPart.indexOf("=");
            if (startPos === -1) {
                return '';
            }
            var value = urlPart.substring(startPos+1);

            // we sanitize values to add a protection layer against XSS
            // parameters 'segment', 'popover' and 'compareSegments' are not sanitized, since segments are designed to accept any user input
            if(param != 'segment' && param != 'popover' && param != 'compareSegments') {
                value = value.replace(/[^_%~\*\+\-\<\>!@\$\.()=,;0-9a-zA-Z]/gi, '');
            }
            return value;
        }
    },

    /**
     * Returns the hash without the starting #
     * @return {string} hash part of the current url
     */
    getHash: function () {
        return broadcast.getHashFromUrl().replace(/^#/, '').split('#')[0];
    },

    /**
     * Removes the hash portion of a URL and returns the rest.
     *
     * @param {string} url
     * @return {string} url w/o hash
     */
    _removeHashFromUrl: function (url) {
        var searchString = '';
        if (url) {
            var urlParts = url.split('#');
            searchString = urlParts[0];
        } else {
            searchString = window.location.search;
        }
        return searchString;
    }
};

window.broadcast = broadcast; // hack to get broadcast to work in vue (jest) tests
