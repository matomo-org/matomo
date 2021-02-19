/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
            loadInitialSites: loadInitialSites,
            hasMultipleSites: hasMultipleSites
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
            }).finally(function () {
                model.isLoading = false;
                model.currentRequest = null;
            });
        }

        function loadSite(idsite) {
            if (idsite == 'all') {
                document.location.href = piwikHelper.getCurrentQueryStringWithParametersModified(piwikHelper.getQueryStringFromParameters({
                    module: 'MultiSites',
                    action: 'index',
                    date: piwik.currentDateString,
                    period: piwik.period
                }));
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
                initialSites = model.sites;
                model.isInitialized = true
            });
        }

        function hasMultipleSites() {
            return initialSites && initialSites.length > 1;
        }
    }
})();