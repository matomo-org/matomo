/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('sitesManagerAPI', SitesManagerAPIFactory);

    SitesManagerAPIFactory.$inject = ['sitesManagerApiHelper'];

    function SitesManagerAPIFactory(api) {

        return {
            getCurrencyList: getCurrencyList(),
            getTimezonesList: getTimezonesList(),
            isTimezoneSupportEnabled: isTimezoneSupportEnabled(),
            getGlobalSettings: getGlobalSettings(),
            getSitesIdWithAdminAccess: getSitesIdWithAdminAccess()
        };

        function getSitesIdWithAdminAccess () {
            return api.fetchApi('SitesManager.getSitesIdWithAdminAccess', api.noop, {
                filter_limit: '-1',
            });
        }
        function getCurrencyList () {
            return api.fetchApi('SitesManager.getCurrencyList', api.noop);
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

})();
