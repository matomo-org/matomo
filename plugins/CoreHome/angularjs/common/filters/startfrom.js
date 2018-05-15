/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('startFrom', startFrom);

    function startFrom() {
        return function(input, start) {
            start = +start; //parse to int
            return input.slice(start);
        };
    }
})();