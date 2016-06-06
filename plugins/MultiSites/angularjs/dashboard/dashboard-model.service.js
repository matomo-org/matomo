/**
 * Model for Multisites Dashboard aka All Websites Dashboard.
 */
(function () {
    angular.module('piwikApp').factory('multisitesDashboardModel', multisitesDashboardModel);

    multisitesDashboardModel.$inject = ['piwikApi', '$filter', '$timeout'];

    function multisitesDashboardModel(piwikApi, $filter, $timeout) {

        var refreshPromise = null;

        // those sites are going to be displayed
        var model = {
            sites        : [],
            isLoading    : false,
            pageSize     : 25,
            currentPage  : 0,
            totalVisits  : '?',
            totalPageviews : '?',
            totalActions : '?',
            totalRevenue : '?',
            searchTerm   : '',
            lastVisits   : '?',
            lastVisitsDate : '?',
            numberOfSites : 0,
            updateWebsitesList: updateWebsitesList,
            getNumberOfFilteredSites: getNumberOfFilteredSites,
            getNumberOfPages: getNumberOfPages,
            getCurrentPagingOffsetStart: getCurrentPagingOffsetStart,
            getCurrentPagingOffsetEnd: getCurrentPagingOffsetEnd,
            previousPage: previousPage,
            nextPage: nextPage,
            searchSite: searchSite,
            sortBy: sortBy,
            reverse: true,
            sortColumn: 'nb_visits',
            fetchAllSites: fetchAllSites,
            refreshInterval: 0
        };

        return model;

        function cancelRefereshInterval()
        {
            if (refreshPromise) {
                $timeout.cancel(refreshPromise);
                refreshPromise = null;
            };
        }

        function onError () {
            model.errorLoadingSites = true;
            model.sites = [];
        }

        function updateWebsitesList(report) {
            if (!report) {
                onError();
                return;
            }

            var allSites = report.sites;
            angular.forEach(allSites, function (site, index) {
                site.visits_evolution    = parseInt(site.visits_evolution, 10);
                site.pageviews_evolution = parseInt(site.pageviews_evolution, 10);
                site.revenue_evolution   = parseInt(site.revenue_evolution, 10);
            });

            model.totalVisits   = report.totals.nb_visits;
            model.totalPageviews  = report.totals.nb_pageviews;
            model.totalActions  = report.totals.nb_actions;
            model.totalRevenue  = report.totals.revenue;
            model.lastVisits    = report.totals.nb_visits_lastdate;
            model.sites = allSites;
            model.numberOfSites  = report.numSites;
            model.lastVisitsDate = report.lastDate;
        }

        function getNumberOfFilteredSites () {
            return model.numberOfSites;
        }

        function getNumberOfPages() {
            return Math.ceil(getNumberOfFilteredSites() / model.pageSize - 1);
        }

        function getCurrentPagingOffsetStart() {
            return Math.ceil(model.currentPage * model.pageSize);
        }

        function getCurrentPagingOffsetEnd() {
            var end = getCurrentPagingOffsetStart() + parseInt(model.pageSize, 10);
            var max = getNumberOfFilteredSites();
            if (end > max) {
                end = max;
            }
            return parseInt(end, 10);
        }

        function previousPage() {
            model.currentPage = model.currentPage - 1;
            fetchAllSites();
        }

        function sortBy(metric) {
            if (model.sortColumn == metric) {
                model.reverse = !model.reverse;
            }

            model.sortColumn = metric;
            fetchAllSites();
        };

        function previousPage() {
            model.currentPage = model.currentPage - 1;
            fetchAllSites();
        }

        function nextPage() {
            model.currentPage = model.currentPage + 1;
            fetchAllSites();
        }

        function searchSite (term) {
            model.searchTerm  = term;
            model.currentPage = 0;
            fetchAllSites();
        }

        function fetchAllSites() {

            if (model.isLoading) {
                piwikApi.abort();
                cancelRefereshInterval();
            }

            model.isLoading = true;
            model.errorLoadingSites = false;

            var params = {
                module: 'MultiSites',
                action: 'getAllWithGroups',
                hideMetricsDoc: '1',
                filter_sort_order: 'asc',
                filter_limit: model.pageSize,
                filter_offset: getCurrentPagingOffsetStart(),
                showColumns: 'label,nb_visits,nb_pageviews,visits_evolution,pageviews_evolution,revenue_evolution,nb_actions,revenue'
            };

            if (model.searchTerm) {
                params.pattern = model.searchTerm;
            }

            if (model.sortColumn) {
                params.filter_sort_column = model.sortColumn;
            }

            if (model.reverse) {
                params.filter_sort_order = 'desc';
            }

            return piwikApi.fetch(params).then(function (response) {
                updateWebsitesList(response);
            }, onError)['finally'](function () {
                model.isLoading = false;

                if (model.refreshInterval && model.refreshInterval > 0) {
                    cancelRefereshInterval();

                    refreshPromise = $timeout(function () {
                        refreshPromise = null;
                        fetchAllSites(model.refreshInterval);
                    }, model.refreshInterval * 1000);
                }
            });
        }
    }
})();
