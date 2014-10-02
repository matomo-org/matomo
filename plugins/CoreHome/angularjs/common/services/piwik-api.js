/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('piwikApi', piwikApiService);

    piwikApiService.$inject = ['$http', '$q', '$rootScope', 'piwik', '$window'];

    function piwikApiService ($http, $q, $rootScope, piwik, $window) {

        var url = 'index.php';
        var format = 'json';
        var getParams  = {};
        var postParams = {};
        var allRequests = [];

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

        function isErrorResponse(response) {
            return response && response.result == 'error';
        }

        function createResponseErrorNotification(response, options) {
            if (response.message
                && options.createErrorNotification
            ) {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(response.message, {
                    context: 'error',
                    type: 'toast',
                    id: 'ajaxHelper',
                    placeat: options.placeat
                });
                notification.scrollToNotification();
            }
        }

        /**
         * Send the request
         * @return $promise
         */
        function send (options) {
            if (!options) {
                options = {};
            }

            if (options.createErrorNotification === undefined) {
                options.createErrorNotification = true;
            }

            var deferred = $q.defer(),
                requestPromise = deferred.promise;

            var onError = function (message) {
                deferred.reject(message);
            };

            var onSuccess = function (response) {
                if (isErrorResponse(response)) {
                    onError(response.message || null);

                    createResponseErrorNotification(response, options);
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
        function getPostParams (params) {
            params.token_auth = piwik.token_auth;
            return params;
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

        function abortAll() {
            reset();

            allRequests.forEach(function (request) {
                request.abort();
            });

            allRequests = [];
        }

        function abort () {
            abortAll();
        }

        /**
         * Perform a reading API request.
         * @param getParams
         */
        function fetch (getParams, options) {

            getParams.module = getParams.module || 'API';
            getParams.format = 'JSON2';

            addParams(getParams, 'GET');

            var promise = send(options);

            reset();

            return promise;
        }

        function post(getParams, _postParams_, options) {
            if (_postParams_) {
                postParams = _postParams_;
            }

            return fetch(getParams, options);
        }

        /**
         * Convenience method that will perform a bulk request using Piwik's API.getBulkRequest method.
         * Bulk requests allow you to execute multiple Piwik requests with one HTTP request.
         *
         * @param {object[]} requests
         * @param {object} options
         * @return {HttpPromise} a promise that is resolved when the request finishes. The argument passed
         *                       to the .then(...) callback will be an array with one element per request
         *                       made.
         */
        function bulkFetch(requests, options) {
            var bulkApiRequestParams = {
                urls: requests.map(function (requestObj) { return '?' + $.param(requestObj); })
            };

            var deferred = $q.defer(),
                requestPromise = post({method: "API.getBulkRequest"}, bulkApiRequestParams, options).then(function (response) {
                    if (!(response instanceof Array)) {
                        response = [response];
                    }

                    // check for errors
                    for (var i = 0; i != response.length; ++i) {
                        var specificResponse = response[i];

                        if (isErrorResponse(specificResponse)) {
                            deferred.reject(specificResponse.message || null);

                            createResponseErrorNotification(specificResponse, options || {});

                            return;
                        }
                    }

                    deferred.resolve(response);
                }).catch(function () {
                    deferred.reject.apply(deferred, arguments);
                });

            return deferred.promise;
        }

        return {
            bulkFetch: bulkFetch,
            post: post,
            fetch: fetch,
            /**
             * @deprecated
             */
            abort: abort,
            abortAll: abortAll
        };
    }
})();