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
            getIpFromHeader: getIpFromHeader()
        };

        function getIpFromHeader() {
            return api.fetchApi('API.getIpFromHeader', api.valueAdaptor);
        }
    }

})();
