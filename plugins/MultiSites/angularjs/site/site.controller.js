/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('MultiSitesSiteController', MultiSitesSiteController);

    MultiSitesSiteController.$inject = ['$scope', 'piwik'];

    function MultiSitesSiteController($scope, piwik){

        $scope.period = piwik.period;
        $scope.date   = piwik.broadcast.getValueFromUrl('date');
        $scope.dashboardUrl = dashboardUrl;
        $scope.sparklineImage = sparklineImage;
        $scope.website.label  = piwik.helper.htmlDecode($scope.website.label);

        this.getWebsite = function () {
            return $scope.website;
        };

        function tokenParam() {
            var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
            return token_auth.length ? '&token_auth=' + token_auth : '';
        }

        function dashboardUrl(website){
            return 'index.php?module=CoreHome&action=index&date=' + $scope.date + '&period=' + $scope.period + '&idSite=' + website.idsite + tokenParam();
        }

        function sparklineImage(website){
            var metric = $scope.metric;

            switch ($scope.evolutionMetric) {
                case 'visits_evolution':
                    metric = 'nb_visits';
                    break;
                case 'pageviews_evolution':
                    metric = 'nb_pageviews';
                    break;
                case 'revenue_evolution':
                    metric = 'revenue';
                    break;
            }

            return 'index.php?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' + metric + '&columns=' + metric + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + tokenParam() + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
        }
    }
})();
