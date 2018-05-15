/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Shows the Piwik reporting menu.
 *
 * It automatically calls the API to fetch all data.
 *
 * Example:
 * <div piwik-reporting-menu></div>
 */

(function () {
    angular.module('piwikApp').directive('piwikReportingMenu', piwikReportingMenu);

    piwikReportingMenu.$inject = ['piwik'];

    function piwikReportingMenu(piwik){

        return {
            restrict: 'A',
            replace: true,
            scope: {},
            templateUrl: 'plugins/CoreHome/angularjs/reporting-menu/reportingmenu.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ReportingMenuController'
        };
    }
})();