/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Renders a single website row, for instance to be used within the MultiSites Dashboard.
 *
 * Usage:
 * <div piwik-multisites-site>
 *     website="{label: 'Name', main_url: 'http://...', idsite: '...'}"
 *     evolution-metric="visits_evolution"
 *     show-sparklines="true"
 *     date-sparkline="2014-01-01,2014-02-02"
 *     display-revenue-column="true"
 *     </div>
 */
angular.module('piwikApp').directive('piwikMultisitesSite', function($document, piwik, $filter){

    return {
        restrict: 'AC',
        replace: true,
        scope: {
            website: '=',
            evolutionMetric: '=',
            showSparklines: '=',
            dateSparkline: '=',
            displayRevenueColumn: '=',
            metric: '='
        },
        templateUrl: 'plugins/MultiSites/angularjs/site/site.html?cb=' + piwik.cacheBuster,
        controller: function ($scope) {

            $scope.period   = piwik.period;
            $scope.date     = piwik.broadcast.getValueFromUrl('date');

            this.getWebsite = function () {
                return $scope.website;
            };

            $scope.sparklineImage = function(website){
                var append = '';
                var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
                if (token_auth.length) {
                    append = '&token_auth=' + token_auth;
                }

                return piwik.piwik_url + '?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' +$scope.metric + '&columns=' + $scope.metric + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
            };
        }
    };
});