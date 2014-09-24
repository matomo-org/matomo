/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('sitesManagerAPI', SitesManagerAPIFactory);

    // can probably be shared
    angular.module('piwikApp').factory('coreAPI', CoreAPIFactory);

    // can probably be shared
    angular.module('piwikApp').factory('coreAdminAPI', CoreAdminAPIFactory);

    // can probably be renamed and shared
    angular.module('piwikApp').factory('sitesManagerApiHelper', SitesManagerAPIHelperFactory);

    function SitesManagerAPIFactory(sitesManagerApiHelper) {

        var api = sitesManagerApiHelper;

        return {
            getCurrencyList: getCurrentcyList,
            getSitesWithAdminAccess: getSitesWithAdminAccess,
            getTimezonesList: getTimezonesList,
            isTimezoneSupportEnabled: isTimezoneSupportEnabled,
            getGlobalSettings: getGlobalSettings
        };

        function getCurrencyList () {
            return api.fetchApi('SitesManager.getCurrencyList', api.noop);
        }

        function getSitesWithAdminAccess () {
            return api.fetchApi('SitesManager.getSitesWithAdminAccess', api.noop, {fetchAliasUrls: true});
        }

        function getTimezonesList () {
            return api.fetchApi('SitesManager.getTimezonesList', api.noop);
        }

        function isTimezoneSupportEnabled () {
            return api.fetchApi('SitesManager.isTimezoneSupportEnabled', api.valueAdaptor);
        }

        function getGlobalSettings () {
            return api.fetchAction('SitesManager', 'getGlobalSettings', api.noop);
        }
    }

    function CoreAPIFactory(sitesManagerApiHelper) {

        var api = sitesManagerApiHelper;

        return {
            getIpFromHeader: getIpFromHeader
        };

        function getIpFromHeader() {
            return api.fetchApi('API.getIpFromHeader', api.valueAdaptor);
        }
    }

    function CoreAdminAPIFactory(sitesManagerApiHelper) {

        var api = sitesManagerApiHelper;

        return {
            isPluginActivated: isPluginActivated
        };

        function isPluginActivated() {
            return api.fetchApi('CoreAdminHome.isPluginActivated', api.valueAdaptor);
        }
    }

    function SitesManagerAPIHelperFactory(piwikApi) {

        return {

            fetch: function (endpoint, jsonResponseAdaptor, params) {

                return function (clientHandover, additionalParams) {

                    params = angular.extend(params || {}, additionalParams || {});

                    var requestDefinition = angular.extend(endpoint, params);

                    var responseHandler = function (response) {

                        response = jsonResponseAdaptor(response);

                        clientHandover(response);
                    };

                    piwikApi.fetch(requestDefinition).then(responseHandler);
                }
            },

            commaDelimitedFieldToArray: function(value) {

                if(value == null || value == '')
                    return [];

                return value.split(',');
            },

            fetchApi: function (apiMethod, jsonResponseAdaptor, params) {

                return this.fetch({method: apiMethod}, jsonResponseAdaptor, params);
            },

            fetchAction: function (module, action, jsonResponseAdaptor, params) {

                return this.fetch({module: module, action: action}, jsonResponseAdaptor, params);
            },

            singleObjectAdaptor: function (response) {
                return response[0];
            },

            valueAdaptor: function (response) {
                return response.value;
            },

            noop: function (response) {
                return response;
            }
        };
    }
})();
