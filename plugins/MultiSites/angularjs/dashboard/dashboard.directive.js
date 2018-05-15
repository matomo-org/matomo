/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Renders the multisites dashboard
 * Example usage:
 *
 * <div piwik-multisites-dashboard
 *      display-revenue-column="true"
 *      show-sparklines="true"
 *      date-sparkline="true"
 *      page-size="50"
 *      auto-refresh-today-report="500" // or 0 to disable
 * ></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikMultisitesDashboard', piwikMultisitesDashboard);

    piwikMultisitesDashboard.$inject = ['piwik'];

    function piwikMultisitesDashboard(piwik){

        return {
            restrict: 'A',
            scope: {
                displayRevenueColumn: '@',
                showSparklines: '@',
                dateSparkline: '@'
            },
            templateUrl: 'plugins/MultiSites/angularjs/dashboard/dashboard.directive.html?cb=' + piwik.cacheBuster,
            controller: 'MultiSitesDashboardController',
            link: function (scope, element, attrs, controller) {

                if (attrs.pageSize) {
                    scope.model.pageSize = attrs.pageSize;
                }

                controller.refresh(attrs.autoRefreshTodayReport);
            }
        };
    }
})();