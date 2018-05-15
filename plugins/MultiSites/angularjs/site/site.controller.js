/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('MultiSitesSiteController', MultiSitesSiteController);

    MultiSitesSiteController.$inject = ['$scope', 'piwik'];

    function MultiSitesSiteController($scope, piwik){

        $scope.period = piwik.period;
        $scope.date   = piwik.broadcast.getValueFromUrl('date');
        $scope.sparklineImage = sparklineImage;
        $scope.website.label  = piwik.helper.htmlDecode($scope.website.label);

        this.getWebsite = function () {
            return $scope.website;
        };

        function sparklineImage(website){
            var append = '';
            var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
            if (token_auth.length) {
                append = '&token_auth=' + token_auth;
            }

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

            return piwik.piwik_url + '?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' + metric + '&columns=' + metric + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
        }
    }
})();
