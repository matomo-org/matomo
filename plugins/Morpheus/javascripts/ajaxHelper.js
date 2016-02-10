/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * global ajax queue
 *
 * @type {Array} array holding XhrRequests with automatic cleanup
 */
var globalAjaxQueue = [];
globalAjaxQueue.active = 0;

/**
 * Removes all finished requests from the queue.
 *
 * @return {void}
 */
globalAjaxQueue.clean = function () {
    for (var i = this.length; i--;) {
        if (!this[i] || this[i].readyState == 4) {
            this.splice(i, 1);
        }
    }
};

/**
 * Extend Array.push with automatic cleanup for finished requests
 *
 * @return {Object}
 */
globalAjaxQueue.push = function () {
    this.active += arguments.length;

    // cleanup ajax queue
    this.clean();

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

    this.active = 0;
};

/**
 * Global ajax helper to handle requests within piwik
 *
 * @type {Object}
 * @constructor
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
     * A timeout for the request which will override any global timeout
     * @type {Boolean}
     */
    this.timeout =        null;

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
     * Callback function to be executed on complete (after error or success)
     */
    this.completeCallback =  function () {};

    /**
     * Params to be passed as GET params
     * @type {Object}
     * @see ajaxHelper._mixinDefaultGetParams
     */
    this.getParams =      {};

    /**
     * Base URL used in the AJAX request. Can be set by setUrl.
     *
     * It is set to '?' rather than 'index.php?' to increase chances that it works
     * including for users who have an automatic 301 redirection from index.php? to ?
     * POST values are missing when there is such 301 redirection. So by by-passing
     * this 301 redirection, we avoid this issue.
     *
     * @type {String}
     * @see ajaxHelper.setUrl
     */
    this.getUrl = '?';

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
        if (typeof params == 'string') {
            params = broadcast.getValuesFromUrl(params);
        }

        for (var key in params) {
            if(type.toLowerCase() == 'get') {
                this.getParams[key] = params[key];
            } else if(type.toLowerCase() == 'post') {
                this.postParams[key] = params[key];
            }
        }
    };

    /**
     * Sets the base URL to use in the AJAX request.
     *
     * @param {string} url
     */
    this.setUrl = function (url) {
        this.addParams(broadcast.getValuesFromUrl(url), 'GET');
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
     * Set a timeout (in milliseconds) for the request. This will override any global timeout.
     *
     * @param {integer} timeout  Timeout in milliseconds
     * @return {void}
     */
    this.setTimeout = function (timeout) {
        this.timeout = timeout;
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
     * @param {object} [params] to modify in redirect url
     * @return {void}
     */
    this.redirectOnSuccess = function (params) {
        this.setCallback(function() {
            piwikHelper.redirect(params);
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
     * Sets the complete callback which is called after an error or success callback.
     *
     * @param {function} callback  Callback function
     * @return {void}
     */
    this.setCompleteCallback = function (callback) {
        this.completeCallback = callback;
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
     * @param {String} [element]  selector for the loading element
     */
    this.setLoadingElement = function (element) {
        if (!element) {
            element = '#ajaxLoadingDiv';
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
     * @param {Boolean} [sync]  indicates if the request should be synchronous (defaults to false)
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

        var url = this.getUrl;
        if (url[url.length - 1] != '?') {
            url += '&';
        }

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
            complete: this.completeCallback,
            error:    function () {
                --globalAjaxQueue.active;

                if (that.errorCallback) {
                    that.errorCallback.apply(this, arguments);
                }
            },
            success:  function (response, status, request) {
                if (that.loadingElement) {
                    $(that.loadingElement).hide();
                }

                if (response && response.result == 'error' && !that.useRegularCallbackInCaseOfError) {

                    var placeAt = null;
                    var type    = 'toast';
                    if ($(that.errorElement).length && response.message) {
                        $(that.errorElement).show();
                        placeAt = that.errorElement;
                        type    = null;
                    }

                    if (response.message) {

                        var UI = require('piwik/UI');
                        var notification = new UI.Notification();
                        notification.show(response.message, {
                            placeat: placeAt,
                            context: 'error',
                            type: type,
                            id: 'ajaxHelper'
                        });
                        notification.scrollToNotification();
                    }

                } else {
                    that.callback(response, status, request);
                }

                --globalAjaxQueue.active;
                var piwik = window.piwik;
                if (piwik
                    && piwik.ajaxRequestFinished
                ) {
                    piwik.ajaxRequestFinished();
                }
            },
            data:     this._mixinDefaultPostParams(this.postParams)
        };

        if (this.timeout !== null) {
            ajaxCall.timeout = this.timeout;
        }

        return $.ajax(ajaxCall);
    };

    this._getDefaultPostParams = function () {
        return {
            token_auth: piwik.token_auth
        };
    }

    /**
     * Mixin the default parameters to send as POST
     *
     * @param {object}   params   parameter object
     * @return {object}
     * @private
     */
    this._mixinDefaultPostParams = function (params) {

        var defaultParams = this._getDefaultPostParams();

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
