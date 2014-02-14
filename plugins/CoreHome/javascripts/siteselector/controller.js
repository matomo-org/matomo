/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.controller('SiteSelectorController', function($scope, siteSelectorModel, piwik){

    $scope.model = siteSelectorModel;
    $scope.selectedSite = {id: '', name: ''};
    $scope.activeSiteId = piwik.idSite;

    $scope.model.loadInitialSites();

    $scope.switchSite = function (site) {
        if (!$scope.switchSiteOnSelect || $scope.activeSiteId == site.idsite) {
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

    $scope.getUrlAllSites  = function () {
        var newParameters = 'module=MultiSites&action=index'
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
    }
    $scope.getUrlForSiteId = function (idSite) {
        var idSiteParam   = 'idSite=' + idSite;
        var newParameters = 'segment=&' + idSiteParam;
        var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters)
            + '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    };
});
