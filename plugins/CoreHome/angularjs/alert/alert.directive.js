/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-alert>
 */
(function () {
    angular.module('piwikApp').directive('piwikAlert', piwikAlert);

    piwikAlert.$inject = ['piwik'];

    function piwikAlert(piwik){

        return {
            restrict: 'A',
            transclude: true,
            scope: {severity: '@piwikAlert'},
            templateUrl: 'plugins/CoreHome/angularjs/alert/alert.directive.html?cb=' + piwik.cacheBuster
        };
    }
})();