/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').directive('piwikMultisitesDashboard', function($document, piwik, $filter){

    return {
        restrict: 'A',
        scope: {
            displayRevenueColumn: '=',
            showSparklines: '=',
            dateSparkline: '@'
        },
        templateUrl: 'plugins/MultiSites/javascripts/multisites-dashboard/multisites-dashboard.html',
        controller: 'MultiSitesDashboardController',
        link: function (scope, element, attrs) {

            if (attrs.pageSize) {
                scope.model.pageSize = attrs.pageSize;
            }

            scope.model.fetchAllSites();
        }
    }
});