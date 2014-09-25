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

        this.getWebsite = function () {
            return $scope.website;
        };

        function sparklineImage(website){
            var append = '';
            var token_auth = piwik.broadcast.getValueFromUrl('token_auth');
            if (token_auth.length) {
                append = '&token_auth=' + token_auth;
            }

            return piwik.piwik_url + '?module=MultiSites&action=getEvolutionGraph&period=' + $scope.period + '&date=' + $scope.dateSparkline + '&evolutionBy=' +$scope.metric + '&columns=' + $scope.metric + '&idSite=' + website.idsite + '&idsite=' + website.idsite + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
        }
    }
})();
