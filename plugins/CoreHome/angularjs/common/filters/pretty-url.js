/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('prettyUrl', prettyUrl);

    function prettyUrl() {
        return function(input) {
            return input.trim().replace('http://', '');
        };
    }

})();