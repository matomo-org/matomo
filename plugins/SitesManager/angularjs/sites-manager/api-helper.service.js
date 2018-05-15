/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    // can probably be renamed and shared
    angular.module('piwikApp').factory('sitesManagerApiHelper', SitesManagerAPIHelperFactory);

    SitesManagerAPIHelperFactory.$inject = ['piwikApi'];

    function SitesManagerAPIHelperFactory(piwikApi) {

        return {
            fetch: fetch,
            commaDelimitedFieldToArray: commaDelimitedFieldToArray,
            fetchApi: fetchApi,
            fetchAction: fetchAction,
            singleObjectAdaptor: singleObjectAdaptor,
            valueAdaptor: valueAdaptor,
            noop: noop
        };

        function fetch (endpoint, jsonResponseAdaptor, params) {

            return function (clientHandover, additionalParams) {

                params = angular.extend(params || {}, additionalParams || {});

                var requestDefinition = angular.extend(endpoint, params);

                var responseHandler = function (response) {

                    response = jsonResponseAdaptor(response);

                    clientHandover(response);
                };

                piwikApi.fetch(requestDefinition).then(responseHandler);
            };
        }

        function commaDelimitedFieldToArray (value) {

            if(!value)
                return [];

            return value.split(',');
        }

        function fetchApi(apiMethod, jsonResponseAdaptor, params) {

            return fetch({method: apiMethod}, jsonResponseAdaptor, params);
        }

        function fetchAction(module, action, jsonResponseAdaptor, params) {

            return fetch({module: module, action: action}, jsonResponseAdaptor, params);
        }

        function singleObjectAdaptor(response) {
            return response[0];
        }

        function valueAdaptor(response) {
            return response.value;
        }

        function noop(response) {
            return response;
        }
    }
})();
