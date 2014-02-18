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
    $scope.predicate = 'nb_visits';
    $scope.evolutionSelector = 'visits_evolution';

    // 'General_EvolutionSummaryGeneric'|translate:'General_NVisits' | translate:totalVisits,prettyDate,'General_NVisits' | translate:pastTotalVisits,pastPeriodPretty,totalVisitsEvolution
    $scope.totalVisitsEvolutionTitle = 'TODO';
    $scope.parseInt = parseInt;

    // TODO
    $scope.hasSuperUserAccess = true;

    $scope.sortBy = function (predicate) {
        $scope.predicate = predicate;
        $scope.reverse   = !$scope.reverse;
    };
});
