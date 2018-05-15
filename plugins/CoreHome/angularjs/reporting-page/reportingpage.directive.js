/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Shows a piwik reporting page.
 *
 * The content to be displayed is automatically loaded via API based on the current URL. The URL parameters
 * 'category' and 'subcategory' need to be present in the URL in order to see something in the reporting page.
 *
 * Example:
 * <div piwik-reporting-page></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikReportingPage', piwikReportingPage);

    piwikReportingPage.$inject = ['piwik'];

    function piwikReportingPage(piwik){

        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/CoreHome/angularjs/reporting-page/reportingpage.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ReportingPageController'
        };
    }
})();