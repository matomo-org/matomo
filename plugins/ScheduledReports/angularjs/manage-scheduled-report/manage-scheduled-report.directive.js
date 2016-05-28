/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var getReportParametersFunctions = Object();
var updateReportParametersFunctions = Object();
var resetReportParametersFunctions = Object();

/**
 * Usage:
 * <div piwik-manage-scheduled-report>
 */
(function () {
    angular.module('piwikApp').directive('piwikManageScheduledReport', piwikManageScheduledReport);

    piwikManageScheduledReport.$inject = ['piwik'];

    function piwikManageScheduledReport(piwik){

        return {
            restrict: 'A',
            priority: 10,
            controller: 'ManageScheduledReportController',
            controllerAs: 'manageScheduledReport'
        };
    }
})();