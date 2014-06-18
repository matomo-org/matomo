/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').factory('siteSelectorModel', function (piwikApi, $filter, piwik) {

    var model = {};
    model.sites = [];
    model.hasMultipleWebsites = false;
    model.isLoading = false;
    model.firstSiteName = '';

    var initialSites = null;

    model.updateWebsitesList = function (sites) {

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

    model.loadSite = function (idsite) {
        if (idsite == 'all') {
            piwik.broadcast.propagateNewPage('module=MultiSites&action=index');
        } else {
            piwik.broadcast.propagateNewPage('segment=&idSite=' + idsite, false);
        }
    };

    model.loadInitialSites = function () {
        if (initialSites) {
            model.sites = initialSites;
            return;
        }

        this.searchSite('%').then(function (websites) {
            initialSites = websites;
        });
    };

    return model;
});
