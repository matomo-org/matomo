/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('MultiSitesDashboardController', MultiSitesDashboardController);

    MultiSitesDashboardController.$inject = ['$scope', 'piwik', 'multisitesDashboardModel'];

    function MultiSitesDashboardController($scope, piwik, multisitesDashboardModel){

        $scope.model = multisitesDashboardModel;

        $scope.reverse = true;
        $scope.predicate = 'nb_visits';
        $scope.evolutionSelector = 'visits_evolution';
        $scope.hasSuperUserAccess = piwik.hasSuperUserAccess;
        $scope.date = piwik.broadcast.getValueFromUrl('date');
        $scope.url  = piwik.piwik_url;
        $scope.period = piwik.period;

        $scope.sortBy = function (metric) {

            var reverse = $scope.reverse;
            if ($scope.predicate == metric) {
                reverse = !reverse;
            }

            $scope.predicate = metric;
            $scope.reverse   = reverse;
        };

        this.refresh = function (interval) {
            multisitesDashboardModel.fetchAllSites(interval);
        };
    }
})();
