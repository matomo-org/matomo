/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').controller('SiteSelectorController', function($scope, siteSelectorModel, piwik, AUTOCOMPLETE_MIN_SITES){

    $scope.model = siteSelectorModel;

    $scope.autocompleteMinSites = AUTOCOMPLETE_MIN_SITES;
    $scope.selectedSite = {id: '', name: ''};
    $scope.activeSiteId = piwik.idSite;

    $scope.switchSite = function (site) {
        $scope.selectedSite.id  = site.idsite;

        if (site.name === $scope.allSitesText) {
            $scope.selectedSite.name = $scope.allSitesText;
        } else {
            $scope.selectedSite.name = site.name.replace(/[\u0000-\u2666]/g, function(c) {
                return '&#'+c.charCodeAt(0)+';';
            });
        }

        if (!$scope.switchSiteOnSelect || $scope.activeSiteId == site.idsite) {
            return;
        }

        $scope.model.loadSite(site.idsite);
    };

    $scope.getUrlAllSites = function () {
        var newParameters = 'module=MultiSites&action=index';
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
    };
    $scope.getUrlForSiteId = function (idSite) {
        var idSiteParam   = 'idSite=' + idSite;
        var newParameters = 'segment=&' + idSiteParam;
        var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
            '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    };

    siteSelectorModel.loadInitialSites();
});
