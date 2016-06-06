/**
 * Model for Sites Manager. Fetches only sites one has at least Admin permission.
 */
(function () {
    angular.module('piwikApp').factory('sitesManagerAdminSitesModel', sitesManagerAdminSitesModel);

    sitesManagerAdminSitesModel.$inject = ['piwikApi'];

    function sitesManagerAdminSitesModel(piwikApi)
    {
        var model = {
            sites        : [],
            searchTerm   : '',
            isLoading    : false,
            pageSize     : 10,
            currentPage  : 0,
            offsetStart  : 0,
            offsetEnd    : 10,
            hasPrev      : false,
            hasNext      : false,
            previousPage: previousPage,
            nextPage: nextPage,
            searchSite: searchSite,
            fetchLimitedSitesWithAdminAccess: fetchLimitedSitesWithAdminAccess
        };

        return model;

        function onError ()
        {
            setSites([]);
        }

        function setSites(sites)
        {
            model.sites = sites;

            var numSites      = sites.length;
            model.offsetStart = model.currentPage * model.pageSize;
            model.offsetEnd   = model.offsetStart + numSites;
            model.hasPrev     = model.currentPage >= 1;
            model.hasNext     = numSites === model.pageSize;
        }

        function setCurrentPage(page)
        {
            if (page < 0) {
                page = 0;
            }

            model.currentPage = page;
        }

        function previousPage()
        {
            setCurrentPage(model.currentPage - 1);
            fetchLimitedSitesWithAdminAccess();
        }

        function nextPage()
        {
            setCurrentPage(model.currentPage + 1);
            fetchLimitedSitesWithAdminAccess();
        }

        function searchSite (term)
        {
            model.searchTerm = term;
            setCurrentPage(0);
            fetchLimitedSitesWithAdminAccess();
        }

        function fetchLimitedSitesWithAdminAccess(callback)
        {
            if (model.isLoading) {
                piwikApi.abort();
            }

            model.isLoading = true;

            var limit  = model.pageSize;
            var offset = model.currentPage * model.pageSize;

            var params = {
                method: 'SitesManager.getSitesWithAdminAccess',
                fetchAliasUrls: true,
                limit: limit + offset, // this is applied in SitesManager.getSitesWithAdminAccess API
                filter_offset: offset, // filter_offset and filter_limit is applied in response builder
                filter_limit: limit
            };

            if (model.searchTerm) {
                params.pattern = model.searchTerm;
            }

            return piwikApi.fetch(params).then(function (sites) {

                if (!sites) {
                    onError();
                    return;
                }

                setSites(sites);

            }, onError)['finally'](function () {
                if (callback) {
                    callback();
                }

                model.isLoading = false;
            });
        }

    }
})();
