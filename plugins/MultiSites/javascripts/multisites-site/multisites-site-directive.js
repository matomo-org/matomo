/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').directive('piwikMultisitesSite', function($document, piwik, $filter){

    return {
        restrict: 'AC',
        replace: true,
        scope: {
            website: '=',
            evolutionSelector: '=',
            showSparklines: '=',
            dateSparkline: '=',
            displayRevenueColumn: '='
        },
        templateUrl: 'plugins/MultiSites/javascripts/multisites-site/multisites-site.html',
        controller: function ($scope) {

            $scope.period = piwik.period;
            $scope.date   = $scope.period == 'range' ? (piwik.startDateString + ',' + piwik.endDateString) : piwik.currentDateString;

            $scope.sparklineImage = function(website){
                var append = '';
                var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
                if (token_auth.length) {
                    append = '&token_auth=' + token_auth;
                }

                return '?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' +$scope.evolutionSelector + '&columns=' + $scope.evolutionSelector + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
            }
        }
    }
});