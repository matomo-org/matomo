/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    // can probably be shared
    angular.module('piwikApp').factory('coreAPI', CoreAPIFactory);

    CoreAPIFactory.$inject = ['sitesManagerApiHelper'];

    function CoreAPIFactory(api) {

        return {
            getIpFromHeader: getIpFromHeader(),
            isPluginActivated: isPluginActivated()
        };

        function getIpFromHeader() {
            return api.fetchApi('API.getIpFromHeader', api.valueAdaptor);
        }

        function isPluginActivated() {
            return api.fetchApi('API.isPluginActivated', api.valueAdaptor);
        }
    }

})();
