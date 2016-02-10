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
        var limitPromise = null;

        var model = {
            sites : [],
            hasMultipleWebsites : false,
            isLoading : false,
            firstSiteName : '',
            onlySitesWithAdminAccess: false,
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
                if (!site.name) {
                    return;
                }
                // Escape site names, see https://github.com/piwik/piwik/issues/7531
                site.name = site.name.replace(/[\u0000-\u2666]/g, function(c) {
                    return '&#'+c.charCodeAt(0)+';';
                });
            });

            model.sites = sortSites(sites);

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
                if (model.currentRequest) {
                    model.currentRequest.abort();
                } else if (limitPromise) {
                    limitPromise.abort();
                    limitPromise = null;
                }
            }

            model.isLoading = true;

            if (!limitPromise) {
                limitPromise = piwikApi.fetch({method: 'SitesManager.getNumWebsitesToDisplayPerPage'});
            }

            return limitPromise.then(function (response) {
                var limit = response.value;

                var methodToCall = 'SitesManager.getPatternMatchSites';
                if (model.onlySitesWithAdminAccess) {
                    methodToCall = 'SitesManager.getSitesWithAdminAccess';
                }

                model.currentRequest = piwikApi.fetch({
                    method: methodToCall,
                    limit: limit,
                    pattern: term
                });

                return model.currentRequest;
            }).then(function (response) {
                if (angular.isDefined(response)) {
                    return updateWebsitesList(response);
                }
            })['finally'](function () {    // .finally() is not IE8 compatible see https://github.com/angular/angular.js/commit/f078762d48d0d5d9796dcdf2cb0241198677582c
                model.isLoading = false;
                model.currentRequest = null;
            });
        }

        function loadSite(idsite) {
            if (idsite == 'all') {
                piwik.broadcast.propagateNewPage('module=MultiSites&action=index');
            } else {
                piwik.broadcast.propagateNewPage('segment=&idSite=' + idsite, false);
            }
        }

        function sortSites(websites)
        {
            return $filter('orderBy')(websites, '+name');
        }

        function loadInitialSites() {
            if (initialSites) {
                model.sites = initialSites;
                return;
            }

            searchSite('%').then(function () {
                initialSites = model.sites
            });
        }
    }
})();