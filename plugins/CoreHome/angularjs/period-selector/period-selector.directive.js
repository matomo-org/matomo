/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-period-selector>
 */
(function () {
    angular.module('piwikApp').directive('piwikPeriodSelector', piwikPeriodSelector);

    piwikPeriodSelector.$inject = ['piwik', '$rootScope'];

    function piwikPeriodSelector(piwik, $rootScope) {
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

                $rootScope.$on('hidePeriodSelector', function () {
                    element.hide();
                });

                // some widgets might hide the period selector using the event above, so ensure it's shown again when switching the page
                $rootScope.$on('piwikPageChange', function () {
                    element.show();
                });

                function closePeriodSelector() {
                    element.find('.periodSelector').removeClass('expanded');
                }
            }
        };
    }
})();