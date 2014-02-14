piwikApp.controller('SiteSelectorController', ['$scope', 'siteSelectorModel', 'piwik', function($scope, siteSelectorModel, piwik){

    $scope.model = siteSelectorModel;
    $scope.selectedSite = {id: '', name: ''};
    $scope.activeSiteId = piwik.idSite;

    $scope.model.loadInitialSites();

    $scope.switchSite = function (site) {
        if (!$scope.switchSiteOnSelect || piwik.idSite == site.idsite) {
            $scope.selectedSite.id   = site.idsite;
            $scope.selectedSite.name = site.name;
            return;
        }

        if (site.idsite == 'all') {
            piwik.broadcast.propagateNewPage('module=MultiSites&action=index');
        } else {
            piwik.broadcast.propagateNewPage('segment=&idSite=' + site.idsite, false);
        }
    };

    function getUrlForWebsiteId (idSite) {
        var idSiteParam   = 'idSite=' + idSite;
        var newParameters = 'segment=&' + idSiteParam;
        var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters)
            + '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    };
}]);

/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
