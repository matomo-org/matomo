/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('escape', escape);

    function escape() {

        return function(value) {
            return piwikHelper.escape(piwikHelper.htmlEntities(value));
        };
    }
})();