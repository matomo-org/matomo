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
            loadingMessage: _pk_translate('MultiSites_LoadingWebsites'),
            updateWebsitesList: updateWebsitesList,
            getNumberOfFilteredSites: getNumberOfFilteredSites,
            getNumberOfPages: getNumberOfPages,
            getPaginationLowerBound: getPaginationLowerBound,
            getPaginationUpperBound: getPaginationUpperBound,
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

                if (site.hasOwnProperty('ratio') && site.ratio != 1) {
                    var percent = NumberFormatter.formatPercent(Math.round((site.ratio * 100)));
                    var metricName = null;
                    var previousTotal = 0;
                    var currentTotal = 0;
                    var evolution = 0;
                    var previousTotalAdjusted = 0;
                    if (model.sortColumn == 'nb_visits' || model.sortColumn == 'visits_evolution') {
                        previousTotal = NumberFormatter.formatNumber(site.previous_nb_visits);
                        currentTotal = NumberFormatter.formatNumber(site.nb_visits);
                        evolution = NumberFormatter.formatPercent(site.visits_evolution);
                        metricName = _pk_translate("General_ColumnNbVisits");
                        previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(site.previous_nb_visits * site.ratio));
                    }
                    if (model.sortColumn == 'pageviews_evolution') {
                        previousTotal = site.previous_Actions_nb_pageviews;
                        currentTotal = site.nb_pageviews;
                        evolution = NumberFormatter.formatPercent(site.pageviews_evolution);
                        metricName = _pk_translate("General_ColumnPageviews");
                        previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(site.previous_Actions_nb_pageviews * site.ratio));
                    }
                    if (model.sortColumn == 'revenue_evolution') {
                        previousTotal = NumberFormatter.formatCurrency(site.previous_Goal_revenue, site.currencySymbol);
                        currentTotal = NumberFormatter.formatCurrency(site.revenue, site.currencySymbol);
                        evolution = NumberFormatter.formatPercent(site.revenue_evolution);
                        metricName = _pk_translate("General_ColumnRevenue");
                        previousTotalAdjusted = NumberFormatter.formatCurrency(Math.round(site.previous_Goal_revenue * site.ratio), site.currencySymbol);
                    }

                    if (metricName) {
                        site.tooltip = _pk_translate("MultiSites_EvolutionComparisonIncomplete", [percent]) + "\n";
                        site.tooltip += _pk_translate("MultiSites_EvolutionComparisonProportional", [percent, previousTotalAdjusted, metricName, previousTotal]) + "\n";

                        switch (site.periodName) {
                            case 'day':
                                site.tooltip += _pk_translate("MultiSites_EvolutionComparisonDay", [currentTotal, metricName, previousTotalAdjusted, site.previousRange, evolution]);
                                break;
                            case 'week':
                                site.tooltip += _pk_translate("MultiSites_EvolutionComparisonWeek", [currentTotal, metricName, previousTotalAdjusted, site.previousRange, evolution]);
                                break;
                            case 'month':
                                site.tooltip += _pk_translate("MultiSites_EvolutionComparisonMonth", [currentTotal, metricName, previousTotalAdjusted, site.previousRange, evolution]);
                                break;
                            case 'year':
                                site.tooltip += _pk_translate("MultiSites_EvolutionComparisonYear", [currentTotal, metricName, previousTotalAdjusted, site.previousRange, evolution]);
                                break;
                        }
                    }

                }
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

        function getCurrentPagingOffset() {
            return Math.ceil(model.currentPage * model.pageSize);
        }

        function getPaginationLowerBound() {
            return getCurrentPagingOffset() + 1;
        }

        function getPaginationUpperBound() {
            var end = getCurrentPagingOffset() + parseInt(model.pageSize, 10);
            var max = getNumberOfFilteredSites();
            if (end > max) {
                end = max;
            }
            return parseInt(end, 10);
        }

        function sortBy(metric) {
            if (model.sortColumn == metric) {
                model.reverse = !model.reverse;
            }

            model.sortColumn = metric;
            fetchAllSites();
        }

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
                filter_offset: getCurrentPagingOffset(),
                showColumns: 'label,nb_visits,nb_pageviews,visits_evolution,visits_evolution_trend,pageviews_evolution,pageviews_evolution_trend,revenue_evolution,revenue_evolution_trend,nb_actions,revenue'
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
            }, onError).finally(function () {
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
