/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.service').factory('piwikApi', function ($http, $q, $rootScope, piwik, $window) {

    var url = 'index.php';
    var format = 'json';
    var getParams  = {};
    var postParams = {};
    var allRequests = [];

    var piwikApi = {};

    /**
     * Adds params to the request.
     * If params are given more then once, the latest given value is used for the request
     *
     * @param {object}  params
     * @return {void}
     */
    function addParams (params) {
        if (typeof params == 'string') {
            params = piwik.broadcast.getValuesFromUrl(params);
        }

        for (var key in params) {
            getParams[key] = params[key];
        }
    }

    function reset () {
        getParams  = {};
        postParams = {};
    }

    /**
     * Send the request
     * @return $promise
     */
    function send () {
        var deferred = $q.defer(),
            requestPromise = deferred.promise;

        var onError = function (message) {
            deferred.reject(message);
        };

        var onSuccess = function (response) {
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

        var headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            // ie 8,9,10 caches ajax requests, prevent this
            'cache-control': 'no-cache'
        };

        var ajaxCall = {
            method: 'POST',
            url: url,
            responseType: format,
            params: _mixinDefaultGetParams(getParams),
            data: $.param(getPostParams(postParams)),
            timeout: requestPromise,
            headers: headers
        };

        $http(ajaxCall).success(onSuccess).error(onError);

        // we can't modify requestPromise directly and add an abort method since for some reason it gets
        // removed after then/finally/catch is called.
        var addAbortMethod = function (to) {
            return {
                then: function () {
                    return addAbortMethod(to.then.apply(to, arguments));
                },

                'finally': function () {
                    return addAbortMethod(to['finally'].apply(to, arguments));
                },

                'catch': function () {
                    return addAbortMethod(to['catch'].apply(to, arguments));
                },

                abort: function () {
                    deferred.reject();
                    return this;
                }
            };
        };

        var request = addAbortMethod(requestPromise);

        allRequests.push(request);

        return request;
    }

    /**
     * Get the parameters to send as POST
     *
     * @param {object}   params   parameter object
     * @return {object}
     * @private
     */
     function getPostParams () {
        return {
            token_auth: piwik.token_auth
        };
    }

    /**
     * Mixin the default parameters to send as GET
     *
     * @param {object}   getParamsToMixin   parameter object
     * @return {object}
     * @private
     */
    function _mixinDefaultGetParams (getParamsToMixin) {

        var defaultParams = {
            idSite:  piwik.idSite || piwik.broadcast.getValueFromUrl('idSite'),
            period:  piwik.period || piwik.broadcast.getValueFromUrl('period'),
            segment: piwik.broadcast.getValueFromHash('segment', $window.location.href.split('#')[1])
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
            getParamsToMixin.date = piwik.currentDateString || piwik.broadcast.getValueFromUrl('date');
            if (getParamsToMixin.period == 'range' && piwik.currentDateString) {
                getParamsToMixin.date = piwik.startDateString + ',' + getParamsToMixin.date;
            }
        }

        return getParamsToMixin;
    }

    piwikApi.abortAll = function () {
        reset();

        allRequests.forEach(function (request) {
            request.abort();
        });

        allRequests = [];
    };

    /**
     * @deprecated
     */
    piwikApi.abort = function () {
        this.abortAll();
    };

    /**
     * Perform a reading API request.
     * @param getParams
     */
    piwikApi.fetch = function (getParams) {

        getParams.module = getParams.module || 'API';
        getParams.format = 'JSON2';

        addParams(getParams, 'GET');

        var promise = send();

        reset();

        return promise;
    };

    piwikApi.post = function (getParams, _postParams_) {
        if (_postParams_) {
            postParams = _postParams_;
        }

        return this.fetch(getParams);
    };

    return piwikApi;
});
