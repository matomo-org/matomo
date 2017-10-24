/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-period-selector>
 */
(function () {
    angular.module('piwikApp').directive('piwikPeriodSelector', piwikPeriodSelector);

    piwikPeriodSelector.$inject = ['piwik'];

    function piwikPeriodSelector(piwik) {
        return {
            restrict: 'A',
            scope: {
                periods: '<'
            },
            templateUrl: 'plugins/CoreHome/angularjs/period-selector/period-selector.directive.html?cb=' + piwik.cacheBuster,
            controller: 'PeriodSelectorController',
            controllerAs: 'periodSelector',
            bindToController: true,
            link: function (scope, element) {
                scope.periodSelector.closePeriodSelector = closePeriodSelector;

                scope.$on('$locationChangeSuccess', scope.periodSelector.updateSelectedValuesFromHash);

                function closePeriodSelector() {
                    element.find('.periodSelector').removeClass('expanded');
                }
            }
        };
    }
})();