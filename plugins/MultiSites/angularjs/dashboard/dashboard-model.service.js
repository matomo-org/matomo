/**
 * Model for Multisites Dashboard aka All Websites Dashboard.
 */
(function () {
    angular.module('piwikApp').factory('multisitesDashboardModel', multisitesDashboardModel);

    multisitesDashboardModel.$inject = ['piwikApi', '$filter', '$timeout'];

    function multisitesDashboardModel(piwikApi, $filter, $timeout) {
        /**
         *
         * this is the list of all available sites. For performance reason this list is different to model.sites. ngRepeat
         * won't operate on the whole list this way. The allSites array contains websites and groups in the following
         * structure
         *
         * - website1
         * - website2
         * - website3.sites = [ // this is a group
         *    - website4
         *    - website5
         * ]
         * - website6
         *
         * This structure allows us to display the sites within a group directly under the group without big logic and also
         * allows us to calculate the summary for each group easily
         */
        var allSitesByGroup = [];

        // those sites are going to be displayed
        var model = {
            sites        : [],
            isLoading    : false,
            pageSize     : 5,
            currentPage  : 0,
            totalVisits  : '?',
            totalActions : '?',
            totalRevenue : '?',
            searchTerm   : '',
            lastVisits   : '?',
            lastVisitsDate : '?',
            updateWebsitesList: updateWebsitesList,
            getNumberOfFilteredSites: getNumberOfFilteredSites,
            getNumberOfPages: getNumberOfPages,
            getCurrentPagingOffsetStart: getCurrentPagingOffsetStart,
            getCurrentPagingOffsetEnd: getCurrentPagingOffsetEnd,
            previousPage: previousPage,
            nextPage: nextPage,
            searchSite: searchSite,
            fetchAllSites: fetchAllSites
        };

        fetchPreviousSummary();

        return model;

        // create a new group object which has similar structure than a website
        function createGroup(name){
            return {
                label: name,
                sites: [],
                nb_visits: 0,
                nb_pageviews: 0,
                revenue: 0,
                isGroup: true
            };
        }

        // create a new group with empty site to make sure we do not change the original group in $allSites
        function copyGroup(group)
        {
            return {
                label: group.label,
                sites: [],
                nb_visits: group.nb_visits,
                nb_pageviews: group.nb_pageviews,
                revenue: group.revenue,
                isGroup: true
            };
        }

        function onError () {
            model.errorLoadingSites = true;
            model.sites     = [];
            allSitesByGroup = [];
        }

        function calculateMetricsForEachGroup(groups)
        {
            angular.forEach(groups, function (group) {
                angular.forEach(group.sites, function (site) {
                    var revenue = 0;
                    if (site.revenue) {
                        revenue = (site.revenue+'').match(/(\d+\.?\d*)/); // convert $ 0.00 to 0.00 or 5€ to 5
                    }

                    group.nb_visits    += parseInt(site.nb_visits, 10);
                    group.nb_pageviews += parseInt(site.nb_pageviews, 10);
                    if (revenue.length) {
                        group.revenue += parseInt(revenue[0], 10);
                    }
                });
            });
        }

        function createGroupsAndMoveSitesIntoRelatedGroup(allSitesUnordered, reportMetadata)
        {
            var sitesByGroup = [];
            var groups = {};

            // we do 3 things (complete site information, create groups, move sites into group) in one step for
            // performance reason, there can be > 20k sites
            angular.forEach(allSitesUnordered, function (site, index) {
                site.idsite   = reportMetadata[index].idsite;
                site.group    = reportMetadata[index].group;
                site.main_url = reportMetadata[index].main_url;
                // casting evolution to int fixes sorting, see: https://github.com/piwik/piwik/issues/4885
                site.visits_evolution    = parseInt(site.visits_evolution, 10);
                site.pageviews_evolution = parseInt(site.pageviews_evolution, 10);
                site.revenue_evolution   = parseInt(site.revenue_evolution, 10);

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

            calculateMetricsForEachGroup(groups);

            return sitesByGroup;
        }

        function getSumTotalActions(allSitesUnordered)
        {
            var totalActions = 0;

            if (allSitesUnordered && allSitesUnordered.length) {
                for (var index in allSitesUnordered) {
                    var site = allSitesUnordered[index];
                    if (site && site.nb_pageviews) {
                        totalActions += parseInt(site.nb_pageviews, 10);
                    }
                }
            }

            return totalActions;
        }

        function updateWebsitesList(processedReport) {
            if (!processedReport) {
                onError();
                return;
            }

            var allSitesUnordered = processedReport.reportData;

            model.totalActions = getSumTotalActions(allSitesUnordered);
            model.totalVisits  = processedReport.reportTotal.nb_visits;
            model.totalRevenue = processedReport.reportTotal.revenue;

            allSitesByGroup = createGroupsAndMoveSitesIntoRelatedGroup(allSitesUnordered, processedReport.reportMetadata);

            if (!allSitesByGroup.length) {
                return;
            }

            if (model.searchTerm) {
                searchSite(model.searchTerm);
            } else {
                model.sites = allSitesByGroup;
            }
        }

        function getNumberOfFilteredSites () {
            var numSites = model.sites.length;

            var groupNames = {};

            for (var index = 0; index < model.sites.length; index++) {
                var site = model.sites[index];
                if (site && site.isGroup) {
                    numSites += site.sites.length;
                }
            }

            return numSites;
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
        }

        function nextPage() {
            model.currentPage = model.currentPage + 1;
        }

        function nestedSearch(sitesByGroup, term)
        {
            var filteredSites = [];

            term = term.toLowerCase();

            for (var index in sitesByGroup) {
                var site = sitesByGroup[index];
                if (site.isGroup) {
                    var matchingSites = nestedSearch(site.sites, term);
                    if (matchingSites.length || (''+site.label).toLowerCase().indexOf(term) > -1) {
                        var clonedGroup   = copyGroup(site);
                        clonedGroup.sites = matchingSites;
                        filteredSites.push(clonedGroup);
                    }
                } else if ((''+site.label).toLowerCase().indexOf(term) > -1) {
                    filteredSites.push(site);
                } else if (site.group && (''+site.group).toLowerCase().indexOf(term) > -1) {
                    filteredSites.push(site);
                }
            }

            return filteredSites;
        }

        function searchSite (term) {
            model.searchTerm  = term;
            model.currentPage = 0;
            model.sites       = nestedSearch(allSitesByGroup, term);
        }

        function fetchPreviousSummary () {
            piwikApi.fetch({
                method: 'API.getLastDate'
            }).then(function (response) {
                if (response && response.value) {
                    return response.value;
                }
            }).then(function (lastDate) {
                if (!lastDate) {
                    return;
                }

                model.lastVisitsDate = lastDate;

                return piwikApi.fetch({
                    method: 'API.getProcessedReport',
                    apiModule: 'MultiSites',
                    apiAction: 'getAll',
                    hideMetricsDoc: '1',
                    filter_limit: '0',
                    showColumns: 'label,nb_visits',
                    enhanced: 1,
                    date: lastDate
                });
            }).then(function (response) {
                if (response && response.reportTotal) {
                    model.lastVisits = response.reportTotal.nb_visits;
                }
            });
        }

        function fetchAllSites(refreshInterval) {

            if (model.isLoading) {
                piwikApi.abort();
            }

            model.isLoading = true;
            model.errorLoadingSites = false;

            return piwikApi.fetch({
                method: 'API.getProcessedReport',
                apiModule: 'MultiSites',
                apiAction: 'getAll',
                hideMetricsDoc: '1',
                filter_limit: '-1',
                showColumns: 'label,nb_visits,nb_pageviews,visits_evolution,pageviews_evolution,revenue_evolution,nb_actions,revenue',
                enhanced: 1
            }).then(function (response) {
                updateWebsitesList(response);
            }, onError)['finally'](function () {
                model.isLoading = false;

                if (refreshInterval && refreshInterval > 0) {
                    $timeout(function () {
                        fetchAllSites(refreshInterval);
                    }, refreshInterval * 1000);
                }
            });
        }
    }
})();
