/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').factory('sitesManagerAPI', function SitesManagerAPIFactory(sitesManagerApiHelper) {

    var api = sitesManagerApiHelper;

    return {
        getCurrencyList: api.fetchApi('SitesManager.getCurrencyList', api.singleObjectAdaptor),
        getSitesWithAdminAccess: api.fetchApi('SitesManager.getSitesWithAdminAccess', api.noop, {fetchAliasUrls: true}),
        getTimezonesList: api.fetchApi('SitesManager.getTimezonesList', api.noop),
        isTimezoneSupportEnabled: api.fetchApi('SitesManager.isTimezoneSupportEnabled', api.valueAdaptor),
        getGlobalSettings: api.fetchAction('SitesManager', 'getGlobalSettings', api.singleObjectAdaptor)
    };
});

// can probably be shared
angular.module('piwikApp').factory('coreAPI', function CoreAPIFactory(sitesManagerApiHelper) {

    var api = sitesManagerApiHelper;

    return {
        getIpFromHeader: api.fetchApi('API.getIpFromHeader', api.valueAdaptor)
    };
});

// can probably be shared
angular.module('piwikApp').factory('coreAdminAPI', function CoreAdminAPIFactory(sitesManagerApiHelper) {

    var api = sitesManagerApiHelper;

    return {
        isPluginActivated: api.fetchApi('CoreAdminHome.isPluginActivated', api.valueAdaptor)
    };
});

// can probably be renamed and shared
angular.module('piwikApp').factory('sitesManagerApiHelper', function SitesManagerAPIHelperFactory(piwikApi) {

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
});
