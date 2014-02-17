
angular.module('piwikApp').factory('siteSelectorModel', function (piwikApi, $filter) {

    var model = {};
    model.sites = [];
    model.hasMultipleWebsites = false;
    model.isLoading = false;
    model.firstSiteName = '';

    model.updateWebsitesList = function (websites) {

        if (!websites || !websites.length) {
            model.sites = [];
            return;
        }

        angular.forEach(websites, function (website) {
            website.name = $filter('htmldecode')(website.name);
        });

        websites = $filter('orderBy')(websites, '+name')

        model.sites = websites;

        if (!model.firstSiteName) {
            model.firstSiteName = websites[0].name;
        }

        model.hasMultipleWebsites = model.hasMultipleWebsites || websites.length > 1;
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

        piwikApi.fetch({
            method: 'SitesManager.getPatternMatchSites',
            pattern: term
        }).then(function (response) {
            model.updateWebsitesList(response);
        }).finally(function () {
            model.isLoading = false;
        });
    };

    model.loadInitialSites = function () {
        this.searchSite('%');
    }

    return model;
});
