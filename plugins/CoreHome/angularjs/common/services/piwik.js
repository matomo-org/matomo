/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').service('piwik', piwikService);

    function piwikService() {

        piwik.helper    = piwikHelper;
        piwik.broadcast = broadcast;
        return piwik;
    }
})();