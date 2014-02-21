
angular.module('piwikApp').factory('siteSelectorModel', function (piwikApi, $filter) {

    var model = {};
    model.sites = [];
    model.hasMultipleWebsites = false;
    model.isLoading = false;
    model.firstSiteName = '';

    var initialSites = null;

    model.updateWebsitesList = function (websites) {

        if (!websites || !websites.length) {
            model.sites = [];
            return [];
        }

        model.sites = $filter('orderBy')(websites, '+name');

        if (!model.firstSiteName) {
            model.firstSiteName = model.sites[0].name;
        }

        model.hasMultipleWebsites = model.hasMultipleWebsites || websites.length > 1;

        return model.sites;
    };

    model.searchSite = function (term) {

        if (!term) {
            model.loadInitialSites();
            return;
        }

        if (model.isLoading) {
            piwikApi.abort();
        }

        model.isLoading = true;

        return piwikApi.fetch({
            method: 'SitesManager.getPatternMatchSites',
            pattern: term
        }).then(function (response) {
            return model.updateWebsitesList(response);
        })['finally'](function () {    // .finally() is not IE8 compatible see https://github.com/angular/angular.js/commit/f078762d48d0d5d9796dcdf2cb0241198677582c
            model.isLoading = false;
        });
    };

    model.loadInitialSites = function () {
        if (initialSites) {
            model.sites = initialSites;
            return;
        }

        this.searchSite('%').then(function (websites) {
            initialSites = websites;
        });
    }

    return model;
});
