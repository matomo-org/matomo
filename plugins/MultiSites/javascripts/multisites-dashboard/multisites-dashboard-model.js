
angular.module('piwikApp').factory('multisitesDashboardModel', function (piwikApi, $filter) {

    var model       = {};
    model.sites     = [];
    model.allSites  = [];
    model.isLoading = false;
    model.pageSize  = 5;
    model.currentPage  = 0;
    model.totalVisits  = 0;
    model.totalActions = 0;
    model.prettyDate   = '';

    model.updateWebsitesList = function (processedReport) {

        model.allSites     = processedReport.reportData;
        model.totalVisits  = processedReport.reportTotal.nb_visits;
        model.totalActions = processedReport.reportTotal.nb_actions;
        model.prettyDate   = processedReport.prettyDate;

        if (!model.allSites || !model.allSites.length) {
            return;
        }

        model.sites    = model.allSites;
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
        if (end > model.allSites.length) {
            end = model.allSites.length
        }
        return parseInt(end, 10);
    }

    model.previousPage = function () {
        model.currentPage = model.currentPage - 1
    };

    model.nextPage = function () {
        model.currentPage = model.currentPage + 1
    };

    model.numberOfPages = function () {
        return Math.ceil(model.allSites.length / model.pageSize);
    };

    model.searchSite = function (term) {
        model.currentPage = 0;
        model.sites = $filter('filter')(model.allSites, term);
    }

    model.fetchAllSites = function () {

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
        });
    };

    return model;
});
