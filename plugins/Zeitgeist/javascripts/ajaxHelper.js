/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * global ajax queue
 *
 * @type {Array} array holding XhrRequests with automatic cleanup
 */
var globalAjaxQueue = new Array();

/**
 * Extend Array.push with automatic cleanup for finished requests
 *
 * @return {Object}
 */
globalAjaxQueue.push = function () {
    // cleanup ajax queue
    for (var i = this.length; i--;) {
        if (!this[i] || this[i].readyState == 4) {
            this.splice(i, 1);
        }
    }
    // call original array push
    return Array.prototype.push.apply(this, arguments);
};

/**
 * Extend with abort function to abort all queued requests
 *
 * @return {void}
 */
globalAjaxQueue.abort = function () {
    // abort all queued requests
    for (var i = this.length; i--;) {
        this[i] && this[i].abort && this[i].abort(); // abort if possible
    }
    // remove all elements from array
    this.splice(0, this.length);
};

/**
 * Global ajax helper to handle requests within piwik
 *
 * @type {Object}
 */
function ajaxHelper() {

    /**
     * Format of response
     * @type {String}
     */
    this.format =         'json';

    /**
     * Should ajax request be asynchronous
     * @type {Boolean}
     */
    this.async =          true;

    /**
     * Callback function to be executed on success
     */
    this.callback =       function () {};

    /**
     * Use this.callback if an error is returned
     * @type {Boolean}
     */
    this.useRegularCallbackInCaseOfError = false;

    /**
     * Callback function to be executed on error
     */
    this.errorCallback =  this.defaultErrorCallback;

    /**
     * Params to be passed as GET params
     * @type {Object}
     * @see ajaxHelper._mixinDefaultGetParams
     */
    this.getParams =      {};

    /**
     * Params to be passed as GET params
     * @type {Object}
     * @see ajaxHelper._mixinDefaultPostParams
     */
    this.postParams =     {};

    /**
     * Element to be displayed while loading
     * @type {String}
     */
    this.loadingElement = null;

    /**
     * Element to be displayed on error
     * @type {String}
     */
    this.errorElement = '#ajaxError';

    /**
     * Handle for current request
     * @type {XMLHttpRequest}
     */
    this.requestHandle =  null;

    /**
     * Adds params to the request.
     * If params are given more then once, the latest given value is used for the request
     *
     * @param {object}  params
     * @param {string}  type  type of given parameters (POST or GET)
     * @return {void}
     */
    this.addParams = function (params, type) {
        for (var key in params) {
            if(type.toLowerCase() == 'get') {
                this.getParams[key] = params[key];
            } else if(type.toLowerCase() == 'post') {
                this.postParams[key] = params[key];
            }
        }
    };
    
    /**
     * Gets this helper instance ready to send a bulk request. Each argument to this
     * function is a single request to use.
     */
    this.setBulkRequests = function () {
        var urls = [];
        for (var i = 0; i != arguments.length; ++i) {
            urls.push($.param(arguments[i]));
        }

        this.addParams({
            module: 'API',
            method: 'API.getBulkRequest',
            urls: urls,
            format: 'json'
        }, 'post');
    };

    /**
     * Sets the callback called after the request finishes
     *
     * @param {function} callback  Callback function
     * @return {void}
     */
    this.setCallback = function (callback) {
        this.callback = callback;
    };

    /**
     * Set that the callback passed to setCallback() should be used if an application error (i.e. an
     * Exception in PHP) is returned.
     */
    this.useCallbackInCaseOfError = function () {
        this.useRegularCallbackInCaseOfError = true;
    };

    /**
     * Set callback to redirect on success handler
     * &update=1(+x) will be appended to the current url
     *
     * @param {object} params to modify in redirect url
     * @return {void}
     */
    this.redirectOnSuccess = function (params) {
        this.setCallback(function(response) {
            // add updated=X to the URL so that a "Your changes have been saved" message is displayed
            if (typeof params == 'object') {
                params = piwikHelper.getQueryStringFromParameters(params);
            }
            var urlToRedirect = piwikHelper.getCurrentQueryStringWithParametersModified(params);
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
            piwikHelper.redirectToUrl(urlToRedirect);
        });
    };


    /**
     * Sets the callback called in case of an error within the request
     *
     * @param {function} callback  Callback function
     * @return {void}
     */
    this.setErrorCallback = function (callback) {
        this.errorCallback = callback;
    };

    /**
     * error callback to use by default
     *
     * @param deferred
     * @param status
     */
    this.defaultErrorCallback = function(deferred, status)
    {
        // do not display error message if request was aborted
        if(status == 'abort') {
            return;
        }
        $('#loadingError').show();
        setTimeout( function(){
            $('#loadingError').fadeOut('slow');
        }, 2000);
    };

    /**
     * Sets the response format for the request
     *
     * @param {string} format  response format (e.g. json, html, ...)
     * @return {void}
     */
    this.setFormat = function (format) {
        this.format = format;
    };

    /**
     * Set the div element to show while request is loading
     *
     * @param {String} element  selector for the loading element
     */
    this.setLoadingElement = function (element) {
        if (!element) {
            element = '#ajaxLoading';
        }
        this.loadingElement = element;
    };

    /**
     * Set the div element to show on error
     *
     * @param {String} element  selector for the error element
     */
    this.setErrorElement = function (element) {
        if (!element) {
            return;
        }
        this.errorElement = element;
    };

    /**
     * Send the request
     * @param {Boolean} sync  indicates if the request should be synchronous (defaults to false)
     * @return {void}
     */
    this.send = function (sync) {
        if (sync === true) {
            this.async = false;
        }

        if ($(this.errorElement).length) {
            $(this.errorElement).hide();
        }

        if (this.loadingElement) {
            $(this.loadingElement).fadeIn();
        }

        this.requestHandle = this._buildAjaxCall();
        globalAjaxQueue.push(this.requestHandle);
    };

    /**
     * Aborts the current request if it is (still) running
     * @return {void}
     */
    this.abort = function () {
        if (this.requestHandle && typeof this.requestHandle.abort == 'function') {
            this.requestHandle.abort();
            this.requestHandle = null;
        }
    };

    /**
     * Builds and sends the ajax requests
     * @return {XMLHttpRequest}
     * @private
     */
    this._buildAjaxCall = function () {
        var that = this;

        var parameters = this._mixinDefaultGetParams(this.getParams);

        var url = 'index.php?';

        // we took care of encoding &segment properly already, so we don't use $.param for it ($.param URL encodes the values)
        if(parameters['segment']) {
            url += 'segment=' + parameters['segment'] + '&';
            delete parameters['segment'];
        }
        if(parameters['date']) {
            url += 'date=' + decodeURIComponent(parameters['date']) + '&';
            delete parameters['date'];
        }
        url += $.param(parameters);
        var ajaxCall = {
            type:     'POST',
            async:    this.async !== false,
            url:      url,
            dataType: this.format || 'json',
            error:    this.errorCallback,
            success:  function (response) {
                if (that.loadingElement) {
                    $(that.loadingElement).hide();
                }

                if (response && response.result == 'error' && !that.useRegularCallbackInCaseOfError) {
                    if ($(that.errorElement).length && response.message) {
                        $(that.errorElement).html(response.message).fadeIn();
                        piwikHelper.lazyScrollTo(that.errorElement, 250);
                    }
                    return;
                }

                that.callback(response);
            },
            data:     this._mixinDefaultPostParams(this.postParams)
        };

        return $.ajax(ajaxCall);
    };

    /**
     * Mixin the default parameters to send as POST
     *
     * @param {object}   params   parameter object
     * @return {object}
     * @private
     */
    this._mixinDefaultPostParams = function (params) {

        var defaultParams = {
            token_auth: piwik.token_auth
        };

        for (var index in defaultParams) {

            if (!params[index]) {

                params[index] = defaultParams[index];
            }
        }

        return params;
    };

    /**
     * Mixin the default parameters to send as GET
     *
     * @param {object}   params   parameter object
     * @return {object}
     * @private
     */
    this._mixinDefaultGetParams = function (params) {

        var defaultParams = {
            idSite:  piwik.idSite || broadcast.getValueFromUrl('idSite'),
            period:  piwik.period || broadcast.getValueFromUrl('period'),
            segment: broadcast.getValueFromHash('segment', window.location.href.split('#')[1])
        };

        // never append token_auth to url
        if (params.token_auth) {
            params.token_auth = null;
            delete params.token_auth;
        }

        for (var key in defaultParams) {
            if (!params[key] && !this.postParams[key] && defaultParams[key]) {
                params[key] = defaultParams[key];
            }
        }

        // handle default date & period if not already set
        if (!params.date && !this.postParams.date) {
            params.date = piwik.currentDateString || broadcast.getValueFromUrl('date');
            if (params.period == 'range' && piwik.currentDateString) {
                params.date = piwik.startDateString + ',' + params.date;
            }
        }

        return params;
    };

    return this;
}
