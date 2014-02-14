/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.factory('piwikApi', function ($http, $q, $rootScope) {

    var url = 'index.php';
    var format = 'json';
    var getParams  = {};
    var postParams = {};

    var piwikApi = {};

    /**
     * Adds params to the request.
     * If params are given more then once, the latest given value is used for the request
     *
     * @param {object}  params
     * @param {string}  type  type of given parameters (POST or GET)
     * @return {void}
     */
    piwikApi.addParams = function (params, type) {
        if (typeof params == 'string') {
            params = broadcast.getValuesFromUrl(params);
        }

        for (var key in params) {
            if(type.toLowerCase() == 'get') {
                getParams[key] = params[key];
            } else if(type.toLowerCase() == 'post') {
                postParams[key] = params[key];
            }
        }
    };

    /**
     * Sets the base URL to use in the AJAX request.
     *
     * @param {string} url
     */
    piwikApi.setUrl = function (url) {
        this.addParams(broadcast.getValuesFromUrl(url), 'GET');
    };

    /**
     * Gets this helper instance ready to send a bulk request. Each argument to this
     * function is a single request to use.
     */
    piwikApi.setBulkRequests = function () {
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
     * Sets the response format for the request
     *
     * @param {string} theFormat  response format (e.g. json, html, ...)
     * @return {void}
     */
    piwikApi.setFormat = function (theFormat) {
        format = theFormat;
    };

    /**
     * Send the request
     * @param {Boolean} [sync]  indicates if the request should be synchronous (defaults to false)
     * @return {void}
     */
    piwikApi.send = function () {

        var deferred = $q.defer();

        var onError = function (message) {
            deferred.reject(message);
        };

        var onSuccess  = function (response) {
            if (response && response.result == 'error') {

                if (response.message) {
                    onError(response.message);

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(response.message, {
                        context: 'error',
                        type: 'toast',
                        id: 'ajaxHelper'
                    });
                    notification.scrollToNotification();
                } else {
                    onError(null);
                }

            } else {
                deferred.resolve(response);
            }
        };

        var ajaxCall = {
            method: 'POST',
            url: url,
            responseType: format,
            params: _mixinDefaultGetParams(getParams),
            data: $.param(_mixinDefaultPostParams(postParams)),
            timeout: deferred.promise,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        };

        $http(ajaxCall).success(onSuccess).error(onError);

        return deferred.promise;
    };

    /**
     * Mixin the default parameters to send as POST
     *
     * @param {object}   params   parameter object
     * @return {object}
     * @private
     */
     function _mixinDefaultPostParams (params) {

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
     * @param {object}   getParamsToMixin   parameter object
     * @return {object}
     * @private
     */
    function _mixinDefaultGetParams (getParamsToMixin) {

        var defaultParams = {
            idSite:  piwik.idSite || broadcast.getValueFromUrl('idSite'),
            period:  piwik.period || broadcast.getValueFromUrl('period'),
            segment: broadcast.getValueFromHash('segment', window.location.href.split('#')[1])
        };

        // never append token_auth to url
        if (getParamsToMixin.token_auth) {
            getParamsToMixin.token_auth = null;
            delete getParamsToMixin.token_auth;
        }

        for (var key in defaultParams) {
            if (!getParamsToMixin[key] && !postParams[key] && defaultParams[key]) {
                getParamsToMixin[key] = defaultParams[key];
            }
        }

        // handle default date & period if not already set
        if (!getParamsToMixin.date && !postParams.date) {
            getParamsToMixin.date = piwik.currentDateString || broadcast.getValueFromUrl('date');
            if (getParamsToMixin.period == 'range' && piwik.currentDateString) {
                getParamsToMixin.date = piwik.startDateString + ',' + getParamsToMixin.date;
            }
        }

        return getParamsToMixin;
    };

    /**
     * Convenient method for making an API request
     * @param getParams
     */
    piwikApi.fetch = function (getParams) {

        getParams.module = 'API';
        getParams.format = 'JSON';

        this.addParams(getParams, 'GET');

        return this.send();
    };

    return piwikApi;
});
