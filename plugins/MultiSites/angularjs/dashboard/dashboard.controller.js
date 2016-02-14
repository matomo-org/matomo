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

        $scope.evolutionSelector = 'visits_evolution';
        $scope.hasSuperUserAccess = piwik.hasSuperUserAccess;
        $scope.date = piwik.broadcast.getValueFromUrl('date');
        $scope.idSite = piwik.broadcast.getValueFromUrl('idSite');
        $scope.url  = piwik.piwik_url;
        $scope.period = piwik.period;
        $scope.arePiwikProAdsEnabled = piwik.config && piwik.config.are_ads_enabled;

        this.refresh = function (interval) {
            multisitesDashboardModel.refreshInterval = interval;
            multisitesDashboardModel.fetchAllSites();
        };
    }
})();
