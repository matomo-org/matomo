/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    // can probably be shared
    angular.module('piwikApp').factory('coreAdminAPI', CoreAdminAPIFactory);

    CoreAdminAPIFactory.$inject = ['sitesManagerApiHelper'];

    function CoreAdminAPIFactory(api) {

        return {
            isPluginActivated: isPluginActivated()
        };

        function isPluginActivated() {
            return api.fetchApi('CoreAdminHome.isPluginActivated', api.valueAdaptor);
        }
    }
})();
