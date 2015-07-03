/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Shows a general loading message while [loading] is set to true.
 *
 * @param {Boolean} loading  If true, the activity indicator is shown, otherwise the indicator is hidden.
 *
 * Example:
 * <div piwik-activity-indicator loading="true|false"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikActivityIndicator', piwikActivityIndicator);

    piwikActivityIndicator.$inject = ['piwik'];

    function piwikActivityIndicator(piwik){
        return {
            restrict: 'A',
            transclude: true,
            scope: {
                loading: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/activity-indicator/activityindicator.html?cb=' + piwik.cacheBuster
        };
    }
})();