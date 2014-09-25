/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('siteSelectorModel', siteSelectorModel);

    siteSelectorModel.$inject = ['piwikApi', '$filter', 'piwik'];

    function siteSelectorModel(piwikApi, $filter, piwik) {

        var initialSites = null;

        var model = {
            sites : [],
            hasMultipleWebsites : false,
            isLoading : false,
            firstSiteName : '',
            updateWebsitesList: updateWebsitesList,
            searchSite: searchSite,
            loadSite: loadSite,
            loadInitialSites: loadInitialSites
        };

        return model;

        function updateWebsitesList(sites) {

            if (!sites || !sites.length) {
                model.sites = [];
                return [];
            }

            angular.forEach(sites, function (site) {
                if (site.group) site.name = '[' + site.group + '] ' + site.name;
            });

            model.sites = $filter('orderBy')(sites, '+name');

            if (!model.firstSiteName) {
                model.firstSiteName = model.sites[0].name;
            }

            model.hasMultipleWebsites = model.hasMultipleWebsites || sites.length > 1;

            return model.sites;
        }

        function searchSite(term) {

            if (!term) {
                loadInitialSites();
                return;
            }

            if (model.isLoading) {
                model.currentRequest.abort();
            }

            model.isLoading = true;

            model.currentRequest = piwikApi.fetch({
                method: 'SitesManager.getPatternMatchSites',
                pattern: term
            }).then(function (response) {
                return updateWebsitesList(response);
            })['finally'](function () {    // .finally() is not IE8 compatible see https://github.com/angular/angular.js/commit/f078762d48d0d5d9796dcdf2cb0241198677582c
                model.isLoading = false;
                model.currentRequest = null;
            });

            return model.currentRequest;
        }

        function loadSite(idsite) {
            if (idsite == 'all') {
                piwik.broadcast.propagateNewPage('module=MultiSites&action=index');
            } else {
                piwik.broadcast.propagateNewPage('segment=&idSite=' + idsite, false);
            }
        }

        function loadInitialSites() {
            if (initialSites) {
                model.sites = initialSites;
                return;
            }

            searchSite('%').then(function (websites) {
                initialSites = websites;
            });
        }
    }
})();