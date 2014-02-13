/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.controller('SiteSelectorCtrl', ['$scope', 'piwikApi', function($scope, piwikApi){
    var filterLimit = 10;

    $scope.templateUrl = 'plugins/CoreHome/javascripts/siteselector//siteSelector.tpl.html';
    $scope.allWebsitesLinkLocation = 'bottom';
    $scope.sites = [];
    $scope.showSelectedSite = false;
    $scope.show_autocompleter = true;
    $scope.siteSelectorId = '';
    $scope.switchSiteOnSelect = false;
    $scope.hasMultipleWebsites = false;
    $scope.isLoading = false;
    $scope.showAllSitesItem = true;
    $scope.selectedSiteId = 0;
    $scope.searchTerm = '';
    $scope.max_sitename_width = 130; // can be removed?

    $scope.switchSite = function (site) {
        if (!$scope.switchSiteOnSelect || piwik.idSite == site.idsite) {
            $scope.selectedSiteId = site.idsite;
            $scope.siteName = site.name;
            return;
        }

        if (site.idsite == 'all' && !$scope.showAllSitesItem) {
            broadcast.propagateNewPage('module=MultiSites&action=index');
        } else {
            broadcast.propagateNewPage($scope.getUrlForWebsiteId(site.idsite), false);
        }
    };

    $scope.getUrlForWebsiteId = function (idSite) {
        var idSiteParam   = 'idSite=' + idSite;
        var newParameters = 'segment=&' + idSiteParam;
        var hash = broadcast.isHashExists() ? broadcast.getHashFromUrl() : "";
        return piwikHelper.getCurrentQueryStringWithParametersModified(newParameters)
            + '#' + piwikHelper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    };

    $scope.updateWebsitesList = function (websites) {
        angular.forEach(websites, function (website) {
            website.name = piwikHelper.htmlDecode(website.name);
        });

        $scope.sites = websites;

        if (!$scope.siteName) {
            $scope.siteName = websites[0].name;
        }

        $scope.hasMultipleWebsites = websites.length > 1;
    };

    $scope.searchSite = function (term) {
        if (!term) {
            $scope.loadInitialSites();
            return;
        }
        $scope.isLoading = true;
        piwikApi.fetch({
            method: 'SitesManager.getPatternMatchSites',
            filter_limit: filterLimit,
            pattern: term
        }).then(function (response) {
            $scope.updateWebsitesList(response);
        }).finally(function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadInitialSites = function () {
        $scope.isLoading = true;
        piwikApi.fetch({
            method: 'SitesManager.getSitesWithAtLeastViewAccess',
            filter_limit: filterLimit,
            showColumns: 'name,idsite'
        }).then(function (response) {
            $scope.updateWebsitesList(response);
        }).finally(function () {
            $scope.isLoading = false;
        });
    }

}]);