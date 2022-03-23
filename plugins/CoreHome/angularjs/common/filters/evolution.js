/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('evolution', evolutionFilter);

    function evolutionFilter() {
        return function(currentValue, pastValue) {
          return window.CoreHome.getFormattedEvolution(currentValue, pastValue);
        };
    }
})();
