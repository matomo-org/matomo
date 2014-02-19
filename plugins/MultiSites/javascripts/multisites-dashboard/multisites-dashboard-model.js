
angular.module('piwikApp').factory('multisitesDashboardModel', function (piwikApi, $filter, $timeout) {

    var model       = {};
    model.sites     = [];
    model.allSites  = [];
    model.isLoading = false;
    model.pageSize  = 5;
    model.currentPage  = 0;
    model.totalVisits  = 0;
    model.totalActions = 0;
    model.prettyDate   = '';

    function createGroup(name){
        return {
            label: name,
            sites: [],
            nb_visits: 0,
            nb_pageviews: 0,
            revenue: 0,
            isGroup: true
        }
    }
    function copyGroup(group)
    {
        return {
            label: group.label,
            sites: [],
            nb_visits: group.nb_visits,
            nb_pageviews: group.nb_pageviews,
            revenue: group.revenue,
            isGroup: true
        }
    }

    model.updateWebsitesList = function (processedReport) {

        model.allSites     = processedReport.reportData;
        model.totalVisits  = processedReport.reportTotal.nb_visits;
        model.totalActions = processedReport.reportTotal.nb_actions;
        model.prettyDate   = processedReport.prettyDate;

        var sitesByGroup = [];
        var groups = {};
        angular.forEach(model.allSites, function (site, index) {
            site.idsite   = processedReport.reportMetadata[index].idsite;
            site.group    = processedReport.reportMetadata[index].group;
            site.main_url = processedReport.reportMetadata[index].main_url;

            if (site.group) {

                if (!groups[site.group]) {
                    var group = createGroup(site.group);

                    groups[site.group] = group;
                    sitesByGroup.push(group);
                }

                groups[site.group].sites.push(site);

            } else {
                sitesByGroup.push(site);
            }
        });

        angular.forEach(groups, function (group) {
            angular.forEach(group.sites, function (site) {
                var revenue = (site.revenue+'').match(/(\d+\.?\d*)/);
                group.nb_visits    += site.nb_visits;
                group.nb_pageviews += site.nb_pageviews;
                if (revenue.length) {
                    group.revenue += parseInt(revenue[0], 10);
                }
            });
        });

        if (!sitesByGroup || !sitesByGroup.length) {
            return;
        }

        model.allSites = sitesByGroup;
        model.sites    = sitesByGroup;
    };

    model.getNumberOfFilteredSites = function () {
        return model.sites.length;
    }

    model.getNumberOfFilteredSites = function () {
        return model.sites.length;
    }
    model.getNumberOfPages = function () {
        return model.sites.length / model.pageSize - 1;
    }

    model.getCurrentPagingOffsetStart = function() {
        return Math.ceil(model.currentPage * model.pageSize);
    }

    model.getCurrentPagingOffsetEnd = function() {
        var end = model.getCurrentPagingOffsetStart() + parseInt(model.pageSize, 10);
        if (end > model.sites.length) {
            end = model.sites.length
        }
        return parseInt(end, 10);
    }

    model.previousPage = function () {
        model.currentPage = model.currentPage - 1;
    };

    model.nextPage = function () {
        model.currentPage = model.currentPage + 1;
    };

    model.numberOfPages = function () {
        return Math.ceil(model.allSites.length / model.pageSize);
    };

    function nestedSearch(sites, term)
    {
        var filteredSites = [];

        for (var index in sites) {
            var site = sites[index];
            if (site.isGroup) {
                var matchingSites = nestedSearch(site.sites, term);
                if (matchingSites.length || (''+site.label).toLowerCase().indexOf(term) > -1) {
                    var clonedGroup   = copyGroup(site);
                    clonedGroup.sites = matchingSites;
                    filteredSites.push(clonedGroup);
                }
            } else if (!site.group && (''+site.label).toLowerCase().indexOf(term) > -1) {
                filteredSites.push(site);
            } else if (site.group && (''+site.label).toLowerCase().indexOf(term) > -1) {
                filteredSites.push(site);
            }
        }

        return filteredSites;
    }

    model.searchSite = function (term) {
        model.currentPage = 0;
        model.sites = nestedSearch(model.allSites, term);
    }

    model.fetchAllSites = function (refreshInterval) {

        if (model.isLoading) {
            piwikApi.abort();
        }

        model.isLoading = true;

        return piwikApi.fetch({
            method: 'API.getProcessedReport',
            apiModule: 'MultiSites',
            apiAction: 'getAll',
            hideMetricsDoc: '1',
            filter_limit: '-1',
            showColumns: 'label,nb_visits,nb_pageviews,visits_evolution,pageviews_evolution,revenue_evolution,nb_actions,revenue',
            enhanced: 1
        }).then(function (response) {
            model.updateWebsitesList(response);
        }).finally(function () {
            model.isLoading = false;

            if (refreshInterval && refreshInterval > 0) {
                $timeout(function () {
                    model.fetchAllSites(refreshInterval)
                }, refreshInterval * 1000);
            }
        });
    };

    return model;
});
