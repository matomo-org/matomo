/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').controller('MultiSitesDashboardController', function($scope, piwik, multisitesDashboardModel){

    $scope.model = multisitesDashboardModel;

    $scope.showSparklines = false;
    $scope.reverse = true;
    $scope.predicate = 'visits';
    $scope.evolutionSelector = 'visits_evolution';
    $scope.period = piwik.period;
    $scope.date = $scope.period == 'range' ? (piwik.startDateString + ',' + piwik.endDateString) : piwik.currentDateString;

    // 'General_EvolutionSummaryGeneric'|translate:'General_NVisits' | translate:totalVisits,prettyDate,'General_NVisits' | translate:pastTotalVisits,pastPeriodPretty,totalVisitsEvolution
    $scope.totalVisitsEvolutionTitle = 'TODO';
    $scope.parseInt = parseInt;

    // TODO
    $scope.hasSuperUserAccess = true;

    $scope.sparklineImage = function(website){
        var append = '';
        var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
        if (token_auth.length) {
            append = '&token_auth=' + token_auth;
        }

        return '?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' +$scope.evolutionSelector + '&columns=' + $scope.evolutionSelector + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
    }

    $scope.sortBy = function (predicate) {
        $scope.predicate = predicate;
        $scope.reverse   = !$scope.reverse;
    };
});
